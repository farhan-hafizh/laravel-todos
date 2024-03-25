<?php

namespace Database\Factories;

use App\Models\Task;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $faker = new Faker();
        return [
            'title' => $faker->sentence,
            'description' => $faker->paragraph,
            'completed' => false,
            'due_date' => $faker->dateTimeBetween('+1 day', '+1 week'),
        ];
    }
}
