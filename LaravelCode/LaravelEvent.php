<?php

#Event
//Event顾名思义就是事件的意思，在实际开发中 当用户做了某些动作或操作的时候 我们需要监听这些事件来做相应的处理。
//比如说用户注册一个账户我们需要往用户的邮箱中发验证信息这种操作。
//我们可以通过artisan命令来生成一个event和一个listener：
php artisan make:event  CustomEvent
php artisan make:listener  CustomListener

//但是有一个更加便利的方法，首先来到这个文件下：app/Providers/EventServiceProvider.php
//观察这个数组：
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
    ];

//我们可以在这个数组中写我们想要生成的Event和Listener，一个Event可以对应多个Listener，下面来写一个例子：
    protected $listen = [
        'App\Events\UserSignUp' => [
            'App\Listeners\SendEmail',
            'App\Listeners\SaveUser',
        ],
    ];
//编辑完我们想要的事件和监听者后，使用artisan命令来生成他们：
php artisan event:generate
//生成的Evnet在app/Event中 而Listener在app/Listener中

#触发事件
#进入到我们刚刚创建的Event中：
class UserSignUp extends Event
{
    use SerializesModels;

    public $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        // 可以在构造方法中声明要传入的数据 这里以User为例
        $this->user = $user;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }

//然后进入到Listener中写处理的逻辑：
class SendEmail
{
    protected $mail;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Mail $mail)
    {
        // 可以在这里依赖注入你需要的功能 这里以Email为例
        $this->mail = $mail;
    }

    /**
     * Handle the event.
     *
     * @param  UserSignUp  $event
     * @return void
     */
    public function handle(UserSignUp $event)
    {
        // 在这里完成要操作的业务逻辑,简单测试一下
        dump('send Email to ' . $event->user->name);
    }
}

//现在只差触发这个事件了，触发事件可以使用全局帮助函数：
Route::get('/', function () {
    $user = \App\User::findOrFail(0);
    // 这样就触发了一条Event
    event(new \App\Events\UserSignUp($user));
});