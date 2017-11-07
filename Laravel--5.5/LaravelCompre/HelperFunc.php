<?php

Laravel 的辅助函数列表#
简介#

可用方法#

数组#

array_add()#
//如果给定的健不在数组中，那么 array_add 函数将会把给定健值对添加到数组中：
$array = array_add(['name' => 'Desk'], 'price', 100);
// ['name' => 'Desk', 'price' => 100]


array_collapse()#
//array_collapse 函数把数组中的每一个数组合并成单个数组：
$array = array_collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
// [1, 2, 3, 4, 5, 6, 7, 8, 9]

array_divide()#
//array_divide 函数返回两个数组，一个包含原始数组的健，另一个包含原始数组的值：
list($keys, $values) = array_divide(['name' => 'Desk']);
// $keys: ['name']
// $values: ['Desk']

array_dot()#
//array_dot 函数将多维数组平坦化为使用「点」符号表示深度的一维数组：
$array = array_dot(['foo' => ['bar' => 'baz']]);
// ['foo.bar' => 'baz'];

array_except()#
//array_except 函数从数组中删除指定的健值对：
$array = ['name' => 'Desk', 'price' => 100];
$array = array_except($array, ['price']);
// ['name' => 'Desk']


array_first()#
//array_first 函数返回数组中第一个通过指定测试的元素：
$array = [100, 200, 300];
$value = array_first($array, function ($value, $key) {
    return $value >= 150;
});
// 200

也可以将默认值作为第三个参数传递给方法。如果没有值通过测试，则返回默认值：
$value = array_first($array, $callback, $default);

array_flatten()#
//array_flatten 函数将多维数组平坦化为一维数组。
$array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];
$array = array_flatten($array);
// ['Joe', 'PHP', 'Ruby'];

array_forget()#
//array_forget 函数使用「点」表示法从一个深度嵌套的数组中删除给定的健值对：
$array = ['products' => ['desk' => ['price' => 100]]];
array_forget($array, 'products.desk');
// ['products' => []]


array_get()#
//array_get 函数使用「点」符号从深度嵌套的数组中检索一个值：
$array = ['products' => ['desk' => ['price' => 100]]];
$value = array_get($array, 'products.desk');
// ['price' => 100]

array_get 函数也接受一个默认值，如果没有找到指定的健，则返回默认值：
$value = array_get($array, 'names.john', 'default');

array_has()#
//array_has 使用「点」表示法检查数组中是否存在指定的项目：
$array = ['product' => ['name' => 'desk', 'price' => 100]];
$hasItem = array_has($array, 'product.name');

// true

$hasItems = array_has($array, ['product.price', 'product.discount']);
// false

array_last()#
//array_last 函数返回数组中最后一个通过指定测试的元素：
$array = [100, 200, 300, 110];
$value = array_last($array, function ($value, $key) {
    return $value >= 150;
});
// 300

array_only()#
//array_only 函数只返回给定的数组中指定的健值对：
$array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
$array = array_only($array, ['name', 'price']);
// ['name' => 'Desk', 'price' => 100]


array_pluck()#
array_pluck 函数将从数组中提取出一列给定的健值对：
$array = [
    ['developer' => ['id' => 1, 'name' => 'Taylor']],
    ['developer' => ['id' => 2, 'name' => 'Abigail']],
];
$array = array_pluck($array, 'developer.name');
// ['Taylor', 'Abigail'];

你也可以指定生成的列表的想要的健是什么：
$array = array_pluck($array, 'developer.name', 'developer.id');
// [1 => 'Taylor', 2 => 'Abigail'];


array_prepend()#
array_prepend 函数将一个项目推到数组的开始位置：
$array = ['one', 'two', 'three', 'four'];
$array = array_prepend($array, 'zero');
// $array: ['zero', 'one', 'two', 'three', 'four']

array_pull()#
array_pull 函数从数组移除指定键值对并返回该键值对：
$array = ['name' => 'Desk', 'price' => 100];
$name = array_pull($array, 'name');
// $name: Desk
// $array: ['price' => 100]


array_set()#
array_set 函数使用「点」表示法在深度嵌套的数组中设置一个值：
$array = ['products' => ['desk' => ['price' => 100]]];
array_set($array, 'products.desk.price', 200);
// ['products' => ['desk' => ['price' => 200]]]


array_sort()#
array_sort 函数根据给定的闭包的结果对数组进行排序：
$array = [
    ['name' => 'Desk'],
    ['name' => 'Chair'],
];

