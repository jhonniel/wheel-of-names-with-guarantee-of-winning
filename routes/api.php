<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WheelController;

Route::get('/wheel', [WheelController::class, 'index']);
Route::post('/spin', [WheelController::class, 'spin']);
Route::post('/config', [WheelController::class, 'upsertConfig']);


