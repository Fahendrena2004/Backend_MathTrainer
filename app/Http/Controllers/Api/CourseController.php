<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Course::query()
            ->with('chapters')
            ->where('is_active', true);

        if ($request->filled('topic_id')) {
            $query->where('topic_id', $request->integer('topic_id'));
        }

        if ($request->filled('school_level_id')) {
            $query->where('school_level_id', $request->integer('school_level_id'));
        }

        return response()->json([
            'data' => $query
                ->orderBy('school_level_id')
                ->orderBy('topic_id')
                ->orderBy('id')
                ->get()
                ->map(fn (Course $course) => $course->toApiArray()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $course = Course::query()->create($this->validateData($request));

        return response()->json([
            'data' => $course->load('chapters')->toApiArray(),
        ], 201);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json([
            'data' => $course->load('chapters')->toApiArray(),
        ]);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $course->update($this->validateData($request));

        return response()->json([
            'data' => $course->refresh()->load('chapters')->toApiArray(),
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->update(['is_active' => false]);

        return response()->json(status: 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'topic_id' => ['required', 'integer', Rule::exists('topics', 'id')],
            'school_level_id' => ['required', 'integer', Rule::exists('school_levels', 'id')],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
