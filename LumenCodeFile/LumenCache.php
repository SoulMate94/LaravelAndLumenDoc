<?php

//Lumen 给各种不同的缓存系统提供了统一的 API，缓存的配置文件都放在 .env 中，在这个文件中，你可以指定默认想用哪个缓存驱动，Lumen 支持当前流行的缓存后端，如 Memcached 和 Redis

//不同于Laravel
//注意： 如果你想要使用 Cache facade，你需要把 bootstrap/app.php 里对 $app->withFacades() 的调用 「取消代码注释」。

#Redis支持
//在你选择使用 Redis 作为 Lumen 的缓存之前，你需要通过 Composer 预先安装 predis/predis (~1.0) 扩展包 ，还有 illuminate/redis (5.2.*) 扩展包。安装完成后在 bootstrap/app.php 文件中注册 Illuminate\Redis\RedisServiceProvider 。

//如果你没有在 bootstrap/app.php 文件中调用 $app->withEloquent() 的话，请确定在 bootstrap/app.php 中调用 $app->configure('database');，这样才能保证 Redis 数据库配置信息能被正确加载。

$app->withEloquent();
   