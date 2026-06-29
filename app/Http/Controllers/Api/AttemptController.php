<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreAttemptRequest;
use App\Services\AttemptService;

class AttemptController extends Controller
{
    private AttemptService $attemptService;

    public function __construct(AttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
    }
    public function index(): JsonResponse
    {
        $attempts = Attempt::query()
            ->with(['user', 'exercise.topic'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($attempts);
    }

    public function store(StoreAttemptRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $attempt = $this->attemptService->createAttempt($validated);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $exercise = $attempt->exercise;

        return response()->json([
            'data' => $attempt->load('exercise'),
            'correction' => $exercise->correction,
            'points_max' => $exercise->points_max,
        ], 201);
    }
}
