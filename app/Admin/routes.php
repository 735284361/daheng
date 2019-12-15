<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('sys-params', SysParamsController::class);
    $router->resource('banners', BannerController::class);
    $router->resource('goods', GoodsController::class);
    $router->resource('goods-categories', GoodsCategoryController::class);
    $router->resource('sys-pics', SysPicController::class);
    $router->resource('users', UsersController::class);
    $router->resource('shipping-fees', ShippingFeeController::class);
    $router->resource('orders', OrderController::class);

    // 订单
    $router->group(['order' => 'delivery'], function($router) {
        $router->post('update-status','Api\OrderController@updateOrderStatus')->name('admin.order.update-status');
        $router->post('delivery','Api\OrderController@delivery')->name('admin.order.delivery');
    });
    // 快递
    $router->group(['prefix' => 'delivery'], function($router) {
        $router->get('list','Api\DeliveryController@listProviders')->name('admin.deliver.list');
    });
    // 月度分成
    $router->get('divide/divide','Api\DivideController@divide')->name('admin.divide.divide');
});
