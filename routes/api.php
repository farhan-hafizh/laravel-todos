<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'tasks'], function () {
    Route::get('/', [TaskController::class, 'index']);  // Get all tasks (ongoing)
    Route::get('/{task}', [TaskController::class, 'show']); // Get a specific task
    Route::post('/', [TaskController::class, 'store']);   // Create a new task
    Route::put('/{task}', [TaskController::class, 'update']); // Update a task
    Route::delete('/{task}', [TaskController::class, 'destroy']); // Delete a task

    // Additional routes for completed tasks (optional)
    Route::get('/completed', [TaskController::class, 'completed']); // Get all completed tasks

    // Routes for subtasks (assuming related to tasks)
    Route::get('/{taskId}/subtasks', [TaskController::class, 'showSubtasks']); // Get subtasks for a specific task
    Route::post('/{taskId}/subtasks', [TaskController::class, 'storeSubtask']); // Create a subtask for a specific task
    Route::put('/{taskId}/subtasks/{subtaskId}', [TaskController::class, 'updateSubtask']); // Update a subtask
    Route::delete('/{taskId}/subtasks/{subtaskId}', [TaskController::class, 'destroySubtask']); // Delete a subtask
});
