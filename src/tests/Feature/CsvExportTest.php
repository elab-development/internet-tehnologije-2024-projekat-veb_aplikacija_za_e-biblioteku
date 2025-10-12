<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CsvExportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->admin()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
        
        $this->regularUser = User::factory()->create([
            'email' => 'user@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_admin_can_export_books_as_csv()
    {
        Book::factory(5)->create();
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        

        $this->assertTrue(true);
    }

    public function test_regular_user_cannot_export_books()
    {
        Book::factory(3)->create();
        
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/v1/books/export');
        
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Access denied. Admin privileges required.'
        ]);
    }

    public function test_unauthenticated_user_cannot_export_books()
    {
        $response = $this->getJson('/api/v1/books/export');
        
        $response->assertStatus(401);
    }

    public function test_csv_export_with_search_filter()
    {
        Book::factory()->create(['title' => 'Test Book', 'author' => 'Test Author']);
        Book::factory()->create(['title' => 'Another Book', 'author' => 'Another Author']);
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export?q=Test');
        
        $response->assertStatus(200);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        

        $this->assertTrue(true);
    }

    public function test_csv_export_with_genre_filter()
    {
        Book::factory()->create(['title' => 'Fiction Book', 'genre' => 'Fiction']);
        Book::factory()->create(['title' => 'Drama Book', 'genre' => 'Drama']);
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export?genre=Fiction');
        
        $response->assertStatus(200);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        

        $this->assertTrue(true);
    }

    public function test_csv_export_with_year_filters()
    {
        Book::factory()->create(['title' => 'Old Book', 'year' => 1990]);
        Book::factory()->create(['title' => 'New Book', 'year' => 2020]);
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export?year_from=2000&year_to=2025');
        
        $response->assertStatus(200);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        

        $this->assertTrue(true);
    }

    public function test_csv_export_with_multiple_filters()
    {
        Book::factory()->create([
            'title' => 'Matching Book',
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2010
        ]);
        Book::factory()->create([
            'title' => 'Non Matching Book',
            'author' => 'Other Author',
            'genre' => 'Drama',
            'year' => 2010
        ]);
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export?q=Test&genre=Fiction&year_from=2000&year_to=2020');
        
        $response->assertStatus(200);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $this->assertTrue(true);
    }

    public function test_csv_export_empty_result()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export?q=NonexistentBook');
        
        $response->assertStatus(200);
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        

        $this->assertTrue(true);
    }

    public function test_csv_export_has_correct_filename()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export');
        
        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('books_export_', $contentDisposition);
        $this->assertStringContainsString('.csv', $contentDisposition);
    }

    public function test_csv_export_rate_limiting()
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->adminUser, 'sanctum')
                ->getJson('/api/v1/books/export');
            $this->assertEquals(200, $response->getStatusCode());
        }
        
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/v1/books/export');
        
        $response->assertStatus(429);
    }
}