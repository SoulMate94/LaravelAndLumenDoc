<?php

# 获取请求
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class userController extends Controller
{
    /**
     * 保存新的用户
     *
     * @param Request $request
     * @param Response
     */
    public function store(Request $request)
    {
        $name = $request->input('name');
    }
}

// 如果控制器方法也有输入数据是从路由参数传入的，只需将路由参数置于其它依赖之后
$app->put('user/{id}', 'UserController@update');

# 获取请求的URI
$uri = $request->path();
if ($request->is('admin/*')) {

}
// 不包含请求字符串
$url = $request->url();
// 包含请求字符串
$url = $request->fullUrl();

# 获取请求的方法
$method = $request->method();
if ($request->isMethod('post')) {

}


# PSR-7 请求
// composer require symfony/psr-http-message-bridge
// composer require zendframeword/zend-diactoros

use Psr\Http\Message\ServerRequestInterface;

Route::get('/', function(ServerRequestInterface $request) {

});

# Retirev 获取输入数据
// 获取特定输入值
$name = $request->input('name');
$name = $request->input('name', 'Later');
$name = $request->input('products.0.name');

// 确认是否有输入值
if ($request->has('name')) {
    //
}

// 获取所有输入数据
$input = $request->all();

// 获取部分输入数据
$input = $request->only(['username', 'password']);
$input = $request->only('username', 'password');

$input = $request->except(['credit_card']);
$input = $request->except(['credit_card']);


// 获取上传文件
$file = $request->file('photo');
if ($request->hasFile('photo')) {

}

// 确认上传的文件是否有效
if($request->file('photo')->isVailid()){}

// 移动上传的文件
$request->file('photo')->move($destinationPath);
$request->file('photo')->move($destinationPath, $fileName);












