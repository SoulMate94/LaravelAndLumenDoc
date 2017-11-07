<?php

Eloquent：集合#
简介#
//默认情况下 Eloquent 返回的都是一个 Illuminate\Database\Eloquent\Collection 对象的实例，包含通过 get 方法或是访问一个关联来获取到的结果
//Eloquent 集合对象继承了 Laravel 集合基类，因此它自然也继承了许多可用于与 Eloquent 模型交互的方法。
//当然，所有集合都可以作为迭代器，来让你像遍历一个 PHP 数组一样来遍历一个集合：
$users = App\User::where('active', 1)->get();

foreach ($users as $user) {
    echo $user->name;
}

//然而，集合比数组更强大的地方是其使用了各种 map / reduce 的直观操作。例如，我们移除所有未激活的用户模型和收集其余各个用户的名字：
$users = App\User::where('active', 1)->get();

$names = $users->reject(function ($user) {
    return $user->active === false;
})
->map(function ($user) {
    return $user->name;
});

可用的方法#
集合对象#
//所有 Eloquent 集合都继承了 Laravel 集合 对象。因此，他们也继承了所有集合类提供的强大的方法：
##https://d.laravel-china.org/docs/5.1/collections#method-all

自定义集合#
//如果你需要使用一个自定义的 Collection 对象到自己的扩充方法上，则可以在模型中重写 newCollection 方法：
<?php

namespace App;

use App\CustomCollection;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 创建一个新的 Eloquent 集合实例。
     *
     * @param  array  $models
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return new CustomCollection($models);
    }
}
//一旦你定义了 newCollection 方法，则可在任何 Eloquent 返回该模型的 Collection 实例时，接收到一个你的自定义集合的实例。
//如果你想要在应用程序的每个模型中使用自定义集合，则应该在所有的模型继承的模型基类中重写 newCollection 方法。
