<?php
namespace App\Http\Controllers\Api\v1\Billing;
use App\Domain\Invoices\Invoice;
use Illuminate\Http\Request;
class InvoiceController {
    public function index(Request $r){ $q=Invoice::query(); if($s=$r->query('status')) $q->where('status',$s); return $q->orderBy('id','desc')->paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['building_id'=>'nullable|exists:buildings,id','total_cents'=>'required|integer|min:0']); return response()->json(Invoice::create($d),201); }
    public function update(Request $r, Invoice $invoice){ $d=$r->validate(['status'=>'required|in:unpaid,paid,canceled']); $invoice->update($d); return $invoice; }
}
