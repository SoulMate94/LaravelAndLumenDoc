<?php

# From Requests
// 表单请求机制（Form requests）不被 Lumen 支持，如果你想使用此功能，请使用 Laravel。

# $this->validate
use Illuminate\Http\Request;

$app->post('/user', function(Request $request) {
    $this->validate($request, [
        'name'  => 'required',
        'email' => 'required|email|unique:users'
    ]);
});

// 当然，你可以选择使用 Validator::make facade 方法来做验证。

# $errors 视图变量
// 因为 Lumen 的使用场景是无状态的 API 调用，所以并没有 $errors 视图变量

// 当验证不通过的时候，$this->validate 会抛出异常 Illuminate\Validation\ValidationException 返回 JSON 类型的附带错误信息的响应。如果你不是在构建无状态 API 的话，你应该使用 Laravel 框架
