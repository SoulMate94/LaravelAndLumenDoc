<?php

#事件#
#简介
//Laravel 事件提供了简单的侦听器实现，允许你订阅和监听事件
事件类通常被保存在 app/Events 目录下，而它们的侦听器被保存在 app/Listeners 目录下。

#注册事件或侦听器
//你可以在 EventServiceProvider 注册所有的事件侦听器
//listen 属性是一个数组，包含所有事件（键）以及事件对应的侦听器（值），你也可以根据需求增加事件到这个数组，例如：让我们增加 PodcastWasPurchased 事件
/**
 * 应用程序的事件侦听器映射。
 *
 * @var array
 */
protected $listen = [
    'App\Events\PodcastWasPurchased' => [
        'App\Listeners\EmailPurchaseConfirmation',
    ],
];

生成事件或侦听器类#
//你可以使用 event:generate 来协作你处理此类操作，这个命令会自动生成所有列出在 EventServiceProvider 的事件文件和侦听器文件，已经存在的事件和侦听器将保持不变：
php artisan event:generate

手动注册事件#
//一般来说，事件必须通过 EventServiceProvider 的 $listen 数组进行注册
//不过，你也可以通过 Event facade 或是 Illuminate\Contracts\Events\Dispatcher contract 实现的事件发送器来手动注册事件：
/**
 * 注册你应用程序中的任何其它事件。
 *
 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
 * @return void
 */
public function boot(DispatcherContract $events)
{
    parent::boot($events);

    $events->listen('event.name', function ($foo, $bar) {
        //
    });
}

事件侦听器的通配符#
//你也可以使用 * 通配符注册侦听器，让你可以在同个侦听器拦截多个事件
$events->listen('event.*', function (array $data) {
    //
});

#定义事件#
//一个事件类只是一个包含了相关事件信息的数据容器
//例如，假设我们生成了 PodcastWasPurchased 事件来接收一个 Eloquent ORM 对象：
<?php

namespace App\Events;

use App\Podcast;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;

class PodcastWasPurchased extends Event
{
    use SerializesModels;

    public $podcast;

    /**
     * 创建一个新的事件实例。
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct(Podcast $podcast)
    {
        $this->podcast = $podcast;
    }
}
正如你所见的，这个事件类没有包含其它特殊逻辑。它只是一个被购买的 Podcast 对象的容器
//如果事件对象是使用 PHP 的 serialized 函数进行序列化，那么事件所使用的 SerializesModels trait 将会优雅的序列化任何的 Eloquent 模型。



#定义侦听器
//事件侦听器的 handle 方法接收了事件实例。event:generate 命令将会在事件的 handle 方法自动加载正确的事件类和类型提示
//在 handle 方法内，你可以运行任何必要响应该事件的逻辑。
<?php

namespace App\Listeners;

use App\Events\PodcastWasPurchased;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailPurchaseConfirmation
{
    /**
     * 创建事件侦听器。
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 处理事件。
     *
     * @param  PodcastWasPurchased  $event
     * @return void
     */
    public function handle(PodcastWasPurchased $event)
    {
        // 使用 $event->podcast 访问播客（podcast）...
    }
}

//你的事件侦听器也可以在构造器内对任何依赖使用类型提示。所有事件侦听器经由 Laravel 服务容器做解析，所以依赖将会自动的被注入：
use Illuminate\Contracts\Mail\Mailer;

public function __construct(Mailer $mailer)
{
    $this->mailer = $mailer;
}
//停止一个事件的传播#
有时候，你可能希望停止一个事件传播到其它的侦听器。你可以通过在侦听器的 handle 方法中返回 false 来实现


#可队列的事件侦听器
//只要增加 ShouldQueue 接口到你的侦听器类。由 event:generate Artisan 命令生成的侦听器已经将此接口导入到命名空间了，因此可以像这样来立即使用它：
<?php

namespace App\Listeners;

use App\Events\PodcastWasPurchased;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailPurchaseConfirmation implements ShouldQueue
{
    //
}
//仅此而已！现在，当这个侦听器调用事件时，事件发送器会使用 Laravel 的 队列系统 自动进行队列处理
//如果侦听器是通过队列运行而没有抛出任何异常，则已处理过的队列任务将会被自动删除。


手动访问队列#
//如果你需要手动访问底层队列任务的 delete 和 release 方法，那是可以做到的
//默认生成的侦听器会加载 Illuminate\Queue\InteractsWithQueue trait，让你可以访问这些方法：
<?php

namespace App\Listeners;

use App\Events\PodcastWasPurchased;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailPurchaseConfirmation implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PodcastWasPurchased $event)
    {
        if (true) {
            $this->release(30);
        }
    }
}

#触发事件
//如果要触发一个事件，你可以使用 Event facade 来发送一个事件的实例到 fire 方法
//fire 方法将会发送事件到所有已经注册的侦听器上：
<?php

namespace App\Http\Controllers;

use Event;
use App\Podcast;
use App\Events\PodcastWasPurchased;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 显示指定用户的基本数据
     *
     * @param  int  $userId
     * @param  int  $podcastId
     * @return Response
     */
    public function purchasePodcast($userId, $podcastId)
    {
        $podcast = Podcast::findOrFail($podcastId);

        // 购买播客（podcast）逻辑...

        Event::fire(new PodcastWasPurchased($podcast));
    }
}
//另外，你也可以使用全局 event 辅助函数来触发事件：
event(new PodcastWasPurchased($podcast));

