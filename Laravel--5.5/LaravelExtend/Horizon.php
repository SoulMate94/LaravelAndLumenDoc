<?php

Laravel 队列监控面板 - Horizon#
介绍#
Horizon 为 Laravel 官方出品的 Redis 队列提供了一个可以通过代码进行配置、并且非常漂亮的仪表盘，并且能够轻松监控队列的任务吞吐量、执行时间以及任务失败情况等关键指标。

队列执行者的所有配置项都存放在一个简单的配置文件中，所以团队可以通过版本控制进行协作维护

安装#
!!由于 Horizon 中使用了异步处理信号，所以需要 PHP 7.1+

可以使用 Composer 将 Horizon 安装进你的 Laravel 项目：
composer require laravel/horizon

安装完成后，使用 vendor:publish Artisan 命令发布相关文件：
php artisan vendor:publish --provider="Laravel\Horizon\HorizonServiceProvider"



配置#
Horizon 的主要配置文件会被放置到 config/horizon.php

负载均衡配置#
Horizon 有三种负载均衡策略：simple、auto、 和 false，默认策略是 simple，会将接收到的任务均分给队列进程：
'balance' => 'simple',

策略 auto 会根据每个队列的压力自动调整其执行者进程数目，例如：如果 notifications 有 1000 个待执行的任务，但是你的 render 队列是空的，Horizon 会分派更多执行者进程给 notifications 队列，直到队列任务全部执行完毕（即队列为空）

当配置项 balance 设置为 false 时，Horizon 的执行策略与 Laravel 默认行为一致，及根据队列在配置文件中配置的顺序处理队列任务。

仪表盘权限验证#
// Horizon 仪表盘的路由是 /horizon ，默认只能在 local 环境中访问仪表盘。
// 我们可以使用 Horizon::auth 函数定义更具体的访问策略。auth 函数能够接受一个回调函数，此回调函数需要返回 true 或 false ，从而确认当前用户是否有权限访问 Horizon 仪表盘：
Horizon::auth(function ($request) {
    // return true / false;
});


运行 Horizon#
php artisan horizon

使用 Artisan 命令 horizon:pause 和 horizon:continue 来暂停和恢复队列的执行：
php artisan horizon:pause

php artisan horizon:continue

使用 Artisan 命令 horizon:terminate 来正常停止系统中的 Horizon 主进程，此命令执行时，Horizon 当前执行中的任务会被正常完成，然后 Horizon 执行结束：
php artisan horizon:terminate



部署 Horizon#
生产环境中，我们需要配置一个进程管理工具来监控 php artisan horizon 命令的执行，以便在其意外退出时自动重启。

当服务器部署新代码时，需要终止当前 Horizon 主进程，然后通过进程管理工具来重启，从而使用最新的代码


使用 Artisan 命令 horizon:terminate 来正常停止系统中的 Horizon 主进程，此命令执行时，Horizon 当前执行中的任务会被正常完成，然后 Horizon 执行结束：
php artisan horizon:terminate


Supervisor 配置#
[program:horizon]
process_name=%(program_name)
command=php /home/forge/app.com/artisan horizon
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/home/forge/app.com/horizon.log

标签#
Horizon 允许我们给队列任务打上一系列标签，包括 mailables、事件广播、通知以及队列中的时间侦听器
事实上，Horizon 会智能并且自动根据任务携带的 Eloquent 模型给大多数任务打上标签，如下任务示例：

<?php

namespace App\Jobs;

use App\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RenderVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The video instance.
     *
     * @var \App\Video
     */
    public $video;

    /**
     * Create a new job instance.
     *
     * @param  \App\Video  $video
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}

$video = App\Video::find(1);

App\Jobs\RenderVideo::dispatch($video);


自定义标签#
如果需要自定义一个可被放入队列对象的标签，可以在此类中定义 tags 函数：
class RenderVideo implements ShouldQueue
{
    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {
        return ['render', 'video:'.$this->video->id];
    }
}


通知#
Note: 使用通知之前，需要将 Composer 包 guzzlehttp/guzzle 安装到目标项目，如果配置 Horizon 发送短信通知，也要注意阅读Nexmo 通知驱动的依赖条件。


如果需要在队列等待时间过长时发起通知，可以在应用的 AppServiceProvider 中调用 Horizon::routeSlackNotificationsTo 和 Horizon::routeSmsNotificationsTo 函数：
Horizon::routeSlackNotificationsTo('slack-webhook-url');

Horizon::routeSmsNotificationsTo('15556667777');

配置等待时间过长通知的阈值#
可以在 config/horizon.php 中配置等待时间过长具体秒数，配置项 waits 可以针对每个 链接/队列 配置阈值：
'waits' => [
    'redis:default' => 60,
],


Metrics#
Horizon 包含一个 metrics 仪表盘，它可以提供任务和队列等待时间和吞吐量信息，为了填充此仪表盘，需要使用应用的 scheduler 每五分钟运行一次 Horizon 的 Artisan 命令 snapshot：

/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
protected function schedule(Schedule $schedule)
{
    $schedule->command('horizon:snapshot')->everyFiveMinutes();
}