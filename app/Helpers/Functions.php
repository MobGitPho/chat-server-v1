<?php

namespace App\Helpers;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;
use Str;

class Functions
{
    /**
     * check if array is associative
     *
     * @param  mixed $arr
     * @return bool
     */
    static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * check if string start with provided string part
     *
     * @param  mixed $string
     * @param  mixed $startString
     * @return bool
     */
    static function startsWith($string, $startString)
    {
        $len = strlen($startString);

        return (substr($string, 0, $len) === $startString);
    }

    /**
     * check if string end with provided string part
     *
     * @param  mixed $string
     * @param  mixed $endString
     * @return bool
     */
    static function endsWith($string, $endString)
    {
        $len = strlen($endString);

        if ($len == 0) return true;

        return (substr($string, -$len) === $endString);
    }

    /**
     * Check if the request is an API request.
     *
     * @return bool
     */
    static function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->header('Accept') == 'application/json';
    }

    /**
     * generate uid
     *
     * @return string
     */
    static function generateUid(): string
    {
        $uid = (string) Str::uuid();

        if (User::whereUid($uid)->exists()) {
            return self::generateUid();
        }

        return $uid;
    }

    /**
     * generate access token
     *
     * @return NewAccessToken
     */
    static function generateAccessToken(User $user, $xApiKey = null): NewAccessToken
    {
        $expireAt = null;

        switch ($xApiKey) {
            case env('APP_WEB_X_API_KEY'):
                $expireAt = now()->addMonths(18);
                break;

            case env('APP_ADMIN_X_API_KEY'):
                $expireAt = now()->addMinutes(42);
                break;

            case env('APP_MOBILE_X_API_KEY'):
                $expireAt = null;
                break;

            default:
                $expireAt = now()->addMinutes(42);
                break;
        }

        return $user->createToken(
            'access-token',
            [TokenAbility::ACCESS_API->value],
            $expireAt
        );
    }

    /**
     * generate refresh token
     *
     * @return NewAccessToken
     */
    static function generateRefreshToken(User $user, $longDuration = true): NewAccessToken
    {
        $tokens = $user->tokens()->where('name', 'refresh-token')->get();

        foreach ($tokens as $token) {
            $token->delete();
        }

        return $user->createToken(
            'refresh-token',
            [TokenAbility::ISSUE_ACCESS_TOKEN->value],
            $longDuration ? now()->addWeeks(3) : now()->addHours(6)
        );
    }
}
