<?php

#将控制权限存储到数据库
//一般要做一个完整的网站我们需要把一系列的管理权限存储到数据库中，这样就可以更加灵活的使用用户控制权限，首先我们来创建两个模型 permission(权限)和role(角色)

php artisan make:model Permission 
php artisan make:model Role 

#逻辑关系
//上面我们创建了权限和角色模型，现在我们来生成对应的数据表，但是要先想清楚它们之间的关系，一个权限可以被很多个用户使用，一个用户又可以有很多的权限，很明显这是一个多对多的关系，那么我们就需要在迁移文件中创建第三张关系表。

//为了方便我就在一个migration中创建3张表咯：
php artisan make:migration create_permission_role_table --create=roles

 public function up()
    {
        // 创建角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');                     // 角色的名字,比如 admin 和 number
            $table->string('lable')->nullable();        // 角色的标签(可空)
            $table->timestamps();
        });

        // 创建权限表
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');                     // 权限的名字,比如 update 和 delete
            $table->string('lable')->nullable();        // 权限的标签(可空)
            $table->timestamps();
        });

        // 创建多对多关联表
        Schema::create('permission_role', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();

            // 声明permisstion_id外键
            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');
            // 声明role_id外键
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');

            // 设置主键
            $table->primary(['permission_id','role_id']);
        });
    }

//这样我们的多对多关系就设置好了，但是还不够，我们需要对user表和roles表添加关系，所以我们在添加一张表
// 创建user和role的关联表
        Schema::create('user_role', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();

            // 声明user_id外键
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            // 声明role_id外键
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            // 设置主键
            $table->primary(['user_id','role_id']);
        });
//现在我们就可以生成表啦：
php artisan migrate 

#在模型中声明多对多关系
//刚刚在表中实现了多对多关系，现在我们在模型中来创建多对多的关系，首先是permission模型：

#Permission模型
class Permission extends Model
{
    public function roles()
    {
        // 这里使用belongsToMany而不是belongsTo,因为它们是多对多关系
        return $this->belongsToMany(Role::class);
    }
}


#Role模型
class Role extends Model
{
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
//在Role中我们在声明一个方法：
    // 给予权限
    public function givePermission(Permission $permission)
    {
        return $this->permissions()->save($permission);
    }


#User模型
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }


#生成数据
//我们在tinker中生成数据，进入tinker后编写：
>>> namespace App;
=> null
>>> $role = new Role();
=> App\Role {#665}
>>> $role->name = 'admin';
=> "admin"
>>> $role->lable = 'Admin';
=> "Admin"
>>> $role->save();
=> true

>>> $permission = new Permission;
=> App\Permission {#669}
>>> $permission->name = 'edit_form';
=> "edit_form"
>>> $permission->lable = 'Edit The Form';
=> "Edit The Form"
>>> $permission->save();
=> true

#以上已经在数据库中添加了两条数据，但是并没有在关联表中添加，不过我们已经声明了givePermission方法，我们在tinker中直接添加：

>>> $role->givePermission($permission);

#观察我们的数据库，我们现在已经添加了关联关系。


#开始测试
//我们现在就来添加控制权限，打开AuthServiceProvider，进入boot方法添加以下代码：
public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);
        
        // 循环取出权限
        foreach (Permission::with('roles')->get() as $permission){
            $gate->define($permission->name, function (User $user) use ($permission){
                return $user->hasRole($permission->roles);
            });
        }
    }

//在user中声明hasRole方法：
public function hasRole($role)
{
    if (is_string($role)){
        // 是否存在这个字符串(名字)
        return $this->roles->contains($role);
    }
    return !! $role->intersect($this->roles)->count();
}

//只是我们现在的user_role表还没有数据 我们在tinker创建下：
>>> $role = App\Role::first();
=> App\Role {#659
     id: 1,
     name: "admin",
     lable: "Admin",
     created_at: "2017-03-25 04:54:55",
     updated_at: "2017-03-25 04:54:55",
   }

>>> $user = App\User::first();
=> App\User {#661
     id: 1,
     name: "Dorcas Johnston",
     email: "vdaugherty@example.com",
     created_at: "2017-03-25 04:53:00",
     updated_at: "2017-03-25 04:53:00",
   }

>>> $user->roles()->save($role);
=> App\Role {#659
     id: 1,
     name: "admin",
     lable: "Admin",
     created_at: "2017-03-25 04:54:55",
     updated_at: "2017-03-25 04:54:55",
   }

//我们返回view来查看结果：
    public function index()
    {
        \Auth::loginUsingID(2);
        $post = Post::findOrFail(1);
        return view('show', compact('post'));
    }
//show.blade.php的代码如下：
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>{{ $post->title }}</h1>
    @can('edit_form')
    <a href="#">编辑</a>
    @endcan
</body>
</html>
//我们登陆的是id为2的用户，这个用户没有admin的角色 所以不会显示编辑链接，但是我们登陆id为1的用户 就会显示编辑链接。