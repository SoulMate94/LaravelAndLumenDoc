<?php

Laravel 的收费系统 Cashier#
简介#
Laravel Cashier 提供了直观、流畅的接口来接入 Stripe's and Braintree's 订阅付费服务

配置#


Stripe#

Composer#
首先, 将 Stripe 的 Cashier 包添加到您的依赖项中：
composer require "laravel/cashier":"~7.0"

服务提供者#
下一步, 在 config/app.php 配置文件中，注册 Laravel\Cashier\CashierServiceProvider 服务提供者.

数据库迁移#
在使用 Cashier 之前，我们需要 准备数据库. 我们需要向您的 users 表中添加几个列，并创建一个新的 subscriptions 表来保存所有客户的订阅：
Schema::table('users', function ($table) {
    $table->string('stripe_id')->nullable();
    $table->string('card_brand')->nullable();
    $table->string('card_last_four')->nullable();
    $table->timestamp('trial_ends_at')->nullable();
});

Schema::create('subscriptions', function ($table) {
    $table->increments('id');
    $table->integer('user_id');
    $table->string('name');
    $table->string('stripe_id');
    $table->string('stripe_plan');
    $table->integer('quantity');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('ends_at')->nullable();
    $table->timestamps();
});

一旦迁移文件建立好后，运行 Artisan 的 migrate 命令。

Billable 模型#
下一步, 需要添加 Billable trait 到你的模型定义
trait 提供各种方法让您执行常见的帐单任务，例如：创建订阅、应用优惠券和更新信用卡信息：
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Billable;
}

API Keys#
最后, 你需要在 services.php 配置文件中设置你的 Stripe key
 你可以在 Stripe 的控制面板获取到相关的 API keys。
'stripe' => [
    'model'  => App\User::class,
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],


Braintree#
Braintree 注意事项#
对于许多操作，Cashier 的 Stripe 和 Braintree 实现都是一样的。这两项服务都提供信用卡支付服务，但 Braintree 支持通过 PayPal 支付。然而，Braintree 也缺少一些由 Stripe 支持的功能。当你决定使用 Stripe 或 Braintree 时，你应该记住以下几点：
Braintree 支持 PayPal ，而 Stripe 不支持.
Braintree 不支持订阅的 increment 和 decrement 方法。这是 Braintree 的限制，而不是 Cashier 限制。
Braintree 不支持基于百分比的折扣。这是 Braintree 的限制，而不是 Cashier 限制。

Composer#
首先，将 Braintree 的 Cashier 包添加到您的依赖项：
composer require "laravel/cashier-braintree":"~2.0"

服务提供者#
下一步, 在 config/app.php 配置文件中，注册 Laravel\Cashier\CashierServiceProvider 服务提供者：
Laravel\Cashier\CashierServiceProvider::class


货币配置#


订阅#


创建订阅#


检查订阅状态#


修改订阅计划#


订阅量#


订阅税额#


取消订阅#


恢复订阅#


更新信用卡#


试用订阅#


有信用卡的情况下#


在没有信用卡的情况下#


处理 Stripe Webhooks#


定义 Webhook 事件处理程序#


订阅失败#


处理 Braintree Webhooks#


定义 Webhook 事件处理程序#


订阅失败#


一次性收费#


发票#


生成发票的 PDFs#

