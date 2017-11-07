<?php

Laravel 的事件广播系统#
简介#


配置#
所有关于事件广播的配置都被保存在 config/broadcasting.php 文件中
 Laravel 自带了几个广播驱动器：Pusher, Redis, 和一个用于本地开发与调试的 log 驱动器
 另外，还有一个 null 驱动器可以让你完全关闭广播功能

广播服务提供者#
//在对事件进行广播之前，你必须先注册 App\Providers\BroadcastServiceProvider
//对于一个全新安装的 Laravel 应用程序，你只需在 config/app.php 配置文件的 providers 数组中取消对该提供者的注释即可。该提供者将允许你注册广播授权路由和回调。

CSRF 令牌#
// Laravel Echo 会需要访问当前会话的 CSRF 令牌
//如果可用，Echo 会从 Laravel.csrfToken JavaScript 对象中获取该令牌。如果你运行了 make:auth Artisan 命令，该对象会在 resources/views/layouts/app.blade.php 布局文件中被定义。如果你未使用该布局文件，可以在应用程序的 head HTML 元素中定义一个 meta 标签：
<meta name="csrf-token" content="{{ csrf_token() }}">

对驱动器的要求#
Pusher#
composer require pusher/pusher-php-server "~3.0"

//然后，你需要在 config/broadcasting.php 配置文件中填写你的 Pusher 证书
//该文件中已经包含了一个 Pusher 示例配置，你只需指定 Pusher key、secret 和 application ID 即可
// config/broadcasting.php 中的 pusher 配置项同时也允许你指定 Pusher 支持的 options ，例如 cluster：
'options' => [
    'cluster' => 'eu',
    'encrypted' => true
],

// 当把 Pusher 和 Laravel Echo 一起使用时，你应该在 resources/assets/js/bootstrap.js 文件中实例化 Echo 对象时指定 pusher 作为所需要的 broadcaster :
import Echo from "laravel-echo"

window.Pusher = require('pusher-js');

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key'
});

Redis#
composer require predis/predis

Socket.IO#
<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>

//接着，你需要在实例化 Echo 时指定 socket.io 连接器和 host。
import Echo from "laravel-echo"

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ':6001'
});

对队列的要求#
//在开始广播事件之前，你还需要配置和运行 队列侦听器 。所有的事件广播都是通过队列任务来完成的，因此应用程序的响应时间不会受到明显影响。


概念综述#
Laravel 的事件广播允许你使用基于驱动的 WebSockets 将服务端的 Larevel 事件广播到客户端的 JavaScript 应用程序

使用示例程序#
// 在我们的应用程序中，让我们假设有一个允许用户查看订单配送状态的页面。有一个 ShippingStatusUpdated 事件会在配送状态更新时被触发：
event(new ShippingStatusUpdated($update));

ShouldBroadcast 接口#
//当用户在查看自己的订单时，我们不希望他们必须通过刷新页面才能看到状态更新
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ShippingStatusUpdated implements ShouldBroadcast
{
    /**
     * Information about the shipping status update.
     *
     * @var string
     */
    public $update;
}

//ShouldBroadcast 接口要求事件实现 broadcastOn 方法。该方法负责指定事件被广播到哪些频道
/**
 * Get the channels the event should broadcast on.
 *
 * @return array
 */
public function broadcastOn()
{
    return new PrivateChannel('order.'.$this->update->order_id);
}

频道授权#
// 记住，用户只有在被授权后才能监听私有频道。我们可以在 routes/channels.php 文件中定义频道的授权规则
在本例中，我们需要对试图监听私有 order.1 频道的所有用户进行验证，确保只有订单的创建者才能进行监听：

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});

channel 方法接收两个参数：频道名称和一个回调函数，该回调通过返回 true 或者 false 来表示用户是否被授权监听该频道。
//所有的授权回调接收当前被认证的用户作为第一个参数，任何额外的通配符参数作为后续参数

对事件广播进行监听#
//首先，使用 private 方法来订阅私有频道。然后，使用 listen 方法来监听 ShippingStatusUpdated 事件。默认情况下，事件的所有公有属性会被包括在广播事件中：
Echo.private(`order.${orderId}`)
    .listen('ShippingStatusUpdated', (e) => {
        console.log(e.update);
    });


定义广播事件#
//要告知 Laravel 一个给定的事件是广播类型，只需在事件类中实现 Illuminate\Contracts\Broadcasting\ShouldBroadcast 接口即可

