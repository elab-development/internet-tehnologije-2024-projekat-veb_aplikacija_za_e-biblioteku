<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'borrowed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'due_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'returned_at' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => $this->faker->dateTimeBetween($attributes['borrowed_at'], 'now'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'returned_at' => null,
        ]);
    }
}
