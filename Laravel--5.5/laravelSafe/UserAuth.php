<?php

用户认证#
简介#
!!只需在新的 Laravel 应用上运行 php artisan make:auth 和 php artisan migrate 命令。然后可以用浏览器访问 http://your-app.dev/register 或者你在程序中定义的其它 URL 。这两个命令就可以构建好整个认证系统。

其配置文件位于 config/auth.php

数据库注意事项#
//默认情况下，Laravel 在 app 目录中包含了一个 Eloquent 模型 App\User
为 App\User 模型创建数据库表结构时，确保密码字段长度至少为 60 个字符以及默认字符串列长度为 255 个字符。

你要验证的用户（或等效的）表要包含一个可空的、长度为 100 的字符串 remember_token。这个字段将用于存储当用户登录应用并勾选「记住我」时的令牌

认证快速入门#
//Laravel 自带几个预构建的认证控制器，它们被放置在 App\Http\Controllers\Auth 命名空间内
RegisterController 处理新用户注册，
LoginController 处理用户认证，
ForgotPasswordController 处理用于重置密码的邮件链接，而 
ResetPasswordController 包含重置密码的逻辑



路由#
php artisan make:auth
//该命令最好在新的应用下使用，它会生成布局、注册和登录视图以及所有的认证接口的路由。同时它还会生成 HomeController 来处理应用的登录请求

视图#
//php artisan make:auth 命令会在 resources/views/auth 目录创建所有认证需要的视图
//同时，make:auth 命令还创建了 resources/views/layouts 目录，该目录包含了应用的基本布局视图。所有这些视图都是用 Bootstrap CSS 框架，你也可以根据需要对其自定义


认证#
//当用户成功通过身份认证后，他们会被重定向到 /home URI。你可以通过在 LoginController、RegisterController 和 ResetPasswordController中设置 redirectTo 属性来自定义重定向的位置：
protected $redirectTo = '/';

如果重定向路径需要自定义生成逻辑，你可以定义 redirectTo 方法来代替 redirectTo 属性：
public function redirectTo()
{
	return '/path';
}
!!redirectTo 方法优先于 redirectTo 属性。

自定义用户名#
//Laravel 默认使用 email 字段来认证。如果你想用其他字段认证，可以在 LoginController 里面定义一个 username 方法：
public function username()
{
	return 'username';
}

自定义看守器#
//你还可以自定义用于认证和注册用户的「看守器」。要实现这一功能，需要在 LoginController、RegisterController 和 ResetPasswordController 中定义 guard 方法。该方法需要返回一个 guard 实例：
<?php
use Illuminate\Support\Facades\Auth;
protected function guard()
{
	return Auth::guard('guard-name');
}

自定义验证／存储#
//要修改新用户在应用注册时所需的表单字段，或者自定义如何将新用户存储到数据库中，你可以修改 RegisterController 类。该类负责验证和创建应用的新用户。
RegisterController 的 validator 方法包含了应用验证新用户的规则，你可以按需要自定义该方法。
RegisterController 的 create 方法负责使用 Eloquent ORM 在数据库中创建新的 App\User 记录。你可以根据数据库的需要自定义该方法。

检索认证用户#
你可以通过 Auth facade 来访问认证的用户：
<?php
use Illuminate\Support\Facades\Auth;

//获取当前已经认证的用户
$user = Auth::user();

//获取当前已经认证的用户ID
$id = Auth::id();

或者，你还可以通过 Illuminate\Http\Request 实例来访问已认证的用户。请记住，类型提示的类会被自动注入到您的控制器方法中：

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * 更新用户的简介。
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $user=$request->user() 返回已认证的用户的实例...
    }
}

确定当前用户是否认证#
use Illuminate\Support\Facades\Auth;

if (Auth::check()) {
	// user already login
}
!!即使可以使用 check 方法确定用户是否被认证，在允许用户访问某些路由／控制器之前，通常还是会使用中间件来验证用户是否进行身份验证

