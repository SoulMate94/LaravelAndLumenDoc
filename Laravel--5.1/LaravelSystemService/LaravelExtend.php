<?php

简介#
//扩展包是添加功能到 Laravel 的主要方式。扩展包可以包含许多好用的功能，像 Carbon 可用于处理时间，或像 Behat 这种完整的 BDD 测试框架。


服务提供者#
//服务提供者 是你的扩展包与 Laravel 连接的重点
//服务提供者负责绑定一些东西至 Laravel 的 服务容器 并告知 Laravel 要从哪加载扩展包的资源，例如视图、配置文件、语言包
服务提供者继承了 Illuminate\Support\ServiceProvider 类并包含了两个方法：register 及 boot
基底的 ServiceProvider 类被放置在 Composer 的 illuminate/support 扩展包，你必须将它加入至你自己的扩展包依赖中。


路由#
//要为你的扩展包定义路由，只需简单的在扩展包的服务提供者的 boot 方法 require 路由文件
在你的路由文件中，你可以如同在一般的 Laravel 应用程序中一样使用 Route facade 来 注册路由：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    if (! $this->app->routesAreCached()) {
        require __DIR__.'/../../routes.php';
    }
}

资源#
视图#
//若要在 Laravel 中注册扩展包 视图，则必须告诉 Laravel 你的视图位置
//你可以使用服务提供者的 loadViewsFrom 方法来实现。loadViewsFrom 方法允许两个参数：视图模板路径与扩展包名称
例如，如果你的扩展包名称是「courier」，你可以按照以下方式将其添加至服务提供者的 boot 方法内：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');
}

//扩展包视图参照使用了双分号 package::view 语法。所以，你可以通过如下方式从 courier 扩展包中加载 admin 视图：
Route::get('admin', function () {
    return view('courier::admin');
});

重写扩展包视图#
当你使用 loadViewsFrom 方法时，Laravel 实际上为你的视图注册了 两个 位置：一个是应用程序的 resources/views/vendor 目录，另一个是你所指定的目录
//所以，以 courier 为例：当用户请求一个扩展包的视图时，Laravel 会在第一时间检查 resources/views/vendor/courier 是否有开发者提供的自定义版本视图存在。接着，如果这个路径没有自定义的视图，Laravel 会搜索你在扩展包 loadViewsFrom 方法里所指定的视图路径


发布视图#
//若要发布扩展包的视图至 resources/views/vendor 目录，则必须使用服务提供者的 publishes 方法
publishes 方法允许一个包含扩展包视图路径及对应发布路径的数组。/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');

    $this->publishes([
        __DIR__.'/path/to/views' => base_path('resources/views/vendor/courier'),
    ]);
}

现在，当你的扩展包用户运行 Laravel 的 vendor:publish Artisan 命令时，扩展包的视图将会被复制到指定的位置上。

语言#
//如果你的扩展包里面包含了 语言文件，则可以使用 loadTranslationsFrom 方法来告知 Laravel 该如何加载它们
//举个例子，如果你的扩展包名称为「courier」，你可以按照以下方式将其添加至服务提供者的 boot 方法：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');
}
//扩展包翻译参照使用了双分号 package::file.line 语法。所以，你可以按照以下方式来加载 courier 扩展包中的 messages 文件 welcome 语句：
echo trans('courier::messages.welcome');

发布语言包#
//如果你想将扩展包的语言包发布至应用程序的 resources/lang/vendor 目录，则可以使用服务提供者的 publishes 方法。
//publishes 方法接受一个包含扩展包路径及对应发布位置的数组。例如，在我们的 courier 扩展包中发布语言包：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');

    $this->publishes([
        __DIR__.'/path/to/translations' => base_path('resources/lang/vendor/courier'),
    ]);
}

现在，当使用你扩展包的用户运行 Laravel 的 vendor:publish Artisan 命令时，扩展包的语言包将会被复制到指定的位置上。


配置文件#
//有时候，你可能想要将扩展包的配置文件发布到应用程序本身的 config 目录上
这能够让扩展包的用户轻松的重写这些默认的设置选项。如果要发布扩展包的配置文件，只需要在服务提供者里的 boot 方法内使用 publishes 方法：

/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->publishes([
        __DIR__.'/path/to/config/courier.php' => config_path('courier.php'),
    ]);
}

//现在当扩展包的用户使用 Laravel 的 vendor:publish 命令时，扩展包的文件将会被复制到指定的位置上。当然，只要你的配置文件被发布，就可以如其它配置文件一样被访问：
$value = config('courier.option');

默认扩展包配置文件#
//你也可以选择合并你的扩展包配置文件和应用程序里的副本配置文件
//这样能够让你的用户在已经发布的副本配置文件中只包含他们想要重写的设置选项。如果想要合并配置文件，可在服务提供者里的 register 方法里使用 mergeConfigFrom 方法：
/**
 * 在容器中注册绑定。
 *
 * @return void
 */
public function register()
{
    $this->mergeConfigFrom(
        __DIR__.'/path/to/config/courier.php', 'courier'
    );
}

公用资源文件#
//你的扩展包内可能会包含许多的资源文件，像 JavaScript、CSS 和图片等文件
//如果要发布这些资源文件到应用程序的 public 目录上，只需使用服务提供者的 publishes 方法
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->publishes([
        __DIR__.'/path/to/assets' => public_path('vendor/courier'),
    ], 'public');
}

//现在当扩展包的用户运行 vendor:publish 命令时，资源文件将会被复制到指定的位置上。每次当扩展包需要更新重写资源文件时，可以使用 --force 来标记：
php artisan vendor:publish --tag=public --force
如果你想要确保公用资源文件始终保持在最新的版本，可以将此命令加入 composer.json 文件中的 post-update-cmd 列表。

发布分类文件#
//你可能想要分别发布分类的扩展包资源文件或是资源
//举例来说，你可能想让用户不用发布扩展包的所有资源文件，只需要单独发布扩展包的配置文件即可
//这可以通过在调用 publishes 方法时使用「标签」来实现。例如，让我们在扩展包的服务提供者中的 boot 方法定义两个发布群组：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->publishes([
        __DIR__.'/../config/package.php' => config_path('package.php')
    ], 'config');

    $this->publishes([
        __DIR__.'/../database/migrations/' => database_path('migrations')
    ], 'migrations');
}
//现在当你的用户使用 vendor:publish Artisan 命令时，就可以通过标签名称分别发布不同分类的资源文件：
php artisan vendor:publish --provider="Vendor\Providers\PackageServiceProvider" --tag="config"
