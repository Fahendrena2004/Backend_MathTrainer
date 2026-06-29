<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Topic::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $topic = Topic::query()->create($this->validateData($request));

        return response()->json(['data' => $topic], 201);
    }

    public function update(Request $request, Topic $topic): JsonResponse
    {
        $topic->update($this->validateData($request, $topic->id));

        return response()->json(['data' => $topic->refresh()]);
    }

    public function destroy(Topic $topic): JsonResponse
    {
        $topic->update(['is_active' => false]);

        return response()->json(status: 204);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:topics,name,'.$ignoreId],
            'description' => ['required', 'string'],
            'display_order' => ['sometimes', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
