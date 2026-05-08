<?php

namespace App\Http\Controllers;

use App\Models\Joke;
use Illuminate\Http\JsonResponse;

class JokeController extends Controller
{
    public function index(): JsonResponse
    {
        $jokes = Joke::all();
        return response()->json($jokes);
    }
}
