<?php

Laravel 的 Blade 模板引擎#
简介#
所有 Blade 视图文件都将被编译成原生的 PHP 代码并缓存起来，除非它被修改，否则不会重新编译，这就意味着 Blade 基本上不会给你的应用增加任何负担



模板继承#
定义布局#
Blade 的两个主要优点是 模板继承 和 区块
<!-- 文件保存于 resources/views/layouts/app.blade.php -->

<html>
    <head>
        <title>应用程序名称 - @yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            这是主布局的侧边栏。
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>

继承布局#
<!-- 文件保存于 resources/views/child.blade.php -->

@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    <p>这将被添加到主侧边栏。</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection

Components & Slots#

!!与上一个示例相反，此侧边栏部分以 @endsection 而不是 @show 结尾。 @endsection 指令只定义一个区块，而 @show 则是定义并立即生成该区块

你也可以通过在路由中使用全局辅助函数 view 来返回 Blade 视图：
Route::get('blade', function () {
    return view('child');
});


Components & Slots#
Components 和 slots 类似于布局中的 @section，但其使用方式更容易使人理解
首先，假设我们有一个能在整个应用程序中被重复使用的「警告」组件:
<!-- /resources/views/alert.blade.php -->

<div class="alert alert-danger">
    {{ $slot }}
</div>

{{ $slot }} 变量将包含我们希望注入到组件的内容
然后，我们可以使用 Blade 命令 @component 来构建这个组件：
@component('alert')
    <strong>Whoops!</strong> Something went wrong!
@endcomponent

有时为组件定义多个 slots 是很有帮助的。现在我们要对「警报」组件进行修改，让它可以注入「标题」
通过简单地 「打印」匹配其名称的变量来显示被命名的 @slot 之间的内容：
<!-- /resources/views/alert.blade.php -->

<div class="alert alert-danger">
    <div class="alert-title">{{ $title }}</div>

    {{ $slot }}
</div>

现在，我们可以使用 @slot 指令注入内容到已命名的 slot 中，任何没有被 @slot 指令包裹住的内容将传递给组件中的 $slot 变量:
@component('alert')
    @slot('title')
        Forbidden
    @endslot

    你没有权限访问这个资源！
@endcomponent


传递额外的数据给组件#
有时候你可能需要传递额外的数据给组件。你可以传递一个数组作为第二个参数传递给 @component 指令
所有的数据都将以变量的形式传递给组件模版:
@component('alert', ['foo' => 'bar'])
    ...
@endcomponent



显示数据#
你可以使用 「中括号」 包住变量将数据传递给 Blade 视图。如下面的路由设置：
Route::get('greeting', function () {
    return view('welcome', ['name' => 'Samantha']);
});

你可以像这样显示 name 变量的内容：
Hello, {{ $name }}.

当然，不仅仅只能用传递数据的方式让视图来显示变量内容。你也可以打印 PHP 函数的结果
其实，你可以在 Blade 打印语法中放置任何 PHP 代码：
The current UNIX timestamp is {{ time() }}.

!!Blade 的 {{ }} 语法会自动调用 PHP htmlspecialchars 函数来避免 XSS 攻击。

显示未转义的数据#
Hello, {!! $name !!}.

渲染 JSON#
<script>
    var app = <?php json_encode($array); ? >;
</script>

你可以使用 Blade 指令 @json 来代替手动调用 json_encode：
<script>
    var app = @json($array)
</script>


Blade & JavaScript 框架#
你可以使用 @ 符号来告知 Blade 渲染引擎你需要保留这个表达式原始形态，例如：
<h1>Laravel</h1>

Hello, @{{ name }}.
在这个例子里，@ 符号最终会被 Blade 引擎删除，达到不受 Blade 模板引擎影响的目的，最终 {{ name }} 表达式会保持不变使得 JavaScript 框架可以使用它。

@verbatim 指令#
你可以使用 @verbatim 指令来包裹 HTML 内容
@verbatim
    <div class="container">
        Hello, {{ name }}.
    </div>
@endverbatim

流程控制#

If 语句#
@if (count($records) === 1)
    我有一条记录！
@elseif (count($records) > 1)
    我有多条记录！
@else
    我没有任何记录！
@endif

为了方便，Blade 还提供了一个 @unless 命令：
@unless (Auth::check())
    你尚未登录。
@endunless

除了以上的条件指令之外，@isset 和 @empty 指令也可以视为与 PHP 函数有相同的功能
@isset($records)
    // $records 被定义并且不为空...
@endisset

@empty($records)
    // $records 是「空」的...
@endempty

身份验证快捷方式#
@auth 和 @guest 指令可以用来快速确定当前用户是否已通过身份验证，是否为访客：
@auth
    // 用户已经通过身份认证...
@endauth

@guest
    // 用户没有通过身份认证...
@endguest



Switch 语句#
@switch($i)
    @case(1)
        First case...
        @break

    @case(2)
        Second case...
        @break

    @default
        Default case...
@endswitch


循环#
@for ($i = 0; $i < 10; $i++)
    目前的值为 {{ $i }}
