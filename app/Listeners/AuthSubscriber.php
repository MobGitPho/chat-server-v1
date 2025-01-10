<?php

namespace App\Listeners;

use App\Events\Auth\EmailVerifiedEvent;
use App\Models\User;
use App\Events\Auth\UserLoggedInEvent;
use App\Events\Auth\UserLoggedOutEvent;
use App\Events\Auth\UserRegisteredEvent;
use App\Events\Auth\ProfileCreatedEvent;
use App\Events\Auth\ProfileUpdatedEvent;
use App\Helpers\Functions;
use App\Notifications\WelcomeUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AuthSubscriber implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event subscriber.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event UserLoggedIn.
     */
    public function onUserLoggedIn(UserLoggedInEvent $event)
    {
        $user = User::find($event->userId);
        if ($user) {
            Functions::generateRefreshToken($user);

            $user->last_logged_in_at = now();
            $user->save();
        }
    }

    /**
     * Handle the event UserLoggedOut.
     */
    public function onUserLoggedOut(UserLoggedOutEvent $event)
    {
        //
    }

    /**
     * Handle the event UserRegistered.
     */
    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = User::find($event->userId);
        if ($user) Functions::generateRefreshToken($user);
    }

    /**
     * Handle the event ProfileCreated.
     */
    public function onProfileCreated(ProfileCreatedEvent $event)
    {
        $user = User::find($event->userId);
        if ($user) {
            $user->notify(new WelcomeUser($user->id));
            if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Exception $e) {
                    Log::channel('debug')->debug($e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the event ProfileUpdated.
     */
    public function onProfileUpdated(ProfileUpdatedEvent $event)
    {
        //
    }

    /**
     * Handle the event EmailVerified.
     */
    public function onEmailVerified(EmailVerifiedEvent $event)
    {
        //
    }

    public function subscribe(Dispatcher $dispatcher)
    {
        return [
            UserLoggedInEvent::class => 'onUserLoggedIn',
            UserLoggedOutEvent::class => 'onUserLoggedOut',
            UserRegisteredEvent::class => 'onUserRegistered',
            ProfileCreatedEvent::class => 'onProfileCreated',
            ProfileUpdatedEvent::class => 'onProfileUpdated',
            EmailVerifiedEvent::class => 'onEmailVerified',
        ];
    }
}
