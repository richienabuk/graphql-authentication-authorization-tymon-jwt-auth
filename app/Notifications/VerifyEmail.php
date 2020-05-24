<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail as Notification;

class VerifyEmail extends Notification
{
    protected function verificationUrl($notifiable)
    {
        $payload = base64_encode(json_encode([
            'id'         => $notifiable->getKey(),
            'hash'       => encrypt($notifiable->getEmailForVerification()),
            'expiration' => encrypt(Carbon::now()->addMinutes(60)->toIso8601String()),
        ]));

        return config('app.client_url') . '/email-verify' .'?token='.$payload;
    }
}