$array = array_values(array_sort($array, function ($value) {
    return $value['name'];
}));

/*
    [
        ['name' => 'Chair'],
        ['name' => 'Desk'],
    ]
*/


array_sort_recursive()#
array_sort_recursive 使用 sort 函数递归排序数组：
$array = [
    [
        'Roman',
        'Taylor',
        'Li',
    ],
    [
        'PHP',
        'Ruby',
        'JavaScript',
    ],
];

$array = array_sort_recursive($array);

/*
    [
        [
            'Li',
            'Roman',
            'Taylor',
        ],
        [
            'JavaScript',
            'PHP',
            'Ruby',
        ]
    ];
*/


array_where()#
array_where 函数使用给定的闭包过滤数组：
$array = [100, '200', 300, '400', 500];
$array = array_where($array, function ($value, $key) {
    return is_string($value);
});

// [1 => 200, 3 => 400]


array_wrap()#
array_wrap 函数将给定的值包装成一个数组。如果给定的值已经是一个数组，则不会被改变：
$string = 'Laravel';
$array = array_wrap($string);
// [0 => 'Laravel']


head()#
head 函数返回给定数组中的第一个元素：
$array = [100, 200, 300];
$first = head($array);
// 100

last()#
last 函数返回给定数组中的最后一个元素：
$array = [100, 200, 300];
$last = last($array);
// 300




路径#
app_path()#
app_path 返回 app 目录的完整路径。你还可以使用 app_path 函数来生成相对于 app 目录的文件完整路径：
$path = app_path();
$path = app_path('Http/Controllers/Controller.php');



base_path()#
base_path 函数返回项目根目录的完整路径。你还可以使用 base_path 函数生成指定文件相对于项目根目录的完整路径：
$path = base_path();
$path = base_path('vendor/bin');


config_path()#
config_path 函数返回应用程序配置目录的完整路径：
$path = config_path();


database_path()#
database_path 函数返回应用程序数据库目录的完整路径：
$path = database_path();

mix()#
mix 函数获取 版本化 Mix 文件 文件的路径
mix($file);


public_path()#
public_path 函数返回 public 目录的完整路径：
$path = public_path();


resource_path()#
resource_path 函数返回 resources 目录的完整路径。你还可以使用 resource_path 函数来生成相对于资源目录的指定文件的完整路径：
$path = resource_path();
$path = resource_path('assets/sass/app.scss');


storage_path()#
storage_path 函数返回 storage 目录的完整路径。你还可以使用 storage_path 来生成相对于储存目录的指定文件的完整路径：
$path = storage_path();
$path = storage_path('app/file.txt');




字符串#
camel_case()#
camel_case 函数将给定的值符传转换为 驼峰命名：
$camel = camel_case('foo_bar');
// fooBar


class_basename()#
class_basename 返回给定类删除命名空间的类名：
$class = class_basename('Foo\Bar\Baz');
// Baz


e()#
e 函数使用 PHP 函数 htmlspecialchars 并且 double_encode 选项设置为 false：
echo e('<html>foo</html>');
// &lt;html&gt;foo&lt;/html&gt;



ends_with()#
ends_with 函数判断给定的字符串结尾是否是指定的内容：
$value = ends_with('This is my name', 'name');
// true


kebab_case()#
kebab_case 函数将给定的字符串转换为 短横线隔开式：
$value = kebab_case('fooBar');
// foo-bar

snake_case()#
snake_case 函数将给定的字符串转换为 蛇形命名：
$snake = snake_case('fooBar');
// foo_bar


str_limit()#
str_limit 函数限制字符串的字符数。该函数第一个参数接受一个字符串，第二个参数作为允许的最大字符数。
$value = str_limit('The PHP framework for web artisans.', 7);
// The PHP...


starts_with()#
starts_with 函数判断给定的字符串的开头是否是指定值：
$value = starts_with('This is my name', 'This');
// true


str_after()#
str_after 函数返回字符串中指定值之后的所有内容：
$value = str_after('This is: a test', 'This is:');
// ' a test'


str_before()#
str_before 函数返回字符串指定值之前的所有内容：
$value = str_before('Test :it before', ':it before');
// 'Test '

str_contains()#
str_contains 函数判断字符串是否包含指定的值：
$value = str_contains('This is my name', 'my');
// true


你还可以传递一个值的数组，来判断字符串是否包任意指定内容：
$value = str_contains('This is my name', ['my', 'foo']);
// true

