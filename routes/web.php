<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\WheelController;
use App\Http\Controllers\WinnerController;
use Illuminate\Support\Facades\Route;

// Public wheel route - anyone can view and spin
Route::get('/', function () {
    $activeBackground = \App\Models\WheelBackground::getActive();
    $activeLogo = \App\Models\WheelLogo::getActive();
    $spinDuration = \App\Models\WheelSetting::getSpinDuration();
    $displayMode = \App\Models\WheelSetting::getDisplayMode();
    $audioEnabled = \App\Models\WheelSetting::getAudioEnabled();
    return view('wheel', compact('activeBackground', 'activeLogo', 'spinDuration', 'displayMode', 'audioEnabled'));
});

// Public API routes for wheel functionality
Route::get('/api/wheel', [WheelController::class, 'index']);
Route::post('/api/spin', [WheelController::class, 'spin']);

// Protected routes - only authenticated users can access
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Participants management (admin only)
    Route::get('/participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::post('/participants', [ParticipantController::class, 'store'])->name('participants.store');
    Route::post('/participants/batch', [ParticipantController::class, 'batchImport'])->name('participants.batch');
    Route::post('/participants/bulk-delete', [ParticipantController::class, 'bulkDelete'])->name('participants.bulk-delete');
    Route::post('/participants/{participant}/update', [ParticipantController::class, 'update'])->name('participants.update');
    Route::post('/participants/{participant}/deactivate', [ParticipantController::class, 'deactivate'])->name('participants.deactivate');
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    Route::post('/participants/{participant}/config', [ParticipantController::class, 'saveConfig'])->name('participants.config');
    Route::post('/participants/spin-duration', [ParticipantController::class, 'updateSpinDuration'])->name('participants.spin-duration');
    Route::post('/participants/display-mode', [ParticipantController::class, 'updateDisplayMode'])->name('participants.display-mode');
    Route::post('/participants/audio-setting', [ParticipantController::class, 'updateAudioSetting'])->name('participants.audio-setting');

    // Wheel settings (admin only)
    Route::post('/api/config', [WheelController::class, 'upsertConfig']);

    // Background management (admin only)
    Route::post('/participants/background', [ParticipantController::class, 'uploadBackground'])->name('participants.background.upload');
    Route::post('/participants/background/{background}/activate', [ParticipantController::class, 'activateBackground'])->name('participants.background.activate');
    Route::delete('/participants/background/{background}', [ParticipantController::class, 'deleteBackground'])->name('participants.background.delete');

    // Logo management (admin only)
    Route::post('/participants/logo/upload', [ParticipantController::class, 'uploadLogo'])->name('participants.logo.upload');
    Route::post('/participants/logo/{logo}/activate', [ParticipantController::class, 'activateLogo'])->name('participants.logo.activate');
    Route::delete('/participants/logo/{logo}', [ParticipantController::class, 'deleteLogo'])->name('participants.logo.delete');

    // Winners management (admin only)
    Route::get('/winners', [WinnerController::class, 'index'])->name('winners.index');
    Route::post('/winners', [WinnerController::class, 'store'])->name('winners.store');
    Route::delete('/winners/{winner}', [WinnerController::class, 'destroy'])->name('winners.destroy');

    // User management (admin only)
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
});

require __DIR__.'/auth.php';
