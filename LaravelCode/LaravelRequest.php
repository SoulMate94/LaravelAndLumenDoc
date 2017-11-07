<?php

#我们先来创建一个路由测试请求实例

Route::controller('articles','ArticlesController');
//创建相应方法：
public function getTest(Request $request)
{
    $input = $request->input('test');
    echo $input;
}

// 访问http://localhost:8000/articles/test?test=10 来测试。


#获取URL路径和请求方法 
##Request还可以获取url和uri路径： 
public function getTest(Request $request)
{
    // 如果不是articles/路径就抛出404
    if (!$request->is('articles/*')){
        abort(404);
    }
    $uri = $request->path();
    $url = $request->url();
    echo $uri;
    echo '<br />';
    echo $url;
}

##获取请求方法：
public function getTest(Request $request)
{
    // 如果不是get方法就抛出404
    if (!$request->isMethod('get')){
        abort(404);
    }
    $method = $request->method();
    echo $method;
}


#获取请求数据
##我们使用Request的input方法来获取当前请求的输入数据，注意看注释哦：

public function getTest(Request $request)
{
    // input方法可接受两个参数 第一个参数是输入数据的键,第二个参数是默认值,当没有取得数据时使用默认值。
    $name = $request->input('name','defaultName');

    // input方法还支持数组取值
    // 对应的输入数据:http://......?test[][name]=balabala
    $test = $request->input('test.0.name');

    echo $name;
    echo '<br />';
    echo $test;
}	
//使用这个url来测试：http://localhost:8000/articles/test?name=ask&test[][name]=test

##我们可以使用has方法判断参数是否存在
public function getTest(Request $request)
{
    // 判断参数是否存在
    if ($request->has('name')){
        echo $request->input('name');
    }
}

##实际上我们可以使用一系列方法来获取我们想要的输出数据比如：only，except，all
public function getTest(Request $request)
{
    // 获取全部数据
    $allData = $request->all();

    // 只获取name和age
    $onlyData = $request->only('name','age');

    // 获取除了name的所有数据
    $exceptData = $request->except('name');

    echo '<pre>';
    print_r($allData);
    print_r($onlyData);
    print_r($exceptData);
}
//我们使用这个url来测试：http://localhost:8000/articles/test?name=ask&age=24&test=test


#获取上次的请求输入
##如果想要获取上一次请求的输入，需要在处理上一次请求时使用Request实例上的flash方法将请求数据暂时保存到session中，然后在当前请求中使用Request实例上的old方法获取session中保存的数据，获取到数据后就会将session中保存的请求数据销毁：

public function getLastRequest(Request $request)
{
    $request->flash();	//将请求数据暂时保存到session中
}

public function getCurrentRequest(Request $request)
{
    $lastData = $request->old();	//获取session中保存的数据 
    echo '<pre>';
    print_r($lastData);
}
//如果你嫌这样麻烦 也可以重定向 使用withinput方法 效果是一样的：
public function getLastRequest(Request $request)
{
    return redirect('/articles/current-request')->withInput();
}

public function getCurrentRequest(Request $request)
{
    $lastData = $request->old();
    echo '<pre>';
    print_r($lastData);
}

#获取cookie数据
//我们可以使用Request实例上的cookie方法获取cookie数据，该方法可以接收一个参数名返回对应的cookie值，如果不传入参数，默认返回所有cookie值：

public function getCookie(Request $request)
{
    $cookies = $request->cookie();	//获取cookie数据
    dd($cookies);
}

##我们可以调用Response实例上的withCookie方法新增cookie：
public function getAddCookie()
{
    $response = new Response();
    //第一个参数是cookie名，第二个参数是cookie值，第三个参数是有效期（分钟）
    $response->withCookie(cookie('cookie','learn-laravel',3));
    //如果想要cookie长期有效使用如下方法
	//$response->withCookie(cookie()->forever('cookie-name','cookie-value'));
    return $response;
}


#上传文件
##我们先来创建表单：
	//文件上传表单
	public function getFileupload()
	{
	    $postUrl = '/articles/fileupload';
	    $csrf_field = csrf_field();		
	    //Laravel自动为每个用户Session生成了一个CSRF Token，该Token可用于验证登录用户和发起请求者是否是同一人，如果不是则请求失败
	//     $html = <<<CREATE
	// <form action="$postUrl" method="POST" enctype="multipart/form-data">
	// $csrf_field
	// <input type="file" name="file"><br/><br/>
	// <input type="submit" value="提交"/>
	// </form>
	// CREATE;
	    return $html;
	}

	//上传文件操作：
	 public function postFileupload(Request $request)
    {
        // hasfile  判断文件是否存在 参数值对应着表单中的name值
        if (!$request->hasFile('file')){
            exit('上传文件为空');
        }
        // 取到文件
        $file = $request->file('file');
        //判断文件上传过程中是否出错
        if (!$file->isValid()){
            exit('文件上传出错');
        }
        // 判断路由是否存在
        $destPath = public_path('images');
        if (!file_exists($destPath)){
            // 不存在就创建
            mkdir($destPath,0755,true);
        }
        $filename = $file->getClientOriginalName();
        if (!$file->move($destPath,$filename)){
            exit('文件保存失败');
        }
        exit('文件上传成功');
    }
