<?php

Laravel 数据库之：数据填充#
简介#
Laravel 可以用 seed 类轻松地为数据库填充测试数据。所有的 seed 类都存放在 database/seeds 目录下

你可以任意为 seed 类命名，但是应该遵守类似 UsersTableSeeder 的命名规范


可以在这个类中使用 call 方法来运行其它的 seed 类来控制数据填充的顺序。


编写 Seeders#
可以通过运行 make:seeder Artisan 命令 来生成一个 Seeder。所有由框架生成的 seeders 都将被放置在 database/seeds 目录下：
php artisan make:seeder UsersTableSeeder

一个 seeder 类只包含一个默认方法：run。这个方法在 db:seed Artisan 命令 被调用时执行。在 run 方法里你可以为数据库添加任何数据。你也可以用 查询语句构造器 或 Eloquent 模型工厂 来手动添加数据
<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * 运行数据库填充
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => str_random(10),
            'email' => str_random(10).'@gmail.com',
            'password' => bcrypt('secret'),
        ]);
    }
}

使用模型工厂#
使用 factory 这个辅助函数来向数据库中添加记录。
/**
 * 运行数据库填充
 *
 * @return void
 */
public function run()
{
    factory(App\User::class, 50)->create()->each(function ($u) {
        $u->posts()->save(factory(App\Post::class)->make());
    });
}


调用其他 Seeders#
在 DatabaseSeeder 类中，你可以使用 call 方法来运行其他的 seed 类
为避免单个 seeder 类过大，可使用 call 方法将数据填充拆分成多个文件。只需简单传递要运行的 seeder 类名称即可：
/**
 * 运行数据库填充
 *
 * @return void
 */
public function run()
{
    $this->call(UsersTableSeeder::class);
    $this->call(PostsTableSeeder::class);
    $this->call(CommentsTableSeeder::class);
}

运行 Seeders#
在默认情况下，db:seed 命令将运行可以用来调用其他填充类的 DatabaseSeeder 类。但是可以用 --class 选项来单独运行一个特定的 seeder 类：

php artisan db:seed

php artisan db:seed --class=UsersTableSeeder

也可以使用会先回滚再重新运行所有迁移的 migrate:refresh 命令来填充数据库。这个命令在彻底重构数据库时非常有用：

php artisan migrate:refresh --seed