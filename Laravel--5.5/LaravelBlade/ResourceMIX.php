<?php

Laravel 的资源任务编译器 Laravel Mix#
简介#
Laravel Mix 提供了简介且可读性高的 API，用于使用几个常见的 CSS 和 JavaScript 预处理器为应用定义 Webpack 构建步骤
mix.js('resources/assets/js/app.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css');

如果你曾经对于使用 Webpack 及编译资源感到困惑和不知所措，那么你会爱上 Laravel Mix


安装 & 配置#
安装 Node#
在开始使用 Mix 之前，必须先确保你的机器上安装了 Node.js 和 NPM。
node -v
npm -v
// 默认情况下，Laravel Homestead 会包含你所需的一切。当然，如果你没有使用 Vagrant，就使用简单的图形安装程序从 其下载页面 安装最新版的 Node 和 NPM。

Laravel Mix#
然后就只需要安装 Laravel Mix。在新的 Laravel 项目中，你可以在目录结构的根目录中找到一个 package.json 文件
npm install

如果你正在 Windows 系统上进行开发，或者在 Windows 主机系统上运行虚拟机，那你要在运行 npm install 命令时使用 --no-bin-links：
npm install --no-bin-links


运行 Mix#
Mix 是位于 Webpack 顶部的配置层，所以要运行 Mix 任务，只需要执行默认的Laravel package.json 文件中包含的一个 NPM 脚本：
// 运行所有 Mix 任务...
npm run dev

// 运行所有 Mix 任务并缩小输出..
npm run production


监控资源文件修改#
npm run watch 会在你的终端里持续运行，监控所有相关的资源文件以便进行更改
Webpack 会在检测到文件更改时自动重新编译资源：
npm run watch

在某些环境中，当文件更改时，Webpack 不会更新。如果系统出现这种情况，请考虑使用 watch-poll 命令
npm run watch-poll

webpack.mix.js 文件是所有资源编译的入口点
可以把它看作是 Webpack 中的轻量级配置封装清单。Mix 任务可以一起被链式调用，以精确定义资源的编译方式。


使用样式#

Less#
less 方法可以用于将 Less 编译为 CSS
在 webpack.mix.js 中这样写，可以将 app.less 编译到 public/css/app.css 中。
mix.less('resources/assets/less/app.less', 'public/css');

可以多次调用 less 方法来编译多个文件:
mix.less('resources/assets/less/app.less', 'public/css')
   .less('resources/assets/less/admin.less', 'public/css');

如果要自定义编译的 CSS 的文件名，可以将一个完整的路径作为第二个参数传给 less 方法:
mix.less('resources/assets/less/app.less', 'public/stylesheets/styles.css');

如果你需要重写 底层 Less 插件选项，你可以将一个对象作为第三个参数传到 mix.less()：
mix.less('resources/assets/less/app.less', 'public/css', {
    strictMath: true
})



Sass#
sass 方法可以将 Sass 编译为 CSS。用法如下所示：
mix.sass('resources/assets/sass/app.scss', 'public/css');

跟 less 方法一样，你可以将多个 Sass 文件编译到各自的 CSS 文件中，甚至可以自定义生成的 CSS 的输出目录：
mix.sass('resources/assets/sass/app.sass', 'public/css')
   .sass('resources/assets/sass/admin.sass', 'public/css/admin');

另外，Node-Sass 插件选项 也同样可以作为第三个参数：
mix.sass('resources/assets/sass/app.sass', 'public/css', {
    precision: 5
});



Stylus#
类似于 Less 和 Sass，stylus 方法可以将 Stylus 编译为 CSS：
mix.stylus('resources/assets/stylus/app.styl', 'public/css');

你也可以安装其他的 Stylus 插件，例如 Rupture
首先，通过 NPM (npm install rupture) 来安装插件，然后在调用 mix.stylus() 时引用它：
mix.stylus('resources/assets/stylus/app.styl', 'public/css', {
    use: [
        require('rupture')()
    ]
});


PostCSS#
Laravel Mix 自带了一个用来转换 CSS 的强大工具 PostCSS
Mix 利用了流行的 Autoprefixer 插件来自动添加所需要的 CSS3 浏览器引擎前缀
mix.sass('resources/assets/sass/app.scss', 'public/css')
   .options({
        postCss: [
            require('postcss-css-variables')()
        ]
   });

纯 CSS#
如果你只是想将一些纯 CSS 样式合并成单个的文件, 你可以使用 styles 方法。
mix.styles([
    'public/css/vendor/normalize.css',
    'public/css/vendor/videojs.css'
], 'public/css/all.css');


URL 处理#
.example {
    background: url('../images/example.png');
}

默认情况下，Laravel Mix 和 Webpack 会找到 example.png，然后把它复制到你的 public/images 目录下，然后重写生成的样式中的 url()。这样，你编译之后的 CSS 会变成：
.example {
  background: url(/images/example.png?d41d8cd98f00b204e9800998ecf8427e);
}

但如果你想以你喜欢的方式配置现有的文件夹结构，可以禁用 url() 的重写：
mix.sass('resources/assets/app/app.scss', 'public/css')
   .options({
      processCssUrls: false
   });

在你的 webpack.mix.js 文件像上面这样配置之后，Mix 将不再匹配 url() 或者将资源复制到你的 public 目录
换句话说，编译后的 CSS 会跟原来输入的一样：

资源映射#
默认情况下资源映射是禁用的，可以在 webpack.mix.js 文件中调用 mix.sourceMaps() 方法来开启它
尽管它会带来一些编译／性能的成本，但在使用编译资源时，可以为使用浏览器的开发人员工具提供额外的调试信息：
mix.js('resources/assets/js/app.js', 'public/js')
   .sourceMaps();



使用 JavaScript#
使用脚本#
mix.js('resources/assets/js/app.js', 'public/js');
仅仅这上面的一行代码，就支持：
    ES 2015 语法
    模块
    编译 .vue 文件
    生产环境压缩代码



提取 Vendor#
提取依赖库#
将应用程序特定的 JavaScript 与依赖库捆绑在一起有个潜在的缺点，会使得长期缓存更加困难
mix.js('resources/assets/js/app.js', 'public/js')
   .extract(['vue'])


React#


原生 JS#


自定义 Webpack 配置#


复制文件 & 目录#


版本控制 & 缓存清除#


Browsersync 重新加载#


环境变量#


通知#

