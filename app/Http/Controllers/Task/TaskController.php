<?php

namespace App\Http\Controllers\Task;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
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
            'status' => ['integer']
        ]);
        $request['project_id'] = (int)$request['project_id'];

        if ($validator->fails()) {
            return response()->json($validator->errors(), 404);
        } else {
            $projectCheck = Project::find($request['project_id']);
            if (!$projectCheck) {
                return response()->json([
                    'err' => 'project not found'
                ], 404);
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
            ], 200);
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
            $task = Db::table('tasks')->where('project_id', $request['project_id'])->where('id', $id)->update($request->all());
            return response()->json(Task::find($id));
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('tasks')->where('id', $id)->delete();
        DB::table('user_tasks')->where('task_id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['msg' => 'removed']);

    }

    public function addUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer'],
            'user_id' => ['required', 'string'],
            'task_id' => ['required', 'integer']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $project = Project::find($request['project_id']);
            if(!$project){
                return response()->json(['err'=>'project not found'],404);
            }
            $user = User::find($request['user_id']);
            if(!$user){
                return response()->json(['err'=>'user not found'],404);
            }
            $task = Task::find($request['task_id']);
            if(!$task){
                return response()->json(['err'=>'Task not found'],404);
            }
            foreach ($request['user_id'] as $item) {
                $ret = UserTasks::create([
                    'user_id' => $item,
                    'project_id' => $request['project_id'],
                    'task_id'  => $request['task_id']
                ]);
            }
            return response()->json(['msg'=>'added']);

        }

    }

    public function currentUsers(int $id): JsonResponse
    {
        $userIds = UserTasks::select('user_id')->where('task_id',$id)->get();
        foreach ($userIds as $userId) {
            $user = User::select('id','name')->where('id',$userId['user_id'])->get();
            $users[] = $user;
        }
        return response()->json($users);
    }

}


