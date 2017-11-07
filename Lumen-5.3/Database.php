<?php

// 你可以在 .env 文件中使用 DB_* 配置数据库设置，例如数据库驱动、Host、用户名和密码。
// 注意: 如果你想使用 DB facade 的话，你需要在 bootstrap/app.php 中把 $app->withFacades() 这行调用的注释去除掉。

# Basic use
// 例如，在没启用 facade 的时候，你可以使用 app 帮助方法来使用数据库连接：
$result = app('db')->select('select * from users');

// 在开启 DB facade 以后：
$results = DB::select('select * from users');

// 如果你想要使用 Eloquent ORM，你需要在 bootstrap/app.php 文件中，把 $app->withEloquent() 这行调用的注释删除掉。