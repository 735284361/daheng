<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AgentController extends Controller
{
    //

    protected $agentService;

    public function __construct()
    {
        $this->agentService = new AgentService();
    }

    public function getAgentInfo()
    {
        return $this->agentService->getAgentInfo(auth('uid')->id());
    }

    public function apply()
    {
        return $this->agentService->applyAgent();
    }

    public function statistics()
    {
        $this->agentService->statistics(auth('api')->id());
    }

    public function members()
    {
        return $this->agentService->agentMembers(auth('api')->id());
    }

    public function getQrCode()
    {
        $app = \EasyWeChat::miniProgram();
        $response = $app->app_code->get('pages/distribution/code/code');
//        $path = storage_path('qrcode');
        $path = storage_path('app/public/qrcord/');
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $filename = $response->saveAs($path, auth('api')->id().'.png');
        }
        dd(env('APP_URL').$path.$filename);
    }

    public function invite(Request $request)
    {
        $this->validate($request,['id'=>'exists:agent,user_id']);

    }

}
