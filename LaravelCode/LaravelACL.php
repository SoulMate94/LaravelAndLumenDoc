<?php

#ACL权限
//ACL其实就是一个认证，我们先来创建一个新的项目来为学习做准备，我们顺便复习之前学的东西，创建项目：
composer create-project laravel/laravel Learn_ACL 5.1.1 

//创建好项目后用我们的编译器工具打开，然后修改数据库的配置：

DB_HOST=127.0.0.1
DB_DATABASE=ACL
DB_USERNAME=root
DB_PASSWORD=

//创建我们的数据库迁移文件：

php artisan make:migration create_post_table --create=posts

//在up方法中设置字段：
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body');
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            // 声明user_id外键
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

//执行migrate：
php artisan migrate
//创建对应的model：
php artisan make:model Post

//有了这几张表后 我们使用model factory来生成测试数据，进入factory生成方法：
$factory->define(App\Post::class, function ($faker) {
    return [
        // 先使用factory创建一个用户,然后取到ID
        'user_id' => factory(App\User::class)->create()->id,
        'title' => $faker->sentence,
        'body' => $faker->paragraph,
    ];
});
//进入Tinker来创建测试数据：
php artisan tinker
factory('App\Post')->create();
//执行过后我们就有了一条文章和一个用户，为了方便测试 我们再来生成一个用户：
factory('App\User')->create();

//下面我们就来生成一个controller：
php artisan make:controller PostsController
//注册路由：
Route::resource('post', 'PostsController');



##ACL权限
//现在我们来说说这篇文章的终点 ACL权限，ACL其实就是访问权限控制，比如说：一个用户发表的帖子只有他自己可以编辑，当别的用户访问这篇文章时不会显示编辑按钮，让我们来看看它怎么使用吧：
//打开app\Providers\AuthServiceProvider在boot方法中编写以下代码：

    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
        
        // 我们编写的代码
        $gate->define('show_post', function ($user, $post){
            // 判断登陆进来的用户是否是文章的创建者。
            return $user->id == $post->user_id;
        });
    }

//之后就进入到控制器中使用吧：
    public function index()
    {
        \Auth::loginUsingID(1);
        $post = Post::findOrFail(1);
        if (Gate::denies('show_post',$post)){
            abort(403, 'sorry');
        }
        return $post->title;
    }

//我们登陆了ID为1的用户，就会正常展示文章，如果登陆了ID为2的用户，那么就会进入403界面。

//还有另一种写法：
public function index()
    {
        \Auth::loginUsingID(2);
        $post = Post::findOrFail(1);
        $this->authorize('show-post', $post);
//        if (Gate::denies('show_post',$post)){
//            abort(403, 'sorry');
//        }
        return $post->title;
    }

//我们在视图中来演示下怎么使用吧：
    public function index()
    {
        \Auth::loginUsingID(1);
        $post = Post::findOrFail(1);

        return view('show', compact('post'));
    }

    <body>
	    <h1>{{ $post->title }}</h1>
	    @can('show_post', $post)
	    <a href="#">编辑</a>
	    @endcan
	</body>

