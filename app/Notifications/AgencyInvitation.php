<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class AgencyInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;
    public $inviterName;

    public function __construct($token, $inviterName)
    {
        $this->token = $token;
        $this->inviterName = $inviterName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = URL::temporarySignedRoute(
            'password.setup',
            Carbon::now()->addDays(7),
            [
                'token' => $this->token,
                'email' => $notifiable->email,
            ]
        );

        return (new MailMessage)
            ->subject('Set Up Your Agency Account - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited by ' . $this->inviterName . ' to create an agency account.')
            ->line('Click the button below to set up your account and choose a password.')
            ->action('Set Up Account', $url)
            ->line('This invitation link will expire in 7 days.')
            ->line('If you did not expect to receive this invitation, you can safely ignore this email.');
    }
}
