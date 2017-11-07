<?php

验证#
简介#
Laravel 提供了几种不同的方法来验证传入应用程序的数据。默认情况下，Laravel 的控制器基类使用 ValidatesRequests Trait，它提供了一种方便的方法使用各种强大的验证规则来验证传入的 HTTP 请求


快速验证#


定义路由#
Route::get('post/create', 'PostController@create');

Route::post('post', 'PostController@store');

GET 路由用来显示一个供用户创建新的博客文章的表单，POST 路由则是会将新的博客文章保存到数据库

创建控制器#
下一步，我们来看一个处理这些路由的控制器。我们将 store 方法置空：
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
        // 验证以及保存博客文章...
    }
}


编写验证逻辑#
让我们接着回到 store 方法来深入理解 validate 方法：
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

    // 文章内容是符合规则的，存入数据库
}

在第一次验证失败后停止#
有时，你希望在某个属性第一次验证失败后停止运行验证规则。为了达到这个目的，附加 bail 规则到该属性：
$this->validate($request, [
    'title' => 'bail|required|unique:posts|max:255',
    'body' => 'required',
]);

在这个例子里，如果 title 字段没有通过 unique，那么不会检查 max 规则。规则会按照分配的顺序来验证

关于数组数据的注意事项#
//如果你的 HTTP 请求包含一个 「嵌套」 参数（即数组），那你可以在验证规则中通过 「点」 语法来指定这些参数。
$this->validate($request, [
    'title' => 'required|unique:posts|max:255',
    'author.name' => 'required',
    'author.description' => 'required',
]);


显示验证错误#
如果传入的请求参数未通过给定的验证规则呢？正如前面所提到的，Laravel 会自动把用户重定向到先前的位置。另外，所有的验证错误信息会被自动 闪存至 session。
// 重申一次，我们不必在 GET 路由中将错误消息显式绑定到视图。因为 Lavarel 会检查在 Session 数据中的错误信息，并自动将其绑定到视图（如果存在）。而其中的变量 $errors 是 Illuminate\Support\MessageBag 的一个实例
!! $errors 变量被由Web中间件组提供的 Illuminate\View\Middleware\ShareErrorsFromSession 中间件绑定到视图
当这个中间件被应用后，在你的视图中就可以获取到 $error 变量，可以使一直假定 $errors 变量存在并且可以安全地使用。

所以，在我们的例子中，当验证失败的时候，用户将会被重定向到控制器的 create 方法，让我们在视图中显示错误信息：
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

<!-- 创建文章表单 -->



可选字段上的注意事项#
默认情况下，Laravel 在你应用的全局中间件堆栈中包含在 App\Http\Kernel 类中的 TrimStrings 和 ConvertEmptyStringsToNull 中间件。
因此，如果你不希望验证程序将 null 值视为无效的，那就将「可选」的请求字段标记为 nullable。
$this->validate($request, [
    'title' => 'required|unique:posts|max:255',
    'body' => 'required',
    'publish_at' => 'nullable|date',
]);

在这个例子里，我们指定 publish_at 字段可以为 null 或者一个有效的日期格式。如果 nullable 的修饰词没有被添加到规则定义中，验证器会认为 null 是一个无效的日期格式。

AJAX 请求 & 验证#
当我们对 AJAX 的请求中使用 validate 方法时，Laravel 并不会生成一个重定向响应，而是会生成一个包含所有验证错误信息的 JSON 响应。这个 JSON 响应会包含一个 HTTP 状态码 422 被发送出去

表单请求验证#
创建表单请求#
php artisan make:request StoreBlogPost

新生成的类保存在 app/Http/Requests 目录下。如果这个目录不存在，运行 make:request 命令时它会被创建出来。让我们添加一些验证规则到 rules 方法中：
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

验证规则是如何运行的呢？你所需要做的就是在控制器方法中类型提示传入的请求。在调用控制器方法之前验证传入的表单请求，这意味着你不需要在控制器中写任何验证逻辑：
/**
 * 保存传入的博客文章。
 *
 * @param  StoreBlogPost  $request
 * @return Response
 */
