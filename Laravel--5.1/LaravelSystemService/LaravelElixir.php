<?php

#Laravel Elixir#
#简介
//Laravel Elixir 提供了简洁流畅的 API，让你能够在你的 Laravel 应用程序中定义基本的 Gulp 任务
//Elixir 支持许多常见的 CSS 与 JavaScrtip 预处理器，甚至包含了测试工具。使用链式调用，Elixir 让你流畅地定义开发流程，例如：
elixir(function(mix) {
    mix.sass('app.scss')
       .coffee('app.coffee');
});

#安装与配置
#安装 
Node#
//在开始使用 Elixir 之前，你必须先确定你的机器上有安装 Node.js。
node -v
//https://nodejs.org/download/

Gulp#
//接着，你需要全局安装 Gulp 的 NPM 扩展包：
npm install --global gulp

//最后的步骤就是安装 Elixir！在每一份新安装的 Laravel 代码里，你会发现根目录有个名为 package.json 的文件
npm install
//如果你是在 Windows 系统上或在 Windows 主机系统上运行 VM 进行开发，你需要在运行 npm install 命令时将 --no-bin-links 开启：
npm install --no-bin-links

#运行 Elixir
//Elixir 是创建于 Gulp 之上，所以要运行你的 Elixir 任务，只需要在命令行运行 gulp 命令。在命令增加 --production 标示会告知 Elixir 压缩你的 CSS 及 JavaScript 文件：
// 运行所有任务...
gulp

// 运行所有任务并压缩所有 CSS 及 JavaScript...
gulp --production

//监控资源文件修改#
//因为每次修改你的资源文件之后在命令行运行 gulp 命令会相当不便，因此你可以使用 gulp watch 命令
//此命令会在你的命令行运行并监控资源文件的任何修改。当发生修改时，新文件将会自动被编译：
gulp watch


#使用样式
项目根目录的 gulpfile.js 包含你所有的 Elixir 任务。Elixir 任务可以被链式调用起来，以定义你的资源文件该如何进行编译

#Less
//要将 Less 编译为 CSS，你可以使用 less 方法
//less 方法会假设你的 Less 文件被保存在 resources/assets/less 文件夹中
//默认情况下，此例子的任务会将编译后的 CSS 放置于 public/css/app.css
elixir(function(mix) {
    mix.less('app.less');
});

//你可能会想合并多个 Less 文件至单个 CSS 文件。同样的，生成的 CSS 会被放置于 public/css/app.css：
elixir(function(mix) {
    mix.less([
        'app.less',
        'controllers.less'
    ]);
});

//如果你想自定义编译后的 CSS 输出位置，可以传递第二个参数至 less 方法
elixir(function(mix) {
    mix.less('app.less', 'public/stylesheets');
});

// 指定输出的文件名称...
elixir(function(mix) {
    mix.less('app.less', 'public/stylesheets/style.css');
});

#Sass
//sass 方法让你能编译 Sass 至 CSS。Sass 文件的默认读取路径是 resources/assets/sass，你可以使用此方法
elixir(function(mix) {
    mix.sass('app.scss');
});
//同样的，如同 less 方法，你可以编译多个 Sass 文件至单个的 CSS 文件，甚至可以自定义生成的 CSS 的输出目录：
elixir(function(mix) {
    mix.sass([
        'app.scss',
        'controllers.scss'
    ], 'public/assets/css');
});

#纯 CSS
//如果你只是想将一些纯 CSS 样式合并成单个的文件，你可以使用 styles 方法。此方法的默认路径为 resources/assets/css 目录，而生成的 CSS 会被放置于 public/css/all.css
elixir(function(mix) {
    mix.styles([
        'normalize.css',
        'main.css'
    ]);
});
//当然，你也可以通过传递第二个参数至 styles 方法，将生成的文件输出至指定的位置：
elixir(function(mix) {
    mix.styles([
        'normalize.css',
        'main.css'
    ], 'public/assets/css');
});

#Source Maps
//Source maps 在默认情况下是开启的。因此，针对每个被编译的文件，同目录内都会伴随着一个 *.css.map 文件
//这个文件能够让你在浏览器调试时，可以追踪编译后的样式选择器至原始的 Sass 或 Less 位置
//如果你不想为你的 CSS 生成 source maps，你可以使用一个简单的配置选项关闭它们：
elixir.config.sourcemaps = false;

elixir(function(mix) {
    mix.sass('app.scss');
});

#使用脚本
Elixir 也提供了一些函数来帮助你使用 JavaScript 文件，像是编译 ECMAScript 6、编译 CoffeeScript、Browserify、压缩、及简单的串联纯 JavaScript 文件。
#CoffeeScript
coffee 方法可以用于编译 CoffeeScript 至纯 JavaScrip
//coffee 函数接收一个相对于 resources/assets/coffee 目录的 CoffeeScript 文件名字符串或数组，接着在 public/js 目录生成单个的 app.js 文件：
elixir(function(mix) {
    mix.coffee(['app.coffee', 'controllers.coffee']);
});

#Browserify
Elixir 还附带了一个 browserify 方法，给予你在浏览器引入模块及 ECMAScript 6 的有用的特性
//此任务假设你的脚本都保存在 resources/assets/js，并会将生成的文件放置于 public/js/main.js
elixir(function(mix) {
    mix.browserify('main.js');
});
//虽然 Browserify 附带了 Partialify 及 Babelify 转换器，但是只要你愿意，你可以随意安装并增加更多的转换器：
npm install aliasify --save-dev

elixir.config.js.browserify.transformers.push({
    name: 'aliasify',
    options: {}
});

elixir(function(mix) {
    mix.browserify('main.js');
});

