<?php

#基本任务清单#
#安装
//你可以选择使用 Homestead 虚拟机 或是其它本机 PHP 环境来运行框架
composer create-project laravel/laravel quickstart --prefer-dist
//如果你想下载这个快速入门指南的源代码并在你的本机机器运行，那么你需要克隆它的 Git 代码仓库并安装依赖：
git clone https://github.com/laravel/quickstart-basic quickstart
cd quickstart
composer install
php artisan migrate


#准备数据库
#数据库迁移
//Artisan 命令行接口 可以被用于生成各种类，为你构建 Laravel 项目时节省大量手动输入的时间。在此例中，让我们使用 make:migration 命令为 tasks 数据表生成新的数据库迁移：
php artisan make:migration create_tasks_table --create=tasks
//此迁移会被放置在项目的 database/migrations 目录中。你可能已经注意到，make:migration 命令已经增加了自动递增的 ID 及时间戳至迁移文件。让我们编辑这个文件并为任务的名称增加额外的 string 字段：
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

//我们可以使用 migrate Artisan 命令运行迁移。如果你使用了 Homestead，则必须在虚拟机中运行这个命令，因为你的主机无法直接访问数据库：
php artisan migrate
//这个命令会创建我们所有的数据表。如果你使用了数据库客户端来查看数据表，那么你应该会看到新的 tasks 数据表，其中包含了我们迁移中所定义的字段。

#Eloquent 模型
//Eloquent 是 Laravel 默认的 ORM（对象关联映射）
//Eloquent 通过明确的定义「模型」，让你可以很轻松的在数据库获取及保存数据。一般情况下，每个 Eloquent 模型会直接对应一张数据表。
//所以，让我们定义一个对应至 tasks 数据表的 Task 模型
//所以，让我们定义一个对应至 tasks 数据表的 Task 模型
php artisan make:model Task
//这个模型会放置在你应用程序的 app 目录中。默认情况下此模型类将是空的。我们不必明确告知 Eloquent 模型要对应哪张数据表，因为它会假设数据表是 模型名称 的复数型态。所以，在此例中，Task 模型会假设对应至 tasks 数据表。所以我们的空模型看起来应该如下：
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
}


#路由
#构建路由
//默认情况下，Laravel 所有的路由都会被定义在 app/Http/routes.php 文件中，每个新的 Laravel 项目都会包含此文件。
<?php

use App\Task;
use Illuminate\Http\Request;

/**
 * 显示所有任务
 */
Route::get('/', function () {
    //
});

/**
 * 增加新的任务
 */
Route::post('/task', function (Request $request) {
    //
});

/**
 * 删除一个已有的任务
 */
Route::delete('/task/{id}', function ($id) {
    //
});

#显示视图
//在 Laravel 里，所有的 HTML 模版都保存在 resources/views 目录，且我们可以在路由中使用 view 辅助函数来返回这些模版的其中一个：
Route::get('/', function () {
    return view('tasks');
});


#构建布局与视图
#定义布局
//Laravel 使用了 Blade 布局 来让不同页面共用这些相同的功能。
//如同我们前面讨论的那样，Laravel 所有的视图都被保存在 resources/views。所以，让我们来定义一个新的布局视图至 resources/views/layouts/app.blade.php 中。
//.blade.php 扩展名会告知框架使用 Blade 模板引擎 渲染此视图
// resources/views/layouts/app.blade.php

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Laravel 快速入门 - 基本</title>

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

//注意布局中的 @yield('content') 部分。这是特殊的 Blade 命令，让子页面可以在此处注入自己的内容以扩展布局。接着，让我们定义将会使用此布局并提供主要内容的子视图。

#定义子视图
// resources/views/tasks.blade.php

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
                <label for="task" class="col-sm-3 control-label">Task</label>

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

    <!-- 代办：目前任务 -->
@endsection


#增加任务
#验证
Route::post('/task', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:255',
    ]);

    if ($validator->fails()) {
        return redirect('/')
            ->withInput()
            ->withErrors($validator);
    }

    // 创建该任务...
});

//$errors 变量
//->withErrors($validator) 的调用会通过指定的验证器实例将错误消息闪存至 session 中，所以我们可以在视图中通过 $errors 变量访问它们
//common.errors 让我们可以简单的在所有的页面都显示相同格式的错误验证消息。现在让我们定义此视图的内容：
// resources/views/common/errors.blade.php

@if (count($errors) > 0)
    <!-- 表单错误清单 -->
    <div class="alert alert-danger">
        <strong>哎呀！出了些问题！</strong>

        <br><br>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
//注意：errors 变量可用于 每个 Laravel 的视图中。如果没有错误验证消息存在，那么它就会是一个空的 ViewErrorBag 实例。

#创建任务
//要创建该任务，我们可以在为新的 Eloquent 模型创建及设置属性后使用 save 方法：
Route::post('/task', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:255',
    ]);

    if ($validator->fails()) {
        return redirect('/')
            ->withInput()
            ->withErrors($validator);
    }

    $task = new Task;
    $task->name = $request->name;
    $task->save();

    return redirect('/');
});

#显示已有的任务
//view 函数接收一个能在视图中被取用的数据数组作为第二个参数，数组中的每个键都会在视图中作为变量：
Route::get('/', function () {
    $tasks = Task::orderBy('created_at', 'asc')->get();

    return view('tasks', [
        'tasks' => $tasks
    ]);
});
//一旦数据被传递，我们便可以在 tasks.blade.php 视图中将任务切分并将它们显示至数据库表中。@foreach 命令结构让我们可以编写简洁的循环的语句，并编译成快速的纯 PHP 代码：
@extends('layouts.app')

@section('content')
    <!-- 创建任务表单... -->

    <!-- 目前任务 -->
    @if (count($tasks) > 0)
        <div class="panel panel-default">
            <div class="panel-heading">
                目前任务
            </div>

            <div class="panel-body">
                <table class="table table-striped task-table">

                    <!-- 表头 -->
                    <thead>
                        <th>Task</th>
                        <th>&nbsp;</th>
                    </thead>

                    <!-- 表身 -->
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <!-- 任务名称 -->
                                <td class="table-text">
                                    <div>{{ $task->name }}</div>
                                </td>

                                <td>
                                    <!-- 待办：删除按钮 -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

#删除任务
#增加删除按钮
<tr>
    <!-- 任务名称 -->
    <td class="table-text">
        <div>{{ $task->name }}</div>
    </td>

    <!-- 删除按钮 -->
    <td>
        <form action="/task/{{ $task->id }}" method="POST">
            {{ csrf_field() }}
            {{ method_field('DELETE') }}

            <button>删除任务</button>
        </form>
    </td>
</tr>

//伪造方法
<input type="hidden" name="_method" value="DELETE">

#删除该任务
//我们可以使用 Eloquent 的 findOrFail 方法通过 ID 来获取模型，当该模型不存在时则会抛出 404 异常
Route::delete('/task/{id}', function ($id) {
    Task::findOrFail($id)->delete();

    return redirect('/');
});

