<?php

namespace Tests\Unit;

use App\Http\Controllers\PatientController;
use App\Services\AuditLogService;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class PatientControllerSearchTest extends TestCase
{
    public function test_search_returns_paginated_items_from_the_service(): void
    {
        $patientService = Mockery::mock(PatientService::class);
        $auditLogService = Mockery::mock(AuditLogService::class);

        $items = [
            ['id' => 1, 'first_name' => 'Ada'],
            ['id' => 2, 'first_name' => 'Grace'],
        ];

        $paginator = new LengthAwarePaginator($items, 2, 15, 1, ['path' => '/patients']);

        $patientService->shouldReceive('search')->once()->with('ada')->andReturn($paginator);

        $controller = new PatientController($patientService, $auditLogService);
        $response = $controller->search(new Request(['q' => 'ada']));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($items, $response->getData(true)['data']);
    }
}
