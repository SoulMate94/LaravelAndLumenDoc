<?php

#中间件(过滤条件)

//Lumen 框架已经内置了一些中间件，包括维护、身份验证、CSRF 保护，等等。所有的中间件都放在 app/Http/Middleware 目录内。


#创建中间件
//可以复制 Lumen 自带的中间件示例文件 ExampleMiddleware 里的内容来创建新的中间件
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

#前置中间件/后置中间件
##「前置中间件（BeforeMiddleware）」运行于请求处理之前：
<?php

namespace App\Http\Middleware;

use Closure;

class BeforeMiddleware
{
    public function handle($request, Closure $next)
    {
        // Perform action

        return $next($request);
    }
}

##这个中间件会在应用程序处理请求 后 运行它的任务：
<?php

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Perform action

        return $response;
    }
}


#注册中间件
//全局中间件
//若是希望每个 HTTP 请求都经过一个中间件，只要将中间件的类加入到 bootstrap/app.php 的 $app->middleware() 调用参数数组中。
$app->middleware([
   App\Http\Middleware\OldMiddleware::class
]);

//为路由指派中间件
//如果你要指派中间件给特定路由，你需要给中间件设置一个好记的 键， 同样的，可以在 bootstrap/app.php 的 $app->middleware() 调用参数数组中进行设置。
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

$app->routeMiddleware([
    'jwt_auth'   => App\Http\Middleware\Auth\JWT::class,    
    'admin_auth' => App\Http\Middleware\Auth\Admin::class,  
    'qiniu_auth' => App\Http\Middleware\Auth\Qiniu::class,  
    'ak_sk_auth' => App\Http\Middleware\Auth\AKSK::class,  
    'migrate_user_filter' => App\Http\Middleware\Filter\MigrateUser::class, 
    'jwt'        => App\Http\Middleware\CheckUserJwt::class,     
]);

//中间件一旦被定义，即可在路由选项内使用 middleware 键值指定：
$app->get('admin/profile', ['middleware' => 'auth', function () {
    //
}]);

//使用一组数组为路由指定多个中间件
$app->get('/', ['middleware' => ['first', 'second'], function () {
    //
}]);


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
            // 重定向...
        }

        return $next($request);
    }

}
//在路由中可使用冒号 : 来区隔中间件名称与指派参数，多个参数可使用逗号作为分隔：
$app->put('post/{id}', ['middleware' => 'role:editor', function ($id) {
    //
}]);


#Terminable中间件
//有些时候中间件需要在 HTTP 响应被发送到浏览器之后才运行，例如，「session」中间件存储的 session 数据是在响应被发送到浏览器之后才进行写入的。想要做到这一点，你需要定义中间件为「terminable」
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
        // 保存 session 数据...
    }
}
//terminate 方法必须接收请求及响应。一旦定义了 terminable 中间件，你便需要将它增加到 bootstrap/app.php 文件的全局中间件清单列表中。
//当在你的中间件调用 terminate 方法时，Lumen 会从 服务容器 解析一个全新的中间件实例。
//如果你希望在 handle 及 terminate 方法被调用时使用一致的中间件实例，只需在容器中使用容器的 singleton 方法注册中间件。
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);