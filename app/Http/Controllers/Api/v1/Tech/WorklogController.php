<?php
namespace App\Http\Controllers\Api\v1\Tech;
use App\Domain\Tasks\Task;
use App\Domain\Tasks\TaskWorklog;
use Illuminate\Http\Request;
class WorklogController {
    public function index(Request $r){ return TaskWorklog::where('user_id',$r->user()->id)->latest()->paginate($r->input('limit',15)); }
    public function store(Request $r, Task $task){ if($task->assigned_to_id!==$r->user()->id) abort(403); $d=$r->validate(['description'=>'required|string','duration_min'=>'required|integer|min:1']); $log=TaskWorklog::create(['task_id'=>$task->id,'user_id'=>$r->user()->id,'description'=>$d['description'],'duration_min'=>$d['duration_min']]); return response()->json($log,201); }
}
