<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\DivideRate;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\UserBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
     * 申请代理商
     * @return mixed
     */
    public function applyAgent()
    {
        return Agent::firstOrCreate(['user_id'=>auth('api')->id()]);
    }

    /**
     * 月销量统计
     * @param $userId
     * @return mixed
     */
    public function statistics($userId)
    {
        // 本月销量
        $endAt = Carbon::now();
        $total = $this->getSalesAmount($userId, $endAt);
        $divide = $this->getDivideAmount($total);

        $data['amount'] = $total;
        $data['divide'] = $divide;

        return $data;
    }

    /**
     * 代理提成月度结算
     * @param $userId
     */
    public function agentOrderSettle($userId)
    {
        $endAt = Carbon::now()->subMonth()->lastOfMonth();
        // TODO 判断是否已经结算
        $salesAmount = $this->getSalesAmount($userId, $endAt);
        $divideAmount = $this->getDivideAmount($salesAmount);
        // 修改代理订单状态为已提成
        $this->setDivided($userId, $endAt);
        // 增加代理商余额
        if ($divideAmount > 0) {
            $this->incAgentBalance($userId, $divideAmount);
        }
        // 保存提成账单
        $this->saveAgentDivideBill($userId, $divideAmount);
    }

    /**
     * 保存代理分成订单流水
     * @param $userId
     * @param $amount
     * @return mixed
     */
    private function saveAgentDivideBill($userId, $amount)
    {
        $agent = Agent::where('user_id',$userId)->first();
        return $agent->bill()->create([
            'user_id' => $userId,
            'bill_name' => UserBill::getBillType(UserBill::BILL_TYPE_DIVIDE),
            'amount' => $amount,
            'amount_type' => UserBill::AMOUNT_TYPE_INCOME,
            'status' => UserBill::BILL_STATUS_NORMAL,
            'bill_type' => UserBill::BILL_TYPE_DIVIDE
        ]);
    }

    /**
     * 设置代理订单为已结算
     * @param $userId
     * @param $endAt
     * @return bool|int
     */
    private function setDivided($userId, $endAt)
    {
        return AgentOrderMaps::with('order')
            ->where('agent_id',$userId)
            ->where('status',AgentOrderMaps::STATUS_SETTLED) // 订单已完成
            ->where('status_divide',AgentOrderMaps::STATUS_DIVIDE_UNSETTLE) // 未参与分成的订单
            ->where('created_at','<=',$endAt)
            ->update([
                'status_divide'=>AgentOrderMaps::STATUS_DIVIDE_SETTLED
            ]);
    }

    /**
     * 按指定时间获取代理订单列表
     * @param $userId
     * @param $endAt
     * @return AgentOrderMaps[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    private function getAgentOrderList($userId, $endAt)
    {
        // 本月销量
        $list = AgentOrderMaps::with('order')
            ->where('agent_id',$userId)
            ->where('status',AgentOrderMaps::STATUS_SETTLED) // 订单已完成
            ->where('status_divide',AgentOrderMaps::STATUS_DIVIDE_UNSETTLE) // 未参与分成的订单
            ->where('created_at','<=',$endAt)
            ->get();
        return $list;
    }

    /**
     * 获取指定时间段的销售额
     * @param $userId
     * @param $endAt
     * @return int
     */
    private function getSalesAmount($userId, $endAt)
    {
        // 本月销量
        $list = $this->getAgentOrderList($userId, $endAt);
        $total = 0;
        $list->map(function ($data) use (&$total) {
            $total += $data->order->product_amount_total;
        });
        return $total;
    }

    /**
     * 获取提成数
     * @param $total
     * @return float|int
     */
    private function getDivideAmount($total)
    {
        $divideRate = DivideRate::where('sales_start','<',$total)->where('sales_end','>=',$total)->first();
        if ($divideRate) {
            return round(($divideRate->proportion * $total) / 100);
        } else {
            return 0;
        }
    }

    /**
     * 获取代理商订单
     * @param $agentId
     * @return Order[]|array|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function agentOrderList($agentId)
    {
        $data = AgentOrderMaps::where('agent_id',$agentId)->get('order_no');
        $list = array();
        if ($data) {
            $data = array_column(json_decode($data,true),'order_no');
            $list = Order::with('goods')->whereIn('order_no',$data)->get();
        }
        return $list;
    }

    /**
     * 分销成员
     * @param $agentId
     * @return AgentMember[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function agentMembers($agentId)
    {
        return AgentMember::with(array('user'=>function($query){
            $query->select('id','nickname','avatar');
        }))->where('agent_id',$agentId)->get();
    }

    /**
     * 获取分销二维码
     * @param $userId
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function getQrCode($userId)
    {
        $agent = $this->getAgentInfo($userId);
        if ($agent && $agent->qrcode) {
            $qrcode = $agent->qrcode;
        } else {
            $app = \EasyWeChat::miniProgram();
            $response = $app->app_code->get('pages/distribution/code/code?id='.auth('api')->id());
            $path = storage_path('app/public/qrcode');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'qrcode/'.$filename;
            $agent->save();
        }
        return asset('storage/'.$qrcode);
    }

    /**
     * 加入代理商的成员
     * @param $agentId
     * @param $userId
     * @return array
     */
    public function acceptInvite($agentId, $userId)
    {
        // 判断是否已经加入过别人的代理
        if ($data = AgentMember::with('user')->where('user_id',$userId)->exists()) {
            return ['code' => 1, 'msg' => '已加入：'.$data->user->nickname.'的代理，不能重复加入'];
        }
        // 判断代理商是否存在
        $agentInfo = $this->getAgentInfo($agentId);
        if (!$agentInfo) {
            return ['code' => 1, 'msg' => '该代理商不存在'];
        }
        // 判断代理商状态
        if ($agentInfo->status != Agent::STATUS_NORMAL) {
            return ['code' => 1, 'msg' => '该代理商暂不支持加入'];
        }
        // 判断自己是否是代理商
        $myAgent = $this->getAgentInfo($userId);
        if ($myAgent) {
            return ['code' => 1, 'msg' => '你已经是代理商，不能加入他人的代理'];
        }
        // 加入代理

        $agentMember = new AgentMember();
        $agentMember->agent_id = $agentId;
        $agentMember->user_id = $userId;
        $agentMember->save();
        return ['code' => 0, 'msg' => '加入成功'];
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
            $this->saveBillInfo(
                $orderNo,
                $agentOrderMaps->agent_id,
                UserBill::getBillType(UserBill::BILL_TYPE_COMMISSION),
                $agentOrderMaps->commission,
                UserBill::AMOUNT_TYPE_INCOME,
                UserBill::BILL_STATUS_NORMAL,
                UserBill::BILL_TYPE_COMMISSION
            );
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
        $userAccount = new UserAccountService($userId);
        return $userAccount->incBalance($account);
    }

    /**
     * 保存账单
     * @param $orderNo
     * @param $userId
     * @param $billName
     * @param $amount
     * @param $amountType
     * @param $status
     * @param $billType
     * @return mixed
     */
    private function saveBillInfo($orderNo, $userId, $billName, $amount, $amountType, $status, $billType)
    {
        $order = Order::where('order_no',$orderNo)->first();
        return $order->bill()->create([
            'user_id' => $userId,
            'bill_name' => $billName,
            'amount' => $amount,
            'amount_type' => $amountType,
            'status' => $status,
            'bill_type' => $billType
        ]);
    }

    /**
     * 更新代理商状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function updateAgentStatus($id, $status)
    {
        $agent = Agent::find($id);
        $agent->status = $status;
        return $agent->save();
    }
}
