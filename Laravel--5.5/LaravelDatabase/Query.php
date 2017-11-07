<?php

Laravel 数据库之：数据库请求构建器#
简介#
Laravel 的查询构造器使用 PDO 参数绑定，来保护你的应用程序免受 SQL 注入的攻击

获取结果#
从数据表中获取所有的数据列#
// 你可以使用 DB facade 的 table 方法开始查询。这个 table 方法针对查询表返回一个查询构造器实例，允许你在查询时链式调用更多约束，并使用 get 方法获取最终结果：
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Show a list of all of the application's users.
     *
     * @return Response
     */
    public function index()
    {
        $users = DB::table('users')->get();

        return view('user.index', ['users' => $users]);
    }
}

get 方法会返回一个 Illuminate\Support\Collection 结果，其中每个结果都是一个 PHP StdClass 对象的实例。您可以通过访问列中对象的属性访问每个列的值：
foreach ($users as $user) {
    echo $user->name;
}


从数据表中获取单个列或行#
$user = DB::table('users')->where('name', 'John')->first();

echo $user->name;

如果你不需要一整行数据，则可以使用 value 方法来从单条记录中取出单个值。此方法将直接返回字段的值：
$email = DB::table('users')->where('name', 'John')->value('email');

获取一列的值#
如果你想要获取一个包含单个字段值的集合，可以使用 pluck 方法。
$titles = DB::table('roles')->pluck('title');

foreach ($titles as $title) {
    echo $title;
}

你也可以在返回的数组中指定自定义的键值字段：
$roles = DB::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}


结果分块#
如果你需要操作数千条数据库记录，可以考虑使用 chunk 方法
这个方法每次只取出一小块结果，并会将每个块传递给一个 闭包 处理
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});

你可以从 闭包 中返回 false，以停止对后续分块的处理：
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    // Process the records...

    return false;
})


聚合#
查询构造器也支持各种聚合方法，如 count、 max、 min、 avg 和 sum
$users = DB::table('users')->count();

$price = DB::table('orders')->max('price');

当然，你也可以将这些方法结合其它子句来进行查询：
$price = DB::table('orders')
                ->where('finalized', 1)
                ->avg('price');



Selects#
指定一个 Select 子句#
$users = DB::table('users')->select('name', 'email as user_email')->get();

distinct 方法允许你强制让查询返回不重复的结果：
$users = DB::table('users')->distinct()->get();

如果你已有一个查询构造器实例，并且希望在现有的 select 子句中加入一个字段，则可以使用 addSelect 方法：
$query = DB::table('users')->select('name');

$users = $query->addSelect('age')->get();



原始表达式#
要小心避免造成 SQL 注入攻击！要创建一个原始表达式，可以使用 DB::raw 方法：
$users = DB::table('users')
                     ->select(DB::raw('count(*) as user_count, status'))
                     ->where('status', '<>', 1)
                     ->groupBy('status')
                     ->get();

Joins#
Inner Join 语法#
$users = DB::table('users')
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.*', 'contacts.phone', 'orders.price')
            ->get();

Left Join 语法#
$users = DB::table('users')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
            ->get();

Cross Join 语法#
使用 crossJoin 方法和你想要交叉连接的表名来做「交叉连接」。交叉连接通过第一个表和连接表生成一个笛卡尔积：
$users = DB::table('sizes')
            ->crossJoin('colours')
            ->get();

高级 Join 语法#
DB::table('users')
        ->join('contacts', function ($join) {
            $join->on('users.id', '=', 'contacts.user_id')->orOn(...);
        })
        ->get();

如果你想要在连接中使用「where」风格的子句，则可以在连接中使用 where 和 orWhere 方法
DB::table('users')
        ->join('contacts', function ($join) {
            $join->on('users.id', '=', 'contacts.user_id')
                 ->where('contacts.user_id', '>', 5);
        })
        ->get();

Unions#
查询构造器也提供了一个快捷的方法来「合并」 两个查询
例如，你可以先创建一个初始查询，并使用 union 方法将它与第二个查询进行合并：
$first = DB::table('users')
            ->whereNull('first_name');

