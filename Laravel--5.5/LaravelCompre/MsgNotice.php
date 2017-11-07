<?php

Laravel 的消息通知系统#
简介
除了 发送邮件，Laravel 还支持通过多种频道发送通知，包括邮件、短信（通过 Nexmo）以及 Slack 。通知还能存到数据库，这样就能在网页界面上显示了。

创建通知#
// Laravel 中一条通知就是一个类（通常存在 app/Notifications 文件夹里）。看不到的话不要担心，运行一下 make:notification 命令就能创建了：
php artisan make:notification InvoicePaid

发送通知#
使用 Notifiable Trait#
通知可以通过两种方法发送： Notifiable trait 的 notify 方法或 Notification facade 。首先，让我们探索使用 trait ：
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
}

// 默认的 App\User 模型中使用了这个 trait，它包含着一个可以用来发通知的方法：notify 。 notify 方法需要一个通知实例做参数：
use App\Notifications\InvoicePaid;

$user->notify(new InvoicePaid($invoice));



使用 Notification Facade#
// 要用 facade 发送通知的话，要把可接收通知的实体和通知的实例传递给 send 方法：
Notification::send($users, new InvoicePaid($invoice));

指定发送频道#
每个通知类都有个 via 方法，它决定了通知在哪个频道上发送。开箱即用的通知频道有 mail, database, broadcast, nexmo, 和 slack 。
// via 方法受到一个 $notifiable 实例，它是接收通知的类实例。你可以用 $notifiable 来决定通知用哪个频道来发送
/**
 * 获取通知发送频道
 *
 * @param  mixed  $notifiable
 * @return array
 */
public function via($notifiable)
{
    return $notifiable->prefers_sms ? ['nexmo'] : ['mail', 'database'];
}



队列化通知#
// 可以通过添加 ShouldQueue 接口和 Queueable trait 把通知加入队列。它们两个在使用 make:notification 命令来生成通知文件的时候就已经被导入了，所以你只需要添加到你的通知类就行了：
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    // ...
}


// 一旦加入 ShouldQueue 接口，你就能像平常那样发送通知了。Laravel 会检测 ShouldQueue 接口并自动将通知的发送放入队列中。
$user->notify(new InvoicePaid($invoice));

如果你想延迟发送，你可以通过 delay 方法来链式操作你的通知实例：
$when = Carbon::now()->addMinutes(10);

$user->notify((new InvoicePaid($invoice))->delay($when));


按需通知#
有时候你可能需要将通知发送给某个不是以”用户”身份存储在你的应用中的人。使用Notification::route 方法，你可以在发送通知之前指定 ad-hoc 通知路由信息：
Notification::route('mail', 'taylor@laravel.com')
            ->route('nexmo', '5555555555')
            ->send(new InvoicePaid($invoice));

邮件通知#
格式化邮件消息#
如果一条通知支持以邮件发送，你应该在通知类里定义一个 toMail 方法。这个方法将收到一个 $notifiable 实体并返回一个 Illuminate\Notifications\Messages\MailMessage 实例
/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
public function toMail($notifiable)
{
    $url = url('/invoice/'.$this->invoice->id);

    return (new MailMessage)
                ->line('One of your invoices has been paid!')
                ->action('View Invoice', $url)
                ->line('Thank you for using our application!');
}
// 在这个例子中，我们注册了一行文本，引导链接 ，然后又是一行文本。 MailMessage 提供的这些方法简化了对小的事务性的邮件进行格式化操作。邮件频道将会把这些消息组件转换成漂亮的响应式的 HTML 邮件模板并附上文本。下面是个 mail 频道生成的邮件示例：
发送邮件通知前，请在 config/app.php 中设置 name 值。 这将会在邮件通知消息的头部和尾部中被使用。

其他通知格式选项#
你可以使用 view 方法来指定一个应用于渲染通知电子邮件的自定义模板，而不是在通知类中定义文本的「模板」：
/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
public function toMail($notifiable)
{
    return (new MailMessage)->view(
        'emails.name', ['invoice' => $this->invoice]
    );
}


自定义接受者#
另外，你可以从 toMail 方法中返回一个 mailable 对象 ：
use App\Mail\InvoicePaid as Mailable;

/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return Mailable
 */
public function toMail($notifiable)
{
    return (new Mailable($this->invoice))->to($this->user->email);
}

错误消息#
有些通知是给用户提示错误，比如账单支付失败的提示。你可以通过调用 error 方法来指定这条邮件消息被当做一个错误提示。当邮件消息使用了 error 方法后，引导链接按钮会变成红色而非蓝色：
/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
public function toMail($notifiable)
{
   return (new MailMessage)
                ->error()
                ->subject('Notification Subject')
                ->line('...');
}

