<?php

安装#
运行环境要求#
//系统要求为以下：
PHP >= 5.5.9
OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
Tokenizer PHP Extension

安装 Laravel#
//Laravel 使用 Composer 来管理代码依赖。所以，在使用 Laravel 之前，请先确认你的电脑上安装了 Composer。

通过 Laravel 安装工具#
//首先，使用 Composer 下载 Laravel 安装包：
composer global require "laravel/installer"
//请确定你已将 ~/.composer/vendor/bin 路径加到 PATH，只有这样系统才能找到 laravel 的执行文件

//一旦安装完成，就可以使用 laravel new 命令在指定目录创建一个新的 Laravel 项目，例如：laravel new blog

//将会在当前目录下创建一个叫 blog 的目录，此目录里面存放着新安装的 Laravel 和代码依赖。这个方法的安装速度比通过 Composer 安装要快上许多：
laravel new blog
//因为代码依赖是直接一起打包安装的。


通过 Composer Create-Project#
//除此之外，你也可以通过 Composer 在命令行运行 create-project 命令来安装 Laravel：
composer create-project --prefer-dist laravel/laravel blog "5.2.*"

配置信息#
//安装完成后，你应该指定 Web 服务器的网站根目录到 public 文件夹上。
//所有 Laravel 框架的配置文件都放置在 config 目录下


目录权限#
//安装 Laravel 之后，你必须设置一些文件目录权限。storage 和 bootstrap/cache 目录必须让服务器有写入权限。如果你使用 Homestead 虚拟机，那么这些权限已经被设置好了。


应用程序密钥#
//在你安装完 Laravel 后，首先需要做的事情是设置一个随机字符串的密钥。假设你是通过 Composer 或是 Laravel 安装工具安装的 Laravel，那么这个密钥已经通过 key:generate 命令帮你设置完成
//通常这个密钥会有 32 字符长。这个密钥可以被设置在 .env 环境文件中。如果你还没将 .env.example 文件重命名为 .env，那么你现在应该去设置下。
如果应用程序密钥没有被设置的话，你的用户 Session 和其它的加密数据都是不安全的！


其它设置#
//Laravel 几乎不需做任何其它设置就可以马上使用，但是建议你先浏览 config/app.php 文件和对应的文档，这里面包含着一些选项，如 时区 和 语言环境，你可以根据应用程序的情况来修改
你也可以设置 Laravel 的几个附加组件，像是：
	缓存
	数据库
	Session

//一旦 Laravel 安装完成，你应该立即 设置本机环境。
