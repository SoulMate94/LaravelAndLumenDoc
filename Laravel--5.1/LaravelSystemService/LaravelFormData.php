<?php

表单验证#
简介#
//默认情况下，Laravel 的基底控制器类使用了 ValidatesRequests trait，其提供了一种便利的方法来使用各种强大的验证规则验证传入的 HTTP 请求

验证快速上手#
定义路由#
//首先，我们假设在 app/Http/routes.php 文件中定义了以下路由：
// 显示一个创建博客文章的表单...
Route::get('post/create', 'PostController@create');

// 保存一个新的博客文章...
Route::post('post', 'PostController@store');
//GET 路由会显示一个用于创建新博客文章的表单，POST 路由则会将新的博客文章保存到数据库。

创建控制器#
//接下来，让我们来看下操作这些路由的控制器。我们先让 store 方法空着
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * 显示创建博客文章的表单。
     *
     * @return Response
     */
    public function create()
    {
        return view('post.create');
    }

    /**
     * 保存一个新的博客文章。
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        // 验证以及保存博客发表文章...
    }
}

编写验证逻辑#
//检查应用程序的基底控制器 (App\Http\Controllers\Controller) 类你会看到这个类使用了 ValidatesRequests trait。这个 trait 在你所有的控制器里提供了方便的 validate 验证方法。
//validate 方法会接收 HTTP 传入的请求以及验证的规则
//如果验证通过，你的代码就可以正常的运行。若验证失败，则会抛出异常错误消息并自动将其返回给用户
//在一般的 HTTP 请求下，都会生成一个重定向响应，对于 AJAX 请求则会发送 JSON 响应。
/**
 * 保存一篇新的博客文章。
 *
 * @param  Request  $request
 * @return Response
 */
public function store(Request $request)
{
    $this->validate($request, [
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ]);

    // 博客文章成功发表，将其保存到数据库...
}
//如你所见，我们将本次 HTTP 请求及所需的验证规则传递至 validate 方法中。另外再提醒一次，如果验证失败，将会自动生成一个对应的响应。如果验证通过，那我们的控制器将会继续正常运行

对于嵌套属性的提醒#
//如果你的 HTTP 请求中包含了「嵌套」参数，则可以在验证规则中使用「点」语法来指定他们：
$this->validate($request, [
    'title' => 'required|unique:posts|max:255',
    'author.name' => 'required',
    'author.description' => 'required',
]);

显示验证错误#
//如果本次请求的参数未通过我们指定的验证规则呢？正如前面所提到的，Laravel 会自动把用户重定向到先前的位置。另外，所有的验证错误会被自动 闪存至 session。
//请注意我们并不需要在 GET 路由中明确的将错误消息绑定到视图上。这是因为 Laravel 会自动检查 session 内的错误数据，如果错误存在的话，它会自动将这些错误消息绑定到视图上
//因此需要的注意一点是 $errors 变量在每次请求的所有视图中都可以被使用，你可以很方便的假设 $errors 变量已被定义且进行安全地使用。$errors 变量是 Illuminate\Support\MessageBag 的实例
<!-- /resources/views/post/create.blade.php -->

<h1>创建文章</h1>

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- 创建文章的表单 -->

