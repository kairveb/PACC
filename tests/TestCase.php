<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Track output buffer level at the start of each test to avoid
     * leaving stray buffers open (which PHPUnit flags as risky).
     */
    protected int $initialObLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initialObLevel = ob_get_level();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->initialObLevel) {
            @ob_end_clean();
        }

        parent::tearDown();
    }
}
