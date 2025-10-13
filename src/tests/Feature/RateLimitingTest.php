<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_login_rate_limiting()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Prvi zahtev - treba da prođe
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $response->assertStatus(200);

        // Simuliramo više neuspelih pokušaja sa pogrešnim passwordom
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrong_password',
            ]);
        }

        // Sledeći zahtev treba da bude rate limited
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(429);
    }

    public function test_search_rate_limiting()
    {
        // Kreirajmo nekoliko knjiga za pretragu
        \App\Models\Book::factory()->count(3)->create();

        // Simuliramo više zahteva za pretragu
        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson('/api/v1/books/search?query=test');
            $response->assertStatus(200);
        }

        // Sledeći zahtev treba da bude rate limited
        $response = $this->getJson('/api/v1/books/search?query=test');
        $response->assertStatus(429);
    }

    public function test_upload_rate_limiting()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin, 'sanctum');

        $book = \App\Models\Book::factory()->create();

        // Simuliramo više upload zahteva - throttle je 10 zahteva po minuti
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson("/api/v1/books/{$book->id}/cover", [
                'cover' => \Illuminate\Http\UploadedFile::fake()->image('cover.jpg'),
            ]);
        }

        // Sledeći zahtev treba da bude rate limited
        $response = $this->postJson("/api/v1/books/{$book->id}/cover", [
            'cover' => \Illuminate\Http\UploadedFile::fake()->image('cover.jpg'),
        ]);

        $response->assertStatus(429);
    }
}