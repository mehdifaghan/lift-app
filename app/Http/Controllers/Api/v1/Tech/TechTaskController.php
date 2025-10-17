<?php
namespace App\Http\Controllers\Api\v1\Tech;
use App\Domain\Tasks\Task;
use Illuminate\Http\Request;
class TechTaskController {
    public function index(Request $r){ return Task::where('assigned_to_id',$r->user()->id)->paginate($r->input('limit',15)); }
    public function updateStatus(Request $r, Task $task){ if($task->assigned_to_id!==$r->user()->id) abort(403); $d=$r->validate(['status'=>'required|in:pending,in_progress,done,canceled']); $task->status=$d['status']; $task->save(); return $task; }
}
