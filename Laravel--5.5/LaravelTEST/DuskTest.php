<?php

Laravel 测试之 - 浏览器测试 (Laravel Dusk)#
简介#


安装#
将 laravel/dusk 添加到你的项目 Composer 依赖中：
composer require --dev laravel/dusk
!!!永远不要在生产环境安装 Dusk。否则，任何人都可以未经授权地访问你的应用。

安装了 Dusk 之后，你需要注册 Laravel\Dusk\DuskServiceProvider 服务提供者
接下来运行 dusk:install Artisan 命令：
php artisan dusk:install


使用 dusk Artisan 命令来运行你的测试。dusk 命令可以接受任何 phpunit 能接受的参数：
php artisan dusk



使用其他浏览器#
Dusk 默认使用 Google Chrome 和 ChromeDriver 来运行你的浏览器测试
当然，你也可以运行你的 Selenium 服务器，然后对任何你想要的浏览器运行测试。

打开你的 tests/DuskTestCase.php 文件，这个文件是你应用中最基础的 Dusk 测试用例。你可以在这个文件中移除 startChromeDriver 方法。这样 Dusk 就不会自动运行 ChromeDriver：
/**
 * 为 Dusk 的测试做准备。
 *
 * @beforeClass
 * @return void
 */
public static function prepare()
{
    // static::startChromeDriver();
}


然后，你可以通过简单地修改 driver 方法来连接到你指定的 URL 和端口。同时，你要修改传递给 WebDriver 的「desired capabilities」：
/**
 * 创建 RemoteWebDriver 实例。
 *
 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
 */
protected function driver()
{
    return RemoteWebDriver::create(
        'http://localhost:4444/wd/hub', DesiredCapabilities::phantomjs()
    );
}



开始#


创建测试#
使用 dusk:make Artisan 命令来创建 Dusk 测试。创建好的测试类会放在 tests/Browser 目录：
php  artisan dusk:make LoginTest


运行测试#
使用 dusk Artisan 命令来运行你的浏览器测试：
php artisan dusk

dusk 命令可以接受任何 PHPUnit 能接受的参数。例如，让你可以只在指定 分组 中运行测试:
php artisan dusk --group=foo


手动运行 ChromeDriver#
Dusk 默认会尝试自动运行 ChromeDriver
如果在你特定的系统中不能正常运行，你可以在运行 dusk 命令之前通过手动的方式来运行 ChromeDriver。如果你选择手动运行 ChromeDriver，你需要在你的 tests/DuskTestCase.php 文件中注释掉下面这行：
/**
 * 为 Dusk 的测试做准备。
 *
 * @beforeClass
 * @return void
 */
public static function prepare()
{
    // static::startChromeDriver();
}

另外，如果你是在非 9515 端口运行 ChromeDriver，你需要在 tests/DuskTestCase.php 修改 driver 方法：
/**
 * 创建 RemoteWebDriver 实例。
 *
 * @return \Facebook\WebDriver\Remote\RemoteWebDriver
 */
protected function driver()
{
    return RemoteWebDriver::create(
        'http://localhost:9515', DesiredCapabilities::chrome()
    );
}


环境处理#
在你项目的根目录创建 .env.dusk.{environment} 文件来强制 Dusk 使用自己的的环境文件来运行测试
简单来说，如果你想要以 local 环境来运行 dusk 命令，你需要创建一个 .env.dusk.local 文件。

运行测试的时候，Dusk 会备份你的 .env 文件，然后重命名你的 Dusk 环境文件为 .env。一旦测试结束之后，将会恢复你的 .env 文件。



创建浏览器#
让我们来写一个测试用例，这个测试用例可以验证我们是否能够登录系统
<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Chrome;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * 一个基本的浏览器测试示例。
     *
     * @return void
     */
    public function testBasicExample()
    {
        $user = factory(User::class)->create([
            'email' => 'taylor@laravel.com',
        ]);

        $this->browse(function ($browser) use ($user) {
            $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'secret')
                    ->press('Login')
                    ->assertPathIs('/home');
        });
    }
}

在上面的示例中，你可以看到 browse 方法接受一个回调参数。 Dusk 会自动将这个浏览器实例注入到回调当中，而这个浏览器实例可以让你与你的应用之间进行交互和断言。


创建多个浏览器#
有时你可能需要多个浏览器才能正确地进行测试
要想创建多个浏览器，你只需要简单地在 browse 方法的回调中，用名字来区分浏览器实例，然后传给回调来 「申请」 多个浏览器实例即可：
$this->browse(function ($first, $second) {
    $first->loginAs(User::find(1))
          ->visit('/home')
          ->waitForText('Message');

    $second->loginAs(User::find(2))
           ->visit('/home')
           ->waitForText('Message')
           ->type('message', 'Hey Taylor')
           ->press('Send');

    $first->waitForText('Hey Taylor')
          ->assertSee('Jeffrey Way');
});


