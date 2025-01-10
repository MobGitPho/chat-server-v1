<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Events\Auth\EmailVerifiedEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Email already verified',
                ResponseErrorCode::AUTH_EMAIL_ALREADY_VERIFIED
            );
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Verification email sent successfully'
        );
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {

            return $this->sendResponse(
                null,
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Email already verified',
                ResponseErrorCode::AUTH_EMAIL_ALREADY_VERIFIED
            );
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new EmailVerifiedEvent($request->user()->id));
        }

        return $this->sendResponse(
            [
                'user' => new UserResource($request->user()->load(['profile', 'roles', 'permissions'])),
            ],
            ResponseStatusCode::OK,
            'Email verified successfully'
        );
    }

    public function verifyManually(Request $request, int $id, string $hash): RedirectResponse|View
    {
        // If data not provided redirect to home
        if ($id > 0 && $hash) {

            $user = User::find($id);

            if ($user) {

                if ($user->hasVerifiedEmail()) {
                    return view('auth.verify-email', [
                        'alreadyVerified' => true
                    ]);
                }

                if (hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {

                    if ($user->markEmailAsVerified()) {

                        event(new EmailVerifiedEvent($user->id));

                        return view('auth.verify-email', [
                            'alreadyVerified' => false
                        ]);
                    }
                }
            }
        }

        return redirect()->intended();
    }
}