$users = DB::table('users')
            ->whereNull('last_name')
            ->union($first)
            ->get();
也可使用 unionAll 方法，它和 union 方法有着相同的用法。



Where 子句#
简单的 Where 子句#
$users = DB::table('users')->where('votes', '=', 100)->get();
为方便起见，如果你只是想简单的校验某个字段等于一个指定的值，你可以直接将这个值作为第二个参数传入 where 方法：
$users = DB::table('users')->where('votes', 100)->get();


当然，在编写 where 子句时，你也可以使用各种数据库所支持其它的运算符：
$users = DB::table('users')
                ->where('votes', '>=', 100)
                ->get();

$users = DB::table('users')
                ->where('votes', '<>', 100)
                ->get();

$users = DB::table('users')
                ->where('name', 'like', 'T%')
                ->get();

你也可以通过一个条件数组做 where 的查询：
$users = DB::table('users')->where([
    ['status', '=', '1'],
    ['subscribed', '<>', '1'],
])->get();


Or 语法#
$users = DB::table('users')
                    ->where('votes', '>', 100)
                    ->orWhere('name', 'John')
                    ->get();

其它 Where 子句#
whereBetween
$users = DB::table('users')
                    ->whereBetween('votes', [1, 100])->get();

whereNotBetween
$users = DB::table('users')
                    ->whereNotBetween('votes', [1, 100])
                    ->get();

whereIn 与 whereNotIn
$users = DB::table('users')
                    ->whereIn('id', [1, 2, 3])
                    ->get();

$users = DB::table('users')
                    ->whereNotIn('id', [1, 2, 3])
                    ->get();

whereNull 与 whereNotNull
$users = DB::table('users')
                    ->whereNull('updated_at')
                    ->get();

$users = DB::table('users')
                    ->whereNotNull('updated_at')
                    ->get();

whereDate / whereMonth / whereDay / whereYear
whereDate 方法比较某字段的值与指定的日期是否相等：
$users = DB::table('users')
                ->whereDate('created_at', '2016-12-31')
                ->get();

whereMonth 方法比较某字段的值是否与一年的某一个月份相等：
$users = DB::table('users')
                ->whereMonth('created_at', '12')
                ->get();

whereDay 方法比较某列的值是否与一月中的某一天相等：
$users = DB::table('users')
                ->whereDay('created_at', '31')
                ->get();

whereYear 方法比较某列的值是否与指定的年份相等：
$users = DB::table('users')
                ->whereYear('created_at', '2016')
                ->get();

whereColumn
whereColumn 方法用来检测两个列的数据是否一致：
$users = DB::table('users')
                ->whereColumn('first_name', 'last_name')
                ->get();

此方法还可以使用运算符：
$users = DB::table('users')
                ->whereColumn('updated_at', '>', 'created_at')
                ->get();

whereColumn 方法可以接收数组参数。条件语句会使用 and 连接起来：
$users = DB::table('users')
                ->whereColumn([
                    ['first_name', '=', 'last_name'],
                    ['updated_at', '>', 'created_at']
                ])->get();


参数分组#
DB::table('users')
            ->where('name', '=', 'John')
            ->orWhere(function ($query) {
                $query->where('votes', '>', 100)
                      ->where('title', '<>', 'Admin');
            })
            ->get();

这个例子会生成以下 SQL：
select * from users where name = 'John' or (votes > 100 and title <> 'Admin')



Where Exists 语法#
whereExists 方法允许你编写 where exists SQL 子句
DB::table('users')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('orders')
                      ->whereRaw('orders.user_id = users.id');
            })
            ->get();

上述查询将生成以下 SQL：
select * from users
where exists (
    select 1 from orders where orders.user_id = users.id
)



JSON 查询语句#
Laravel 也支持查询 JSON 类型的字段。目前，本特性仅支持 MySQL 5.7+ 和 Postgres数据库。可以使用 -> 运算符来查询 JSON 列数据：
$users = DB::table('users')
                ->where('options->language', 'en')
                ->get();

