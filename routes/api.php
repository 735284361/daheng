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

Route::any('/wechat/login', 'WeChatController@login');
Route::any('/wechat/register', 'WeChatController@register');

Route::group(['prefix' => 'v1'], function () {
    Route::get('test','TestController@test');
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
            Route::get('detail','OrderController@detail');
        });

        // 代理商
        Route::group(['prefix' => 'agent'], function() {
            Route::get('detail','AgentController@getAgentInfo');
            Route::post('apply','AgentController@apply');
            Route::get('statistics','AgentController@statistics');
            Route::get('members','AgentController@members');
            Route::get('qrcode','AgentController@getQrcode');
            Route::get('invite','AgentController@invite')->name('agent.invite');
        });
    });
});


