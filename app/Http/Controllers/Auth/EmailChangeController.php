<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Http\Controllers\Controller;
use App\Mail\CommonMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class EmailChangeController extends Controller
{
    /**
     * Change email.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users'],
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                ResponseErrorCode::FORM_INVALID_DATA
            );
        }

        $user = Auth::user();
        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        Mail::to($user)->queue(new CommonMail(
            [
                'title' => __('auth.email_change'),
                'message' => __('auth.email_changed_successfully'),
            ],
        ));

        Password::sendResetLink(
            $request->only('email')
        );

        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Email changed successfully',
        );
    }
}
