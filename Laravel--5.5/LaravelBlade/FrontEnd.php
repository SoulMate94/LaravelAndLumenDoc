<?php

Laravel 的前端资源处理 JavaScript＆CSS 构建#
简介#

移除前端脚手架#
php artisan preset none


编写 CSS#
在编译 CSS 代码之前，需要你先使用 Node 包管理工具(NPM) 来安装项目的前端依赖：
npm install

使用 npm install 成功安装依赖后，你就可以使用 Laravel Mix 来将 SASS 文件编译为纯 CSS
npm run dev 命令会处理 webpack.mix.js
文件中的指令。通常情况下，编译好的 CSS 代码会被放置在 public/css 目录：
npm run dev

Laravel 自带的 webpack.mix.js 默认会编译 resources/assets/sass/app.scss SASS 文件


编写 JavaScript#
在项目根目录中的 package.json 可以找到应用程序的所有 JavaScript 依赖。
它和 composer.json 文件类似，不同的是它指定的是 JavaScript 的依赖而不是 PHP 的依赖。使用 Node 包管理器 (NPM) 来安装这些依赖包：
npm install

安装依赖之后，就可以使用 npm run dev 命令来 编译资源文件
Webpack 是一个为现代 JavaScript 应用而生的模块构建工具。当你运行 npm run dev 命令时，Webpack 会执行 webpack.mix.js 文件中的指令：
npm run dev

默认情况下，Laravel 的 webpack.mix.js 会编译 SASS 文件和 resources/assets/js/app.js 文件。
你可以在 app.js 文件中注册你的 Vue 组件，或者如果你更喜欢其他的框架，请配置自己的 JavaScript 应用程序。编译好的 JavaScript 文件通常会放置在 public/js 目录。


app.js 会加载 resources/assets/js/bootstrap.js 文件来启动并 配置 Vue、Axios、jQuery 以及其他的 JavaScript 依赖


编写 Vue 组件#
新 Laravel 程序默认会在 resources/assets/js/components 中包含一个 Example.vue 的 Vue 组件

Example.vue 文件是在同一文件中定义其 JavaScript 和 HTML 模板的 单文件 Vue 组件 的示例。它为构建 JavaScript 驱动的应用程序提供了非常方便的方法。这个示例组件已经在 app.js 文件中注册：

Vue.component('example', require('./components/Example.vue'));

在应用程序中使用组件，你只需要简单的将其放到你的 HTML 模板之中。

例如，运行 Artisan 命令 make:auth 去生成应用的用户认证和注册的框架页面后，可以把组件放到 home.blade.php Blade 模板中：
@extends('layouts.app')

@section('content')
    <example></example>
@endsection


!!谨记，每次修改 Vue 组件后都应该运行 npm run dev 命令。或者，你可以使用 npm run watch 命令来监控并在每次文件被修改时自动重新编译组件。


使用 React#
Laravel 很容易就能将 Vue 脚手架替换为 React 脚手架
在任何新建的 Laravel 应用程序下，你可以用 preset 命令加 react 选项:
php artisan preset react

该命令将移除 Vue 脚手架并用 React 脚手架替换， 包括 Laravel 自带的示例组件。
