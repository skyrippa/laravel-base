<?php

namespace Database\Factories;

use App\Models\State;
use Faker\Provider\pt_BR\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class StateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = State::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition ()
    {
        $this->faker->addProvider(new Address($this->faker));

        return [
            'name' => $this->faker->state,
            'uf'   => $this->faker->stateAbbr,
        ];
    }
}
