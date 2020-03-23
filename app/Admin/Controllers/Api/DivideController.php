<?php

namespace App\Admin\Controllers\Api;

use App\Models\Agent;
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
        if (Carbon::today() != Carbon::now()->firstOfMonth()) {
            $agentList = Agent::where('status',Agent::STATUS_NORMAL)->get();
            foreach ($agentList as $agent) {
                // 结算上个月最后一天之前的数据
                $userId = $agent->user_id;
                DB::transaction(function () use ($userId) {
                    // 奖金结算
                    $this->agentService->agentOrderSettle($userId);
                });
            }
        }
        return;
    }

    public function teamDivide()
    {
        if (Carbon::today() != Carbon::now()->firstOfMonth()) {
            $agentList = Agent::where('status',Agent::STATUS_NORMAL)->get();
            foreach ($agentList as $agent) {
                // 结算上个月最后一天之前的数据
                $userId = $agent->user_id;
                DB::transaction(function () use ($userId) {
                    // 奖金结算
                    $this->agentService->agentOrderSettle($userId);
                });
            }
        }
        return;
    }

}
