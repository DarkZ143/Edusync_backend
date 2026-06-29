<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::put('/students/{id}', [StudentController::class, 'update']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
    Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    Route::post('/login', [StudentController::class, 'login']);
    Route::get('/admin/notifications', [StudentController::class, 'notifications']);
    Route::get('/admin/dashboard', [StudentController::class, 'dashboard']);

    Route::get(
        '/admin/activity',
        [StudentController::class, 'allActivity']
    );

    Route::put(
        '/admin/notifications/{id}/read',
        [StudentController::class, 'markNotificationRead']
    );
});
Route::get(
    '/students/{id}/activity',
    [StudentController::class, 'activity']
);
Route::post('/admin/login', [StudentController::class, 'adminLogin']);
Route::post(
    '/admin/logout',
    [StudentController::class, 'adminLogout']
)->middleware('auth:sanctum');

Route::post(
    '/student/change-password',
    [StudentController::class, 'changePassword']
)->middleware('auth:sanctum');
