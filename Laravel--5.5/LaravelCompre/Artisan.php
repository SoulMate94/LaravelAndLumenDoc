<?php

Artisan 控制台#
简介#
Artisan 是 Laravel 自带的命令行接口
要查看所有可用的 Artisan 命令的列表，可以使用 list 命令：
php artisan list


每个命令包含了「帮助」界面，它会显示并概述命令的可用参数及选项
只需要在命令前面加上 help 即可查看命令帮助界面：
php artisan help migrate

Laravel REPL#
所有 Laravel 应用都包含了 Tinker，一个基于 PsySH 包提供支持的 REPL
//Tinker 让你可以在命令行中与你整个的 Laravel 应用进行交互，包括 Eloquent ORM、任务、事件等等。运行 Artisan 命令 tinker 进入 Tinker 环境：
php artisan tinker


编写命令#
//除 Artisan 提供的命令之外，还可以构建自己的自定义命令
命令默认存储在 app/Console/Commands 目录，你也可以修改 composer.json 文件来指定你想要存放的目录。


生成命令#
要创建一个新的命令，可以使用 Artisan 命令 make:command
//。这个命令会在 app/Console/Commands 目录中创建一个新的命令类。 不必担心应用中不存在这个目录，因为它会在你第一次运行 Artisan 命令 make:command 时创建
php artisan make:command SendEmails

命令结构#
//命令生成后，应先填写类的 signature 和 description 属性，这会在使用 list 命令的时候显示出来
//执行命令时会调用 handle 方法，你可以在这个方法中放置命令逻辑。

//注意，我们可以在 Command 的构造函数中注入我们需要的任何依赖项。Laravel 服务容器 将会自动注入所有在构造函数中的带类型约束的依赖：
<?php

namespace App\Console\Commands;

use App\User;
use App\DripEmailer;
use Illuminate\Console\Command;

class SendEmails extends Command
{
    /**
     * 控制台命令 signature 的名称。
     *
     * @var string
     */
    protected $signature = 'email:send {user}';

    /**
     * 控制台命令说明。
     *
     * @var string
     */
    protected $description = 'Send drip e-mails to a user';

    /**
     * 邮件服务的 drip 属性。
     *
     * @var DripEmailer
     */
    protected $drip;

    /**
     * 创建一个新的命令实例。
     *
     * @param  DripEmailer  $drip
     * @return void
     */
    public function __construct(DripEmailer $drip)
    {
        parent::__construct();

        $this->drip = $drip;
    }

    /**
     * 执行控制台命令。
     *
     * @return mixed
     */
    public function handle()
    {
        $this->drip->send(User::find($this->argument('user')));
    }
}

闭包命令#
//基于闭包的命令提供一个用类替代定义控制台命令的方法
// 同样的，路由闭包是控制器的一种替代方法，而命令闭包可以视为命令类的替代方法。在 app/Console/Kernel.php 文件的 commands 方法中， Laravel 加载了 routes/console.php 文件：
/**
 * 注册应用的基于闭包的命令。
 *
 * @return void
 */
protected function commands()
{
    require base_path('routes/console.php');
}

//你可以使用 Artisan::command 方法定义所有基于闭包的路由。
//command 方法接收两个参数：命令签名 和一个接收命令参数和选项的闭包：
Artisan::command('build {project}', function ($project) {
    $this->info("Building {$project}!");
});

//闭包绑定底层的命令实例，因此你可以完全访问通常可以在完整命令类中访问的所有辅助方法

类型提示依赖#
//除了接收命令的参数和选项外，命令闭包也可以使用类型提示从 服务容器 中解析你想要的其他依赖关系：
use App\User;
use App\DripEmailer;

Artisan::command('email:send {user}', function (DripEmailer $drip, $user) {
    $drip->send(User::find($user));
});

闭包命令描述#
//当定义一个基于闭包的命令时，你可以使用 describe 方法来为命令添加描述
这个描述会在你执行 php artisan list 或 php artisan help 命令时显示：
Artisan::command('build {project}', function ($project) {
    $this->info("Building {$project}!");
})->describe('Build the project');