public function store(StoreBlogPost $request)
{
    // The incoming request is valid...
}

如果验证失败，就会生成一个让用户返回到先前的位置的重定向响应。这些错误也会被闪存到 Session 中，以便这些错误都可以在页面中显示出来。如果传入的请求是 AJAX，会向用户返回具有 422 状态代码和验证错误信息的 JSON 数据的 HTTP 响应。


添加表单请求后钩子#
如果你想在表单请求「之后」添加钩子，可以使用 withValidator 方法。这个方法接收一个完整的验证构造器，允许你在验证结果返回之前调用任何方法：
/**
 * 配置验证器实例。
 *
 * @param  \Illuminate\Validation\Validator  $validator
 * @return void
 */
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if ($this->somethingElseIsInvalid()) {
            $validator->errors()->add('field', 'Something is wrong with this field!');
        }
    });
}



授权表单请求#
表单请求类内也包含了 authorize 方法
在这个方法中，你可以检查经过身份验证的用户确定其是否具有更新给定资源的权限。比方说，你可以判断用户是否拥有更新文章评论的权限：
/**
 * 判断用户是否有权限做出此请求。
 *
 * @return bool
 */
public function authorize()
{
    $comment = Comment::find($this->route('comment'));

    return $comment && $this->user()->can('update', $comment);
}

由于所有的表单请求都是继承了 Laravel 中的请求基类，所以我们可以使用 user 方法去获取当前认证登录的用户
Route::post('comment/{comment}');

如果 authorize 方法返回 false，则会自动返回一个包含 403 状态码的 HTTP 响应，也不会运行控制器的方法
如果你打算在应用程序的其它部分也能处理授权逻辑，只需从 authorize 方法返回 true
/**
 * 判断用户是否有权限进行此请求。
 *
 * @return bool
 */
public function authorize()
{
    return true;
}


自定义错误消息#
你可以通过重写表单请求的 messages 方法来自定义错误消息。此方法应该如下所示返回属性/规则对数组及其对应错误消息：
/**
 * 获取已定义的验证规则的错误消息。
 *
 * @return array
 */
public function messages()
{
    return [
        'title.required' => 'A title is required',
        'body.required'  => 'A message is required',
    ];
}

手动创建验证器#
如果你不想要使用请求上使用 validate 方法，你可以通过 validator Facade 手动创建一个验证器实例。
// 用 Facade 上的 make 方法生成一个新的验证器实例：
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

        // 保存文章
    }
}

传给 make 方法的第一个参数是要验证的数据。第二个参数则是该数据的验证规则
// 如果请求没有通过验证，则可以使用 withErrors 方法把错误消息闪存到 Session。使用这个方法进行重定向之后，$errors 变量会自动与视图中共享，你可以将这些消息显示给用户。withErrors 方法接收验证器、MessageBag 或 PHP array。


自动重定向#
// 如果想手动创建验证器实例，又想利用请求中 validates 方法提供的自动重定向，那么你可以在现有的验证器实例上调用 validate 方法。如果验证失败，用户会自动重定向，如果是 AJAX 请求，将会返回 JSON 格式的响应：
Validator::make($request->all(), [
    'title' => 'required|unique:posts|max:255',
    'body' => 'required',
])->validate();

命名错误包#
// 如果你一个页面中有多个表单，你可以命名错误信息的 MessageBag 来检索特定表单的错误消息。只需给 withErrors 方法传递一个名字作为第二个参数：
return redirect('register')
            ->withErrors($validator, 'login');

然后你能从 $errors 变量中获取命名的 MessageBag 实例：
{{ $errors->login->first('email') }}


验证后钩子#
验证器还允许你添加在验证完成之后运行的回调函数。以便你进行进一步的验证，甚至是在消息集合中添加更多的错误消息。使用它只需在验证实例上使用 after 方法
$validator = Validator::make(...);

$validator->after(function ($validator) {
    if ($this->somethingElseIsInvalid()) {
        $validator->errors()->add('field', 'Something is wrong with this field!');
    }
});

if ($validator->fails()) {
    //
}

