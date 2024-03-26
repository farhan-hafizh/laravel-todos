<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Task::where('completed', false)->with('subtasks')->get(); // Fetch ongoing tasks with subtasks
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s', // Allow optional date format
            'subtasks.*.title' => 'required|string|max:255', // Validate subtask titles if provided
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = Task::create($request->except('subtasks')); // Create task

        // Create subtasks if provided in the request
        if ($request->has('subtasks')) {
            foreach ($request->subtasks as $subtaskData) {
                $subtask = new Subtask([
                    'task_id' => $task->id,
                    'title' => $subtaskData['title'],
                ]);
                $task->subtasks()->save($subtask);
            }
        }

        return response()->json($task->load('subtasks'), 201); // Return created task with subtasks
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $task = Task::with('subtasks')->findOrFail($id);
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date_format:Y-m-d H:i:s', // Allow optional date format
            'completed' => 'nullable|boolean',
            'subtasks.*.id' => 'nullable|integer', // Allow updating subtask IDs
            'subtasks.*.title' => 'nullable|string|max:255', // Allow updating subtask titles
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = Task::findOrFail($id);
        $task->update($request->except('subtasks'));

        // Update subtasks (if provided)
        if ($request->has('subtasks')) {
            $existingSubtaskIds = $task->subtasks->pluck('id')->toArray();
            $newSubtasks = [];

            foreach ($request->subtasks as $subtaskData) {
                if (isset($subtaskData['id']) && in_array($subtaskData['id'], $existingSubtaskIds)) {
                    // Update existing subtask
                    $subtask = Subtask::find($subtaskData['id']);
                    $subtask->title = $subtaskData['title'];
                    $subtask->save();
                } else {
                    // Create new subtask
                    $newSubtask = new Subtask([
                        'task_id' => $task->id,
                        'title' => $subtaskData['title'],
                    ]);
                    $newSubtasks[] = $newSubtask;
                }
            }

            // Delete removed subtasks (based on missing IDs)
            $deletedSubtaskIds = array_diff($existingSubtaskIds, array_column($request->subtasks, 'id'));
            Subtask::whereIn('id', $deletedSubtaskIds)->delete();

            // Save any newly created subtasks
            if (count($newSubtasks) > 0) {
                $task->subtasks()->saveMany($newSubtasks);
            }
        }

        return response()->json($task->load('subtasks'), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->subtasks()->delete(); // Delete associated subtasks
        $task->delete();

        return response()->json(null, 204); // No content response on successful deletion
    }

    // Additional methods for completed tasks (optional)
    public function completed()
    {
        return Task::where('completed', true)->with('subtasks')->get(); // Fetch completed tasks with subtasks
    }

    // Additional methods for subtasks (assuming related to tasks)
    public function getSubtasks($taskId)
    {
        $task = Task::findOrFail($taskId);  // Find the parent task

        // Check if the task has any subtasks
        if ($task->subtasks->count() > 0) {
            return response()->json($task->subtasks);
        } else {
            return response()->json([], 204); // Empty response with No Content status code
        }
    }

    public function storeSubtask(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subtask = new Subtask([
            'task_id' => $taskId,
            'title' => $request->input('title'),
        ]);
        $subtask->save();

        return response()->json($subtask, 201);
    }
    public function updateSubtask(Request $request, $taskId, $subtaskId)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'completed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $subtask = Subtask::where('task_id', $taskId)->findOrFail($subtaskId);
        $subtask->update($request->only('title', 'completed'));

        return response()->json($subtask, 200);
    }

    public function destroySubtask($taskId, $subtaskId)
    {
        $subtask = Subtask::where('task_id', $taskId)->findOrFail($subtaskId);
        $subtask->delete();

        return response()->json(null, 204);
    }
}
