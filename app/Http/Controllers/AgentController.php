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
            switch ($data->status) {
                case Agent::STATUS_APPLY :
                    $msg = '正在审核中，请耐心等待';
                    break;
                case Agent::STATUS_NORMAL :
                    $msg = '您已是代理商';
                    break;
                case Agent::STATUS_DISABLE :
                    $msg = '您的代理已被禁用';
                    break;
                case Agent::STATUS_REFUSE :
                    $msg = '您的申请已被拒绝';
                    break;
                default :
                    $msg = '代理状态错误';
                    break;

            }
            return ['code' => 0, 'msg' => $msg, 'data' => $data];
        } else {
            return ['code' => 1, 'msg' => '没有代理商数据'];
        }
    }

    public function getAgentViewRight()
    {
        $userId = auth('api')->id();
        if ($this->agentService->getAgentInfo($userId)) {
            return 1; // 代理商
        }
        $applyCon = $this->agentService->checkApplyAgentCon($userId);
        if ($applyCon) {
            return 2; // 可申请
        } else {
            return 0; // 不可申请
        }
    }

    /**
     * 申请代理商
     * @return array
     */
    public function apply()
    {
        return $this->agentService->applyAgent();
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
    public function inviteMember(Request $request)
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

    /**
     * 团队申请
     * @return array
     */
    public function applyTeam()
    {
        return $this->agentService->applyTeam();
    }

    /**
     * 团队二维码
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function teamQrCode()
    {
        $data = $this->agentService->getTeamQrCode();
        if ($data) {
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 1];
        }
    }

    /**
     * 加入代理团队
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function joinTeam(Request $request)
    {
        $this->validate($request,['id'=>'exists:agent_team,id']);
        return $this->agentService->joinTeam($request->id);
    }

    /**
     * 团队信息接口
     * @return mixed
     */
    public function teamInfo()
    {
        return $this->agentService->teamInfo();
    }

    /**
     * 获取队长信息
     * @param Request $request
     * @return \App\Models\AgentTeam|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getTeamLeaderInfo(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        return $this->agentService->getTeamLeaderInfo($request->id);
    }

}
