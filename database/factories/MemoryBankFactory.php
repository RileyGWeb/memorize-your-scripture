<?php

namespace Database\Factories;

use App\Models\MemoryBank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemoryBankFactory extends Factory
{
    protected $model = MemoryBank::class;

    public function definition(): array
    {
        $books = [
            'John', 'Romans', 'Psalms', 'Matthew', 'Philippians', 
            'Ephesians', 'Colossians', 'James', '1 Peter', '2 Timothy'
        ];

        $book = $this->faker->randomElement($books);
        $chapter = $this->faker->numberBetween(1, 28);
        
        // Generate random verse ranges
        $startVerse = $this->faker->numberBetween(1, 25);
        $endVerse = $this->faker->randomElement([
            $startVerse, // single verse
            $startVerse + 1, // two verses
            $startVerse + 2, // three verses
        ]);

        return [
            'user_id' => 1, // Default to user ID 1, will be overridden by forUser()
            'book' => $book,
            'chapter' => $chapter,
            'verses' => [[$startVerse, $endVerse]],
            'difficulty' => $this->faker->randomElement(['easy', 'normal', 'strict']),
            'accuracy_score' => $this->faker->randomFloat(2, 70, 100),
            'memorized_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'bible_translation' => 'ESV',
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