#广播事件
//所有的事件广播设置选项都保存在 config/broadcasting.php 配置文件内
//Laravel 内置支持多种广播驱动：Pusher、Redis，和一个用于本机开发和调试的 log 驱动程序。配置文件例子包含了所有的驱动程序。
#设置
广播先决条件#
//事件广播需要以下的依赖：
Pusher: pusher/pusher-php-server ~2.0
Redis: predis/predis ~1.0

队列先决条件#
在广播事件之前，你还需要设置和运行队列侦听器
所有事件广播经由队列任务完成，因此对应用程序的响应时间不会有严重影响。

#将事件标示为广播
为了通知 Laravel 应该广播一个特定事件，在你的事件类实现 Illuminate\Contracts\Broadcasting\ShouldBroadcast
//ShouldBroadcast 要求你实现单个方法：broadcastOn。broadcastOn 方法应该返回一个必须被广播的「频道」名称数组：
<?php

namespace App\Events;

use App\User;
use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ServerCreated extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $user;

    /**
     * 创建一个新的事件实例。
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * 获取事件应该被广播的频道。
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['user.'.$this->user->id];
    }
}

//接着，你只需要像往常一样 触发事件。一旦事件被触发之后，队列任务 将会自动的广播事件到你指定的广播驱动上。

重写广播事件名称#
//默认情况下，广播事件名称会使用完整的事件类名称。
//以下方的类为例子，该广播事件会是 App\Events\ServerCreated。你可以使用 broadcastAs 方法来自定你想要的广播事件名称：
/**
 * 获取广播事件名称。
 *
 * @return string
 */
public function broadcastAs()
{
    return 'app.server-created';
}

#广播数据
//当事件被广播时，所有的 public 属性都会被自动序列化且将广播作为事件的有效负载，允许你从 JavaScript 应用程序中访问任何公开的数据
//所以，在这个例子中，假设事件有一个单个公开的 $user 属性且包含了一个 Eloquent 模型，广播数据将会是：
{
    "user": {
        "id": 1,
        "name": "Jonathan Banks"
        ...
    }
}

//然而，如果你希望在广播数据中有更精确的控制，则可以增加 broadcastWith 方法到事件上
//这个方法应该返回一个你希望广播的事件数据数组：
/**
 * 获取广播数据。
 *
 * @return array
 */
public function broadcastWith()
{
    return ['user' => $this->user->id];
}

#消耗事件广播
Pusher#
//通过 Pusher 驱动，你可以使用 Pusher 的 JavaScript SDK 方便的消耗事件广播
//例如，让我们从先前的例子消耗 App\Events\ServerCreated 事件：
this.pusher = new Pusher('pusher-key');

this.pusherChannel = this.pusher.subscribe('user.' + USER_ID);

this.pusherChannel.bind('App\\Events\\ServerCreated', function(message) {
    console.log(message.user);
});


Redis#
//如果你使用了 Redis 广播器，则需要编写自己的 Redis pub/sub 消耗器来接收消息和广播，并使用你所选择的 WebSocket 技术
//例如，你可能选择使用 Node 编写、很受欢迎的 Socket.io 函数库
使用 socket.io 和 ioredis Node 函数库可以快速的编写一个事件广播器，在 Laravel 应用程序发布所有事件的广播：
var app = require('http').createServer(handler);
var io = require('socket.io')(app);

var Redis = require('ioredis');
var redis = new Redis();

app.listen(6001, function() {
    console.log('Server is running!');
});

function handler(req, res) {
    res.writeHead(200);
    res.end('');
}

io.on('connection', function(socket) {
    //
});

redis.psubscribe('*', function(err, count) {
    //
});

redis.on('pmessage', function(subscribed, channel, message) {
    message = JSON.parse(message);
    io.emit(channel + ':' + message.event, message.data);
});

#事件订阅器
//事件订阅器是一个让你可以订阅多个事件的类，允许你在单个类内定义多个事件的操作
//订阅器应该定义一个可以发送一个事件发送器实例的 subscribe 方法，：
<?php

namespace App\Listeners;

class UserEventListener
{
    /**
     * 处理用户登录事件。
     */
    public function onUserLogin($event) {}

    /**
     * 处理用户注销事件。
     */
    public function onUserLogout($event) {}

    /**
     * 注册侦听器的订阅者。
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\UserLoggedIn',
            'App\Listeners\UserEventListener@onUserLogin'
        );

        $events->listen(
            'App\Events\UserLoggedOut',
            'App\Listeners\UserEventListener@onUserLogout'
        );
    }

}

注册事件订阅器#
//一旦订阅器被定义，它就可以被注册到事件发送器
//你可以在 EventServiceProvider 中使用 $subscribe 属性注册订阅器
例如，让我们增加 UserEventListener
?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件侦听器映射到应用程序。
     *
     * @var array
     */
    protected $listen = [
        //
    ];

    /**
     * 订阅者类进行注册。
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\UserEventListener',
    ];
}

#框架事件

