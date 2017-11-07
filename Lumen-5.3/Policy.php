<?php

# 定义权限
Gate::define('update-post', function($user, $post) {
    return $user->id === $post->user_id;
});

# 定义授权策略
Gate::policy(Post::class, PostPolicy::class);

# 检查权限
if (Gate::allows('update-post', $post)) {

}

if (Gate::denies('update-post', $post)) {
    abort(403);
}


if ($request->user()->can('update-post', $post)) {
    abort(403);
}

if ($request->user()->cannot('update-post', $post)) {
    abort(403);
}