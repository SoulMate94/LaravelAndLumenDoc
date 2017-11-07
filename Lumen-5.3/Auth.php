<?php

$this->app['auth']->viaRequest('api', function($request) {
    // 返回 User 或者 null
});

# 获取已认证的用户信息
use Illuminate\Http\Request;

$app->get('/post/{id}', ['middleware' => 'auth', function(Request $request, $id){
    $user = Auth::user();

    $user = $request->user();
}]);

// 注意： 如果你想要使用 Auth::user() 来获取当前用户的话，你需要把 bootstrap/app.php 里对 $app->withFacades() 的调用 「取消代码注释」

// 你需要把 bootstrap/app.php 里对 $app->routeMiddleware() 的调用 「取消代码注释」，然后你就可以在路由中使用 auth 中间件 了
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);