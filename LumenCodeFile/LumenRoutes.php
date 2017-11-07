<?php

#routes目录
	//注册路由
$app->group([
    'prefix'     => 'sys',
    'namespace'  => 'Admin',
], function () use ($app) {
    $app->group([
        'middleware' => [
            'admin_auth',
        ],
    ], function () use ($app) {
        $app->get('/', [
            'as'   => 'admin_dashboard',
            'uses' => 'Admin@dashboard',
        ]);
        $app->get('dd', 'DataDict@index');
        $app->get('dd/fields', 'DataDict@getFields');
        $app->post('logout', 'Passport@logout');
        $app->group([
            'prefix' => 'upload_scenario',
        ], function () use ($app) {
            $app->get('/', 'UploadScenario@index');
            $app->get('table_fields/{tbName}', 'UploadScenario@getFieldsOfTable');
            $app->get('{us_id}', 'UploadScenario@createOrEdit');
            $app->post('{us_id}', 'UploadScenario@sideReq');
        });
    });
    $app->get('login', [
        'as'   => 'admin_login',
        'uses' => 'Passport@login',
    ]);
    $app->post('login', 'Passport@loginAction');
});

##GET请求
$app->get('foo', function () {
    return 'Hello World';
});

$app->get('login', 'Passport@loginAction');


##POST请求
$app->post('foo', function () {
    //
});
$app->post('login', 'Passport@loginAction');

##命令路由
$app->get('profile', ['as' => 'profile', function () {
    //
}]);

$app->get('profile', [
    'as' => 'profile', 'uses' => 'UserController@showProfile'
]);

##Resource资源路由

##Group群组
	//中间件
	$app->group(['middleware' => 'auth'], function () use ($app) {
	    $app->get('/', function ()    {
	        // Uses Auth Middleware
	    });

	    $app->get('user/profile', function () {
	        // Uses Auth Middleware
	    });
	});

	//命名空间
	$app->group(['namespace' => 'App\Http\Controllers\Admin'], function() use ($app)
	{
	    // 控制器在「App\Http\Controllers\Admin」命名空间

	    $app->group(['namespace' => 'App\Http\Controllers\Admin\User'], function() use ($app) {
	        // 控制器在「App\Http\Controllers\Admin\User」命名空间
	    });
	});


	//路由前缀
	$app->group(['prefix' => 'admin'], function () use ($app) {
	    $app->get('users', function ()    {
	        // Matches The "/admin/users" URL
	    });
	});
	//使用 prefix 参数去指定路由群组中共用的参数：
	$app->group(['prefix' => 'accounts/{account_id}'], function () use ($app) {
	    $app->get('detail', function ($accountId)    {
	        // Matches The "/accounts/{account_id}/detail" URL
	    });
	});
	