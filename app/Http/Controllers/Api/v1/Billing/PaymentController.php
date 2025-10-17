<?php
namespace App\Http\Controllers\Api\v1\Billing;
use App\Domain\Payments\Payment;
use App\Domain\Invoices\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentController {
    public function index(Request $r){ return Payment::latest()->paginate($r->input('limit',15)); }
    public function store(Request $r, Invoice $invoice){
        $d=$r->validate(['amount_cents'=>'required|integer|min:1','method'=>'required|string','reference'=>'required|string']);
        return DB::transaction(function() use($invoice,$d){
            $p=Payment::create(['invoice_id'=>$invoice->id,'amount_cents'=>$d['amount_cents'],'method'=>$d['method'],'reference'=>$d['reference']]);
            $sum=Payment::where('invoice_id',$invoice->id)->sum('amount_cents');
            if($sum >= $invoice->total_cents){ $invoice->status='paid'; $invoice->save(); }
            return response()->json($p,201);
        });
    }
}
