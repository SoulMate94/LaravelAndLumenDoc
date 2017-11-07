<?php

Lumen中使用速度更快的PhpRedis扩展（更新队列驱动）#

//但是在项目中使用Redis时发现Lumen默认使用的 predis/predis 会拖慢整体速度，特别是在高并发的情况下，所以寻思着使用 PhpRedis 代替，毕竟 PhpRedis 是C语言写的模块，性能上肯定优于 predis


Lumen中使用PhpRedis
//很简单，只需要在 bootstrap/app.php 中添加下列代码将PhpRedis注入容器即可：
$app->singleton('phpredis', function(){
    $redis = new Redis;
    $redis->pconnect('127.0.0.1'); //建立连接
        $redis->select(1); //选择库
        $redis->auth('xxxx'); //认证
    return $redis;
});
unset($app->availableBindings['redis']);
绑定后即可通过 app('phpredis') 直接使用 PhpRedis 了，具体使用方法可以看相应的官方文档。

Lumen中为PhpRedis增加Cache驱动
//由于实际使用中更多的将Redis用于缓存，Lumen自带的Redis缓存驱动是基于 predis/predis 实现，我们现在新建一个驱动以支持 Phpredis

我们首先创建一个 ServiceProvider ：
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use TargetLiu\PHPRedis\PHPRedisStore;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        Cache::extend('phpredis', function ($app) {
            return Cache::repository(new PHPRedisStore($app->make('phpredis'), $app->config['cache.prefix']));
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

//这样就建立一个名为 phpreids 的驱动。再创建一个基于 Illuminate\Contracts\Cache\Store 契约的缓存操作类用以操作 PhpRedis
<?php

namespace TargetLiu\PHPRedis;

use Illuminate\Contracts\Cache\Store;

class PHPRedisStore implements Store
{

    /**
     * The Redis database connection.
     *
     * @var \Illuminate\Redis\Database
     */
    protected $redis;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Create a new Redis store.
     *
     * @param  \Illuminate\Redis\Database  $redis
     * @param  string  $prefix
     * @return void
     */
    public function __construct($redis, $prefix = '')
    {
        $this->redis = $redis;
        $this->setPrefix($prefix);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {
        if (!is_null($value = $this->connection()->get($this->prefix . $key))) {
            return is_numeric($value) ? $value : unserialize($value);
        }
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys)
    {
        $return = [];

        $prefixedKeys = array_map(function ($key) {
            return $this->prefix . $key;
        }, $keys);

        $values = $this->connection()->mGet($prefixedKeys);

        foreach ($values as $index => $value) {
            $return[$keys[$index]] = is_numeric($value) ? $value : unserialize($value);
        }

        return $return;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->set($this->prefix . $key, $value, (int) max(1, $minutes * 60));
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  int  $minutes
     * @return void
     */
    public function putMany(array $values, $minutes)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value, $minutes);
        }
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return $this->connection()->incrBy($this->prefix . $key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->connection()->decrBy($this->prefix . $key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever($key, $value)
    {
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->set($this->prefix . $key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return (bool) $this->connection()->delete($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->connection()->flushDb();
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Predis\ClientInterface
     */
    public function connection()
    {
        return $this->redis;
    }

    /**
     * Get the Redis database instance.
     *
     * @return \Illuminate\Redis\Database
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = !empty($prefix) ? $prefix . ':' : '';
    }
}

//通过以上两个步骤基本上就完成了Cache驱动的创建，现在只需要在 bootstrap/app.php 中注入新建的Cache驱动然后配置 .env 中 CACHE_DRIVER = phpredis ，最后再在 config/cache.php 中加入相应的驱动代码即可
'phpredis' => [
    'driver' => 'phpredis'
],


一个基于PhpRedis的Lumen扩展包
[TargetLiu/PHPRedis]

https://github.com/TargetLiu/PHPRedis
https://packagist.org/packages/targetliu/phpredis

安装：composer require targetliu/phpredis

//，引入了 PhpRedis 并做了最简单的缓存驱动。目前支持根据 .env 获取Redis配置、Cache的基本读写等。

Session和Queue可以继续使用Lumen自带的Redis驱动，两者互不影响。下一步如有需要可以继续完善这两部分的驱动