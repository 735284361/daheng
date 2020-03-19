<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentBill;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\AgentTeam;
use App\Models\AgentTeamUser;
use App\Models\DivideRate;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\SysParams;
use App\Models\UserBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgentService
{

    protected $agentConsume = 0;

    protected $userId;

    public function __construct($userId = '')
    {
        $this->userId = $userId == '' ? auth('api')->id() : '';
    }

    public function agentConsumeCon()
    {
        return SysParams::where('code','agentConsumeCon')->getField('content');
    }

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
        // 检查消费金额是否满足条件
        if (!$this->checkConsume()) {
            $msg = "消费满".$this->agentConsume."元才能申请团队";
            return ['code' => 1, 'msg' => $msg];
        }

        $agent = Agent::firstOrCreate(['user_id'=>auth('api')->id()]);
        if ($agent->wasRecentlyCreated) {
            AdminMsgService::sendAgentApplyMsg();
        }

        if ($agent) {
            return ['code' => 0, 'msg' => '申请成功'];
        } else {
            return ['code' => 1, 'msg' => '申请失败'];
        }
    }

    /**
     * 月销量统计
     * @param $userId
     * @return mixed
     */
    public function statistics($userId)
    {
        // 本月销量
        $sales = $this->getCurrentMonthSales($userId);
        $total = $divide = 0;
        if ($sales) {
            $total = $sales->sales_volume;
            $divide = $this->getDivideAmount($total);
        }

        $data['amount'] = $total;
        $data['divide'] = $divide;

        return $data;
    }

    public function getCurrentMonthSales($userId)
    {
        return AgentBill::where([
            'user_id' => $userId,
            'month'   => Carbon::now()->format('Ym')
        ])->first();
    }

    /**
     * 代理提成月度结算
     * @param $userId
     */
    public function agentOrderSettle($userId)
    {
        $endAt = Carbon::now()->subMonth()->lastOfMonth();
        // TODO 判断是否已经结算
        // 获取上月的销量
        $salesAmount = $this->getSalesAmount($userId, $endAt);
        $total = $salesAmount['total'];
        $list = $salesAmount['list'];
        // 获取销量对应的奖金数
        $divideAmount = $this->getDivideAmount($total);
        if ($divideAmount <= 0) {
            return;
        }
        // 修改代理订单状态为已提成
        $this->setDivided($list);
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

        $billName = date('n',strtotime('-1 month')).'月份销售分成';
        return $agent->bill()->create([
            'user_id' => $userId,
            'bill_name' => $billName,
            'amount' => $amount,
            'amount_type' => UserBill::AMOUNT_TYPE_INCOME,
            'status' => UserBill::BILL_STATUS_NORMAL,
            'bill_type' => UserBill::BILL_TYPE_DIVIDE
        ]);
    }

    /**
     * 设置代理订单为已结算
     * @return bool|int
     */
    private function setDivided($list)
    {

        $orders = $list->pluck(['order_no'])->toArray();

        return  AgentOrderMaps::whereIn('order_no',$orders)->update([
                'status_divide'=>AgentOrderMaps::STATUS_DIVIDE_SETTLED
            ]);
//        return AgentOrderMaps::with('order')
//            ->where('agent_id',$userId)
//            ->where('status',AgentOrderMaps::STATUS_SETTLED) // 订单已完成
//            ->where('status_divide',AgentOrderMaps::STATUS_DIVIDE_UNSETTLE) // 未参与分成的订单
//            ->where('created_at','<=',$endAt)
//            ->update([
//                'status_divide'=>AgentOrderMaps::STATUS_DIVIDE_SETTLED
//            ]);
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
            ->where('created_at','<=',$endAt) // 代理订单生成时间
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

        $arr['total'] = $total;
        $arr['list'] = $list;
        return $arr;
    }

    /**
     * 获取提成数
     * @param $total
     * @return float|int
     */
    private function getDivideAmount($total)
    {
        $total = (int)$total;
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
        $order = $data->pluck('order_no');
        $list = Order::with('goods')->join(
            'agent_order_maps',
            'order.order_no',
            'agent_order_maps.order_no'
        )->select('order.*','agent_order_maps.commission')->whereIn('order.order_no',$order)->get();
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
            $response = $app->app_code->get('pages/distribution/accept/index?id='.auth('api')->id());
            $path = storage_path('app/public/qrcode');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'qrcode/'.$filename;
            $agent->qrcode = $qrcode;
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
            return ['code' => 1, 'msg' => '已加入，不能重复加入'];
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
        $agentInfo = AgentMember::with('agent')->where('user_id',$order->user_id)->first();
        if ($agentInfo && $agentInfo->agent->status == Agent::STATUS_NORMAL) { // 如果存在代理关系 则进入代理流程
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
        $agentOrderMaps = AgentOrderMaps::with('order')
            ->where('order_no',$orderNo)
            ->where('status',AgentOrderMaps::STATUS_UNSETTLE)
            ->first();
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
            // 增加用户代理的消费数据
            AgentMember::where('user_id',$agentOrderMaps->order->user_id)->increment('order_number');
            AgentMember::where('user_id',$agentOrderMaps->order->user_id)->increment('amount',$agentOrderMaps->commission);
            // 增加代理商的销售额
            $this->saveAgentBill($agentOrderMaps->agent_id,$agentOrderMaps->order->product_amount_total);
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
     * 增加代理商的销售数据
     * @param $agentId
     * @param $account
     * @return mixed
     */
    private function saveAgentBill($agentId, $account)
    {
        $bill = AgentBill::firstOrNew(['user_id'=>$agentId,'month'=>Carbon::now()->format('Ym')]);
        if ($bill) {
            $salesVolume = $bill->sales_volume + $account;
        } else {
            $salesVolume = $account;
        }
        $bill->sales_volume = $salesVolume;
        return $bill->save();
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
        $res = $agent->save();

        return $res;
    }

    /**
     * 更新团队状态
     * @param $id
     * @param $status
     * @return mixed
     */
    public function updateTeamStatus($id, $status)
    {
        $agentTeam = AgentTeam::find($id);
        $agentTeam->status = $status;
        $res = $agentTeam->save();

        // 如果状态为正常 则同时更新代理商状态
        if ($status == AgentTeam::STATUS_NORMAL) {
            $agent = Agent::where('user_id',$agentTeam->user_id)->first();
            $this->updateAgentStatus($agent->id,Agent::STATUS_NORMAL);
        }

        return $res;
    }

    /**
     * 申请团队
     * @return array
     */
    public function applyTeam()
    {
        /**
         * 1.检查消费金额是否满足条件
         * 2.检查用户是否在团队里
         */

        // 检查消费金额是否满足条件
        if (!$this->checkConsume()) {
            $msg = "消费满".$this->agentConsume."元才能申请团队";
            return ['code' => 1, 'msg' => $msg];
        }

        // 检查用户是否在团队里
        $userTeam = $this->getUsersTeamInfo();
        if ($userTeam) {
            return ['code' => 1, 'msg' => '你已加入团队，不能申请团队'];
        }

        // 创建团队
        $team = AgentTeam::firstOrCreate(['user_id'=>auth('api')->id()]);

        if ($team->wasRecentlyCreated) {
            // 同时处理用户代理商数据
            $this->applyAgent();
            $this->addTeamUser($team->id);
            AdminMsgService::sendAgentTeamApplyMsg();
            return ['code' => 0, 'msg' => '申请成功'];
        } else {
            return ['code' => 1, 'msg' => '申请失败'];
        }
    }

    /**
     * 加入团队
     * @param $teamId
     * @return array
     */
    public function joinTeam($teamId)
    {
        /**
         * 1.是否是代理
         * 2.是否加入过团队
         */
        $agent = $this->getAgentInfo($this->userId);
        if (!$agent || $agent->status != Agent::STATUS_NORMAL)
        {
            return ['code' => 1001, 'msg' => '您还不是代理商，请先申请成为代理商'];
        }

        if ($this->getUsersTeamInfo()) {
            return ['code' => 1002, 'msg' => '您已加入过团队不能重复加入'];
        }

        $team = $this->getTeamLeaderInfo($teamId);
        if (!$team || $team->status != AgentTeam::STATUS_NORMAL) {
            return ['code' => 1, 'msg' => '该团队暂不支持加入'];
        }

        $user = $this->addTeamUser($teamId);

        if ($user->wasRecentlyCreated) {
            return ['code' => 0, 'msg' => '加入成功'];
        } else {
            return ['code' => 1, 'msg' => '加入失败'];
        }

    }

    /**
     * 添加队员信息
     * @param $teamId
     * @return mixed
     */
    public function addTeamUser($teamId)
    {
        return AgentTeamUser::firstOrCreate(['user_id'=>$this->userId],['user_id'=>$this->userId,'team_id'=>$teamId]);
    }

    /**
     * 团队 用户组内信息
     * @return mixed
     */
    public function getUsersTeamInfo()
    {
        return AgentTeamUser::where('user_id',$this->userId)->first();
    }

    /**
     * 检查用户消费金额是否满足申请条件
     * @return bool
     */
    public function checkConsume()
    {
        $orderService = new OrderService();
        $amount = $orderService->getUserOrderConsumeAmount();
        if ($amount >= $this->agentConsume) {
            return true;
        }
        return false;
    }

    /**
     * 团队邀请码
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function getTeamQrCode()
    {
        $team = $this->getTeamLeaderInfo();
        if ($team && $team->status != AgentTeam::STATUS_NORMAL) return false;
        if ($team && $team->qrcode) {
            $qrcode = $team->qrcode;
        } else {
            $app = \EasyWeChat::miniProgram();
            $response = $app->app_code->get('pages/distribution/team-accept/index?id='.$team->id);
            $path = storage_path('app/public/qrcode');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'qrcode/'.$filename;
            $team->save();
        }
        return asset('storage/'.$qrcode);
    }

    /**
     * 团队 所有信息接口
     * @return mixed
     */
    public function teamInfo()
    {
        /**
         * 1.队长信息
         * 2.团队销售额
         * 3.团队成员
         */

        $data['team'] = [];
        $data['isTeamLeader'] = false;
        $data['users'] = [];

        // 判断是否已加入团队
        $myTeam = $this->getUsersTeamInfo();
        if (!$myTeam) {
            // 如果没有加入团队 则判断是否有在申请的团队
            $teamInfo = $this->getTeamLeaderInfo();
            if ($teamInfo) {
                $teamInfo->statusDes = AgentTeam::getStatusDes($teamInfo->status);
                $data['team'] = $teamInfo;
            }
            return $data;
        }

        $teamId = $myTeam->team_id;
        // 获取队长信息
        $teamInfo = $this->getTeamLeaderInfo($teamId);
        $teamInfo->statusDes = AgentTeam::getStatusDes($teamInfo->status);
        $data['team'] = $teamInfo;

        // 判断是否是队长
        if ($myTeam->user_id == $teamInfo->user_id) {
            $data['isTeamLeader'] = true;
            // 计算销售统计信息
            $userList = $this->getTeamUsersList($teamId);
            // 销售总额
            $totalAmount = $userList->sum('sales_volume');
            $divide = $this->getDivideAmount($totalAmount);
            $data['sales_total']['amount'] = $totalAmount;
            $data['sales_total']['divide'] = $divide;
        } else {
            $userList = $this->getTeamUsersList($teamId, $myTeam->user_id);
        }

        $userList->map(function($item) {
            $item->sales_volume = (integer)$item->sales_volume;
            $item->divide = $this->getDivideAmount($item->sales_volume);
        });

        $data['users'] = $userList;
        return $data;
    }

    /**
     * 获取团队队长信息
     * @param null $teamId
     * @return AgentTeam|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getTeamLeaderInfo($teamId = null)
    {
        $query = AgentTeam::with(array('user_info' => function($query){
            $query->select('id','nickname','avatar');
        }));
        if ($teamId == null) {
            $query->where('user_id',$this->userId);
        } else {
            $query->where('id',$teamId);
        }

        return $query->first();
    }

    /**
     * 团队 获取队员信息
     * @param $teamId
     * @param null $userId
     * @return AgentTeamUser[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTeamUsersList($teamId, $userId = null)
    {
        $query = AgentTeamUser::with(array('user_info' => function($query){
            $query->select('id','nickname','avatar');
        }))->withCount(['agent_bill as sales_volume' => function($query) {
            $query->where('month',Carbon::now()->format('Ym'))->select(DB::raw('sum(sales_volume)'));
        }])->rightJoin('agents', 'agents.user_id', '=', 'agent_team_users.user_id')
            ->where('agents.status',Agent::STATUS_NORMAL)
            ->where('team_id',$teamId);

        // 如果指定了用户ID 只查询对应用户ID的数据
        if ($userId != null) {
            $query->where('agent_team_users.user_id', $userId);
        }

        return $query->get();
    }
}
