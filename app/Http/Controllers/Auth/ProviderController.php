<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\AuthType;
use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Enums\UserRole;
use App\Events\Auth\UserLoggedInEvent;
use App\Events\Auth\UserRegisteredEvent;
use App\Helpers\Functions;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Str;
use Twilio\Rest\Client as TwilioClient;

class ProviderController extends Controller
{
    /**
     * Handle an incoming google auth request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authWithGoogle(Request $request): JsonResponse
    {
        $googleConfig = config('services.google');

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes',
            'credential' => 'required',
            'preferences' => 'sometimes|array'
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

        $google = new GoogleClient(['client_id' => $googleConfig['client_id']]);
        $google->addScope('email');

        $payload = $google->verifyIdToken($request->input('credential'));

        if ($payload) {
            $userFound = User::whereEmail($payload['email'])->first();

            if ($userFound) {
                if ($userFound->account_status == AccountStatus::DISABLED->value) {
                    // When the user is disabled
                    return $this->sendResponse(
                        null,
                        ResponseStatusCode::FORBIDDEN,
                        'User disabled',
                        ResponseErrorCode::AUTH_USER_DISABLED,
                    );
                }

                Auth::login($userFound);

                event(new UserLoggedInEvent($userFound->id));

                return $this->sendResponse(
                    [
                        'access_token' => Functions::generateAccessToken($userFound, $request->header('X-API-KEY'))->plainTextToken,
                        'user' => new UserResource($userFound->load(['profile', 'roles', 'permissions'])),
                    ],
                    ResponseStatusCode::OK,
                    'Logged in successfully'
                );
            } else {
                $user = User::create([
                    'firstname' => $payload['given_name'] ?? $payload['name'] ?? '',
                    'lastname' => $payload['family_name'] ?? '',
                    'avatar' => $payload['picture'],
                    'email' => $payload['email'],
                    'password' => Hash::make(Str::password(8, true, true, false)),
                    'uid' => Functions::generateUid(),
                    'account_status' => $request->input('account_status')
                        ? $request->input('account_status')
                        : (User::count() > 0 ? AccountStatus::DISABLED->value : AccountStatus::ENABLED->value),
                    'auth_type' => AuthType::GOOGLE->value,
                    'preferences' => $request->input('preferences')
                ]);

                if ($user) {
                    if ($payload['email_verified']) {
                        $user->markEmailAsVerified();
                        $user->save();

                        $user->refresh();
                    }

                    if (User::count() === 1) $user->addRole(UserRole::SUPER_ADMIN->value);

                    event(new UserRegisteredEvent($user->id));

                    Auth::login($user);

                    return $this->sendResponse(
                        [
                            'access_token' => Functions::generateAccessToken($user, $request->header('X-API-KEY'))->plainTextToken,
                            'user' => new UserResource($user->load(['profile', 'roles', 'permissions'])),
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
        } else {
            return $this->sendResponse(
                null,
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Invalid token',
                ResponseErrorCode::AUTH_INVALID_TOKEN
            );
        }
    }

    /**
     * Handle an incoming phone auth request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authWithPhone(Request $request): JsonResponse
    {
        $twilioConfig = config('services.twilio');
        $wassaSmsConfig = config('services.wassa_sms');

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'preferences' => 'sometimes|array',
            'allow_registration' => 'sometimes|boolean',
            'provider' => 'sometimes|in:twilio,wassa_sms'
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

        $phone = str_replace(' ', '', $request->input('phone'));
        $verificationCode = mt_rand(100000, 999999);

        $userDoesntExist = User::wherePhone($phone)->doesntExist();

        if ($userDoesntExist && $request->input('allow_registration')) {

            $user = User::create([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => null,
                'phone' => $phone,
                'password' => Hash::make(Str::password(8, true, true, false)),
                'uid' => Functions::generateUid(),
                'account_status' => $request->input('account_status')
                    ? $request->input('account_status')
                    : (User::count() > 0 ? AccountStatus::DISABLED->value : AccountStatus::ENABLED->value),
                'auth_type' => AuthType::PHONE->value,
                'preferences' => $request->input('preferences')
            ]);

            if (User::count() === 1) $user->addRole(UserRole::SUPER_ADMIN->value);

            event(new UserRegisteredEvent($user->id));
        }

        $user = User::wherePhone($phone)->first();

        if ($user) {

            $user->verification_code = $verificationCode;
            $user->save();

            try {
                if ($request->input('provider') === 'wassa_sms') {

                    $client = new \GuzzleHttp\Client();
                    $response = $client->get(
                        $wassaSmsConfig['endpoint'],
                        [
                            'query' => [
                                'access-token' => $wassaSmsConfig['token'],
                                'sender'       => config('app.name'),
                                'receiver'     => str_replace('+', '', $phone),
                                'text'         => __('auth.verification_code', ['code' => $verificationCode]),
                                'dlr_url'      => '',
                            ]
                        ]
                    );

                    $verification = $response->getBody()->getContents();

                    return $this->sendResponse(
                        $verification,
                        ResponseStatusCode::OK,
                        'Verification code sent with success by Wassa SMS!'
                    );
                } else {
                    $twilio = new TwilioClient($twilioConfig['sid'], $twilioConfig['token']);

                    $verification = $twilio->verify->v2->services($twilioConfig['service'])
                        ->verifications
                        ->create($phone, "sms");

                    return $this->sendResponse(
                        $verification,
                        ResponseStatusCode::OK,
                        'Verification code sent with success by Twilio!'
                    );
                }
            } catch (\Exception $e) {

                return $this->sendResponse(
                    $e,
                    ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    "Code sending failed: $e",
                    ResponseErrorCode::AUTH_CODE_SENDING_FAILED
                );
            }
        }

        return $this->sendResponse(
            null,
            ResponseStatusCode::NOT_FOUND,
            'User not found',
            ResponseErrorCode::AUTH_USER_NOT_FOUND
        );
    }

    /**
     * Verify phone auth.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verifyPhoneAuth(Request $request): JsonResponse
    {
        $twilioConfig = config('services.twilio');

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'code' => 'required',
            'provider' => 'sometimes|in:twilio,wassa_sms'
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

        $phone = str_replace(' ', '', $request->input('phone'));

        $user = User::wherePhone($phone)->first();

        if ($user) {
            try {
                if ($request->input('provider') === 'wassa_sms') {
                    if ($user->verification_code !== $request->input('code')) {
                        throw new \Exception('Invalid code!');
                    }
                } else {
                    $twilio = new TwilioClient($twilioConfig['sid'], $twilioConfig['token']);

                    $verification_check = $twilio->verify->v2->services($twilioConfig['service'])
                        ->verificationChecks
                        ->create(
                            [
                                "to" => $phone,
                                "code" => $request->input('code')
                            ]
                        );

                    if ($verification_check->status != 'approved') {
                        throw new \Exception('Check not approved!');
                    }
                }
            } catch (\Exception $e) {

                return $this->sendResponse(
                    $e,
                    ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    "Code verification failed: $e",
                    ResponseErrorCode::AUTH_CODE_VERIFICATION_FAILED
                );
            }
        } else {

            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'User not found',
                ResponseErrorCode::AUTH_USER_NOT_FOUND
            );
        }

        if ($user->account_status != AccountStatus::DISABLED->value || $user->profile === null) {

            event(new UserLoggedInEvent($user->id));

            Auth::login($user);

            return $this->sendResponse(
                [
                    'access_token' => Functions::generateAccessToken($user, $request->header('X-API-KEY'))->plainTextToken,
                    'user' => new UserResource($user->load(['profile', 'roles', 'permissions']))
                ],
                ResponseStatusCode::OK,
                'User Logged in',
            );
        } else {

            return $this->sendResponse(
                null,
                ResponseStatusCode::FORBIDDEN,
                'User disabled',
                ResponseErrorCode::AUTH_USER_DISABLED
            );
        }
    }
}
