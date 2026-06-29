<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\LearningSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttemptService
{
    public function createAttempt(array $data): Attempt
    {
        $user = User::query()->findOrFail($data['user_id']);
        $exercise = Exercise::query()->findOrFail($data['exercise_id']);

        if (! $user->isAdmin() && $this->hasRecentAttempt($user->id, $exercise->id)) {
            throw new \DomainException('A student cannot retry the same exercise within 1 hour.');
        }

        $score = $this->normalize($data['answer']) === $this->normalize($exercise->expected_answer)
            ? $exercise->points_max
            : 0;

        $attempt = DB::transaction(function () use ($data, $user, $exercise, $score) {
            $session = isset($data['learning_session_id'])
                ? LearningSession::query()->where('user_id', $user->id)->findOrFail($data['learning_session_id'])
                : LearningSession::query()->create([
                    'user_id' => $user->id,
                    'started_at' => now(),
                ]);

            $attempt = Attempt::query()->create([
                'user_id' => $data['user_id'],
                'exercise_id' => $data['exercise_id'],
                'learning_session_id' => $session->id,
                'answer' => trim($data['answer']),
                'score' => $score,
                'success' => $score >= ($exercise->points_max * 0.5),
                'time_spent' => $data['time_spent'] ?? null,
                'file_url' => $data['file_url'] ?? null,
                'created_at' => now(),
            ]);

            $session->increment('score_total', $attempt->score);
            $user->increment('points_total', $attempt->score);

            return $attempt->refresh();
        });

        $this->syncBadges($user);

        return $attempt;
    }

    private function hasRecentAttempt(int $userId, int $exerciseId): bool
    {
        return Attempt::query()
            ->where('user_id', $userId)
            ->where('exercise_id', $exerciseId)
            ->where('created_at', '>', now()->subHour())
            ->exists();
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function syncBadges(User $user): void
    {
        $successCount = $user->attempts()
            ->where('success', true)
            ->count();

        $badgeIds = Badge::query()
            ->where('is_active', true)
            ->where('required_success_count', '<=', $successCount)
            ->pluck('id')
            ->all();

        foreach ($badgeIds as $badgeId) {
            $user->badges()->syncWithoutDetaching([
                $badgeId => ['earned_at' => now()],
            ]);
        }
    }
}
