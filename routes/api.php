<?php

declare(strict_types=1);

use App\Application\Http\Controllers\Api\AuditLogController;
use App\Application\Http\Controllers\Api\AuthController;
use App\Application\Http\Controllers\Api\ChangeOrderController;
use App\Application\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    Route::get('/projects/{project}/change-orders', [ChangeOrderController::class, 'index']);
    Route::post('/projects/{project}/change-orders', [ChangeOrderController::class, 'store']);
    Route::get('/projects/{project}/change-orders/{changeOrder}', [ChangeOrderController::class, 'show']);
    Route::patch('/projects/{project}/change-orders/{changeOrder}/transition', [ChangeOrderController::class, 'transition']);

    Route::get('/projects/{project}/change-orders/{changeOrder}/audit-logs', [AuditLogController::class, 'index']);
});
