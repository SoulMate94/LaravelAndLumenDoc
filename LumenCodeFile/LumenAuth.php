<?php

#用户认证

//因 Lumen 面向的是无状态 API 的开发，不支持 session，所以默认的配置不同。Lumen 必须使用无状态的机制来实现，如 API 令牌（Token）。

##认证服务提供者
//注意： 在使用 Lumen 的认证功能前，请对 bootstrap/app.php 中 AuthServiceProvider 的调用取消代码注释


//AuthServiceProvider 存放在 app/Providers 文件夹中，此文件中只有一个 Auth::viaRequest 调用。viaRequest 会在系统需要认证的时候被调用，此方法接受一个匿名函数传参，在此匿名函数内，你可以任意的解析 App\User 并返回，或者在解析失败时返回 null：

$this->app['auth']->viaRequest('api', function ($request) {
    // 返回 User 或者 null...
});


//获取已认证的用户信息
//可以通过 Auth facade 来访问认证的用户。也有另外一种方法可以访问认证过的用户，就是通过 Illuminate\Http\Request 实例，请注意类型提示的类会被自动注入

use Illuminate\Http\Request;

$app->get('/post/{id}', ['middleware' => 'auth', function (Request $request, $id) {
    $user = Auth::user();

    $user = $request->user();

    //
}]);

//注意： 如果你想要使用 Auth::user() 来获取当前用户的话，你需要把 bootstrap/app.php 里对 $app->withFacades() 的调用 「取消代码注释」。

//你需要把 bootstrap/app.php 里对 $app->routeMiddleware() 的调用 「取消代码注释」，然后你就可以在路由中使用 auth 中间件 了。
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);


