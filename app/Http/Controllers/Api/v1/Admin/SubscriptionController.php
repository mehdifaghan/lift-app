<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Domain\Subscriptions\Subscription;
use Illuminate\Http\Request;
class SubscriptionController {
    public function index(Request $r){ return Subscription::paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['tenant_id'=>'required|exists:tenants,id','plan_id'=>'required|exists:plans,id','started_at'=>'required|date','expires_at'=>'nullable|date|after_or_equal:started_at','status'=>'in:active,expired,canceled','auto_renew'=>'boolean']); return response()->json(Subscription::create($d),201); }
    public function show(Subscription $subscription){ return $subscription; }
    public function update(Request $r, Subscription $subscription){ $d=$r->validate(['plan_id'=>'sometimes|exists:plans,id','expires_at'=>'nullable|date|after_or_equal:started_at','status'=>'in:active,expired,canceled','auto_renew'=>'boolean']); $subscription->update($d); return $subscription; }
    public function destroy(Subscription $subscription){ $subscription->delete(); return response()->noContent(); }
}
