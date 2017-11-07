<?php


#Lumen 事件提供了简单的侦听器实现，允许你订阅和监听事件，事件类通常被保存在 app/Events 目录下，而它们的侦听器被保存在 app/Listeners 目录下。

//生成器
Lumen 中没有可用来生成事件监听器的命令，你可以复制 ExampleEvent 或者 ExampleListener 文件，这两个示例文件提供了基础的类结构，你可以作为参考。

#注册事件或侦听器
//你可以在 EventServiceProvider 注册所有的事件侦听器。listen 属性是一个数组，包含所有事件（键）以及事件对应的侦听器（值），你也可以根据需求增加事件到这个数组，例如：让我们增加 PodcastWasPurchased 事件：

/**
 * 应用程序的事件侦听器映射。
 *
 * @var array
 */
protected $listen = [
    'App\Events\ExampleEvent' => [
        'App\Listeners\ExampleListener',
    ],
];


#触发事件
如果要触发一个事件，你可以使用 Event facade 来发送一个事件的实例到 fire 方法。fire 方法将会发送事件到所有已经注册的侦听器上。或者可以使用 全局 event 辅助函数来触发事件：

event(new ExampleEvent);

Event::fire(new ExampleEvent);