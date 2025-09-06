<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['CREATE', 'UPDATE', 'DELETE', 'RESTORE'];
        $tables = ['users', 'memory_bank', 'memorize_later', 'audit_logs'];

        return [
            'user_id' => null, // We'll set this explicitly in tests
            'action' => $this->faker->randomElement($actions),
            'table_name' => $this->faker->randomElement($tables),
            'record_id' => $this->faker->numberBetween(1, 1000),
            'old_values' => $this->faker->randomElement([
                null,
                ['name' => 'Old Name', 'email' => 'old@example.com'],
                ['status' => 'inactive'],
            ]),
            'new_values' => $this->faker->randomElement([
                null,
                ['name' => 'New Name', 'email' => 'new@example.com'],
                ['status' => 'active'],
            ]),
            'performed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
