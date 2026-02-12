<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('amount')
            ->get();

        return response()->json([
            'data' => $plans,
        ]);
    }
}
