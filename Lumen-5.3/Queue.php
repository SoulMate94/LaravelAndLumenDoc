<?php

// 队列的配置在 .env 中 QUEUE_DRIVER 选项里。

// 如果你想完全自定义配置信息，你可以复制 vendor/laravel/lumen-framework/config/queue.php 整个文件到 config/queue.php 中，根目录如果没有 config 的话你应该创建一个。

# 驱动的必要设置

##数据库
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

## 生成器
// amesapce App\Jobs;

class ExampleJob extends Job
{
    /**
     * 创建一个新的任务实例
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 运行任务
     *
     * @return void
     */
    public function handle()
    {

    }
}

# 将任务推送到队列上
dispatch(new ExampleJob);

// 当然，你也可以使用 Queue facade。 如果你想使用 facade 的话，你需要在 bootstrap/app.php 中把 $app->withFacades() 这行调用的注释去除掉。
Queue::push(new ExampleJob);