自定义闪存的错误消息格式#
//当验证失败时，如果你想要在闪存上自定义验证的错误格式，则需在控制器中重写 formatValidationErrors
//别忘了将 Illuminate\Contracts\Validation\Validator 类引入到文件上方：
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    /**
     * {@inheritdoc}
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return $validator->errors()->all();
    }
}



AJAX 请求和验证#
当我们在 AJAX 的请求中使用 validate 方法时，Laravel 并不会生成一个重定向响应，而是会生成一个包含所有错误验证的 JSON 响应。这个 JSON 响应会发送一个 422 HTTP 状态码。



其它验证的处理#
手动创建验证程序#
//如果你不想要使用 ValidatesRequests trait 的 validate 方法，你可以手动创建一个 validator 实例并通过 Validator::make 方法在 facade 生成一个新的 validator 实例：
<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    /**
     * 保存一篇新的博客文章。
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();
        }

        // 保存文章...
    }
}
第一个传给 make 方法的参数是验证数据。第二个参数则是数据的验证规则
//如果请求没有通过验证，则可以使用 withErrors 方法把错误消息闪存到 session。在进行重定向之后，$errors 变量可以在视图中自动共用，让你可以轻松地显示这些消息并返回给用户。withErrors 方法接收 validator、MessageBag，或 PHP array。


命名错误清单#
//假如在一个页面中有许多表单，你可能希望为 MessageBag 的错误命名，这可以让你获取特定表单的所有错误消息。只需在 withErrors 的第二个参数设置名称即可：
return redirect('register')
            ->withErrors($validator, 'login');
//然后你就可以从一个 $errors 变量中，获取已命名的 MessageBag 实例：
{{ $errors->login->first('email') }}


验证后的挂勾#
//在验证完成之后，validator 可以让你附加返回消息
//你可以更简单的做进一步的验证以及增加更多的错误消息到消息集合上。在 validator 实例使用 after 方法如下所示：
$validator = Validator::make(...);

$validator->after(function($validator) {
    if ($this->somethingElseIsInvalid()) {
        $validator->errors()->add('field', 'Something is wrong with this field!');
    }
});

if ($validator->fails()) {
    //
}

表单请求验证#
//在更复杂的验证情境中，你可能会想要创建一个「表单请求（ form request ）」。表单请求是一个自定义的请求类，里面包含着验证逻辑。要创建一个表单请求类，可使用 Artisan 命令行命令 make:request ：
php artisan make:request StoreBlogPostRequest

//新生成的类文件会被放在 app/Http/Requests 目录下。让我们将一些验证规则加入到 rules 方法中：
/**
 * 获取适用于请求的验证规则。
 *
 * @return array
 */
public function rules()
{
    return [
        'title' => 'required|unique:posts|max:255',
        'body' => 'required',
    ];
}
//怎样才能较好的运行验证规则呢？你所需要做的就是在控制器方法中利用类型提示传入请求。传入的请求会在控制器方法被调用前进行验证，意思就是说你不会因为验证逻辑而把控制器弄得一团糟：
/**
 * 保存传入的博客文章。
 *
 * @param  StoreBlogPostRequest  $request
 * @return Response
 */
public function store(StoreBlogPostRequest $request)
{
    // 传入的请求是有效的...
}
//如果验证失败，就会生成一个重定向响应把用户返回到先前的位置
//这些错误会被闪存到 session，所以这些错误都可以被显示。如果进来的是 AJAX 请求的话，则会传回一个 HTTP 响应，其中包含了 422 状态码和验证错误的 JSON 数据

授权表单请求#
//表单的请求类内包含了 authorize 方法。在这个方法中，你可以确认用户是否真的通过了授权，以便更新指定数据
//比方说，有一个用户想试图去更新一篇文章的评论，你能保证他确实是这篇评论的拥有者吗？具体代码如下
/**
 * 判断用户是否有权限做出此请求。
 *
 * @return bool
 */
public function authorize()
{
    $commentId = $this->route('comment');

    return Comment::where('id', $commentId)
                  ->where('user_id', Auth::id())->exists();
}

//请注意，在上面例子中调用 route 方法。该方法可以帮助你获取路由被调用时传入的 URI 参数，如示例中的 {comment} 参数：
Route::post('comment/{comment}');

//如果 authorize 方法返回 false，则会自动返回一个 HTTP 响应，其中包含 403 状态码，而你的控制器方法也将不会被运行。

//如果你打算在应用程序的其它部分处理授权逻辑，只需从 authorize 方法返回 true 
/**
 * 判断用户是否有权限做出此请求。
 *
 * @return bool
 */
public function authorize()
{
    return true;
}

自定义闪存的错误消息格式#
//如果你想要自定义验证失败时闪存到 session 的验证错误格式，可在你的基底请求 (App\Http\Requests\Request) 中重写 formatErrors。别忘了文件上方引入 Illuminate\Contracts\Validation\Validator 类：
/**
 * {@inheritdoc}
 */
