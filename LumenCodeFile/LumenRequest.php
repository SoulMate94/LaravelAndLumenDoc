<?php

#Request

//获取请求的 URI#
//path 方法会返回请求的 URI。所以，如果接收到的请求目标是 http://domain.com/foo/bar，那么 path 方法就会返回 foo/bar
$uri = $request->path();

//is 方法可以验证接收到的请求 URI 与指定的规则是否相匹配。使用此方法时你可以将 * 符号作为通配符：
if ($request->is('admin/*')) {
    //
}

//使用 url 方法，可以获取完整的网址，fullUrl 方法可以获取到带 get 请求参数的完整网址：
// 不包含请求字串
$url = $request->url();

// 包含请求字串（请求字串如：`?id=2`）
$url = $request->fullUrl();

//获取请求的方法#
//method 方法会返回此次请求的 HTTP 动作。也可以通过 isMethod 方法来验证 HTTP 动作和指定的字符串是否相匹配：
$method = $request->method();

if ($request->isMethod('post')) {
    //
}



//PSR-7 请求#
//如果你想获得一个 PSR-7 的请求实例，你就必须先安装几个函数库。Laravel 使用 Symfony 的 HTTP 消息桥接组件，将原 Laravel 的请求及响应转换至 PSR-7 所支持的实现
composer require symfony/psr-http-message-bridge

composer require zendframework/zend-diactoros

//安装完这些函数库后，就可以在路由或控制器中，简单的对请求类型使用类型提示来获取 PSR-7 的请求：
use Psr\Http\Message\ServerRequestInterface;

Route::get('/', function (ServerRequestInterface $request) {
    //
});
//如果你从路由或控制器返回一个 PSR-7 的响应实例，那么它会被框架自动转换回 Laravel 的响应实例并显示


#Retriev## 获取输入数据#
//获取特定输入值

//获取所有的用户输入数据
$name = $request->input('name');
//可以在 input 方法的第二个参数中传入一个默认值。当请求的输入数据不存在于此次请求时，就会返回默认值：
$name = $request->input('name', 'Sally');
//如果是「数组」形式的输入数据，则可以使用「点」语法来获取数组：
$name = $request->input('products.0.name');
$names = $request->input('products.*.name');

//确认是否有输入值
//要判断数据是否存在于此次请求，可以使用 has 方法。当该数据存在 并且 字符串不为空时，has 方法就会传回 true：
if ($request->has('name')) {
    //
}


//获取所有输入数据
$input = $request->all();


//获取部分输入数据#
//如果你想获取输入数据的子集，则可以使用 only 及 except 方法。这两个方法都接受单个数组或是动态列表作为参数：
$input = $request->only(['username', 'password'])
$input = $request->only('username', 'password');

$input = $request->except(['credit_card']);
$input = $request->except('credit_card');


//获取上传文件
$file = $request->file('photo');
//你可以使用请求的 hasFile 方法确认上传的文件是否存在：
if ($request->hasFile('photo')) {
    //
}

//确认上传的文件是否有效
if ($request->file('photo')->isValid()) {
    //
}

//移动上传的文件
$request->file('photo')->move($destinationPath);
$request->file('photo')->move($destinationPath, $fileName);

