<?php
namespace App\Http\Controllers\Api\v1\Warehouse;
use App\Domain\InventoryItems\InventoryItem;
use App\Domain\StockTransactions\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class StockController {
    public function adjust(Request $r, InventoryItem $item){
        $d=$r->validate(['delta'=>'required|integer|not_in:0','reason'=>'nullable|string','task_id'=>'nullable|exists:tasks,id']);
        return DB::transaction(function() use($item,$d,$r){
            $item->stock += $d['delta']; $item->save();
            $tx=StockTransaction::create(['item_id'=>$item->id,'delta'=>$d['delta'],'reason'=>$d['reason']??null,'task_id'=>$d['task_id']??null,'created_by'=>$r->user()->id]);
            return response()->json(['item'=>$item,'transaction'=>$tx],201);
        });
    }
}
