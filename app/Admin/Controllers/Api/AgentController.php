<?php

namespace App\Admin\Controllers\Api;

use App\Services\AgentService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgentController extends Controller
{
    //

    protected $agentService;

    public function __construct()
    {
        $this->agentService = new AgentService();
    }

    /**
     * 更新代理商状态
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateStatus(Request $request)
    {
        $this->validate($request,['id' => 'required|integer','status' => 'required']);

        $res = $this->agentService->updateAgentStatus($request->id,$request->status);

        $res ? $code = 0 : $code = 1;
        return ['code' => $code];
    }
}
