<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FavoriteProduct>
 */
class FavoriteProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => fake()->numberBetween(1, 20), // FakeStore API tem ~20 produtos
        ];
    }

    /**
     * Create favorite for specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create favorite for specific product.
     */
    public function forProduct(int $productId): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Create multiple favorites for the same user.
     */
    public function forUserWithProducts(User $user, array $productIds): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'product_id' => fake()->randomElement($productIds),
        ]);
    }
}
