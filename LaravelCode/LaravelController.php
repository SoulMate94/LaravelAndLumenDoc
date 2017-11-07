<?php

//我们之前在route文件中是使用匿名函数来进行操作的，所有操作都用匿名函数是不合理的，下面我们来学习下控制器

#创建控制器
##我们使用artisan来创建控制器：
php artisan make:controller ArticlesController 

//执行后我们进入目录后就可以看到这个控制器了：\app\Http\Controller


#RESTFul风格的控制器
//laravel的控制器是RESTFul风格的控制器 方便我们做增删改查的工作，我们为控制器绑定路由：
Route::resource('articles', 'ArticlesController');

//其实这短短的一行代码就已经注册了多条路由，我们可以使用artisan命令来看看：
php artisan route:list

//我们可以看到，每条路由都对应着控制器里的每一个方法，我们可以在控制器中的方法中完成相应的业务逻辑。

//如果我们不想要这么多条路由怎么办呢？有这么个方法：
// 指定只为index 和 store方法生成路由
Route::resource('articles', 'ArticlesController', ['only'=>['index', 'store']]);

//默认情况下，所有资源控制器动作都有一个路由名称，然而，我们可以通过传入names数组来覆盖这些默认的名字：
Route::resource('articles', 'ArticlesController',
    ['names' => ['create' => 'articles.build']]);

//在实际开发中，我们少不了要使用路由嵌套
//比如说一篇文章下有多个评论，我们可以这样嵌套：
Route::resource('articles.comments', 'ArticlesController');

//如果你不明白这是什么意思，可以使用artisan命令 php artisan route:list看下，我们在对应的控制器中的方法：
    public function show($articleId, $commentId)
    {
        // 显示谋篇文章下的评论
    }

//show方法对应的路由格式是：localhost8000/articles/{articleId}/comments/{commentId}


#注册单条路由
//如果我们要注册单条路由，就需要这样写：
Route::get('/articles/{id}', 'ArticlesController@showArticles');
//意思是当调用这条路由时 使用控制器中的showArticles方法，对应控制器中的方法是这样：
    public function showArticles($id)
    {
        // 执行逻辑代码
    }

#控制器在路由中的命名空间
//在route中 控制器默认的命名空间是App\Http\Controllers 当我们的控制器在这个命名空间下 我们只需要加上后面的部分即可：
Route::get('/get/user', '\Auth\AuthController@someMethod');


#为控制器路由命名
Route::get('/articles', ['uses' => 'ArticlesController@showArticles', 'as' => 'show']);
//我们可以使用函数action()来查找url：
$url = action('ArticlesController@showArticles');
//也可以使用route()函数：
$url = route('show');


#在控制器中使用middleware
//之前的章节中 我们介绍过中间件 只是如果在控制器中如何使用呢？let's look this：
Route::get('/test/middleware', ['uses' => 'ArticlesController@method', 'middleware' => 'any']);

//其实上面的例子并不常用，在控制器中的构造方法植入middleware更加方便
    public function __construct()
    {
        $this->middleware('someMiddleware');
        // 'only'代表 只有那几个方法使用到这个middleware
        $this->middleware('auth',['only'=>['index','show']]);
        // 'except'代表 除了那几个方法不适用这个middleware
        $this->middleware('log',['except'=>['getAny']]);
    }


#隐式控制器
//Laravel允许你只定义一个路由即可访问控制器类中的所有动作，首先，使用Route::controller方法定义一个路由，
//该controller方法接收两个参数，第一个参数是控制器处理的baseURI，第二个参数是控制器的类名：
Route::controller('articles','ArticlesController');

//接下来我们看看控制器中如何响应路由吧：

//我们以请求方式为前缀命名方法
    public function getIndex()
    {
        // 这个方法对应的是:GET方式  /articles 这条路由
    }
   	public function getShow($id)
    {
        // 这个方法对应: GET方式 /articles/show/{id}  这条路由
    }
    public function postProfile()
    {
        // 这个方法对应: POST方式 /articles/profile  这条路由
    }