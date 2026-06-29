<?php

namespace Tests\Feature;

use Tests\TestCase;

class AttemptApiTest extends TestCase
{
    public function test_attempt_store_returns_validation_errors_for_missing_fields(): void
    {
        $response = $this->postJson('/api/attempts', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'exercise_id', 'answer']);
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'user_id',
                'exercise_id',
                'answer',
            ],
        ]);
    }
}
