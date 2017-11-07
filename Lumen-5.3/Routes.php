<?php

# Basic Routing
$app->get('foo', function() {
    return 'sth';
});
$app->post('foo', function() {
    //
});

$app->get($uri, $callback);
$app->post($uri, $callback);
$app->put($uri, $callback);
$app->patch($uri, $callback);
$app->delete($uri, $callback);
$app->options($uri, $callback);


# 路由参数
$app->get('user/{id}', function(){
    return 'User'.$id;
});
$app->get('posts/{post}/comments/{comment}', function($postId, $commentId) {
    //
});

# namespace
$app->get('profile', ['as' => 'profile', function(){
    //
}]);
$app->get('profile', [
    'as'   => 'profile',
    'uses' => 'UserController@showProfile'
]);

#对命名路由审查过程URLs
$url = route('profile'); //生成URLs
return redirect()->route('profile'); // 生成重定向
$app->get('user/{id}/profile', ['as' => 'profile', function(){
    //
}]);
$url = route('profile', ['id' => 1]);

# Group
// Middleware
$app->group([
    'middleware' => 'auth'
], function() use ($app){
    $app->get('/', function() {
        //Uses Auth Middleware
    });
    $app->get('user/profile', function() {
        // Uses Auth Middleware
    });
});

// Namespace
$app->group([
    'namespace' => 'App\Http\Controllers\Admin'
], function() use ($app) {
    // 控制器在 App\Http\Controllers\Admin命名空间
    $app->group([
        'namespace' => 'App\Http\Controllers\Admin\User'
    ], function() use ($app){
        // 控制器在 App\Http\Controllers\Admin\User命名空间
    });

});

// Prefix
$app->group([
    'prefix' => 'admin'
], function() use ($app){
    $app->get('users', function() {
        // Match the '/admin/users' URL
    });
});



