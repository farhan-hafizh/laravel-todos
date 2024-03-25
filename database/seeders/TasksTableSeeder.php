<?php

namespace Database\Seeders;

use App\Models\Task;
use Database\Factories\SubtaskFactory;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Task::factory()->count(10)->create()->each(function ($task) {
            // Create a random number of subtasks between 1 and 5
            $subtaskCount = rand(1, 5);

            // Use a loop to create subtasks using the SubtaskFactory
            for ($i = 0; $i < $subtaskCount; $i++) {
                $task->subtasks()->save(SubtaskFactory::new()->make());
            }
        });
    }
}
