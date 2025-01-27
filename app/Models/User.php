<?php

namespace App\Models;

use App\Enums\ProfileType;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laratrust\Contracts\LaratrustUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Laratrust\Traits\HasRolesAndPermissions;
use Laravel\Sanctum\HasApiTokens;
use Str;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail, CanResetPassword, LaratrustUser, HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions, SoftDeletes, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'avatar',
        'lastname',
        'firstname',
        'email',
        'password',
        'phone',
        'address',
        'account_status',
        'preferences',
        'auth_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_logged_in_at' => 'datetime',
        'preferences' => 'array'
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = ['profile'];

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale(): string
    {
        return $this->preferences['locale'] ?? App::currentLocale();
    }

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->morphTo();
    }

    /**
     * Get all of the sessions for the user.
     */
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get user fullname.
     */
    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Get avatar url from user path.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Str::isUrl($this->avatar, ['http', 'https']) ? $this->avatar : Storage::disk('public')->url($this->avatar);
        }

        return null;
    }

    /**
     * Admin Profile accessor
     *
     * @return bool
     */
    public function getHasAdminProfileAttribute()
    {
        return $this->profile_type == ProfileType::ADMIN->value;
    }

    /**
     * Admin Profile scope
     *
     * @return Builder
     */
    public function scopeWhereHasAdminProfile(Builder $builder)
    {
        return $builder->where('profile_type', ProfileType::ADMIN->value);
    }

    /**
     * Customer Profile accessor
     *
     * @return bool
     */
    public function getHasCustomerProfileAttribute()
    {
        return $this->profile_type == ProfileType::CUSTOMER->value;
    }

    /**
     * Customer Profile scope
     *
     * @return Builder
     */
    public function scopeWhereHasCustomerProfile(Builder $builder)
    {
        return $builder->where('profile_type', ProfileType::CUSTOMER->value);
    }

    /**
     * Summary of group
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
