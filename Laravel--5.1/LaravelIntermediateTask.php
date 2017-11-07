<?php

#中级任务清单#
#安装
composer create-project laravel/laravel quickstart --prefer-dist

git clone https://github.com/laravel/quickstart-intermediate quickstart
cd quickstart
composer install
php artisan migrate

#准备数据库
//users 数据表
Laravel 已经附带了创建 users 数据表的迁移，所以我们没必要再手动生成一个。默认的 users 数据表迁移位于 database/migrations 目录中。

//tasks 数据表
php artisan make:migration create_tasks_table --create=tasks
//此迁移会被放置在你项目的 database/migrations 目录中。你可能已经注意到，make:migration 命令已经增加了自动递增的 ID 及时间戳至迁移文件。让我们编辑这个文件并为任务的名称增加额外的 string 字段，也增加连接 tasks 与 users 数据表的 user_id 字段：
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * 运行迁移。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * 还原迁移。
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tasks');
    }
}

//我们可以使用 migrate Artisan 命令来运行迁移。如果你使用了 Homestead，则必须在虚拟机中运行这个命令，因为你的主机无法直接访问数据库：
php artisan migrate

#Eloquent 模型
//Task 模型
//让我们定义一个对应至 tasks 数据表的 Task 模型。同样的，我们可以使用 Artisan 命令来生成此模型。在此例中，我们会使用 make:model 命令：
php artisan make:model Task


#Eloquent 关联
//例如，我们的 User 可以拥有多个 Task 实例，而一条 Task 则只能被赋给一名 User。定义好关联可以让我们很轻松的整理它们之间的关系，就像这样：
$user = App\User::find(1);

foreach ($user->tasks as $task) {
    echo $task->name;
}

//tasks 关联
//Eloquent 支持多种不同类型的关联
//我们会在 User 模型中定义一个 tasks 函数，并调用 Eloquent 提供的 hasMany 方法：
<?php

namespace App;

// 导入的命名空间...

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    // 其它的 Eloquent 属性...

    /**
     * 获取该用户的所有任务。
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}


//user 关联
//让我们在 Task 模型定义 user 关联
<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * 这些属性能被批量赋值。
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * 获取拥有此任务的用户。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


#路由
我们在 routes.php 中将所有逻辑都定义为闭包

#显示视图
//在 Laravel 里，所有的 HTML 模版都保存在 resources/views 目录，且我们可以在路由中使用 view 辅助函数来返回这些模版的其中一个：
Route::get('/', function () {
    return view('welcome');
});


#认证
//首先，你会注意到在应用程序中已经包含一个 app/Http/Controllers/Auth/AuthController。这个控制器使用了特别的 AuthenticatesAndRegistersUsers trait，它包含了所有创建及认证用户的必要逻辑。

//认证路由
//让我们在 app/Http/routes.php 文件中增加需要的路由：
// 认证路由...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// 注册路由...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

//认证视图
//认证需要在 resources/views/auth 目录中创建 login.blade.php 与 register.blade.ph
register.blade.php 文件必需有一个包含 name、email、password 与 password_confirmation 字段的表单，并创建一个到 /auth/register 路由上的 POST 请求。

login.blade.php 文件必需有一个包含 email 与 password 字段的表单，并创建一个到 /auth/login 上的 POST 请求。


#任务控制器
//因为我们已经知道任务需要可被获取及保存，所以让我们使用 Artisan 命令行接口创建一个 TaskController，这个新的控制器会放置在 app/Http/Controllers 目录中：
php artisan make:controller TaskController --plain
//现在这个控制器已经被生成，让我们继续在 app/Http/routes.php 文件中构建一些对应至此控制器的路由：
Route::get('/tasks', 'TaskController@index');
Route::post('/task', 'TaskController@store');
Route::delete('/task/{task}', 'TaskController@destroy');

//认证所有的任务路由
//要让所有控制器中的行为要求已认证的用户，我们可以在控制器的构造器中增加 middleware 方法的调用
//所以可用的路由中间件都被定义在 app/Http/Kernel.php 文件中
<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    /**
     * 创建一个新的控制器实例。
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}

#构建布局与视图
#定义布局
//Laravel 所有的视图都被保存在 resources/views
//.blade.php 扩展名会告知框架使用 Blade 模板引擎 渲染此视图
// resources/views/layouts/app.blade.php

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Laravel 快速入门 - 高级</title>

        <!-- CSS 及 JavaScript -->
    </head>

    <body>
        <div class="container">
            <nav class="navbar navbar-default">
                <!-- Navbar 内容 -->
            </nav>
        </div>

        @yield('content')
    </body>
</html>
//注意布局中的 @yield('content') 部分。这是特殊的 Blade 命令，让子页面可以在此处注入自己的内容以扩展布局。接着，让我们定义将会使用此布局并提供主要内容的子视图



#定义子视图
//让我们将此视图定义在 resources/views/tasks/index.blade.php，它会对应至我们 TaskController 的 index 方法。
// resources/views/tasks/index.blade.php

@extends('layouts.app')

@section('content')

    <!-- Bootstrap 模版... -->

    <div class="panel-body">
        <!-- 显示验证错误 -->
        @include('common.errors')

        <!-- 新任务的表单 -->
        <form action="/task" method="POST" class="form-horizontal">
            {{ csrf_field() }}

            <!-- 任务名称 -->
            <div class="form-group">
                <label for="task-name" class="col-sm-3 control-label">任务</label>

                <div class="col-sm-6">
                    <input type="text" name="name" id="task-name" class="form-control">
                </div>
            </div>

            <!-- 增加任务按钮-->
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-6">
                    <button type="submit" class="btn btn-default">
                        <i class="fa fa-plus"></i> 增加任务
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 待办：目前任务 -->
@endsection


#增加任务


#验证


#创建任务


#显示已有的任务


#依赖注入


#显示任务


#删除任务


#增加删除按钮


#路由模型绑定


#授权


#删除该任务

