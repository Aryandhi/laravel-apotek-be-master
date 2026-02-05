<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::query()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $units->map(fn ($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'code' => $unit->code,
            ]),
        ]);
    }
}
