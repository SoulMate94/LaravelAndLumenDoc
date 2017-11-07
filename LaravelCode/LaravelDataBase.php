<?php

数据库配置
//通过修改项目根目录中.env文件配置数据库连接方式
// 连接类型
DB_CONNECTION=mysql
// HOST
DB_HOST=127.0.0.1
// PORT
DB_PORT=3306
// 数据库名
DB_DATABASE=dbname
// 用户名
DB_USERNAME=root
// 密码
DB_PASSWORD=root

利用artisan提供的迁移方法初始化数据库
结构初始化

通过php artisan make:migration create_tablename_table命令生成一个结构文件

到/database/migrations里找到这个文件，复制多份，把tablename分别改为表名
参照这个例子来编辑这些文件：

<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable(false);
            $table->string('uuid')->unique();
            $table->string('summary');
            $table->boolean('disabled');
            $table->text('content');
            $table->integer('category_id');
            $table->timestamps(); // 自动生成created_at和updated_at字段
            $table->softDeletes(); // 自动生成deleted_at字段
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('article');
    }
}

在命令行执行php artisan migrate，完成建表


数据初始化
//在/database/seeds文件夹中建立要执行初始化的文件，假如仅需要给user表插入初始化数据，则新建文件UserTableSeeder.php：
<?php


use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class UserTableSeeder extends Seeder {
    public function run() {
        User::create([
            'id' => 1,
            'username' => 'admin',
            'password' => md5('admin'),
            'realname' => '原始管理员'
        ]);
    }
}

//修改/database/seeds/DatabaseSeeder.php文件，使其执行建立的文件：
<?php

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
        $this->call('UserTableSeeder');
    }
}
执行命令php artisan db:seed导入数据


直接使用SQL语句操作数据库

不使用任何辅助的情况下，使用SQL语句操作数据库：
$results = app('db')->select("SELECT * FROM users");

//虽然直接的操作可能可以节省一些资源，但容易出现不可控BUG，在以前无框架的年代，我们就经常对单引号进行过滤，给代码增加复杂程度。所以，除非整个程序都是需要极简便的数据库操作，其他情境并不建议这样做。

使用Facade操作数据库
//然而，Lumen只支持facade的DB类，要使用facade DB类，只需要修改bootstrap/app.php文件把$app->withFacades()一行去掉即可
use DB;
...
$results = DB::select("SELECT * FROM users");


使用Eloquent ORM操作数据库
//要使用Eloquent ORM，只需要修改bootstrap/app.php文件把$app->withEloquent()一行去掉即可

针对数据库建立Model：

//在app文件夹中新建文件夹Models
//针对要用到的数据库表，建立对应的Model，更多功能如软删除，表关联等，请留意代码中注释：
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 若使用软删除则取消SoftDeletes的注释
// use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model {
    // 软删除：
    // use SoftDeletes;
    protected $table = 'article';
    // 白名单
    protected $fillable = [
        'title',
        'content',
        'author',
        'origin',
        'category_id'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
        // 软删除：
        // 'deleted_at'
    ];
    // 若有字段在读取时不读出来的，放在$hidden数组里
    protected $hidden = [
        // 一般用于'password'
    ];
    // 一对多关联，参数分别为Model,外表中用来关联的字段，关联的本表字段
    public function comments() {
        return $this->hasMany('App\Models\Comment', 'article_id', 'id');
    }
    // 从属（反向一对多）关联，参数分别为Model,外表中用来关联的字段，关联的本表字段
    public function category() {
        return $this->belongsTo('App\Models\Category', 'id', 'category_id');
    }
    // 一对一关联，参数分别为Model,外表中用来关联的字段，关联的本表字段
    public function remark() {
        return $this->hasOne('App\Models\Remark', 'article_id', 'id');
    }
}


在控制器中操作数据库，常用操作注释在代码里头好了
use App\Models\Article;
...
// 主键查找
$id = 5;
$item = Article::find($id);

// 主键查找找不到时抛出ModelNotFoundException导常
$item = Article::findOrFail($id);

// where, order
$list = Article::where('disabled', '=', 1)->orderBy('created_at', desc);

// paginate 分页，参数是页容量，页码将自动获取请求$request->input('page')
$list = $list->paginate(10)

// 插入
$item = new Article;
$item->title = '新文章';
$item->save();

// 修改
$item = Article::findOrFail($id);
$item->title = '修改后的标题';
$item->save();

// 删除
$item = Article::findOrFail($id);
$item->delete();

// 获取关联数据
$item = Article::with('comments', 'category')->all();


结合Facade和Eloquent，给流程性处理添加事务
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DB;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Counter;
use App\Models\Log;
...
// 开始事务
DB::beginTransaction();
try {
    $id = 5;
    $article = Article::findOrFail($id);
    Comment::where('article_id', $id)->delete();
    Counter::where('article_id', $id)->delete();
    Log::insert([
        'content' => '删除文章：'.$id
    ]);
    // 流程操作顺利则commit
    DB::commit();
} catch (ModelNotFoundException $e) {
    // 抛出异常则rollBack
    DB::rollBack();
}


使用Collection辅助处理结果
//Eloquent的查询手段是有限的，但查询返回的对象只要调用get()方法，即可生成集合Collection对象，对于复杂的查询，或是对结果的进一步处理，集合Collection可以作很大的补充作用，甚至还可以用结果进行GroupBy，或是内嵌函数来计算sum或avg，节省执行数据库查询的次数

//这里举一个例，已查询出购物车信息，要计算每个商品价格x数量得出的总价：

$userid = 5;
$result = Cart::where('userid', $userid)->get();
$total = $result->sum(function ($item) {
    return 1 * $item['price'] * $item['amount'];
});