$users = DB::table('users')
                ->where('preferences->dining->meal', 'salad')
                ->get();


Ordering, Grouping, Limit, & Offset#
orderBy#
$users = DB::table('users')
                ->orderBy('name', 'desc')
                ->get();

latest / oldest#
latest 和 oldest 方法允许你更容易的依据日期对查询结果排序。默认查询结果将依据 created_at 列。或者,你可以使用字段名称排序：
$user = DB::table('users')
                ->latest()
                ->first();

inRandomOrder#
inRandomOrder 方法可以将查询结果随机排序。例如，你可以使用这个方法获取一个随机用户：
$randomUser = DB::table('users')
                ->inRandomOrder()
                ->first();

groupBy / having / havingRaw#
groupBy 和 having 方法可用来对查询结果进行分组。having 方法的用法和 where 方法类似：
$users = DB::table('users')
                ->groupBy('account_id')
                ->having('account_id', '>', 100)
                ->get();

havingRaw 方法可以将一个原始的表达式设置为 having 子句的值。例如，我们能找出所有销售额超过 2,500 元的部门：
$users = DB::table('orders')
                ->select('department', DB::raw('SUM(price) as total_sales'))
                ->groupBy('department')
                ->havingRaw('SUM(price) > 2500')
                ->get();

skip / take#
你可以使用 skip 和 take 方法来限制查询结果数量或略过指定数量的查询：
$users = DB::table('users')->skip(10)->take(5)->get();
或者，你也可以使用 limit 和 offset 方法：
$users = DB::table('users')
                ->offset(10)
                ->limit(5)
                ->get();



条件语句#
$role = $request->input('role');

$users = DB::table('users')
                ->when($role, function ($query) use ($role) {
                    return $query->where('role_id', $role);
                })
                ->get();
只有当 when 方法的第一个参数为 true 时，闭包里的 where 语句才会执行。如果第一个参数是 false，这个闭包将不会被执行。

$sortBy = null;

$users = DB::table('users')
                ->when($sortBy, function ($query) use ($sortBy) {
                    return $query->orderBy($sortBy);
                }, function ($query) {
                    return $query->orderBy('name');
                })
                ->get();

Inserts#
DB::table('users')->insert(
    ['email' => 'john@example.com', 'votes' => 0]
);

你甚至可以在 insert 调用中传入一个嵌套数组向表中插入多条记录。每个数组表示要插入表中的行：
DB::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);

自增 ID#
$id = DB::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);



Updates#
DB::table('users')
            ->where('id', 1)
            ->update(['votes' => 1]);

更新 JSON#
当更新一个JSON 列时,你应该使用 -> 语法来访问 JSON 对象的键。仅在数据库支持 JSON 列的时候才可使用这个操作：
DB::table('users')
            ->where('id', 1)
            ->update(['options->enabled' => true]);


自增或自减#
DB::table('users')->increment('votes');

DB::table('users')->increment('votes', 5);

DB::table('users')->decrement('votes');

DB::table('users')->decrement('votes', 5);

您还可以指定要操作中更新其它字段：
DB::table('users')->increment('votes', 1, ['name' => 'John']);

Deletes#
DB::table('users')->delete();

DB::table('users')->where('votes', '>', 100)->delete();

如果你需要清空表，你可以使用 truncate 方法，这将删除所有行，并重置自动递增 ID 为零：
DB::table('users')->truncate();



悲观锁#
查询构造器也包含一些可以帮助你在 select 语法上实现「悲观锁定」的函数 
若要在查询中使用「共享锁」，可以使用 sharedLock 方法。共享锁可防止选中的数据列被篡改，直到事务被提交为止：

DB::table('users')->where('votes', '>', 100)->sharedLock()->get();
另外，你也可以使用 lockForUpdate 方法。使用「更新」锁可避免行被其它共享锁修改或选取：
DB::table('users')->where('votes', '>', 100)->lockForUpdate()->get();
