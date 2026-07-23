<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CreneauController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin')->group(function () {
        // routes reservees a l'admin, a completer plus tard
    });

    Route::middleware('role:medecin')->group(function () {
        Route::post('/creneaux', [CreneauController::class, 'store']);
        Route::get('/mes-creneaux', [CreneauController::class, 'mesCreneaux']);
        Route::delete('/creneaux/{creneau}', [CreneauController::class, 'destroy']);
    });

    Route::middleware('role:patient')->group(function () {
        // routes reservees au patient, a completer plus tard
    });
});

Route::get('/medecins/{medecinProfileId}/creneaux-disponibles', [CreneauController::class, 'creneauxDisponibles']);