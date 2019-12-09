<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\OrderNotification;
use App\User;

class MessageService
{

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

}