认证#
你可能经常会测试一些需要认证的页面。你可以使用 Dusk 的 loginAs 方法来避免每个测试都去登录页面登录一次。
loginAs 方法可以使用用户 ID 或者用户模型实例：
$this->browse(function ($first, $second) {
    $first->loginAs(User::find(1))
          ->visit('/home');
});

!!使用 loginAs 方法后, 该用户的 session 将会持久化供其他测试用例使用。

与元素交互#
点击链接#
你可以在你的浏览器实例中使用 clickLink 方法来模拟点击一个链接。clickLink 方法会点击传入的显示文本：
$browser->clickLink($linkText);



文本、值和属性#
获取和设置值#
// 获取值...
$value = $browser->value('selector');

// 设置值...
$browser->value('selector', 'value');

获取文本#
$text = $browser->text('selector');

获取属性#
$attribute = $browser->attribute('selector', 'value');

使用表单#
输入值#
首先，让我们来看看一个在 input 框中输入文本的示例：
$browser->type('email', 'taylor@laravel.com');
!!注意：虽然 type 方法可以传递 CSS 选择器作为第一个参数，但这并不是强制要求

你可以使用 clear 方法来 「清除」 输入值。
$browser->clear('email');

下拉菜单#
$browser->select('size', 'Large');

你也可以通过省略第二个参数来随机选择一个选项：
$browser->select('size');

复选框#
$browser->check('terms');

$browser->uncheck('terms');


单选按钮#
$browser->radio('version', 'php7');


附加文件#
$browser->attach('photo', __DIR__.'/photos/me.png');


使用键盘#
keys 方法让你可以在指定元素中输入比 type 方法更加复杂的输入序列
$browser->keys('selector', ['{shift}', 'taylor'], 'otwell');

甚至你可以在你应用中选中某个元素之后按下 「快捷键」：
$browser->keys('.app', ['{command}', 'j']);

使用鼠标#
点击元素#
$browser->click('.selector');

Mouseover#
$browser->mouseover('.selector');

拖拽#
$browser->drag('.from-selector', '.to-selector');

或者将元素向一个方向拖拽
$browser->dragLeft('.selector', 10);
$browser->dragRight('.selector', 10);
$browser->dragUp('.selector', 10);
$browser->dragDown('.selector', 10);



元素作用域#
例如，你可能希望在某个 table 中断言有某些文本，然后在同一个 table 中点击按钮。你可以使用 with 方法来达到这个目的。
$browser->with('.table', function ($table) {
    $table->assertSee('Hello World')
          ->clickLink('Delete');
});

等待元素#
等待#
如果你需要暂停指定毫秒数，你可以使用 pause 方法：
$browser->pause(1000);

等待选择器元素#
waitFor 方法用来暂停测试的执行，直到与 CSS 选择器匹配的元素显示在页面中
在抛出异常之前，默认最多暂停 5 秒。如果需要，你也可以自定义超时时间作为第二个参数传给这个方法：
// 最多为这个元素的显示等待 5 秒...
$browser->waitFor('.selector');

// 最多为这个元素的显示等待 1 秒...
$browser->waitFor('.selector', 1);

你也可以等待指定元素直到超时都还在页面中找不到：
$browser->waitUntilMissing('.selector');

$browser->waitUntilMissing('.selector', 1);

可用元素的作用域#
有时候，你可能想要等待与指定选择器匹配的元素，然后与这元素进行交互

例如，你可能需要等待某个模态窗口可用，然后在模态窗口中点击「OK」按钮。在这种情况下，可以使用 whenAvailable 方法。所有闭包中的操作都针对这个原始的元素：
$browser->whenAvailable('.modal', function ($modal) {
    $modal->assertSee('Hello World')
          ->press('OK');
});

等待文本#
waitForText 方法用于等待指定文本，直到显示在页面中为止：
// 最多为这个文本的显示等待 5 秒...
$browser->waitForText('Hello World');

// 最多为这个文本的显示等待 1 秒...
$browser->waitForText('Hello World', 1);

等待超链接#
waitForLink 方法用来等待指定链接文本，直到链接文本显示在页面中为止：
// 最多为这个链接的显示等待 5 秒...
$browser->waitForLink('Create');

// 最多为这个链接的显示等待 1 秒...
$browser->waitForLink('Create', 1);

等待页面跳转#
在进行例如 $browser->assertPathIs('/home')的路径断言时，如果 window.location.pathname 为异步完成，断言就会失败
你需要使用 waitForLocation 方法去等待指定的跳转：
$browser->waitForLocation('/secret');

