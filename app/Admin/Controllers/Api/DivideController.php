<?php

namespace App\Admin\Controllers\Api;

use App\Models\Agent;
use App\Models\AgentTeam;
use App\Services\AgentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DivideController extends Controller
{
    //

    protected $agentService;

    public function __construct()
    {
        $this->agentService = new AgentService();
    }

    /**
     * 代理商分成
     * @throws \Throwable
     */
    public function divide()
    {
        if (Carbon::today() == Carbon::now()->firstOfMonth()) {
            $exception = DB::transaction(function () {
                // 代理商结算
                $agentList = Agent::where('status',Agent::STATUS_NORMAL)->get();
                foreach ($agentList as $agent) {
                    // 奖金结算
                    $this->agentService->agentOrderSettle($agent->user_id);
                }

                // 团队队长奖金结算
                $teamList = AgentTeam::where('status',AgentTeam::STATUS_NORMAL)->get();
                foreach ($teamList as $team) {
                    // 奖金结算
                    $this->agentService->agentTeamSettle($team->id);
                }
            });

            if (is_null($exception)) {
                return ['code' => 0,'msg'=>'结算成功'];
            } else {
                return ['code' => 1,'msg'=>'结算失败'];
            }
        }
        return ['code' => 1,'msg'=>'每月的第一天才能进行结算'];
    }

}
