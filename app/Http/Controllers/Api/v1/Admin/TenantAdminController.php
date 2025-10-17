<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Domain\Tenants\Tenant;
use Illuminate\Http\Request;
class TenantAdminController {
    public function index(Request $r){ return Tenant::orderBy('id','desc')->paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['name'=>'required|string','is_active'=>'boolean']); return response()->json(Tenant::create($d),201); }
    public function show(Tenant $tenant){ return $tenant; }
    public function update(Request $r, Tenant $tenant){ $d=$r->validate(['name'=>'sometimes|string','is_active'=>'boolean']); $tenant->update($d); return $tenant; }
    public function destroy(Tenant $tenant){ $tenant->delete(); return response()->noContent(); }
    public function stats(Tenant $tenant){ return response()->json(['id'=>$tenant->id,'name'=>$tenant->name,'users'=>\App\Models\User::where('tenant_id',$tenant->id)->count(),'buildings'=>\App\Domain\Buildings\Building::where('tenant_id',$tenant->id)->count(),'tasks'=>\App\Domain\Tasks\Task::where('tenant_id',$tenant->id)->count()]); }
}
