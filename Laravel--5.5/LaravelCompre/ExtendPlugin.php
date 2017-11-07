<?php

Laravel 的扩展插件开发指南#
简介#


Facade 注解#


扩展包发现#
在 Laravel 应用程序的 config/app.php 配置文件中，providers 选项定义了应该被 Laravel 加载的服务提供者的列表
你需要让你的服务提供者包含在这个列表里。而不是要求用户手动将你的服务提供者添加到这个列表里，你可能需要在你扩展包 composer.json 文件的 extra 部分的定义这些提供者
"extra": {
    "laravel": {
        "providers": [
            "Barryvdh\\Debugbar\\ServiceProvider"
        ],
        "aliases": {
            "Debugbar": "Barryvdh\\Debugbar\\Facade"
        }
    }
},

选择扩展包发现#
如果你是扩展包的使用者，你想要禁用一个包的扩展包发现，你可以在应用程序的 composer.json 文件的 extra 部分列出这个扩展包：
"extra": {
    "laravel": {
        "dont-discover": [
            "barryvdh/laravel-debugbar"
        ]
    }
},
你可以通过在应用程序的 dont-discover 指令中使用 * 字符，禁用扩展包发现功能：
"extra": {
    "laravel": {
        "dont-discover": [
            "*"
        ]
    }
},


服务提供者#
服务提供者继承了 Illuminate\Support\ServiceProvider 类并包含了两个方法： register 和 boot. 。基底的 ServiceProvider 类被放置在 Composer 的 illuminate/support 扩展包，你必须将它加入至你自己的扩展包依赖中


资源文件#
配置#
有时候，你可能想要将扩展包的配置文件发布到应用程序本身的 config 目录上。这能够让扩展包的用户轻松的重写这些默认的设置选项
如果要发布扩展包的配置文件，只需要在服务提供者里的 boot 方法内使用 publishes 方法：
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

现在当扩展包的用户使用 Laravel 的 vendor:publish 命令时，扩展包的文件将会被复制到指定的位置上。当然，只要你的配置文件被发布，就可以如其它配置文件一样被访问：
$value = config('courier.option');
!!您不应在配置文件中定义闭包函数。 当用户执行 config:cache Artisan命令时，它们不能正确地序列化

默认的扩展包配置文件#
你也可以选择合并你的扩展包配置文件和应用程序里的副本配置文件。这样能够让你的用户在已经发布的副本配置文件中只包含他们想要重写的设置选项。如果想要合并配置文件，可在服务提供者里的 register 方法里使用 mergeConfigFrom 方法：
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


数据库迁移#
如果你的扩展包包含 数据库迁移 ，你需要使用 loadMigrationsFrom 方法告知 Laravel 如何去加载他们
loadMigrationsFrom 方法只需要你的扩展包的迁移文件路径作为唯一参数：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadMigrationsFrom(__DIR__.'/path/to/migrations');
}

完成扩展包迁移文件注册之后，在运行 php artisan migrate 命令时，它们就会自动被执行。你并不需要把他们导出到应用程序的 database/migrations 目录。


路由#
如果您的扩展包中包含路由，您可以使用 loadRoutesFrom 方法加载它们。此方法将自动确定应用程序的路由是否已缓存，如果路由已缓存，将不会加载路由文件：
/**
 * 执行服务的注册后启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadRoutesFrom(__DIR__.'/routes.php');
}


语言包#
如果你的扩展包里面包含了 本地化 ，则可以使用 loadTranslationsFrom 方法来告知 Laravel 该如何加载它们
举个例子，如果你的扩展包名称为 courier ，你可以按照以下方式将其添加至服务提供者的 boot 方法：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');
}
扩展包翻译参照使用了双分号 package::file.line 语法。所以，你可以按照以下方式来加载 courier 扩展包中的 messages 文件 welcome 语句：
echo trans('courier::messages.welcome');

发布语言包#
如果你想将扩展包的语言包发布至应用程序的 resources/lang/vendor 目录，则可以使用服务提供者的 publishes 方法。
publishes 方法接受一个包含扩展包路径及对应发布位置的数组。例如，在 courier 扩展包中发布语言包，示例如下：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');

    $this->publishes([
        __DIR__.'/path/to/translations' => resource_path('lang/vendor/courier'),
    ]);
}
现在，当使用你扩展包的用户运行 Laravel 的 vendor:publish Artisan 命令时，扩展包的语言包将会被复制到指定的位置上。


视图#
若要在 Laravel 中注册扩展包 视图，则必须告诉 Laravel 你的视图位置。你可以使用服务提供者的 loadViewsFrom 方法来实现
loadViewsFrom 方法允许两个参数：视图模板路径与扩展包名称。例如，如果你的扩展包名称是 courier ，你可以按照以下方式将其添加至服务提供者的 boot 方法内：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');
}
扩展包视图参照使用了双分号 package::view 语法。所以，你可以通过如下方式从 courier 扩展包中加载 admin 视图：
Route::get('admin', function () {
    return view('courier::admin');
});


重写扩展包视图#
当你使用 loadViewsFrom 方法时，Laravel 实际上为你的视图注册了 两个位置：一个是应用程序的 resources/views/vendor 目录，另一个是你所指定的目录

发布视图#
若要发布扩展包的视图至 resources/views/vendor 目录，则必须使用服务提供者的 publishes 方法。publishes 方法允许一个包含扩展包视图路径及对应发布路径的数组：

/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    $this->loadViewsFrom(__DIR__.'/path/to/views', 'courier');

    $this->publishes([
        __DIR__.'/path/to/views' => resource_path('views/vendor/courier'),
    ]);
}

现在，当你的扩展包用户运行 Laravel 的 vendor:publish Artisan 命令时，扩展包的视图将会被复制到指定的位置上


命令#
给你的扩展包注册 Artisan 命令，您可以使用 commands 方法。此方法需要一个命令类的数组。一旦命令被注册，您可以使用 Artisan 命令行 执行它们：
/**
 * 在注册后进行服务的启动。
 *
 * @return void
 */
public function boot()
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            FooCommand::class,
            BarCommand::class,
        ]);
    }
}



公用 Assets#
你的扩展包内可能会包含许多的资源文件，像 JavaScript、CSS 和图片等文件。如果要发布这些资源文件到应用程序的 public 目录上，只需使用服务提供者的 publishes 方法
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

现在，当您的扩展包的用户执行 vendor:publish 命令时，您的 Assets 将被复制到指定的发布位置。由于每次更新包时通常都需要覆盖资源，因此您可以使用 --force 标志：
php artisan vendor:publish --tag=public --force

发布群组文件#
举例来说，你可能想让用户不用发布扩展包的所有资源文件，只需要单独发布扩展包的配置文件即可。

这可以通过在调用 publishes 方法时使用「标签」来实现。例如，让我们在扩展包的服务提供者中的 boot 方法定义两个发布群组：

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

现在当你的用户使用 vendor:publish Artisan 命令时，就可以通过标签名称分别发布不同分类的资源文件：
php artisan vendor:publish --tag=config