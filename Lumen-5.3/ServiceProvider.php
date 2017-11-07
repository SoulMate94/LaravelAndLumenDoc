<?php

# 注册方法
namespace App\Providers;

use Riak\Connection;
use Illuminate\Support\ServiceProvider;

class RiakServiceProvider extends ServiceProvider
{
    /**
     * 在容器中注册绑定
     *
     * @return void
     */
    public function register()
    {
        $this->app->singelton(Connection::class, function($app) {
            return new Connection(config('riak'));
        });
    }
}

# 启动方法
// namespace App\Providers;

use Queue;
// use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // 其他服务提供者的属性

    /**
     * 运行注册后的启动服务
     *
     * @return void
     */
    public function boot()
    {
        Queue::failing(function ($event) {

        });
    }
}

# 注册提供者
// 所有的服务提供者都在 bootstrap/app.php 中被注册。你也许需要额外的调用 $app->register() 来注册你的服务提供者。

