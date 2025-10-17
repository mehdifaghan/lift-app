<?php
namespace App\Http\Controllers\Api\v1\Timesheet;
use App\Domain\TimesheetEntries\TimesheetEntry;
use Illuminate\Http\Request;
class TimesheetController {
    public function clockIn(Request $r){ TimesheetEntry::where('user_id',$r->user()->id)->whereNull('ended_at')->update(['ended_at'=>now()]); $rec=TimesheetEntry::create(['user_id'=>$r->user()->id,'tenant_id'=>app('tenant.id'),'started_at'=>now(),'task_id'=>$r->input('task_id')]); return response()->json($rec,201); }
    public function clockOut(Request $r){ $open=TimesheetEntry::where('user_id',$r->user()->id)->whereNull('ended_at')->latest()->first(); if(!$open) return response()->json(['message'=>'No open entry'],422); $open->ended_at=now(); $open->save(); return $open; }
    public function me(Request $r){ return TimesheetEntry::where('user_id',$r->user()->id)->orderBy('started_at','desc')->paginate($r->input('limit',15)); }
}
