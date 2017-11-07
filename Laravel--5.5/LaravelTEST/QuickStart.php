<?php


Laravel 测试: 入门指南#
简介#
默认在你应用的 tests 目录下包含了两个子目录： Feature 和 Unit

单元测试是针对你代码中相对独立而且非常少的一部分代码来进行测试

功能测试是针对你代码中大部分的代码来进行测试，包括几个对象的相互作用，甚至是一个完整的 HTTP 请求 JSON 实例



测试环境#
在运行测试时，Laravel 会根据 phpunit.xml 文件中设定好的环境变量自动将环境变量设置为 testing，并将 Session 及缓存以 array 的形式存储，也就是说在测试时不会持久化任何 Session 或缓存数据。


你可以随意创建其它必要的测试环境配置。testing 环境的变量可以在 phpunit.xml 文件中被修改，但是在运行测试之前，请确保使用 config:clear Artisan 命令来清除配置信息的缓存

php artisan config:clear Artisan


定义并运行测试#
可以使用 make:test Artisan 命令，创建一个测试用例：
// 在 Feature 目录下创建一个测试类...
php artisan make:test UserTest

// 在 Unit 目录下创建一个测试类...
php artisan make:test UserTest --unit

测试类生成之后，你就可以像平常使用 PHPUnit 一样来定义测试方法。要运行测试只需要在终端上运行 phpunit 命令即可：
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * 基本的测试用例。
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}

!!如果要在你的测试类自定义自己的 setUp 方法，请确保调用了 parent::setUp() 方法