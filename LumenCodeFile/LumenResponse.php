<?php

#响应

//基本响应
$app->get('/', function () {
    return 'Hello World';
});


//响应对象
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


//附加标头至响应
return response($content)
            ->header('Content-Type', $type)
            ->header('X-Header-One', 'Header Value')
            ->header('X-Header-Two', 'Header Value');

//或者你可以使用 withHeaders 来设置数组标头：
return response($content)
            ->withHeaders([
                'Content-Type' => $type,
                'X-Header-One' => 'Header Value',
                'X-Header-Two' => 'Header Value',
            ]);


#JSON响应
//json 方法会自动将标头的 Content-Type 设置为 application/json，并通过 PHP 的 json_encode 函数将指定的数组转换为 JSON：
return response()->json(['name' => 'Abigail', 'state' => 'CA']);
//如果你想创建一个 JSONP 响应，则可以使用 json 方法并加上 setCallback：
return response()->json(['name' => 'Abigail', 'state' => 'CA'])
                 ->setCallback($request->input('callback'));


#文件下载
//download 方法可以用于生成强制让用户的浏览器下载指定路径文件的响应。download 方法接受文件名称作为方法的第二个参数，此名称为用户下载文件时看见的文件名称。最后，你可以传递一个 HTTP 标头的数组作为第三个参数传入该方法：
return response()->download($pathToFile);

return response()->download($pathToFile, $name, $headers);


#重定向
//重定向响应是类 Illuminate\Http\RedirectResponse 的实例，并且包含用户要重定向至另一个 URL 所需的标头。有几种方法可以生成 RedirectResponse 的实例。最简单的方式就是通过全局的 redirect 辅助函数：
$app->get('dashboard', function () {
    return redirect('home/dashboard');
});

//重定向至命名路由
return redirect()->route('login');
//如果你的路由有参数，则可以将参数放进 route 方法的第二个参数：

// 重定向到以下 URI: profile/{id}

return redirect()->route('profile', ['id' => 1]);

//如果你要重定向至路由且路由的参数为 Eloquent 模型的「ID」，则可以直接将模型传入，ID 将会自动被提取：
return redirect()->route('profile', [$user]);