protected function formatErrors(Validator $validator)
{
    return $validator->errors()->all();


自定义错误消息#
//你可以通过重写表单请求的 messages 方法来自定义错误消息。此方法必须返回一个数组，其中含有成对的属性或规则以及对应的错误消息：
/**
 * 获取已定义验证规则的错误消息。
 *
 * @return array
 */
public function messages()
{
    return [
        'title.required' => '标题是必填的',
        'body.required'  => '消息是必填的',
    ];
}


处理错误消息#
//调用一个 Validator 实例的 errors 方法，会得到一个 Illuminate\Support\MessageBag 的实例，里面有许多可让你操作错误消息的便利方法。

查看特定字段的第一个错误消息#
$messages = $validator->errors();

echo $messages->first('email');


查看特定字段的所有错误消息#
foreach ($messages->get('email') as $message) {
    //
}

查看所有字段的所有错误消息#
foreach ($messages->all() as $message) {
    //
}

判断特定字段是否含有错误消息#
if ($messages->has('email')) {
    //
}

获取格式化后的错误消息#
echo $messages->first('email', '<p>:message</p>');

获取所有格式化后的错误消息#
foreach ($messages->all('<li>:message</li>') as $message) {
    //
}

自定义错误消息#
//如果有需要的话，你也可以自定义错误的验证消息来取代默认的验证消息。有几种方法可以来自定义指定的消息。首先，你需要先通过传递三个参数到 Validator::make 方法来自定义验证消息：
$messages = [
    'required' => ':attribute 的字段是必要的。',
];

$validator = Validator::make($input, $rules, $messages);

//在这个例子中，:attribute 占位符会被通过验证的字段实际名称所取代。除此之外，你还可以使用其它默认字段的验证消息。例如：
$messages = [
    'same'    => ':attribute 和 :other 必须相同。',
    'size'    => ':attribute 必须是 :size。',
    'between' => ':attribute 必须介于 :min - :max。',
    'in'      => ':attribute 必须是以下的类型之一： :values。',
];

指定自定义消息到特定的属性#
//有时候你可能想要对特定的字段来自定义错误消息。只需在属性名称后加上「.」符号和指定验证的规则即可
$messages = [
    'email.required' => '我们需要知道你的 e-mail 地址！',
];

在语言包中自定义指定消息#
//在许多情况下，你可能希望在语言包中被指定的特定属性自定义消息不被直接传到 Validator 上。因此你可以把消息加入到 resources/lang/xx/validation.php 语言包中的 custom 数组
'custom' => [
    'email' => [
        'required' => '我们需要知道你的 e-mail 地址！',
    ],
],

可用的验证规则#
accepted#
//验证字段值是否为 yes、on、1、或 true。这在确认「服务条款」是否同意时相当有用。

active_url#
//验证字段值是否为一个有效的网址，会通过 PHP 的 checkdnsrr 函数来验证。

after:date#
//验证字段是否是在指定日期之后。这个日期将会通过 strtotime 函数来验证。
'start_date' => 'required|date|after:tomorrow'
//作为替换 strtotime 传递的日期字符串，你可以指定其它的字段来比较日期：
'finish_date' => 'required|date|after:start_date'

alpha#
//验证字段值是否仅包含字母字符。

alpha_dash#
//验证字段值是否仅包含字母、数字、破折号（ - ）以及下划线（ _ ）。

alpha_num#
//验证字段值是否仅包含字母、数字。

array#
//验证字段必须是一个 PHP array。

before:date#
//验证字段是否是在指定日期之前。这个日期将会使用 PHP strtotime 函数来验证。

between:min,max#
//验证字段值的大小是否介于指定的 min 和 max 之间。字符串、数值或是文件大小的计算方式和 size 规则相同。

boolean#
//验证字段值是否能够转换为布尔值。可接受的参数为 true、false、1、0、"1" 以及 "0"。

confirmed#
//验证字段值必须和 foo_confirmation 的字段值一致。例如，如果要验证的字段是 password，就必须和输入数据里的 password_confirmation 的值保持一致。

date#
//验证字段值是否为有效日期，会根据 PHP 的 strtotime 函数来做验证。

date_format:format#
//验证字段值符合定义的日期格式，通过 PHP 的 date_parse_from_format 函数来验证。

different:field#
//验证字段值是否和指定_字段（ field ）_不同。

digits:value#
//验证字段值是否为 numeric 且长度为 value。

digits_between:min,max#
//验证字段值的长度是否在 min 和 max 之间。

email#
//验证字段值是否符合 e-mail 格式。

exists:table,column#
//验证字段值是否存在指定的数据表中。

Exists 规则的基本使用方法#
'state' => 'exists:states'

指定一个特定的字段名称#
'state' => 'exists:states,abbreviation'

也可以指定更多的条件，它们会被加到「where」查询语句中：
'email' => 'exists:staff,email,account_id,1'

你也可以传递 NULL 或 NOT_NULL 至「where」语句：
'email' => 'exists:staff,email,deleted_at,NULL'

'email' => 'exists:staff,email,deleted_at,NOT_NULL'


image#
//验证字段文件必须为图片格式（ jpeg、png、bmp、gif、 或 svg ）。

in:foo,bar,...#
//验证字段值是否有在指定的列表里面。

integer#
//验证字段值是否是整数。

ip#
//验证字段值是否符合 IP address 的格式。


json#
//验证字段是否是一个有效的 JSON 字符串。


max:value#
//字段值必须小于或等于 value 。字符串、数值或是文件大小的计算方式和 size 规则相同。


mimes:foo,bar,...#
//验证字段文件的 MIME 类型是否符合列表中指定的格式。


MIME 规则基本用法#
'photo' => 'mimes:jpeg,bmp,png'
//即使你可能只需要验证指定扩展名，但此规则实际上会验证文件的 MIME 类型，其通过读取文件的内容以猜测它的 MIME 类型。
//完整的 MIME 类型及对应的扩展名列表可以在下方链接找到：
#http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types

min:value#
//字段值必须大于或等于 value。字符串、数值或是文件大小的计算方式和 size 规则相同。


not_in:foo,bar,...#
//验证字段值是否不在指定的列表里。


numeric#
//验证字段值是否为数值。

regex:pattern#
//验证字段值是否符合指定的正则表达式。
//注意：当使用 regex 规则时，你必须使用数组，而不是使用管道分隔规则，特别是当正则表达式含有管道符号时。

required#
//验证字段必须存在输入数据，且不为空。字段符合下方任一条件时即为「空」：
	该值为 null。
	该值为空字符串。
	该值为空数组或空的可数对象。
	该值为没有路径的上传文件。

required_if:anotherfield,value,...#
//如果指定的其它字段（ anotherfield ）等于任何一个 value 时，此字段为必填。

required_unless:anotherfield,value,...#
//如果指定的其它字段（ anotherfield ）等于任何一个 value 时，则此字段为不必填。

required_with:foo,bar,...#
//如果指定的字段中的 任意一个 有值，则此字段为必填。

required_with_all:foo,bar,...#
//如果指定的 所有 字段都有值，则此字段为必填。

required_without:foo,bar,...#
//如果缺少任意一个指定的字段，则此字段为必填。

required_without_all:foo,bar,...#
//如果所有指定的字段都没有值，则此字段为必填。

same:field#
//验证字段值和指定的 字段（ field ） 值是否相同。

size:value#
//验证字段值的大小是否符合指定的 value 值。对于字符串来说，value 为字符数。对于数字来说，value 为某个整数值。对文件来说，size 对应的是文件大小（单位 kb ）。

string#
//验证字段值的类型是否为字符串。

timezone#
//验证字段值是否是有效的时区，会根据 PHP 的 timezone_identifiers_list 函数来判断。

unique:table,column,except,idColumn#
//在指定的数据表中，验证字段必须是唯一的。如果没有指定 column，将会使用字段本身的名称。
//指定一个特定的字段名称：
'email' => 'unique:users,email_address'

自定义数据库连接
//有时候你可能需要自定义一个连接，来通过 Validator 对数据库进行查找。如上面所示，设置 unique:users 作为验证规则，通过默认数据库连接来做数据库查找。如果要重写验证规则，可在指定的连接的表单名称后面加上「.」：
'email' => 'unique:connection.users,email_address'


强迫 Unique 规则忽略指定 ID：
//有时候，你希望在验证字段时对指定 ID 进行忽略
'email' => 'unique:users,email_address,'.$user->id
//假设用户提供的 e-mail 已经被其他用户使用，则需要抛出验证错误。若要用指定规则来忽略用户 ID，则应该把要发送的 ID 当作第三个参数：

//如果你的数据表使用的主键名称不是 id，那么你可以在第四个参数中来指定它：
'email' => 'unique:users,email_address,'.$user->id.',user_id'


增加额外的 Where 语句：
//你也可以指定更多的条件到「where」查询语句：
'email' => 'unique:users,email_address,NULL,id,account_id,1'
//上述规则中，只有 account_id 为 1 的数据列会被包含在 unique 规则的验证

url#
//根据 PHP 的 filter_var 函数来验证字段是否符合 URL 格式。



按条件增加规则#
//在某些情况下，你可能只想在输入数据中有此字段时才进行验证。可通过增加 sometimes 规则到规则列表来实现：
$v = Validator::make($data, [
    'email' => 'sometimes|required|email',
]);
//在上面的例子中，email 字段的验证只会在 $data 数组有此字段时才会进行。

复杂的条件验证#
//例如，你可以希望某个指定字段在另一个字段的值超过 100 时才为必填。或者当某个指定字段有值时，另外两个字段要拥有符合的特定值。增加这样的验证条件并不难。首先，利用你熟悉的 static rules 来创建一个 Validator 实例：
$v = Validator::make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);

