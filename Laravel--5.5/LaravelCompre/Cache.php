<?php

Laravel 的缓存系统#
配置信息#
缓存配置信息位于 config/cache.php，在这个文件中你可以为你的应用程序指定默认的缓存驱动，Laravel 支持当前流行的缓存系统，如非常棒的 Memcached 和 Redis 

//Laravel 默认使用将序列化缓存对象保存在文件系统中的 file 缓存驱动，对于大型应用程序而言，推荐你使用如 Memcached 或者 Redis 这样更强大的缓存驱动


驱动前提条件#
数据库#
//当使用 database 缓存驱动时，你需要配置一个用来存放缓存项的数据库表，下面是一个 Schema 数据表结构声明的示例：
Schema::create('cache', function ($table) {
    $table->string('key')->unique();
    $table->text('value');
    $table->integer('expiration');
});

//你也可以使用 php artisan cache:table 这个 Artisan 命令生成一个有合适数据表结构的 migration

Memcached#
//使用 Memcached 驱动需要安装 Memcached PECL 扩展包 。你可以把所有 Memcached 服务器都列在 config/cache.php 这个配置信息文件中：
'memcached' => [
    [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 100
    ],
],
//当然你也可以把 host 选项配置为 UNIX 的 socket 路径。如果你这样配置了， port 选项应该设置为 0:
'memcached' => [
    [
        'host' => '/var/run/memcached/memcached.sock',
        'port' => 0,
        'weight' => 100
    ],
],
//在使用 Redis 作为 Laravel 的缓存驱动前，你需要通过 Composer 安装 predis/predis 扩展包 (~1.0) 或者使用 PECL 安装 PhpRedis PHP 拓展。

缓存的使用#
获取一个缓存实例#
//Illuminate\Contracts\Cache\Factory 和 Illuminate\Contracts\Cache\Repository contracts 提供了访问 Laravel 缓存服务的机制。
//Factory contract 则为你的应用程序定义了访问所有缓存驱动的机制
//Repository contract 是典型的用 cache 配置信息文件指定你的应用程序默认缓存驱动的实现。
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * Show a list of all users of the application.
     *
     * @return Response
     */
    public function index()
    {
        $value = Cache::get('key');

        //
    }
}

访问多个缓存仓库#
//使用 Cache facade, 你可以通过 store 方法来访问缓存仓库
//  传入 store 方法的键应该对应一个 cache 配置信息文件中的 stores 配置信息数组中列出的配置值：
$value = Cache::store('file')->get('foo');

Cache::store('redis')->put('bar', 'baz', 10);

从缓存中获取项目#
//Cache facade 中的 get 方法用来从缓存中获取缓存项，如果缓存中不存在该缓存项，则返回 null
//你也可以向 get 方法传递第二个参数，用来指定缓存项不存在时返回的默认值
$value = Cache::get('key');

$value = Cache::get('key', 'default');
//你甚至可以将 Closure 作为默认值传递。如果指定的缓存项在缓存中不存在， Closure 的结果将被返回

//传递一个闭包允许你延迟从数据库或外部服务中取出默认值：
$value = Cache::get('key', function () {
    return DB::table(...)->get();
});

确认项目是否存在#
//has 方法可以用来检查一个项目是否存在于缓存中，如果值为 null 或 false ，则此方法将返回 false：
if (Cache::has('key')) {
    //
}

递增与递减值#
//increment 和 decrement 方法可以用来调整缓存中整数项目值
Cache::increment('key');
Cache::increment('key', $amount);
Cache::decrement('key');
Cache::decrement('key', $amount);

获取和更新#
//有时你可能会想从缓存中取出一个项目，但也想在取出的项目不存在时存入一个默认值，例如，你可能会想从缓存中取出所有用户，或者当用户不存在时，从数据库中将这些用户取出并放入缓存中，你可以使用 Cache::remember 方法实现：
$value = Cache::remember('users', $minutes, function () {
    return DB::table('users')->get();
});

//如果缓存项在缓存中不存在，则返回给 remember 方法的 Closure 将会被运行，其结果将被放置在缓存中

获取和删除#
//如果你需要从缓存中获取一个缓存项然后删除它，你可以使用 pull 方法。像 get 方法一样，如果缓存项在缓存中不存在， 则返回 null :
$value = Cache::pull('key');


存放项目到缓存中#
// 你可以使用 Cache facade 的 put 方法来存放缓存项到缓存中，当你在缓存中存放缓存项时，你需要使用第三个参数来设定缓存的存放时间：
Cache::put('key', 'value', $minutes);

//如果要指定一个缓存项过期的分钟数，你也可以传递一个 DateTime 实例来表示该缓存项过期的时间点：
$expiresAt = Carbon::now()->addMinutes(10);

Cache::put('key', 'value', $expiresAt);

