<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExerciseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exercise::query()
            ->with(['topic', 'schoolLevel'])
            ->where('is_active', true);

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->integer('topic_id'));
        }

        if ($request->filled('school_level_id')) {
            $query->where('school_level_id', $request->integer('school_level_id'));
        }

        if ($request->filled('cycle')) {
            $query->whereHas('schoolLevel', function ($q) use ($request) {
                $q->where('cycle', $request->input('cycle'));
            });
        }

        return response()->json([
            'data' => $query
                ->orderBy('difficulty')
                ->orderBy('id')
                ->get()
                ->map(fn (Exercise $exercise) => $exercise->toApiArray()),
        ]);
    }

    public function show(Exercise $exercise): JsonResponse
    {
        return response()->json([
            'data' => $exercise->load(['topic', 'schoolLevel'])->toApiArray(withCorrection: true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $exercise = Exercise::query()->create($this->validateData($request));

        return response()->json([
            'data' => $exercise->load(['topic', 'schoolLevel'])->toApiArray(withCorrection: true),
        ], 201);
    }

    public function update(Request $request, Exercise $exercise): JsonResponse
    {
        $exercise->update($this->validateData($request));

        return response()->json([
            'data' => $exercise->refresh()->load(['topic', 'schoolLevel'])->toApiArray(withCorrection: true),
        ]);
    }

    public function destroy(Exercise $exercise): JsonResponse
    {
        $exercise->update(['is_active' => false]);

        return response()->json(status: 204);
    }

    private function validateData(Request $request): array
    {
        if ($request->filled('type') && ! $request->filled('exercise_type')) {
            $request->merge([
                'exercise_type' => match ($request->input('type')) {
                    'freeText' => 'free_text',
                    default => $request->input('type'),
                },
            ]);
        }

        $validated = $request->validate([
            'topic_id' => ['required', 'integer', Rule::exists('topics', 'id')],
            'school_level_id' => ['required', 'integer', Rule::exists('school_levels', 'id')],
            'title' => ['required', 'string', 'max:120'],
            'statement' => ['required', 'string'],
            'exercise_type' => ['required', Rule::in(['qcm', 'free_text', 'calculation'])],
            'options' => ['nullable'],
            'expected_answer' => ['required', 'string'],
            'correction' => ['required', 'string'],
            'points_max' => ['required', 'integer', 'min:1'],
            'difficulty' => ['required', 'integer', 'between:1,3'],
            'chapter' => ['nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'file_url' => ['nullable', 'string'],
        ]);

        if (is_array($validated['options'] ?? null)) {
            $validated['options'] = implode('|', $validated['options']);
        }

        return $validated;
    }
}
