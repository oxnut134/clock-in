<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        //dd($verificationUrl);
        return (new MailMessage)
            ->subject('メールアドレスの確認')
            ->line('以下のリンクをクリックしてメールアドレスを確認してください。')
            ->action('メールアドレスを確認する', $verificationUrl)
            ->line('このメールに心当たりがない場合は無視してください。');
    }
}

