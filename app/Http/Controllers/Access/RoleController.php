<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Enums\ResponseStatusCode;
use App\Http\Requests\Access\RoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->sendResponse(Role::with('permissions')->get());
    }

    /**
     * Display user resources.
     */
    public function indexUser(User $user = null)
    {
        if ($user) {
            return $this->sendResponse($user->roles()->with('permissions')->get());
        }

        return $this->sendResponse(Auth::user()->roles()->with('permissions')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
        ]);

        if ($request->input('permissions')) {
            $role->givePermissions($request->input('permissions'));
        }

        return $this->sendResponse(
            $role,
            ResponseStatusCode::CREATED,
            'Role created successfully'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return $this->sendResponse($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string',
            'description' => 'sometimes|string',
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
            );
        }

        $role->update([
            'display_name' => $request->input('display_name'),
            'description' => $request->input('description'),
        ]);

        if ($request->input('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }

        return $this->sendResponse(
            $role,
            ResponseStatusCode::OK,
            'Role updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Role deleted successfully'
        );
    }
}
