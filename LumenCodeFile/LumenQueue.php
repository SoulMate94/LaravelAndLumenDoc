<?php

#队列
//队列允许你将一个耗时的任务进行延迟处理

#配置信息
队列的配置在 .env 中 QUEUE_DRIVER 选项里。
QUEUE_DRIVER=sync //同步

//如果你想完全自定义配置信息，你可以复制 vendor/laravel/lumen-framework/config/queue.php 整个文件到 config/queue.php 中，根目录如果没有 config 的话你应该创建一个。


#驱动的必要设置
##数据库
//要使用 database 这个队列驱动的话，则需要创建一个数据表来记住任务:

Schema::create('jobs', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('queue');
    $table->longText('payload');
    $table->tinyInteger('attempts')->unsigned();
    $table->tinyInteger('reserved')->unsigned();
    $table->unsignedInteger('reserved_at')->nullable();
    $table->unsignedInteger('available_at');
    $table->unsignedInteger('created_at');
    $table->index(['queue', 'reserved', 'reserved_at']);
});


#其它队列系统的依赖扩展包
//在使用列表里的队列服务前，必须安装以下依赖扩展包
Amazon SQS: aws/aws-sdk-php ~3.0
Beanstalkd: pda/pheanstalk ~3.0
Redis: predis/predis ~1.0


#不同于Laravel
//生成器
Lumen 中没有可用来生成事件监听器的命令，你可以复制 ExampleJob 文件，这个示例文件提供了基础的类结构，你可以作为参考。基类 Job 已经加载了我们需要的 traits InteractsWithQueue, Queueable, 和 SerializesModels：

<?php

namespace App\Jobs;

class ExampleJob extends Job
{
    /**
     * 创建一个新的任务实例。
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 运行任务。
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}


#将任务推送到队列上
//就如 Laravel 一样，你可使用 dispatch 辅助函数来推送任务到队列上：
dispatch(new ExampleJob);

//当然，你也可以使用 Queue facade。 如果你想使用 facade 的话，你需要在 bootstrap/app.php 中把 $app->withFacades() 这行调用的注释去除掉。
Queue::push(new ExampleJob);