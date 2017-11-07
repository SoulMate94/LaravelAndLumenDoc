<?php

Laravel 的 Facades 介绍#
简介#
为应用程序的 服务容器 中可用的类提供了一个「静态」接口
//。Laravel Facades 实际上是服务容器中底层类的「静态代理」，它提供了简洁而富有表现力的语法，甚至比传统的静态方法更具可测试性和扩展性
所有的 Laravel Facades 都在 Illuminate\Support\Facades 命名空间中定义。所以，我们可以轻松地使用 Facade
use Illuminate\Support\Facades\Cache;

Route::get('/cache', function () {
    return Cache::get('key');
});

何时使用 Facades#
!!使用 Facades 最主要的风险就是会引起类作用范围的膨胀
//因为 Facades 使用起来非常简单而且不需要注入，就会使得我们在不经意间在单个类中使用许多 Facades，从而导致类变的越来越大。而使用依赖注入的时候，使用的类越多，构造方法就会越长，在视觉上就会引起注意，提醒你这个类有点庞大了。因此在使用 Facades 的时候，要特别注意控制好类的大小，让类的作用范围保持短小。


Facades Vs. 依赖注入#
//依赖注入的主要优点之一是切换注入类的实现的能力。这在测试的时候很有用，因为你可以注入一个 mock 或者 stub ，并断言在 stub 上调用的各种方法。

//真正的静态方法是不可能被 mock 或者 stub。但是，因为 Facades 使用动态方法来代理从服务容器解析的对象的方法调用，我们可以像测试注入的类实例一样来测试 Facades。例如，像下面的路由：
use Illuminate\Support\Facades\Cache;

Route::get('/cache', function () {
    return Cache::get('key');
});

//我们可以用下面的测试代码来验证使用预期的参数来调用 Cache::get 方法：
use Illuminate\Support\Facades\Cache;

/**
 * 一个基础功能的测试用例。
 *
 * @return void
 */
public function testBasicExample()
{
    Cache::shouldReceive('get')
         ->with('key')
         ->andReturn('value');

    $this->visit('/cache')
         ->see('value');
}


Facades Vs. 辅助函数#
//除了 Facades， Laravel 还包含各种「辅助函数」来实现一些常用的功能，比如生成视图、触发事件、调度任务或者发送 HTTP 响应。

return View::make('profile');

return view('profile');

//这里的 Facades 和辅助函数之间没有实际的区别。当你使用辅助函数时，你可以使用对应的 Facade 进行测试。例如，下面的路由：
Route::get('/cache', function () {
    return cache('key');
});

//在底层，辅助函数 cache 实际是调用 Cache facade 中的 get 方法
//因此，尽管我们使用的是辅助函数，我们依然可以编写以下测试来验证该方法是否使用我们预期的参数来调用：
use Illuminate\Support\Facades\Cache;

/**
 * 一个基础功能的测试用例。
 *
 * @return void
 */
public function testBasicExample()
{
    Cache::shouldReceive('get')
         ->with('key')
         ->andReturn('value');

    $this->visit('/cache')
         ->see('value');
}

Facades 工作原理#
//在 Laravel 应用中，Facade 就是一个可以从容器访问对象的类。其中核心的部件就是 Facade 类
// 不管是 Laravel 自带的 Facades，还是用户自定义的 Facades ，都继承自 Illuminate\Support\Facades\Facade 类。

Facade 基类使用了 __callStatic() 魔术方法将你的 Facades 的调用延迟，直到对象从容器中被解析出来

//在下面的例子中，调用了 Laravel 的缓存系统。通过浏览这段代码，可以假定在 Cache 类中调用了静态方法 get：
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 显示给定用户的信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function showProfile($id)
    {
        $user = Cache::get('user:'.$id);

        return view('profile', ['user' => $user]);
    }
}
//注意在上面这段代码中，我们「导入」Cache Facade 。这个 Facade 作为访问 Illuminate\Contracts\Cache\Factory 接口底层实现的代理。我们使用 Facade 进行的任何调用都将传递给 Laravel 缓存服务的底层实例。

如果我们看一下 Illuminate\Support\Facades\Cache 这个类，你会发现类中根本没有 get 这个静态方法：
class Cache extends Facade
{
    /**
     * 获取组件的注册名称。
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'cache'; }
}

Cache Facade 继承了 Facade 的类库，并且定义了 getFacadeAccessor() 方法
//这个方法的作用是返回服务容器绑定的名称。当用户调用 Cache Facade 中的任何静态方法时， Laravel 会从 服务容器 中解析 cache 绑定以及该对象运行所请求的方法（在这个例子中就是 get 方法）


Facade 类参考#


