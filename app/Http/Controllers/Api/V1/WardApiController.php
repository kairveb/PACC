<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ward;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class WardApiController extends Controller
{
    /**
     * List wards with room and bed counts.
     */
    public function index(): JsonResponse
    {
        abort_unless(Gate::allows('view-wards'), 403, 'You are not authorized.');

$wards = Ward::withCount('rooms')
            ->with(['rooms' => fn ($q) => $q->withCount('beds')])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Wards retrieved successfully.',
            'data' => $wards,
        ]);
    }
}
