<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class DepartmentApiController extends Controller
{
    /**
     * List departments.
     */
    public function index(): JsonResponse
    {
        abort_unless(Gate::allows('view-appointments'), 403, 'You are not authorized.');

        $departments = Department::with('specialties')->get();

        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully.',
            'data' => $departments,
        ]);
    }

    /**
     * Show a single department.
     */
    public function show(int $id): JsonResponse
    {
        $department = Department::with('specialties', 'providers')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Department retrieved successfully.',
            'data' => $department,
        ]);
    }
}
