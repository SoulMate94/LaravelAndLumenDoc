<?php

#服务容器#
#简介
//Laravel 服务容器是管理类依赖与运行依赖注入的强力工具
//依赖注入是个花俏的名词，事实上是指：类的依赖通过构造器或在某些情况下通过「setter」方法「注入」。
<?php

namespace App\Jobs;

use App\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Bus\SelfHandling;

class PurchasePodcast implements SelfHandling
{
    /**
     * 邮件寄送器的实现。
     */
    protected $mailer;

    /**
     * 创建一个新实例。
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * 购买一个播客
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}



#绑定
//在服务提供者中，你总是可以通过 $this->app 实例变量访问容器。我们可以使用 bind 方法注册一个绑定，传递我们希望注册的类或接口名称，并连同返回该类实例的闭包：
$this->app->bind('HelpSpot\API', function ($app) {
    return new HelpSpot\API($app['HttpClient']);
});

//注意，我们获取到的容器本身作为参数传递给解析器，这样就可以使用容器来解析绑定对象的 次要依赖

//绑定一个单例
//singleton 方法绑定一个只会被解析一次的类或接口至容器中，且后面的调用都会从容器中返回相同的实例：
$this->app->singleton('FooBar', function ($app) {
    return new FooBar($app['SomethingElse']);
});

//绑定实例
//你也可以使用 instance 方法，绑定一个已经存在的对象实例至容器中。后面的调用都会从容器中返回指定的实例：
$fooBar = new FooBar(new SomethingElse);

$this->app->instance('FooBar', $fooBar);

#绑定接口至实现
//服务容器有个非常强大的特色功能，就是能够将指定的实现绑定至接口
//举个例子，让我们假设我们有个 EventPusher 接口及一个 RedisEventPusher 实现。一旦我们编写完该接口的 RedisEventPusher 实现，就可以将它注册至服务容器：
$this->app->bind('App\Contracts\EventPusher', 'App\Services\RedisEventPusher');

//这么做会告知容器当有个类需要 EventPusher 的实现时，必须注入 RedisEventPusher。现在我们可以在构造器中对 EventPusher 接口使用类型提示，或任何需要通过服务容器注入依赖的其它位置：
use App\Contracts\EventPusher;

/**
 * 创建一个新的类实例。
 *
 * @param  EventPusher  $pusher
 * @return void
 */
public function __construct(EventPusher $pusher)
{
    $this->pusher = $pusher;
}

#情境绑定
//有时候，你可能有两个类使用到相同接口，但你希望每个类都能注入不同实现
例如，当系统收到新订单时，我们可能想通过 PubNub 来发送事件，而不是 Pusher。Laravel 提供一个简单流畅的接口来定义此行为：
$this->app->when('App\Handlers\Commands\CreateOrderHandler')
          ->needs('App\Contracts\EventPusher')
          ->give('App\Services\PubNubEventPusher');

//你甚至可以传递一个闭包至 give 方法：
$this->app->when('App\Handlers\Commands\CreateOrderHandler')
          ->needs('App\Contracts\EventPusher')
          ->give(function () {
                  // Resolve dependency...
              });          

#标记
//有些时候，可能需要解析某个「分类」下的所有绑定
//例如，你正在构建一个能接收多个不同 Report 接口实现数组的报表汇整器
//注册完 Report 实现后，可以使用 tag 方法为它们赋予一个标签
$this->app->bind('SpeedReport', function () {
    //
});

$this->app->bind('MemoryReport', function () {
    //
});

$this->app->tag(['SpeedReport', 'MemoryReport'], 'reports');

//一旦服务被标记之后，你可以通过 tagged 方法将它们全部解析：
$this->app->bind('ReportAggregator', function ($app) {
    return new ReportAggregator($app->tagged('reports'));
});          

#解析
//有几种方式可以从容器中解析一些东西。首先，你可以使用 make 方法，它会接收你希望解析的类或是接口的名称：
$fooBar = $this->app->make('FooBar');
//或者，你可以像数组一样从容器中进行访问，因为他实现了 PHP 的 ArrayAccess 接口：
$fooBar = $this->app['FooBar'];

//最后，也是最重要的，你可以在类的构造器简单地对依赖使用「类型提示」，类将会从容器中进行解析，包含 控制器、事件侦听器、队列任务、中间件 等等

//容器会自动为类注入解析出的依赖。举个例子，你可以在控制器的构造器中对应用程序定义的 Repository 进行类型提示。Repository 会自动被解析及注入至类中
<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Users\Repository as UserRepository;

class UserController extends Controller
{
    /**
     * 用户 Repository 的实例。
     */
    protected $users;

    /**
     * 创建一个新的控制器实例。
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * 显示指定 ID 的用户。
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }
}

#容器事件
//每当服务容器解析一个对象时就会触发事件。你可以使用 resolving 方法监听这个事件：
$this->app->resolving(function ($object, $app) {
    // 当容器解析任何类型的对象时会被调用...
});

$this->app->resolving(FooBar::class, function (FooBar $fooBar, $app) {
    // 当容器解析「FooBar」类型的对象时会被调用...
});
