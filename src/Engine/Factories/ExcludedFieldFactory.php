<?php

namespace Grixu\Synchronizer\Engine\Factories;

use Grixu\Synchronizer\Engine\Models\ExcludedField;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExcludedFieldFactory extends Factory
{
    public $model = ExcludedField::class;

    public function definition()
    {
        return [
            'field' => $this->faker->name,
            'model' => $this->faker->name,
            'update_empty' => $this->faker->numberBetween(0,1),
        ];
    }
}
