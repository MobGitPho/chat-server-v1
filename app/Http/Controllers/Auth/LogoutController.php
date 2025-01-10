<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Enums\ResponseStatusCode;
use App\Events\Auth\UserLoggedOutEvent;

class LogoutController extends Controller
{
    /**
     * Destroy an authenticated session.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        event(new UserLoggedOutEvent($request->user()->id));

        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Logged out successfully'
        );
    }
}
