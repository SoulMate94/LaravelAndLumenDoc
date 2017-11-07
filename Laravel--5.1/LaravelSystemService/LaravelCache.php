<?php

#缓存#
#配置信息
//缓存的配置文件都放在 config/cache.php 中
//Laravel 默认采用的缓存驱动是 file
译者注：推荐使用 Redis 来做缓存驱动。缓存和 Session 一起使用 Redis 的话，还需要多余的配置，请参考 - Laravel 下配置 Redis 让缓存、Session 各自使用不同的 Redis 数据库

#场景布置#
数据库#
//当使用 database 这个缓存驱动时，你需要配置一个数据库表来放置缓存项目，下面是表结构：
Schema::create('cache', function($table) {
    $table->string('key')->unique();
    $table->text('value');
    $table->integer('expiration');
});

Memcached#
//使用 Memcached 做缓存需要先安装 Memcached PECL 扩展包。
//默认的 配置文件 采用以 Memcached::addServer 为基础的 TCP/IP：
'memcached' => [
    [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 100
    ],
],
//你可能也想配置 host 选项到 UNIX 的 socket 路径中，如果你这么做了，记得 port 选项要设为 0
'memcached' => [
    [
        'host' => '/var/run/memcached/memcached.sock',
        'port' => 0,
        'weight' => 100
    ],
],

Redis#
//在你选择使用 Redis 作为 Laravel 的缓存之前，你需要通过 Composer 预先安装 predis/predis 扩展包 (~1.0)

#获取一个缓存的实例
//Illuminate\Contracts\Cache\Factory 和 Illuminate\Contracts\Cache\Repository contracts 提供了访问 Laravel 缓存服务的机制

// 而 Factory contract 则为你的应用程序提供了访问所有缓存驱动的机制，Repository contract 是典型的缓存驱动实现，它会依照你的缓存配置文件的变化而变化

<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Routing\Controller;

class UserController extends Controller
{
    /**
     * 显示应用程序中所有用户列表。
     *
     * @return Response
     */
    public function index()
    {
        $value = Cache::get('key');

        //
    }
}

//访问多个缓存仓库#
$value = Cache::store('file')->get('foo');

Cache::store('redis')->put('bar', 'baz', 10);

#从缓存中获取项目
//在 Cache facade 中，get 方法可以用来取出缓存中的项目，缓存不存在的话返回 null，get 方法接受第二个参数，作为找不到项目时返回的预设值：
$value = Cache::get('key');

$value = Cache::get('key', 'default');

//你甚至可以传入一个闭包作为默认值，当指定的项目不存在缓存中时，闭包将会被返回，传入一个闭包允许你延迟从数据库或外部服务中取出值：
$value = Cache::get('key', function() {
    return DB::table(...)->get();
});

//确认项目是否存在
if (Cache::has('key')) {
    //
}

//递增与递减值
Cache::increment('key');

Cache::increment('key', $amount);

Cache::decrement('key');

Cache::decrement('key', $amount);

//取出或更新
//有时候，你可能会想从缓存中取出一个项目，但也想在当取出的项目不存在时存入一个默认值
//例如，你可能会想从缓存中取出所有用户，当找不到用户时，从数据库中将这些用户取出并放入缓存中，你可以使用 Cache::remember 方法达到目的：
$value = Cache::remember('users', $minutes, function() {
    return DB::table('users')->get();
});
//如果那个项目不存在缓存中，则返回给 remember 方法的闭包将会被运行，而且闭包的运行结果将会被存放在缓存中。
//也可以结合 remember 和 forever 这两个方法来 ”永久“ 存储缓存：
$value = Cache::rememberForever('users', function() {
    return DB::table('users')->get();
});

//取出与删除
//如果对象不存在缓存中，pull 方法将会返回 null：
$value = Cache::pull('key');

#存放项目到缓存中
//你可以使用 Cache facade 的 put 方法来存放项目到缓存中，你需要使用第三个参数来设定缓存的存放时间：
Cache::put('key', 'value', $minutes);
//如果要指定一个缓存项目过期的分钟数，你也可能需要传递一个 PHP 的 DateTime 实例来表示该缓存项目过期的时间点：
$expiresAt = Carbon::now()->addMinutes(10);

Cache::put('key', 'value', $expiresAt);

//add 方法只会把暂时不存在缓存中的项目放入缓存，如果成功存放，会返回 true，否则返回 false：
Cache::add('key', 'value', $minutes);
//forever 方法可以用来存放永久的项目到缓存中，这些值必须被手动的删除，这可以通过 forget 方法实现：
Cache::forever('key', 'value');

