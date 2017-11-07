<?php

数组#
array_add()#
//如果指定的键不存在于该数组，array_add 函数便会将指定的键值对加到数组中：
$array = array_add(['name' => 'Desk'], 'price', 100);

// ['name' => 'Desk', 'price' => 100]


array_collapse()#
//array_collapse 函数将数组的每一个数组折成单个数组：
$array = array_collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);

// [1, 2, 3, 4, 5, 6, 7, 8, 9]


array_divide()#
//array_divide 函数返回两个数组，一个包含原本数组的键，另一个包含原本数组的值：
list($keys, $values) = array_divide(['name' => 'Desk']);

// $keys: ['name']

// $values: ['Desk']

array_dot()#
//array_dot 函数把多维数组扁平化成一维数组，并用「点」式语法表示深度：
$array = array_dot(['foo' => ['bar' => 'baz']]);

// ['foo.bar' => 'baz'];


array_except()#
//array_except 函数从数组移除指定的键值对：
$array = ['name' => 'Desk', 'price' => 100];

$array = array_except($array, ['price']);

// ['name' => 'Desk']


array_first()#
//array_first 函数返回数组中第一个通过测试的元素：
$array = [100, 200, 300];

$value = array_first($array, function ($key, $value) {
    return $value >= 150;
});

// 200

//可传递第三个参数作为默认值。当没有任何数值通过测试时将返回该数值：
$value = array_first($array, $callback, $default);


array_flatten()#
//array_flatten 函数将多维数组扁平化成一维。
$array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];

$array = array_flatten($array);

// ['Joe', 'PHP', 'Ruby'];


array_forget()#
//array_forget 函数以「点」式语法从深度嵌套数组中移除指定的键值对：
$array = ['products' => ['desk' => ['price' => 100]]];

array_forget($array, 'products.desk');

// ['products' => []]


array_get()#
//array_get 函数使用「点」式语法从深度嵌套数组中取回指定的值：
$array = ['products' => ['desk' => ['price' => 100]]];

$value = array_get($array, 'products.desk');

// ['price' => 100]

//array_get 函数同样接受默认值，当指定的键找不到时返回：
$value = array_get($array, 'names.john', 'default');


array_has()#
//array_has 函数使用「点」式语法检查指定的项目是否存在于数组中：
$array = ['products' => ['desk' => ['price' => 100]]];

$hasDesk = array_has($array, ['products.desk']);

// true


array_only()#
//array_only 函数从数组返回指定的键值对：
$array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];

$array = array_only($array, ['name', 'price']);

// ['name' => 'Desk', 'price' => 100]


array_pluck()#
//array_pluck 函数从数组拉出一列指定的键值对：
$array = [
    ['developer' => ['id' => 1, 'name' => 'Taylor']],
    ['developer' => ['id' => 2, 'name' => 'Abigail']],
];

$array = array_pluck($array, 'developer.name');

// ['Taylor', 'Abigail'];

//你也能指定要以什么作为结果列的键值：
$array = array_pluck($array, 'developer.name', 'developer.id');

// [1 => 'Taylor', 2 => 'Abigail'];


array_pull()#
//array_pull 函数从数组移除并返回指定的键值对：
$array = ['name' => 'Desk', 'price' => 100];

$name = array_pull($array, 'name');

// $name: Desk

// $array: ['price' => 100]


array_set()#
//array_set 函数使用「点」式语法在深度嵌套数组中写入值：
$array = ['products' => ['desk' => ['price' => 100]]];

array_set($array, 'products.desk.price', 200);

// ['products' => ['desk' => ['price' => 200]]]


array_sort()#
//array_sort 函数借助指定闭包结果排序数组：
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
//array_sort_recursive 函数使用 sort 函数递归排序数组：
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
//array_where 函数使用指定的闭包过滤数组：
$array = [100, '200', 300, '400', 500];

$array = array_where($array, function ($key, $value) {
    return is_string($value);
});

// [1 => 200, 3 => 400]

head()#
//head 函数返回指定数组的第一个元素：
$array = [100, 200, 300];

