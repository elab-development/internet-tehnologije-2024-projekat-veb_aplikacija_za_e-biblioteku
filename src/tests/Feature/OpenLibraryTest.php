<?php

namespace Tests\Feature;

use App\Services\OpenLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_book_by_valid_isbn()
    {
        $response = $this->getJson('/api/v1/books/fetch-by-isbn?isbn=9780140449198');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'title',
                    'author',
                    'year',
                    'description',
                    'isbn',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'isbn' => '9780140449198',
                ],
            ]);
    }

    public function test_fetch_book_by_invalid_isbn()
    {
        $response = $this->getJson('/api/v1/books/fetch-by-isbn?isbn=1234567890');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Book not found in Open Library',
            ]);
    }

    public function test_fetch_book_by_short_isbn()
    {
        $response = $this->getJson('/api/v1/books/fetch-by-isbn?isbn=123');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'errors' => [
                    'isbn',
                ],
            ]);
    }

    public function test_fetch_book_missing_isbn()
    {
        $response = $this->getJson('/api/v1/books/fetch-by-isbn');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'errors' => [
                    'isbn',
                ],
            ]);
    }

    public function test_rate_limiting_on_fetch_by_isbn()
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/books/fetch-by-isbn?isbn=9780140449198');
            $response->assertStatus(200);
        }

        $response = $this->getJson('/api/v1/books/fetch-by-isbn?isbn=9780140449198');
        $response->assertStatus(429);
    }
}