处理错误消息#
在 Validator 实例上调用 errors 方法后，会得到一个 Illuminate\Support\MessageBag 实例，该实例具有各种方便的处理错误消息的方法。$errors 变量是自动提供给所有视图的 MessageBag 类的一个实例。

查看特定字段的第一个错误消息#
$errors = $validator->errors();

echo $errors->first('email');

查看特定字段的所有错误消息#
如果你想以数组的形式获取指定字段的所有错误消息，则可以使用 get 方法：
foreach ($errors->get('email') as $message) {
    //
}

如果要验证表单的数组字段，你可以使用 * 来获取每个数组元素的所有错误消息：
foreach ($errors->get('attachments.*') as $message) {
    //
}

查看所有字段的错误消息#
foreach ($errors->all() as $message) {
    //
}

判断特定字段是否含有错误消息#
if ($errors->has('email')) {
    //
}

自定义错误消息#
如果有需要的话，你也可以自定义错误消息取代默认值进行验证
有几种方法可以指定自定义消息。首先，你可以将自定义消息作为第三个参数传递给 Validator::make 方法：
$messages = [
    'required' => 'The :attribute field is required.',
];

$validator = Validator::make($input, $rules, $messages);

在这个例子中，:attribute 占位符会被验证字段的实际名称取代。除此之外，你还可以在验证消息中使用其他占位符。例如：
$messages = [
    'same'    => 'The :attribute and :other must match.',
    'size'    => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute must be between :min - :max.',
    'in'      => 'The :attribute must be one of the following types: :values',
];

为给定属性指定自定义消息#
有时候你可能只想为特定的字段自定义错误消息。只需在属性名称后使用「点」语法来指定验证的规则即可：
$messages = [
    'email.required' => 'We need to know your e-mail address!',
];

在语言文件中指定自定义消息#
//现实中大多数情况下，我们可能不仅仅只是将自定义消息传递给 Validator，而是想要会使用不同的语言文件来指定自定义消息。实现它需要在 resources/lang/xx/validation.php 语言文件中将定制的消息添加到 custom 数组
'custom' => [
    'email' => [
        'required' => 'We need to know your e-mail address!',
    ],
],

在语言文件中指定自定义属性#
如果要使用自定义属性名称替换验证消息的 :attribute 部分，就在 resources/lang/xx/validation.php 语言文件的 attributes 数组中指定自定义名称：
'attributes' => [
    'email' => 'email address',
],



可用的验证规则#
accepted#
验证的字段必须为 yes、 on、 1、或 true。这在确认「服务条款」是否同意时相当有用

active_url#
相当于使用了 PHP 函数 dns_get_record，验证的字段必须具有有效的 A 或 AAAA 记录。

after:date#
验证的字段必须是给定日期后的值。这个日期将会通过 PHP 函数 strtotime 来验证。
'start_date' => 'required|date|after:tomorrow'

你也可以指定其它的字段来比较日期：
'finish_date' => 'required|date|after:start_date'


after_or_equal:date#
验证的字段必须等于给定日期之后的值。更多信息请参见 after 规则。

alpha#
验证的字段必须完全是字母的字符。


alpha_dash#
验证的字段可能具有字母、数字、破折号（ - ）以及下划线（ _ ）。

alpha_num#
验证的字段必须完全是字母、数字。

array#
验证的字段必须是一个 PHP 数组。

before:date#
验证的字段必须是给定日期之前的值。这个日期将会通过 PHP 函数 strtotime 来验证。


before_or_equal:date#
验证的字段必须是给定日期之前或之前的值。这个日期将会使用 PHP 函数 strtotime 来验证。

between:min,max#
验证的字段的大小必须在给定的 min 和 max 之间。字符串、数字、数组或是文件大小的计算方式都用 size 方法进行评估。

boolean#
验证的字段必须能够被转换为布尔值。可接受的参数为 true、false、1、0、"1" 以及 "0"。

confirmed#
验证的字段必须和 foo_confirmation 的字段值一致。例如，如果要验证的字段是 password，输入中必须存在匹配的 password_confirmation 字段。


date#
验证的字段值必须是通过 PHP 函数 strtotime 校验的有效日期。

