<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/books/{$book->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => true,
                ]
            ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_user_can_unlike_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        Like::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/books/{$book->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => false,
                ]
            ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_like_book()
    {
        $book = Book::factory()->create();

        $response = $this->postJson("/api/v1/books/{$book->id}/like");

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_user_cannot_like_same_book_twice()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        Like::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/books/{$book->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => false,
                ]
            ]);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_like_status_endpoint_works()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/books/{$book->id}/like-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => false,
                    'likes_count' => 0,
                ]
            ]);
    }

    public function test_like_status_endpoint_works_for_unauthenticated_user()
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}/like-status");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => false,
                    'likes_count' => 0,
                ]
            ]);
    }

    public function test_book_shows_correct_like_count()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();
        
        Like::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);
        
        Like::create([
            'user_id' => $user2->id,
            'book_id' => $book->id,
        ]);
        
        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'likes_count' => 2,
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertTrue($responseData['is_liked_by_user']);
    }

    public function test_like_count_decreases_when_user_unlikes()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();
        
        Like::create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
        ]);
        
        Like::create([
            'user_id' => $user2->id,
            'book_id' => $book->id,
        ]);
        
        Sanctum::actingAs($user1);

        $response = $this->postJson("/api/v1/books/{$book->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_liked' => false,
                    'likes_count' => 1,
                ]
            ]);
    }
}