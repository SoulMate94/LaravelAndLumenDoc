<?php

#配置信息
//Lumen 对主流数据库系统连接和查询都提供了很好的支持，目前，Lumen 支持以下四种数据库系统：
	MySQL
	Postgres
	SQLite
	SQL Server

//你可以在 .env 文件中使用 DB_* 配置数据库设置，例如数据库驱动、Host、用户名和密码。


DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=homestead
DB_USERNAME=homestead
DB_PASSWORD=secret
DB_PREFIX=jh_


//基础使用
//注意: 如果你想使用 DB facade 的话，你需要在 bootstrap/app.php 中把 $app->withFacades() 这行调用的注释去除掉。
$app->withFacades();

//例如，在没启用 facade 的时候，你可以使用 app 帮助方法来使用数据库连接：
$results = app('db')->select("SELECT * FROM users");

//或者，在开启 DB facade 以后：
$results = DB::select("SELECT * FROM users");


//基本查询

//查询语句构造器

//Eloquent ORM
如果你想要使用 Eloquent ORM，你需要在 bootstrap/app.php 文件中，把 $app->withEloquent() 这行调用的注释删除掉。


#数据库迁移