date_equals:date#
验证的字段必须等于给定的日期。该日期会被传递到 PHP 函数 strtotime。

date_format:format#
验证的字段必须与给定的格式相匹配。你应该只使用 date 或 date_format 其中一个用于验证，而不应该同时使用两者。

different:field#
验证的字段值必须与字段 (field) 的值不同。

digits:value#
验证的字段必须是数字，并且必须具有确切的值。

digits_between:min,max#
验证的字段的长度必须在给定的 min 和 max 之间。


dimensions#
验证的文件必须是图片并且图片比例必须符合规则：
'avatar' => 'dimensions:min_width=100,min_height=200'
可用的规则为： min_width、 max_width 、 min_height 、 max_height 、 width 、 height 、 ratio。

比例应该使用宽度除以高度的方式来约束。这样可以通过 3/2 这样的语句或像 1.5 这样的浮点的约束：
'avatar' => 'dimensions:ratio=3/2'

由于此规则需要多个参数，因此你可以 Rule::dimensions 方法来构造可读性高的规则：
use Illuminate\Validation\Rule;

Validator::make($data, [
    'avatar' => [
        'required',
        Rule::dimensions()->maxWidth(1000)->maxHeight(500)->ratio(3 / 2),
    ],
]);

distinct#
验证数组时，指定的字段不能有任何重复值。
'foo.*.id' => 'distinct'


email#
验证的字段必须符合 e-mail 地址格式。

exists:table,column#
验证的字段必须存在于给定的数据库表中。


Exists 规则的基本使用方法#
'state' => 'exists:states'

指定自定义字段名称#
'state' => 'exists:states,abbreviation'

如果你需要指定 exists 方法用来查询的数据库。你可以通过使用「点」语法将数据库的名称添加到数据表前面来实现这个目的：
'email' => 'exists:connection.staff,email'


如果要自定义验证规则执行的查询，可以使用 Rule 类来定义规则。在这个例子中，我们使用数组指定验证规则，而不是使用 | 字符来分隔它们：
use Illuminate\Validation\Rule;

Validator::make($data, [
    'email' => [
        'required',
        Rule::exists('staff')->where(function ($query) {
            $query->where('account_id', 1);
        }),
    ],
]);


file#
验证的字段必须是成功上传的文件。

filled#
验证的字段在存在时不能为空。

image#
验证的文件必须是一个图像（ jpeg、png、bmp、gif、或 svg ）。

in:foo,bar,...#
验证的字段必须包含在给定的值列表中。因为这个规则通常需要你 implode 一个数组，Rule::in 方法可以用来构造规则：
use Illuminate\Validation\Rule;

Validator::make($data, [
    'zones' => [
        'required',
        Rule::in(['first-zone', 'second-zone']),
    ],
]);

in_array:anotherfield#
验证的字段必须存在于另一个字段（anotherfield）的值中。

integer#
验证的字段必须是整数。

ip#
验证的字段必须是 IP 地址。

ipv4#
验证的字段必须是 IPv4 地址。

ipv6#
验证的字段必须是 IPv6 地址。

json#
验证的字段必须是有效的 JSON 字符串。

max:value#
验证中的字段必须小于或等于 value。字符串、数字、数组或是文件大小的计算方式都用 size 方法进行评估。

mimetypes:text/plain,...#
验证的文件必须与给定 MIME 类型之一匹配：
'video' => 'mimetypes:video/avi,video/mpeg,video/quicktime'
要确定上传文件的 MIME 类型，会读取文件的内容来判断 MIME 类型，这可能与客户端提供的 MIME 类型不同

mimes:foo,bar,...#
验证的文件必须具有与列出的其中一个扩展名相对应的 MIME 类型。
MIME 规则基本用法#
'photo' => 'mimes:jpeg,bmp,png'

min:value#
验证中的字段必须具有最小值。字符串、数字、数组或是文件大小的计算方式都用 size 方法进行评估。

nullable#
验证的字段可以为 null。这在验证基本数据类型时特别有用，例如可以包含空值的字符串和整数。

not_in:foo,bar,...#
验证的字段不能包含在给定的值列表中。Rule::notIn 方法可以用来构建规则：
use Illuminate\Validation\Rule;

