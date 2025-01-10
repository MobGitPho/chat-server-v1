<?php

use App\Http\Controllers\Auth\EmailChangeController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ProviderController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\TokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Auth Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('guest')
        ->name('login');

    Route::post('/register', [RegistrationController::class, 'registerUser'])
        ->middleware('guest')
        ->name('register');

    Route::post('/google', [ProviderController::class, 'authWithGoogle'])
        ->middleware('guest')
        ->name('provider.google');

    Route::post('/phone', [ProviderController::class, 'authWithPhone'])
        ->middleware('guest')
        ->name('provider.phone');

    Route::post('/phone/verify', [ProviderController::class, 'verifyPhoneAuth'])
        ->middleware('guest')
        ->name('provider.phone.verify');

    Route::post('/profile', [RegistrationController::class, 'registerProfile'])
        ->middleware('auth:sanctum')
        ->name('profile.register');

    Route::put('/profile/{id}', [RegistrationController::class, 'updateProfile'])
        ->middleware('auth:sanctum')
        ->name('profile.update');

    Route::post('/send-password-link', [PasswordResetController::class, 'send'])
        ->middleware('guest')
        ->name('password.send.link');

    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('guest')
        ->name('password.reset.link');

    Route::post('/edit-password', [PasswordResetController::class, 'edit'])
        ->middleware('auth:sanctum')
        ->name('password.edit');

    Route::post('/edit-email', EmailChangeController::class)
        ->middleware('auth:sanctum')
        ->name('email.edit');

    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.verify.email');

    Route::get('/send-verification-email', [EmailVerificationController::class, 'send'])
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.send.email');

    Route::get('/logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('logout');

    Route::name('tokens.')->prefix('/tokens')->group(function () {
        Route::get('/{name}', [TokenController::class, 'index'])
            ->middleware('auth:sanctum')
            ->name('index');

        Route::get('/create/{name}', [TokenController::class, 'create'])
            ->middleware('auth:sanctum')
            ->name('create');

        Route::post('/revoke/{id}', [TokenController::class, 'revoke'])
            ->middleware('auth:sanctum')
            ->name('revoke');

        Route::post('/refresh', [TokenController::class, 'refresh'])
            ->name('refresh');
    });
});
