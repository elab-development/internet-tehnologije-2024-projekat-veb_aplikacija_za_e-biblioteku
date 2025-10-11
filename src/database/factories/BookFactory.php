<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serbianBooks = [
            'Na Drini ćuprija', 'Prokleta avlija', 'Travnička hronika', 'Gospođica', 'Jelena, žena koje nema',
            'Gorski vijenac', 'Luca mikrokozma', 'Pisma iz Italije', 'Ogledalo srpsko', 'Naučnik i pesnik',
            'Krvava bajka', 'Krv i pepeo Jasenovca', 'Čovek po imenu Uve', 'Sto godina samoće', 'Ana Karenjina',
            'Rat i mir', 'Zločin i kazna', 'Braća Karamazovi', 'Idiot', 'Majstor i Margarita',
            'Doktor Živago', 'Lolita', '1984', 'Životinjska farma', 'Veliki Getsbi',
            'Ubiti pticu rugalicu', 'Gronovi gneva', 'Stranac', 'Kuga', 'Mali princ',
            'Zapad od raja', 'Hiljadu i jedna noć', 'Don Kihot', 'Hamlet', 'Romeo i Julija',
            'Makbet', 'Othello', 'Kralj Lir', 'Midsummer Night\'s Dream', 'The Tempest',
            'Pride and Prejudice', 'Jane Eyre', 'Wuthering Heights', 'Great Expectations', 'Oliver Twist',
            'David Copperfield', 'A Tale of Two Cities', 'Les Misérables', 'The Hunchback of Notre-Dame',
            'The Count of Monte Cristo', 'The Three Musketeers', 'Around the World in Eighty Days'
        ];

        $serbianAuthors = [
            'Ivo Andrić', 'Petar II Petrović Njegoš', 'Miloš Crnjanski', 'Meša Selimović', 'Danilo Kiš',
            'Milorad Pavić', 'Isidora Sekulić', 'Branislav Nušić', 'Jovan Sterija Popović', 'Laza Kostić',
            'Jovan Jovanović Zmaj', 'Đura Jakšić', 'Laza Lazarević', 'Stevan Sremac', 'Borisav Stanković',
            'Radoje Domanović', 'Veljko Petrović', 'Momo Kapor', 'David Albahari', 'Goran Petrović',
            'Vladimir Arsenijević', 'Svetislav Basara', 'Radoslav Petković', 'Aleksandar Gatalica',
            'Vladimir Pištalo', 'Dragan Velikić', 'Milica Mićić Dimovska', 'Svetlana Velmar-Janković',
            'Grozdana Olujić', 'Ljiljana Habjanović Đurović'
        ];

        $genres = [
            'Fiction', 'Drama', 'Poetry', 'History', 'Biography', 'Science Fiction',
            'Mystery', 'Romance', 'Thriller', 'Fantasy', 'Adventure', 'Philosophy',
            'Psychology', 'Sociology', 'Politics', 'Economics', 'Art', 'Music',
            'Travel', 'Cooking', 'Health', 'Education', 'Religion', 'Sports'
        ];

        return [
            'title' => $this->faker->randomElement($serbianBooks),
            'author' => $this->faker->randomElement($serbianAuthors),
            'year' => $this->faker->numberBetween(1800, 2024),
            'genre' => $this->faker->randomElement($genres),
            'isbn' => $this->faker->unique()->isbn13(),
        ];
    }
}
