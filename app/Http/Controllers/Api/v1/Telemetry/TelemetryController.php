<?php
namespace App\Http\Controllers\Api\v1\Telemetry;
use App\Domain\TelemetryLocations\TelemetryLocation;
use App\Domain\Tasks\Task;
use Illuminate\Http\Request;
class TelemetryController {
    public function location(Request $r){
        $d=$r->validate(['lat'=>'required|numeric','lng'=>'required|numeric','accuracy'=>'nullable|numeric','task_id'=>'nullable|exists:tasks,id']);
        if(!empty($d['task_id'])){ $task=Task::findOrFail($d['task_id']); if($task->assigned_to_id!==$r->user()->id) abort(403); }
        $rec=TelemetryLocation::create(['user_id'=>$r->user()->id,'tenant_id'=>app('tenant.id'),'task_id'=>$d['task_id']??null,'lat'=>$d['lat'],'lng'=>$d['lng'],'accuracy'=>$d['accuracy']??null]);
        return response()->json($rec,201);
    }
}
