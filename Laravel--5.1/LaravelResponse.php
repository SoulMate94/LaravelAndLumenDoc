<?php

#HTTP 响应#
#基本响应
Route::get('/', function () {
    return 'Hello World';
});
//返回一个完整的 Response 实例时，就能够自定义响应的 HTTP 状态码以及标头。Response 实例继承了 Symfony\Component\HttpFoundation\Response 类，其提供了很多创建 HTTP 响应的方法：
use Illuminate\Http\Response;

Route::get('home', function () {
    return (new Response($content, $status))
                  ->header('Content-Type', $value);
});
//为了方便起见，你可以使用辅助函数 response：
Route::get('home', function () {
    return response($content, $status)
                  ->header('Content-Type', $value);
});


#附加标头至响应
//举例来说，你可以在响应发送给用户之前，使用 header 方法增加一系列的标头至响应：
return response($content)
            ->header('Content-Type', $type)
            ->header('X-Header-One', 'Header Value')
            ->header('X-Header-Two', 'Header Value');



#附加 Cookies 至响应
//通过响应实例的 withCookie 辅助方法可以让你轻松的附加 cookies 至响应
return response($content)->header('Content-Type', $type)
     					 ->withCookie('name', 'value');
//withCookie 方法可以接受额外的可选参数，让你进一步自定义 cookies 的属性：
->withCookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly) 					 
//默认情况下，所有 Laravel 生成的 cookies 都会被加密并加上认证标识，因此无法被用户读取及修改。如果你想停止对某个 cookies 的加密，则可以利用 App\Http\Middleware\EncryptCookies 中间件的 $except 属性：
/**
 * 无需被加密的 cookies 名称。
 *
 * @var array
 */
protected $except = [
    'cookie_name',
];


#其它响应类型
//使用辅助函数 response 可以轻松的生成其它类型的响应实例。当你调用辅助函数 response 且不带任何参数时，将会返回 Illuminate\Contracts\Routing\ResponseFactory contract 的实现


#视图响应
//如果你想要控制响应状态码及标头，同时也想要返回一个 视图 作为返回的内容时
return response()->view('hello', $data)->header('Content-Type', $type);

#JSON 响应
//json 方法会自动将标头的 Content-Type 设置为 application/json，并通过 PHP 的 json_encode 函数将指定的数组转换为 JSON：
return response()->json(['name' => 'Abigail', 'state' => 'CA']);
//如果你想创建一个 JSONP 响应，则可以使用 json 方法并加上 setCallback：
return response()->json(['name' => 'Abigail', 'state' => 'CA'])
                 ->setCallback($request->input('callback'));


#文件下载
//download 方法可以用于生成强制让用户的浏览器下载指定路径文件的响应。download 方法接受文件名称作为方法的第二个参数，此名称为用户下载文件时看见的文件名称。最后，你可以传递一个 HTTP 标头的数组作为第三个参数传入该方法：
return response()->download($pathToFile);

return response()->download($pathToFile, $name, $headers);
//注意：管理文件下载的扩展包 Symfony HttpFoundation，要求下载文件必须是 ASCII 文件名。



#重定向
//重定向响应是类 Illuminate\Http\RedirectResponse 的实例，并且包含用户要重定向至另一个 URL 所需的标头
//最简单的方式就是通过全局的 redirect 辅助函数：
Route::get('dashboard', function () {
    return redirect('home/dashboard');
});

//有时你可能希望将用户重定向至前一个位置
Route::post('user/profile', function () {
    // 验证该请求...

    return back()->withInput();
});


#重定向至命名路由
return redirect()->route('login');
//如果你的路由有参数，则可以将参数放进 route 方法的第二个参数：
// 重定向到以下 URI: profile/{id}

return redirect()->route('profile', [1]);

//如果你要重定向至路由且路由的参数为 Eloquent 模型的「ID」，则可以直接将模型传入，ID 将会自动被提取：
return redirect()->route('profile', [$user]);


#重定向至控制器行为
//你可能会希望生成重定向至 控制器的行为。要做到这一点，只需传递控制器及行为名称至 action 方法
return redirect()->action('HomeController@index');
//当然，如果你的控制器路由需要参数的话，你可以传递它们至 action 方法的第二个参数：
return redirect()->action('UserController@profile', [1]);


#重定向并加上 Session 闪存数据
Route::post('user/profile', function () {
    // 更新用户的个人数据...

    return redirect('dashboard')->with('status', 'Profile updated!');
});
//当然，在用户重定向至新的页面后，你可以获取并显示 session 的闪存数据。举个例子，使用 Blade 的语法：
@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

#响应宏
//如果你想要自定义可以在很多路由和控制器重复使用的响应，可以使用 Illuminate\Contracts\Routing\ResponseFactory 实现的方法 macro
//举个例子，来自 服务提供者的 boot 方法：
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\ResponseFactory;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * 提供注册后运行的服务。
     *
     * @param  ResponseFactory  $factory
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        $factory->macro('caps', function ($value) use ($factory) {
            return $factory->make(strtoupper($value));
        });
    }
}

//macro 函数第一个参数为宏名称，第二个参数为闭包函数。宏的闭包函数会在 ResponseFactory 的实现或者辅助函数 response 调用宏名称的时候被运行：
return response()->caps('foo');