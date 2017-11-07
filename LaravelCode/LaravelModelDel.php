<?php

#delete删除模型
//获取到模型，执行delete方法就好：
    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        if ($article->delete()){
            echo '删除成功';
        }else{
            echo '删除失败';
        }
    }
//delete方法会返回一个bool值。


#destory删除模型
//相比较delete而言更加简洁，只要你知道id字段就可以使用：
    $delete = \App\Models\Article::destroy(3);
    // 也可以接受一个数组
   	//$delete = \App\Models\Article::destroy([1,5,6,7]);
    return "删除了{$delete}条数据";


#通过某些条件删除模型
//我们可以使用where来满足我们的业务逻辑：
   // deleted来记录删除了多少条数据
   $deleted = \App\Models\Article::where('id','<',10)->delete();


#今日焦点---软删除实现
//软删除其实是一种假删除，它的核心理念是 加入了一个标记字段，如果已经被软删除，这个字段的值就会改变 每次我们查询时就会过滤这条数据，看上去就像已经被删除了一样。

//在laravel中 以一个日期字段作为标识，这个日期字段是可以自定义的，我们一般使用 delete_at，当记录被软删除时 delete_at会赋予删除时间，否则它便是空的。 如果我们要使用软删除，需要做一些配置：


//要让Eloquent模型支持软删除，还要做一些设置。首先在模型类中要使用SoftDeletestrait，该trait为软删除提供一系列相关方法，具体可参考源码Illuminate\Database\Eloquent\SoftDeletes，此外还要设置$date属性数组，将deleted_at置于其中：


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;
    // 声明哪些属性是可以批量赋值的
    protected $fillable = ['title','content','author'];		//白名单

    protected $dates = ['delete_at']; //当记录被软删除时 delete_at会赋予删除时间
}


//我们当时创建数据表时并没有添加delete_at这个字段，下面我们来使用数据库迁移(migration)添加一下：
php artisan make:migration insert_delete_at_intro_articles --table=articles
	class InsertDeleteAtIntroArticles extends Migration
	{
	    /**
	     * Run the migrations.
	     *
	     * @return void
	     */
	    public function up()
	    {
	        Schema::table('articles', function (Blueprint $table) {
	            $table->softDeletes();
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
	            $table->dropSoftDeletes();
	        });
	    }
	}

//确定无误后执行migrate：
php artisan migrate

//这时我们的数据库就已经生成了这个字段了，紧接着 我们来测试下吧：
Route::get('/trashed', function (){
    $article = \App\Models\Article::findOrFail(20);
    $article->delete();
    if ($article->trashed()){
        return '软删除成功';
    }else{
        return '软删除失败';
    }
});

//现在我的数据库delete_at字段已经更新了，代码跑的通，而且我们再使用Article::all()方式获得所有模型数据时，已经看不到我们软删除的数据了，它已经被过滤掉了。


#查找已经被删除的数据
//如果我们想要得到已经被删除的数据可以执行这段代码：

##当然 这个方法会获取到所有被删除的数据 无论是普通删除  还是软删除
//如果我们只想获取被软删除的数据时，使用这个方法：
Route::get('/only-trashed/get', function (){
    $articles = \App\Models\Article::onlyTrashed()->get();
    dd($articles);
});


#恢复被软删除的数据
//恢复单个数据模型：
    // 获取被软删除的模型
    $article = \App\Models\Article::withTrashed()->find(20);
    $article->restore();
    dd($article);
//通过条件恢复多个模型：
    // 获取被软删除的模型
    $articles = \App\Models\Article::withTrashed()->where('id','>',3);
    $articles->restore();
    dd($articles);
//恢复所有被软删除的模型：
 	\App\Models\Article::onlyTrashed()->restore();



#强制删除
//如果我们配置了软删除后确实想要彻底删除一条数据要怎么办呢？可以这样：
    $article = \App\Models\Article::find(19);
    $article->forceDelete();
//这条记录会被彻底删除，当然我们也可以使用这个方法来删除已经被软删除的数据