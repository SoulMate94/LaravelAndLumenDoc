<?php
#最基本的HTTP响应只需在路由闭包或控制器动作中返回一个简单字符串即可，但是具体业务逻辑中大部分响应都是在控制器动作中返回Response实例或者视图

#Response是继承自 Symfony\Component\HttpFoundation\Response的 Illuminate\Http\Response类的一个实例，我们可以使用该实例上的一系列方法来创建HTTP响应：


Route::get('testResponse', function (){
    $content = '测试response';
    $status = 200;
    $value = 'text/html;charset=utf-8';
    $response = new \Illuminate\Http\Response($content,$status);
    return $response->header('Content-Type', $value);
});
//我们可以使用浏览器的开发者工具查看此次响应。其实为了方便使用，我们可以使用全局帮助函数：
Route::get('testResponse', function (){
    $content = '测试response';
    $status = 500;
    $value = 'text/html;charset=utf-8';
    //全局帮助函数
    return response($content,$status)->header('Content-Type',$value);
});
//以上代码我们把状态码修改成了500，使用浏览器的开发者工具可以查看头信息，此外我们使用的response()是全局帮助函数，以后我们将默认使用这种方式 它们的效果是一样的，不再生成Response对象实例。

//此外，需要注意的是，Illuminate\Http\Response 类中还使用了ResponseTrait，header方法正是该trait提供的，除了header之外，该trait还提供了withCookie、content和status方法。
//header方法用于设置响应头信息，withCookie方法用于添加cookie，这两个方法都会返回调用它的Response自身对象，所以这两个方法都支持方法链（即多次调用header或withCookie方法）；而content和status方法则用于返回当前响应的响应实体内容和响应状态码。


#添加cookie
##正如上面提到的，我们使用withCookie方法为响应添加cookie，由于header和withCookie支持方法链，所以我们可以这样使用：
Route::get('testResponse', function (){
    $content = '测试response';
    $status = 200;
    $value = 'text/html;charset=utf-8';
    return response($content,$status)->header('Content-Type',$value)->withCookie('site','addCookie');
});

##在浏览器工具中可以清楚的看见名为site的cookie被添加了。
##如果有需要 可以指定cookie的有效期，作用域信息等：

return 	response($content,$status)
			->header('Content-Type',$value)
				->withCookie('site','addCookie',30,'/','test.app');

// 我们可以观察到cookie是加密的，这也是为了安全考虑，如果你不想加密cookie的话 
//到app/Http/Middleware/EncryptCookies.php文件中将对应的cookie名添加到EncryptCookies类属性$except中即可：

    protected $except = [
        'site',
    ];		//极力反对这样的做法。。。

#ResponseFactory
//我们在上面所用到的全局帮助函数 response() 方法  当不传入任何参数时 该方法内部会返回一个ResponseFactory给我们，它是Illuminate\Contracts\Routing\ResponseFactory契约的实现。
##ResponseFactory提供了非常多的方法来生成丰富的相应类型，如：试图响应，json，文件下载。

##视图响应
Route::get('testResponse', function (){
    $value = 'text/html;charset=utf-8';
    return response()->view('hello',['message'=>'我们正在学习response'])->header('Content-Type',$value);
});
//然后我们只需要创建hello.blade.php文件 将message传入就好了。

//如果你不需要自定义响应头的话 直接使用view()这个全局帮助函数会更方便，效果是一样的：
Route::get('testResponse', function (){
    return view('hello',['message'=>'我们正在学习response']);
});


#Json相应
Route::get('testResponse', function (){
    return response()->json(['id'=>1, 'name'=>'ask']);
});
//使用浏览器开发者工具查看，根据输出信息可见，json方法会自动设置Content-Type为application/json，并调用PHP内置函数json_encode讲数组转化为json格式字符串。


#文件下载
Route::get('testResponse', function (){
    return response()->download(
        realpath(base_path('public/images')).'/20150621200925_NMjYu-2.jpeg',
        'testDownload.jpeg'
    );
});



#RedirectResponse--重定向

//重定向我们之前使用过，重定向响应是Illuminate\Http\RedirectResponse类的实例，
//我们通常使用全局帮助函数redirect来生成 RedirectResponse实例。和response类似，
//redirect函数如果接收参数则调用的是Illuminate\Routing\Redirector类的to方法，如果无参调用则返回的是Redirector对象实例。

#最基本的重定向
Route::get('testRedirect', function (){
    return redirect('/test');
});

Route::get('/test',function (){
    return '测试重定向';
});
##使用back方法 重定向到上一个位置：
Route::get('/back', function (){
    return back()->withInput();
});


#重定向到路由命名
Route::get('testRedirect', function (){
    return redirect()->route('update',[100]);
});

Route::get('/articles/update/{id}',['as'=>'update', function($id){
    return '修改文章'.$id;
}]);
//如果没有参数的话 就不用带参数。

#重定向到控制器动作
Route::resource('articles', 'ArticlesController');

Route::get('/testRedirect',function(){
    return redirect()->action('ArticlesController@index');
});
//也可以跟上参数：
Route::resource('articles', 'ArticlesController');

Route::get('/testRedirect',function(){
    return redirect()->action('ArticlesController@edit',[2]);
});



#带一次性session数据的重定向
//这种重定向很有用，使用with方法可以携带一次性session数据到重定向请求页面（一次性session数据即使用后立即销毁的session数据项）：
	Route::resource('articles', 'ArticlesController');	//资源路由

	Route::get('/testRedirect',function(){
	    return redirect()->action('ArticlesController@index')->with('message','欢迎来到文章列表');
	});
//对应的index方法：
    public function index()
    {
        return view('home');
    }
//home.blade.php中的代码：
	@extends('app')

	@section('content')
	    @if(!empty(session('message')))
	        {{session('message')}}
	    @endif
	@stop