Validator::make($data, [
    'toppings' => [
        'required',
        Rule::notIn(['sprinkles', 'cherries']),
    ],
]);

numeric#
验证的字段必须是数字。

present#
验证的字段必须存在于输入数据中，但可以为空。

regex:pattern#
验证的字段必须与给定的正则表达式匹配。
注意： 当使用 regex 规则时，你必须使用数组，而不是使用 | 分隔符，特别是如果正则表达式包含 | 字符。

required#
验证的字段必须存在于输入数据中，而不是空。如果满足以下条件之一，则字段被视为「空」：
    该值为 null.
    该值为空字符串。
    该值为空数组或空的 可数 对象。
    该值为没有路径的上传文件。


required_if:anotherfield,value,...#
如果指定的其它字段（ anotherfield ）等于任何一个 value 时，则验证的字段必须存在且不为空。

required_unless:anotherfield,value,...#
如果指定的其它字段（ anotherfield ）等于任何一个 value 时，验证中的字段必须存在且不为空。

required_with:foo,bar,...#
验证的字段必须存在，并且只有当其他指定的字段存在时才能为空。

required_with_all:foo,bar,...#
验证的字段必须存在，并且只有当所有其他指定的字段都存在时才能为空。

required_without:foo,bar,...#
验证的字段必须存在，并且只有当其他指定的字段不存在时才能为空。

required_without_all:foo,bar,...#
验证的字段必须存在，并且只有当所有其他指定的字段不存在时才能为空。

same:field#
给定字段必须与验证的字段匹配。

size:value#
验证的字段必须具有与给定值匹配的大小。对于字符串来说，value 对应于字符数。对于数字来说，value 对应于给定的整数值。对于数组来说， size 对应的是数组的 count 值。对文件来说，size 对应的是文件大小（单位 kb ）

string#
验证的字段必须是字符串。如果要允许该字段的值为 null ，就将 nullable 规则附加到该字段中。

timezone#
验证的字段必须是有效的时区标识符，会根据 PHP 函数 timezone_identifiers_list 来判断。

unique:table,column,except,idColumn#
验证的字段在给定的数据库表中必须是唯一的。如果没有指定 column，将会使用字段本身的名称。

指定自定义字段名称：
'email' => 'unique:users,email_address'

自定义数据库连接
'email' => 'unique:connection.users,email_address'

强迫 Unique 规则忽略指定 ID：
use Illuminate\Validation\Rule;

Validator::make($data, [
    'email' => [
        'required',
        Rule::unique('users')->ignore($user->id),
    ],
]);

如果你的数据表使用的主键名称不是 id，那就在调用 ignore 方法时指定字段的名称：
'email' => Rule::unique('users')->ignore($user->id, 'user_id')

增加额外的 Where 语句：
你也可以通过 where 方法指定额外的查询条件。例如，我们添加 account_id 为 1 的约束
'email' => Rule::unique('users')->where(function ($query) {
    $query->where('account_id', 1);
})

url#
验证的字段必须是有效的 URL。

按条件增加规则#

存在才验证#
在某些情况下，只有在该字段存在于输入数组中时，才可以对字段执行验证检查。可通过增加 sometimes 到规则列表来实现：
$v = Validator::make($data, [
    'email' => 'sometimes|required|email',
]);

在上面的例子中，email 字段只有在 $data 数组中存在时才会被验证。

复杂的条件验证#
$v = Validator::make($data, [
    'email' => 'required|email',
    'games' => 'required|numeric',
]);

$v->sometimes('reason', 'required|max:500', function ($input) {
    return $input->games >= 100;
});

传入 sometimes 方法的第一个参数是要用来验证的字段名称。第二个参数是我们想使用的验证规则。闭包 作为第三个参数传入，如果其返回 true，则额外的规则就会被加入。这个方法可以轻松地创建复杂的条件验证。你甚至可以一次对多个字段增加条件验证：
$v->sometimes(['reason', 'cost'], 'required', function ($input) {
    return $input->games >= 100;
});

