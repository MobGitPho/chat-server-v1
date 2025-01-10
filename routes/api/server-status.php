<?php

use App\Http\Controllers\ServerStatusController;

/*
|--------------------------------------------------------------------------
| API Server Status Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ServerStatusController::class, 'check'])->name('root');
