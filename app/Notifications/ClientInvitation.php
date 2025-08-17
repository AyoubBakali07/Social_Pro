<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ClientInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $token;
    public $agencyName;

    public function __construct($token, $agencyName)
    {
        $this->token = $token;
        $this->agencyName = $agencyName;
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
            ->subject('Set Up Your Account - ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited by ' . $this->agencyName . ' to join their client portal.')
            ->line('Please click the button below to set up your account and choose a password.')
            ->action('Set Up Account', $url)
            ->line('This invitation link will expire in 7 days.')
            ->line('If you did not expect to receive this invitation, you can safely ignore this email.');
    }
}
