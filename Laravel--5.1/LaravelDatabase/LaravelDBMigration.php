<?php

数据库: 迁移#
简介#
//迁移就像是数据库中的版本控制，它让团队能够轻松的修改跟共享应用程序的数据库结构
//Laravel 的 Schema facade 对数据表的创建和操作提供了相应支持。它在所有 Laravel 支持的数据库系统中共用了一套相同的 API。

生成迁移#
//你可以使用 make:migration Artisan 命令 来创建迁移：
php artisan make:migration create_users_table
//新的迁移文件将会被放置在 database/migrations 目录中。每个迁移文件的名称都包含了一个时间戳，以便让 Laravel 确认迁移的顺序。

//--table 和 --create 选项可用来指定数据表的名称，或是该迁移被执行时会创建的新数据表。这些选项需在预生成迁移文件时填入指定的数据表：
php artisan make:migration add_votes_to_users_table --table=users

php artisan make:migration create_users_table --create=users

//如果你想为生成的迁移指定一个自定义输出路径，则可以在运行 make:migration 命令时使用 --path 选项。提供的路径必须是相对于应用程序的基本路径。

迁移结构#
//一个迁移类会包含两个方法：up 和 down。
//up 方法可为数据库增加新的数据表、字段、或索引，而 down 方法则可简单的反向运行 up 方法的操作。
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlightsTable extends Migration
{
    /**
     * 运行迁移。
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('airline');
            $table->timestamps();
        });
    }

    /**
     * 还原迁移。
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('flights');
    }
}


运行迁移#
//要运行应用程序中的所有未执行迁移，可以使用 migrate Artisan 命令。如果你使用了 Homestead 虚拟主机，则应该在虚拟机中运行如下命令：
php artisan migrate
//如果在你运行时出现「class not found」的错误，请试着在运行 composer dump-autoload 命令后再次运行迁移命令

在线上环境强制运行迁移#	
//一些迁移的操作是具有破坏性的，意思是它们可能会导致数据丢失。为了保护线上环境的数据库，系统会在这些命令被运行之前显示确认提示。若要忽略此提示并强制运行命令，则可以使用 --force 标记：
php artisan migrate --force

还原迁移#
//若要将迁移还原至上一个「操作」，则可以使用 rollback 命令。请注意，此还原是对上一次执行的「批量」迁移进行还原，其中可能包括多个迁移文件：
php artisan migrate:rollback
//migrate:reset 命令会还原应用程序中的所有迁移：
php artisan migrate:reset

单个命令还原或运行迁移#
//migrate:refresh 命令首先会还原数据库的所有迁移，接着再运行 migrate 命令。此命令能有效的重建整个数据库：
php artisan migrate:refresh

php artisan migrate:refresh --seed


编写迁移#
创建数据表#
//要创建一张新的数据表，则可以使用 Schema facade 的 create方法
//create 方法接收两个参数。第一个参数为数据表的名称，第二个参数为一个闭包，此闭包会接收一个用于定义新数据表的 Blueprint 对象：
Schema::create('users', function (Blueprint $table) {
    $table->increments('id');
});

//当然，当创建数据表时，你也可以使用任何结构构造器的 字段方法 来定义数据表的字段。


检查数据表或字段是否存在#
//你可以使用 hasTable 和 hasColumn 方法简单的检查数据表或字段是否存在：
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('users', 'email')) {
    //
}

连接与存储引擎#
//如果你想要在一个非默认的数据库连接中进行结构操作，则可以使用 connection 方法：
Schema::connection('foo')->create('users', function ($table) {
    $table->increments('id');
});

//若要设置数据表的存储引擎，只需在结构构造器上设置 engine 属性即可：
Schema::create('users', function ($table) {
    $table->engine = 'InnoDB';

    $table->increments('id');
});

重命名与删除数据表#
//若要重命名一张已存在的数据表，则可以使用 rename 方法
Schema::rename($from, $to);

//要删除已存在的数据表，可使用 drop 或 dropIfExists 方法：
Schema::drop('users');

Schema::dropIfExists('users');

创建字段#
//若要更新一张已存在的数据表，我们会使用 Schema facade 的 table 方法。
//如同 create 方法，table 方法会接收两个参数：一个是数据表的名称，另一个则是接收 Blueprint 实例的闭包。我们可以使用它来为数据表新增字段：
Schema::table('users', function ($table) {
    $table->string('email');
});

可用的字段类型#
//结构构造器包含了许多字段类型，供你构建数据表时使用：
命令							描述
$table->bigIncrements('id');		递增 ID（主键），相当于「UNSIGNED BIG INTEGER」型态。
$table->bigInteger('votes');		相当于 BIGINT 型态。
$table->binary('data');				相当于 BLOB 型态。
$table->boolean('confirmed');		相当于 BOOLEAN 型态。
$table->char('name', 4);			相当于 CHAR 型态，并带有长度。
$table->date('created_at');			相当于 DATE 型态。
$table->dateTime('created_at');		相当于 DATETIME 型态。
$table->decimal('amount', 5, 2);	相当于 DECIMAL 型态，并带有精度与基数。
$table->double('column', 15, 8);	相当于 DOUBLE 型态，总共有 15 位数，在小数点后面有 8 位数。
$table->enum('choices', ['foo', 'bar']);	相当于 ENUM 型态。
$table->float('amount');			相当于 FLOAT 型态。
$table->increments('id');			递增的 ID (主键)，使用相当于「UNSIGNED INTEGER」的型态。
$table->integer('votes');			相当于 INTEGER 型态。
$table->json('options');			相当于 JSON 型态。
$table->jsonb('options');			相当于 JSONB 型态。
$table->longText('description');	相当于 LONGTEXT 型态。
$table->mediumInteger('numbers');	相当于 MEDIUMINT 型态。
$table->mediumText('description');	相当于 MEDIUMTEXT 型态。
$table->morphs('taggable');			加入整数 taggable_id 与字符串 taggable_type。
$table->nullableTimestamps();		与 timestamps() 相同，但允许为 NULL。
$table->rememberToken();			加入 remember_token 并使用 VARCHAR(100) NULL。
$table->smallInteger('votes');		相当于 SMALLINT 型态。
$table->softDeletes();				加入 deleted_at 字段用于软删除操作。
$table->string('email');			相当于 VARCHAR 型态。
$table->string('name', 100);		相当于 VARCHAR 型态，并带有长度。
$table->text('description');		相当于 TEXT 型态。
$table->time('sunrise');			相当于 TIME 型态。
$table->tinyInteger('numbers');		相当于 TINYINT 型态。
$table->timestamp('added_on');		相当于 TIMESTAMP 型态。
$table->timestamps();				加入 created_at 和 updated_at 字段。
$table->uuid('id');					相当于 UUID 型态。

字段修饰#
//除了上述的字段类型列表，还有一些其它的字段「修饰」，你可以将它增加到字段中。例如，若要让字段「nullable」，那么你可以使用 nullable 方法：
Schema::table('users', function ($table) {
    $table->string('email')->nullable();
});

//以下列表为字段的可用修饰。此列表不包括 索引修饰：

修饰					描述
->first()				将此字段放置在数据表的「第一个」（仅限 MySQL）
->after('column')		将此字段放置在其它字段「之后」（仅限 MySQL）
->nullable()			此字段允许写入 NULL 值
->default($value)		为此字段指定「默认」值
->unsigned()			设置 integer 字段为 UNSIGNED


修改字段#
先决条件#
//在修改字段之前，请务必在你的 composer.json 中增加 doctrine/dbal 依赖。
//Doctrine DBAL 函数库被用于判断当前字段的状态以及创建调整指定字段的 SQL 查询。


更新字段属性#
//change 方法让你可以修改一个已存在的字段类型，或修改字段属性
//例如，你或许想增加字符串字段的长度。要看看 change 方法的具体作用，让我们来将 name 字段的长度从 25 增加到 50
Schema::table('users', function ($table) {
    $table->string('name', 50)->change();
});

//我们也能将字段修改为 nullable：
Schema::table('users', function ($table) {
    $table->string('name', 50)->nullable()->change();
});


重命名字段#
//要重命名字段，可使用结构构造器的 renameColumn 方法。在重命名字段前，请确定你的 composer.json 文件内已经加入 doctrine/dbal 依赖：
Schema::table('users', function ($table) {
    $table->renameColumn('from', 'to');
});

//注意：数据表的 enum 字段暂时不支持修改字段名称。

移除字段#
//要移除字段，可使用结构构造器的 dropColumn 方法：
Schema::table('users', function ($table) {
    $table->dropColumn('votes');
});

//你可以传递字段的名称数组至 dropCloumn 方法来移除多个字段：
Schema::table('users', function ($table) {
    $table->dropColumn(['votes', 'avatar', 'location']);
});

//注意：在 SQLite 数据库中移除字段前，你需要先增加 doctrine/dbal 依赖至你的 composer.json 文件，并在你的命令行中运行 composer update 命令来安装该函数库。
//注意：SQLite 数据库并不支持在单个迁移中移除或修改多个字段。


创建索引#
//结构构造器支持多种类型的索引。首先，让我们先来看看一个示例，其指定了字段的值必须是唯一的。你可以简单的在字段定义之后链式调用 unique 方法来创建索引：
$table->string('email')->unique();
//此外，你也可以在定义完字段之后创建索引。例如：
$table->unique('email');
//你也可以传递一个字段的数组至索引方法来创建复合索引：
$table->index(['account_id', 'created_at']);


可用的索引类型#

命令									描述
$table->primary('id');					加入主键。
$table->primary(['first', 'last']);		加入复合键。
$table->unique('email');				加入唯一索引。
$table->index('state');					加入基本索引。

移除索引#
//若要移除索引，则必须指定索引的名称。Laravel 默认会自动给索引分配合理的名称。其将数据表名称，索引的字段名称，及索引类型简单地连接在了一起。举例如下：

命令											描述
$table->dropPrimary('users_id_primary');		从「users」数据表移除主键。
$table->dropUnique('users_email_unique');		从「users」数据表移除唯一索引。
$table->dropIndex('geo_state_index');			从「geo」数据表移除基本索引。



外键约束#
//Laravel 也为创建外键约束提供了支持，用于在数据库层中的强制引用完整性
//例如，让我们定义一个有 user_id 字段的 posts 数据表，user_id 引用了 users 数据表的 id 字段
Schema::table('posts', function ($table) {
    $table->integer('user_id')->unsigned();

    $table->foreign('user_id')->references('id')->on('users');
});

//你也可以指定约束的「on delete」及「on update」：
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');

//要移除外键，你可以使用 dropForeign 方法。外键约束与索引采用相同的命名方式
//所以，我们可以将数据表名称和约束字段连接起来，接着在该名称后面加上「_foreign」后缀：
$table->dropForeign('posts_user_id_foreign');

//你也通过传递一个自动使用传统约束名称的数组值来移除外键：
$table->dropForeign(['user_id']);      
