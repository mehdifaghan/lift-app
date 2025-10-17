<?php
namespace App\Http\Controllers\Api\v1\Warehouse;
use App\Domain\PartRequests\PartRequest;
use App\Domain\InventoryItems\InventoryItem;
use App\Domain\StockTransactions\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PartRequestController {
    public function index(Request $r){ return PartRequest::latest()->paginate($r->input('limit',15)); }
    public function storeFromTech(Request $r, \App\Domain\Tasks\Task $task){
        if($task->assigned_to_id!==$r->user()->id) abort(403);
        $d=$r->validate(['part_name'=>'required|string','qty'=>'required|integer|min:1','note'=>'nullable|string']);
        $pr=PartRequest::create(['task_id'=>$task->id,'user_id'=>$r->user()->id,'part_name'=>$d['part_name'],'qty'=>$d['qty'],'note'=>$d['note']??null]);
        return response()->json($pr,201);
    }
    public function updateStatus(Request $r, PartRequest $partRequest){
        $d=$r->validate(['status'=>'required|in:approved,rejected']);
        if($d['status']==='approved'){
            DB::transaction(function() use($partRequest,$r){
                $item=InventoryItem::where('name',$partRequest->part_name)->first();
                if($item){ if($item->stock<$partRequest->qty) abort(422,'Insufficient stock'); $item->stock-=$partRequest->qty; $item->save();
                    StockTransaction::create(['item_id'=>$item->id,'delta'=>-$partRequest->qty,'reason'=>'part_request','task_id'=>$partRequest->task_id,'created_by'=>$r->user()->id]);
                }
                $partRequest->status='approved'; $partRequest->save();
            });
        }else{ $partRequest->status='rejected'; $partRequest->save(); }
        return $partRequest;
    }
}
