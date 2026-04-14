<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $salesUserId = User::query()->where('role', 'Sales')->value('id')
            ?? User::factory()->create([
                'role' => 'Sales',
                'username' => fake()->unique()->userName(),
            ])->id;

        return [
            'user_id' => $salesUserId,
            'invoice_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'customer_name' => $this->faker->company(),
            'customer_number' => $this->faker->numberBetween(5000, 5999),
            'fiscal_data' => $this->faker->text(100),
            'delivery_address' => $this->faker->address(),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(Order::STATUSES),
            'route_photo' => null,
            'delivery_photo' => null,
        ];
    }
}