str_finish()#
str_finish 函数添加一个如果没有以指定值结尾的字符串后面：
$string = str_finish('this/string', '/');
$string2 = str_finish('this/string/', '/');
// this/string/


str_is()#
str_is 函数判断指定的字符串是否匹配指定的格式。星号可以作为通配符使用：
$value = str_is('foo*', 'foobar');
// true

$value = str_is('baz*', 'foobar');
// false


str_plural()#
str_plural 函数将字符串转换为复数形式。这个函数目前仅支持英文：
$plural = str_plural('car');
// cars

$plural = str_plural('child');
// children

你可以给函数的第二个参数传递一个整数，来检索字符串的单数形式或者复数形式：
$plural = str_plural('child', 2);
// children

$plural = str_plural('child', 1);
// child

str_random()#
str_random 函数生成一个指定长度的随机字符串。这个函数数用 PHP 的 random_bytes 函数：
$string = str_random(40);


str_singular()#
str_singular 函数将字符串转换为单数形式。这个函数目前仅支持英文：
$singular = str_singular('cars');
// car


str_slug()#
str_slug 函数根据给定的字符串生成一个友好的「slug」URL：
$title = str_slug('Laravel 5 Framework', '-');
// laravel-5-framework


studly_case()#
studly_case 函数将给定的字符串转换为 首字母大写：
$value = studly_case('foo_bar');
// FooBar


title_case()#
title_case 函数将给定的字符串转换为 每个单词首字母大写;
$title = title_case('a nice title uses the correct case');
// A Nice Title Uses The Correct Case


trans()#
trans 函数使用你的 本地化文件 来翻译给定的语句：
echo trans('validation.required'):


trans_choice()#
trans_choice 函数根据给定数量来决定翻译指定语句是复数形式还是单数形式：
$value = trans_choice('foo.bar', $count);




URLs#
action()#
action 函数为指定的控制器动作生成一个 URL。你不需要传递完整的控制器命名空间。只需要传递相对于 App\Http\Controllers 的命名空间：
$url = action('HomeController@getIndex');

如果该方法接受路由参数，你可以使用第二个参数传递：
$url = action('UserController@profile', ['id' => 1]);



asset()#
使用当前请求的协议（ HTTP 或 HTTPS ）为资源文件生成一个 URL：
$url = asset('img/photo.jpg');


secure_asset()#
使用 HTTPS 协议生成资源文件的 URL:
echo secure_asset('foo/bar.zip', $title, $attributes = []);


route()#
route 函数为给定的命名路由生成一个 URL：
$url = route('routeName');


如果路由接受参数，则可以使用第二个参数传递给方法：
$url = route('routeName', ['id' => 1]);


默认情况下，route 函数生成的是绝对 URL。如果你想生成一个相对 URL，你可以第三个值传递 false：
$url = route('routeName', ['id' => 1], false);



secure_url()#
secure_url 函数为给定的路径生成一个完整的 HTTPS URL 路径：
echo secure_url('user/profile');
echo secure_url('user/profile', [1]);



url()#
url 函数生成给定的路径的完整 URL：
echo url('user/profile');
echo url('user/profile', [1]);


如果没有提供路径，则返回 Illuminate\Routing\UrlGenerator 实例：
echo url()->current();
echo url()->full();
echo url()->previous();



其他#
abort()#
abort 函数将会跑出一个 HTTP 异常并且由异常处理程序处理：
abort(401);

你还可以提供异常的响应文本：
abort(401, 'Unauthorized.');

abort_if()#
如果给定的布尔值为 true 则 abort_if 函数将抛出一个 HTTP 异常：
abort_if(! Auth::user()->isAdmin(), 403);


abort_unless()#
如果给定的布尔值为 false 则 abort_unless 函数将抛出一个 HTTP 异常：
abort_unless(Auth::user()->isAdmin(), 403);


auth()#
为例方便起见 auth 函数返回一个认证实例。你可以使用它来替代 Auth facade：
$user = auth()->user();


back()#
back() 函数会生成用户之前位置的一个重定向响应：
return back();


bcrypt()#
bcrypt 使用 Bcrypt 对给定的值进行散列。你可以使用它替代 Hash facade：
$password = bcrypt('my-secret-password');


cache()#
cache 函数可以用来从缓存中获取值。如果缓存中不存在给定的健，则返回默认值：
$value = cache('key');
$value = cache('key', 'default');


你可以通过健值对的数组来添加项目到缓冲中。你还应该传递一个以分钟为单位缓存过期时间：
cache(['key' => 'value'], 5);
cache(['key' => 'value'], Carbon::now()->addSeconds(10));


