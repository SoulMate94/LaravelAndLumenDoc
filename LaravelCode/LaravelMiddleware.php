<?php
/*
	中间件的主要功能是在达到最终请求动作前对请求进行过滤和处理。
	中间件在Laravel中有着广泛的应用，比如用户认证、日志、维护模式、开启Session、从Session中获取错误信息，以及CSRF验证，等等
	中间件的所在目录：\app\Http\Middleware。里面有一些默认的middleware
 */

#创建自己的middleware
//创建middleware非常简单，我们打开终端，cd到项目目录下执行以下命令即可：
php artisan make:middleware TestMiddleware 
//这样 我们就可以在\app\Http\Middleware下看见我们刚刚创建的middleware了。

	<?php
	namespace App\Http\Middleware;

	use Closure;

	class TestMiddleware
	{
	    /**
	     * Handle an incoming request.
	     *
	     * @param  \Illuminate\Http\Request  $request
	     * @param  \Closure  $next
	     * @return mixed
	     */
	    public function handle($request, Closure $next)
	    {
	        // 在这里执行我们的逻辑
	        return $next($request);
	    }
	}

#实现一个简单的逻辑
    public function handle($request, Closure $next)
    {
        if ($request->input('age')<18){
            return redirect()->route('refuse');		//重定向
        }
        return $next($request);
    }

    ##在路由中使用中间件，需要在\app\Http\Kernel.php 文件中进行注册：

	<?php

	namespace App\Http;

	use Illuminate\Foundation\Http\Kernel as HttpKernel;

	class Kernel extends HttpKernel
	{
	    /**
	     * The application's global HTTP middleware stack.
	     *
	     * @var array
	     */
	    protected $middleware = [
	        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
	        \App\Http\Middleware\EncryptCookies::class,
	        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
	        \Illuminate\Session\Middleware\StartSession::class,
	        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
	        \App\Http\Middleware\VerifyCsrfToken::class,
	    ];

	    /**
	     * The application's route middleware.
	     *
	     * @var array
	     */
	    protected $routeMiddleware = [
	        'auth' => \App\Http\Middleware\Authenticate::class,
	        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
	        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
	        // 这是我们注册的middleware:
	        'test' => \App\Http\Middleware\TestMiddleware::class,
	    ];
	}

	//注意：我们将testmiddleware注册到了 $routeMiddleware变量中，laravel的注释写的很清楚 如果我们要在全局都使用到这个middleware 就把他注册到$middleware变量中
	//接下来我们就可以在路由中使用middleware了：
	Route::group(['prefix'=>'laravel', 'middleware'=>'test'], function (){
	    Route::get('/write', function (){
	        return 'Write laravel';
	    });
		Route::get('/update', function (){
		        return 'Update laravel';
		});
	});
	Route::get('/age/refuse',['as'=>'refuse', function(){
	    return '您的年龄未满18岁';
	}]);

	//我们在浏览器这样访问来测试：localhost:8000/laravel/write?age=15 或 localhost:8000/laravel/update?age=20
	
#在请求后执行动作
##有些时候，我们需要在请求后执行一些动作，可以这样写：
public function handle($request, Closure $next)
{
    $response = $next($request);
    // 执行一些动作

    return $response;
}



#带参数的Middleware
##除了请求实例$request和闭包$next之外，中间件还可以接收额外参数，我们还是以TestMiddleware为例，现在要求年龄在18岁以上的男性才能访问指定页面，handle方法定义如下：
    public function handle($request, Closure $next, $gender)
    {
    	//中间件还可以接收额外参数
        if ($request->input('age')>=18 && $gender==$request->input('gender')){
            return $next($request);
        }
        return redirect()->route('refuse');
    }
    //对应的修改路由：
    // 使用:语法为middleware传入参数
	Route::group(['prefix'=>'laravel', 'middleware'=>'test:male'], function (){
	    Route::get('/write', function (){
	        return 'Write laravel';
	    });
	    Route::get('/update', function (){
	        return 'Update laravel';
	    });
	});
	Route::get('/age/refuse',['as'=>'refuse', function(){
	    return '本站只允许满18岁的男士访问';
	}])

	