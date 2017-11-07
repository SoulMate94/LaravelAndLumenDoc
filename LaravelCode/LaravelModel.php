<?php

#什么是ORM?
##ORM，即 Object-Relational Mapping（对象关系映射），它的作用是在关系型数据库和业务实体对象之间作一个映射，这样，我们在操作具体的 业务对象时，就不需要再去和复杂的SQL语句打交道，只需简单的操作对象的属性和方法即可。

##ORM 两种最常见的实现方式是 ActiveRecord 和 DataMapper，ActiveRecord 尤其流行，在很多框架中都能看到它的身影。两者的区别主要在于 ActiveRecord 中模型与数据表一一对应，而 DataMapper 中模型与数据表是完全分离的。

##Laravel 中的 Eloquent ORM 使用的也是 ActiveRecord 实现方式，每一个 Eloquent 模型类对应着数据库中的一张表，我们通过调用模型类的相应方法实现对数据库的增删改查。


#创建模型
##我们使用artisan命令来创建模型：
php artisan make:model Models/Article

## laravel默认将所有创建的模型放到app路径下，我们也可以在创建时指定一个文件夹。我们创建的这个模型 就在app\Models\路径下。

	<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Model;

	class Article extends Model
	{
	    //
	}

#指定表名
##如果不指定的话，Article模型对应的表就是articles，如果是一个Order模型 那它对应的表就是 orders，以此类推，如果要指定别的表明就添加一下代码：
    //指定别的表名
    public $table = 'article';

#指定主键
##如果不指定主键，默认的主键是：id，如果要指定别的主键：
    //指定别的主键
    protected $primaryKey = 'article_id';


#时间戳设置
#默认情况下，Eloquent模型类会自动管理时间戳列create_at和update_at，如果要取消自动管理，可以设置$timestamps属性为false：
  	public $timestamps = false;

##还有，如果你想要设置时间戳的格式，可以使用$dateFormat属性，该属性决定了日期时间以何种格式存入数据库，以及以何种格式显示：
    // 设置时间戳格式为Unix
    protected $dateFormat = 'U';