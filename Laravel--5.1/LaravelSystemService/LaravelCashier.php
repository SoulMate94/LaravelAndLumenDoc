<?php

#Laravel Cashier#
#简介
//Laravel Cashier 给 Stripe 的订购交易服务提供了生动流畅的接口。它基本上处理了所有会让人恐惧的订购管理的相关逻辑。除了基本的订购管理外，Cashier 还可以处理优惠券，订购转换，管理订购「数量」、取消宽限期，甚至生成发票的 PDF 文件。

配置#
Composer 安装#
//首先，将 Cashier 扩展包添加至 composer.json 文件并运行 composer update 命令：
"laravel/cashier": "~5.0" (For Stripe SDK ~2.0, and Stripe APIs on 2015-02-18 version and later)
"laravel/cashier": "~4.0" (For Stripe APIs on 2015-02-18 version and later)
"laravel/cashier": "~3.0" (For Stripe APIs up to and including 2015-02-16 version)

服务提供者#
接着，在 app 配置文件中注册 Laravel\Cashier\CashierServiceProvider 服务提供者。

数据库迁移#
使用 Cashier 前，我们需要增加几个字段到数据库，你可以使用 cashier:table Artisan 命令，创建迁移文件来添加必要字段
//举个例子，若要增加字段到 users 数据表，使用命令：
php artisan cashier:table users
//创建完迁移文件后，只需运行 migrate 命令即可。

模型设置#
//接着，将 Billable trait 和适当的日期访问器添加至你的模型定义中：
use Laravel\Cashier\Billable;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class User extends Model implements BillableContract
{
    use Billable;

    protected $dates = ['trial_ends_at', 'subscription_ends_at'];
}
//你的模型中 $dates 属性里添加的字段会指定 Eloquent 必须将该字段返回为 Carbon 或 DateTime 实例，而不是原始字符串。

Stripe 密钥#
//最后，在你的 services.php 配置文件中设置 Stripe 密钥：
'stripe' => [
    'model'  => 'User',
    'secret' => env('STRIPE_API_SECRET'),
],

#订购
#创建订购
//要创建一个订购，首先要获取可交易的模型实例，这通常会是 App\User 的实例。一旦你获取了模型实例，你可以使用 subscription 方法来管理模型的订购：
$user = User::find(1);

$user->subscription('monthly')->create($creditCardToken);
//create 方法会自动创建与 Stripe 的交易，以及将 Stripe 客户 ID 和其它相关帐款信息更新到数据库
//如果你的方案有在 Stripe 设置试用期，试用的截止日期也会自动保存至用户的记录
//如果你想要实现试用期，但是你想完全用应用程序来管理试用期，而不是在 Stripe 里设置，那么你必须手动设置试用截止日期：
$user->trial_ends_at = Carbon::now()->addDays(14);

$user->save();

//额外用户详细数据
//如果你想自定义额外的顾客详细数据，可以将数据数组作为 create 方法的第二个参数传入：
$user->subscription('monthly')->create($creditCardToken, [
    'email' => $email, 'description' => '我们的第一个客户'
]);

//优惠券
//如果你想在创建订购的时候使用优惠券，可以使用 withCoupon 方法
$user->subscription('monthly')
     ->withCoupon('code')
     ->create($creditCardToken);

#确认订购状态
//首先，当用户拥有有效订购时，subscribed 方法会返回 true，即使该订购目前在试用期间：
if ($user->subscribed()) {
    //
}     

//subscribed 方法很适合用在 路由中间件，让你可以通过用户的订购状态，过滤访问路由及控制器：
public function handle($request, Closure $next)
{
    if ($request->user() && ! $request->user()->subscribed()) {
        // 此用户不是付费用户...
        return redirect('billing');
    }

    return $next($request);
}

//如果你想确认用户是否还在他们的试用期内，你可以使用 onTrial 方法。利用此方法可以在页面的顶部展示正在试用期内的提示：
if ($user->onTrial()) {
    //
}
//onPlan 方法可以用 Stripe ID 来确认用户是否订购某方案：
if ($user->onPlan('monthly')) {
    //
}

//取消订购状态
//若要确认用户是否曾经订购过，但现在已取消订购，你可以使用 cancelled 方法：
if ($user->cancelled()) {
    //
}

//你可能想确认用户是否已经取消订购，但是订购还在他们完全到期前的「宽限期」内。例如，如果用户在三月五号取消了订购，但是服务会到三月十号才过期。那么用户到三月十号前都是「宽限期」。注意，subscribed 方法在这个期间仍然会返回 true。
if ($user->onGracePeriod()) {
    //
}

//everSubscribed 方法可以用来确认用户是否订购过应用程序里的方案：
if ($user->everSubscribed()) {
    //
}


#改变方案
//当用户在你的应用程序订购之后，他们有时可能想更改自己的订购方案
//使用 swap 方法可以把用户转换到新的订购方案。举个例子，我们可以简单的将用户切换至 premium 订购方案：
$user = App\User::find(1);

$user->subscription('premium')->swap();

//如果用户还在试用期间，试用服务会跟之前一样可用。如果订单有「数量」，也会和之前一样。当改变方案时，你也可以使用 prorate 方法以表示该费用是按照比例计算。此外，你可以使用 swapAndInvoice 方法马上开发票给改变方案的用户：
$user->subscription('premium')
            ->prorate()
            ->swapAndInvoice();

#订购数量

#订购税金

#取消订购

#恢复订购

#处理 Stripe Webhooks

#订购失败

#其它 Webhooks

#一次性收费

#发票

#生成发票的 PDF
