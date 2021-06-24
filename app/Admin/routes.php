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
    $router->resource('m-p-video-items', MPVideoController::class);

});
