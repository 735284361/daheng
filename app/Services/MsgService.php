<?php

namespace App\Services;

use App\Notifications\UserMsg;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class MsgService
{

    // 短信发送端口
    public static function sendMsg($data, $phone)
    {
        if ($data == '') return;
        try {
            $number = new PhoneNumber($phone,86);
            Notification::route(
                EasySmsChannel::class,
                $number
            )->notify(new UserMsg($data));
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getResults();
        }
        return;
    }
}
