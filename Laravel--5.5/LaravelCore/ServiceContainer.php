<?php

Laravel 服务容器解析#
简介#
//Laravel 服务容器是用于管理类的依赖和执行依赖注入的工具
依赖注入这个花俏名词实质上是指：类的依赖项通过构造函数，或者某些情况下通过「setter」方法「注入」到类中。
<?php

namespace App\Http\Controllers;

use App\User;
use App\Repositories\UserRepository;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 用户存储库的实现。
     *
     * @var UserRepository
     */
    protected $users;

    /**
     * 创建新的控制器实例。
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * 显示指定用户的 profile。
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $user = $this->users->find($id);

        return view('user.profile', ['user' => $user]);
    }
}

绑定#
绑定基础#
因为几乎所有服务容器都是在 服务提供器 中注册绑定的，所以文档中大多数例子都是使用了在服务提供器中绑定的容器。

简单绑定#
//在服务提供器中，你可以通过 $this->app 属性访问容器
//我们可以通过 bind 方法注册绑定，传递我们想要注册的类或接口名称再返回类的实例的 Closure ：
$this->app->bind('HelpSpot\API', function ($app) {
    return new HelpSpot\API($app->make('HttpClient'));
});

!!注意，我们接受容器本身作为解析器的参数。然后，我们可以使用容器来解析正在构建的对象的子依赖

绑定一个单例#
//singleton 方法将类或接口绑定到只能解析一次的容器中
$this->app->singleton('HelpSpot\API', function ($app) {
    return new HelpSpot\API($app->make('HttpClient'));
});

绑定实例#
//给定的实例会始终在随后的调用中返回到容器中：
$api = new HelpSpot\API(new HttpClient);

$this->app->instance('HelpSpot\API', $api);

绑定初始数据#
//当你有一个类不仅需要接受一个注入类，还需要注入一个基本值（比如整数）
//你可以使用上下文绑定来轻松注入你的类需要的任何值：
$this->app->when('App\Http\Controllers\UserController')
          ->needs('$variableName')
          ->give($value);

绑定接口到实现#
//服务容器有一个强大的功能，就是将接口绑定到给定实现
//例如，如果我们有一个 EventPusher 接口和一个 RedisEventPusher 实现。编写完接口的 RedisEventPusher 实现后，我们就可以在服务容器中注册它，像这样：
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\VideoController;
use Illuminate\Contracts\Filesystem\Filesystem;

$this->app->when(PhotoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('local');
          });

$this->app->when(VideoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('s3');
          });



标记#
//有时候，你可能需要解析某个「分类」下的所有绑定
$this->app->bind('SpeedReport', function () {
    //
});

$this->app->bind('MemoryReport', function () {
    //
});

$this->app->tag(['SpeedReport', 'MemoryReport'], 'reports');
//服务被标记后，你可以通过 tagged 方法轻松地将它们全部解析：
$this->app->bind('ReportAggregator', function ($app) {
    return new ReportAggregator($app->tagged('reports'));
});

解析#
Make 方法#
//使用 make 方法将容器中的类实例解析出来。make 方法接受要解析的类或接口的名称：
$api = $this->app->make('HelpSpot\API');

//如果你的代码处于不能访问 $app 变量的位置，你可以使用全局的辅助函数 resolve
$api = resolve('HelpSpot\API');

//如果你的某些类的依赖项不能通过容器去解析，那你可以通过将它们作为关联数组传递到 makeWith 方法来注入它们。
$api = $this->app->makeWith('HelpSpot\API', ['id' => 1]);

自动注入#
<?php

namespace App\Http\Controllers;

use App\Users\Repository as UserRepository;

class UserController extends Controller
{
    /**
     * 用户存储库实例。
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
     * 显示指定 ID 的用户信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }
}

容器事件#
//每当服务容器解析一个对象时触发一个事件。你可以使用 resolving 方法监听这个事件：
$this->app->resolving(function ($object, $app) {
    // 当容器解析任何类型的对象时调用...
});

$this->app->resolving(HelpSpot\API::class, function ($api, $app) {
    // 当容器解析类型为「HelpSpot\API」的对象时调用...
});

PSR-11#
//Laravel 的服务容器实现了 PSR-11 接口。因此，你可以对 PSR-11容器接口类型提示来获取 Laravel 容器的实例：
use Psr\Container\ContainerInterface;

Route::get('/', function (ContainerInterface $container) {
    $service = $container->get('Service');

    //
});
