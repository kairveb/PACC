<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RoomApiController extends Controller
{
    /**
     * List rooms.
     */
    public function index(): JsonResponse
    {
        abort_unless(Gate::allows('view-wards'), 403, 'You are not authorized.');

        $rooms = Room::with('ward', 'beds')->get();

        return response()->json([
            'success' => true,
            'message' => 'Rooms retrieved successfully.',
            'data' => $rooms,
        ]);
    }
}
