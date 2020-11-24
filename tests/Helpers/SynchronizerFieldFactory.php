<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Grixu\Synchronizer\Models\SynchronizerField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class SynchronizerFieldFactory
 * @package Grixu\Synchronizer\Tests\Helpers
 */
class SynchronizerFieldFactory extends Factory
{
    public $model = SynchronizerField::class;

    public function definition()
    {
        return [
            'field' => $this->faker->name,
            'model' => $this->faker->name,
            'update_empty' => $this->faker->numberBetween(0,1),
        ];
    }
}
