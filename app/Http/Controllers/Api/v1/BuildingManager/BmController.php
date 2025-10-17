<?php
namespace App\Http\Controllers\Api\v1\BuildingManager;
use App\Domain\Invoices\Invoice;
use Illuminate\Http\Request;
class BmController {
    public function monthly(Request $r){ return response()->json(['summary'=>'monthly reports placeholder']); }
    public function invoices(Request $r){ return Invoice::orderBy('id','desc')->paginate($r->input('limit',15)); }
    public function pay(Request $r, Invoice $invoice){ return response()->json(['payment_url'=>'https://gateway.example.com/pay/'.$invoice->id]); }
}
