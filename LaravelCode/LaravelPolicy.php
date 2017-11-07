<?php

#Policy
##所有权限不可能都放在AuthServiceProvider中，这时候我们需要使用到Policy。

#第一步 创建policy
//我们使用artisan命令来创建：
php artisan make:policy PostPolicy 

//进入路径打开这个policy，app\policy：
class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}


#第二步 添加一个权限
//我们在创建的policy文件中 新建一个方法：
    public function update(User $user, Post $post)
    {
        return $user->owns($post);
    }
//User的owns方法是这样的：
    public function owns(Post $post)
    {
        return $post->user_id == $this->id;
    } 

#第三步 使用这个policy
//进入到AuthServiceProvider中 修改policies数组进行注册：
    protected $policies = [
        'App\Post' => 'App\Policies\PostPolicy',
    ];

//之后我们就可以在PostsController中使用了：
    public function index()
    {
        \Auth::loginUsingID(1);
        $post = Post::findOrFail(1);
        if (Gate::denies('update', $post)){
            abort(403, 'sorry');
        }
        return $post->title;
//        return view('show', compact('post'));
    }
//当然可以使用authorize方法：
    public function index()
    {
        \Auth::loginUsingID(1);
        $post = Post::findOrFail(1);
//        if (Gate::denies('update', $post)){
//            abort(403, 'sorry');
//        }
        $this->authorize('update', $post);
        return $post->title;
//        return view('show', compact('post'));
    }



#在view中使用
//只需要在@can中修改成我们在policy中定义的方法名就可以了：
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>{{ $post->title }}</h1>
    @can('update', $post)
    <a href="#">编辑</a>
    @endcan
</body>
</html>