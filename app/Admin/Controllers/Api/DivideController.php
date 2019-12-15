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

    public function divide()
    {
//        if (Carbon::now() == Carbon::now()->firstOfMonth()) {
        if (Carbon::now() != Carbon::now()->firstOfMonth()) {
            $agentList = Agent::where('status',Agent::STATUS_NORMAL)->get();
            foreach ($agentList as $agent) {
                // 结算上个月最后一天之前的数据
                $userId = $agent->user_id;
                DB::transaction(function () use ($userId) {
                    $this->agentService->agentOrderSettle($userId);
                });
            }
        }
        return;
    }

}