自定义接收者#
当通过 mail 频道来发送通知的时候，通知系统将会自动寻找你的 notifiable 实体中的 email 属性。你可以通过在实体中定义 routeNotificationForMail 方法来自定义邮件地址。
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * 邮件频道的路由
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email_address;
    }
}



自定义主题#
// 默认情况下，邮件主题是格式化成了标题格式的通知类的类名。所以如果你对通知类名为 InvoicePaid ，邮件主题将会是 Invoice Paid 。如果你想显式指定消息的主题，你可以在构建消息时调用 subject 方法：
/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
public function toMail($notifiable)
{
    return (new MailMessage)
                ->subject('Notification Subject')
                ->line('...');
}


自定义模板#
// 你可以通过发布通知包的资源来修改 HTML 模板和纯文本模板。运行这个命令后，邮件通知模板就被放在了 resources/views/vendor/notifications 文件夹下：
php artisan vendor:publish --tag=laravel-notifications

Markdown 邮件通知#


生成消息#
// 要使用相应的 Markdown 模板生成通知，您可以使用 make：notification Artisan命令的 --markdown 选项：
php artisan make:notification InvoicePaid --markdown=mail.invoice.paid

与所有其他邮件通知一样，使用 Markdown 模板的通知应在其通知类上定义一个 toMail 方法。 但是，不使用 line 和 action 方法来构造通知，而是使用 markdown 方法来指定应该使用的 Markdown 模板的名称：
/**
 * 获取通知的邮件展示方式
 *
 * @param  mixed  $notifiable
 * @return \Illuminate\Notifications\Messages\MailMessage
 */
public function toMail($notifiable)
{
    $url = url('/invoice/'.$this->invoice->id);

    return (new MailMessage)
                ->subject('Invoice Paid')
                ->markdown('mail.invoice.paid', ['url' => $url]);
}

写消息#
// Markdown 邮件通知使用 Blade 组件和Markdown语法的组合，允许您轻松构建通知，同时利用Laravel的预制通知组件：
@component('mail::message')
# Invoice Paid

Your invoice has been paid!

@component('mail::button', ['url' => $url])
View Invoice
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

自定义组件#


数据库通知#
先决条件#
database 通知频道在一张数据表里存储通知信息。这张表包含了比如通知类型、JSON 格式数据等描述通知的信息
// 你可以查询这张表的内容在应用界面上展示通知。但是在这之前，你需要先创建一个数据表来保存通知。你可以用 notifications:table 命令来生成迁移表：
php artisan notifications:table
php artisan migrate


格式化数据库通知#
如果通知支持被存储到数据表中，你应该在通知类中定义一个 toDatabase 或 toArray 方法
// 这个方法接收 $notifiable 实体参数并返回一个普通的 PHP 数组。这个返回的数组将被转成 JSON 格式并存储到通知数据表的 data 列。我们来看一个 toArray 的例子：
/**
 * 获取通知的数组展示方式
 *
 * @param  mixed  $notifiable
 * @return array
 */
public function toArray($notifiable)
{
    return [
        'invoice_id' => $this->invoice->id,
        'amount' => $this->invoice->amount,
    ];
}

toDatabase Vs toArray#
toArray 方法在 broadcast 频道也用到了，它用来决定广播给 JavaScript 客户端的数据。如果你想在 database 和 broadcast 频道中采用两种不同的数组展示方式，你应该定义 toDatabase 方法而非 toArray 方法



访问通知#
$user = App\User::find(1);

foreach ($user->notifications as $notification) {
    echo $notification->type;
}

如果你只想检索未读通知，你可以使用 unreadNotifications 关联。检索出来的通知也是以 created_at 时间戳来排序的：
$user = App\User::find(1);

foreach ($user->unreadNotifications as $notification) {
    echo $notification->type;
}

标为已读#
// 通常情况下，当用户查看了通知时，你就希望把通知标为已读。Illuminate\Notifications\Notifiable trait 提供了一个 markAsRead 方法，它能在对应的数据库记录里更新 read_at 列：
$user = App\User::find(1);

foreach ($user->unreadNotifications as $notification) {
    $notification->markAsRead();
}

你可以使用 markAsRead 方法直接操作一个通知集合，而不是一条条遍历每个通知：
$user->unreadNotifications->markAsRead();

你可以用批量更新的方式来把所有通知标为已读，而不用在数据库里检索：
$user = App\User::find(1);

$user->unreadNotifications()->update(['read_at' => Carbon::now()]);

