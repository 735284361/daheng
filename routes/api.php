<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Route::get('v1/address/saveAddress','AddressController@postAddress');


Route::get('test','TestController@test');

Route::group(['prefix' => 'v1'], function () {
    // 登录注册
    Route::any('/wechat/login', 'WeChatController@login');
    Route::any('/wechat/register', 'WeChatController@register');
    // 系统参数
    Route::get('config/value','SysParamController@value');
    // 轮播
    Route::get('banner/list','BannerController@getList');
    // 商城
    Route::get('shop/goods/list','ShopController@lists');
    Route::get('shop/goods/detail','ShopController@detail');
    Route::get('shop/goods/price','ShopController@price');
    Route::get('shop/goods/goods_stock','ShopController@getGoodsStock');
    Route::get('shop/shipping/fee','ShopController@shippingFee');
    Route::get('shop/category/list','CategoryController@lists');

    // 支付
    Route::group(['prefix' => '/pay'], function () {
        Route::group(['middleware' => ['auth:api']], function() {
            Route::any('/pay', 'PayController@pay');
        });
        Route::any('/callback', 'PayController@callback');
        Route::any('/refund', 'PayController@refund');
    });

    // 验证登录
    Route::group([ 'middleware'=>['auth:api']], function() {
//        Route::get('test','TestController@test');
        // 用户地址
        Route::group(['prefix' => 'address'], function() {
            Route::get('list','AddressController@lists');
            Route::get('detail','AddressController@detail');
            Route::post('saveAddress','AddressController@postAddress');
            Route::get('delete','AddressController@delete');
            Route::post('setDefault','AddressController@setDefault');
            Route::get('default','AddressController@default');
        });

        // 订单
        Route::group(['prefix' => 'order'], function() {
            Route::post('create','OrderController@create');
            Route::get('statistics','OrderController@statistics');
            Route::get('list','OrderController@lists');
            Route::get('close','OrderController@closeOrder');
            Route::get('confirm','OrderController@confirmOrder');
            Route::post('repay','OrderController@repay');
            Route::get('detail','OrderController@detail');
            Route::post('reputation','OrderController@reputation');
        });

        // 代理商
        Route::group(['prefix' => 'agent'], function() {
            Route::get('detail','AgentController@getAgentInfo');
            Route::get('viewAgentRight','AgentController@getAgentViewRight');
            Route::post('apply','AgentController@apply');
            Route::get('statistics','AgentController@statistics');
            Route::get('members','AgentController@members');
            Route::get('qrcode','AgentController@getQrcode');
            Route::any('inviteMember','AgentController@inviteMember')->name('agent.inviteMember');
            Route::get('orders','AgentController@orders');
            Route::get('getAgentUserInfo','AgentController@getAgentUserInfo');
        });

        // 团队
        Route::group(['prefix' => 'agentTeam'], function() {
            Route::post('applyTeam','AgentController@applyTeam');
            Route::get('teamInfo','AgentController@teamInfo');
            Route::get('getTeamLeaderInfo','AgentController@getTeamLeaderInfo');
            Route::get('teamQrCode','AgentController@teamQrCode');
            Route::get('joinTeam','AgentController@joinTeam');
        });

        // 账单
        Route::group(['prefix' => 'bill'], function() {
            Route::get('list','UserBillController@lists');
        });

        // 用户
        Route::group(['prefix' => 'user'], function() {
            Route::get('account','UserController@getAccount');
            Route::post('feedback','UserController@feedback');
            Route::get('feedback/types','UserController@getFeedBackTypes');
            Route::post('withdraw/apply','WithdrawController@apply');
        });
    });
});


