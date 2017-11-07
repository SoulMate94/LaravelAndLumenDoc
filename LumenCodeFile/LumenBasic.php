<?php

#Lumen基础开发流程

//用composer创建项目：
composer create-project laravel/laravel laravel-app 5.1.1

composer create-project laravel/lumen lumen-app 5.1.1

//创建表
php arisan make:migration create_discussions_table  --create=discussions 
//执行migrate，创建discussion模型：
php artisan migrate
php artisan make:model Discussion

//进入tinker中批量生成数据：
php artisan tinker 
>>> factory('App\Discussion',30)->create();	

// 通过id取到文章模型
$discussion = Discussion::findOrFail($id);


//定义一条登录的路由：
Route::get('/user/login', 'UsersController@login');


//到此我们还需要创建一个Request：
php artisan make:request UserLoginRequest 


//首先创建一个评论控制器：CommentsController，并且添加resource路由：
php artisan make:controller CommentsController

Route::resource('comments', 'CommentsController');