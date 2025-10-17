<?php
namespace App\Http\Controllers\Api\v1\Manager;
use App\Domain\Tasks\Task;
use App\Models\User;
use Illuminate\Http\Request;
class TaskController {
    public function index(Request $r){ return Task::paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['building_id'=>'required|exists:buildings,id','elevator_id'=>'nullable|exists:elevators,id','title'=>'required|max:255']); $d['status']='pending'; return response()->json(Task::create($d),201); }
    public function show(Task $task){ return $task; }
    public function update(Request $r, Task $task){ $d=$r->validate(['title'=>'sometimes|max:255','status'=>'sometimes|in:pending,in_progress,done,canceled']); $task->update($d); return $task; }
    public function destroy(Task $task){ $task->delete(); return response()->noContent(); }
    public function assign(Request $r, Task $task){ $d=$r->validate(['assigned_to_id'=>'required|exists:users,id']); $u=User::findOrFail($d['assigned_to_id']); if($u->role!=='TECH') abort(422,'Assigned user must be TECH'); $task->assigned_to_id=$u->id; $task->save(); return $task; }
}
