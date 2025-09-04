<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemorizeLater>
 */
class MemorizeLaterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $books = ['Genesis', 'Exodus', 'Psalms', 'Proverbs', 'Matthew', 'Mark', 'Luke', 'John', 'Romans', 'Ephesians'];
        $startVerse = $this->faker->numberBetween(1, 20);
        $endVerse = $this->faker->numberBetween($startVerse, $startVerse + 5);
        
        return [
            'user_id' => User::factory(),
            'book' => $this->faker->randomElement($books),
            'chapter' => $this->faker->numberBetween(1, 50),
            'verses' => range($startVerse, $endVerse),
            'note' => $this->faker->optional(0.7)->sentence(),
            'added_at' => $this->faker->dateTimeThisYear(),
        ];
    }

    /**
     * Create a memorize later entry with a specific verse reference.
     */
    public function withVerse(string $book, int $chapter, array $verses): static
    {
        return $this->state(fn (array $attributes) => [
            'book' => $book,
            'chapter' => $chapter,
            'verses' => $verses,
        ]);
    }

    /**
     * Create a memorize later entry without a note.
     */
    public function withoutNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => null,
        ]);
    }

    /**
     * Create a memorize later entry with a specific note.
     */
    public function withNote(string $note): static
    {
        return $this->state(fn (array $attributes) => [
            'note' => $note,
        ]);
    }
}