@endfor

@foreach ($users as $user)
    <p>此用户为 {{ $user->id }}</p>
@endforeach

@forelse ($users as $user)
    <li>{{ $user->name }}</li>
@empty
    <p>没有用户</p>
@endforelse

@while (true)
    <p>死循环了。</p>
@endwhile


@foreach ($users as $user)
    @if ($user->type == 1)
        @continue
    @endif

    <li>{{ $user->name }}</li>

    @if ($user->number == 5)
        @break
    @endif
@endforeach

你还可以使用一行代码包含指令声明的条件：
@foreach ($users as $user)
    @continue($user->type == 1)

    <li>{{ $user->name }}</li>

    @break($user->number == 5)
@endforeach

循环变量#
循环时，可以在循环内使用 $loop 变量。这个变量可以提供一些有用的信息，比如当前循环的索引，当前循环是不是首次迭代，又或者当前循环是不是最后一次迭代：
@foreach ($users as $user)
    @if ($loop->first)
        这是第一个迭代。
    @endif

    @if ($loop->last)
        这是最后一个迭代。
    @endif

    <p>This is user {{ $user->id }}</p>
@endforeach

在一个嵌套的循环中，可以通过使用 $loop 变量的 parent 属性来获取父循环中的 $loop 变量：
@foreach ($users as $user)
    @foreach ($user->posts as $post)
        @if ($loop->parent->first)
            This is first iteration of the parent loop.
        @endif
    @endforeach
@endforeac

$loop 变量也包含了其它各种有用的属性：
$loop->index    当前循环迭代的索引（从0开始）。

$loop->iteration    当前循环迭代 （从1开始）。

$loop->remaining    循环中剩余迭代数量。

$loop->count    迭代中的数组项目总数。

$loop->first    当前迭代是否是循环中的首次迭代。

$loop->last 当前迭代是否是循环中的最后一次迭代。

$loop->depth    当前循环的嵌套级别。

$loop->parent   在嵌套循环中，父循环的变量。

注释#
{{-- 此注释将不会出现在渲染后的 HTML --}}

PHP#
@php
    //
@endphp


引入子视图#
<div>
    @include('shared.errors')

    <form>
        <!-- Form Contents -->
    </form>
</div>

被引入的视图会继承父视图中的所有数据，同时也可以向引入的视图传递额外的数组数据
@include('view.name', ['some' => 'data'])

当然，如果尝试使用 @include 去引入一个不存在的视图，Laravel 会抛出错误。如果想引入一个可能存在或可能不存在的视图，就使用 @includeIf 指令:
@includeIf('view.name', ['some' => 'data'])

如果要根据给定的布尔条件 @include 视图，可以使用 @includeWhen 指令：
@includeWhen($boolean, 'view.name', ['some' => 'data'])

!!请避免在 Blade 视图中使用 __DIR__ 及 __FILE__ 常量，因为它们会引用编译视图时缓存的位置。



为集合渲染视图#
@each('view.name', $jobs, 'job')

第一个参数是对数组或集合中的每个元素进行渲染的部分视图。
第二个参数是要迭代的数组或集合，
而第三个参数是将被分配给视图中当前迭代的变量名称

你也可以传递第四个参数到 @each 命令。当需要迭代的数组为空时，将会使用这个参数提供的视图来渲染
@each('view.name', $jobs, 'job', 'view.empty')

！！通过 @each 渲染的视图不会从父视图继承变量。 如果子视图需要这些变量，则应该使用 @foreach 和 @include。


堆栈#
@push('scripts')
    <script src="/example.js"></script>
@endpush

你可以根据需要多次压入堆栈，通过 @stack 命令中传递堆栈的名称来渲染完整的堆栈内容：
<head>
    <!-- Head Contents -->

    @stack('scripts')
</head>

服务注入#
@inject 命令可用于从 Larvel 服务容器 中检索服务。传递给 @inject 的第一个参数为置放该服务的变量名称，而第二个参数是要解析的服务的类或是接口的名称：
@inject('metrics', 'App\Services\MetricsService')

<div>
    Monthly Revenue: {{ $metrics->monthlyRevenue() }}.
</div>



Blade 扩展#
Blade 甚至允许你使用 directive 方法来定义自定义指令。当 Blade 编译器遇到自定义指令时，它将使用指令包含的表达式调用提供的回调。
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 执行注册后引导服务。
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('datetime', function ($expression) {
            return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
        });
    }

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

如你所见，我们可以链式调用在指令中传递的任何表达式的 format 方法。所以，在这个例子里，该指令最终生成了以下 PHP 代码：
<?php echo ($var)->format('m/d/Y H:i'); ? >

自定义 If 语句#
use Illuminate\Support\Facades\Blade;

/**
 * Perform post-registration booting of services.
 *
 * @return void
 */
public function boot()
{
    Blade::if('env', function ($environment) {
        return app()->environment($environment);
    });
}


定义了自定义条件之后，就可以很轻松地在模板中使用它：
@env('local')
    // The application is in the local environment...
@else
    // The application is not in the local environment...
@endenv