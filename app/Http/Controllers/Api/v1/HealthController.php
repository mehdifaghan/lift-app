<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;

class HealthController
{
    public function index(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }
}
