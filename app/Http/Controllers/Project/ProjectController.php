<?php


namespace App\Http\Controllers\Project;


use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
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
        $project = $request->has('id')
            ? Project::find($request->id)
            : Project::all();
        return response()->json($project);
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
            return response()->json($project);
        }
    }

    public function update(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3'],
            'id' => ['required']
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            $project = Project::where('id', $request->id)->update($request->all());
            return response()->json(Project::find($request->id));
        }
    }

    public function tasks(Request $request): JsonResponse
    {
        $task = $request->has('pr_id')
            ? Task::where('project_id', $request->id)->get()
            : DB::table('tasks')->leftJoin('projects','tasks.project_id','=','projects.id')->select('tasks.*','projects.*')->get();
        return response()->json($task);
    }
}
