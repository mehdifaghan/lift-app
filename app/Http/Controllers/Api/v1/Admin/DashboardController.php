<?php
namespace App\Http\Controllers\Api\v1\Admin;
use Illuminate\Support\Facades\DB;
class DashboardController { public function summary(){ return ['tenants'=>DB::table('tenants')->count(),'users'=>DB::table('users')->count(),'tasks'=>DB::table('tasks')->count(),'revenue_cents'=>DB::table('payments')->sum('amount_cents')]; } }
