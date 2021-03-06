<?php

#服务提供者#
#简介
//服务提供者是所有 Laravel 应用程序启动的中心所在。包括你自己的应用程序，以及所有的 Laravel 核心服务，都是通过服务提供者启动的。
//但我们所说的「启动」指的是什么？一般而言，我们指的是 注册 事物，包括注册服务容器绑定、事件侦听器、中间件，甚至路由。服务提供者是设置你的应用程序的中心所在。
若你打开 Laravel 的 config/app.php 文件，你将会看到 providers 数组

#编写服务提供者
//所有的服务提供者都继承了 Illuminate\Support\ServiceProvider 类。这个抽象类要求你在你的提供者上定义至少一个方法：register
//在 register 方法中，你应该 只将事物绑定至 服务容器 之中。永远不要试图在 register 方法中注册任何事件侦听器、路由或任何其它功能。
//Artisan 命令行接口可以很容易地通过 make:provider 命令生成新的提供者：
php artisan make:provider RiakServiceProvider


#注册方法
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
        $this->app->singleton('Riak\Contracts\Connection', function ($app) {
            return new Connection(config('riak'));
        });
    }
}
//此服务提供者只定义了一个 register 方法，并在服务容器中使用此方法定义了一份 Riak\Contracts\Connection 的实现

#启动方法
//因此，若我们需要在我们的服务提供者中注册一个视图 composer 则应该在 boot 方法中完成
//此方法会在所有其它的服务提供者被注册后才被调用，意味着你能访问已经被框架注册的所有其它服务：
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * 运行注册后的启动服务。
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('view', function () {
            //
        });
    }

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

//启动方法依赖注入
//我们可以为我们 boot 方法中的依赖作类型提式。服务容器 会自动注入你所需要的任何依赖：
use Illuminate\Contracts\Routing\ResponseFactory;

public function boot(ResponseFactory $factory)
{
    $factory->macro('caps', function ($value) {
        //
    });
}

#注册提供者
//所有的服务提供者都在 config/app.php 配置文件中被注册
//这个文件包含了一个 providers 数组，你可以在其中列出你所有服务提供者的名称。此数组默认会列出一组 Laravel 的核心服务提供者
'providers' => [
    // 其它的服务提供者

    App\Providers\AppServiceProvider::class,
],


#延迟提供者
//若你的提供者 仅 在 服务容器 中注册绑定，你可以选择延缓其注册，直到真正需要其中已注册的绑定，延迟提供者加载可提高应用程序的性能。
//要延迟提供者加载，可将 defer 属性设置为 true，并定义一个 provides 方法
//provides 方法会返回提供者所注册的服务容器绑定：
<?php

namespace App\Providers;

use Riak\Connection;
use Illuminate\Support\ServiceProvider;

class RiakServiceProvider extends ServiceProvider
{
    /**
     * 指定提供者加载是否延缓。
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * 注册服务提供者。
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Riak\Contracts\Connection', function ($app) {
            return new Connection($app['config']['riak']);
        });
    }

    /**
     * 获取提供者所提供的服务。
     *
     * @return array
     */
    public function provides()
    {
        return ['Riak\Contracts\Connection'];
    }

}
