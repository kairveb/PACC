<?php

namespace Tests\Feature;

use Tests\TestCase;

class PhilippineAddressDatasetTest extends TestCase
{
    public function test_it_returns_psgc_structured_address_data(): void
    {
        $response = $this->getJson('/api/v1/address-data/philippines');

        $response->assertOk();

        $provinces = $response->json('data.provinces');

        $this->assertIsArray($provinces);
        $this->assertGreaterThanOrEqual(80, count($provinces));
        $this->assertNotEmpty($provinces[0]['code']);
        $this->assertNotEmpty($provinces[0]['cities']);
        $this->assertNotEmpty($provinces[0]['cities'][0]['barangays']);
    }
}