保护路由#
//Laravel 自带了在 Illuminate\Auth\Middleware\Authenticate 中定义的 auth 中间件。由于这个中间件已经在 HTTP 内核中注册，
//所以只需要将中间件附加到路由定义中：
Route::get('profile',function(){
	// only AuthUser can
})->middleware('auth');

//当然，如果使用 控制器，则可以在构造器中调用 middleware 方法来代替在路由中直接定义：
public function __construct()
{
	$this->middleware('auth');
}
指定看守器#
//将 auth 中间件添加到路由时，还需要指定使用哪个看守器来认证用户。指定的看守器对应配置文件 auth.php 中 guards 数组的某个键：
public function __construct()
{
	$this->middleware('auth:api');
}

登录限制#
//Laravel 内置的控制器 LoginController 已经包含了 Illuminate\Foundation\Auth\ThrottlesLogins trait。默认情况下，如果用户在进行几次尝试后仍不能提供正确的凭证，该用户将在一分钟内无法进行登录。这个限制基于用户的用户名／邮件地址和 IP 地址


手动认证用户#
//当然，不一定要使用 Laravel 内置的认证控制器。如果选择删除这些控制器，你可以直接调用 Laravel 的认证类来管理用户认证。别担心，这简单得很。
我们可以通过 Auth facade 来访问 Laravel 的认证服务，因此需要确认类的顶部引入了 Auth facade。接下来让我们看一下 attempt 方法：
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{

    /**
     * 处理身份认证尝试.
     *
     * @return Response
     */
    public function authenticate()
    {
    	if (Auth::attempt(['email'=>$email,'password'=>$password])) {
    		# 认证通过
    		return redirect()->intended('dashboard');
    	}
    }
}
//attempt 方法会接受一个键值对数组作为其第一个参数。这个数组的值将用来在数据库表中查找用户。所以在上面的例子中，用户将被 email 字段的值检索。如果用户被找到了，数据库中存储的散列密码将与通过数组传递给方法的散列 password 进行比较。 如果两个散列密码匹配，就会为用户启动一个已认证的会话
如果认证成功，attempt 方法将返回 true，反之则返回 false。
//重定向器上的 intended 方法将重定向到用户尝试访问的 URL。如果该 URL 无效，会给该方法传递回退 URI。
指定额外条件#
//除了用户的电子邮件和密码之外，你还可以向身份验证查询添加额外的条件。例如，我们可能会验证用户是否被标记为「活动」状态：
if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
    // 用户处于活动状态，不被暂停，并且存在。
}

访问指定的看守器实例#
//可以通过 Auth facade 的 guard 方法来指定要使用哪个看守器实例。这允许你使用完全独立的可认证模型或用户表来管理应用的抽离出来的身份验证。
传递给 guard 方法的看守器名称应该与 auth.php 配置文件中 guards 中的其中一个值相对应：
if (Auth::guard('admin')->attempt($credentials)) {
	# code...
}

注销用户#
//要让用户从应用中注销，可以在 Auth facade 上使用 logout 方法。这会清除用户会话中的身份验证信息：
Auth::logout();

记住用户#
//如果你想要在应用中提供「记住我」的功能 ， 则可以传递一个布尔值作为 attempt 方法的第二个参数，这会使在用户手动注销前一直保持已验证状态。
//当然，users 数据表必须包含 remember_token 字段，这是用来保存「记住我」的令牌。
if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
    // 这个用户被记住了...
}
!!如果你使用 Laravel 内置的 LoginController，则「记住」用户的逻辑已经由控制器使用的 traits 来实现
//如果你「记住」用户，可以使用 viaRemember 方法来检查这个用户是否使用「记住我」 cookie 进行认证：
if (Auth::viaRemember) {
	# code...
}

其它认证方法#
验证用户实例#
//如果需要将现有用户实例记录到应用，可以使用用户实例调用 login 方法
给定的对象必须实现了 Illuminate\Contracts\Auth\Authenticatable 契约 。当然，Laravel 自带的 App\User 模型已经实现了这个接口：
Auth::login($user);

