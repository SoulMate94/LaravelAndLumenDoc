<?php

#运行环境要求#
//Laravel 框架会有一些系统上的要求。当然，这些要求在 Laravel Homestead 虚拟机上都已经完全配置好了：
PHP >= 5.5.9
OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
Tokenizer PHP Extension


#安装 Laravel#

//通过 Laravel 安装工具
//首先，使用 Composer 下载 Laravel 安装包：
composer global require "laravel/installer"
//请确定你已将 ~/.composer/vendor/bin 路径加到 PATH，只有这样系统才能找到 laravel 的执行文件。
laravel new blog


#通过 Composer Create-Project#
composer create-project laravel/laravel --prefer-dist blog
//安装 Laravel 5.1 LTS，请使用以下命令：
composer create-project laravel/laravel your-project-name --prefer-dist "5.1.*"

#配置信息
//基本配置
所有 Laravel 框架的配置文件都放置在 config 目录下


#目录权限
安装 Laravel 之后，你必须设置一些文件目录权限。storage 和 bootstrap/cache 目录必须让服务器有写入权限。如果你使用 Homestead 虚拟机，那么这些权限已经被设置好了。


#应用程序密钥
假设你是通过 Composer 或是 Laravel 安装工具安装的 Laravel，那么这个密钥已经通过 key:generate 命令帮你设置完成
通常这个密钥会有 32 字符长。这个密钥可以被设置在 .env 环境文件中。如果你还没将 .env.example 文件重命名为 .env，那么你现在应该去设置下。

如果应用程序密钥没有被设置的话，你的用户 Session 和其它的加密数据都是不安全的！


#优雅链接
Apache
//Laravel 框架通过 public/.htaccess 文件来让 URL 不需要 index.php 即可访问。如果你的服务器是使用 Apache，请确认是否有开启 mod_rewrite 模块。
//如果 Laravel 附带的 .htaccess 文件在 Apache 中无法使用的话，请尝试下方的做法：
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]


Nginx
//若你使用了 Nginx，则可以在网站设置中增加以下设置来开启「优雅链接」：
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
//如果你使用了 Homestead 的话，它将会自动的帮你设置好优雅链接。


#获取目前应用程序的环境#
//应用程序的当前环境是由 .env 文件中的 APP_ENV 变量所决定的。你可以通过 App facade 的 environment 方法来获取该值：
$environment = App::environment();
//你也可以传递参数至 environment 方法来确认当前环境是否与参数相符合：
if (App::environment('local')) {
    // 环境是 local
}

if (App::environment('local', 'staging')) {
    // 环境是 local 或 staging...
}
//也可通过 app 辅助函数获取应用程序实例：
$environment = app()->environment();


#缓存配置信息
//可以使用 config 辅助函数获取你的设置值，设置值可以通过「点」语法来获取，其中包含了文件与选项的名称。你也可以指定一个默认值，当该设置选项不存在时就会返回默认值：
$value = config('app.timezone');
//若要在运行期间修改设置值，请传递一个数组至 config 辅助函数：
config(['app.timezone' => 'America/Chicago']);


#命名你的应用程序
//举例来说，假设你的应用程序叫做「Horsefly」，则可以在安装完的根目录运行下方的命令：
php artisan app:name Horsefly


#维护模式#
//启用维护模式，只需要运行 Artisan 命令 down：
php artisan down
//关闭维护模式，请使用 Artisan 命令 up：
php artisan up

#维护模式的响应模板#
维护模式的默认模板放在 resources/views/errors/503.blade.php。

#维护模式与队列#
当应用程序处于维护模式中时，将不会处理任何 队列工作。所有的队列工作将会在应用程序离开维护模式后被继续运行。