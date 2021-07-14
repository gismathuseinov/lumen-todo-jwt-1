<?php


namespace App\Http\Controllers;


use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\UserProjects;
use App\Models\UserTasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

            return response()->json($user->where('id', '!=', Auth::id())->get());
        } else {
            return response()->json(User::findOrFail($request->id));
        }
    }

    public function profile(): JsonResponse
    {
        return response()->json(Auth::user());
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
        if($request->has('id')){
            $project = Project::where('user_id', $userId)->where('id', $request->id)->get();
            return response()->json($project);
        }else{
            $projectList = UserProjects::where('user_id',$userId)->get();

            if(sizeof($projectList)!==0){
                $projects = [];
                foreach ($projectList as $item) {

                    $data = Project::find($item['project_id']);
                    $projects[] = $data;
                }
                if(sizeof($projects)===0){
                    return response()->json();
                }
            }
            return response()->json($projects);
        }


    }

    public function tasks(Request $request): JsonResponse
    {
        $tasks = Task::query();
        if (!$request->has('id')) {
            if ($request->has('title')) {
                $tasks->where('title', 'like', '%' . $request->get('title') . '%');
            }
            if ($request->has('close')) {
                $tasks->where('close', 'like', '%' . $request->get('close') . '%');
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
        $return = [];
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
            return response()->json($return, 200);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return response()->json([
                'msg' => 'user deleted'
            ], 200);
        } else {
            return response()->json(['msg' => 'User not found'], 404);
        }
    }

    public function edit(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users'],
            'name' => ['required', 'min:6', 'string'],
            'roles' => ['required']
        ]);
        if ($validator->failed()) {
            return response()->json([$validator->errors()], 404);
        } else {
            $user = DB::table('users')->where('id', $id)->update($request->all());
            return response()->json($user, 200);
        }
    }

    public function total(): JsonResponse
    {
        $user = User::count();
        $doneTask = Task::where('status', \Status::DONE)->count();
        $doingTask = Task::where('status', \Status::DOING)->count();
        $projects = Project::count();
        return response()->json([
            'user' => $user,
            'doneTask' => $doneTask,
            'doingTask' => $doingTask,
            'projects' => $projects
        ], 200);
    }

    public function usersList(int $id): JsonResponse
    {
        $users = User::select('id', 'name', 'roles')->get();
        foreach ($users as $user) {
            $check = UserProjects::where('project_id', $id)->where('user_id', $user['id'])->count();
            if ($check===0) {
                $emptyUsers [] = $user;
            }
        }
        $managers = [];
        $employers = [];
        if (!empty($emptyUsers)) {
            foreach ($emptyUsers as $user) {
                if ($user['roles'] == \Roles::MANAGER) {
                    $managers[] = $user;
                }
                if ($user['roles'] === \Roles::EMPLOYER) {
                    $employers[] = $user;
                }
            }
            return response()->json([$managers, $employers], 200);

        } else {
            return response()->json(['err' => 'users not found'], 404);
        }

    }
}
