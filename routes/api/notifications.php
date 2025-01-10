<?php

use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Notifications Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::name('notifications.')->prefix('/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'userIndex'])
            ->name('user.index');

        Route::get('/unread', [NotificationController::class, 'userIndexUnread'])
            ->name('user.index-unread');

        Route::get('/read', [NotificationController::class, 'userIndexRead'])
            ->name('user.index-read');

        Route::put('/mark/{notification}', [NotificationController::class, 'mark'])
            ->name('mark');

        Route::put('/mark-all', [NotificationController::class, 'userMarkAll'])
            ->name('user.mark-all');

        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
            ->name('destroy');
    });
});
