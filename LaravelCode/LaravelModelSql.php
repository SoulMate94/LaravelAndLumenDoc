<?php

#准备工作
##1、生成表

	php artisan make:migration create_articles_table --create=articles

	<?php

	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Database\Migrations\Migration;

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
	            // 定义字段
	            $table->string('title');
	            $table->text('content');
	            $table->string('author');
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
	//php artisan migrate

##2、创建模型
	php artisan make:model Models/Article


#使用factory创建测试数据

##在实际开发中，我们需要创建表后就开始测试数据，这时我们需要很多数据 不可能一条一条的手动添加 这时需要用到模型工厂-ModelFactory
// 它位于：\database\factories\ModelFactory.php

<?php

$factory->define(App\User::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => str_random(10),
        'remember_token' => str_random(10),
    ];
});


//其中laravel默认为我们提供了一个User的factory，下面我们自己写一个:
$factory->define(App\Models\Article::class, function ($faker){
    // 返回我们想要的数据格式
    return [
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
        'author' => $faker->name,
    ];
});


//因为是批量插入，所以我们在模型中指明我们的白名单(之后详细讲)：
class Article extends Model
{
    protected $fillable = ['title','content','author'];		//白名单
}

//之后 我们在tinker里批量产出数据吧：
php artisan tinker

//呼出tinker之后 我们来写PHP代码吧：
factory(App\Models\Article::class,20)->create();
// enter之后 就生成了20条数据，如此便利神奇，实在好用有木有，快看看数据库把


#查询操作--取出模型数据
//我们可以使用all()方法来获取所有模型：
Route::get('/articles',function (){
    $articles = \App\Models\Article::all();
    dd($articles);		//die and dump
});
// 也可以使用条件语句过滤：
Route::get('/articles',function (){
    $articles = \App\Models\Article::where('id','<','5')->orderBy('id','desc')->get();
    dd($articles);
});
//我们也可以使用一些条件来取得一条数据：
$articles = \App\Models\Article::where('id',1)->first();
dd($articles);
//还有节俭的写法：
$articles = \App\Models\Article::find(1);
dd($articles);
//当记录没有找到会在页面上显示 null，如果我们想捕捉一些信息可以使用findorfail来抛出一个404页面：
$articles = \App\Models\Article::findOrfail(100);
dd($articles);


#聚合查询
//如果我们要对查询结果进行一些计算统计，可以用聚合函数，比如获取文章总数：
$count = \App\Models\Article::where('id','<=','11')->count();
dd($count);