//假设我们有一个专为游戏收藏家所设计的网页应用程序。如果游戏收藏家收藏超过一百款游戏，我们会希望他们来说明下为什么他们会拥有这么多游戏
$v->sometimes('reason', 'required|max:500', function($input) {
    return $input->games >= 100;
});

//传入 sometimes 方法的第一个参数是我们要用条件认证的字段名称。第二个参数是我们想使用的验证规则。闭包 作为第三个参数传入，如果其返回 true，则额外的规则就会被加入。这个方法可以轻松的创建复杂的条件式验证。你甚至可以一次对多个字段增加条件式验证：
$v->sometimes(['reason', 'cost'], 'required', function($input) {
    return $input->games >= 100;
});

注意：传入闭包的 $input 参数是 Illuminate\Support\Fluent 实例，可用来访问你的输入或文件对象。

自定义验证规则#
//Laravel 提供了许多有用的验证规则。但你可能想自定义一些规则。注册自定义验证规则的方法之一，就是使用 Validator facade 中的 extend 方法，让我们在 服务提供者 中使用这个方法来自定义注册的验证规则：
<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 启动所有应用程序服务。
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('foo', function($attribute, $value, $parameters, $validator) {
            return $value == 'foo';
        });
    }

    /**
     * 注册服务提供者。
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
自定义的验证闭包接收四个参数：要被验证的属性名称 $attribute，属性的值 $value，传入验证规则的参数数组 $parameters，及 Validator 实例。

//除了使用闭包，你也可以传入类和方法到 extend 方法中：
Validator::extend('foo', 'FooValidator@validate');


自定义错误消息#
//这可以通过使用自定义内联消息数组或是在验证语言包中加入新的规则来实现。此消息应该被放在数组的第一级，而不是被放在 custom 数组内，这是仅针对特定属性的错误消息:
"foo" => "你的输入是无效的！",

"accepted" => ":attribute 必须被接受。",

// 其余的验证错误消息...

//当你在创建自定义验证规则时，你可能需要定义保留字段来取代错误消息。你可以像上面所描述的那样通过 Validator facade 来使用 replacer 方法创建一个自定义验证器。通过 服务提供者 中的 boot 方法可以实现
/**
 * 启动所有应用程序服务。
 *
 * @return void
 */
public function boot()
{
    Validator::extend(...);

    Validator::replacer('foo', function($message, $attribute, $rule, $parameters) {
        return str_replace(...);
    });
}


隐式扩展功能#
//默认情况下，若有一个类似 required 这样的规则，当此规则被验证的属性不存在或包含空值时，其一般的验证规则（包括自定扩展功能）都将不会被运行。例如，当 integer 规则的值为 null 时将不会被运行：
$rules = ['count' => 'integer'];

$input = ['count' => null];

Validator::make($input, $rules)->passes(); // true

//如果要在属性为空时依然运行此规则，则此规则必须暗示该属性为必填。要创建一个「隐式」扩展功能，可以使用 Validator::extendImplicit() 方法：
Validator::extendImplicit('foo', function($attribute, $value, $parameters, $validator) {
    return $value == 'foo';
});

//注意：一个「隐式」扩展功能只会 暗示 该属性为必填。它的实际属性是否为无效属性或空属性主要取决于你。