写入目前不存在的项目#
//add 方法只会把暂时不存在于缓存中的缓存项放入缓存，如果存放成功将返回 true ，否则返回 false ：
Cache::add('key', 'value', $minutes);

永久写入项目#
//forever 方法可以用来将缓存项永久存入缓存中，因为这些缓存项不会过期，所以必须通过 forget 方法手动删除：
Cache::forever('key', 'value');
!!如果你使用 Memcached 驱动，那么当缓存达到大小限制时，那些「永久」保存的缓存项可能会被删除

删除缓存中的项目#
//你可以使用 forget 方法从缓存中移除一个项目：
Cache::forget('key');

你也可以使用 flush 方法清空所有缓存：
Cache::flush();

Cache 帮助函数#
//当 cache 只接收一个字符串参数的时候，它将会返回给定键对应的值：
$value = cache('key');

//如果你传给函数一个键值对数组和过期时间，它将会把值和过期时间保存在缓存中：
cache(['key' => 'value'], $minutes);

cache(['key' => 'value'], Carbon::now()->addSeconds(10));

!!如果在测试中使用全局函数 cache ，你应该使用 Cache::shouldReceive 方法，就好像你在 测试 facade一样。

缓存标签#
!!缓存标签并不支持使用 file 或 database 的缓存驱动。此外，当在缓存使用多个标签并 「永久」写入时，类似 memcached 的驱动性能会是最佳的，且会自动清除旧的纪录


写入被标记的缓存项#
//缓存标签允许你在缓存中标记关联的项目，并清空所有已分配指定标签的缓存值
Cache::tags(['people', 'artists'])->put('John', $john, $minutes);

Cache::tags(['people', 'authors'])->put('Anne', $anne, $minutes);


访问被标记的缓存项#
//若要获取一个被标记的缓存项，只要传递一样的有序标签列表至 tags 方法，然后通过你希望获取的值对应的键来调用 get 方法：
$john = Cache::tags(['people', 'artists'])->get('John');

$anne = Cache::tags(['people', 'authors'])->get('Anne');

移除被标记的缓存项#
//你可以清空已分配的单个标签或是一组标签列表中的所有缓存项
//例如，下方的语句会把被标记为 people ， authors，或两者都标记了的缓存都移除。所以， Anne 与 John 都会从缓存中移除：
Cache::tags(['people', 'authors'])->flush();

//相反的，下方的语句只会删除被标记为 authors 的缓存，所以 Anne 会被移除，但 John 不会：
Cache::tags('authors')->flush();

增加自定义的缓存驱动#


写驱动#
//为了创建自定义的缓存驱动，首先我们需要部署 Illuminate\Contracts\Cache\Store contract 。所以 MongoDB 缓存实现看起来会像这样：
<?php

namespace App\Extensions;

use Illuminate\Contracts\Cache\Store;

class MongoStore implements Store
{
    public function get($key) {}
    public function many(array $keys);
    public function put($key, $value, $minutes) {}
    public function putMany(array $values, $minutes);
    public function increment($key, $value = 1) {}
    public function decrement($key, $value = 1) {}
    public function forever($key, $value) {}
    public function forget($key) {}
    public function flush() {}
    public function getPrefix() {}
}

//我们只需要通过一个 MongoDB 的连接来实现这些方法。关于如何实现这些方法，可以查看框架源代码中的 Illuminate\Cache\MemcachedStore 。一旦我们的部署完成，我们就可以完成自定义驱动的注册了。
Cache::extend('mongo', function ($app) {
    return Cache::repository(new MongoStore);
});

注册驱动#
//通过 Laravel 注册自定义缓存驱动，我们将用到 Cache facade 的 extend 方法
<?php

namespace App\Providers;

use App\Extensions\MongoStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('mongo', function ($app) {
            return Cache::repository(new MongoStore);
        });
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
//传递给 extend 方法的第一个参数是驱动名称。这取决于你的 config/cache.php 配置信息文件的 driver 选项。第二个参数是一个闭包，它应该返回一个 Illuminate\Cache\Repository 实例。这个闭包将传递一个service container的 $app 实例。

一旦你的扩展被注册，就可以轻松的更新 config/cache.php 配置信息文件的 driver 选项为你的扩展名称

缓存事件#
//为了在每一次缓存操作时执行代码，你可以监听缓存触发的事件 事件 。一般来说，你应该将这些事件监听器放置在 EventServiceProvider 中:
**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'Illuminate\Cache\Events\CacheHit' => [
        'App\Listeners\LogCacheHit',
    ],

    'Illuminate\Cache\Events\CacheMissed' => [
        'App\Listeners\LogCacheMissed',
    ],

    'Illuminate\Cache\Events\KeyForgotten' => [
        'App\Listeners\LogKeyForgotten',
    ],

    'Illuminate\Cache\Events\KeyWritten' => [
        'App\Listeners\LogKeyWritten',
    ],
];
