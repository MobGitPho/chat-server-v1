<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\AuthType;
use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Enums\UserRole;
use App\Enums\ProfileType;
use App\Events\Auth\ProfileCreatedEvent;
use App\Events\Auth\ProfileUpdatedEvent;
use App\Helpers\Functions;
use App\Http\Controllers\Controller;
use App\Models\{User, AdminProfile, CustomerProfile};
use App\Http\Resources\UserResource;
use App\Events\Auth\UserRegisteredEvent;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerUser(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);
        $validated['account_status'] = isset($validated['account_status'])
            ? $validated['account_status']
            : (User::count() > 0 ? AccountStatus::DISABLED->value : AccountStatus::ENABLED->value);
        $validated['auth_type'] = AuthType::EMAIL_PASSWORD->value;
        $validated['uid'] = Functions::generateUid();

        $user = User::create($validated);

        if ($user) {
            if (User::count() === 1) $user->addRole(UserRole::SUPER_ADMIN->value);

            event(new UserRegisteredEvent($user->id));

            Auth::login($user);

            return $this->sendResponse(
                [
                    'access_token' => Functions::generateAccessToken($user, $request->header('X-API-KEY'))->plainTextToken,
                    'user' => new UserResource($user->load(['profile', 'roles', 'permissions']))
                ],
                ResponseStatusCode::CREATED,
                'User registered'
            );
        } else {
            return $this->sendResponse(
                null,
                ResponseStatusCode::INTERNAL_SERVER_ERROR,
                'Registration failed',
                ResponseErrorCode::AUTH_REGISTRATION_FAILED
            );
        }
    }

    /**
     * Handle an incoming profile creation request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerProfile(Request $request): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'profile_data' => 'present|array'
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

        $user = User::find($request->input('id'));

        // Check if user already has profile
        if ($user->profile) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::CONFLICT,
                'User already has profile',
                ResponseErrorCode::AUTH_PROFILE_ALREADY_EXISTS
            );
        }

        if ($user) {
            switch ($request->input('type')) {
                case ProfileType::ADMIN->value:
                    $adminValidator = Validator::make($request->input('profile_data'), []);

                    if ($adminValidator->fails()) {
                        return $this->sendResponse(
                            $validator->errors(),
                            ResponseStatusCode::UNPROCESSABLE_ENTITY,
                            'Validation failed',
                            ResponseErrorCode::FORM_INVALID_DATA
                        );
                    }

                    $adminProfile = AdminProfile::create([]);

                    if ($adminProfile) {
                        $user->profile_id = $adminProfile->id;
                        $user->profile_type = ProfileType::ADMIN->value;
                        $user->save();
                    }
                    break;

                case ProfileType::CUSTOMER->value:
                    $customerValidator = Validator::make($request->input('profile_data'), []);

                    if ($customerValidator->fails()) {
                        return $this->sendResponse(
                            $validator->errors(),
                            ResponseStatusCode::UNPROCESSABLE_ENTITY,
                            'Validation failed',
                            ResponseErrorCode::FORM_INVALID_DATA
                        );
                    }

                    $customerProfile = CustomerProfile::create([]);

                    if ($customerProfile) {
                        $user->profile_id = $customerProfile->id;
                        $user->profile_type = ProfileType::CUSTOMER->value;
                        $user->save();
                    }
                    break;

                default:
                    // In case user type not found
                    return $this->sendResponse(
                        $request->input('type'),
                        ResponseStatusCode::NOT_FOUND,
                        'Profile type not found',
                        ResponseErrorCode::AUTH_PROFILE_TYPE_NOT_FOUND,
                    );
            }

            $user->refresh();

            if ($user->profile) {
                event(new ProfileCreatedEvent($user->id));

                return $this->sendResponse(
                    new UserResource($user->load(['profile', 'roles', 'permissions'])),
                    ResponseStatusCode::OK,
                    'User profile created with success'
                );
            }

            // If profile not found
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'Profile not found',
                ResponseErrorCode::AUTH_PROFILE_NOT_FOUND,
            );
        } else {
            // If user not found
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'User not found',
                ResponseErrorCode::AUTH_USER_NOT_FOUND,
            );
        }
    }

    /**
     * Handle an incoming profile update request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateProfile(Request $request, int $id): JsonResponse
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'profile_data' => 'present|array'
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

        $profile = null;

        switch ($request->input('type')) {
            case ProfileType::ADMIN->value:

                $profile = AdminProfile::find($id);

                if ($profile) {
                    $profile->update($request->input('profile_data'));
                }
                break;

            case ProfileType::CUSTOMER->value:

                $profile = CustomerProfile::find($id);

                if ($profile) {
                    $profile->update($request->input('profile_data'));
                }
                break;

            default:
                // In case user type not found
                return $this->sendResponse(
                    $request->input('type'),
                    ResponseStatusCode::NOT_FOUND,
                    'Profile type not found',
                    ResponseErrorCode::AUTH_PROFILE_TYPE_NOT_FOUND,
                );
        }

        if ($profile) {
            event(new ProfileUpdatedEvent($profile, $request->input('type')));

            return $this->sendResponse(
                $profile,
                ResponseStatusCode::OK,
                'User profile updated with success'
            );
        }

        // If profile not found
        return $this->sendResponse(
            null,
            ResponseStatusCode::NOT_FOUND,
            'Profile not found',
            ResponseErrorCode::AUTH_PROFILE_NOT_FOUND,
        );
    }
}
