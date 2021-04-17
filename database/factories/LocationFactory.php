<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->catchPhrase,
            'phone_number' => $this->faker->phoneNumber,
            'feed_id' => (Location::query()->latest()->first()->feed_id ?? $this->faker->numberBetween(0, 9)) + 1,
            'brand_id' => Brand::query()->latest()->first()->id,
        ];
    }
}
