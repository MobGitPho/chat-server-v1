<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'fullname' => $this->fullname,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'address' => $this->address,
            'profile_type' => $this->profile_type,
            'profile_id' => $this->profile_id,
            'account_status' => $this->account_status,
            'preferences' => $this->preferences,
            'auth_type' => $this->auth_type,
            'last_logged_in_at' => $this->last_logged_in_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'groups' => GroupResource::collection($this->whenLoaded('groups')),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles()->with('permissions')->get();
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->allPermissions();
            }),
            'profile' => $this->whenLoaded('profile', function () {
                if ($this->hasAdminProfile) {
                    return new AdminProfileResource($this->profile);
                }

                if ($this->hasCustomerProfile) {
                    return new CustomerProfileResource($this->profile);
                }

                return $this->profile;
            }),
        ];
    }
}
