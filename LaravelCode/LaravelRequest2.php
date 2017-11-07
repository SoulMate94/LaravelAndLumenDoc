<?php

#模型--查询作用域

#查询作用域--scope
//laravel的模型是允许我们把一些常用的查询语句封装成方法的，这样也方便了我们的调用，不需要每次都要写条件语句，下面我们来看看这个scope的语法：

//首先打开模型文件 添加这个方法：
    // 创建scope方法
    public function scopeAsk($query){
        // 查找作者为ASK的所有文章数据
        return $query->where('author','ASK');
    }

//接下来我们调用的时候直接这样使用，直接写ask即可 省略scope：
$articles = \App\Models\Article::ask()->orderBy('id','desc')->get();
dd($articles);

//此外scope可以接收参数，并且可以串联使用，我们重新写两个scope：
    public function scopeShow($query){
        return $query->where('is_show',1);
    }
    // 动态传入参数的scope
    public function scopeAuthor($query,$author){
        return $query->where('author',$author);
    }

//我们来看看串联使用和传入参数的使用吧：
$articles = \App\Models\Article::author('ASK')->show()->get();
dd($articles);
//可以看到，我们在一行代码中使用了两个scope，大大增加了效率。


#模型事件
laravel的模型支持几种事件：
	creating 		
	created
	updating
	updated
	saving
	saved
	deleting 		//deleting是在删除操作前执行
	deleted 		//deleted是在删除操作后执行

	//首先介绍下deleting和deleted，deleting是在删除操作前执行，deleted是在删除操作后执行
	
	//当模型创建时，依次触发事件的顺序是：saving → creating → created → saved。

	//当模型更新时，依次触发事件的顺序是：saving → updating → updated → saved。

//你可以在任何你喜欢的地方注册模型事件，这里我们选择在服务提供者AppServiceProvider的boot方法中注册，路径是：\app\Providers\AppServiceProvider

    public function boot()
    {
        Article::saving(function ($article){
            // 这里传入的参数 $article 就是我们创建的模型 我们可以得到它做一些逻辑处理。
            echo '模型正在保存中。。。';
            // 我们可以做些判断逻辑 如果要终止操作 返回false
//            if (...){
//                return false;
//            }
        });
        
        Article::creating(function ($article){
            echo '模型正在创建中。。。';
//            if (...){
//                return false;
//            }
        });
    }

//注意：我们在上面只演示了两个例子，如果你想要监听更多的事件，可以一一注册，当要终止操作时 在回调函数中 返回false即可中断任务