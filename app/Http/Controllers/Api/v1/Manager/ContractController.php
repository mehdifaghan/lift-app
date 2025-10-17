<?php
namespace App\Http\Controllers\Api\v1\Manager;
use App\Domain\Contracts\Contract;
use Illuminate\Http\Request;
class ContractController {
    public function index(Request $r){ return Contract::paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['building_id'=>'required|exists:buildings,id','title'=>'required|max:255','start_date'=>'required|date','end_date'=>'nullable|date|after_or_equal:start_date','price_cents'=>'required|integer|min:0']); return response()->json(Contract::create($d),201); }
    public function show(Contract $c){ return $c; }
    public function update(Request $r, Contract $c){ $d=$r->validate(['title'=>'sometimes|max:255','start_date'=>'sometimes|date','end_date'=>'nullable|date|after_or_equal:start_date','price_cents'=>'sometimes|integer|min:0']); $c->update($d); return $c; }
    public function destroy(Contract $c){ $c->delete(); return response()->noContent(); }
}
