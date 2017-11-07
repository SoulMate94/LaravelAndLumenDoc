<?php

视图#
创建视图#
视图包含应用程序的 HTML，并且将控制器／应用程序逻辑与演示逻辑分开。视图文件存放于 resources/views 目录下。一个简单的视图如下所示：
<!-- 此视图文件位置：resources/views/greeting.blade.php -->

<html>
    <body>
        <h1>Hello, {{ $name }}</h1>
    </body>
</html>

该视图文件位于 resources/views/greeting.blade.php，使用全局辅助函数 view 来返回
Route::get('/', function () {
    return view('greeting', ['name' => 'James']);
});

return view('admin.profile', $data);


判断视图文件是否存在#
如果需要判断视图文件是否存在，可以使用 View Facade 上的 exists 方法。如果视图文件存在，该方法会返回 true ：
use Illuminate\Support\Facades\View;

if (View::exists('emails.customer')) {
    //
}



向视图传递数据#
如上述例子所示，你可以使用数组将数据传递到视图：
return view('greetings', ['name' => 'Victoria']);
当用这种方式传递数据时，作为第二个参数的数据必须是键值对数组

在视图文件中，你可以通过对应的键获取相应的值，
例如 <?php echo $key; 。作为将完整数据传递给辅助函数 view 的替代方法，你可以使用 with 方法将单个数据片段添加到视图：
return view('greeting')->with('name', 'Victoria');



与所有视图共享数据#
如果需要共享一段数据给应用程序的所有视图，你可以在服务提供器的 boot 方法中调用视图 Facade 的 share 方法
<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 引导任何应用服务。
     *
     * @return void
     */
    public function boot()
    {
        View::share('key', 'value');
    }

    /**
     * 注册服务提供器
     *
     * @return void
     */
    public function register()
    {
        //
    }

视图合成器#
在下面这个例子中，我们会在一个 服务提供器 中注册视图合成器，使用 View Facade 来访问底层的 Illuminate\Contracts\View\Factory 契约实现。默认情况下，Laravel 没有存放视图合成器的目录，你需要根据喜好来重新建立目录，例如：App\Http\ViewComposers。

<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * 在容器中注册绑定
     *
     * @return void
     */
    public function boot()
    {
        // 使用基于类的 composer...
        View::composer(
            'profile', 'App\Http\ViewComposers\ProfileComposer'
        );

        // 使用基于闭包的 composers...
        View::composer('dashboard', function ($view) {
            //
        });
    }

    /**
     * 注册服务器提供者
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

到此我们已经注册了视图合成器，每次渲染 profile 视图时都会执行 ProfileComposer@compose 方法。那么下面我们来定义视图合成器的这个类吧：
<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\UserRepository;

class ProfileComposer
{
    /**
     * 用户 repository 实现
     *
     * @var UserRepository
     */
    protected $users;

    /**
     * 创建一个新的 profile composer
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(UserRepository $users)
    {
        // 依赖关系由服务容器自动解析...
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

视图合成器的 compose 方法会在视图渲染之前被调用，并传入一个 Illuminate\View\View 实例。你可以使用 with 方法将数据绑定到视图。


将视图构造器添加到多个视图#
通过将一组视图作为第一个参数传入 composer 方法，将一个视图合成器添加到多个视图
View::composer(
    ['profile', 'dashboard'],
    'App\Http\ViewComposers\MyViewComposer'
);


composer 方法同时也接受通配符 *，表示将一个视图合成器添加到所有视图：
View::composer('*', function ($view) {
    //
});

视图构造器#

视图构造器和视图合成器非常相似。唯一不同之处在于：视图构造器在视图实例化之后立即执行，而视图合成器在视图即将渲染时执行。使用 creator 方法注册视图构造器：
View::creator('profile', 'App\Http\ViewCreators\ProfileCreator');