//ShouldBroadcast 接口要求你实现一个方法：broadcastOn. broadcastOn 方法返回一个频道或一个频道数组，事件会被广播到这些频道

//频道必须是 Channel、PrivateChannel 或 PresenceChannel 的实例。
//Channel 实例表示任何用户都可以订阅的公开频道，而
//PrivateChannels 和 PresenceChannels 则表示需要 频道授权 的私有频道：
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ServerCreated implements ShouldBroadcast
{
    use SerializesModels;

    public $user;

    /**
     * 创建一个新的事件实例
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * 指定事件在哪些频道上进行广播
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.'.$this->user->id);
    }
}

//然后，你只需要像你平时那样 触发事件 。一旦事件被触发，一个 队列任务 会自动广播事件到你指定的广播驱动器上


广播名称#
//Laravel 默认会使用事件的类名作为广播名称来广播事件
/**
 * 事件的广播名称。
 *
 * @return string
 */
public function broadcastAs()
{
    return 'server.created';
}

//如果您使用 broadcastAs 方法自定义广播名称，你需要在你使用订阅事件的时候为事件类加上 . 前缀。这将指示 Echo 不要将应用程序的命名空间添加到事件中：
.listen('.server.created', function (e) {
    ....
});

广播数据#
//当一个事件被广播时，它所有的 public 属性会自动被序列化为广播数据，这允许你在你的 JavaScript 应用中访问事件的公有数据
//因此，举个例子，如果你的事件有一个公有的 $user 属性，它包含了一个 Elouqent 模型，那么事件的广播数据会是：
{
    "user": {
        "id": 1,
        "name": "Patrick Stewart"
        ...
    }
}

//然而，如果你想更细粒度地控制你的广播数据，你可以添加一个 broadcastWith 方法到你的事件中
/**
 * 指定广播数据
 *
 * @return array
 */
public function broadcastWith()
{
    return ['id' => $this->user->id];
}




广播队列#
//你可以通过在事件类中定义一个 broadcastQueue 属性来自定义广播器使用的队列。该属性用于指定广播使用的队列名称：
/**
 * 指定事件被放置在哪个队列上
 *
 * @var string
 */
public $broadcastQueue = 'your-queue-name';

//如果要使用 sync 队列而不是默认队列驱动程序广播你的事件，你可以实现 ShouldBroadcastNow 接口而不是 ShouldBroadcast:
<?php

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ShippingStatusUpdated implements ShouldBroadcastNow
{
    //
}

广播条件#
//有时，你想在给定条件为 true ，才广播你的事件。你可以通过在事件类中添加一个 broadcastWhen 方法来定义这些条件：
/**
 * Determine if this event should broadcast.
 *
 * @return bool
 */
public function broadcastWhen()
{
    return $this->value > 100;
}

频道授权#

定义授权路由#
//在 BroadcastServiceProvider 中，你会看到一个对 Broadcast::routes 方法的调用。该方法会注册 /broadcasting/auth 路由来处理授权请求：

Broadcast::routes();

//Broadcast::routes 方法会自动把它的路由放进 web 中间件组中；另外，如果你想对一些属性自定义，可以向该方法传递一个包含路由属性的数组：
Broadcast::routes($attributes);

定义授权回调#
//接下来，我们需要定义真正用于处理频道授权的逻辑。这是在 routes/channels.php 文件中完成

//在该文件中，你可以用 Broadcast::channel 方法来注册频道授权回调函数：

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return $user->id === Order::findOrNew($orderId)->user_id;
});
channel 方法接收两个参数：频道名称和一个回调函数，该回调通过返回 true 或 false 来表示用户是否被授权监听该频道。

//所有的授权回调接收当前被认证的用户作为第一个参数，任何额外的通配符参数作为后续参数

授权回调模型绑定#
//就像 HTTP 路由一样，频道路由也可以利用显式或隐式 路由模型绑定
//例如，相比于接收一个字符串或数字类型的 order ID，你也可以请求一个真正的 Order 模型实例:
use App\Order;

Broadcast::channel('order.{order}', function ($user, Order $order) {
    return $user->id === $order->user_id;
});

对事件进行广播#
//一旦你已经定义好了一个事件并实现了 ShouldBroadcast 接口，剩下的就是使用 event 函数来触发该事件
event(new ShippingStatusUpdated($update));