#删除缓存中的项目
//你可以使用 forget 方法从缓存中移除一个项目：
Cache::forget('key');
//也使用 flush 方法清空所有缓存：
Cache::flush();
清空缓存 并不会 遵从缓存的前缀，并会将缓存中所有的项目删除。在清除与其它应用程序共用的缓存时应谨慎考虑这一点。

#加入自定义的缓存驱动
//我们可以在 Cache facade 中使用 extend 方法自定义缓存驱动来扩充 Laravel 缓存，它被用来绑定一个自定义驱动的解析器到管理者上，通常这可以通过 服务容器 来完成
//例如，要注册一个名为「mongo」的缓存驱动：
<?php

namespace App\Providers;

use Cache;
use App\Extensions\MongoStore;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * 运行注册后的启动服务。
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('mongo', function($app) {
            return Cache::repository(new MongoStore);
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

第一个传给 extend 方法的参数是驱动的名称，这个名称要与你在 config/cache.php 配置文件中，driver 选项指定的名称相同
第二个参数是一个应返回一个 Illuminate\Cache\Repository 实例的闭包，这个闭包会被传入一个 $app 实例，这个实例是属于类 服务容器 的。

//调用 Cache::extend 的工作可以在新加入的 Laravel 应用程序中默认的 App\Providers\AppServiceProvider 的 boot 方法中完成，或者你可以创建你自己的服务提供者来管理扩展功能（只是请别忘了在 config/app.php 中的服务提供者数组注册这个提供者）
为了创建我们的自定义缓存驱动，首先需要实现 Illuminate\Contracts\Cache\Store contract。因此我们的 MongoDB 缓存实现大概会长这样子：
<?php

namespace App\Extensions;

class MongoStore implements \Illuminate\Contracts\Cache\Store
{
    public function get($key) {}
    public function put($key, $value, $minutes) {}
    public function increment($key, $value = 1) {}
    public function decrement($key, $value = 1) {}
    public function forever($key, $value) {}
    public function forget($key) {}
    public function flush() {}
    public function getPrefix() {}
}

//我们只需要通过一个 MongoDB 的连接来实现这些方法，一旦我们完成实现，我们就可以接着完成注册我们的自定义驱动：
Cache::extend('mongo', function($app) {
    return Cache::repository(new MongoStore);
});

//如果你想让自定义扩展驱动设置为默认驱动，只需要更新 config/cache.php 配置文件中的 driver 选项为驱动的 key 即可，如这个例子的 mongo

//如果你不知道要将你的自定义缓存驱动代码放置在何处，可以考虑将它放在 Packagist 上！或者你可以在你的 app 目录下创建一个 Extension 的命名空间。但是请记住，Laravel 没有硬性规定应用程序的结构，你可以依照你的喜好任意组织你的应用程序


#缓存标签
注意： 缓存标签并不支持使用 file 或 dababase 的缓存驱动。此外，当在缓存使用多个标签并「永久」写入时，类似 memcached 的驱动性能会是最佳的，且会自动清除旧的纪录。

#写入被标记的缓存项目
//缓存标签允许你在缓存中标记关联的项目，并清空所有已分配指定标签的缓存值
Cache::tags(['people', 'artists'])->put('John', $john, $minutes);

Cache::tags(['people', 'authors'])->put('Anne', $anne, $minutes);

//当然，你不必限制于 put 方法。你可以在使用标签时使用任何缓存保存系统的方法

#获取被标记的缓存项目
//若要获取一个被标记的缓存项目，只要传递一样的标签串行表至 tags 方法：
$john = Cache::tags(['people', 'artists'])->get('John');

$anne = Cache::tags(['people', 'authors'])->get('Anne');

//你可以清空已分配的单个标签或是一组标签列表中的所有项目
//例如，下方的语法会把被标记 people、authors，或两者的缓存都给移除。所以，Anne 与 John 都从缓存中被移除
Cache::tags(['people', 'authors'])->flush();
//相反的，下方的语法只会删除被标示为 authors 的缓存，所以 Anne 会被移除，但 John 不会。
Cache::tags('authors')->flush();
#缓存事件
//你可以监听到缓存做每一次操作的触发 事件。一般来说，你必须将事件侦听器放置在 EventServiceProvider 的 boot 方法中：
/**
 * 为你的应用程序注册任何其它事件。
 *
 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
 * @return void
 */
public function boot(DispatcherContract $events)
{
    parent::boot($events);

    $events->listen('cache.hit', function ($key, $value) {
        //
    });

    $events->listen('cache.missed', function ($key) {
        //
    });

    $events->listen('cache.write', function ($key, $value, $minutes) {
        //
    });

    $events->listen('cache.delete', function ($key) {
        //
    });
}