等待页面重载#
如果你需要在页面重载后进行断言，你可以使用 waitForReload 方法：
$browser->click('.some-action')
        ->waitForReload()
        ->assertSee('something');

等待 JavaScript 表达式#
有时候你可能想要暂停测试用例的执行，直到指定的 JavaScript 表达式计算结果为 true
使用 waitUntil 方法可以让你很容易做到这一点。传递表达式给方法的时候，你不需要包括 return 关键词或者结束分号：
// 最多为表达式的成立等待 5 秒...
$browser->waitUntil('App.dataLoaded');

$browser->waitUntil('App.data.servers.length > 0');

// 最多为表达式的成立等待 1 秒...
$browser->waitUntil('App.data.servers.length > 0', 1);

等待回调#
Dusk 中的许多「等待」方法依赖于 waitUsing 方法
该方法可以等待一个回调返回 true。waitUsing 接受的参数为最大等待秒数、闭包的执行间隔、闭包以及一个可选的错误信息。
$browser->waitUsing(10, 1, function () use ($something) {
    return $something->isReady();
}, "Something wasn't ready in time.");



可用的断言#
$browser->assertTitle($title)   断言页面标题符合指定文本。

$browser->assertTitleContains($title)   断言页面标题包含指定文本。

$browser->assertPathBeginsWith($path)   断言当前 URL 开始于指定的值。

$browser->assertPathIs('/home') 断言当前 URL 为指定的值。

$browser->assertPathIsNot('/home')  断言当前 URL 不是指定的值。

$browser->assertRouteIs($name, $parameters) 断言当前 URL 为指定的路由生成。

$browser->assertQueryStringHas($name, $value)   断言指定的查询条件为指定的值

$browser->assertQueryStringMissing($name)   断言不存在指定的查询条件。

$browser->assertHasQueryStringParameter($name)  断言存在指定的查询条件。

$browser->assertHasCookie($name)    断言存在指定 Cookie。

$browser->assertCookieValue($name, $value)  断言指定 Cookie 为指定值。

$browser->assertPlainCookieValue($name, $value) 断言一个未加密的 Cookie 为指定值。

$browser->assertSee($text)  断言页面中存在指定文本。

$browser->assertDontSee($text)  断言页面中不存在指定文本。

$browser->assertSeeIn($selector, $text) 断言匹配指定选择器中存在指定文本。

$browser->assertDontSeeIn($selector, $text) 断言匹配指定选择器中不存在指定文本

$browser->assertSourceHas($code)    断言页面的源码中存在指定的值。

$browser->assertSourceMissing($code)    断言页面的源码中不存在指定的值。

$browser->assertSeeLink($linkText)  断言页面中存在指定链接。

$browser->assertDontSeeLink($linkText)  断言页面中不存在指定链接。

$browser->assertInputValue($field, $value)  断言指定的 input 输入框为指定的值。

$browser->assertInputValueIsNot($field, $value) 断言指定的 input 输入框不为指定的值。

$browser->assertChecked($field) 断言指定的复选框已被选中。

$browser->assertNotChecked($field)  断言指定的复选框未被选中。

$browser->assertRadioSelected($field, $value)   断言指定的单选框已被选中。

$browser->assertRadioNotSelected($field, $value)    断言指定的单选框未被选中。

$browser->assertSelected($field, $value)    断言指定的下拉列表指定的值被选中。

$browser->assertNotSelected($field, $value) 断言指定的下拉列表指定的值未被选中

$browser->assertSelectHasOptions($field, $values)   断言指定数组中的值存在于指定的下拉列表的选项中。

$browser->assertSelectMissingOptions($field, $values)   断言指定数组中的值不存在于指定的下拉列表的选项中

$browser->assertSelectHasOption($field, $value) 断言指定的值存在于指定的下拉列表的选项中。

$browser->assertValue($selector, $value)    断言匹配指定选择器的元素为指定值。

$browser->assertVisible($selector)  断言匹配指定选择器的元素是可见的。

$browser->assertMissing($selector)  断言匹配指定选择器的元素是不可见的。

$browser->assertDialogOpened($message)  断言消息为指定值的对话框已被打开。



页面#
使用 dusk:page Artisan 命令来创建页面对象。所有的页面对象会存放在 tests/Browser/Pages 目录中：
php artisan dusk:page Login

配置页面#
页面默认拥有 3 个方法： url， assert 和 elements
url 方法#
url 方法应该返回表示页面 URL 的路径。 Dusk 将会在浏览器中使用这个 URL 来导航到具体页面：
/**
 * Get the URL for the page.
 *
 * @return string
 */
