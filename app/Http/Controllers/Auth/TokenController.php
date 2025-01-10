<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Helpers\Functions;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class TokenController extends Controller
{
    public function index(Request $request, string $name): JsonResponse
    {
        if ($name === 'refresh-token' || $name === 'access-token') {
            return $this->sendResponse(
                null,
                ResponseStatusCode::FORBIDDEN,
                'Access and refresh tokens can not be retrieved'
            );
        }

        $tokens = $request->user()->tokens()->where('name', $name)->get();

        return $this->sendResponse($tokens);
    }

    public function create(Request $request, string $name): JsonResponse
    {
        $token = $request->user()->createToken($name);

        return $this->sendResponse(['token' => $token->plainTextToken]);
    }

    public function revoke(Request $request, int $id): JsonResponse
    {
        $token = $request->user()->tokens()->find($id);

        if ($token) {
            // Revoke the token
            $token->delete();

            return $this->sendResponse(
                null,
                ResponseStatusCode::OK,
                'Token revoked successfully'
            );
        } else {
            // Token not found
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'Token not found'
            );
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        // For security reasons, the refresh token is taken directly from the database
        // and is not exposed to the client

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
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

        $user = User::find($request->input('user_id'));

        if ($user) {
            // Find the refresh token by name
            $refreshToken = $user->tokens()->where('name', 'refresh-token')->first();

            if ($refreshToken) {
                $token = $request->bearerToken() ?? $request->input('expired_access_token') ?? 'token';
                $expiredAccessToken = PersonalAccessToken::findToken($token);

                if ($expiredAccessToken) {

                    $expiredAccessToken->delete();
                } else {

                    return $this->sendResponse(
                        null,
                        ResponseStatusCode::NOT_FOUND,
                        'Expired access token not found'
                    );
                }

                Functions::generateRefreshToken($user);

                return $this->sendResponse(
                    [
                        'access_token' => Functions::generateAccessToken($user, $request->header('X-API-KEY'))->plainTextToken,
                        'user' => new UserResource($user->load(['profile', 'roles', 'permissions'])),
                    ],
                    ResponseStatusCode::OK,
                    'New access token generated successfully'
                );
            } else {
                // Refresh token not found
                return $this->sendResponse(
                    null,
                    ResponseStatusCode::NOT_FOUND,
                    'Refresh token not found'
                );
            }
        } else {
            // User not found
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'User not found'
            );
        }
    }
}
