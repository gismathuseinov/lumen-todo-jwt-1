<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\UserTasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $task = Task::query();
        if (!$request->has('id')) {
            if ($request->has('title')) {
                $task->where('title', 'like', '%' . $request->get('title') . '%')->get();
            }
            if ($request->has('close')) {
                $task->where('close', $request->get('close'))->get();
            }
            if ($request->has('status')) {
                $task->where('status', $request->get('status'))->get();
            }
            return response()->json($task->get());
        } else {
            return response()->json(Task::findOrFail($request->id));
        }
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
        $request['project_id'] = (int)$request['project_id'];

        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $projectCheck = Project::find($request['project_id']);
            if (!$projectCheck) {
                return response()->json([
                    'err' => 'project not found'
                ]);
            }
            $task = Task::create($request->all());
            $rel = UserTasks::create([
                'user_id' => Auth::id(),
                'task_id' => $task['id'],
                'project_id' => $request['project_id']
            ]);
            return response()->json([
                'task' => $task,
                'relation' => $rel
            ]);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'min:5'],
            'descr' => ['required', 'string', 'min:20'],
            'close' => ['required'],
            'status' => ['required', 'integer']
        ]);
        $request['project_id'] = (int)$request['project_id'];
        if ($validator->fails()) {
            return response()->json($validator->errors());

        } else {
            $projectCheck = Project::find($request['project_id']);
            if (!$projectCheck) {
                return response()->json([
                    'err' => 'project not found'
                ]);
            }
            $task = Task::findOrFail($id);
            $task->update($request->all());
            return response()->json($task);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('tasks')->where('id', $id)->delete();
        DB::table('user_tasks')->where('task_id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['msg' => 'removed']);

    }

}
