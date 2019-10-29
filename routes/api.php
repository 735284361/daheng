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

Route::any('/wechat/login', 'WeChatController@login');
Route::any('/wechat/register', 'WeChatController@register');

//前端小程序拿到的地址：https://域名/api/v1/自己写的接口
Route::group(['prefix' => '/v1','middleware' => 'auth:api'], function () {
    Route::any('/profile', 'WeChatController@profile');
});

Route::group(['prefix' => 'v1'], function () {
    // 系统参数
    Route::get('config/value','SysParamController@value');
    // 轮播
    Route::get('banner/list','BannerController@getList');

    // 商城
    Route::get('shop/goods/list','ShopController@lists');
    Route::get('shop/goods/detail','ShopController@detail');
    Route::get('shop/goods/price','ShopController@price');
});
