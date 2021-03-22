<?php

namespace Grixu\Synchronizer\Tests\Helpers;

use Illuminate\Database\Eloquent\Factories\Factory;

class FakeForeignSqlSourceFactory extends Factory
{
    protected $model = FakeForeignSqlSourceModel::class;

    public function definition()
    {
        return [
            'Knt_Nazwa1' => $this->faker->name,
            'Knt_Nazwa2' => $this->faker->name,
            'Knt_Kraj' => $this->faker->countryCode,
            'Knt_KodP' => $this->faker->postcode,
            'Knt_Miasto' => $this->faker->city,
            'Knt_Nip' => $this->faker->numberBetween(1111111111, 9999999999),
            'Knt_Ulica' => $this->faker->streetName,
            'Knt_Wojewodztwo' => $this->faker->word,
            'Knt_Powiat' => $this->faker->country,
            'Knt_Telefon1' => $this->faker->phoneNumber,
            'Knt_Telefon2' => $this->faker->phoneNumber,
            'Knt_EMail' => $this->faker->email,
            'Knt_LimitOkres' => $this->faker->numberBetween(600,900),
            'Knt_AtrWlascicielFrsID' => $this->faker->numberBetween(100000000, 999999999),
            'Knt_OpeNumer' => $this->faker->numberBetween(100000000, 999999999),
            'Knt_GIDNumer' => $this->faker->numberBetween(100000000, 999999999),
            'Knt_SyncTimeStamp' => now(),
        ];
    }
}
