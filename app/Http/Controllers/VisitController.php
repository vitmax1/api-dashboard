<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => 'nullable|string|max:255',
            'device' => 'required|string|in:Desktop,Mobile',
            'payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $ip = $request->ip();

        Visit::create([
            'ip' => $ip,
            'city' => $request->input('city'),
            'device' => $request->input('device'),
            'payload' => $request->input('payload'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visit recorded successfully',
        ], 201);
    }
}
