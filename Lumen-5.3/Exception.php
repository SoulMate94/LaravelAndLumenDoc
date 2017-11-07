<?php

// 你的应用程序通过 .env 配置文件中的 APP_DEBUG 设置选项来控制浏览器对错误的细节显示

# 自定义的Monolog设置
$app->configureMonologUsing(function($monolog) {
    $monolog->pushHandler('sth');
});
return $app;

# 错误处理

## 报告方法
public function report(Exception $e)
{
    if ($e instanceof CustomException) {
        //
    }

    return parent::report($e);
}

## 呈现方法
/*
public function render($request, Exception $e)
{
    if ($e instanceof CustomException) {
        return response('Custom Message');
    }

    return parent::render($request, $e);
}
*/


## HTTP 异常
abort(404);

// abort(403, 'Unauthorized action.');

# Log

// namespace App\Http\Controllers;

use Log;
use App\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
   /**
    * show the user  for thr given ID
    *
    * @param int $id
    * @return Response
    */
   public function show($id)
   {
       Log::info('Showing user:'. $id);

       return User::findOrFail($id);
   }
}
// 注意: 在使用 Log facade 前，请在 bootstrap/app.php 中把 $app->withFacades() 这行调用的注释去除掉。

// 日志工具提供了定义在 RFC 5424 的八个级别： emergency、alert、critical、error、warning、notice、info 和 debug。
Log::emergency($error);
Log::alert($error);
Log::critical($error);
Log::warning($error);
Log::notice($error);
Log::info($error);
Log::debug($error);

## 上下文消息
Log::info('User failed to login.', ['id' => $user->id]);
