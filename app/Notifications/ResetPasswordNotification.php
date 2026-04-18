<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

public function __construct($token)
    {
        $this->token = $token;
    }

public function via(object $notifiable): array
    {
        return ['mail'];
    }

public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = env('FRONTEND_URL', 'http://localhost:3000') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->getEmailForPasswordReset());
        
        return (new MailMessage)
            ->view('emails.reset-password', ['resetUrl' => $resetUrl, 'header' => 'P2L'])
            ->subject('P2L - Password Reset Request')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You are receiving this email because we received a password reset request for your P2L account.')
            ->line('Click the button below to reset your password:')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in ' . config('auth.passwords.users.expire') . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation('Best regards,')
            ->line('The P2L Team');
    }

public function toArray(object $notifiable): array
    {
        return [
        ];
    }
}

