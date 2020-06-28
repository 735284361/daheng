<?php

namespace App\Services;

use App\Notifications\AdminMsg;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class AdminMsgService
{

    const PROJECT_APPLY_AGENT = '2QxPt2'; // 代理商申请
    const PROJECT_APPLY_AGENT_TEAM = 'nm7fa'; // 团队申请
    const PROJECT_APPLY_WITHDRAW = 'OcUCD'; // 提现申请



    public static function sendWithdrawApplyMsg()
    {
        AdminMsgService::sendMsg(self::PROJECT_APPLY_WITHDRAW);
    }

    public static function sendAgentApplyMsg()
    {
        AdminMsgService::sendMsg(self::PROJECT_APPLY_AGENT);
    }

    public static function sendAgentTeamApplyMsg()
    {
        AdminMsgService::sendMsg(self::PROJECT_APPLY_AGENT_TEAM);
    }

    // 管理员短信发送接口
    public static function sendMsg($project)
    {
        if ($project == '') return;
        $phoneNumber = [
//            17600296638,
            18580886262
        ];
        try {
            foreach ($phoneNumber as $phone) {
                $number = new PhoneNumber($phone,86);
                Notification::route(
                    EasySmsChannel::class,
                    $number
                )->notify(new AdminMsg($project));
            }
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getResults();
        }
        return;
    }
}