定义输入期望#
//Laravel 可以非常方便地在你的命令里用 signature 属性来定义你期望用户输入的内容
//signature 属性允许你使用单一且可读性非常高的、类似路由的语法定义命令的名称、参数和选项。


参数#
/**
 * 控制台命令 signature 的名称。
 *
 * @var string
 */
protected $signature = 'email:send {user}';
//你也可以创建可选参数，并定义参数的默认值：
// 可选参数...
email:send {user?}

// 带有默认值的可选参数...
email:send {user=foo}

选项#
//选项，如参数，是用户输入的另一种格式。当命令行指定选项时，它们以两个连字符 (--) 作为前缀
//有两种类型的选项：接收值和不接受值。不接收值的选项作为布尔值的 「开关」。让我们看一下这种类型的选项的例子：
/**
 * 控制台命令 signature 的名称。
 *
 * @var string
 */
protected $signature = 'email:send {user} {--queue}';
//在这个例子中，可以在调用Artisan命令时指定 --queue 开关。如果 --queue 开关被传递，该选项的值为 true ，否则为 false ：
php artisan email:send 1 --queue

带值的选项#
//如果用户必须为选项指定一个值，需要用一个等号 = 作为选项名称的后缀：
/**
 * 控制台命令 signature 的名称。
 *
 * @var string
 */
protected $signature = 'email:send {user} {--queue=}';
//在这个例子中， 用户可以传递该选项的值，如下所示：
php artisan email:send 1 --queue=default
//你可以通过在选项名称后面指定默认值来设定选项的默认值。如果用户没有传递选项值，将使用设定的默认值：
email:send {user} {--queue=default}

选项简写#
// 要在定义选项时指定简写，你可以在选项名称前指定它，并且使用 | 分隔符将简写与完整选项名称分隔开：
email:send {user} {--Q|queue}

输入数组#
//如果你想定义参数或选项你可以使用 * 符号来期望输入数组
email:send {user*}
//调用此方法时，可以传递 user 参数给命令行。例如，以下命令会设置 user 的值为 ['foo', 'bar'] ：
php artisan email:send foo bar
//在定义期望数组输入的选项时，传递给命令的每个选项值都应以选项名称为前缀：
email:send {user} {--id=*}

php artisan email:send --id=1 --id=2


输入说明#
//你可以通过冒号为输入参数和选项分配说明文字
// 如果你需要一点额外的空间来定义你的命令，可以随意将多个行分开：
/**
 * 设置控制台命令的 signature 属性。
 *
 * @var string
 */
protected $signature = 'email:send
                        {user : The ID of the user}
                        {--queue= : Whether the job should be queued}';


I/O 命令#
检索输入#
// 在命令执行时，你可以使用 argument 和 option 方法获取命令的参数和选项：
/**
 * 执行控制台命令。
 *
 * @return mixed
 */
public function handle()
{
    $userId = $this->argument('user');

    //
}
//如果你需要将所有参数以 array 检索，就调用 arguments 方法：
$arguments = $this->arguments();

//可以使用 option 方法像 arguments 一样容易地检索选项。要将所有选项作为 array 检索，请调用 options 方法：
// 检索特定选项...
$queueName = $this->option('queue');

// 检索所有选项...
$options = $this->options();
// 如果参数或选项不存在，则返回 null 。

交互式输入#
// 除了显示输出外，你还可以要求用户在执行命令时提供输入
ask 方法将提示用户给定问题，接收他们的输入，然后将用户的输入返回到你的命令：
/**
 * 执行控制台命令。
 *
 * @return mixed
 */
public function handle()
{
    $name = $this->ask('What is your name?');
}

//secret 方法和 ask 方法类似，即用户输入的内容在他们输入控制台时是不可见的。这个方法适用于需要用户输入像密码这样的敏感信息的情况：
$password = $this->secret('What is the password?');

请求确认#
//如果你要用户提供一些简单的确认信息，你可以使用 confirm 方法
// 默认情况下，该方法将返回 false。但是，如果用户根据提示输入 y 或者 yes 则会返回 true。
if ($this->confirm('Do you wish to continue?')) {
    //
}