传入 闭包 的 $input 参数是 Illuminate\Support\Fluent 的一个实例，可用来访问你的输入或文件对象。


验证数组#
验证表单的输入为数组的字段也不难。你可以使用「点」语法来验证数组中的属性。例如，如果传入的 HTTP 请求中包含 photos[profile] 字段，可以如下验证：
$validator = Validator::make($request->all(), [
    'photos.profile' => 'required|image',
]);

你还可以验证数组中的每个元素。例如，要验证指定数组输入字段中的每一个 email 是唯一的，可以这么做：
$validator = Validator::make($request->all(), [
    'person.*.email' => 'email|unique:users',
    'person.*.first_name' => 'required_with:person.*.last_name',
]);

同理，你可以在语言文件定义验证信息时使用 * 字符，为基于数组的字段使用单个验证消息：
'custom' => [
    'person.*.email' => [
        'unique' => 'Each person must have a unique e-mail address',
    ]
],

自定义验证规则#
使用规则对象#
Laravel 会将新的规则存放在 app/Rules 目录中：
php artisan make:rule Uppercase

一旦创建了规则，我们就可以定义它的行为。规则对象包含两个方法： passes 和 message
passes 方法接收属性值和名称，并根据属性值是否符合规则而返回 true 或者 false
message 应返回验证失败时应使用的验证错误消息：
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Uppercase implements Rule
{
    /**
     * 判断验证规则是否通过。
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return strtoupper($value) === $value;
    }

    /**
     * 获取验证错误信息。
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be uppercase.';
    }
}


当然，如果你希望从翻译文件中返回一个错误信息，你可以从 message 方法中调用辅助函数 trans：
/**
 * 获取验证错误信息。
 *
 * @return string
 */
public function message()
{
    return trans('validation.uppercase');
}

一旦规则对象被定义好后，你可以通过将规则对象的实例传递给其他验证规则来将其附加到验证器：
use App\Rules\Uppercase;

$request->validate([
    'name' => ['required', new Uppercase],
]);



使用扩展#
另外一个注册自定义验证规则的方法，就是使用 Validator Facade 中的 extend 方法。让我们在 服务提供器 中使用这个方法来注册自定义验证规则：

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 引导任何应用服务。
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('foo', function ($attribute, $value, $parameters, $validator) {
            return $value == 'foo';
        });
    }

    /**
     * 注册服务提供器。
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

自定义的验证闭包接收四个参数：要被验证的属性名称 $attribute、属性的值 $value、传入验证规则的参数数组 $parameters、及 Validator 实例。
除了使用闭包，你也可以传入类和方法到 extend 方法中：
Validator::extend('foo', 'FooValidator@validate');


自定义错误消息#
你还需要为自定义规则定义错误消息。这可以通过使用自定义内联消息数组或是在验证语言文件中加入新的规则来实现。此消息应该被放在数组的第一级，而不是被放在 custom 数组内，这是仅针对特定属性的错误消息:
"foo" => "你的输入是无效的!",

"accepted" => ":attribute 必须被接受。",

// 其余的验证错误消息...
创建自定义验证规则时，可能需要为错误消息定义自定义替换占位符。你可以像上面所描述的那样通过 Validator Facade 来使用 replacer 方法创建一个自定义验证器。你可以在 服务提供器 中的 boot 方法中执行此操作：
/**
 * 引导任何应用服务。
 *
 * @return void
 */
public function boot()
{
    Validator::extend(...);

    Validator::replacer('foo', function ($message, $attribute, $rule, $parameters) {
        return str_replace(...);
    });
}

隐式扩展#
默认情况下，当所要验证的属性不存在或包含由 required 规则定义的空值时，将不会运行正常的验证规则（包括自定义扩展）。例如，unique 规则不会针对 null 运行：
$rules = ['name' => 'unique'];

$input = ['name' => null];

Validator::make($input, $rules)->passes(); // true

即使属性为空的规则也可以运行，该规则必须意味着该属性是必需的。要创建这样一个「隐式」扩展，可以使用 Validator::extendImplicit() 方法：
Validator::extendImplicit('foo', function ($attribute, $value, $parameters, $validator) {
    return $value == 'foo';
});
