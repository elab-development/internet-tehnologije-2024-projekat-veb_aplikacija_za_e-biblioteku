<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Book;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        if (!Book::where('isbn', '978-86-17-12345-6')->exists()) {
            Book::factory()->create([
                'title' => 'Na Drini ćuprija',
                'author' => 'Ivo Andrić',
                'year' => 1945,
                'genre' => 'Fiction',
                'isbn' => '978-86-17-12345-6'
            ]);
        }

        if (!Book::where('isbn', '978-86-17-12346-3')->exists()) {
            Book::factory()->create([
                'title' => 'Gorski vijenac',
                'author' => 'Petar II Petrović Njegoš',
                'year' => 1847,
                'genre' => 'Poetry',
                'isbn' => '978-86-17-12346-3'
            ]);
        }

        if (!Book::where('isbn', '978-86-17-12347-0')->exists()) {
            Book::factory()->create([
                'title' => 'Prokleta avlija',
                'author' => 'Ivo Andrić',
                'year' => 1954,
                'genre' => 'Fiction',
                'isbn' => '978-86-17-12347-0'
            ]);
        }
    }
}
