<?php

use App\Http\Controllers\DatabaseController;

/*
|--------------------------------------------------------------------------
| API Database Routes
|--------------------------------------------------------------------------
*/

Route::name('database.')->prefix('/database')->group(function () {
    Route::get('/', [DatabaseController::class, 'check'])->name('check');
    Route::get('/create', [DatabaseController::class, 'create'])->name('create');
    Route::post('/create', [DatabaseController::class, 'create'])->name('create.post');
    Route::get('/migrate', [DatabaseController::class, 'migrate'])->name('migrate');
    Route::get('/seed', [DatabaseController::class, 'seed'])->name('seed');
});
