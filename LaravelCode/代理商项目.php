<?php

#控制器
//命名空间
namespace App\Http\Controllers\Admin;
//获取配置文件
$database  = config('database');

$params = $request->all();
$params['agent_id'] = $request->get('agent_id');

    //token
    $token = [
        "iss" => $_SERVER['SERVER_NAME'],
        "sub" => $data->proxy_id,
        "name" => $data->name,
    ];

    //有效期
    JWT::$leeway = 3600 * 5;

    $jwt = JWT::encode($token, env('SERECT_KEY'),['HS256']);

    $data->token = $jwt;

    return $jwt;

//引用
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';	//自动加载

#SESSION && COOKIE
if (!session_id()) {
	session_start();
}

return $_SESSION[$key] ?? false;	//判断是否存在

session_destroy();	//清除session

unset($_SESSION[$key]);	//删除某个session

setcookie('PHPSESSID', '', time()-1);	//设置COOKIE

#验证
if (!$this->verifyUserParams($params,$rules) || $params['complaint_id'] <= 0) {
    return $this->responseJson(2000,'系统繁忙');
}

	$rules = [
	    'uc_num'=> [
	        'required',
	        'numeric',
	        'regex:/^1[3|4|5|7|8][0-9]{9}$/'
	    ],
	    'uc_pass' => 'required|min:8|max:16'
	];

	if (!$this->verifyUserParams($params,$rules)) {
	    return $this->responseJson(1800,'系统繁忙，请稍后重试');
	}

$validator = Validator::make($params, $rules);

#模型
$order = new OrderModel;	//实例化模型

$admin = \App\Models\Admin::where('admin_name', $this->req->name)
		->where('passwd', md5($this->req->pswd))
		->where('role_id', 1)
		->where('closed', 0)
		->first();

#视图
return view('admin.dashboard');		//返回视图

return redirect()->route('admin_dashboard');	//重定向

#路由

#抛异常
throw new \Exception('Require at least 1 keywords.');

#服务

#返回值
return response()->json($fields, 200);	//返回json数据

    return [
        'err' => $errNo,
        'msg' => $errMsg,
        'res' => json_decode($res, true),
    ];

#中间件

##函数
array_unique(array)		--移出数组中重复的值

array_column(input, column_key)		--返回数组中指定的一列

is_object(var)		--检测变量是否是一个对象

USM::all  //??

USM::find 

is_numeric(var)		--是否数字
	
fopen(filename, mode)	--打开文件

fwrite(handle, string)	--写入文件

curl_init() 		--初始化CURL会话
	
array_merge(array1)	--合并数组

curl_setopt_array(ch, options)	--为 CURL 传输会话批量设置选项

curl_exec(ch)		--执行CURL会话

curl_errno(ch)		-- 返回最后一次的错误代码

curl_error(ch)		--返回当前会话最后一次错误的字符串

curl_close(ch)		--关闭CURL会话