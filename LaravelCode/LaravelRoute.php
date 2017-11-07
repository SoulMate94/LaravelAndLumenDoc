<?php

#路由
#其实我们在浏览器输入 http://localhost:8000/的时候就已经使用到了路由，
#现在让我们看看它是怎么写的吧，打开routes.php位置：\app\Http\routes.php

Route::get('/', function () {
    return view('welcome');
});

##GET路由
// 注册一条get路由
Route::get('/hello',function (){
    return 'hello world';
});
//浏览器中输入：localhost:8000/hello


##POST路由
//首先 我们来弄一个表单：
Route::get('/hello',function (){
    $token = csrf_token();		//生成Token
    // 生成表单 当点击了Test按钮后触发POST
    return <<<FORM
        <form action="/hello" method="POST">
            <input type="hidden" name="_token" value="{$token}">
            <input type="submit" value="Test"/>
        </form>
FORM;
});

// 对应的 我们也来弄一个post路由：
// 这是一条post路由
Route::post('/hello',function (){
    return 'hello (POST)';
});


#匹配不同类型请求的路由
//我们可以使用match方法来匹配多种请求类型：
// 无论get 还是 post 都会返回一个字符串
Route::match(['get','post'],'/hello',function(){
    return "Hello Laravel!";
})

//当然可以使用any方法来匹配所有请求类型 
Route::any('/hello',function(){
    return "Hello Laravel!";
});

#路由必选参数
// 在路径使用{}来声明参数,之后在匿名函数中接收参数 注意:参数名必须一致
Route::get('/{user}', function ($user){
    return 'hello ' . $user;
});


#路由可选参数  
// 可选参数只需要加个 ? 就可以了  然后在匿名函数中指定参数的默认值
Route::get('/hello/{user?}', function ($user = 'Alex'){
    return 'hello ' . $user;
});


#对参数进行正则约束
// user参数只允许输入大小写的字母 其他字符都会报错。
Route::get('/hello/{user?}', function ($user = 'Alex'){
    return 'hello ' . $user;
})->where('user','[A-Za-z]+');

//如果在全局范围内约束所有请求的参数，我们可以在 \app\Providers\RouteServiceProvider.php 中的 boot 方法实现逻辑：
    public function boot(Router $router)
    {
        // 这里是我们要实现的逻辑
        $router->pattern('user','[A-Za-z]+');
        //$router->patterns()
        parent::boot($router);
    }


################################################################

#路由
##重定向
//我们可以使用redirect方法来实现重定向，我们来看一个简单的重定向：
Route::get('/user/{id}', function ($id){
    if ($id <= 0){
        return redirect('/');
    } else {
        return 'hello';
    }
});
//当满足一些逻辑后，使用redirect跳转到别的视图。


#路由命名
// 路由命名就是为路由起一个名字，这样我们在重定向时 可是使用路由的名字，就不用输入那些复杂的路径了。使用 as 关键字来为路由命名：
// 把第二个参数改成一个数组,里面指定一些键值配置,和一个闭包。
Route::get('/hello/laravel',['as' => 'laravel',function(){
    return 'Hello Laravel';
}]);

// 生成一个测试重定向的路由
Route::get('/test/redirect',function (){
    // 我们在跳转时,只需要在route方法中传入路由名就可以实现跳转。
    return redirect()->route('laravel');
});


#路由分组
//路由分组给我们带来了很多便利，当一些路由具有相同的属性时，我们可以使用路由分组将他们包含起来，路由的属性有：
	/* 
	中间件
	路由前缀
	子域名
	命名空间
	*/
//其中中间件和命名空间在之后的章节中做记录，我们先来看看路由前缀和子域名的使用。

#子域名
// 子域名我们可以使用domain来声明：
// 定义一个路由分组,domain传进一个参数:
Route::group(['domain'=>'{service}.laravel.app'], function (){
    Route::get('/write/laravel', function ($service){
        return "Write FROM {$service}.laravel.app";
    });
    Route::get('/update/laravel', function ($service){
        return "Update FROM {$service}.laravel.app";
    });
});
//当我们在浏览器中访问：http://write.laravel.app:8000/write/laravel 时，则会输出：
Write FROM write.laravel.app
//当我们访问：http://update.laravel.app:8000/update/laravel 时，输出：
Update FROM update.laravel.app
##注意：我们要让子域名生效 需要在hosts文件中绑定IP地址。


#路由前缀
//我们使用prefix来指定路由前缀：
Route::group(['prefix' => 'laravel'], function (){
    Route::get('/write', function (){
        return 'Write laravel';
    });
    Route::get('/update', function (){
        return 'Update laravel';
    });
});
//这样的话我们只需要访问：localhost:8000/laravel/write 和 localhost:8000/laravel/update 即可。
//有些时候 我们还可以指定带参数的prefix：
Route::group(['prefix' => 'laravel/{version}'], function (){
    Route::get('/write', function ($version){
        return 'Write laravel' . $version;
    });
    Route::get('/update', function ($version){
        return 'Update laravel' . $version;
    });
});
	
