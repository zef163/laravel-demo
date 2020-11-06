<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request HTTP request.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Filters
        $filters = $request->only(['ownerId', 'reporterId']);

        return response()->json(
            Task::filter($filters)->with(['assignee', 'reporter'])->paginate($request->input('limit'))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TaskStoreRequest $request HTTP request.
     * @return JsonResponse
     */
    public function store(TaskStoreRequest $request): JsonResponse
    {
        // Create the task
        $task = Task::create($request->validated());

        return response()->json($task->only(['id']));
    }

    /**
     * Display the specified resource.
     *
     * @param Task $task Task data.
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        return response()->json($task->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TaskStoreRequest $request HTTP request.
     * @param Task $task Task data.
     * @return JsonResponse
     */
    public function update(TaskStoreRequest $request, Task $task): JsonResponse
    {
        return response()->json([
            'result' => $task->fill($request->validated())->save(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Task $task Task data.
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        return response()->json([
            'result' => $task->delete(),
        ]);
    }
}
