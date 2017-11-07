<?php

#配置数据库和Migration
//在laravel中 我们可以在项目根目录的.env文件中更改我们的配置。
// 打开.env文件后找到DB为前缀的几个字段，来更改我们的配置，如果想做更多的配置 可以到\config\database.php中修改。
// 配置好后我们来初始化数据库，使用laravel自带的迁移来生成user和password表：
php artisan migrate
//执行此命令后会发现数据库多了user和password表，这样我们的数据库就配置好了。


#数据库迁移--migrations
//migration是数据库迁移，也可称作为版本控制，这样别人下载你的源码 有了这些migration文件 只需要执行
//php artisan migrate 	//命令就可以生成所有的表，这样很方便。


#创建迁移文件
php artisan make:migration create_articles_table --create=articles 

//我们创建的migration文件命名应该要明确，后面的--create=articles   声明了要创建一张articles表。

// 在\database\migrations\中我们可以看到我们刚刚创建的migration文件：

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('articles');
    }
}


//其中有两个方法：up和down，up没什么好说的，就是执行migrate命令时要装载的字段，down就是回滚时要做什么操作。
//执行命令：
php artisan migrate
//打开表后。。。哎呀。。忘了加字段了，此时我们可以创建这么一个migration：
php artisan make:migration insert_content_to_articles --table=articles

//此时的 --table=articles  是说明 我们要对articles这张表做些修改，编辑这个migration：

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertContentToArticles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            // 添加我们想要的字段
            $table->string('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            // 对应的回滚操作
            $table->dropColumn('content');
        });
    }
}

//执行migrate操作来看看操作是否成功了吧。


#回滚操作
//回滚上一次的迁移：
php artisan migrate:rollback
//回滚所有的迁移：
php artisan migrate:reset