#Babel
//babel 方法可被用于编译 ECMAScript 6 与 7 至纯 JavaScript
//此函数接收一个相对于 resources/assets/js 目录的文件数组，接着在 public/js 目录生成单个的 all.js 文件
elixir(function(mix) {
    mix.babel([
        'order.js',
        'product.js'
    ]);
});

#Scripts
//如果你想将多个 JavaScript 文件合并至单个文件，你可以使用 scripts 方法
//scripts 方法假设所有的路径都相对于 resources/assets/js 目录，且默认会将生成的 JavaScript 放置于 public/js/all.js：
elixir(function(mix) {
    mix.scripts([
        'jquery.js',
        'app.js'
    ]);
});

//如果你想多个脚本的集合合并成不同文件，你可以使用调用多个 scripts 方法。给予该方法的第二个参数会为每个串联决定生成的文件名称：
elixir(function(mix) {
    mix.scripts(['app.js', 'controllers.js'], 'public/js/app.js')
       .scripts(['forum.js', 'threads.js'], 'public/js/forum.js');
})

//如果你想合并指定目录中的所有脚本，你可以使用 scriptsIn 方法。生成的 JavaScript 会被放置在 public/js/all.js：
elixir(function(mix) {
    mix.scriptsIn('public/js/some/directory');
});

#复制文件与目录
//copy 方法可以复制文件与目录至新位置。所有操作路径都相对于项目的根目录：
elixir(function(mix) {
    mix.copy('vendor/foo/bar.css', 'public/css/bar.css');
});

elixir(function(mix) {
    mix.copy('vendor/package/views', 'resources/views');
});

#版本与缓存清除
//许多的开发者会在它们编译后的资源文件中加上时间戳或是唯一的 token，强迫浏览器加载全新的资源文件以取代提供的旧版本代码副本。你可以使用 version 方法让 Elixir 处理它们。
//version 方法接收一个相对于 public 目录的文件名称，接着为你的文件名称加上唯一的哈希值，以防止文件被缓存。举例来说，生成出来的文件名称可能像这样：all-16d570a7.css：
elixir(function(mix) {
    mix.version('css/all.css');
});
//在为文件生成版本之后，你可以在你的 视图 中使用 Laravel 的全局 elixir PHP 辅助函数来正确加载名称被哈希后的文件。elixir 函数会自动判断被哈希的文件名称：
<link rel="stylesheet" href="{{ elixir('css/all.css') }}">

//为多个文件生成版本
//你可以传递一个数组至 version 方法来为多个文件生成版本：
elixir(function(mix) {
    mix.version(['css/all.css', 'js/app.js']);
});
//一旦该文件被加上版本，你需要使用 elixir 辅助函数来生成哈希文件的正确链接
//切记，你只需要传递未哈希文件的名称至 elixir 辅助函数。此函数会自动使用未哈希的名称来判断该文件为目前的哈希版本
<link rel="stylesheet" href="{{ elixir('css/all.css') }}">

<script src="{{ elixir('js/app.js') }}"></script>

#BrowserSync
//当你对前端资源进行修改后，BrowserSync 会自动刷新你的网页浏览器
//你可以使用 browserSync 方法来告知 Elixir，当你运行 gulp watch 命令时启动 BrowserSync 服务器：
elixir(function(mix) {
    mix.browserSync();
});

//一旦你运行 gulp watch，就能使用连接端口 3000 启用浏览器同步并访问你的网页应用程序：http://homestead.app:3000
//如果你在本机开发所使用的域名不是 homestead.app，那么你可以传递一个 选项 的数组作为 browserSync 方法的第一个参数：
elixir(function(mix) {
    mix.browserSync({
        proxy: 'project.app'
    });
});


#调用既有的 Gulp 任务
//如果你需要在 Elixir 调用一个既有的 Gulp 任务，你可以使用 task 方法。
gulp.task('speak', function() {
    var message = 'Tea...Earl Grey...Hot';

    gulp.src('').pipe(shell('say ' + message));
});
//如果你希望在 Elixir 中调用这个任务，使用 mix.task 方法并传递该任务的名称作为该方法唯一的参数：
elixir(function(mix) {
    mix.task('speak');
});

//自定义监控器
//如果你想注册一个监控器让你的自定义任务能在每次文件改变时就运行，只需传递一个正则表达式作为 task 方法的第二个参数：
elixir(function(mix) {
    mix.task('speak', 'app/**/*.php');
});

#编写 Elixir 扩展功能
//如果你需要比 Elixir 的 task 方法更灵活的方案，你可以创建自定义的 Elixir 扩展功能
// 文件：elixir-extensions.js

var gulp = require('gulp');
var shell = require('gulp-shell');
var Elixir = require('laravel-elixir');

var Task = Elixir.Task;

Elixir.extend('speak', function(message) {

    new Task('speak', function() {
        return gulp.src('').pipe(shell('say ' + message));
    });

});

// mix.speak('Hello World');
// 注意，你的 Gulp 具体的逻辑必须被放置在 Task 第二个参数传递的构造器函数里面
// 可以将此扩展功能放置在 Gulpfile 的上方，取而代之也可以导出至一个自定义任务的文件

//举个例子，如果你将你的扩展功能放置在 elixir-extensions.js 文件中，那么你可以在 Gulpfile 中像这样引入该文件：
// 文件：Gulpfile.js

var elixir = require('laravel-elixir');

require('./elixir-extensions')

elixir(function(mix) {
    mix.speak('Tea, Earl Grey, Hot');
});


#自定义监控器#
//如果你想在运行 gulp watch 时能够重新触发你的自定义任务，你可以注册一个监控器：
new Task('speak', function() {
    return gulp.src('').pipe(shell('say ' + message));
})
.watch('./app/**');