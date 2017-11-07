<?php

#服务提供者
//所有的 Lumen 核心服务，都是通过服务提供者启动的
//但我们所说的「启动」指的是什么？一般而言，我们指的是 注册 事物，包括注册服务容器绑定、事件侦听器、中间件，甚至路由。服务提供者是设置你的应用程序的中心所在

若你打开 Lumen 的 bootstrap/app.php 文件，你将会看到 $app->register() 调用。你也许需要额外的调用来注册你的服务提供者

#编写服务提供者
所有的服务提供者都继承了 Illuminate\Support\ServiceProvider 类。这个抽象类要求你在你的提供者上定义至少一个方法：register。在 register 方法中，你应该 只将事物绑定至 服务容器 之中。永远不要试图在 register 方法中注册任何事件侦听器、路由或任何其它功能。


<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}


#注册方法
//如同之前提到的，在 register 方法中，你应该只将事物绑定至 服务容器 中。永远不要尝试在 register 方法中注册任何事件侦听器、路由或任何其它功能。否则的话，你可能会意外地使用到由尚未加载的服务提供者所提供的服务。

//现在，让我们来看看基本的服务提供者：

<?php

namespace App\Providers;

use Riak\Connection;
use Illuminate\Support\ServiceProvider;

class RiakServiceProvider extends ServiceProvider
{
    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Connection::class, function ($app) {
            return new Connection(config('riak'));
        });
    }
}

//此服务提供者只定义了一个 register 方法，并在服务容器中使用此方法定义了一份 Riak\Contracts\Connection 的实现


#启动方法
因此，若我们需要在我们的服务提供者中注册一个视图 composer 则应该在 boot 方法中完成。此方法会在所有其它的服务提供者被注册后才被调用，意味着你能访问已经被框架注册的所有其它服务

<?php

namespace App\Providers;

use Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // 其他服务提供者的属性...

    /**
     *  运行注册后的启动服务。
     *
     * @return void
     */
    public function boot()
    {
        Queue::failing(function ($event) {

        });
    }
}


#注册提供者
所有的服务提供者都在 bootstrap/app.php 中被注册。你也许需要额外的调用 $app->register() 来注册你的服务提供者。