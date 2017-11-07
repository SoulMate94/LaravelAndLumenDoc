<?php

中间件#
简介#
//Laravel 中间件提供了一种方便的机制来过滤进入应用的 HTTP 请求
//例如，Laravel 内置了一个中间件来验证用户的身份认证。如果用户没有通过身份认证，中间件会将用户重定向到登录界面。但是，如果用户被认证，中间件将允许该请求进一步进入该应用。
Laravel 自带了一些中间件，包括身份验证、CSRF 保护等。所有这些中间件都位于 app/Http/Middleware 目录。

定义中间件#
php artisan make:middleware CheckAge

//该命令将会在 app/Http/Middleware 目录内新建一个 CheckAge 类。在这个中间件里，我们仅允许提供的参数 age 大于 200 的请求访问该路由。否则，我们会将用户重定向到 home 。
<?php

namespace App\Http\Middleware;

use Closure;

class CheckAge
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->age <= 200) {
            return redirect('home');
        }

        return $next($request);
    }

}

前置 & 后置中间件#
<?php

namespace App\Http\Middleware;

use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // 执行动作

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // 执行动作

        return $response;
    }
}


注册中间件#
全局中间件#
//如果你想让中间件在你应用的每个 HTTP 请求期间运行，只需在 app/Http/Kernel.php 类中的 $middleware 属性里列出这个中间件类 

为路由分配中间件#
//如果想为特殊的路由指定中间件，首先应该在 app/Http/Kernel.php 文件内为该中间件指定一个 键。默认情况下，Kernel 类的 $routeMiddleware 属性包含 Laravel 内置的中间件条目。要加入自定义的，只需把它附加到列表后并为其分配一个自定义 键 即可。例如：
// 在 App\Http\Kernel 类中

protected $routeMiddleware = [
    'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
];

//一旦在 Kernel 中定义了中间件，就可使用 middleware 方法将中间件分配给路由：
Route::get('admin/profile', function () {
    //
})->middleware('auth');

//你还可以为路由分配多个中间件：
Route::get('/', function () {
    //
})->middleware('first', 'second');

//分配中间件时，你还可以传递完整的类名：
use App\Http\Middleware\CheckAge;

Route::get('admin/profile', function () {
    //
})->middleware(CheckAge::class);

中间件组#
//有时你可能想用单一的 键 为几个中间件分组，使其更容易分配到路由。可以使用 Kernel 类的 $middlewareGroups 属性来实现。
/**
 * 应用程序的路由中间件组
 *
 * @var array
 */
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    'api' => [
        'throttle:60,1',
        'auth:api',
    ],
];

//可以使用与单个中间件相同的语法将中间件组分配给路由和控制器操作。重申一遍，中间件组只是更方便地实现了一次为路由分配多个中间件。
Route::get('/', function () {
    //
})->middleware('web');

Route::group(['middleware' => ['web']], function () {
    //
});

!!无需任何操作，RouteServiceProvider 会自动将 web 中间件组应用于你的的 routes/web.php 文件。


中间件参数#
//附加的中间件参数应该在 $next 参数之后被传递：
<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * 处理传入的请求
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (! $request->user()->hasRole($role)) {
            // 重定向...
        }

        return $next($request);
    }

}
//定义路由时通过一个 : 来隔开中间件名称和参数来指定中间件参数。多个参数就使用逗号分隔：
Route::put('post/{id}', function ($id) {
    //
})->middleware('role:editor');

Terminable 中间件#
//如果你在中间件中定义一个 terminate 方法，则会在响应发送到浏览器后自动调用：
<?php

namespace Illuminate\Session\Middleware;

use Closure;

class StartSession
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        // Store the session data...
    }
}
//terminate 方法应该同时接收和响应。一旦定义了这个中间件，你应该将它添加到路由列表或 app/Http/Kernel.php 文件的全局中间件中

//在你的中间件上调用 terminate 调用时，Laravel 会从 服务容器 中解析出一个新的中间件实例。如果要在调用 handle 和 terminate 方法时使用同一个中间件实例，就使用容器的 singleton 方法向容器注册中间件


