<?php

#不同于 Laravel#
#定义权限
//在 Lumen 中，你可以很方便的在 AuthServiceProvider 中使用 Gate facade 来定义权限：

Gate::define('update-post', function ($user, $post) {
    return $user->id === $post->user_id;
});

#定义授权策略
//跟 Laravel 不一样的是，Lumen 在 AuthServiceProvider 中并没有 $policies 数组。然而，你可以在 AuthServiceProvider 的 boot 方法中使用 用 Gate facade 的 policy 来定义授权策略

Gate::policy(Post::class,PostPolicy::class)

#检查权限
//你可以像 Laravel 一样使用 Gate facade 来检查权限，在 Lumen 中，你需要启用 bootstrap/app.php 中对 facade 的使用。请记住，我们不需要对 allows 方法进行用户实例的传参，认证过的用户会自动传参到用户授权的回调中：

if (Gate::allows('update-post', $post)) {
    //
}

if (Gate::denies('update-post', $post)) {
    abort(403);
}

//当然，你也可以检查 User 实例是否有某些权限：
if ($request->user()->can('update-post', $post)) {
    abort(403);
}

if ($request->user()->cannot('update-post', $post)) {
    abort(403);
}

