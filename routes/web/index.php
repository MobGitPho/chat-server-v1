<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $frontendUrl = config('app.frontend_url');

    return $frontendUrl
        ? redirect()->intended($frontendUrl)
        : view('home');
})->name('home.index');

Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verifyManually'])
    ->middleware('guest')->name('verification.verify');

Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
    ->middleware('guest')->name('password.reset');

Route::post('/reset-password', [PasswordResetController::class, 'submitResetForm'])
    ->middleware('guest')->name('password.update');
