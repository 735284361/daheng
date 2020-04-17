<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Leonis\Notifications\EasySms\Messages\EasySmsMessage;

class VerificationCode extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return [EasySmsChannel::class,'database'];
    }

    public function toEasySms($notifiable)
    {
        return (new EasySmsMessage)
//            ->setContent('您的验证码为: 6379')
//            ->setTemplate('SMS_183775355')
//            ->setTemplate('3526558')
            ->setData(['project' => 'whUvF1']);
    }

    public function toArray($notifiable)
    {

    }
}
