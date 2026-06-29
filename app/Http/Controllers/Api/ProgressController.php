<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProgressController extends Controller
{
    public function show(User $user): JsonResponse
    {
        $attempts = $user->attempts()
            ->with(['exercise.topic'])
            ->orderByDesc('created_at')
            ->get();

        $total = $attempts->count();
        $successCount = $attempts->where('success', true)->count();

        $byTopic = $attempts
            ->groupBy(fn ($attempt) => $attempt->exercise->topic_id)
            ->map(function ($items) {
                $success = $items->where('success', true)->count();
                $topic = $items->first()->exercise->topic;

                return [
                    'topic_id' => $topic->id,
                    'topic_name' => $topic->name,
                    'attempts' => $items->count(),
                    'success_count' => $success,
                    'success_rate' => $items->count() === 0 ? 0 : round($success / $items->count(), 2),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'attempts_count' => $total,
                'success_count' => $successCount,
                'success_rate' => $total === 0 ? 0 : round($successCount / $total, 2),
                'points_total' => $user->points_total,
                'by_topic' => $byTopic,
                'attempts' => $attempts,
                'sessions' => $user->learningSessions()
                    ->orderByDesc('started_at')
                    ->with('attempts')
                    ->get(),
                'badges' => $user->badges()
                    ->orderBy('required_success_count')
                    ->get(),
            ],
        ]);
    }
}
