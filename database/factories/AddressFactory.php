<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Address;
use App\Models\State;
use App\Utils\Helpers;
use Faker\Provider\pt_BR\Company;
use Faker\Provider\pt_BR\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition ()
    {
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Company($this->faker));

        return [
            'zip_code'     => Helpers::sanitizeString($this->faker->postcode),
            'street'       => $this->faker->streetName,
            'house_number' => rand(1, 2000),
            'neighborhood' => $this->faker->name,
            'state_id'     => State::factory()->create(),
            'city_id'      => City::factory()->create()
        ];
    }
}
