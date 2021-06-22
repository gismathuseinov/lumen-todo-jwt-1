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

    public function index(Request $request): JsonResponse
    {
        $user = User::query();
        if (!$request->has('id')) {
            if ($request->has('name')) {
                $user->where('name', 'like', '%' . $request->get('name') . '%')->get();
            }
            if ($request->has('role')) {
                $user->where('roles', $request->get('role'))->get();
            }

            return response()->json($user->get());
        } else {
            return response()->json(User::findOrFail($request->id));
        }
    }

    public function profile(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user()
        ]);
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
        $tasks = Task::query();
        if (!$request->has('id')) {
            if ($request->has('title')) {
                $tasks->where('title', 'like', '%' . $request->get('title') . '%');
            }
            if ($request->has('close')) {
                $tasks->where('close', 'like','%'.$request->get('close').'%');
            }
            if ($request->has('status')) {
                $tasks->where('status', $request->get('status'));
            }
            return response()->json($tasks->get());
        } else {
            return response()->json(Task::findOrFail($request->id));
        }
    }

    public function projectTasks(int $id): JsonResponse
    {
        $tasks = UserTasks::select('task_id', 'project_id')
            ->where('user_id', Auth::id())
            ->where('project_id', $id)
            ->get();
        if (sizeof($tasks) !== 0) {
            foreach ($tasks as $key => $task) {
                $return[$key] = Task::where('id', $task['task_id'])->where('project_id', $task['project_id'])->get();
            }
            return response()->json($return);
        } else {
            return response()->json(['msg' => 'not found'], 404);
        }
    }
}
