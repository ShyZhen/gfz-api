<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.as'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');
    $router->resource('users', UserController::class);
    $router->resource('timelines', TimelineController::class);
    $router->resource('reports', ReportController::class);
    $router->resource('report-apps', ReportAppController::class);
    $router->resource('comments', CommentController::class);
    $router->resource('coupons', CouponController::class);

    // 视频小程序
    $router->resource('m-p-video-items', MPVideoController::class);

    // 王者荣耀
    $router->resource('m-p-wangzhe-draws', MPWZDrawController::class);
    $router->resource('m-p-wangzhe-skins', MPWZSkinController::class);
    $router->resource('m-p-wangzhe-skin-logs', MPWZSkinLogController::class);
    $router->resource('m-p-wangzhe-skin-converts', MPWZSkinConvertController::class);
    $router->resource('m-p-wangzhe-platforms', MPWZPlatformController::class);

});
