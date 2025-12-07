<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'age' => fake()->numberBetween(18, 65),
            'pictures' => [
                fake()->imageUrl(640, 480, 'people', true),
                fake()->imageUrl(640, 480, 'people', true),
                fake()->imageUrl(640, 480, 'people', true),
            ],
            'location' => fake()->city() . ', ' . fake()->country(),
            'likes_count' => fake()->numberBetween(0, 1000),
            'notified_at' => null,
        ];
    }
}
