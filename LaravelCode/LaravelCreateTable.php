<?php

1.创建建表文件
2.生成表
3.创建填充表数据的文件
4.生成数据
5.假如要新增表，那么在建好建表的文件后，执行php artisan migrate，会提示xxx表已经存在，需要先回滚
6.回滚完毕后，再重复1~4


1.创建建表文件
　　php artisan make:migration create_comments_table

　　//打开database/migrations/xxx_create_comments_table.php：

    public function up()
    {
        Schema::create('comments',function(Blueprint $table){
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('article_id');
            $table->integer('user_id');
            $table->string('content');
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
        Schema::dropIfExists('comments');
    }

2.生成表
php artisan migrate


3.创建填充表数据的文件
php artisan make:seed ReplyTableSeeder


　　1).打开：database/seeds/CommentsTableSeeder.php

	use Illuminate\Database\Seeder;

	class CommentsTableSeeder extends Seeder
	{
	    /**
	     * Run the database seeds.
	     *
	     * @return void
	     */
	    public function run()
	    {
	        factory(\App\Models\Comment::class)->times(30)->create(); // 表示创建30条数据。factory方法对应第三步
	    }
	}

	2).打开database\seeds\DatabaseSeeder.php


	use Illuminate\Database\Seeder;

	class DatabaseSeeder extends Seeder
	{
	    /**
	     * Run the database seeds.
	     *
	     * @return void
	     */
	    public function run()
	    {
	         $this->call(CommentsTableSeeder::class); // 会调用CommentsTableSeeder的run方法
	　　}
	}

　　3).打开database\factories\ModelFactory.php

	$factory->define(App\Models\Comment::class, function (Faker\Generator $faker) {
	    $user = DB::table('users')->select('id')->orderBy(DB::raw('RAND()'))->first();
	    if(empty($user))
	    {
	        $user->id = 0;
	    }

	    $article = DB::table('articles')->select('id')->orderBy(DB::raw('RAND()'))->first();
	    if(empty($article))
	    {
	        $article->id = 0;
	    }

	    return [
	        'user_id'=>$user->id, // user表随机查询
	        'article_id'=>$article->id, // 从article表u随机查询
	        'content' => '内容:'.$faker->text, // faker用法自寻，或转到vendor\fzaninotto\faker\src\Faker\Generator.php，看文件开头的注释
	    ];
	});

 　　4).如何让faker填充中文

　　　　打开app\Providers\AppServiceProvider.php：

    public function boot()
    {
        \Carbon\Carbon::setLocale('zh'); // 针对时间包，转化后为中文的时间

        //生成中文数据
        $this->app->singleton(FakerGenerator::class, function() {
            return FakerFactory::create('zh_CN');
        });
    }

    !! 注：设置后faker属性仍是英文，是因为包里面就没有中文数据

4.生成数据
php artisan db:seed


5.假如要新增表，那么在建好建表的文件后，执行php artisan migrate，会提示xxx表已经存在，需要先回滚
php artisan migrate:rollback // 会调用建表文件中的down方法，把数据库结构恢复到最初状态

!!如果是修改了迁移文件 可以先删除数据表里面的migrations的记录和数据表


6.回滚完毕后，再重复1~4