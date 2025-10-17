<?php
namespace App\Http\Controllers\Api\v1\Manager;
use App\Domain\Elevators\Elevator;
use Illuminate\Http\Request;
class ElevatorController {
    public function index(Request $r){ return Elevator::with('building')->paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['building_id'=>'required|exists:buildings,id','code'=>'required|max:255','brand'=>'nullable','capacity'=>'nullable|integer']); return response()->json(Elevator::create($d),201); }
    public function show(Elevator $e){ return $e; }
    public function update(Request $r, Elevator $e){ $d=$r->validate(['code'=>'sometimes|max:255','brand'=>'nullable','capacity'=>'nullable|integer']); $e->update($d); return $e; }
    public function destroy(Elevator $e){ $e->delete(); return response()->noContent(); }
}
