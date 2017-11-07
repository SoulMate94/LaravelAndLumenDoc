<?php

#自定义Artisan命令
//如果我们想生成自己的artisan命令 首先在cd到项目目录生成console：
php artisan make:console TestArtisan
//这行命令就会生成一个名为 TestArtisan 的console，我们在这个目录就可以找到它：app\Console\Commands：
class TestArtisan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}


#自定义命令
//$signature 这个变量就是我们在命令行中输入的命令 比如make:controller,make:model等，我们将它改为我们想要的命令：
	protected $signature = 'learn_laravel:console';
//$description这个变量没有什么好说的，就是说明：
    protected $description = 'learn how to customize commands';
//具体的业务逻辑是定义在handle方法中的，我们先来定义简单的打印一串文字：
    public function handle()
    {
        $this->info('test Hello');
    }
//最后一步就是将这个console注册，我们在app\Console\Kernal.php文件中注册：
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        // 注册我们自己的命令
        \App\Console\Commands\TestArtisan::class,
    ];
//如何查看我们有没有注册成功呢？我们使用 php artisan 命令就会看到一大堆的命令，这其中如果包含了我们自定义的artisan，那么就注册成功了。
//我们现在可以使用我们自定义的命令来测试了：
php artisan learn_laravel:console


#传入参数
//在诸多的artisan命令中 我们常常会传入一些参数 比如：php artisan make:model Articles。那么如何传入参数呢？我们来修改下$signature：
protected $signature = 'learn_laravel:console {arguments}';
//没错，只需要添加一个{}里面跟上参数名就可以了

//我们可以在handle方法中接收参数：
    public function handle()
    {
        $this->info('test Hello '.$this->argument('arguments'));
    }


#可选参数
//当我们指定了参数后就必须传入参数，在有些时候参数是可以不设置的，那么我们需要这么写：
protected $signature = 'learn_laravel:console {arguments?}';	//加问好
public function handle()
{
    $this->info('test Hello '.$this->argument('arguments'));
}
//这样就不会报错了。


#默认参数
//如果指定了默认参数值，当传入参数后使用传入后的参数，没有指定则使用默认值：
protected $signature = 'learn_laravel:console {arguments=default}';