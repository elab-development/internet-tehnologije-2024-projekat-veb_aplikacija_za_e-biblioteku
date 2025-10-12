<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->book = Book::factory()->create();
    }

    public function test_user_can_borrow_book()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/v1/loans/{$this->book->id}/borrow");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'borrowed_at',
                    'due_at',
                    'book' => ['id', 'title', 'author']
                ],
                'message'
            ]);

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null
        ]);
    }

    public function test_multiple_users_can_borrow_same_book()
    {
        $user2 = User::factory()->create(['role' => 'user']);
        
        Sanctum::actingAs($this->user);
        $response1 = $this->postJson("/api/v1/loans/{$this->book->id}/borrow");
        $response1->assertStatus(201);

        Sanctum::actingAs($user2);
        $response2 = $this->postJson("/api/v1/loans/{$this->book->id}/borrow");
        $response2->assertStatus(201);

        $this->assertDatabaseCount('loans', 2);
        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null
        ]);
        $this->assertDatabaseHas('loans', [
            'user_id' => $user2->id,
            'book_id' => $this->book->id,
            'returned_at' => null
        ]);
    }

    public function test_user_can_return_borrowed_book()
    {
        Sanctum::actingAs($this->user);

        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'borrowed_at',
                    'due_at',
                    'returned_at',
                    'book' => ['id', 'title', 'author']
                ],
                'message'
            ]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'returned_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function test_user_cannot_return_already_returned_book()
    {
        Sanctum::actingAs($this->user);

        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
            'returned_at' => now(),
        ]);

        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'This book has already been returned.'
            ]);
    }

    public function test_user_can_view_own_loans()
    {
        Sanctum::actingAs($this->user);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->getJson('/api/v1/loans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'book_id',
                        'borrowed_at',
                        'due_at',
                        'returned_at',
                        'book' => ['id', 'title', 'author']
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_user_can_view_own_loan_details()
    {
        Sanctum::actingAs($this->user);

        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->getJson("/api/v1/loans/{$loan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'borrowed_at',
                    'due_at',
                    'returned_at',
                    'book' => ['id', 'title', 'author'],
                    'user' => ['id', 'name', 'email']
                ],
                'message'
            ]);
    }

    public function test_user_cannot_view_other_users_loans()
    {
        $otherUser = User::factory()->create(['role' => 'user']);
        
        Sanctum::actingAs($this->user);

        $loan = Loan::create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->getJson("/api/v1/loans/{$loan->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
    }

    public function test_admin_can_view_all_loans()
    {
        Sanctum::actingAs($this->admin);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->getJson('/api/v1/loans');

        $response->assertStatus(200);
    }

    public function test_admin_can_update_loan()
    {
        Sanctum::actingAs($this->admin);

        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $newDueDate = now()->addDays(45)->format('Y-m-d H:i:s');

        $response = $this->putJson("/api/v1/loans/{$loan->id}", [
            'due_at' => $newDueDate
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'borrowed_at',
                    'due_at',
                    'returned_at',
                    'book' => ['id', 'title', 'author'],
                    'user' => ['id', 'name', 'email']
                ],
                'message'
            ]);

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'due_at' => $newDueDate
        ]);
    }

    public function test_regular_user_cannot_update_other_users_loan()
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $loan = Loan::create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->putJson("/api/v1/loans/{$loan->id}", [
            'due_at' => now()->addDays(45)->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
    }

    public function test_create_loan_via_store_endpoint()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/loans', [
            'book_id' => $this->book->id
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'book_id',
                    'borrowed_at',
                    'due_at',
                    'book' => ['id', 'title', 'author']
                ],
                'message'
            ]);

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null
        ]);
    }

    public function test_unauthenticated_user_cannot_access_loans()
    {
        $response = $this->getJson('/api/v1/loans');
        $response->assertStatus(401);

        $response = $this->postJson("/api/v1/loans/{$this->book->id}/borrow");
        $response->assertStatus(401);

        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(30),
        ]);

        $response = $this->postJson("/api/v1/loans/{$loan->id}/return");
        $response->assertStatus(401);
    }
}
