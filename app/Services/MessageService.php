<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Order;
use App\Notifications\OrderNotification;
use App\Notifications\UserMsg;
use App\Notifications\VerificationCode;
use App\User;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class MessageService
{

    // submall 短信通知模板
    const PROJECT_APPLY_AGENT_NORMAL = 'h7Dxp2'; // 代理商申请成功
    const PROJECT_APPLY_AGENT_REFUSED = '3G6O'; // 代理商申请失败

    /**
     * 支付成功消息通知
     * @param Order $order
     * @param $status
     */
    public static function orderMsg(Order $order, $status)
    {
        // 模板消息通知
        // 发送支付成功消息
//        TempMsgService::paySuccess($order);

        // 系统通知系统
        // 用户 订单支付成功
        $user = User::find($order['user_id']);
        $user->notify(new OrderNotification($order,$status));
        return;
    }

    public static function sendAgentApplyMsg($phone, $status)
    {
        switch ($status) {
            case Agent::STATUS_NORMAL :
                $project = self::PROJECT_APPLY_AGENT_NORMAL;
                break;
            case Agent::STATUS_REFUSE :
                $project = self::PROJECT_APPLY_AGENT_REFUSED;
                break;
            default :
                return;
        }
        AdminMsgService::sendMsg($project);
    }

    /**
     * @param $phone
     * @param $project
     */
    public static function sendMsg($phone, $project)
    {
        try {
            $number = new PhoneNumber($phone,86);
            Notification::route(
                EasySmsChannel::class,
                $number
            )->notify(new UserMsg($project));
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getResults();
        }
        return;
    }

}
