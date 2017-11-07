<?php

介绍#
//Laravel 框架的所有配置文件都保存在 config 目录中


环境配置#
//Laravel 利用 Vance Lucas 的 PHP 库 DotEnv 使得此项功能的实现变得非常简单。在新安装好的 Laravel 应用程序中，其根目录会包含一个 .env.example 文件。如果是通过 Composer 安装的 Laravel，该文件会自动更名为 .env。否则，需要你手动更改一下文件名。

!!.env 文件中的所有变量都可被外部环境变量（比如服务器级或系统级环境变量）所覆盖。


检索环境配置#
当应用程序收到请求时，.env 文件中列出的所有变量将被加载到 PHP 的超级全局变量 $ _ENV 中
//你可以使用 env 函数检索这些变量的值。事实上，如果你查看 Laravel 的配置文件，你就能注意到有数个选项已经使用了这个函数：
'debug' => env('APP_DEBUG', false),
//传递给 env 函数的第二个值是「默认值」。如果给定的键不存在环境变量，则会使用该值



确定当前环境#
//应用程序当前所处环境是通过 .env 文件中的 APP_ENV 变量确定的。你可以通过 App facade 中的 environment 方法来访问此值：
$environment = App::environment();

//你还可以传递参数给 environment 方法，以检查当前的环境配置是否与给定值匹配。 如果与给定值匹配，该方法将返回 true：
if (App::environment('local')) {
    // 环境为 local
}

if (App::environment(['local', 'staging'])) {
    // 环境为 local 或 staging
}

访问配置值#
//你可以轻松地在应用程序的任何位置使用全局 config 函数来访问配置值
//。配置值的访问可以使用「点」语法，这其中包含了要访问的文件和选项的名称。还可以指定默认值，如果配置选项不存在，则返回默认值：
$value = config('app.timezone');

//要在运行时设置配置值，传递一个数组给 config 函数：
config(['app.timezone' => 'America/Chicago']);


配置缓存#
//为了给你的应用程序提升速度，你应该使用 Artisan 命令 config:cache 将所有的配置文件缓存到单个文件中
//这会把你的应用程序中所有的配置选项合并成一个单一的文件，然后框架会快速加载这个文件。

//通常来说，你应该把运行 php artisan config:cache 命令作为生产环境部署常规的一部分。这个命令不应在本地开发环境下运行，因为配置选项在应用程序开发过程中是经常需要被更改的
php artisan config:cache

!!如果在部署过程中执行 config:cache 命令，那你应该确保只从配置文件内部调用 env 函数


维护模式#
//当应用程序处于维护模式时，所有对应用程序的请求都显示为一个自定义视图。这样可以在更新或执行维护时轻松地「关闭」你的应用程序

//维护模式检查包含在应用程序的默认中间件栈中。如果应用程序处于维护模式，则将抛出一个状态码为 503 的 MaintenanceModeException 异常

要启用维护模式，只需执行下面的 Artisan 命令 down#
php artisan down

//你还可以向 down 命令提供 message 和 retry 选项
其中 message 选项的值可用于显示或记录自定义消息，
而 retry 值可用于设置 HTTP 请求头中 Retry-After 的值：

php artisan down --message="Upgrading Database" --retry=60

要关闭维护模式，请使用 up 命令#
php artisan up
!!你可以通过修改 resources/views/errors/503.blade.php 模板文件来自定义默认维护模式模板。

维护模式和队列#
//当应用程序处于维护模式时，不会处理 队列任务。而这些任务会在应用程序退出维护模式后再继续处理

//维护模式的替代方案#
//维护模式会导致应用程序有数秒的停机（不响应）时间，因此你可以考虑使用像 Envoyer 这样的替代方案，以便与 Laravel 完成零停机时间部署