自动补全#
anticipate 方法可用于为可能的选择提供自动补全功能
$name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);

多重选择#
//如果你要给用户提供预定义的一组选择，可以使用 choice 方法。如果用户未选择任何选项，你可以返回设置的默认值：
$name = $this->choice('What is your name?', ['Taylor', 'Dayle'], $default);


编写输出#
//可以使用 line 、info 、 comment 、 question 和 error 方法来将输出发送到终端
//例如，如果我们要向用户展示普通信息，通常来说，最好使用 info 方法，它会在控制台将输出的内容显示为绿色：
/**
 * 执行控制台命令。
 *
 * @return mixed
 */
public function handle()
{
    $this->info('Display this on the screen');
}

显示错误信息， 使用 error 方法。 错误信息通常显示为红色：
$this->error('Something went wrong!');

如果你想在控制台显示无色的输出，请使用 line 方法：
$this->line('Display this on the screen');


表布局#
//table 方法可以方便地格式化多行/列的数据。只需要将标题和行传递给这个方法。宽度和高度将基于给定数据动态计算：
$headers = ['Name', 'Email'];

$users = App\User::all(['name', 'email'])->toArray();

$this->table($headers, $users);

进度条#
//对于长时间运行的任务，显示进度指示符可能会很有用
//使用输出对象，我们可以启动、推进和停止进度条。首先，定义进程将遍历的步骤总数。然后，在处理每个项目后推进进度栏：

$users = App\User::all();

$bar = $this->output->createProgressBar(count($users));

foreach ($users as $user) {
    $this->performTask($user);

    $bar->advance();
}

$bar->finish();

注册命令#
//由于在控制台内核的 commands 方法中调用了 load 方法，所以 app/Console/Commands 目录下的所有命令都将自动注册到 Artisan。
// 实际上，你可以自由地调用 load 方法来扫描 Artisan 命令的其他目录：
/**
 * 注册应用程序的命令。
 *
 * @return void
 */
protected function commands()
{
    $this->load(__DIR__.'/Commands');
    $this->load(__DIR__.'/MoreCommands');

    // ...
}

//你还可以通过将其类名添加到 app/Console/Kernel.php 文件的 $command 属性来手动注册命令

//当 Artisan 启动时，该属性中列出的所有命令将由 服务容器 解析并在 Artisan 注册：
protected $commands = [
    Commands\SendEmails::class
];

以编程方式执行命令#
//有时你可能希望在 CLI 之外执行 Artisan 命令。例如，你可能希望从路由或控制器触发 Artisan 命令
你可以使用 Artisan facade 上的 call 方法来完成。 
call 方法接受命令的名称作为第一个参数，命令参数的数组作为第二个参数。结束码会被返
回：

Route::get('/foo', function () {
    $exitCode = Artisan::call('email:send', [
        'user' => 1, '--queue' => 'default'
    ]);

    //
});


//在 Artisan facade 上使用 queue 方法，可以将 Artisan 命令交由 队列工作进程 在后台进行处理
!!在使用此方法之前，请确保已配置队列并运行了队列监听器：
Route::get('/foo', function () {
    Artisan::queue('email:send', [
        'user' => 1, '--queue' => 'default'
    ]);

    //
});

//如果需要指定不接受字符串值的选项的值，例如 migrate：refresh 命令中的 --force 标志，则可以传递 true 或 false ：
$exitCode = Artisan::call('migrate:refresh', [
    '--force' => true,
]);

从其他命令调用命令#
//有时候你希望从现有的 Artisan 命令中调用其它命令
你可以使用 call 方法。call 方法接受命令名称和命令参数的数组：
/**
 * 执行控制台命令。
 *
 * @return mixed
 */
public function handle()
{
    $this->call('email:send', [
        'user' => 1, '--queue' => 'default'
    ]);

    //
}

//如果要调用另一个控制台命令并阻止其所有输出，可以使用 callSilent 方法。 callSilent 和 call 方法用法一样：
$this->callSilent('email:send', [
    'user' => 1, '--queue' => 'default'
]);