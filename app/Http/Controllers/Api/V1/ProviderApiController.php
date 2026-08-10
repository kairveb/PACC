<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProviderApiController extends Controller
{
    /**
     * List providers.
     */
    public function index(): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $providers = Provider::with('department', 'specialties')->get();

        return response()->json([
            'success' => true,
            'message' => 'Providers retrieved successfully.',
            'data' => $providers,
        ]);
    }

    /**
     * Show a single provider.
     */
    public function show(int $id): JsonResponse
    {
        $provider = Provider::with('department', 'specialties', 'schedules')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Provider retrieved successfully.',
            'data' => $provider,
        ]);
    }
}