当然，你可以通过 delete 通知来把它们从数据库删除：
$user->notifications()->delete();

广播通知#
// 如果一条通知支持广播，你应该在通知类里定义一个 toBroadcast 或 toArray 方法。这个方法将收到一个 $notifiable 实体并返回一个普通的 PHP 数组。返回的数组会被编码成 JSON 格式并广播给你的 JavaScript 客户端。我们来看个 toArray 方法的例子：
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * 获取通知的数组展示方式
 *
 * @param  mixed  $notifiable
 * @return BroadcastMessage
 */
public function toBroadcast($notifiable)
{
    return new BroadcastMessage([
        'invoice_id' => $this->invoice->id,
        'amount' => $this->invoice->amount,
    ]);
}

广播队列配置#
// 所有广播通知都排队等待广播。 如果要配置用于广播队列操作的队列连接或队列名称，你可以使用 BroadcastMessage 的 onConnection 和 onQueue 方法：
return new BroadcastMessage($data)
                ->onConnection('sqs')
                ->onQueue('broadcasts');

监听通知#
Echo.private('App.User.' + userId)
    .notification((notification) => {
        console.log(notification.type);
    });

自定义通知通道#
如果您想自定义应通知实体接收其广播通知的渠道，您可以定义一个 receiveBroadcastNotificationsOn 方法：
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * 用户接收的通知广播
     *
     * @return array
     */
    public function receivesBroadcastNotificationsOn()
    {
        return [
            new PrivateChannel('users.'.$this->id),
        ];
    }
}



短信通知#
先决条件#
在 Laravel 中发送短信通知是基于 Nexmo 服务的。在通过 Nexmo 发送短信通知前，你需要安装 nexmo/client Composer 包并在 config/services.php 配置文件中添加几个配置选项。你可以复制下面的配置示例来开始使用
'nexmo' => [
    'key' => env('NEXMO_KEY'),
    'secret' => env('NEXMO_SECRET'),
    'sms_from' => '15556666666',
],

sms_from 选项是短信消息发送者的手机号码。你可以在 Nexmo 控制面板中生成一个手机号码。


格式化短信通知#
// 如果通知支持以短信方式发送，你应该在通知类里定义一个 toNexmo 方法。这个方法将会收到一个 $notifiable 实体并返回一个 Illuminate\Notifications\Messages\NexmoMessage 实例：
/**
 * 获取通知的 Nexmo / 短信展示方式
 *
 * @param  mixed  $notifiable
 * @return NexmoMessage
 */
public function toNexmo($notifiable)
{
    return (new NexmoMessage)
                ->content('Your SMS message content');
}

Unicode 内容#
如果您的 SMS 消息包含 unicode 字符，您应该在构建 NexmoMessage 实例时调用 unicode 方法：
/**
 * 获取通知的 Nexmo / 短信展示方式
 *
 * @param  mixed  $notifiable
 * @return NexmoMessage
 */
public function toNexmo($notifiable)
{
    return (new NexmoMessage)
                ->content('Your unicode message')
                ->unicode();
}


自定义 From 号码#
// 如果你想通过一个手机号来发送某些通知，而这个手机号不同于你的 config/services.php 配置文件中指定的话，你可以在 NexmoMessage 实例中使用 from：
/**
 * 获取通知的 Nexmo / 短信展示方式
 *
 * @param  mixed  $notifiable
 * @return NexmoMessage
 */
public function toNexmo($notifiable)
{
    return (new NexmoMessage)
                ->content('Your SMS message content')
                ->from('15554443333');
}

路由短信通知#
当通过 nexmo 频道来发送通知的时候，通知系统会自动寻找通知实体的 phone_number 属性。如果你想自定义通知被发送的手机号码，你要在通知实体里定义一个 routeNotificationForNexmo 方法。
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Nexmo 频道的路由通知
     *
     * @return string
     */
    public function routeNotificationForNexmo()
    {
        return $this->phone;
    }
}


Slack 通知#
先决条件#
通过 Slack 发送通知前，你必须通过 Composer 安装 Guzzle HTTP 库：
composer require guzzlehttp/guzzle

