<?php

namespace App\Notifications;

use App\Enums\NotificationSource;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUser extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private int $userId)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $emailNotifEnabled = isset($notifiable->preferences['email_notif_enabled']) && $notifiable->preferences['email_notif_enabled'] === 1;

        if ($notifiable->email && $emailNotifEnabled) {
            return ['mail', 'database'];
        }

        return ['database'];
    }

    /**
     * Get the notification data.
     *
     * @return array
     */
    private function getData(): array
    {
        return [
            'user' => User::find($this->userId),
            'title' => __('notifications.welcome_user.title', ['appName' => config('app.name')]),
            'content' => __('notifications.welcome_user.content_1'),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->getData();

        return (new MailMessage)
            ->subject($data['title'])
            ->line($data['title'])
            ->line($data['content']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = $this->getData();

        return [
            'title' => $data['title'],
            'content' => $data['content'],
            'params' => [],
            'triggeredBy' => $data['user']?->id,
            'source' => NotificationSource::SYSTEM->value,
            'target' => '',
            'removable' => true,
        ];
    }
}
