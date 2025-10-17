<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/*
 * موقت برای اجرای migrate بدون SSH
 * حتماً بعد از اجرا، این Route را حذف کنید.
 */
Route::get('/migrate', function () {
    // ✅ محافظت‌ها:
    // 1) توکن مخفی از .env
    $token = request('token');
    if (!$token || !hash_equals($token, (string) config('app.migrate_secret', env('MIGRATE_SECRET')))) {
        abort(403, 'Forbidden');
    }

    // 2) محدودیت IP (اختیاری — اگر IP خودت را می‌دانی، این آرایه را پر کن)
    $allowedIps = [
        // '1.2.3.4', // ← IP خودت را اینجا بگذار
        '127.0.0.1',
        '::1',
    ];
    if (!empty($allowedIps) && !in_array(request()->ip(), $allowedIps, true)) {
        abort(403, 'Forbidden IP');
    }

    // 3) قفل یک‌بار مصرف (از اجرای تکراری جلوگیری می‌کند)
    $lock = Cache::lock('one_time_migrate_lock', 300); // 5 دقیقه
    if (!$lock->get()) {
        abort(429, 'Migration already in progress or recently executed');
    }

    try {
        // اجرای مایگریشن‌ها
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response("<h3>✅ Migration done!</h3><pre>".e($output)."</pre>", 200)
            ->header('Content-Type', 'text/html');
    } finally {
        optional($lock)->release();
    }
})->middleware('throttle:1,10'); // حداکثر 1 بار در 10 دقیقه
