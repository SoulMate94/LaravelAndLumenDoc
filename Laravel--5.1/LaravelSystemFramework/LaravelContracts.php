<?php

#Contracts#
#简介
//Laravel 的 Contracts 是一组定义了框架核心服务的接口（ php class interfaces ）。例如，
Illuminate\Contracts\Queue\Queue contract 定义了队列任务所需要的方法，而 
Illuminate\Contracts\Mail\Mailer contract 定义了寄送 e-mail 需要的方法。

//Contracts Vs. Facades
Laravel 的 facades 提供一个简单的方法来使用服务，而不需要使用类型提示和在服务容器之外解析 contracts
使用 contracts 可以明显地定义出类的依赖，对大部分应用进程而言，使用 facade 就足够了，然而，若你实在需要特别的低耦合，使用 contracts 可以做到这一点



#为何要用 Contracts?
//?为什么要使用接口：低耦合和简单性。

#低耦合#
//首先，让我们来查看这一段和缓存功能有高耦合的代码，如下：
<?php

namespace App\Orders;

class Repository
{
    /**
     * 缓存实例。
     */
    protected $cache;

    /**
     * 创建一个新的仓库实例。
     *
     * @param  \SomePackage\Cache\Memcached  $cache
     * @return void
     */
    public function __construct(\SomePackage\Cache\Memcached $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 借由 ID 获取订单信息。
     *
     * @param  int  $id
     * @return Order
     */
    public function find($id)
    {
        if ($this->cache->has($id)) {
            //
        }
    }
}
//在此类中，程序和缓存实现之间是高耦合。因为它是依赖于扩展包的特定缓存类。一旦这个扩展包的 API 更改了，我们的代码也要跟着改变。
//同样的，如果想要将底层的缓存技术（比如 Memcached ）切换成另一种（像 Redis ），又一次的我们必须修改这个 Repository 类。我们的 Repository 类不应该知道这么多关于谁提供了数据，或是如何提供等细节。
<?php

namespace App\Orders;

use Illuminate\Contracts\Cache\Repository as Cache;

class Repository
{
    /**
     * 缓存实例。
     */
    protected $cache;

    /**
     * 创建一个新的仓库实例。
     *
     * @param  Cache  $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
}


#简单性#
//当所有的 Laravel 服务都使用简洁的接口定义，就能够很容易决定一个服务需要提供的功能。 可以将 contracts 视为说明框架特色的简洁文档。
//除此之外，当依赖的接口足够简洁时，代码的可读性和可维护性大大提高。比起搜索一个大型复杂的类里有哪些可用的方法，你有一个简单，干净的接口可以参考。
 


#Contract 参考

Contract		对应的 Facade
Illuminate\Contracts\Auth\Guard		Auth
Illuminate\Contracts\Auth\PasswordBroker	Password
Illuminate\Contracts\Bus\Dispatcher		Bus
Illuminate\Contracts\Broadcasting\Broadcaster	 
Illuminate\Contracts\Cache\Repository	Cache
Illuminate\Contracts\Cache\Factory	Cache::driver()
Illuminate\Contracts\Config\Repository	Config
Illuminate\Contracts\Container\Container	App
Illuminate\Contracts\Cookie\Factory	Cookie
Illuminate\Contracts\Cookie\QueueingFactory	Cookie::queue()
Illuminate\Contracts\Encryption\Encrypter	Crypt
Illuminate\Contracts\Events\Dispatcher	Event
Illuminate\Contracts\Filesystem\Cloud	 
Illuminate\Contracts\Filesystem\Factory	File
Illuminate\Contracts\Filesystem\Filesystem	File
Illuminate\Contracts\Foundation\Application	App
Illuminate\Contracts\Hashing\Hasher	Hash
Illuminate\Contracts\Logging\Log	Log
Illuminate\Contracts\Mail\MailQueue	Mail::queue()
Illuminate\Contracts\Mail\Mailer	Mail
Illuminate\Contracts\Queue\Factory	Queue::driver()
Illuminate\Contracts\Queue\Queue	Queue
Illuminate\Contracts\Redis\Database	Redis
Illuminate\Contracts\Routing\Registrar	Route
Illuminate\Contracts\Routing\ResponseFactory	Response
Illuminate\Contracts\Routing\UrlGenerator	URL
Illuminate\Contracts\Support\Arrayable	 
Illuminate\Contracts\Support\Jsonable	 
Illuminate\Contracts\Support\Renderable	 
Illuminate\Contracts\Validation\Factory	Validator::make()
Illuminate\Contracts\Validation\Validator	 
Illuminate\Contracts\View\Factory	View::make()
Illuminate\Contracts\View\View	 

#如何使用 Contracts
//所以，要实现一个 contract，你可以在类的构造器使用「类型提示」解析类。
//例如，我们来看看这个事件监听程序：
<?php

namespace App\Listeners;

use App\User;
use App\Events\NewUserRegistered;
use Illuminate\Contracts\Redis\Database;

class CacheUserInformation
{
    /**
     * 实现 Redis 数据库
     */
    protected $redis;

    /**
     * 创建一个新的事件处理对象
     *
     * @param  Database  $redis
     * @return void
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    /**
     * 处理事件
     *
     * @param  NewUserRegistered  $event
     * @return void
     */
    public function handle(NewUserRegistered $event)
    {
        //
    }
}
//当事件监听被解析时，服务容器会经由类构造器参数的类型提示，注入适当的值。要知道怎么注册更多服务容器，参考 这份文档.

