<?php

#服务容器

Laravel 服务容器是管理类依赖与运行依赖注入的强力工具。依赖注入是个花俏的名词，事实上是指：类的依赖通过构造器或在某些情况下通过「setter」方法「注入」。


#获取服务容器
Laravel\Lumen\Application 实例是 Illuminate\Container\Container 的扩展，所以你可以当做服务容器来使用。

//通常我们会在 服务提供者 注册我们的容器解析规则。当然，你可以使用 bind, singleton, instance, 等容器提供的方法。请记住，所有的方法使用都在 Laravel 完整的服务容器文档 中有所记载


#解析实例
//想要从服务容器中解析实例，你可以在大部分的功能类里自动解析（依赖注入），如控制器，中间件等。或者你也可以使用 app 函数在应用程序的任意地方进行解析：
$instance = app(Something::class);