public function url()
{
    return '/login';
}

The assert Method#
assert 方法可以作出任何断言来验证浏览器是否在指定页面上。
这个方法并不是必须的。你可以根据你自己的需求来做出这些断言。这些断言会在你浏览到这个页面的时候自动执行：
/**
 * 断言浏览器是否正在指定页面。
 *
 * @return void
 */
public function assert(Browser $browser)
{
    $browser->assertPathIs($this->url());
}

导航至页面#
一旦页面配置好之后，你可以使用 visit 方法导航至页面：
use Tests\Browser\Pages\Login;

$browser->visit(new Login);

有时候，你可能已经在指定页面了，你需要的只是「加载」当前页面的选择器和方法到当前测试中来
use Tests\Browser\Pages\CreatePlaylist;

$browser->visit('/dashboard')
        ->clickLink('Create Playlist')
        ->on(new CreatePlaylist)
        ->assertSee('@create');

选择器简写#
elements 方法允许你为页面中的任何 CSS 选择器定义简单易记的简写
例如，让我们为应用登录页中的 email 输入框定义一个简写：
/**
 * 获取页面的元素简写。
 *
 * @return array
 */
public function elements()
{
    return [
        '@email' => 'input[name=email]',
    ];
}

现在你可以用这个简写来代替之前在页面中使用的完整 CSS 选择器：
$browser->type('@email', 'taylor@laravel.com');

全局的选择器简写#
安装 Dusk 之后，Page 基类存放在你的 tests/Browser/Pages 目录
该类中包含一个 siteElements 方法，这个方法可以用来定义全局的选择器简写，这样在你应用中每个页面都可以使用这些全局选择器简写了：
/**
 * 获取站点全局的选择器简写。
 *
 * @return array
 */
public static function siteElements()
{
    return [
        '@element' => '#selector',
    ];
}

页面方法#
<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class Dashboard extends Page
{
    // 其他页面方法...

    /**
     * 创建一个新的播放列表。
     *
     * @param  \Laravel\Dusk\Browser  $browser
     * @param  string  $name
     * @return void
     */
    public function createPlaylist(Browser $browser, $name)
    {
        $browser->type('name', $name)
                ->check('share')
                ->press('Create Playlist');
    }
}

方法被定义之后，你可以在任何使用到该页的测试中使用这个方法了。浏览器实例会自动传递该页面方法：
use Tests\Browser\Pages\Dashboard;

$browser->visit(new Dashboard)
        ->createPlaylist('My Playlist')
        ->assertSee('My Playlist');


持续集成#
Travis CI#
在 Travis CI 中运行 Dusk 时需要「sudo-enabled」的 Ubuntu 14.04 (Trusty) 环境
由于 Travis CI 不是图形环境，我们需要一些额外的步骤去启动 Chrome 浏览器，另外，我们需要使用 php artisan serve 命令去启动 PHP 的内置服务器。
sudo: required
dist: trusty

addons:
   chrome: stable

install:
   - cp .env.testing .env
   - travis_retry composer install --no-interaction --prefer-dist --no-suggest

before_script:
   - google-chrome-stable --headless --disable-gpu --remote-debugging-port=9222 http://localhost &
   - php artisan serve &

script:
   - php artisan dusk


CircleCI#

CircleCI 1.0#
在 CircleCI 1.0 中运行 Dusk 时需要使用以下配置进行启动。与 TravisCI 相同，我们需要使用 php artisan serve 命令去启动 PHP 的内置服务器。
dependencies:
  pre:
    - curl -L -o google-chrome.deb https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
    - sudo dpkg -i google-chrome.deb
    - sudo sed -i 's|HERE/chrome\"|HERE/chrome\" --disable-setuid-sandbox|g' /opt/google/chrome/google-chrome
    - rm google-chrome.deb

test:
  pre:
    - "./vendor/laravel/dusk/bin/chromedriver-linux":
        background: true
    - cp .env.testing .env
    - "php artisan serve":
        background: true

  override:
    - php artisan dusk


CircleCI 2.0#
在 CircleCI 2.0 中运行 Dusk 时需要将以下 steps 添加至 build：
version: 2
jobs:
  build:
    steps:
      - run: sudo apt-get install -y libsqlite3-dev
      - run: cp .env.testing .env
      - run: composer install -n --ignore-platform-reqs
      - run: npm install
      - run: npm run production
      - run: vendor/bin/phpunit

      - run:
        name: Start Chrome Driver
        command: ./vendor/laravel/dusk/bin/chromedriver-linux
        background: true

      - run:
        name: Run Laravel Server
        command: php artisan serve
        background: true

      - run:
        name: Run Laravel Dusk Tests
        command: php artisan dusk