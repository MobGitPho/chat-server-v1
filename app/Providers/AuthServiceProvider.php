<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Lang;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // For using frontend page for email verification
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {

            // Parse the URL
            $parsedUrl = parse_url($url);

            // Get the path from the parsed URL
            $path = $parsedUrl['path'];

            // Extract the hash from the path
            $parts = explode('/', $path);
            $hash = end($parts);

            // Parse the query string
            parse_str($parsedUrl['query'], $params);

            $newUrl = config('app.frontend_url') . '/verify-email' . '/' . $notifiable->id . '/' . $hash . '?' . http_build_query($params);

            return (new MailMessage)
                ->greeting(Lang::get('Hello!'))
                ->subject(Lang::get('Verify Email Address'))
                ->line(Lang::get('Please click the button below to verify your email address.'))
                ->action(Lang::get('Verify Email Address'), $newUrl)
                ->line(Lang::get('If you did not create an account, no further action is required.'));
        });

        // For using frontend page for password reset
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return config('app.frontend_url') . '/add-new-password' . '/' . $token . '?' . 'email=' . urlencode($user->email);
        });
    }
}
