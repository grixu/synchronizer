<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Class ProductFactory
 * @package Grixu\Synchronizer\Tests\Helpers
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'index' => Str::random(50),
            'ean' => Str::random(40),
            'weight' => 2.00,
        ];
    }
}
