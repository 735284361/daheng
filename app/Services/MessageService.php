<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderAddress;
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
    const USER_ORDER_DELIVER = 'nJlEy'; // 顾客-发货通知
    const AGENT_USER_PAY_SUCCESS = '6PY4u'; // 代理-用户下单通知

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

        // 短信通知
        switch ($status) {
            case Order::STATUS_PAID : // 支付成功
                $phone = AgentOrderMaps::leftJoin('users','users.id','=','agent_order_maps.agent_id')
                    ->where('order_no',$order->order_no)
                    ->value('phone');
                if ($phone) {
                    $project = self::AGENT_USER_PAY_SUCCESS;
                    $name = OrderAddress::where('order_no',$order->order_no)->value('name');
                    $data = [
                        'project' => $project,
                        'name' => $name
                    ];
                    MsgService::sendMsg($data,$phone);
                }
                break;
            case Order::STATUS_SHIPPED :
                $orderAddress = OrderAddress::where('order_no',$order->order_no)->first();
                if ($orderAddress && $orderAddress->phone) {
                    $project = self::USER_ORDER_DELIVER;
                    $data = [
                        'project' => $project,
                        'delivery_company' => $orderAddress->delivery_company,
                        'delivery_number' => $orderAddress->delivery_number
                    ];
                    MsgService::sendMsg($data,$orderAddress->phone);
                }
                break;
            default :
                return;
        }

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
