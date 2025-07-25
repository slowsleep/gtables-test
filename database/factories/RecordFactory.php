<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Status;

class RecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->title(),
            'status' => $this->faker->randomElement([Status::ALLOWED, Status::PROHIBITED]),
        ];
    }
}
