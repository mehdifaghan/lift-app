<?php
namespace App\Http\Controllers\Api\v1;
use App\Domain\Notifications\Notification;
use Illuminate\Http\Request;
class NotificationController {
    public function index(Request $r){ $q=Notification::where('user_id',$r->user()->id); if(!is_null($r->query('isRead'))){ $isRead=filter_var($r->query('isRead'), FILTER_VALIDATE_BOOLEAN); $q->where('is_read',$isRead);} return $q->latest()->paginate($r->input('limit',15)); }
    public function markRead(Request $r, Notification $notification){ if($notification->user_id!==$r->user()->id) abort(403); $notification->is_read=true; $notification->read_at=now(); $notification->save(); return $notification; }
}
