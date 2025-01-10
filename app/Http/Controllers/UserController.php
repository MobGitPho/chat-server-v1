<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Enums\AuthType;
use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Events\Auth\UserRegisteredEvent;
use App\Helpers\Functions;
use App\Http\Resources\UserResource;
use App\Mail\CommonMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('pageSize');
        $query = $request->query('query');

        if ($pageSize) {

            $users = User::query();

            if ($query) {
                $users = $users->where('firstname', 'like', '%' . $query . '%')
                    ->orWhere('lastname', 'like', '%' . $query . '%');
            }

            $users->with('profile', 'roles', 'permissions');

            $users = $users->paginate($pageSize);

            return $this->sendResponse([
                'users' => UserResource::collection($users),
                'meta' => [
                    'links' => [
                        'first' => $users->url(1),
                        'last' => $users->url($users->lastPage()),
                        'prev' => $users->previousPageUrl(),
                        'next' => $users->nextPageUrl(),
                    ],
                    'currentPage' => $users->currentPage(),
                    'perPage' => $users->perPage(),
                    'total' => $users->total(),
                    'lastPage' => $users->lastPage(),
                ],
            ]);
        }

        return $this->sendResponse(
            UserResource::collection(User::all()->load(['profile', 'roles', 'permissions']))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'account_status' => 'sometimes',
            'preferences' => 'sometimes|array',
            'avatar' => 'sometimes',
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

        $password = Hash::make($request->input('password'));
        $accountStatus = ($request->input('account_status') ?? null) !== null
            ? $request->input('account_status')
            : (User::count() > 0 ? AccountStatus::DISABLED->value : AccountStatus::ENABLED->value);
        $authType = AuthType::EMAIL_PASSWORD->value;
        $uid = Functions::generateUid();
        $avatar = ($request->input('avatar') ?? null) !== null ? $request->input('avatar') : null;

        $user = User::create([
            'lastname' => $request->input('lastname'),
            'firstname' => $request->input('firstname'),
            'email' => $request->input('email'),
            'password' => $password,
            'account_status' => $accountStatus,
            'auth_type' => $authType,
            'uid' => $uid,
            'avatar' => $avatar,
            'preferences' => $request->input('preferences'),
        ]);

        if ($user) {

            if ($request->input('roles') && is_array($request->input('roles'))) {
                $user->syncRoles($request->input('roles'));
            }

            if ($request->input('send_credentials_email')) {
                Mail::to($user)->queue(new CommonMail(
                    [
                        'title' => __('notifications.welcome_user.title', ['appName' => config('app.name')]),
                        'message' => __('notifications.welcome_user.content_1') . ' ' . __('notifications.welcome_user.content_2', ['password' => $request->input('password')]),
                    ],
                ));
            }

            event(new UserRegisteredEvent($user->id));

            return $this->sendResponse(
                new UserResource($user->load(['profile', 'roles', 'permissions'])),
                ResponseStatusCode::CREATED,
                'User created successfully'
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
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->sendResponse(
            new UserResource($user->load(['profile', 'roles', 'permissions']))
        );
    }

    /**
     * Display the list of specified resources.
     */
    public function showList(Request $request)
    {
        $users = User::whereIn('id', $request->input('ids') ?? [])->get();

        return $this->sendResponse(UserResource::collection($users->load(['profile', 'roles', 'permissions'])));
    }

    /**
     * Display the search result for a given query.
     *
     * @param Request $request The HTTP request object.
     * @param string $query The search query.
     * @return \Inertia\Response The rendered search products page.
     */
    public function searchUsers(Request $request, string $query = '')
    {
        $users = User::where('firstname', 'like', '%' . $query . '%')
            ->orWhere('lastname', 'like', '%' . $query . '%')
            ->get();

        return $this->sendResponse(UserResource::collection($users->load(['profile', 'roles', 'permissions'])));
    }

    /**
     * Update the specified resource in storage for the current user.
     */
    public function updateSelf(Request $request, User $user)
    {
        $authUser = Auth::user();

        if ($authUser->id === $user->id) {
            return $this->update($request, $user);
        } else {
            return $this->sendResponse(
                null,
                ResponseStatusCode::FORBIDDEN,
                'You cannot update another user\'s data',
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->all());

        if ($request->input('roles') && is_array($request->input('roles'))) {
            $user->syncRoles($request->input('roles'));
        }

        if ($request->input('send_credentials_email')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8',
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

            $password = $request->input('password');

            $user->password = Hash::make($password);
            $user->save();

            Mail::to($user)->queue(new CommonMail(
                [
                    'title' => __('auth.password_reset'),
                    'message' => __('notifications.welcome_user.content_2', ['password' => $password]),
                ],
            ));
        }

        return $this->sendResponse(
            new UserResource($user->load(['profile', 'roles', 'permissions'])),
            ResponseStatusCode::OK,
            'User updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'User deleted successfully'
        );
    }

    /**
     * Display user permissions.
     */
    public function userPermissions(User $user = null)
    {
        if ($user) {
            return $this->sendResponse($user->allPermissions());
        }

        return $this->sendResponse(Auth::user()->allPermissions());
    }

    /**
     * Display user sessions.
     */
    public function userSessions(User $user = null)
    {
        if ($user) {
            return $this->sendResponse($user->sessions());
        }

        return $this->sendResponse(Auth::user()->sessions());
    }

    /**
     * Display user roles.
     */
    public function userRoles(User $user = null)
    {
        if ($user) {
            return $this->sendResponse($user->roles);
        }

        return $this->sendResponse(Auth::user()->roles);
    }

    /**
     * Display user by email.
     */
    public function userByEmail($email)
    {
        $user = User::whereEmail($email)->first();

        if ($user) {
            return $this->sendResponse(
                new UserResource($user->load(['profile', 'roles', 'permissions']))
            );
        }

        return $this->sendResponse(null, ResponseStatusCode::NOT_FOUND, 'User not found');
    }

    /**
     * Display user by phone.
     */
    public function userByPhone($phone)
    {
        $user = User::wherePhone(str_replace(' ', '', $phone))->first();

        if ($user) {
            return $this->sendResponse(
                new UserResource($user->load(['profile', 'roles', 'permissions']))
            );
        }

        return $this->sendResponse(null, ResponseStatusCode::NOT_FOUND, 'User not found');
    }

    /**
     * Display username by phone.
     */
    public function usernameByPhone($phone)
    {
        $user = User::wherePhone(str_replace(' ', '', $phone))->first();

        if ($user) {
            return $this->sendResponse(
                [
                    'fullname' => $user->fullname,
                    'lastname' => $user->lastname,
                    'firstname' => $user->firstname,
                ]
            );
        }

        return $this->sendResponse(null, ResponseStatusCode::NOT_FOUND, 'User not found');
    }
}