格式化 Slack 通知#
如果通知支持被当做 Slack 消息发送，你应该在通知类里定义一个 toSlack 方法。这个方法将收到一个 $notifiable 实体并且返回一个 Illuminate\Notifications\Messages\SlackMessage 实例
// Slack 消息可以包含文本内容以及一个 「attachment」 用来格式化额外文本或者字段数组。我们来看个基本的 toSlack 例子：
/**
 * 获取通知的 Slack 展示方式
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    return (new SlackMessage)
                ->content('One of your invoices has been paid!');
}

自定义发件人和收件人#
您可以使用 from 和 to 方法来自定义发件人和收件人。 from 方法接受用户名和表情符号标识符，而 to 方法接受一个频道或用户名：
/**
 * 获取通知的 Slack 展示方式
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    return (new SlackMessage)
                ->from('Ghost', ':ghost:')
                ->to('#other')
                ->content('This will be sent to #other');
}

你也可以不使用表情符号，改为使用图片作为你的 logo：
/**
 * 获取通知的 Slack 展示方式
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    return (new SlackMessage)
                ->from('Laravel')
                ->image('https://laravel.com/favicon.png')
                ->content('This will display the Laravel logo next to the message');
}



Slack Attachments#
你可以给 Slack 消息添加 "附加项"。附加项提供了比简单文本消息更丰富的格式化选项。在这个例子中，我们将发送一条有关应用中异常的错误通知，它里面有个可以查看这个异常更多详情的链接：
/**
 * 获取通知的 Slack 展示方式。
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    $url = url('/exceptions/'.$this->exception->id);

    return (new SlackMessage)
                ->error()
                ->content('Whoops! Something went wrong.')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title('Exception: File Not Found', $url)
                               ->content('File [background.jpg] was not found.');
                });
}

附加项也允许你指定一个应该被展示给用户的数据的数组。给定的数据将会以表格样式展示出来，这能方便阅读：
/**
 * 获取通知的 Slack 展示方式
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    $url = url('/invoices/'.$this->invoice->id);

    return (new SlackMessage)
                ->success()
                ->content('One of your invoices has been paid!')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title('Invoice 1322', $url)
                               ->fields([
                                    'Title' => 'Server Expenses',
                                    'Amount' => '$1,234',
                                    'Via' => 'American Express',
                                    'Was Overdue' => ':-1:',
                                ]);
                });
}

Markdown 附件内容#
如果一些附件字段包含 Markdown ，您可以使用 markdown 方法指示 Slack 解析并将给定的附件字段显示为 Markdown 格式的文本：
**
 * 获取通知的 Slack 展示方式
 *
 * @param  mixed  $notifiable
 * @return SlackMessage
 */
public function toSlack($notifiable)
{
    $url = url('/exceptions/'.$this->exception->id);

    return (new SlackMessage)
                ->error()
                ->content('Whoops! Something went wrong.')
                ->attachment(function ($attachment) use ($url) {
                    $attachment->title('Exception: File Not Found', $url)
                               ->content('File [background.jpg] was **not found**.')
                               ->markdown(['title', 'text']);
                });
}



路由 Slack 通知#
要把 Slack 通知路由到正确的位置，你要在通知实体中定义 routeNotificationForSlack 方法。它返回通知要被发往的 URL 回调地址。URL 可以通过在 Slack 组里面添加 "Incoming Webhook" 服务来生成。
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Slack 频道的通知路由
     *
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return $this->slack_webhook_url;
    }
}

通知事件#
当通知发送后，通知系统就会触发 Illuminate\Notifications\Events\NotificationSent 事件。它包含了 「notifiable」 实体和通知实例本身。你应该在 EventServiceProvider 中注册监听器：
/**
 * 应用中的事件监听映射
 *
 * @var array
 */
protected $listen = [
    'Illuminate\Notifications\Events\NotificationSent' => [
        'App\Listeners\LogNotification',
    ],
];

//在事件监听器中，你可以访问事件中的 notifiable, notification, 和 channel 属性，来了解通知接收者和通知本身：
/**
 * 处理事件
 *
 * @param  NotificationSent  $event
 * @return void
 */
public function handle(NotificationSent $event)
{
    // $event->channel
    // $event->notifiable
    // $event->notification
}

自定义发送频道#
Laravel 提供了开箱即用的通知频道，但是你可能会想编写自己的驱动来通过其他频道发送通知。Laravel 很容易实现。首先，定义一个包含 send 方法的类。这个方法应该收到两个参数：$notifiable 和 $notification:
<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class VoiceChannel
{
    /**
     * 发送给定通知
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toVoice($notifiable);

        // 将通知发送给 $notifiable 实例
    }
}

一旦定义了通知频道类，你应该在所有通知里通过 via 方法来简单地返回这个频道的类名
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Channels\VoiceChannel;
use App\Channels\Messages\VoiceMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoicePaid extends Notification
{
    use Queueable;

    /**
     * 获取通知频道
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return [VoiceChannel::class];
    }

    /**
     * 获取通知的声音展示方式
     *
     * @param  mixed  $notifiable
     * @return VoiceMessage
     */
    public function toVoice($notifiable)
    {
        // ...
    }
}
