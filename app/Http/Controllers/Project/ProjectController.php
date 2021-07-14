<?php


namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
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

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): JsonResponse
    {
        $project = Project::query();

        if (!$request->has('id')) {
            if ($request->has('name')) {
                $project->where('name', 'like', '%' . $request->get('name') . '%');
            }

            return response()->json($project->get());
        } else {
            return response()->json(Project::find($request->id));
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $request['user_id'] = Auth::id();
            $project = Project::create($request->all());
            $projectRelation = UserProjects::create([
                'user_id' => $request['user_id'],
                'project_id' => $project['id']
            ]);
            return response()->json([$project, $projectRelation], 200);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            Project::where('id', $id)->update($request->all());
            return response()->json(Project::find($id));
        }
    }

    public function tasks(Request $request): JsonResponse
    {
        $task = $request->has('id')
            ? Task::where('project_id', $request->id)->get()
            : DB::table('tasks')
                ->leftJoin('projects', 'tasks.project_id', '=', 'projects.id')
                ->select('tasks.*', 'projects.*')
                ->distinct()
                ->get();
        return response()->json($task, 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $project = Project::findOrFail($id);
        $tasks = $project->tasks;
        // return response()->json($tasks);
        if ($tasks) {
            $project->delete();
            DB::table('tasks')->where('project_id', $id)->delete();
            DB::table('user_tasks')->where('project_id')->delete();
            return response()->json(['msg' => 'deleted'], 200);
        } else {
            return response()->json(['error' => 'this project not removed,because this project has tasks'], 404);
        }
    }

    public function addUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer'],
            'user_id' => ['required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $project = Project::find($request['project_id']);
            if (!$project) {
                return response()->json(['err' => 'project not found'], 404);
            }
            if (sizeof($request['user_id']) !== 0) {
                foreach ($request['user_id'] as $item) {
                    $user = User::find($item);
                    if (!$user) {
                        return response()->json(['err' => 'user not found'], 404);
                    }
                    $check = UserProjects::where('user_id',$item)->where('project_id',$request['project_id'])->count();
                    if($check === 0){
                        $done = UserProjects::create([
                            'user_id' => $item,
                            'project_id' => $request['project_id']
                        ]);
                    }else{
                        $done = UserProjects::update([
                            'user_id' => $item,
                            'project_id' => $request['project_id']
                        ]);
                    }

                    $return[] = $done;
                }
            } else {
                return response()->json(['err' => 'user ids is empty']);
            }
            return response()->json($return);
        }
    }

    public function allUser(int $id): JsonResponse
    {
        $emptyUsers = [];
        $task = Task::find($id);
        $prId = $task['project_id'];
        $users = User::select('id', 'name', 'roles')->get();
        foreach ($users as $user) {
            $check = UserProjects::where('project_id', $prId)->where('user_id', $user['id'])->count();
            if ($check === 1) {
                $taskCheck = UserTasks::where('user_id', $user['id'])->where('task_id', $id)->where('project_id', $prId)->count();

                if ($taskCheck === 0) {
                    $emptyUsers [] = $user;
                }
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
            return response()->json(['msg' => 'data not found']);
        }
    }
}
