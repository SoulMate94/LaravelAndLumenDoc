<?php

# Basic Ctl
namespace App\Http\Controllers;

use App\User;

class UserController extends Controller
{
    /**
     * 显示指定用户的个人数据
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return User::findOrFail($id);
    }
}
// route
$app->get('user/{id}', 'UserController@show');

#命名控制器路由
$app->get('foo', ['uses' => 'FooController@method', 'as' => 'name']);
#生成命名控制器路由的 URL：
$url = route('name');

# Controller Middleware
$app->get('profile', [
    'middleware' => 'auth',
    'uses' => 'UserController@showProfile'
]);

class UserControllerA extends Controller
{
    /**
     * 添加一个UserController 实例
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

        $this->middleware('subscribed',['except' => [
            'fooAction',
            'barAction',
        ]]);
    }
}

# 依赖注入 And 控制器

## 构造器注入
namespace App\Http\Controllers;

use App\Repositories\UserRepository;

class UserControllerB extends Controller
{
    /**
     * 用户 Repository 实例
     */
    protected $users;

    /**
     * 创建新的控制器实例
     *
     * @param UserRepository $uers
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }
}

## 方法注入
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserControllerC extends Controller
{
    /**
     * 保存一个新的用户
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $name = $request->input('name');
    }
}

// 从控制器方法中获取路由参数的话
$app->put('user/{id}', 'UserController@update');

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
//use Illuminate\Routing\Controller;

class UserControllerD extends Controller
{
    /**
     * 更新指定的用户
     *
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}















