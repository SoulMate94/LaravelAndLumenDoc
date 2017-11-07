<?php

#数据验证
//Lumen 提供了多种不同的处理方法来对应用程序传入的数据进行验证。默认情况下，Lumen 的基底控制器类使用了 ValidatesRequests trait，其提供了一种便利的方法来使用各种强大的验证规则验证传入的 HTTP 请求


#Form Requests

表单请求机制（Form requests）不被 Lumen 支持，如果你想使用此功能，请使用 Laravel。


#$this->validate 方法
在 Lumen 你可以使用 $this->validate 来验证，不同的是此方法只会返回 JSON 类型的附带错误信息的响应。因为 Lumen 只支持无状态的 API 响应，也不会在 session 中的闪存错误信息。
//还有一个不同于 Laravel 的地方，Lumen 支持在路由闭包里面直接使用 validate 方法：

use Illuminate\Http\Request;

$app->post('/user', function (Request $request) {
    $this->validate($request, [
        'name' => 'required',
        'email' => 'required|email|unique:users'
    ]);

    // 存储用户...
});
//当然，你可以选择使用 Validator::make facade 方法来做验证。

#$errors 视图变量#
因为 Lumen 的使用场景是无状态的 API 调用，所以并没有 $errors 视图变量。当验证不通过的时候，$this->validate 会抛出异常 Illuminate\Validation\ValidationException 返回 JSON 类型的附带错误信息的响应。如果你不是在构建无状态 API 的话，你应该使用 Laravel 框架。