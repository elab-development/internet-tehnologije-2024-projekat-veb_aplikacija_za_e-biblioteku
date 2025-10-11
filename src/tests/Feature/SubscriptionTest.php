<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $userWithoutSubscription;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'user']);
        $this->userWithoutSubscription = User::factory()->create(['role' => 'user']);
        $this->book = Book::factory()->create();
    }

    public function test_user_with_active_subscription_can_read_book()
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        \Storage::disk('public')->put('pdfs/test.pdf', 'Test PDF content');

        $this->book->update(['pdf_path' => 'pdfs/test.pdf']);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/books/{$this->book->id}/read");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'book_id',
                    'title',
                    'total_pages',
                    'content',
                    'has_subscription',
                    'is_full_book'
                ]
            ]);
    }

    public function test_user_without_subscription_cannot_read_book()
    {
        Sanctum::actingAs($this->userWithoutSubscription);

        $response = $this->getJson("/api/v1/books/{$this->book->id}/read");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Active subscription required to access this content.',
                'error' => 'subscription_required'
            ]);
    }

    public function test_user_with_expired_subscription_cannot_read_book()
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/books/{$this->book->id}/read");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Active subscription required to access this content.',
                'error' => 'subscription_required'
            ]);
    }

    public function test_user_with_future_subscription_cannot_read_book()
    {
        Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonths(2),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/v1/books/{$this->book->id}/read");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Active subscription required to access this content.',
                'error' => 'subscription_required'
            ]);
    }

    public function test_subscription_model_is_active_method()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($subscription->isActive());

        $expiredSubscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertFalse($expiredSubscription->isActive());
    }

    public function test_user_has_active_subscription_method()
    {
        $this->assertFalse($this->user->hasActiveSubscription());

        Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertTrue($this->user->hasActiveSubscription());
    }

    public function test_user_subscriptions_relationship()
    {
        $subscription1 = Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $subscription2 = Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'premium',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonths(2),
        ]);

        $this->assertCount(2, $this->user->subscriptions);
        $this->assertTrue($this->user->subscriptions->contains($subscription1));
        $this->assertTrue($this->user->subscriptions->contains($subscription2));
    }

    public function test_subscription_belongs_to_user()
    {
        $subscription = Subscription::create([
            'user_id' => $this->user->id,
            'plan' => 'basic',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertEquals($this->user->id, $subscription->user->id);
        $this->assertEquals($this->user->name, $subscription->user->name);
    }

    public function test_unauthenticated_user_cannot_read_book()
    {
        $response = $this->getJson("/api/v1/books/{$this->book->id}/read");

        $response->assertStatus(401);
    }
}
