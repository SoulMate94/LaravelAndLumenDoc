<?php

#视图#
#基本用法
//视图包含你应用程序所用到的 HTML，它能够将控制器和应用程序逻辑在呈现逻辑中进行分离。视图被存在 resources/views 目录下。
<!-- 视图被保存在 resources/views/greeting.php -->

<html>
    <body>
        <h1>Hello, <?php echo $name; ?></h1>
    </body>
</html>
<?php
//因为这个视图被保存在 resources/views/greeting.php，所以我们可以像这样使用全局的辅助函数 view 来返回：
Route::get('/', function ()    {
    return view('greeting', ['name' => 'James']);
});
//view 辅助函数的第一个参数会对应到 resources/views 目录内视图文件的名称；传递到 view 辅助函数的第二个参数是一个能够在视图内取用的数据数组
//当然，视图文件也可以被存放在 resources/views 的子目录内。. （小数点）的表示法可以被用来表示在子目录内的视图文
//如果你的视图文件保存在 resources/views/admin/profile.php，你可以用以下的代码来返回：
return view('admin.profile', $data);

//判断视图文件是否存在
//如果你需要判断视图文件是否存在，则可以在一个不传参的 view 辅助函数之后调用 exists 方法来进行判断。这个方法将会在视图文件存在时返回 true：
if (view()->exists('emails.customer')) {
    //
}
//当 view 辅助函数进行不传参调用时，将会返回一个 Illuminate\Contracts\View\Factory 的实例，以便你调用这个 Factory 的任意方法。


#传递数据到视图
//就像你在之前的例子看到的那样，你可以简单地传递一个数组的数据给视图：
return view('greetings', ['name' => 'Victoria']);
//当你用上面这种方式传递数据时，第二个参数必须是一个键值对的数组。在视图中，你可以用相对应的键名取用值，
$view = view('greeting')->with('name', 'Victoria');

//把数据共享给所有视图
//通常情况下，你会把这些调用 share 方法的代码放在一个服务提供者的 boot 方法内
<?php

namespace App\Providers;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 启动任何应用程序的服务。
     *
     * @return void
     */
    public function boot()
    {
        view()->share('key', 'value');
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



#视图组件
//视图组件就是在视图被渲染前，会被调用的闭包或类方法
//让我们在 服务提供者 内注册我们的视图组件。下面例子将使用 View 辅助函数来获取底层 Illuminate\Contracts\View\Factory contract 实现
//请注意，Laravel 没有默认的目录来放置视图组件。你可以随意把它们放到任何地方。举例来说，你可以创建一个 App\Http\ViewComposers 目录：
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * 在容器内注册所有绑定。
     *
     * @return void
     */
    public function boot()
    {
        // 使用对象型态的视图组件...
        view()->composer(
            'profile', 'App\Http\ViewComposers\ProfileComposer'
        );

        // 使用闭包型态的视图组件...
        view()->composer('dashboard', function ($view) {

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
//记住，如果你创建了包含注册视图组件的一个新的服务提供者，则需要把服务提供者加入在 config/app.php 配置文件内的 providers 数组中

//现在我们已经注册好了视图组件，在每次 profile 视图渲染的时候，ProfileComposer@compose 方法都将会被运行。接下来我们来看看这个视图组件类要如何定义：


#视图组件
<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Users\Repository as UserRepository;

class ProfileComposer
{
    /**
     * 用户对象的实例。
     *
     * @var UserRepository
     */
    protected $users;

    /**
     * 创建一个新的个人数据视图组件。
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        // 所有依赖都会自动地被服务容器解析...
        $this->users = $users;
    }

    /**
     * 将数据绑定到视图。
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('count', $this->users->count());
    }
}

//在视图被渲染之前，视图组件的 compose 方法会被调用，并传入一个 Illuminate\Contracts\View\View 实例。你可以使用 with 方法来把数据绑定到视图。


//在视图组件内使用通配符
//你可以在 composer 方法的第一个参数中传递一个视图数组，来同时对多个视图附加同一个视图组件：
view()->composer(
    ['profile', 'dashboard'],
    'App\Http\ViewComposers\MyViewComposer'
);

//视图的 composer 方法可以接受 * 作为通配符，所以你可以对所有视图附加 composer。如下：
view()->composer('*', function ($view) {
    //
});


//视图创建者
//视图 创建者 几乎和视图组件运作方式一样；只是视图创建者会在视图初始化后就立即运行，而不是像视图组件一样要一直等到视图即将被渲染完成时才会被运行。要注册一个创建者，只要使用 creator 方法即可
view()->creator('profile', 'App\Http\ViewCreators\ProfileCreator');