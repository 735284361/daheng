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

});
