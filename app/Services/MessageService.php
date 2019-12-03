<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\OrderNotification;
use App\User;

class MessageService
{

    // 消息通知类型
    // 10 订单模块
    const ORDER_PAID = 1001; // 订单支付成功
    const ORDER_SHIPPED = 1002; // 已发货
    const ORDER_RECEIVED = 1003; // 已签收
    const ORDER_COMPLETED = 1004; // 已完成

    /**
     * 支付成功消息通知
     * @param Order $order
     */
    public static function paySuccessMsg(Order $order)
    {
        // 模板消息通知
        // 发送支付成功消息
//        TempMsgService::paySuccess($order);

        // 系统通知系统
        // 用户 订单支付成功
        $user = User::find($order['user_id']);
        $user->notify(new OrderNotification($order,self::ORDER_PAID));
        return;
    }

    /**
     * 订单完成通知
     * @param Order $order
     */
    public static function orderCompleteMsg(Order $order)
    {
        // 系统通知系统
        // 用户 订单支付成功
        $user = User::where('uid',$order['user_id'])->first();
        $user->notify(new OrderNotification($order,self::ORDER_COMPLETED));
        return;
    }

}
