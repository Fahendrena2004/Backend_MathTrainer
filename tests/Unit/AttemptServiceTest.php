<?php

namespace Tests\Unit;

use App\Services\AttemptService;
use Tests\TestCase;

class AttemptServiceTest extends TestCase
{
    public function test_service_resolvable()
    {
        // Minimal smoke test: ensure the service can be resolved via DI without touching the DB
        $service = $this->app->make(AttemptService::class);
        $this->assertInstanceOf(AttemptService::class, $service);
    }
}
