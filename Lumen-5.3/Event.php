<?php

# 注册事件或侦听器
/*
protected $listen = [
    'App\Events\ExampleEvent' => [
        'App\Listeners\ExampleListener',
    ],
];
*/
# 触发事件
event(new ExampleEvent);

Event::fire(new ExampleEvent);