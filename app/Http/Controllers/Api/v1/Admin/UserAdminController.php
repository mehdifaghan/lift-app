<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class UserAdminController {
    public function index(Request $r){ return User::orderBy('id','desc')->paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['tenant_id'=>'nullable|exists:tenants,id','name'=>'required|string','email'=>'required|email|unique:users,email','password'=>'required|min:8','role'=>'nullable|string','system_role'=>'nullable|string']); $d['password']=Hash::make($d['password']); return response()->json(User::create($d),201); }
    public function show(User $user){ return $user; }
    public function update(Request $r, User $user){ $d=$r->validate(['name'=>'sometimes|string','email'=>'sometimes|email|unique:users,email,'.$user->id,'role'=>'nullable|string','system_role'=>'nullable|string','is_active'=>'sometimes|boolean']); $user->update($d); return $user; }
    public function destroy(User $user){ $user->delete(); return response()->noContent(); }
}