$first = head($array);

// 100


last()#
//last 函数返回指定数组的最后一个元素：
$array = [100, 200, 300];

$last = last($array);

// 300


路径#
app_path()#
//app_path 函数获取 app 文件夹的完整路径：
$path = app_path();
//你同样可以使用 app_path 函数生成针对指定文件相对于 app 目录的完整路径：
$path = app_path('Http/Controllers/Controller.php');


base_path()#
//base_path 函数获取项目根目录的完整路径：
$path = base_path();
//你同样可以使用 base_path 函数生成针对指定文件相对于项目根目录的完整路径：
$path = base_path('vendor/bin');


config_path()#
//config_path 函数获取应用配置目录的完整路径：
$path = config_path();


database_path()#
//database_path 函数获取应用数据库目录的完整路径：
$path = database_path();


elixir()#
//elixir 函数获取加上版本号的 Elixir 文件路径：
elixir($file);


public_path()#
//public_path 函数获取 public 目录的完整路径：
$path = public_path();


storage_path()#
//storage_path 函数获取 storage 目录的完整路径：
$path = storage_path();
//你同样可以使用 storage_path 函数生成针对指定文件相对于 storage 目录的完整路径：
$path = storage_path('app/file.txt');




字符串#
camel_case()#
//camel_case 函数会将指定的字符串转换成 驼峰式命名：
$camel = camel_case('foo_bar');

// fooBar


class_basename()#
//class_basename 返回不包含命名空间的类名称：
$class = class_basename('Foo\Bar\Baz');

// Baz

e()#
//e 函数对指定字符串运行 htmlentities：
echo e('<html>foo</html>');

// &lt;html&gt;foo&lt;/html&gt;


ends_with()#
//ends_with 函数判断指定字符串结尾是否为指定内容：
$value = ends_with('This is my name', 'name');

// true


snake_case()#
//snake_case 函数会将指定的字符串转换成 蛇形命名：
$snake = snake_case('fooBar');

// foo_bar


str_limit()#
//str_limit 函数限制字符串的字符数量。该函数接受一个字符串作为第一个参数，以及最大字符数量作为第二参数：
$value = str_limit('The PHP framework for web artisans.', 7);

// The PHP...


starts_with()#
//starts_with 函数判断字符串开头是否为指定内容：
$value = starts_with('This is my name', 'This');

// true


str_contains()#
//str_contains 函数判断指定字符串是否包含指定内容：
$value = str_contains('This is my name', 'my');

// true



str_finish()#
//str_finish 函数添加指定内容到字符串结尾：
$string = str_finish('this/string', '/');

// this/string/


str_is()#
//str_is 函数判断指定的字符串与指定的格式是否符合。星号可作为通配符使用：
$value = str_is('foo*', 'foobar');

// true

$value = str_is('baz*', 'foobar');

// false


str_plural()#
//str_plural 函数转换字符串成复数形。该函数目前仅支持英文：
$plural = str_plural('car');

// cars

$plural = str_plural('child');

// children


//你可以提供一个整数作为第二参数，来获取字符串的单数或复数形式：
$plural = str_plural('child', 2);

// children

$plural = str_plural('child', 1);

// child



str_random()#
//str_random 函数生成指定长度的随机字符串：
$string = str_random(40);


str_singular()#
//str_singular 函数转换字符串成单数形式。该函数目前仅支持英文：
$singular = str_singular('cars');

// car


str_slug()#
//str_slug 函数从指定字符串生成网址友善的「slug」：
$title = str_slug("Laravel 5 Framework", "-");

// laravel-5-framework


studly_case()#
//studly_case 函数将指定字符串转换成 首字大写命名：
$value = studly_case('foo_bar');

// FooBar


trans()#
//trans 函数根据你的 本地化文件 翻译指定的语句：
echo trans('validation.required'):


trans_choice()#
//trans_choice 函数根据后缀变化翻译指定的语句：
$value = trans_choice('foo.bar', $count);



