<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected User $anotherUser;
    protected Book $book;
    protected Loan $loan;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed');
        
        $this->adminUser = User::factory()->admin()->create();
        $this->regularUser = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        
        $this->book = Book::factory()->create();
        $this->loan = Loan::factory()->create(['user_id' => $this->regularUser->id]);
    }

    public function test_regular_user_cannot_delete_book()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->deleteJson("/api/v1/books/{$this->book->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is unauthorized.',
            'code' => 403
        ]);
    }

    public function test_admin_can_delete_book()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/v1/books/{$this->book->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Book deleted successfully'
        ]);
    }

    public function test_regular_user_cannot_update_book()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson("/api/v1/books/{$this->book->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is unauthorized.',
            'code' => 403
        ]);
    }

    public function test_admin_can_update_book()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/v1/books/{$this->book->id}", [
                'title' => 'Updated Title'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Book updated successfully'
        ]);
    }

    public function test_regular_user_cannot_create_book()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/v1/books', [
                'title' => 'New Book',
                'author' => 'New Author',
                'year' => 2024,
                'genre' => 'Fiction',
                'isbn' => '978-86-17-99999-9'
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is unauthorized.',
            'code' => 403
        ]);
    }

    public function test_admin_can_create_book()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/v1/books', [
                'title' => 'New Book',
                'author' => 'New Author',
                'year' => 2024,
                'genre' => 'Fiction',
                'isbn' => '978-86-17-99999-9'
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Book created successfully'
        ]);
    }

    public function test_user_can_view_own_loan()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson("/api/v1/loans/{$this->loan->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Loan retrieved successfully'
        ]);
    }

    public function test_user_cannot_view_other_loan()
    {
        $response = $this->actingAs($this->anotherUser, 'sanctum')
            ->getJson("/api/v1/loans/{$this->loan->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is unauthorized.',
            'code' => 403
        ]);
    }

    public function test_admin_can_view_any_loan()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/v1/loans/{$this->loan->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Loan retrieved successfully'
        ]);
    }

    public function test_user_can_update_own_loan()
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->putJson("/api/v1/loans/{$this->loan->id}", [
                'due_at' => '2025-12-01'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Loan updated successfully'
        ]);
    }

    public function test_user_cannot_update_other_loan()
    {
        $response = $this->actingAs($this->anotherUser, 'sanctum')
            ->putJson("/api/v1/loans/{$this->loan->id}", [
                'due_at' => '2025-12-15'
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'This action is unauthorized.',
            'code' => 403
        ]);
    }

    public function test_any_user_can_create_loan()
    {
        $book = Book::factory()->create();
        
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/v1/loans', [
                'book_id' => $book->id
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Loan created successfully'
        ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->deleteJson("/api/v1/books/{$this->book->id}");
        $response->assertStatus(401);

        $response = $this->getJson("/api/v1/loans/{$this->loan->id}");
        $response->assertStatus(401);
    }
}
