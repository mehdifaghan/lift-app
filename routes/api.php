<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Simple health
Route::get('/healthz', fn() => response()->json(['ok'=>true]));

// Helper
$load = function (string $file) {
    $path = __DIR__ . "/{$file}";
    if (file_exists($path)) require_once $path;
};

// Try lift routes (will error if controllers missing), so guard it:
try {
    $load('api.lift.doc.php');
} catch (\Throwable $e) {
    // Fallback minimal routes to avoid 500
    Route::get('/_lift_missing', fn() => response()->json([
        'error' => 'Lift routes not loaded',
        'hint'  => $e->getMessage(),
    ], 500));
}
