<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    Route::apiResource('accounts', AccountController::class)
        ->only(['index', 'show'])
        ->names([
            'index' => 'api.accounts.index',
            'show'  => 'api.accounts.show',
        ]);

    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('api.dashboard');
});

Route::get('/health', function () {
    return [
        'status'    => 'ok',
        'timestamp' => now()->toISOString(),
        'version'   => config('app.version', '1.0.0'),
    ];
})->name('api.health');
