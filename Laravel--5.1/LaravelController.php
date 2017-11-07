<?php

#HTTP 控制器#

//控制器一般存放在 app/Http/Controllers 目录下。


#基础控制器
//所有 Laravel 控制器都应继承基础控制器类，它包含在 Laravel 的默认安装中：
<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 显示指定用户的个人数据。
     *
     * @param  int  $id
     * @return Response
     */
    public function showProfile($id)
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
//我们可以通过路由来指定控制器行为，就像这样：
Route::get('user/{id}', 'UserController@showProfile');

//控制器和命名空间
//若你需要在 App\Http\Controllers 目录内层使用 PHP 命名空间嵌套或组织控制器，只要使用相对于 App\Http\Controllers 根命名空间的特定类名称即可。例如控制器类全名为 App\Http\Controllers\Photos\AdminController，你可以像这样注册一个路由：
Route::get('foo', 'Photos\AdminController@method');

//命名控制器路由
Route::get('foo', ['uses' => 'FooController@method', 'as' => 'name']);

//控制器行为的 URLs
//你也可以使用 route 辅助函数，生成命名控制器路由的 URL：
$url = route('name');

//你也可以使用 action 辅助函数生成指向控制器行为的 URL。同样地，我们只需指定基类命名空间 App\Http\Controllers 之后的部分控制器类名称就可以了：
$url = action('FooController@method');

//你可以使用 Route facade 的 currentRouteAction 方法取到正在运行的控制器行为名称：
$action = Route::currentRouteAction();

#控制器中间件
//可将 中间件 指定给控制器路由，例如：
Route::get('profile', [
    'middleware' => 'auth',
    'uses' => 'UserController@showProfile'
]);
//在控制器构造器中使用 middleware 方法，你可以很容易地将中间件指定给控制器。你甚至可以对中间件作出限制，仅将它提供给控制器类中的某些方法
class UserController extends Controller
{
    /**
     * 添加一个 UserController 实例。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('log', ['only' => ['fooAction', 'barAction']]);

        $this->middleware('subscribed', ['except' => ['fooAction', 'barAction']]);
    }
}


#RESTful 资源控制器
//资源控制器让你可以轻松地创建与资源相关的 RESTful 控制器
//例如，你可能想要创建一个用来处理应用程序保存「相片」时发送 HTTP 请求的控制器。使用 make:controller Artisan 命令，我们可以快速地创建一个像这样的控制器：
php artisan make:controller PhotosController
//此 Artisan 命令会生成 app/Http/Controllers/PhotosController.php 控制器文件。此控制器会包含用来操作可获取到的各种资源的方法。
Route::resource('photos', 'PhotosController');

#部分资源路由
//声明资源路由时，你可以指定让此路由仅处理一部分的行为：
Route::resource('photos', 'PhotosController',
                ['only' => ['index', 'show']]);

Route::resource('photos', 'PhotosController',
                ['except' => ['create', 'store', 'update', 'destroy']]);

//命名资源路由
//所有的资源控制器行为默认都有路由名称；不过你可以在选项中传递一个 names 数组来重写这些名称：
Route::resource('photos', 'PhotosController',
                ['names' => ['create' => 'photo.build']]);


#嵌套资源
//有时你可能会需要定义「嵌套」资源路由。例如，相片资源可能会附带多个「评论」。要「嵌套」此资源控制器，可在路由声明中使用「点」记号：
Route::resource('photos.comments', 'PhotoCommentController');
//此路由会注册一个「嵌套」资源，可通过类似的 URL 来访问它：photos/{photos}/comments/{comments}。
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PhotoCommentController extends Controller
{
    /**
     * 显示指定相片的评论。
     *
     * @param  int  $photoId
     * @param  int  $commentId
     * @return Response
     */
    public function show($photoId, $commentId)
    {
        //
    }
}


#附加资源控制器
//如果想在资源控制器中默认的资源路由之外加入其它额外路由，则应该在调用 Route::resource 之前 定义这些路由。否则，由 resource 方法定义的路由可能会不小心覆盖你附加的路由：
Route::get('photos/popular', 'PhotosController@method');

Route::resource('photos', 'PhotosController');


#隐式控制器
//Laravel 让你能够轻易地通过定义单个路由来处理控制器类中的各种行为。首先，使用 Route::controller 方法来定义路由
//controller 方法接受两个参数。第一个参数是控制器所处理的基本 URI，第二个是控制器的类名称：
Route::controller('users', 'UserController');
//接下来，只要在控制器中加入方法。方法的名称应由它们所响应的 HTTP 动词作为开头，紧跟着首字母大写的 URI 所组成：
<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    /**
     * 响应对 GET /users 的请求
     */
    public function getIndex()
    {
        //
    }

    /**
     * 响应对 GET /users/show/1 的请求
     */
    public function getShow($id)
    {
        //
    }

    /**
     * 响应对 GET /users/admin-profile 的请求
     */
    public function getAdminProfile()
    {
        //
    }

    /**
     * 响应对 POST /users/profile 的请求
     */
    public function postProfile()
    {
        //
    }
}

//分派路由名称
//如果你想要 命名 控制器中的某些路由，你可以在 controller 方法中传入一个名称数组作为第三个参数：
Route::controller('users', 'UserController', [
    'getShow' => 'user.show',
]);

#依赖注入与控制器
//构造器注入
//Laravel 使用 服务容器 来解析控制器的依赖注入。依赖会自动被解析并注入控制器实例之中。
<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    /**
     * 用户 Repository 实例。
     */
    protected $users;

    /**
     * 创建新的控制器实例。
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }
}



//方法注入
//除了构造器注入之外，你也可以对 控制器行为方法的依赖 使用类型提示。例如，让我们对 Illuminate\Http\Request 实例的其中一个方法使用类型提示：
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    /**
     * 保存一个新的用户。
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $name = $request->input('name');

        //
    }
}
//想要从控制器方法中获取路由参数的话，只要在其它的依赖之后列出路由参数即可。例如：
Route::put('user/{id}', 'UserController@update');
//你依然可以做 Illuminate\Http\Request 类型提示并通过类似下面例子这样来定义你的控制器方法，访问你的路由参数 id：
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    /**
     * 更新指定的用户。
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}

#路由缓存
//注意： 路由缓存并不会作用在基于闭包的路由。要使用路由缓存，你必须将所有闭包路由转换为控制器类。
//若你的应用程序完全通过控制器使用路由，你可以利用 Laravel 的路由缓存
//使用路由缓存可以大幅降低注册全部路由所需的时间。在某些情况下，你的路由注册甚至可以快上一百倍！要生成路由缓存，只要运行 route:cache 此 Artisan 命令：
php artisan route:cache
//要移除缓存路由文件而不生成新的缓存，请使用 route:clear 命令：
php artisan route:clear