<?php

Laravel 下的伪造跨站请求保护 CSRF#
简介#
//Laravel 提供了简单的方法使你的应用免受 跨站请求伪造 (CSRF) 的袭击。跨站请求伪造是一种恶意的攻击，它凭借已通过身份验证的用户身份来运行未经过授权的命令

//Laravel 为每个活跃用户的 Session 自动生成一个 CSRF 「令牌」。该令牌用来核实应用接收到的请求是通过身份验证的用户出于本意发送的

//任何情况下在你的应用程序中定义 HTML 表单时都应该包含 CSRF 令牌隐藏域，这样 CSRF 保护中间件才可以验证请求。使用辅助函数 csrf_field 可以用来生成令牌字段
<form method="POST" action="/profile">
    {{ csrf_field() }}
    ...
</form>

//包含在 web 中间件组里的 VerifyCsrfToken 中间件会自动验证请求里的令牌与 Session 中存储的令牌是否匹配

CSRF 令牌 & JavaScript#
//当你构建由 Javascript 驱动的应用时，可以很方便地让你的 Javascript HTTP 函数库在发起每一个请求时自动附上 CSRF 令牌

//默认情况下， resources/assets/js/bootstrap.js 文件会用 Axios HTTP 函数库记录下 csrf-token meta 标签中的值。如果你不使用这个函数库，你需要为你的应用进行手动配置。


CSRF 白名单#
//一般地，你可以把这类路由放到 web 中间件外，因为 RouteServiceProvider 适用于 routes/web.php 中的所有路由

//不过如果一定要这么做，你也可以将这类 URI 添加到 VerifyCsrfToken 中间件中的 $except 属性来排除对这类路由的 CSRF 保护：
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * 这些 URI 会被免除 CSRF 验证
     *
     * @var array
     */
    protected $except = [
        'stripe/*',
    ];
}

X-CSRF-Token#
//除了检查 POST 参数中的 CSRF 令牌外，VerifyCsrfToken 中间件还会检查 X-CSRF-TOKEN 请求头。你可以将令牌保存在 HTML meta 标签中：
<meta name="csrf-token" content="{{ csrf_token() }}">

//一旦创建了 meta 标签，你就可以使用类似 jQuery 的库将令牌自动添加到所有请求的头信息中。这可以为您基于 AJAX 的应用提供简单、方便的 CSRF 保护：
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

!!默认情况下， resources/assets/js/bootstrap.js 文件会用 Axios HTTP 函数库记录下 csrf-token meta 标签中的值。如果你不使用这个函数库，则需要为你的应用进行手动配置。


X-XSRF-TOKEN#
//aravel 将当前的 CSRF 令牌存储在由框架生成的每个响应中包含的一个 XSRF-TOKEN cookie 中。你可以使用该令牌的值来设置 X-XSRF-TOKEN 请求头信息。

//这个 cookie 作为头信息发送主要是为了方便，因为一些 JavaScript 框架，如 Angular 和 Axios，会自动将其值添加到 X-XSRF-TOKEN 头中。
