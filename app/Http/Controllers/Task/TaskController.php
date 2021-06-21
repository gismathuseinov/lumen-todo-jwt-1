<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->has('id')
            ? Task::find($request->id)
            : Task::all();
        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:5'],
            'descr' => ['required', 'string', 'min:20'],
            'close' => ['required'],
            'status' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $task = Task::create($request->all());
            return response()->json($task);
        }
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:5'],
            'descr' => ['required', 'string', 'min:20'],
            'close' => ['required'],
            'status' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $task = Task::where('id', $request->id)->update($request->all());
            return response()->json(Task::find($request->id));
        }
    }
}
