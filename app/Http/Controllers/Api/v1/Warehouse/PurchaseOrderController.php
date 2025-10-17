<?php
namespace App\Http\Controllers\Api\v1\Warehouse;
use App\Domain\PurchaseOrders\PurchaseOrder;
use Illuminate\Http\Request;
class PurchaseOrderController {
    public function index(Request $r){ return PurchaseOrder::orderBy('id','desc')->paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['supplier'=>'nullable|string']); return response()->json(PurchaseOrder::create($d),201); }
}