网址#
action()#
//action 函数生成指定控制器行为网址。你不需要输入该控制器的完整命名空间。作为替代，请输入基于 App\Http\Controllers 命名空间的控制器类名称：
$url = action('HomeController@getIndex');

//如果该方法支持路由参数，你可以作为第二参数传递：
$url = action('UserController@profile', ['id' => 1]);


asset()#
//根据目前请求的协定（HTTP 或 HTTPS）生成资源文件网址：
$url = asset('img/photo.jpg');


secure_asset()#
//根据 HTTPS 生成资源文件网址：
echo secure_asset('foo/bar.zip', $title, $attributes = []);


route()#
//route 函数生成指定路由名称网址：
$url = route('routeName');
//如果该路由接受参数，你可以作为第二参数传递：
$url = route('routeName', ['id' => 1]);


url()#
//url 函数生成指定路径的完整网址：
echo url('user/profile');

echo url('user/profile', [1]);


其它#
auth()#
//auth 函数返回一个认证器实例。你可以使用它取代 Auth facade：
$user = auth()->user();

back()#
//back() 函数生成一个重定向响应让用户回到之前的位置：
return back();

bcrypt()#
//bcrypt 函数使用 Bcrypt 哈希指定的数值。你可以使用它替代 Hash facade：
$password = bcrypt('my-secret-password');


collect()#
//collect 函数从指定的项目生成 集合 实例：
$collection = collect(['taylor', 'abigail']);


config()#
//config 获取设置选项的设置值。设置值可通过「点」式语法读取，其中包含要访问的文件名以及选项名称。可传递一默认值在找不到指定的设置选项时返回该数值：
$value = config('app.timezone');

$value = config('app.timezone', $default);

//config 辅助函数也可以在运行期间，根据指定的键值对指定设置值：
config(['app.debug' => true]);

csrf_field()#
//csrf_field 函数生成包含 CSRF 令牌内容的 HTML 表单隐藏字段。例如，使用 Blade 语法：
{!! csrf_field() !!}


csrf_token()#
//csrf_token 函数获取当前 CSRF 令牌的内容：
$token = csrf_token();

dd()#
//dd 函数输出指定变量并结束脚本运行：
dd($value);


env()#
//env 函数获取环境变量值或返回默认值：
$env = env('APP_ENV');

// 当变量不存在时返回一个默认值...
$env = env('APP_ENV', 'production');


event()#
//event 函数配送指定 事件 到所属的侦听器：
event(new UserRegistered($user));


factory()#
//factory 函数根据指定类、名称以及总数生成模型工厂构造器（model factory builder）。可用于 测试 或 数据填充：
$user = factory(App\User::class)->make();


method_field()#
//method_field 函数生成模拟 HTTP 表单动作内容的 HTML 表单隐藏字段。例如，使用 Blade 语法：
<form method="POST">
    {!! method_field('delete') !!}
</form>


old()#
//old 函数 获取 闪存到 session 的旧有输入数值：
$value = old('value');


redirect()#
//redirect 函数返回重定向器实例以进行 重定向：
return redirect('/home');


request()#
//request 函数获取目前的 请求 实例或输入的项目：
$request = request();

$value = request('key', $default = null)


response()#
//response 函数创建一个 响应 实例或获取一个响应工厂（response factory）实例：
return response('Hello World', 200, $headers);

return response()->json(['foo' => 'bar'], 200, $headers);

session()#
//session 函数可用于获取或设置单个 session 内容：
$value = session('key');
//你可以通过传递键值对给该函数进行内容设置：
session(['chairs' => 7, 'instruments' => 3]);
//该函数在没有传递参数时，将返回 session 实例：
$value = session()->get('key');

session()->put('key', $value);


value()#
//value 函数返回指定数值。而当你传递一个 闭包 给该函数，该 闭包 将被运行并返回结果：
$value = value(function() { return 'bar'; });


view()#
//view 函数获取 视图 实例：
return view('auth.login');

with()#
//with 函数返回指定的数值。该函数主要用于链式调用回所保存的 seesion 内容，除此之外不大可能用到：
$value = with(new Foo)->work();

