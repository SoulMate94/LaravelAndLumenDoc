<?php

配置#
基础介绍#
//所有 Laravel 框架的配置文件都放置在 config 目录下

获取设置值#
//可以使用 config 辅助函数获取你的设置值，设置值可以通过「点」语法来获取，其中包含了文件与选项的名称
//你也可以指定一个默认值，当该设置选项不存在时就会返回默认值：
$value = config('app.timezone');

//若要在运行期间修改设置值，请传递一个数组至 config 辅助函数：
config(['app.timezone' => 'America/Chicago']);

环境配置#
//应用程序常常需要根据不同的运行环境设置不同的值。例如，你会希望在本机开发环境上有与正式环境不同的缓存驱动。类似这种环境变量，只需通过 .env 配置文件就可轻松完成

//Laravel 使用 Vance Lucas 的 DotEnv PHP 函数库来实现项目内环境变量的控制，在安装好的全新 Laravel 应用程序里，在根目录下会包含一个 .env.example 文件。如果你通过 Composer 安装 Laravel，这个文件将自动被更名为 .env，否则你只能手动更改文件名。

//当你的应用程序收到请求时，这个文件所有的变量都会被加载到 PHP 超级全局变量 $_ENV 里。你可以使用辅助函数 env 来获取这些变量的值
'debug' => env('APP_DEBUG', false),
//env 函数的第二个参数是默认值，如果未找到对应的环境变量配置的话，此值就会被返回



判定目前使用的环境#
//应用程序的当前环境是由 .env 文件中的 APP_ENV 变量所决定的。你可以通过 App facade 的 environment 方法来获取该值：
$environment = App::environment();

//你也可以传递参数至 environment 方法来确认当前环境是否与参数相符合：
if (App::environment('local')) {
    // 当前正处于本地开发环境
}

if (App::environment('local', 'staging')) {
    // 当前环境处于 `local` 或者 `staging`
}
//也可通过 app 辅助函数获取应用程序实例：
$environment = app()->environment();

缓存配置信息#
//为了让应用程序的速度获得提升，可以使用 Artisan 命令 config:cache 将所有的配置文件缓存到单个文件。通过此命令将所有的设置选项合并成一个文件，让框架能够更快速的加载。

//你应该将运行 php artisan config:cache 命令作为部署工作的一部分。此命令不应该在开发时运行，因为设置选项会在开发时经常变动。


维护模式#
//当你的应用程序处于维护模式时，所有传递至应用程序的请求都会显示出一个自定义视图

//在你更新应用或进行性能维护时，这么做可以很轻松的「关闭」整个应用程序。维护模式会检查包含在应用程序的默认的中间件堆栈。如果应用程序处于维护模式，则 HttpException 会抛出 503 的状态码

//启用维护模式，只需要运行 Artisan 命令 down：
php artisan down

//关闭维护模式，请使用 Artisan 命令 up：
php artisan up

维护模式的响应模板#
维护模式的默认模板放在 resources/views/errors/503.blade.php

维护模式与队列#
当应用程序处于维护模式中时，将不会处理任何 队列工作。所有的队列工作将会在应用程序离开维护模式后被继续运行。


维护模式的替代方案#
维护模式有几秒钟的服务器不可用时间，如果你想做到平滑迁移的话，推荐使用 Envoyer 服务