collect()
collect 函数根据给定的数组创建一个 集合 实例：
$collection = collect(['taylor', 'abigail']);


config()#
config 函数用来获取配置信息的值，可以使用「点」语法访问配置值，其中要包含文件名和选项名。可以指定一个默认值，如果选项不存在则返回默认值：
$value = config('app.timezone');
$value = config('app.timezone', $default);


config 辅助函数也可以通过传递一个健值对数组在运行的时候配置信息：
config(['app.debug' => true]);


csrf_field()#
csrf_field 函数生成包含 CSRF 令牌值的 HTML hidden 表单字段。例如，使用 Blade语法：
{{ csrf_field() }}


csrf_token()#
csrf_token 函数获取当前 CSRF 令牌的值：
$token = csrf_token();


dd()#
dd 函数输出给定的值并结束脚本运行：
dd($value);
dd($value1, $value2, $value3, ...);


如果你不想终止脚本运行，请改用 dump 函数：
dump($value);


dispatch()#
dispatch 函数将一个新的任务推送到 Laravel 任务列队
dispatch(new App\Jobs\SendEmails);



env()#
env 函数获取环境变量的值或者返回默认值：
$env = env('APP_ENV');
// 如果环境变量不存在则返回默认值...
$env = env('APP_ENV', 'production');


event()#
event 函数将给定的 事件 派发到所属侦听器：
event(new UserRegistered($user));


factory()#
factory 函数根据给定的类、名称和数量创建一个模型工厂构建器。可以在 测试 or 数据填充 中使用：
$user = factory(App\User::class)->make();


info()#
info 函数将信息写入日志：
info('Some helpful information!');


上下文数据的数组也可以传递给函数：
info('User login attempt failed.', ['id' => $user->id]);


logger()#
logger 函数可以将一个 debug 级别的消息写入到日志中：
logger('Debug message');


上下文数据的数组也可以传递给函数：
logger('User has logged in.', ['id' => $user->id]);

如果没有传值给函数则返回 日志 的实例：
logger()->error('You are not allowed here.');



method_field()#
method_field 函数生成一个模拟 HTTP 动作的 HTML hidden 表单字段。例如，使用 Blade 语法：
<form method="POST">
    {{ method_field('DELETE') }}
</form>


old()#
old 函数 获取 一个旧的 session 闪存输入值：
$value = old('value');
$value = old('value', 'default');


redirect()#
redirect 函数返回一个重定向 HTTP 响应，如果没有没有传入参数，则返回重定向实例：
return redirect('/home');
return redirect()->route('route.name');


report()#
report 函数将使用异常处理程序的 report 方法抛出异常：
report($e);


request()#
request 函数返回当前 请求 实例或者获取输入项：
$request = request();
$value = request('key', $default = null)

retry()#
retry 函数尝试执行给定的回调，直到到达给定的最大尝试次数。如果回调没有派出异常并且有返回值则返回返回值。如果回调抛出异常，它将自动重试。如果超过最大尝试次数，则抛出异常。
return retry(5, function () {
    // 在 100ms 左右尝试 5 次... 
}, 100);



session()#
session 函数可以用来获取或者设置 Session 值：
$value = session('key');


你可以通过健值对数组传递给函数来设置 Session 值：
session(['chairs' => 7, 'instruments' => 3]);

session(['chairs' => 7, 'instruments' => 3]);
如果没有传递值给函数，则返回 Session 实例：
$value = session()->get('key');
session()->put('key', $value);


tap()#
tap 函数接受两个参数：$value 和一个闭包。传入的 $value 将会作为闭包函数的传参，处理完后成为 tap 的返回值。闭包的返回值是无关紧要（不需要 return 关键词）。
$user = tap(User::first(), function ($user) {
    $user->name = 'taylor';

    $user->save();
});


如果没有传递闭包给 tap 函数，你可以调用给定 $value 上任何方法。不管方法中定义的实际返回值是什么，你调用的方法返回值始终 $value
例如，Eloquent update 一般返回一个整数。而我们可以通过 tap 函数链式调用 update 的方式返回模型本身：
$user = tap($user)->update([
    'name' => $name,
    'email' => $email
]);


value()#
value 函数可以简单的返回它的值。然而，如果将 闭包 传递给函数，则运行这个 闭包 并返回结果：
$value = value(function () {
    return 'bar';
});


view()#
view 函数获取一个 视图 实例：
return view('auth.login');