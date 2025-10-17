<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Domain\Plans\Plan;
use Illuminate\Http\Request;
class PlanController {
    public function index(Request $r){ return Plan::paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['code'=>'required|string|unique:plans,code','name'=>'required|string','period_months'=>'required|integer|min:1','price_cents'=>'required|integer|min:0','is_active'=>'boolean']); return response()->json(Plan::create($d),201); }
    public function show(Plan $plan){ return $plan; }
    public function update(Request $r, Plan $plan){ $d=$r->validate(['name'=>'sometimes|string','period_months'=>'sometimes|integer|min:1','price_cents'=>'sometimes|integer|min:0','is_active'=>'boolean']); $plan->update($d); return $plan; }
    public function destroy(Plan $plan){ $plan->delete(); return response()->noContent(); }
}
