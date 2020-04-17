<?php

namespace App\Http\Controllers;

use App\Jobs\CompleteOrder;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\Goods;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Notifications\VerificationCode;
use App\Services\AdminMsgService;
use App\Services\AgentService;
use App\Services\MessageService;
use App\Services\OrderService;
use App\Services\UserAccountService;
use App\User;
use Carbon\Carbon;
use Faker\Generator;
use iBrand\Miniprogram\Poster\MiniProgramShareImg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use phpseclib\System\SSH\Agent;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Overtrue\EasySms\PhoneNumber;

class TestController extends Controller
{
    //

    public function test()
    {
        // 支付成功消息发送
//        $order = Order::find(14);
//        return MessageService::paySuccessMsg($order);
        // 支付成功
//        $orderService = new OrderService();
//        $orderService->paySuccess($order);

        // 用户账单
//        $order = Order::where('order_no','GM2019112614502267154')->first();
//        $order->bill()->create([
//            'user_id' => 8,
//            'amount' => 100,
//            'amount_type' => -1,
//            'status' => 1,
//        ]);

        // 用户代理商判断
//        $user = AgentMember::where('user_id',1)->first();

//        $agent = AgentMember::where('user_id',8)->first();
//        if ($agent) { // 如果存在代理关系 则进入代理流程
//            // 佣金计算
//            $orderGoods = OrderGoods::where('order_no','GM2019112614502267154')->get();
//            $commission = 0;
//            foreach ($orderGoods as $goods) {
//                $commission += $goods->product_count * $goods->dist_price;
//            }
//            // 添加代理订单关系
//            $agentOrder = new AgentOrderMaps();
//            $agentOrder->agent_id = $agent->agent_id;
//            $agentOrder->order_no = 'GM2019112614502267154';
//            $agentOrder->commission = $commission;
//            $agentOrder->save();
//        }

//        $order = Order::find(11);
//        CompleteOrder::dispatch($order);

//        $account = new UserAccountService();
//        DB::enableQueryLog();
//        $account->incBalance(8,100);
//        $sql = DB::getQueryLog();
//        dd($sql);

        // 代理
//        $agent = new AgentService();
//        DB::enableQueryLog();
//        $id = 8;
//        return $agent->statistics();
//        return $agent->getAgentInfo(9);
//        $agent->applyAgent();
//        $agent->agentMembers($id);
//        return $agent->getQrCode($id);
//        $agent->agentOrderList(8);

//        dd(DB::getQueryLog());

//        dd(route('admin.divide.divide'));

//        dd(date('Y-m-d H:i:s'));

//        echo date('n',strtotime('-1 month'));

//        $sms = app('easysms');
//        try {
//            $res = $sms->send(17600296638, [
//                'content'  => '【HR百科互助社】尊敬的VIP会员，您好！互助社重磅推出HR百科大学，现正火热报名中。会员权益再升级：1000+名师精品课免费畅听，3大实战训练营免费参加，优秀学员名企职位推荐，优质校友会人脉资源。报名微信：huzhushe2019，退订回N',
//                'template' => 'whUvF1',
//                'data' => [
////                    'type' => '提现',
//                ],
//            ]);
//            dd($res);
//        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
//            $message = $exception->getResults();
//            dd($message);
//        }


//        AdminMsgService::sendAgentApplyMsg();
//
////        $user = User::find(8);
////        $res = $user->notify(new VerificationCode());
//
//        dd($res);


//        echo Carbon::now()->format('Ym');
//        $agent = new AgentService();
//        $agent->saveAgentBill('8',100);

//        return AgentMember::with('agent')->where('user_id',9)->first();

//        echo Carbon::now()->subMonth()->format('Ym');

//        echo AgentService::agentConsumeCon();

//        $goodsList = OrderGoods::with('goods')->where('order_no','GM2020032318495276752')->get();
////        $goodsList = $goodsList->toArray();
//        $billName = [];
//        foreach ($goodsList as $goods) {
//            in_array($goods->goods->name, $billName) ? '' : $billName[] = $goods->goods->name;
//
//        }
//        $billName = implode($billName,',');
//        dd($billName);

//        $agentService = new AgentService();
//        $list = $agentService->getTeamSalesVolume('10','202002');
//        $salesVolume = $list->sum('sales_volume');
//        $divideAmount = $list->sum('divide_amount');
//        dd($list);

//        echo 123;
//        $goods = Goods::find(2);
//        $url = route('share.goods');
//        $result = MiniProgramShareImg::run($goods, $url, true);
//        return $result;
//        $url = 'https://www.baidu.com/';
        $url = route('share.goods');
        $result = MiniProgramShareImg::generateShareImage($url);

        // 代理
//        $agentInfo = AgentMember::whereHas('agent')->where('user_id',9)->first();
    }

    /** @test */
    public function TestConfig()
    {
        $config = config('filesystems.disks');

        $this->assertArrayHasKey('MiniProgramShare', $config);

        $this->assertArrayHasKey('qiniu', $config);
    }

    /** @test */
    public function TestGenerateShareImage()
    {
        config(['ibrand.miniprogram-poster.width' => '1300px']);

        $url    = 'https://www.ibrand.cc/';
        $result = MiniProgramShareImg::generateShareImage($url);
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));

        $result = MiniProgramShareImg::generateShareImage('');
        $this->assertFalse($result);
    }

    /** @test */
    public function TestShareImageV2()
    {
        config(['ibrand.miniprogram-poster.width' => '1300px']);

        $url   = 'https://www.ibrand.cc/';
        $goods = GoodsTestModel::find(1);

        //1. first build.
        $result  = MiniProgramShareImg::run($goods, $url);
        $oldPath = $result['path'];
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertEquals(1, count($goods->posters));

        //2. rebuild and delete old.
        $result   = MiniProgramShareImg::run($goods, $url, true);
        $oldPath2 = $result['path'];
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertFalse(Storage::disk('MiniProgramShare')->exists($oldPath));
        $this->assertEquals(1, count($goods->posters));

        //3. rebuild but not delete old.
        $this->app['config']->set('ibrand.miniprogram-poster.delete', false);
        $result = MiniProgramShareImg::run($goods, $url, true);
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($result['path']));
        $this->assertTrue(Storage::disk('MiniProgramShare')->exists($oldPath2));

        $poster = Poster::find(1);
        $this->assertEquals(GoodsTestModel::class, get_class($poster->posterable));
    }

    /** @test */
    public function TestSaveToQiNiu()
    {
        config(['ibrand.miniprogram-poster.default.storage' => 'qiniu']);

        $config = config('ibrand.miniprogram-poster');
        $this->assertSame($config['default']['storage'], 'qiniu');

        $url    = 'https://www.ibrand.cc/';
        $result = MiniProgramShareImg::generateShareImage($url);
        $this->assertTrue(Storage::disk('qiniu')->exists($result['path']));

        $goods  = GoodsTestModel::find(1);
        $result = MiniProgramShareImg::run($goods, $url);
        $this->assertTrue(Storage::disk('qiniu')->exists($result['path']));
        $this->assertEquals(1, count($goods->posters));
    }

}
