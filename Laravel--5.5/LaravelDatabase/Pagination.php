<?php

Laravel 的分页功能#
简介#
Laravel 的分页器与 查询语句构造器 、 Eloquent ORM 集成在一起，为数据库结果集提供了便捷的、开箱即用的分页器。分页器生产的 HTML 兼容 Bootstrap CSS framework

基本用法#
paginate 方法会自动基于用户当前所查看的页面来设置适当的限制和偏移。

对查询语句构造器进行分页#
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 展示应用中的所有用户
     *
     * @return Response
     */
    public function index()
    {
        $users = DB::table('users')->paginate(15);

        return view('user.index', ['users' => $users]);
    }
}

"简单分页"#
$users = DB::table('users')->simplePaginate(15);



对 Eloquent 模型进行分页#
$users = App\User::paginate(15);

当然，你可以在对查询设置了其他约束条件之后调用 paginate 方法，例如 where 子句：
$users = User::where('votes', '>', 100)->paginate(15);

你也可以在对 Eloquent 模型进行分页使用 simplePaginate 方法：
$users = User::where('votes', '>', 100)->simplePaginate(15);

手动创建分页#
有些时候你可能希望手动创建一个分页实例，将其传递为一个项目数组。你可以依据你的需求创建 Illuminate\Pagination\Paginator 或 Illuminate\Pagination\LengthAwarePaginator 实例


显示分页结果#
<div class="container">
    @foreach ($users as $user)
        {{ $user->name }}
    @endforeach
</div>

{{ $users->links() }}

links 方法将会链接渲染到结果集中其余的页。这些链接中每一个都已经包含了适当的 page 查询字符串变量。记住，links 方法生产的 HTML 兼容 Bootstrap CSS framework 。


自定义分页器的 URI#
withPath 方法允许你在生成链接时自定义分页器所使用的 URI 
例如，如果你想分页器生成的链接如 http://example.com/custom/url?page=N，你应该传递 custom/url 到 withPath 方法：
Route::get('users', function () {
    $users = App\User::paginate(15);

    $users->withPath('custom/url');

    //
});

附加参数到分页链接中#
你可以使用 append 方法附加查询参数到分页链接中。例如，要附加 sort=votes 到每个分页链接，你应该这样调用 append 方法：
{{ $users->appends(['sort' => 'votes'])->links() }}

如果你希望附加「哈希片段」到分页器的链接中，你应该使用 fragment 方法。例如，要附加 #foo 到每个分页链接的末尾，应该这样调用 fragment 方法：
{{ $users->fragment('foo')->links() }}

将结果转换为JSON#
Laravel 分页器结果类实现了 Illuminate\Contracts\Support\Jsonable 接口契约并且提供 toJson 方法
Route::get('users', function () {
    return App\User::paginate();
});

从分页器获取的 JSON 将包含元信息，如： total, current_page, last_page 等等
实际的结果对象将通过 JSON 数组中的 data 键来获取。 以下是一个从路由返回分页器实例创建的 JSON 示例：
{
   "total": 50,
   "per_page": 15,
   "current_page": 1,
   "last_page": 4,
   "next_page_url": "http://laravel.app?page=2",
   "prev_page_url": null,
   "from": 1,
   "to": 15,
   "data":[
        {
            // Result Object
        },
        {
            // Result Object
        }
   ]
}



自定义分页视图#
在默认情况下，视图渲染显示的分页链接都兼容 Bootstrap CSS 框架
{{ $paginator->links('view.name') }}

// Passing data to the view...
{{ $paginator->links('view.name', ['foo' => 'bar']) }}


然而，自定义分页视图最简单的方法是通过 vendor:publish 命令将它们导出到你的 resources/views/vendor 目录：
php artisan vendor:publish --tag=laravel-pagination

这个命令将视图放置在 resources/views/vendor/pagination 目录中。这个目录下的 default.blade.php 文件对应于默认分页视图。你可以简单地编辑这个文件来修改分页的 HTML

分页器实例方法#
$results->count()

$results->currentPage()

$results->firstItem()

$results->hasMorePages()

$results->hasMorePages()

$results->lastPage() (Not available when using simplePaginate)

$results->nextPageUrl()

$results->perPage()

$results->previousPageUrl()

$results->total() (Not available when using simplePaginate)

$results->url($page)

