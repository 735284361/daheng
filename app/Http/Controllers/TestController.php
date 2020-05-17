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
use App\Services\ShareService;
use App\Services\UserAccountService;
use App\Services\UserService;
use App\User;
use Carbon\Carbon;
use Faker\Generator;
use iBrand\Miniprogram\Poster\MiniProgramShareImg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use phpseclib\System\SSH\Agent;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Overtrue\EasySms\PhoneNumber;

class TestController extends Controller
{
    //

    public function test(Request $request)
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
//        $order = Order::where('order_no','GM2020051120161728231')->first();
////        CompleteOrder::dispatch($order);
//        $orderService = new OrderService();
//        $orderService->confirmOrder($order);

//        // 订单手动分成
//        $orderNo = $request->order_no;
//        $agentService = new AgentService();
//        $agentService->orderCommission($orderNo);

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
//        $url = route('share.goods');
//        $result = MiniProgramShareImg::run($goods, $url, true);
//        return $result;
//        $url = 'https://cqyldh.oss-cn-chengdu.aliyuncs.com/images/2ad26007701fd8db8b48884132373e2d.jpeg';
//        $url = route('share.goods');
        // 小程序码海报
//        $user = auth('api')->user();
//        $goods = Goods::find(2);
//        $xcxurl =  base_path().'/public/upload/images/5e393507cdb47.png';
////        $img = $this->getGoodsImageMaker($goods,$share,$xcxurl);
//        $img = ShareService::getGoodsImageMaker($goods,$user,$xcxurl);
//        return $img->response('png');

        // 代理
//        $agentInfo = AgentMember::whereHas('agent')->where('user_id',9)->first();


//        echo Carbon::now()->subDays(5);

//        $Order = new OrderService();
//        $order = Order::find(161);
//        $Order->test($order,Order::STATUS_SHIPPED);

//        UserService::bindPhone('17600296638');

//        return Order::with('agentInfo')->where(['order_no'=>'GM2020050621573816370'])->first();

        // 重新统计用户消费
//        $this->countMemberAmount();

    }

    public function auth_test()
    {

        return UserService::bindPhone('17600296610');
        // 商品码
//        $id = 10;
//        $user = auth()->user();
//        $goods = Goods::find($id);
////        $xcxurl =  base_path().'/public/upload/images/5e393507cdb47.png';
//
//        $xcxurl = ShareService::getGoodsShareQrCode(2,auth('api')->id());
//        $img = ShareService::getGoodsImageMaker($goods,$user,$xcxurl);
//        return $img->response('png');


        // 分销码

//        $user = auth()->user();
//        $agent = new AgentService();
//        $userId = '100635';
//        $xcxurl = $agent->getOnlyQrCode($userId);
//        return $xcxurl;
//        $img = ShareService::getAgentCode($user,$xcxurl);
//        return $img;
    }

    /**
     * 批量重新统计顾客消费
     */
//    public function countMemberAmount()
//    {
//
//        $agentOrderMaps = AgentOrderMaps::with('order')
//            ->where('status',AgentOrderMaps::STATUS_DIVIDE_SETTLED)
//            ->get();
//        foreach ($agentOrderMaps as $order) {
//            AgentMember::where('user_id',$order->order->user_id)->increment('amount',$order->order->order_amount_total);
//        }
//
//    }

    public function addDeliverOrder()
    {
        $app = \EasyWeChat::miniProgram();
        $access_token = $app->access_token;
        $openid = 'opjVL5GvAS-NsWVFQ6CxaOtpXAMU';
        $delivery_id = 'TEST';
        $order_id = 'GM2020050617020393547';

        $sender = [
            'name' => '大亨',
            'mobile' => '18380448817',
            'province' => '重庆市',
            'city' => '重庆市',
            'area' => '江北',
            'address' => '观音桥22号楼2201'
        ];

        $receiver = [
            'name' => '饶后海',
            'mobile' => '17600296638',
            'province' => '北京市',
            'city' => '北京市',
            'area' => '朝阳区',
            'address' => '长楹星座1栋2201'
        ];

        $detail_list = [
            [
                'name' => '商品1',
                'count' => '2'
            ],
            [
                'name' => '商品2',
                'count' => '5'
            ]
        ];
        $cargo = [
            'count' => 7,
            'weight' => 2,
            'space_x' => 100,
            'space_y' => 100,
            'space_z' => 100,
            'detail_list' => $detail_list
        ];

        $shop = [
            'wxa_path' => '/pages/home/index',
            'img_url' => 'https://cqyldh.oss-cn-chengdu.aliyuncs.com/images/%E6%8B%9B%E7%89%8C%E8%BE%A3%E8%BE%A3.jpg',
            'goods_name' => '原味鸭掌',
            'goods_count' => '12'
        ];

        $insured = [
            'use_insured' => 0,
            'insured_value' => 0,
        ];

        $service = [
            'service_type' => '1',
            'service_name' => 'test_service_name'
        ];

        $expect_time = time() + 12 * 3600;
        // 生成运单
        $data = [
            'access_token' => $access_token,
            'add_source' => '0',
            'order_id' => $order_id,
            'openid' => $openid,
            'delivery_id' => $delivery_id,
            'biz_id' => 'test_biz_id',
            'custom_remark' => '这是订单备注',
            'sender' => $sender,
            'receiver' => $receiver,
            'cargo' => $cargo,
            'shop' => $shop,
            'insured' => $insured,
            'service' => $service,
            'expect_time' => $expect_time,
        ];
        return $app->express->createWaybill($data);
    }

    public function getDeliverInfo()
    {
        $app = \EasyWeChat::miniProgram();
        $access_token = $app->access_token;
        $openid = 'opjVL5GvAS-NsWVFQ6CxaOtpXAMU';
        $delivery_id = 'TEST';
        $order_id = 'GM2020050617020393547';
        $waybill_id = 'GM2020050617020393547_1588906184_waybill_id';

        $data = [
            'access_token' => $access_token,
            'order_id' => $order_id,
            'openid' => $openid,
            'delivery_id' => $delivery_id,
            'waybill_id' => $waybill_id
        ];
        return $app->express->getWaybillTrack($data);
    }



}
