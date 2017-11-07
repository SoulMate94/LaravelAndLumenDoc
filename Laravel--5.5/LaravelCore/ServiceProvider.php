<?php

服务提供者#
简介#
//我们说的「引导」其实是指注册，比如注册服务容器绑定、事件监听器、中间件，甚至是路由的注册。服务提供器是配置你的应用程序的中心。

Laravel 的 config/app.php 文件中有一个 providers 数组。数组中的内容是应用程序要加载的所有服务提供器类

编写服务提供者#
//所有服务提供器都会继承 Illuminate\Support\ServiceProvider 类。大多数服务提供器都包含 register 和 boot 方法。在 register 方法中，你只需要绑定类到 服务容器中。而不需要尝试在 register 方法中注册任何事件监听器、路由或任何其他功能。
使用 Artisan 命令行界面，通过 make:provider 命令生成一个新的提供器：
php Artisan make:provider RiakServiceProvider

注册方法#
在 register 方法中，你只需要将类绑定到 服务容器 中
//而不需要尝试在 register 方法中注册任何事件监听器、路由或者任何其他功能。否则，你可能会意外使用到尚未加载的服务提供器提供的服务
让我们来看一个基本的服务提供器。在你的任何服务提供器方法中，你可以通过 $app 属性来访问服务容器：

<?php
namespace App\provider
use Riad\Connection;
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
// 这个服务提供器只定义了一个 register 方法，并使用该方法在服务容器中定义了一个 Riak\Connection 实现

引导方法#
那么，如果我们需要在我们的服务提供器中注册一个视图组件呢？这应该在 boot 方法中完成
此方法在所有其他服务提供器都注册之后才能调用，这意味着你可以访问已经被框架注册的所有服务：

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider  extends ServiceProvider
{
	/**
	 * 引导任何应用程序服务
	 *  @return void
	 */

    public function boot()
    {
        view()->composer('view', function () {
            //
        });
    }
}

引导方法依赖注入#
//你可以为服务提供器的 boot 方法设置类型提示。服务容器 会自动注入你需要的任何依赖项：
use Illuminate\Contracts\Routing\ResponseFactory;

public function boot(ResponseFactory $response)
{
    $response->macro('caps', function ($value) {
        //
    });
}

注册提供者#
所有服务提供器都在 config/app.php 配置文件中注册
//该文件中有一个 providers 数组，用于存放服务提供器的类名
//。默认情况下，这个数组列出了一系列 Laravel 核心服务提供器。这些服务提供器引导 Laravel 核心组件，例如邮件、队列、缓存等。
要注册提供器，只需要将其添加到数组：
'providers' => [
    // 其他服务提供器

    App\Providers\ComposerServiceProvider::class,
],

延迟的提供者#
如果你的提供器仅在 服务容器 中注册绑定，就可以选择推迟其注册，直到当它真正需要注册绑定
//推迟加载这种提供器会提高应用程序的性能，因为它不会在每次请求时都从文件系统中加载。

//要延迟提供器的加载，请将 defer 属性设置为 true ，并定义 provides 方法。provides 方法应该返回由提供器注册的服务容器绑定：
<?php

namespace App\Providers;

use Riak\Connection;
use Illuminate\Support\ServiceProvider;

class RiakServiceProvider extends ServiceProvider
{
    /**
     * 是否延时加载提供器。
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * 注册服务提供器。
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Connection::class, function ($app) {
            return new Connection($app['config']['riak']);
        });
    }

    /**
     * 获取提供器提供的服务。
     *
     * @return array
     */
    public function provides()
    {
        return [Connection::class];
    }

}