//login and remember User
Auth::login($user,true);
//当然，你也可以指定要使用的看守器实例：
Auth::guard('admin')->login($user);

通过 ID 验证用户#
//你可以使用 loginUsingId 方法通过其 ID 将用户记录到应用中。这个方法只接受要验证的用户的主键：
Auth::loginUsingId(1);

//登录并且「记住」给定的用户...
Auth::loginUsingId(1, true);

仅验证用户一次#
//你可以使用 once 方法将用户记录到单个请求的应用中。不会使用任何会话或 cookies， 这个对于构建无状态的 API 非常的有用：
if (Auth::once($credentials)) {
    //
}


HTTP 基础认证#
//HTTP 基础认证 提供一种快速方式来认证应用的用户，而且不需要设置专用的「登录」页面
//先把 auth.basic 中间件 添加到你的路由。auth.basic 中间件已经被包含在 Laravel 框架中，所以你不需要定义它
Route::get('profile', function(){
	//only Auth pass user can

})->middleware('auth.basic');
//中间件被增加到路由后，在浏览器中访问路由时，将自动提示你输入凭证。默认情况下，auth.basic 中间件将会使用用户记录上的 email 字段作为「用户名」
FastCGI 的注意事项#
//如果使用了 PHP FastCGI，HTTP 基础认证可能无法正常工作。你需要将下面这几行加入你 .htaccess 文件中:
RewriteCond %{HTTP:Authorization} ^(.+)$
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]


无状态 HTTP 基础认证#
//你可以使用 HTTP 基础认证，而不在会话中设置用户标识符 cookie，这对于 API 认证来说特别有用
//为此，请 定义一个中间件 并调用 onceBasic 方法。如果 onceBasic 方法没有返回任何响应的话，这个请求可以进一步传递到应用程序中：

<?php
namespace Illuminate\Auth\Middleware;

use Illuminate\Support\Facades\Auth;

class AuthenticateOnceWithBasicAuth
{
    /**
     * 处理传入的请求。
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        return Auth::onceBasic() ?: $next($request);
    }

}

//接着，注册路由中间件 ，然后将它附加到路由：
Route::get('api/user', function(){
	//only Auth pass user can
})->middleware('auth.basic.once');



增加自定义用户提供器#
//你可以使用 Auth facade 的 extend 方法来定义自己的身份验证提供器
// 你需要在 服务提供器 中调用这个提供器。由于 Laravel 已经配备了 AuthServiceProvider，我们可以把代码放在这个提供器中
<?php

namespace App\Providers;

use App\Services\Auth\JwtGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	/**
     * 注册任意应用认证／授权服务。
     *
     * @return void
     */
    public function boot()
    {
    	$this->registerPolicies();

    	Auth::extend('jwt', function($app, $name, array $config){
			// 返回一个 Illuminate\Contracts\Auth\Guard 实例...

			return new JwtGuard(Auth::createUserProvider($config['provider']));
    	})
    }
}

//正如上面的代码所示，传递给 extend 方法的回调应该返回 Illuminate\Contracts\Auth\Guard 的实现的实例
//这个接口包含你需要实现的方法来定义一个自定义看守器。定义完之后，你可以在 auth.php 配置文件的 guards 配置中使用这个看守器：
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],

添加自定义用户提供器#
//我们使用 Auth facade 上的 provider 方法定义自定义用户提供器：
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use App\Extensions\RiakUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * 注册任何应用认证／授权服务。
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('riak', function ($app, array $config) {
            // 返回 Illuminate\Contracts\Auth\UserProvider 实例...

            return new RiakUserProvider($app->make('riak.connection'));
        });
    }
}
//使用 provider 方法注册用户提供器后，你可以在配置文件 auth.php中切换到新的用户提供器。首先，定义一个使用新驱动的 provider：
'providers' => [
    'users' => [
        'driver' => 'riak',
    ],
],

//最后，你可以在 guards 配置中使用这个提供器：
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

