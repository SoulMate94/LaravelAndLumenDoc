<?php

数据库：入门#
简介#
Laravel 通过使用原始 SQL 与数据库的各种数据库进行交互, 非常简单。尤其流畅的使用 查询语句构造器，和 Eloquent ORM
当前，Laravel 支持四种类型的数据库:
    MySQL
    Postgres
    SQLite
    SQL Server

配置信息#
Laravel 应用程序的数据库配置文件放置在 config/database.php 文件中
默认情况下，Laravel 的环境配置 示例会使用 Laravel Homestead，这是一种方便的虚拟机，用于在本地机器上进行 Laravel 的开发。当然，您可以根据本地数据库的需要随意修改这个配置

SQLite 配置#
使用 touch database/database.sqlite 命令创建一个新的 SQLite 文件, 您可以通过使用数据库的绝对路径，轻松地配置环境变量，并指向这个新创建的数据库:
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

SQL Server 配置#
Laravel 支持 SQL Server 数据库; 无论以何种方式, 您都需要将数据库的连接配置添加到您的 config/database.php 配置文件中:
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'host' => env('DB_HOST', 'localhost'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
],


读&写的分离#
有时您可能希望使用数据库的一个连接，只用于 SELECT ，另一个用于 INSERT, UPDATE, 和 DELETE
如何配置读/写连接，让我们看一下这个示例:
'mysql' => [
    'read' => [
        'host' => '192.168.1.1',
    ],
    'write' => [
        'host' => '196.168.1.2'
    ],
    'sticky'    => true,
    'driver'    => 'mysql',
    'database'  => 'database',
    'username'  => 'root',
    'password'  => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
],

注意，在上面的示例中，配置数组中添加了两个键 : read 和 write 。这两个键都包含了一个数组，键的值为: host 。read 和 write 连接的其余配置都在 mysql 这个主数组里面

sticky 选项
sticky 是一个可选的选项，它的具体作用是：若在当前的请求周期内，数据库曾经被写入过一些数据，sticky 选项会立即将这些数据读出来。如果 sticky 选项是 true,而且在当前的请求周期内对数据看执行过 ”写入“ 操作，那么任何 "读取" 的操作都会使用「写」连接
这使得任何在同一请求周期写入的数据都会被立刻读取。这个取决于这个选项的作用是否符合你的程序的期望。



使用多数据库连接#
当使用多个连接时，您可以使用 DB facade 的 connection 方法。 通过 config/database.php 配置信息文件中定义好的数据库连接， 将 name 做为 connection 这个方法的参数传递进去 ：
$users = DB::connection('foo')->select(...);

您还可以使用 getPdo 方法访问原始的PDO实例 ：
$pdo = DB::connection()->getPdo();

运行原生 SQL 语句#
配置好数据库连接后，可以使用 DB facade 运行查询
DB facade 为每种类型的查询提供了方法：select，update，insert，delete 和 statement 。

运行 Select#

运行一个基础的查询语句，你可以使用 DB facade 的 select 方法:
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 查询应用中被激活的所有用户列表
     *
     * @return Response
     */
    public function index()
    {
        $users = DB::select('select * from users where active = ?', [1]);

        return view('user.index', ['users' => $users]);
    }
}


select 方法将始终返回一个数组结果集。数组中的每个结果将是一个PHP StdClass 对象，可以像下面这样访问结果值:
foreach ($users as $user) {
    echo $user->name;
}


使用命名绑定#
除了使用 ? 来表示参数绑定外，你也可以使用命名绑定运行查找：
$results = DB::select('select * from users where id = :id', ['id' => 1]);

运行 Insert#
DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);

运行 Update#
$affected = DB::update('update users set votes = 100 where name = ?', ['John']);

运行 Delete#
$deleted = DB::delete('delete from users');

运行一般声明#
一些数据库语句不返回任何值。对于这些类型的操作，您可以在 DB facade 上使用 statement 方法：
DB::statement('drop table users');



监听查询事件#
如果你希望能够监控到程序执行的每一条 SQL 语句，那么你可以使用 listen 方法
这个方法对于记录查询或调试非常有用。您可以将查询侦听器注册到一个 服务提供者:
<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 启动应用服务。
     *
     * @return void
     */
    public function boot()
    {
        DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
        });
    }

    /**
     * 注册服务提供者。
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

数据库事务#

您可以在 DB facade 上使用 transaction 方法，在数据库事务中运行一组操作。如果在事务 Closure 中抛出一个异常，那么事务将自动回滚。如果 Closure 成功执行，事务将自动被提交。您不需要担心在使用事务方法时手动回滚或提交。
DB::transaction(function () {
    DB::table('users')->update(['votes' => 1]);

    DB::table('posts')->delete();
});


处理死锁#
transaction 方法接受一个可选的第二个参数，该参数定义在发生死锁时，应该重新尝试事务的次数。一旦这些尝试都用尽了，就会抛出一个异常：
DB::transaction(function () {
    DB::table('users')->update(['votes' => 1]);

    DB::table('posts')->delete();
}, 5);


手动操作事务#
如果您想要手工开始一个事务，并且对回滚和提交有完全的控制，那么您可以在 DB facade 上使用 beginTransaction 方法：
DB::beginTransaction();

您可以通过 rollBack 方法回滚事务：
DB::rollback();

最后, 您可以通过 commit 方法提交事务：
DB::commit();