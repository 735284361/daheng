<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
    //

    protected $agentService;

    public function __construct()
    {
        $this->agentService = new AgentService();
    }

    /**
     * 获取代理商信息
     * @return array
     */
    public function getAgentInfo()
    {
        $data = $this->agentService->getAgentInfo(auth('api')->id());
        if ($data) {
            return ['code' => 0, 'msg' => '成功', 'data' => $data];
        } else {
            return ['code' => 1, 'msg' => '没有代理商数据'];
        }
    }

    /**
     * 申请代理商
     * @return array
     */
    public function apply()
    {
        $res = $this->agentService->applyAgent();
        if ($res) {
            return ['code' => 0, 'msg' => '成功'];
        } else {
            return ['code' => 1, 'msg' => '失败'];
        }

    }

    /**
     * 获取代理商分销二维码
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getQrCode()
    {
        $this->checkIsAgent();
        $this->authorize('create',Agent::class);
        $data = $this->agentService->getQrCode(auth('api')->id());
        if ($data) {
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 1];
        }
    }

    /**
     * 加入代理商的成员
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function invite(Request $request)
    {
        $this->validate($request,['id'=>'exists:agents,user_id']);
        return $this->agentService->acceptInvite($request->id,auth('api')->id());
    }

    /**
     * 本月销售数据统计
     * @return array
     */
    public function statistics()
    {
        $this->checkIsAgent();
        $data = $this->agentService->statistics(auth('api')->id());
        if ($data) {
            return ['code' => 0, 'msg' => '成功','data' => $data];
        } else {
            return ['code' => 1, 'msg' => '失败'];
        }
    }

    /**
     * 获取代理商订单列表
     * @return array
     */
    public function orders()
    {
        $this->checkIsAgent();
        $list = $this->agentService->agentOrderList(auth('api')->id());
        return ['code' => 0, 'data' => $list];
    }

    /**
     * 成员列表
     * @return array
     */
    public function members()
    {
        $this->checkIsAgent();
        $data = $this->agentService->agentMembers(auth('api')->id());
        if ($data) {
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 1];
        }
    }

    private function checkIsAgent()
    {
        $this->authorize('isAgent',Agent::class);
    }

}
