<?php

//Lumen 在创建时就已考虑到测试的部分。事实上， Lumen 默认就支持用 PHPUnit 来做测试，并为你的应用程序创建好了 phpunit.xml 文件。框架还提供了一些便利的辅助函数，让你可以更直观的测试应用程序

//在 tests 目录中有提供一个 ExampleTest.php 的示例文件。安装新的 Laravel 应用程序之后，只需在命令行上运行 phpunit 就可以进行测试。


#定义并运行测试
要创建一个测试案例，直接创建你的测试文件并存放在 tests 目录下。测试文件必须继承 TestCase。 接着就可以像平常使用 PHPUnit 一样来定义测试方法。要运行测试只需要在命令行上运行 phpunit 命令即可：
<?php

class FooTest extends TestCase
{
    public function testSomethingIsTrue()
    {
        $this->assertTrue(true);
    }
}

注意： 如果要在你的类自定义自己的 setUp 方法，请确保调用了 parent::setUp。



#测试 JSON APIs
首先，让我们来编写一个测试，将 POST 请求发送至 /user ，并断言其会返回 JSON 格式的指定数组
<?php

class ExampleTest extends TestCase
{
    /**
     * 一个基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->json('POST', '/user', ['name' => 'Sally'])
             ->seeJson([
                'created' => true,
             ]);
    }
}


seeJson 方法会将传入的数组转换成 JSON，并验证该 JSON 片段是否存在于应用程序返回的 JSON 响应中的 任何位置。也就是说，即使有其它的属性存在于该 JSON 响应中，但是只要指定的片段存在，此测试便会通过


#验证完全匹配的 JSON
如果你想验证传入的数组是否与应用程序返回的 JSON 完全 匹配，则可以使用 seeJsonEquals 方法
<?php

class ExampleTest extends TestCase
{
    /**
     * 一个基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->post('/user', ['name' => 'Sally'])
             ->seeJsonEquals([
                'created' => true,
             ]);
    }
}


#认证
actingAs 辅助函数提供了简单的方式来让指定的用户认证为当前的用户：

<?php

class ExampleTest extends TestCase
{
    public function testApplication()
    {
        $user = factory('App\User')->create();

        $this->actingAs($user)
             ->get('/user');
    }
}


#自定义HTTP请求
如果你想要创建一个自定义的 HTTP 请求到应用程序上，并获取完整的 Illuminate\Http\Response 对象，则可以使用 call 方法：

public function testApplication()
{
    $response = $this->call('GET', '/');

    $this->assertEquals(200, $response->status());
}


//如果你创建的是 POST、PUT、或是 PATCH 请求，则可以在请求时传入一个数组作为输入数据。当然，你也可以在路由及控制器中通过 请求实例 取用这些数据：
$response = $this->call('POST', '/user', ['name' => 'Taylor']);



#使用数据库
Lumen 也提供了多种有用的工具来让你更容易的测试使用数据库的应用程序。首先，你可以使用 seeInDatabase 辅助函数，来断言数据库中是否存在与指定条件互相匹配的数据。
举例来说，如果我们想验证 users 数据表中是否存在 email 值为 sally@example.com 的数据，我们可以按照以下的方式来做测试：

public function testDatabase()
{
    // Make call to application...

    $this->seeInDatabase('users', ['email' => 'sally@foo.com']);
}

//当然，使用 seeInDatabase 方法及其它的辅助函数只是为了方便。你也可以随意使用 PHPUnit 内置的所有断言方法来扩充测试

//每次测试结束后重置数据库
在每次测试结束后都需要对数据进行重置，这样前面的测试数据就不会干扰到后面的测试。


#使用迁移
//其中有一种方式就是在每次测试后都还原数据库，并在下次测试前运行迁移。Lumen 提供了简洁的 DatabaseMigrations trait，它会自动帮你处理好这些操作。你只需在测试类中使用此 trait 即可


<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 一个基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->visit('/')
             ->see('Lumen.');
    }
}


#使用事务
//另一个方式，就是将每个测试案例都包含在数据库事务中。Lumen 提供了一个简洁的 DatabaseTransactions trait 来自动帮你处理好这些操作：
<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 一个基本的功能测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->visit('/')
             ->see('Lumen.');
    }
}



#模型工厂
//测试时，常常需要在运行测试之前写入一些数据到数据库中。创建测试数据时，除了手动的来设置每个字段的值，还可以使用 Eloquent 模型 的「工厂」来设置每个属性的默认值。在开始之前，你可以先查看下应用程序的 database/factories/ModelFactory.php 文件。此文件包含一个现成的工厂定义：
$factory->define('App\User', function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
    ];
});



#多个工厂类型
//有时你可能希望针对同一个 Eloquent 模型类来创建多个工厂。例如，除了一般用户的工厂之外，还有「管理员」工厂。你可以使用 defineAs 方法来定义这个工厂：
$factory->defineAs('App\User', 'admin', function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'admin' => true,
    ];
});

//除了从一般用户工厂复制所有基本属性，你也可以使用 raw 方法来获取所有基本属性。一旦你获取到这些属性，就可以轻松的为其增加任何额外值：
$factory->defineAs('App\User', 'admin', function ($faker) use ($factory) {
    $user = $factory->raw('App\User');

    return array_merge($user, ['admin' => true]);
});


#在测试中使用工厂
//在工厂定义后，就可以在测试或是数据库的填充文件中，通过全局的 factory 函数来生成模型实例。接着让我们先来看看几个创建模型的例子。首先我们会使用 make 方法创建模型，但不将它们保存至数据库
public function testDatabase()
{
    $user = factory('App\User')->make();

    // 在测试中使用模型...
}

//如果你想重写模型中的某些默认值，则可以传递一个包含数值的数组至 make 方法。只有指定的数值会被替换，其它剩余的数值则会按照工厂指定的默认值来设置：
$user = factory(App\User::class)->make([
    'name' => 'Abigail',
   ]);

//你也可以创建一个含有多个模型的集合，或创建一个指定类型的模型：
// 创建三个 App\User 实例...
$users = factory(App\User::class, 3)->make();

// 创建一个 App\User「管理员」实例...
$user = factory(App\User::class, 'admin')->make();

// 创建三个 App\User「管理员」实例...
$users = factory(App\User::class, 'admin', 3)->make();


#保存工厂模型
//你不仅可使用 create 方法来创建模型实例，而且也可以使用 Eloquent 的 save 方法来将它们保存至数据库：
public function testDatabase()
{
    $user = factory(App\User::class)->create();

    // 在测试中使用模型...
}

//同样的，你可以在数组传递至 create 方法时重写模型的属性：
$user = factory(App\User::class)->create([
    'name' => 'Abigail',
   ]);



#增加关联至模型
//你甚至可以保存多个模型到数据库上。在本例中，我们还会增加关联至我们所创建的模型。当使用 create 方法创建多个模型时，它会返回一个 Eloquent 集合实例，让你能够使用集合所提供的便利函数，像是 each：
$users = factory(App\User::class, 3)
           ->create()
           ->each(function($u) {
                $u->posts()->save(factory(App\Post::class)->make());
            });




#模拟
//模拟事件
如果你正在频繁地使用 Laravel 的事件系统，你可能希望在测试时停止或是模拟某些事件。举例来说，如果你正在测试用户注册功能，你可能不希望所有 UserRegistered 事件的处理进程都被运行，因为它们会触发「欢迎」邮件的发送

//Laravel 提供了简洁的 expectsEvents 方法，以验证预期的事件有被运行，可防止该事件的任何处理进程被运行：
<?php

class ExampleTest extends TestCase
{
    public function testUserRegistration()
    {
        $this->expectsEvents('App\Events\UserRegistered');

        // Test user registration code...
    }
}

//你可以使用 doesntExpectEvents 来验证某个事件 没被 触发：
<?php

class ExampleTest extends TestCase
{
    public function testUserRegistration()
    {
        $this->withoutEvents();

        // Test user registration code...
    }
}



#模拟任务
//Laravel 提供了一个简洁的 expectsJobs 方法，以验证预期的任务有被派送，但任务本身不会被运行：
<?php

class ExampleTest extends TestCase
{
    public function testPurchasePodcast()
    {
        $this->expectsJobs('App\Jobs\PurchasePodcast');

        // Test purchase podcast code...
    }
}
//注意： 此方法只检测 DispatchesJobs trait 的派送方法所派送出的任务。它并不会检测直接发送到 Queue::push 的任务。


#模拟Facades
//测试时，你可能时常需要模拟调用一个 Laravel facade。可参考下方的控制器行为：
<?php

namespace App\Http\Controllers;

use Cache;

class UserController extends Controller
{
    /**
     * Show a list of all users of the application.
     *
     * @return Response
     */
    public function index()
    {
        $value = Cache::get('key');

        //
    }
}
//我们可以通过 shouldReceive 方法模拟调用 Cache facade，它会返回一个 Mockery 模拟的实例。因为 facades 实际上已经被 Laravel 的 服务容器 解决并管理着，它们比起一般的静态类更有可测性。举个例子，让我们来模拟调用 Cache facade：
<?php

class FooTest extends TestCase
{
    public function testGetIndex()
    {
        Cache::shouldReceive('get')
                    ->once()
                    ->with('key')
                    ->andReturn('value');

        $this->get('/users');
    }
}
//注意： 你不应该模拟 Request facade。应该在测试时使用如 call 及 post 这样的 HTTP 辅助函数来传递你想要的数据。



