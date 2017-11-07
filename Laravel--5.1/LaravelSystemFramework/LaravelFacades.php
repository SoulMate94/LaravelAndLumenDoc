<?php

#Facades#
#简介
//译者注：Facade 中文意为 - 门面，外观，包装器。专有名词的属性多一点，故不采用直译，而是直接做专有名词使用
Facades 为应用程序的 服务容器 中可用的类提供了一个「静态」接口

#使用 Facades
//在 Laravel 应用程序环境（Context）中，facade 是个提供从容器访问对象的类
//Facade 类是这个机制运作的核心部件。Laravel 的 facades，以及任何你创建的自定义 facades，会继承基底 Illuminate\Support\Facades\Facade 类。
facade 类只需要去实现一个方法：getFacadeAccessor
//getFacadeAccessor 方法的工作定义是从容器中解析出什么。Facade 基类利用 __callStatic() 魔术方法从你的 facade 延迟调用来解析对象

//在下面的例子，调用了 Laravel 的缓存系统
<?php

namespace App\Http\Controllers;

use Cache;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * 显示指定用户的个人数据。
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
//注意在文件的上方，我们「导入」Cache facade。这个 facade 做为访问底层实现 Illuminate\Contracts\Cache\Factory 接口的代理。我们使用 facade 的任何调用将会发送给 Laravel 缓存服务的底层实例。
//如果我们查看 Illuminate\Support\Facades\Cache 类，你会发现没有静态方法 get：
class Cache extends Facade
{
    /**
     * 获取组件的注册名称。
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'cache'; }
}

//相反的，Cache facade 继承了基底 Facade 类以及定义了 getFacadeAccessor() 方法
//记住，这个方法的工作是返回服务容器绑定的名称


#Facade 类参考
//在下方你可以找到每个 facade 及其底层的类。这个工具对于通过指定 facade 的来源快速寻找 API 文档相当有用。可应用的 服务容器绑定 关键字也包含在里面。
//http://d.laravel-china.org/docs/5.1/facades