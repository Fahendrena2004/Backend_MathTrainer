<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Badge::query()
                ->where('is_active', true)
                ->orderBy('required_success_count')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $badge = Badge::query()->create($this->validateData($request));

        return response()->json(['data' => $badge], 201);
    }

    public function update(Request $request, Badge $badge): JsonResponse
    {
        $badge->update($this->validateData($request, $badge->id));

        return response()->json(['data' => $badge->refresh()]);
    }

    public function destroy(Badge $badge): JsonResponse
    {
        $badge->update(['is_active' => false]);

        return response()->json(status: 204);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:badges,name,'.$ignoreId],
            'description' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'unlock_condition' => ['nullable', 'string'],
            'required_success_count' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
