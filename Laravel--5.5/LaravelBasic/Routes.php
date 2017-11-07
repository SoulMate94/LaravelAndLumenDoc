<?php

Laravel HTTP 路由功能#
基本路由#
Route::get('foo', function () {
    return 'Hello World';
});

默认路由文件#
Route::get('/user', 'UsersController@index');

可用的路由方法#
Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);
//有的时候你可能需要注册一个可响应多个 HTTP 方法的路由，这时你可以使用 match 方法，也可以使用 any 方法注册一个实现响应所有 HTTP 的请求的路由：
Route::match(['get', 'post'], '/', function () {
    //
});

Route::any('foo', function () {
    //
});

CSRF 保护#
<form method="POST" action="/profile">
    {{ csrf_field() }}
    ...
</form>

重定向路由#
Route::redirect('/here', '/there', 301);


视图路由#
//view 方法有三个参数，其中前两个是必填参数，分别是 URL 和视图名称。第三个参数选填，可以传入一个数组，数组中的数据会被传递给视图。
Route::view('/welcome', 'welcome');

Route::view('/welcome', 'welcome', ['name' => 'Taylor']);

路由参数#
必选路由参数##
Route::get('user/{id}', function ($id) {
    return 'User '.$id;
});
//也可以根据需要在路由中定义多个参数：
Route::get('posts/{post}/comments/{comment}', function ($postId, $commentId) {
    //
});

//路由的参数通常都会被放在 {} 内，并且参数名只能为字母，同时路由参数不能包含 -，请用下划线 (_) 代替。路由参数会按顺序依次被注入到路由回调/控制器中 - 不受回调/控制器的参数名称的影响


可选路由参数##
//如需指定该参数为可选，可以在参数后面加上 ? 来实现，但是相应的变量必须有默认值：
Route::get('user/{name?}', function ($name = null) {
    return $name;
});

Route::get('user/{name?}', function ($name = 'John') {
    return $name;
});

正则表达式约束#
Route::get('user/{name}', function ($name) {
    //
})->where('name', '[A-Za-z]+');

Route::get('user/{id}', function ($id) {
    //
})->where('id', '[0-9]+');

Route::get('user/{id}/{name}', function ($id, $name) {
    //
})->where(['id' => '[0-9]+', 'name' => '[a-z]+']);


全局约束#
//你应该在 RouteServiceProvider 的 boot 方法里定义这些模式：

/**
 * 定义你的路由模型绑定, pattern 过滤器等。
 *
 * @return void
 */
public function boot()
{
    Route::pattern('id', '[0-9]+');

    parent::boot();
}
//Pattern 一旦被定义，便会自动应用到所有使用该参数名称的路由上：
Route::get('user/{id}', function ($id) {
    // 仅在 {id} 为数字时执行...
});

命名路由#
Route::get('user/profile', function () {
    //
})->name('profile');
//你还可以为控制器方法指定路由名称：
Route::get('user/profile', 'UserController@showProfile')->name('profile');


为命名路由生成 URL#
// 生成 URL...
$url = route('profile');

// 生成重定向...
return redirect()->route('profile');

//如果是有定义参数的命名路由，可以把参数作为 route 函数的第二个参数传入，指定的参数将会自动插入到 URL 中对应的位置：
Route::get('user/{id}/profile', function ($id) {
    //
})->name('profile');

$url = route('profile', ['id' => 1]);

检查当前路由#
//如果你想判断当前请求是否指向了某个路由，你可以调用 Route 实例的 named 方法
/**
 * 处理一次请求。
 *
 * @param  \Illuminate\Http\Request  $request
 * @param  \Closure  $next
 * @return mixed
 */
public function handle($request, Closure $next)
{
    if ($request->route()->named('profile')) {
        //
    }

    return $next($request);
}

路由组#
//路由组允许共享路由属性，例如中间件和命名空间等，我们没有必要为每个路由单独设置共有属性，共有属性会以数组的形式放到 Route::group 方法的第一个参数中。
中间件#
Route::middleware(['first', 'second'])->group(function () {
    Route::get('/', function () {
        // 使用 `first` 和 `second` 中间件
    });

    Route::get('user/profile', function () {
        // 使用 `first` 和 `second` 中间件
    });
});

命名空间#
Route::namespace('Admin')->group(function () {
    // 在 "App\Http\Controllers\Admin" 命名空间下的控制器
});

子域名路由#
//可以使用路由组属性的 domain 键声明子域名
Route::domain('{account}.myapp.com')->group(function () {
    Route::get('user/{id}', function ($account, $id) {
        //
    });
});

路由前缀#
//通过路由组数组属性中的 prefix 方法可以给每个路由组中的路由加上指定的 URI 前缀
Route::prefix('admin')->group(function () {
    Route::get('users', function () {
        // 匹配包含 "/admin/users" 的 URL
    });
});

路由模型绑定#
//当向路由控制器中注入模型 ID 时，我们通常需要查询这个 ID 对应的模型，Laravel 路由模型绑定提供了一个方便的方法自动将模型注入到我们的路由中

隐式绑定#
//Laravel 会自动解析定义在路由或控制器方法（方法包含和路由片段匹配的已声明类型变量）中的 Eloquent 模型
Route::get('api/users/{user}', function (App\User $user) {
    return $user->email;
});

自定义键名#
//如果你想要隐式模型绑定除 id 以外的数据库字段，你可以重写 Eloquent 模型类的 getRouteKeyName 方法：
/**
 * 为路由模型获取键名。
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'slug';
}

显式绑定#
//使用路由的 model 方法来为已有参数声明 class。你应该在 RouteServiceProvider 类中的 boot 方法内定义这些显式绑定：
public function boot()
{
    parent::boot();

    Route::model('user', App\User::class);
}
//接着，定义包含 {user} 参数的路由：
Route::get('profile/{user}', function (App\User $user) {
    //
});

!!注意：如果在数据库不存在对应 ID 的数据，就会自动抛出一个 404 异常。

自定义解析逻辑#
public function boot()
{
    parent::boot();

    Route::bind('user', function ($value) {
        return App\User::where('name', $value)->first();
    });
}

表单方法伪造#
//HTML 表单不支持 PUT、PATCH 或 DELETE 动作。所以在定义要在 HTML 表单中调用的 PUT、PATCH 或 DELETE 路由时，你将需要在表单中增加隐藏的 _method 字段
<form action="/foo/bar" method="POST">
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
</form>

获取当前路由信息#
//你可以使用 Route 上的 current，currentRouteName 和 currentRouteAction 方法来访问处理当前输入请求的路由信息：
$route = Route::current();

$name = Route::currentRouteName();

$action = Route::currentRouteAction();