<?php

use App\Http\Controllers\EmailController;

/*
|--------------------------------------------------------------------------
| API Email Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::name('email.')->prefix('/email')->group(function () {
        // Send email
        Route::post('/', [EmailController::class, 'send'])->name('send');
    });
});
