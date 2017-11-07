<?php

# 获取服务容器
// Laravel\Lumen\Application 实例是 Illuminate\Container\Container 的扩展，所以你可以当做服务容器来使用。

# 解析实例
$instance = app(Something::class);