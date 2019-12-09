<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\UserBill;

class AgentService
{

    /**
     * 获取代理商信息
     * @param $userId
     * @return mixed
     */
    public function getAgentInfo($userId)
    {
        return Agent::where('user_id',$userId)->first();
    }

    /**
     * 保存订单和代理的关系
     * @param Order $order
     */
    public static function saveAgentOrderMap(Order $order)
    {
        // 代理
        $agentInfo = AgentMember::where('user_id',$order->user_id)->first();
        if ($agentInfo) { // 如果存在代理关系 则进入代理流程
            // 佣金计算
            $orderGoods = OrderGoods::where('order_no',$order->order_no)->get();
            $commission = 0;
            foreach ($orderGoods as $goods) {
                $commission += $goods->product_count * $goods->dist_price;
            }
            // 添加代理订单关系
            $agentOrder = new AgentOrderMaps();
            $agentOrder->agent_id = $agentInfo->agent_id;
            $agentOrder->order_no = $order->order_no;
            $agentOrder->commission = $commission;
            $agentOrder->save();
        }
        return;
    }

    /**
     * 订单分成
     * @param $orderNo
     */
    public function orderCommission($orderNo)
    {
        // 订单分成流程
        $agentOrderMaps = AgentOrderMaps::with('order')->where('order_no',$orderNo)->where('status',AgentOrderMaps::STATUS_UNSETTLE)->first();
        if ($agentOrderMaps) {
            // 更新订单代理结算状态
            $this->setSettled($agentOrderMaps);
            // 增加代理商的账户余额
            $this->incAgentBalance($agentOrderMaps->agent_id, $agentOrderMaps->commission);
            // 更新代理商资金流水表
            $this->saveBillInfo($agentOrderMaps->agent_id, $agentOrderMaps->commission, UserBill::AMOUNT_TYPE_INCOME,
                UserBill::BILL_STATUS_NORMAL,UserBill::BILL_TYPE_COMMISSION);
            // 增加用户代理的消费金额
            AgentMember::where('user_id',$agentOrderMaps->order->user_id)->increment('order_number');
            AgentMember::where('user_id',$agentOrderMaps->order->user_id)->increment('amount',$agentOrderMaps->commission);
        }
        return;
    }

    /**
     * 将代理订单设置为已结算
     * @param AgentOrderMaps $agentOrderMaps
     * @return bool
     */
    private function setSettled(AgentOrderMaps $agentOrderMaps)
    {
        $agentOrderMaps->status = AgentOrderMaps::STATUS_SETTLED;
        return $agentOrderMaps->save();
    }

    /**
     * 添加代理商余额
     * @param $userId
     * @param $account
     * @return mixed
     */
    private function incAgentBalance($userId, $account)
    {
        $userAccount = new UserAccountService();
        return $userAccount->incBalance($userId, $account);
    }

    /**
     * 保存账单
     * @param $userId
     * @param $amount
     * @param $amountType
     * @param $status
     * @param $billType
     * @return mixed
     */
    private function saveBillInfo($userId, $amount, $amountType, $status, $billType)
    {
        $agent = $this->getAgentInfo($userId);
        return $agent->bill()->create([
            'user_id' => $userId,
            'amount' => $amount,
            'amount_type' => $amountType,
            'status' => $status,
            'bill_type' => $billType
        ]);
    }
}
