<?php

#Composer安装

//Laravel非常依赖Composer，Composer是包依赖管理工具，听说在日后实际开发中可以使用Composer引用一些好用的Packages来方便我们写代码，这么好用的东西，更多关于composer的功能可以直接看composer的官网：http://www.phpcomposer.com/

##首先：要使用Composer 只需要下载一个名为composer.phar文件就成，打开终端执行：
curl -sS https://getcomposer.org/installer | php

##这样我们就已将composer.phar下载下来了，但是这样每次使用composer时总是需要敲：php composer.phar 。很麻烦，我们直接将composer.phar移动到可执行目录下并取名为composer：
mv composer.phar /usr/local/bin/composer

//完成移动后我们可以在终端中敲下composer看看是否能够正常使用，这样我们就安装成功了，如果想要了解更多composer的知识可以看官方文档，我在这里就不做介绍了。



#创建Laravel项目
//当我们成功安装了composer后 我们就可以创建使用Laravel框架的项目了，首先 使用终端cd到你想保存项目的文件夹后执行：
composer create-project laravel/laravel Project-name 5.1.1 
//代码解读：composer 创建项目 laravel/laravel '项目名' 'laravel的版本号 不声明则使用最新版本的laravel'。

//下载好项目后我们cd到项目所在目录，我们使用laravel自带的artisan命令开启本地服务：
php artisan serve
//然后我们在浏览器中输入：http://localhost:8000/   就可以看见漂亮的laravel项目主界面了。