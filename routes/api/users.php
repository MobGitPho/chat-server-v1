<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Users Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::name('user.')->prefix('/user')->group(function () {
        Route::get('/permissions/{user?}', [UserController::class, 'userPermissions'])->name('permissions');

        Route::get('/sessions/{user?}', [UserController::class, 'userSessions'])->name('sessions');

        Route::get('/roles/{user?}', [UserController::class, 'userRoles'])->name('roles');

        Route::get('/email/{email}', [UserController::class, 'userByEmail'])->name('by-email');

        Route::get('/phone/{phone}', [UserController::class, 'userByPhone'])->name('by-phone');
    });

    Route::name('users.')->prefix('/users')->group(function () {
        Route::put('/self/{user}', [UserController::class, 'updateSelf'])->name('update.self');

        Route::middleware(['permission:action-add-user'])->post('/', [UserController::class, 'store'])->name('store');

        Route::middleware(['permission:action-edit-user'])->put('/{user}', [UserController::class, 'update'])->name('update');

        Route::middleware(['permission:action-delete-user'])->delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::apiResource('users', UserController::class)->only(['index', 'show']);
    Route::post('users/list', [UserController::class, 'showList'])->name('users.list');
    Route::get('users/search/{query?}', [UserController::class, 'searchUsers'])->name('users.search');

    Route::apiResource('user-sessions', UserSessionController::class)->except(['store', 'update']);
});

Route::get('/username/phone/{phone}', [UserController::class, 'usernameByPhone'])->name('username-by-phone');
