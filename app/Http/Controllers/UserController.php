<?php


namespace App\Http\Controllers;


use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function profile(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user()
        ]);
    }


    public function show(Request $request): JsonResponse
    {
        $user = $request->has('id')
            ? User::find($request->id)
            : User::all();
        return response()->json($user);
    }

    public function logout(): JsonResponse
    {
        \auth()->logout();
        return response()->json([
            'msg' => 'logged out'
        ]);
    }

    public function projects(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $project = $request->has('id')
            ? Project::where('user_id', $userId)->where('id', $request->id)->get()
            : Auth::user()->projects;
        return response()->json($project);
    }

    public function tasks(Request $request): JsonResponse
    {
        $tasks = $request->has('id')
            ? Auth::user()->singleTask($request->id)
            : Auth::user()->tasks;
        return response()->json($tasks);
    }

    public function projectTasks(int $id): JsonResponse
    {
        $tasks = UserTasks::select('task_id', 'project_id')
            ->where('user_id', Auth::id())
            ->where('project_id', $id)
            ->get();
        foreach ($tasks as $key => $task) {
            $return[$key]['task'] = Task::where('id', $task['task_id'])->where('project_id', $task['project_id'])->get();
            $return[$key]['project'] = Project::where('id', $task['project_id'])->where('user_id', Auth::id())->get();
        }
        return response()->json($return);
    }
}
