<?php

#HTTP 中间件#
//Laravel 框架已经内置了一些中间件，包括维护、身份验证、CSRF 保护，等等。所有的中间件都放在 app/Http/Middleware 目录内。


#创建中间件
//要创建一个新的中间件，则可以使用 make:middleware 这个 Artisan 命令：
php artisan make:middleware OldMiddleware
//此命令将会在 app/Http/Middleware 目录内设定一个名称为 OldMiddleware 的类。在这个中间件内我们只允许请求的年龄 age 变量大于 200 时才能访问路由，否则，我们会将用户重定向到首页「home」这个 URI 上。
<?php

namespace App\Http\Middleware;

use Closure;

class OldMiddleware
{
    /**
     * 运行请求过滤器。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->input('age') <= 200) {
            return redirect('home');
        }

        return $next($request);
    }

}

//前置中间件 
<?php

namespace App\Http\Middleware;

use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // 运行动作

        return $next($request);
    }
}

// 后置中间件
<?php

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 运行动作

        return $response;
    }
}



#注册中间件
//全局中间件
若是希望每个 HTTP 请求都经过一个中间件，只要将中间件的类加入到 app/Http/Kernel.php 的 $middleware 属性清单列表中。


//为路由指派中间件#
如果你要指派中间件给特定路由，你得先在 app/Http/Kernel.php 给中间件设置一个好记的 键，默认情况下，这个文件内的 $routeMiddleware 属性已包含了 Laravel 目前设置的中间件，你只需要在清单列表中加上一组自定义的键即可

// 在 App\Http\Kernel 类内...

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
];
//中间件一旦在 HTTP kernel 文件内被定义，即可在路由选项内使用 middleware 键值指定：
Route::get('admin/profile', ['middleware' => 'auth', function () {
    //
}]);
//使用一组数组为路由指定多个中间件：
Route::get('/', ['middleware' => ['first', 'second'], function () {
    //
}]);
//除了使用数组之外，你也可以在路由的定义之后链式调用 middleware 方法：
Route::get('/', function () {
    //
}])->middleware(['first', 'second']);


#中间件参数
//中间件也可以接收自定义传参，例如，要在运行特定操作前检查已验证用户是否具备该操作的「角色」，可以创建 RoleMiddleware 来接收角色名称作为额外的传参。
//附加的中间件参数将会在 $next 参数之后被传入中间件：
<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * 运行请求过滤
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (! $request->user()->hasRole($role)) {
            // 如果用户没有特定「角色」的话
        }

        return $next($request);
    }

}
//在路由中可使用冒号 : 来区隔中间件名称与指派参数，多个参数可使用逗号作为分隔：
Route::put('post/{id}', ['middleware' => 'role:editor', function ($id) {
    //
}]);




#Terminable 中间件#
//有些时候中间件需要在 HTTP 响应被发送到浏览器之后才运行，例如，Laravel 内置的「session」中间件存储的 session 数据是在响应被发送到浏览器之后才进行写入的。想要做到这一点，你需要定义中间件为「terminable」。