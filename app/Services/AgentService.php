<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\AgentBill;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\AgentTeam;
use App\Models\AgentTeamBill;
use App\Models\AgentTeamUser;
use App\Models\DivideRate;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\SysParams;
use App\Models\UserBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SebastianBergmann\Environment\Console;

class AgentService
{

    protected $userId;
    protected $msg;

    public function __construct($userId = '')
    {
        $this->userId = $userId == '' ? auth('api')->id() : '';
    }

    public function getErrorMsg()
    {
        return $this->msg;
    }

    public function agentConsumeCon()
    {
        $count = SysParams::where('code','agentConsumeCon')->value('value');
        return $count ? $count : 0;
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
     * 获取代理用户信息
     * @param $userId
     * @return Agent|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getAgentUserInfo($userId)
    {
        return Agent::with('user')->where('user_id',$userId)->first();
    }

    /**
     * 申请代理商
     * @return mixed
     */
    public function applyAgent()
    {
        $userId = auth('api')->id();

        $agentInfo = $this->getAgentInfo($userId);

        if ($agentInfo) {
            return ['code' => 1, 'msg' => '不能重复申请'];
        }

        $applyCon = $this->checkApplyAgentCon($userId);
        if (!$applyCon) {
            return ['code' => 0, 'msg' => $this->msg];
        }

        $agent = Agent::firstOrCreate(['user_id'=>$userId]);
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
     * 顾客身份信息
     * @param $userId
     * @return mixed
     */
    public function getCustomerInfo($userId)
    {
        return AgentMember::where('user_id',$userId)->first();
    }

    /**
     * 检查代理商申请权限
     * @param $userId
     * @return bool
     */
    public function checkApplyAgentCon($userId)
    {
        // 消费限制
        if (!$this->checkConsume()) {
            $this->msg = "消费满".$this->agentConsumeCon()."元才能申请团队";
            return false;
        }

        // 顾客层级限制
        $customerLevel = $this->getCustomerLevel($userId);
        if ($customerLevel == 3) { // 三级顾客 无申请权限
            $this->msg = "暂无申请权限";
            return false;
        }
        return true;
    }

    /**
     * 判断用户顾客的层级
     * @param $userId
     * @return int
     */
    public function getCustomerLevel($userId)
    {
        // 顾客层级限制
        $customer = $this->getCustomerInfo($userId);
        if ($customer) { // 如果是顾客
            // 判断用户的代理属于几级代理
            $level = $this->getAgentLevel($customer->agent_id);
            if ($level == 2) {
                return 3;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    /**
     * 获取代理商的层级
     * @param $userId
     * @return int
     */
    public function getAgentLevel($userId)
    {
        $agent = $this->getAgentInfo($userId);
        // 如果
        if ($agent) {
            return $agent->level;
        }
        return 0;
    }

    /**
     * 月销量统计
     * @param $userId
     * @return mixed
     */
    public function statistics($userId)
    {
        // 本月销量
        $sales = $this->getAgentBill($userId);
        $total = $divide = 0;
        if ($sales) {
            $total = $sales->sales_volume;
            $divide = $this->getDivideAmount($total);
        }

        $data['amount'] = $total;
        $data['divide'] = $divide;

        return $data;
    }

    /**
     * 获取代理商账单
     * @param $userId
     * @param null $month
     * @return mixed
     */
    public function getAgentBill($userId,$month = null)
    {
        if ($month == null) {
            $month = Carbon::now()->format('Ym');
        }
        return AgentBill::where([
            'user_id' => $userId,
            'month'   => $month
        ])->first();
    }

    /**
     * 获取团队账单
     * @param $teamId
     * @param null $month
     * @return mixed
     */
    public function getAgentTeamBill($teamId,$month = null)
    {
        if ($month == null) {
            $month = Carbon::now()->format('Ym');
        }
        return AgentTeamBill::where([
            'team_id' => $teamId,
            'month'   => $month
        ])->first();
    }

    /**
     * 代理提成月度结算
     * @param $userId
     */
    public function agentOrderSettle($userId)
    {
        $subMonth = Carbon::now()->subMonth()->format('Ym');
        $agentBill = $this->getAgentBill($userId,$subMonth);
        // 判断是否已经结算
        if ($agentBill && $agentBill->divide_status == AgentBill::DIVIDE_STATUS_DIVIDED) {
            return;
        } else {
            $agentBill = $this->saveAgentBill($userId,0,$subMonth);
        }
        // 获取分成
        $divide = 0;
        if ($agentBill) {
            $divide = $this->getDivideAmount($agentBill->sales_volume);
            // 修改月账单为已结算
            $this->updateAgentBill($agentBill->id,$divide);
        }
        // 增加代理商余额
        if ($divide > 0) {
            $this->incAgentBalance($userId, $divide);
        }
        // 保存提成账单
        $this->saveAgentDivideBill($userId, $divide);
        return;
    }

    public function agentTeamSettle($teamId)
    {
        $subMonth = Carbon::now()->subMonth()->format('Ym');
        $agentTeamBill = $this->getAgentTeamBill($teamId,$subMonth);
        // 判断是否已经结算
        if ($agentTeamBill && $agentTeamBill->divide_status == AgentTeamBill::DIVIDE_STATUS_DIVIDED) {
            return;
        }

        $teamInfo = $this->getTeamLeaderInfo($teamId);

        // 获取销售总额
        $salesList = $this->getTeamSalesVolume($teamId,$subMonth);

        $salesVolume = $salesList->sum('sales_volume'); // 团队销售总额
        $divideAmount = $salesList->sum('divide_amount'); // 已经分成金额
        // 获取分成
        $divideTotalAmount = $this->getDivideAmount($salesVolume);
        $divide = $divideTotalAmount - $divideAmount;
        $divide < 0 ? $divide = 0 : '';
        // 保存团队结算记录
        $userId = $teamInfo->user_id;
        $this->saveAgentTeamBill($teamId, $userId, $subMonth,$salesVolume, $divideTotalAmount, $divide);

        // 增加队长余额
        if ($divide > 0) {
            $this->incAgentBalance($userId, $divide);
        }
        // 保存提成账单
        $this->saveAgentDivideBill($userId, $divide,2);
        return;
    }

    /**
     * 更新代理商的分成账单
     * @param $id
     * @param $amount
     * @return mixed
     */
    public function updateAgentBill($id,$amount)
    {
        $bill = AgentBill::find($id);
        $bill->divide_status = AgentBill::DIVIDE_STATUS_DIVIDED;
        $bill->divide_amount = $amount;
        return $bill->save();
    }

    /**
     * 保存代理分成订单流水
     * @param $userId
     * @param $amount
     * @param int $billType
     * @return mixed
     */
    private function saveAgentDivideBill($userId, $amount, $billType = 1)
    {
        $agent = Agent::where('user_id',$userId)->first();

        $billName = date('n',strtotime('-1 month'));
        if ($billType == 1) {
            $billName .= '月份销售奖金';
        } else if ($billType == 2) {
            $billName .= '月份团队销售奖金';
        }
        return $agent->bill()->create([
            'user_id' => $userId,
            'bill_name' => $billName,
            'amount' => $amount,
            'amount_type' => UserBill::AMOUNT_TYPE_INCOME,
            'status' => UserBill::BILL_STATUS_NORMAL,
            'bill_type' => UserBill::BILL_TYPE_DIVIDE
        ]);
    }

    private function saveAgentTeamBill($teamId, $userId, $month, $salesVolume,$divideTotalAmount,$divideRemainAmount)
    {
        $teamBill = new AgentTeamBill();
        return $teamBill->create([
            'team_id' => $teamId,
            'user_id' => $userId,
            'month' => $month,
            'sales_volume' => $salesVolume,
            'divide_status' => AgentTeamBill::DIVIDE_STATUS_DIVIDED,
            'divide_total_amount' => $divideTotalAmount,
            'divide_remain_amount' => $divideRemainAmount,
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
        $status = [
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_RECEIVED,
            Order::STATUS_COMPLETED
        ];
        $list = Order::with('address')->with('goods')->join(
            'agent_order_maps',
            'order.order_no',
            'agent_order_maps.order_no'
        )->select('order.*','agent_order_maps.commission')
            ->whereIn('order.order_no',$order)
            ->whereIn('order.status',$status)
            ->orderBy('pay_time','desc')
            ->get();
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
        if ($agent) {
            $app = \EasyWeChat::miniProgram();
            $response = $app->app_code->get('/pages/distribution/accept/index?id='.$userId);
            $path = storage_path('app/public/qrcode');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'qrcode/'.$filename;
            $agent->qrcode = $qrcode;
            $agent->save();
            return storage_path('app/public/'.$qrcode);
        }
        return false;
    }

    public function getOnlyQrCode($userId)
    {
        $agent = $this->getAgentInfo($userId);
        if ($agent) {
            $app = \EasyWeChat::miniProgram();
            $response = $app->app_code->get('/pages/distribution/accept/index?id='.$userId);
            $path = storage_path('app/public/qrcode');
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
                $filename = $response->saveAs($path, uniqid().'.png');
            }
            $qrcode = 'qrcode/'.$filename;
            $agent->qrcode = $qrcode;
            $agent->save();
            return asset('storage/qrcode/'.$filename);
        }
        return false;
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
     * 移除代理商的成员
     * @param $userId
     * @return mixed
     */
    public function removeAgentMember($userId)
    {
        return AgentMember::where('user_id',$userId)->delete();
    }

    /**
     * 保存订单和代理的关系
     * @param Order $order
     */
    public static function saveAgentOrderMap(Order $order)
    {
        // 代理
        $Agent = new AgentService();
        $agentId = $Agent->getCommissionUserId($order->user_id);
        if (!$agentId) return;
        // 添加代理订单关系
        $agentOrder = new AgentOrderMaps();
        $agentOrder->agent_id = $agentId;
        $agentOrder->order_no = $order->order_no;
        $agentOrder->commission = $order->commission_fee;;
        $agentOrder->save();
    }

    /**
     * 判断用户的订单是否满足分成流程
     * @param $userId
     * @return bool
     */
    public function getCommissionUserId($userId)
    {
        // 如果自己是代理商 且状态正常
        $agentInfo = $this->getAgentInfo($userId);
        if ($agentInfo && $agentInfo->status == Agent::STATUS_NORMAL) {
            return $agentInfo->user_id;
        } else {
            // 如果是顾客
            $agentInfo = self::getUsersAgent($userId);
            if ($agentInfo) {
                return $agentInfo->agent_id;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取用户所属代理商的信息
     * @param $userId
     * @return AgentMember|bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public static function getUsersAgent($userId)
    {
        // 代理
        $agentInfo = AgentMember::with('agent')->where('user_id',$userId)->first();
        if ($agentInfo && $agentInfo->agent->status == Agent::STATUS_NORMAL) {
            return $agentInfo;
        }
        return false;
    }

    /**
     * 订单分成
     * @param $orderNo
     */
    public function orderCommission($orderNo)
    {
        // 订单分成流程
        DB::enableQueryLog();
        $agentOrderMaps = AgentOrderMaps::with('order')
            ->where('order_no',$orderNo)
            ->where('status',AgentOrderMaps::STATUS_UNSETTLE)
            ->first();
        Log::info($orderNo);
        Log::info('最近的数据查询:',DB::getQueryLog());
        if ($agentOrderMaps !== null) {
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
            AgentMember::where('user_id',$agentOrderMaps->order->user_id)->increment('amount',$agentOrderMaps->order->order_amount_total);
            // 增加代理商的销售额
            $this->saveAgentBill($agentOrderMaps->agent_id,$agentOrderMaps->order->commission_remain_fee);
        } else {
            Log::info('未进行分成');
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
     * @param null $month
     * @return mixed
     */
    private function saveAgentBill($agentId, $account, $month = null)
    {
        $month ? '' : $month = Carbon::now()->format('Ym');
        $bill = AgentBill::firstOrNew(['user_id'=>$agentId,'month'=>$month]);
        if ($bill) {
            $salesVolume = $bill->sales_volume + $account;
        } else {
            $salesVolume = $account;
        }
        $bill->sales_volume = $salesVolume;
        $bill->save();
        return $bill;
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

        // 如果状态从申请状态改为正常状态
        if ($agent->status == Agent::STATUS_APPLY && $status == Agent::STATUS_NORMAL) {
            $userId = $agent->user_id;
            $customerLevel = $this->getCustomerLevel($userId);
            if ($customerLevel == 1) { // 一级顾客直接 创建团队
                $team = $this->addTeam($userId);
                $this->addTeamUser($team->id,$userId);
                $agent->level = 1;
            } elseif ($customerLevel == 2) { // 二级顾客 加入团队
                // 顾客信息
                $customer = $this->getCustomerInfo($userId);
                // 顾客所在团队信息
                $team = $this->getTeamLeaderInfo(null,$customer->agent_id);
                // 添加团队成员
                $this->addTeamUser($team->id,$userId);
                // 移除代理商顾客成员
                $this->removeAgentMember($userId);
                $agent->level = 2;
            } elseif ($customerLevel == 3) { // 三级顾客 无权限
                return false;
            }
         }

        $agent->status = $status;
        $res = $agent->save();

        return $res;
    }

    /**
     * 更新团队状态 弃用
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
     * 申请团队 弃用
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
            $msg = "消费满".$this->agentConsumeCon()."元才能申请团队";
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
            $this->addTeamUser($team->id,auth('api')->id());
            AdminMsgService::sendAgentTeamApplyMsg();
            return ['code' => 0, 'msg' => '申请成功'];
        } else {
            return ['code' => 1, 'msg' => '申请失败'];
        }
    }

    /**
     * 增加团队
     * @param $userId
     * @return mixed
     */
    public function addTeam($userId)
    {
        // 创建团队
        return AgentTeam::firstOrCreate(['user_id'=>$userId]);
    }

    /**
     * 加入团队 弃用
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
     * @param $userId
     * @return mixed
     */
    public function addTeamUser($teamId, $userId)
    {
        return AgentTeamUser::firstOrCreate(['user_id'=>$userId],['user_id'=>$userId,'team_id'=>$teamId]);
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
        $amount = $orderService->getUserPaidConsumeAmount(null,1);
        $consume= $this->agentConsumeCon();
        if ($amount >= $consume) {
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
//        if ($myTeam->user_id == $teamInfo->user_id) {
//            $data['isTeamLeader'] = true;
//            // 计算销售统计信息
//            $userList = $this->getTeamUsersList($teamId);
//            // 销售总额
//            $totalAmount = $userList->sum('sales_volume');
//            $divide = $this->getDivideAmount($totalAmount);
//            $data['sales_total']['amount'] = $totalAmount;
//            $data['sales_total']['divide'] = $divide;
//        } else {
//            $userList = $this->getTeamUsersList($teamId, $myTeam->user_id);
//        }

        // 所有成员
        if ($myTeam->user_id == $teamInfo->user_id) {
            $data['isTeamLeader'] = true;
        }
        // 计算销售统计信息
        $userList = $this->getTeamUsersList($teamId);
        // 销售总额
        $totalAmount = $userList->sum('sales_volume');
        $divide = $this->getDivideAmount($totalAmount);
        $data['sales_total']['amount'] = $totalAmount;
        $data['sales_total']['divide'] = $divide;

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
     * @param null $userId
     * @return AgentTeam|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getTeamLeaderInfo($teamId = null, $userId = null)
    {
        $query = AgentTeam::with(array('user_info' => function($query){
            $query->select('id','nickname','avatar');
        }));
        if ($teamId != null) {
            $query->where('id',$teamId);
        } else if ($userId != null) {
            $query->where('user_id',$userId);
        } else {
            $query->where('user_id',$this->userId);
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

    /**
     * 获取团队总销量
     * @param $teamId
     * @param null $month
     * @return mixed
     */
    public function getTeamSalesVolume($teamId, $month = null)
    {
        if ($month == null) {
            $month = Carbon::now()->format('Ym');
        }
        $list = AgentTeamUser::whereHas('agent',function($query) {
            $query->where('status',Agent::STATUS_NORMAL);
        })->rightJoin('agent_bills', function ($join) use ($month) {
            $join->on('agent_bills.user_id', '=', 'agent_team_users.user_id')
                ->where('agent_bills.month', $month);
        })->where('team_id',$teamId)->get();

        return $list;
    }
}
