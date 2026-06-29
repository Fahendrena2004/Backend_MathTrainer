<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\LearningSession;
use App\Models\SchoolLevel;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            if (! extension_loaded('pdo_pgsql')) {
                $this->markTestSkipped('SQLite or PostgreSQL PDO driver is required for this integration-style test.');
            }

            putenv('DB_CONNECTION=pgsql');
            putenv('DB_HOST=127.0.0.1');
            putenv('DB_PORT=5432');
            putenv('DB_DATABASE=mathtrainer_db');
            putenv('DB_USERNAME=postgres');
            putenv('DB_PASSWORD=fafana');
        }

        parent::setUp();
    }

    public function test_attempt_store_creates_attempt_and_returns_correction(): void
    {
        $schoolLevel = SchoolLevel::create([
            'name' => 'Primaire',
            'display_order' => 1,
            'description' => 'Test school level',
        ]);

        $topic = Topic::create([
            'name' => 'Addition',
            'description' => 'Basic addition exercises',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'topic_id' => $topic->id,
            'school_level_id' => $schoolLevel->id,
            'title' => 'Add 2 + 2',
            'statement' => 'What is 2 + 2?',
            'exercise_type' => 'numeric',
            'expected_answer' => '4',
            'correction' => '2 + 2 = 4',
            'points_max' => 10,
            'difficulty' => 1,
            'is_active' => true,
            'is_new' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'student',
            'school_level_id' => $schoolLevel->id,
        ]);

        $payload = [
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'answer' => '4',
            'time_spent' => 12,
            'file_url' => 'https://example.com/answer.txt',
        ];

        $response = $this->postJson('/api/attempts', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'answer',
                'score',
                'success',
                'time_spent',
                'file_url',
                'exercise',
            ],
            'correction',
            'points_max',
        ]);
        $response->assertJson([
            'points_max' => 10,
            'correction' => '2 + 2 = 4',
        ]);

        $this->assertDatabaseHas('attempts', [
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'answer' => '4',
            'score' => 10,
            'success' => true,
            'file_url' => 'https://example.com/answer.txt',
        ]);
    }

    public function test_attempt_store_returns_not_found_for_foreign_learning_session(): void
    {
        $schoolLevel = SchoolLevel::create([
            'name' => 'Primaire',
            'display_order' => 1,
            'description' => 'Test school level',
        ]);

        $topic = Topic::create([
            'name' => 'Addition',
            'description' => 'Basic addition exercises',
            'display_order' => 1,
            'is_active' => true,
        ]);

        $exercise = Exercise::create([
            'topic_id' => $topic->id,
            'school_level_id' => $schoolLevel->id,
            'title' => 'Add 2 + 2',
            'statement' => 'What is 2 + 2?',
            'exercise_type' => 'numeric',
            'expected_answer' => '4',
            'correction' => '2 + 2 = 4',
            'points_max' => 10,
            'difficulty' => 1,
            'is_active' => true,
            'is_new' => false,
        ]);

        $user = User::factory()->create([
            'role' => 'student',
            'school_level_id' => $schoolLevel->id,
        ]);

        $otherUser = User::factory()->create([
            'role' => 'student',
            'school_level_id' => $schoolLevel->id,
        ]);

        $foreignSession = LearningSession::create([
            'user_id' => $otherUser->id,
            'started_at' => now(),
        ]);

        $payload = [
            'user_id' => $user->id,
            'exercise_id' => $exercise->id,
            'learning_session_id' => $foreignSession->id,
            'answer' => '4',
        ];

        $response = $this->postJson('/api/attempts', $payload);

        $response->assertStatus(404);
        $message = $response->json('message');
        $this->assertTrue(
            is_string($message) && (
                stripos($message, 'not found') !== false ||
                stripos($message, 'no query results') !== false
            ),
            'Expected a 404 with a not-found style message'
        );
    }
}
