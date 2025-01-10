<?php

use App\Http\Controllers\AppSettingController;

/*
|--------------------------------------------------------------------------
| API Settings Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/app-settings')->group(function () {
    Route::get('/', [AppSettingController::class, 'index'])->name('app-settings.index');

    Route::put('/', [AppSettingController::class, 'update'])->name('app-settings.update');
});
