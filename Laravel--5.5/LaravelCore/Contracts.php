<?php

契约 (Contracts)#
简介#
//Laravel 的契约是一组定义框架提供的核心服务的接口。
//例如，Illuminate\Contracts\Queue\Queue 契约定义了队列任务所需的方法，而 Illuminate\Contracts\Mail\Mailer 契约定义了发送电子邮件所需的方法。

契约 VS 门面#


何时使用契约#
使用契约或是 Facades 很大程度上归结于个人或者开发团队的喜好。不管是契约还是 Facades 都可以创建出健壮的、易测试的 Laravel 应用程序
如果你长期关注类的单一职责，你会注意到使用契约还是 Facades 其实没多少实际意义上的区别。


低耦合#
//首先，让我们来看一些高耦合缓存实现的代码。如下
<?php

namespace App\Orders;

class Repository
{
    /**
     * 缓存实例。
     */
    protected $cache;

    /**
     * 创建一个仓库实例。
     *
     * @param  \SomePackage\Cache\Memcached  $cache
     * @return void
     */
    public function __construct(\SomePackage\Cache\Memcached $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 按照 Id 检索订单
     *
     * @param  int  $id
     * @return Order
     */
    public function find($id)
    {
        if ($this->cache->has($id))    {
            //
        }
    }
}
//在这个类中，程序跟给定的缓存实现高耦合。因为我们依赖于一个扩展包的特定缓存类。一旦这个扩展包的 API 被更改了，我们的代码就必须跟着改变。

//同样的，如果我们想要将底层的的缓存技术（ Memcached ）替换为另一种缓存技术（ Redis ），那又得再次修改这个 repository 类。而 repository 类不应该了解太多关于谁提供了这些数据或是如何提供的等等。
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
     * 创建一个仓库实例。
     *
     * @param  Cache  $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }
}
//更改之后的代码没有与任何扩展包甚至是 Laravel 耦合。而契约扩展包不包含任何实现和依赖项，你可以轻松地写任何给定契约的替代实现，来实现不修改任何关于缓存消耗的代码就可以替换缓存实现


简单性#
//当所有 Laravel 的服务都使用简洁的接口定义，就很容易判断给定服务提供的功能。 可以将契约视为说明框架功能的简洁文档。


如何使用 Contracts#
//Laravel 中的许多类型的类都是通过 服务容器 解析出来的，包括控制器、事件监听器、中间件、任务队列，甚至路由闭包

//所以说，要获得一个契约的实现，你只需要被解析的类的构造函数中添加「类型提示」即可
<?php

namespace App\Listeners;

use App\User;
use App\Events\OrderWasPlaced;
use Illuminate\Contracts\Redis\Database;

class CacheOrderInformation
{
    /**
     * Redis 数据库实现。
     */
    protected $redis;

    /**
     * 创建事件处理器实例。
     *
     * @param  Database  $redis
     * @return void
     */
    public function __construct(Database $redis)
    {
        $this->redis = $redis;
    }

    /**
     * 处理事件。
     *
     * @param  OrderWasPlaced  $event
     * @return void
     */
    public function handle(OrderWasPlaced $event)
    {
        //
    }
}

当事件监听器被解析时，服务容器会读取类的构造函数上的类型提示，并注入对应的值

Contract 参考#

