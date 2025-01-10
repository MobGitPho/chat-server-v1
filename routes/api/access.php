<?php

use App\Http\Controllers\Access\PermissionController;
use App\Http\Controllers\Access\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Access Stuffs Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Roles
    Route::apiResource('roles', RoleController::class)->except(['index']);
    Route::get('roles/user/{user?}', [RoleController::class, 'userIndex'])->name('roles.user.index');

    // Permissions
    Route::apiResource('permissions', PermissionController::class)->except(['index']);
    Route::get('permissions/user/{user?}', [PermissionController::class, 'userIndex'])->name('permissions.user.index');
});

Route::apiResource('roles', RoleController::class)->only(['index']);
Route::apiResource('permissions', PermissionController::class)->only(['index']);
