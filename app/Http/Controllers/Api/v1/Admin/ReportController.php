<?php
namespace App\Http\Controllers\Api\v1\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ReportController {
    public function revenue(Request $r){ $from=$r->query('from'); $to=$r->query('to'); $q=DB::table('payments')->selectRaw('DATE(created_at) d, SUM(amount_cents) sum')->groupBy('d')->orderBy('d'); if($from) $q->whereDate('created_at','>=',$from); if($to) $q->whereDate('created_at','<=',$to); return $q->get(); }
    public function expiring(Request $r){ $days=(int)($r->query('days',30)); return DB::table('contracts')->whereBetween('end_date',[now(), now()->addDays($days)])->get(); }
}
