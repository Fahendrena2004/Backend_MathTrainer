<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SchoolLevel::query()->orderBy('display_order');

        if ($request->filled('cycle')) {
            $query->where('cycle', $request->input('cycle'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $level = SchoolLevel::query()->create($this->validateData($request));

        return response()->json(['data' => $level], 201);
    }

    public function update(Request $request, SchoolLevel $level): JsonResponse
    {
        $level->update($this->validateData($request, $level->id));

        return response()->json(['data' => $level->refresh()]);
    }

    public function destroy(SchoolLevel $level): JsonResponse
    {
        $level->delete();

        return response()->json(status: 204);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:30', 'unique:school_levels,name,'.$ignoreId],
            'cycle' => ['required', 'string', 'in:Primaire,Collège,Lycée,Université'],
            'display_order' => ['required', 'integer', 'min:1', 'unique:school_levels,display_order,'.$ignoreId],
            'description' => ['nullable', 'string'],
        ]);
    }
}
