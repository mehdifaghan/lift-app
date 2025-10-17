<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Domain\TenantSettings\TenantSetting;
use App\Domain\Tenants\Tenant;
use Illuminate\Http\Request;
class SettingController {
    public function show(Tenant $tenant){ return TenantSetting::firstOrCreate(['tenant_id'=>$tenant->id]); }
    public function update(Request $r, Tenant $tenant){ $d=$r->validate(['sms_provider'=>'nullable|string','sms_api_key'=>'nullable|string','sms_sender'=>'nullable|string','smtp_host'=>'nullable|string','smtp_port'=>'nullable|integer','smtp_user'=>'nullable|string','smtp_pass'=>'nullable|string','payment_gateway'=>'nullable|string','payment_key'=>'nullable|string','locale'=>'nullable|in:fa,en']); $s=TenantSetting::firstOrCreate(['tenant_id'=>$tenant->id]); $s->update($d); return $s; }
}