只广播给他人#
//当创建一个使用到事件广播的应用程序时，你可以用 broadcast 函数来替代 event 函数
和 event 函数一样，broadcast 函数将事件分发到服务端侦听器：
broadcast(new ShippingStatusUpdated($update));

//不同的是 broadcast 函数有一个 toOthers 方法允许你将当前用户从广播接收者中排除：
broadcast(new ShippingStatusUpdated($update))->toOthers();

axios.post('/task', task)
    .then((response) => {
        this.tasks.push(response.data);
    });

接受广播#
安装 Laravel Echo#
//Laravel Echo 是一个 JavaScript 库，它使得订阅频道和监听由 Laravel 广播的事件变得非常容易
npm install --save laravel-echo pusher-js

//做这件事的一个理想地方是在 resources/assets/js/bootstrap.js 文件的底部，Laravel 框架自带了该文件：
import Echo from "laravel-echo"

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key'
});

//当你使用 pusher 连接器来创建一个 Echo 实例的时候，你需要指定 cluster 以及指定连接是否需要加密：
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key',
    cluster: 'eu',
    encrypted: true
});


对事件进行监听#
Echo.channel('orders')
    .listen('OrderShipped', (e) => {
        console.log(e.order.name);
    });

//如果你想监听私有频道上的事件，请使用 private 方法。你可以通过链式调用 listen 方法来监听一个频道上的多个事件：
Echo.private('orders')
    .listen(...)
    .listen(...)
    .listen(...);

退出频道#
Echo.leave('orders');

命名空间#
//你可能注意到了在上面的例子中我们没有为事件类指定完整的命名空间。这是因为 Echo 会自动认为事件在 App\Events 命名空间下。你可以在实例化 Echo 的时候传递一个 namespace 配置选项来指定根命名空间：
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key',
    namespace: 'App.Other.Namespace'
});

//另外，你也可以在使用 Echo 订阅事件的时候为事件类加上 . 前缀。这时需要填写完全限定名称的类名：
Echo.channel('orders')
    .listen('.Namespace.Event.Class', (e) => {
        //
    });

Presence 频道#
//Presence 频道是在私有频道的安全性基础上，额外暴露出有哪些人订阅了该频道。这使得它可以很容易地建立强大的、协同的应用，如当有一个用户在浏览页面时，通知其他正在浏览相同页面的用户


授权 Presence 频道#
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    if ($user->canJoinRoom($roomId)) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});

加入 Presence 频道#
Echo.join(`chat.${roomId}`)
    .here((users) => {
        //
    })
    .joining((user) => {
        console.log(user.name);
    })
    .leaving((user) => {
        console.log(user.name);
    });

//here 回调函数会在你成功加入频道后被立即执行，它接收一个包含用户信息的数组，用来告知当前订阅在该频道上的其他用户。
//joining 方法会在其他新用户加入到频道时被执行，
//leaving 会在其他用户退出频道时被执行。


广播到 Presence 频道#
/**
 * 指定事件在哪些频道上进行广播
 *
 * @return Channel|array
 */
public function broadcastOn()
{
    return new PresenceChannel('room.'.$this->message->room_id);
}

//和公开或私有事件一样，presence 频道事件也能使用 broadcast 函数来广播。同样的，你还能用 toOthers 方法将当前用户从广播接收者中排除：
broadcast(new NewMessage($message));

broadcast(new NewMessage($message))->toOthers();

//你可以通过 Echo 的 listen 方法来监听 join 事件：
Echo.join(`chat.${roomId}`)
    .here(...)
    .joining(...)
    .leaving(...)
    .listen('NewMessage', (e) => {
        //
    });

客户端事件#
Echo.channel('chat')
    .whisper('typing', {
        name: this.user.name
    });

//你可以使用 listenForWhisper 方法来监听客户端事件：
Echo.channel('chat')
    .listenForWhisper('typing', (e) => {
        console.log(e.name);
    });

消息通知#
//一旦你将一个消息通知配置为使用广播频道，你需要使用 Echo 的 notification 方法来监听广播事件。谨记，频道名称应该和接收消息通知的实体类名相匹配：
Echo.private(`App.User.${userId}`)
    .notification((notification) => {
        console.log(notification.type);
    });
// 在本例中，所有通过 broadcast 频道发送到 App\User 实例的消息通知都会被该回调接收到。一个针对 App.User.{id} 频道的授权回调函数已经被包含在 Laravel 的 BroadcastServiceProvider 中了。
    
