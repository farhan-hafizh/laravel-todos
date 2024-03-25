<?php

namespace Database\Factories;

use App\Models\Subtask;
use App\Models\Task;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubtaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subtask::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $faker = new Faker();
        return [
            'task_id' => Task::factory(), // Create a related Task instance
            'title' => $faker->sentence,
            'completed' => false,
        ];
    }
}
