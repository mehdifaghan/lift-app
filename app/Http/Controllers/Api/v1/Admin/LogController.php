<?php
namespace App\Http\Controllers\Api\v1\Admin;
use App\Domain\SystemLogs\SystemLog;
use Illuminate\Http\Request;
class LogController { public function index(Request $r){ $q=SystemLog::query(); if($lvl=$r->query('level')) $q->where('level',$lvl); if($tid=$r->query('tenantId')) $q->where('tenant_id',$tid); return $q->orderBy('id','desc')->paginate($r->input('limit',15)); } }