用户提供器契约#
//Illuminate\Contracts\Auth\UserProvider 的实现只负责从永久存储系统（如 MySQL、Riak 等）中获取 Illuminate\Contracts\Auth\Authenticatable 的实现实例
//这两个接口允许 Laravel 认证机制继续运行，无论用户数据如何被存储或使用什么类型的类实现它。
让我们来看看 Illuminate\Contracts\Auth\UserProvider 契约：

<?php

namespace Illuminate\Contracts\Auth;

interface UserProvider {
	public function retrieveById($identifier);
	public function retrieveByToken($identifier, $token);
	public function updateRememberToken(Authenticatable $user, $token);
	public function retrieveByCredentials(array $credentials);
	public function validateCredentials(Authenticatable $user,array $credentials);
}
//retrieveById 函数通常接收代表用户的键，例如 MySQL 数据库中自增的 ID。应该通过该方法检索和返回与 ID 匹配的Authenticatable 的实现实例。

//retrieveByToken 函数通过其唯一的 $identifier 来检索用户，并将「记住我」 $token 存储在 remember_token 字段中。与以前的方法一样，应该返回 Authenticatable 实现的实例

//updateRememberToken 方法使用新的 $token 更新了 $user 的 remember_token 字段。当使用「记住我」尝试登录成功时，或用户登出时，这个新的令牌可以是全新的。

//在尝试登录应用程序时，retrieveByCredentials 方法接收传递给 Auth::attempt 方法的凭据数组。然后该方法将「查询」底层持久存储匹配用户的这些凭据。通常，此方法会在 $credentials['username'] 上运行「where」条件的查询。这个方法应该需要返回 Authenticatable 的实现的实例。此方法不应该尝试任何密码验证或认证。

//validateCredentials 方法将给定的 $user 和 $credentials 进行匹配，以此来验证用户。例如，这个方法可以使用 Hash::check 比较 $user->getAuthPassword() 的值及 $credentials['password'] 的值。此方法通过返回 true 或 false 来显示密码是否有效。


认证契约#
现在我们已经探讨了 UserProvider 中的每个方法，让我们来看看 Authenticatable 契约
//记住，提供器需要从 retrieveById 和 retrieveByCredentials 方法来返回这个接口的实现
<?php

namespace Illuminate\Contracts\Auth;
interface Authenticatable {
	public function getAuthIdentifierName();
    public function getAuthIdentifier();
    public function getAuthPassword();
    public function getRememberToken();
    public function setRememberToken($value);
    public function getRememberTokenName();
}
//getAuthIdentifierName 方法返回用户的「主键」字段的名称
//getAuthIdentifier 方法返回用户的「主键」 //重申一次，在 MySQL 后台，这个主键是指自增的主键
//getAuthPassword 应该要返回用户的散列密码。这个接口允许认证系统和任何用户类一起工作，不用管你在使用什么 ORM 或存储抽象层



事件#
//Laravel 在认证过程中引发了各种各样的 事件。你可以在 EventServiceProvider 中对这些事件做监听：
/**
 * 应用程序的事件监听器映射。
 *
 * @var array
 */
protected $listen = [
    'Illuminate\Auth\Events\Registered' => [
        'App\Listeners\LogRegisteredUser',
    ],

    'Illuminate\Auth\Events\Attempting' => [
        'App\Listeners\LogAuthenticationAttempt',
    ],

    'Illuminate\Auth\Events\Authenticated' => [
        'App\Listeners\LogAuthenticated',
    ],

    'Illuminate\Auth\Events\Login' => [
        'App\Listeners\LogSuccessfulLogin',
    ],

    'Illuminate\Auth\Events\Failed' => [
        'App\Listeners\LogFailedLogin',
    ],

    'Illuminate\Auth\Events\Logout' => [
        'App\Listeners\LogSuccessfulLogout',
    ],

    'Illuminate\Auth\Events\Lockout' => [
        'App\Listeners\LogLockout',
    ],
];