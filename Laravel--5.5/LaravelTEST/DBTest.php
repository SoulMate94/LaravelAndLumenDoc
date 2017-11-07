<?php

Laravel 测试之 - 数据库测试#
简介#
举例来说，如果我们想验证 users 数据表中是否存在 email 值为 sally@example.com 的数据，我们可以按照以下的方式来做测试：
public function testDatabase()
{
    // 创建调用至应用程序...

    $this->assertDatabaseHas('users', [
        'email' => 'sally@example.com'
    ]);
}
你也可以使用 assertDatabaseMissing 辅助函数来断言数据不在数据库中。


每次测试后重置数据库#


使用迁移#
其中有一种方式就是在每次测试后都还原数据库，并在下次测试前运行迁移。
Laravel 提供了简洁的 DatabaseMigrations trait，它会自动帮你处理好这些操作。
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $response = $this->get('/');

        // ...
    }
}

使用事务#
另一个方式，就是将每个测试案例都包含在数据库事务中
Laravel 提供了一个简洁的 DatabaseTransactions trait 来自动帮你处理好这些操作。
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $response = $this->get('/');

        // ...
    }
}


创建模型工厂#
测试时，常常需要在运行测试之前写入一些数据到数据库中

在开始之前，你可以先查看下应用程序的 database/factories/UserFactory.php 文件。此文件包含一个现成的模型工厂定义：
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

为了更好的组织代码，你也可以自己为每个数据模型创建对应的模型工厂类。比如说，你可以在 database/factories 文件夹下创建 UserFactory.php 和 CommentFactory.php 文件。
在 factories 目录中的文件都会被 Laravel 自动加载。



工厂状态#
工厂状态可以让你任意组合你的模型工厂，仅需要做出适当差异化的修改，就可以达到让模型拥有多种不同的状态
$factory->state(App\User::class, 'delinquent', [
    'account_status' => 'delinquent',
]);

如果你的工厂状态需要计算或者需要使用 $faker 实例，你可以使用闭包方法来实现状态属性的修改：
$factory->state(App\User::class, 'address', function ($faker) {
    return [
        'address' => $faker->address,
    ];
});


在测试中使用模型工厂#
创建模型#
在模型工厂定义后，就可以在测试或是数据库的填充文件中，通过全局的 factory 函数来生成模型实例
public function testDatabase()
{
    $user = factory(App\User::class)->make();

    // 在测试中使用模型...
}

你也可以创建一个含有多个模型的集合，或创建一个指定类型的模型：
// 创建一个 App\User 实例
$users = factory(App\User::class, 3)->make()

应用模型工厂状态#
你可能需要在你的模型中应用不同的 模型工厂状态。如果你想模型加上多种不同的状态，你只须指定每个你想添加的状态名称即可：
$users = factory(App\User::class, 5)->states('delinquent')->make();

$users = factory(App\User::class, 5)->states('premium', 'delinquent')->make();


重写模型属性#
$user = factory(App\User::class)->make([
    'name' => 'Abigail',
]);

持久化模型#
create 方法不仅会创建模型实例，同时会使用 Eloquent 的 save 方法来将它们保存至数据库：
public function testDatabase()
{
    // 创建一个 App\User 实例
    $user = factory(App\User::class)->create();

    // 创建 3 个 App\User 实例
    $users = factory(App\User::class, 3)->create();

    // 在测试中使用模型...
}

同样的，你可以在数组传递至 create 方法时重写模型的属性
$user = factory(App\User::class)->create([
    'name' => 'Abigail',
]);

模型关联#
$users = factory(App\User::class, 3)
           ->create()
           ->each(function ($u) {
                $u->posts()->save(factory(App\Post::class)->make());
            });

关联和属性闭包#
你可以使用闭包参数来创建模型关联。例如如果你想在创建一个 Post 的顺便创建一个 User 实例：
$factory->define(App\Post::class, function ($faker) {
    return [
        'title' => $faker->title,
        'content' => $faker->paragraph,
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        }
    ];
});


这些闭包也可以获取到生成的模型工厂包含的属性数组：
$factory->define(App\Post::class, function ($faker) {
    return [
        'title' => $faker->title,
        'content' => $faker->paragraph,
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'user_type' => function (array $post) {
            return App\User::find($post['user_id'])->type;
        }
    ];
});

可用的断言方法#

$this->assertDatabaseHas($table, array $data);  断言数据库里含有指定表。

$this->assertDatabaseMissing($table, array $data);  断言表里没有指定数据。

$this->assertSoftDeleted($table, array $data);  断言指定记录已经被软删除。