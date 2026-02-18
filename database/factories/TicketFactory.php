<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'type' => $this->faker->randomElement(['VIP', 'Standard', 'General']),
            'price' => $this->faker->numberBetween(50, 500),
            'quantity' => $this->faker->numberBetween(50, 200),
        ];
    }
}
