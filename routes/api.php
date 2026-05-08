<?php

use App\Http\Controllers\JokeController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/jokes', [JokeController::class, 'index']);
Route::post('/track', [VisitController::class, 'store']);
