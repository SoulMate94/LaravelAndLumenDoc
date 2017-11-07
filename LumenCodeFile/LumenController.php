<?php

#控制器
//控制器一般存放在 app/Http/Controllers 目录下

#基础控制器
//这是一个基础控制器类的例子。所有 Lumen 控制器都应继承基础控制器类，它包含在 Lumen 的默认安装中：
<?php

namespace App\Http\Controllers;

use App\User;

class UserController extends Controller
{
    /**
     * 显示指定用户的个人数据。
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return User::findOrFail($id);
    }
}

//我们可以通过路由来指定控制器行为，就像这样：
$app->get('user/{id}', 'UserController@show');
//当请求和此特定路由的 URI 相匹配时，UserController 类的 show 方法就会被运行。当然，路由的参数也会被传递至该方法


#控制器和命名空间
//有一点非常重要，那就是我们在定义控制器路由时，不需要指定完整的控制器命名空间。我们只需要定义「根」命名空间 App\Http\Controllers 之后的部分类名称即可。bootstrap/app.php 文件在加载 routes.php 时已经把所有路由规则都配置了根控制器命名空间

//若你需要在 App\Http\Controllers 目录内层使用 PHP 命名空间嵌套或组织控制器，只要使用相对于 App\Http\Controllers 根命名空间的特定类名称即可。例如控制器类全名为 App\Http\Controllers\Photos\AdminController，你可以像这样注册一个路由：

$app->get('foo', 'Photos\AdminController@method');


#命名控制器路由
//就像闭包路由，你可以指定控制器路由的名称：
$app->get('foo', ['uses' => 'FooController@method', 'as' => 'name']);

//你也可以使用 route 辅助函数，生成命名控制器路由的 URL：
$url = route('name');



#控制器中间件
//可将 中间件 指定给控制器路由，例如：
$app->get('profile', [
    'middleware' => 'auth',
    'uses' => 'UserController@showProfile'
]);

$app->group([
    'prefix'    => 'user/{id}',
    'namespace' => 'User',
    'middleware' => [
        'migrate_user_filter',
    ],
], function () use ($app) {
    $app->get('/', 'User@info');
    $app->get('withdraw_accounts', 'User@withdrawAccounts');
    $app->get('withdraws', 'User@withdraws');
    $app->post('withdraw', 'User@withdraw');
    $app->get('spasswd', 'User@verifySecurePasswd');
    $app->post('spasswd', 'User@updateSecurePasswd');
});


//在控制器构造器中指定中间件会更为灵活。在控制器构造器中使用 middleware 方法，你可以很容易地将中间件指定给控制器。你甚至可以对中间件作出限制，仅将它提供给控制器类中的某些方法。

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

        $this->middleware('log', ['only' => [
            'fooAction',
            'barAction',
        ]]);

        $this->middleware('subscribed', ['except' => [
            'fooAction',
            'barAction',
        ]]);
    }
}



#依赖注入与控制器
//构造器注入
//Laravel 使用 服务容器 来解析控制器的依赖注入。依赖会自动被解析并注入控制器实例之中。
<?php

namespace App\Http\Controllers;

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
$app->put('user/{id}', 'UserController@update');
//你依然可以做 Illuminate\Http\Request 类型提示并通过类似下面例子这样来定义你的控制器方法，访问你的路由参数 id：
//
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
     * @param  string  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}


