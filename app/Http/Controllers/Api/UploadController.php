<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,svg,pdf,doc,docx,txt', 'max:10240'], // 10MB max
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads', 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'url' => $url,
                'name' => $request->file('file')->getClientOriginalName(),
                'type' => $request->file('file')->getMimeType(),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
