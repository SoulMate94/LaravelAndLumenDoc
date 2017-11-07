<?php

#Laravel目录

--app目录包含了应用的核心代码;
	--Console和Http目录提供了进入应用核心的API，HTTP协议和CLI是和应用进行交互的两种机制，但实际上并不包含应用逻辑。换句话说，它们只是两个向应用发布命令的方式

	--Console目录包含了所有的Artisan命令

	--Http目录包含了控制器、过滤器(中间件Middleware)和请求等

	--Jobs目录是放置队列任务的地方，应用中的任务可以被队列化，也可以在当前请求生命周期内同步执行。

	--Events目录是放置事件类的地方，事件可以用于通知应用其它部分给定的动作已经发生，并提供灵活的解耦的处理。

	--Listeners目录包含事件的处理器类，处理器接收一个事件并提供对该事件发生后的响应逻辑，比如，UserRegistered事件可以被SendWelcomeEmail监听器处理

	--Exceptions目录包含应用的异常处理器，同时还是处理应用抛出的任何异常的好地方

	--Models目录 存放模型

	--Providers目录 存放服务

	--Traits目录 是为类似 PHP 的单继承语言而准备的一种代码复用机制。Trait 为了减少单继承语言的限制，使开发人员能够自由地在不同层次结构内独立的类中复用 method


--bootstrap目录包含了少许文件用于框架的启动和自动载入配置，还有一个cache文件夹用于包含框架生成的启动文件以提高性能；
	//excel
	$app->register(Maatwebsite\Excel\ExcelServiceProvider::class);
	//redis
	$app->register(Illuminate\Redis\RedisServiceProvider::class);

--config目录包含了应用所有的配置文件;
	--custom.php 		//常规配置

	--database.php 		//数据库配置

--database目录包含了数据迁移及填充文件，如果你喜欢的话还可以将其作为SQLite数据库存放目录;
	--migrations 		//命令生成的表
		--php artisan make:migration create_articles_table --create=articles


--public目录包含了前端控制器和资源文件（image、js、css等）;

--resources目录包含了视图文件及原生资源文件（LESS、SASS、CoffeeScript），以及本地化文件;
	--lang/en/validation.php 	//验证自带的提示

	--views	//视图文件(.blade.php结尾的)


--routes目录  定义路由(也就是定义控制器)
	--api.php
		$app->group([
		    'prefix'     => 'sys',		//前缀
		    'namespace'  => 'Admin',	//命名空间
		], function () use ($app) {
		    $app->group([
		        'middleware' => [		//中间件(过滤器)
		            'admin_auth',
		        ],
		    ], function () use ($app) {
		        $app->get('/', [		// 访问/
		            'as'   => 'admin_dashboard',
		            'uses' => 'Admin@dashboard',
		        ]);
		        $app->get('dd', 'DataDict@index');		//访问dd 
		        $app->get('dd/fields', 'DataDict@getFields');
		        $app->post('logout', 'Passport@logout');
		        $app->group([
		            'prefix' => 'upload_scenario',
		        ], function () use ($app) {
		            $app->get('/', 'UploadScenario@index');
		            $app->get('table_fields/{tbName}', '	UploadScenario@getFieldsOfTable');	//带了参数的请求
		            $app->get('{us_id}', 'UploadScenario@createOrEdit');
		            $app->post('{us_id}', 'UploadScenario@sideReq');
		        });
		    });
		    $app->get('login', [
		        'as'   => 'admin_login',
		        'uses' => 'Passport@login',
		    ]);
		    $app->post('login', 'Passport@loginAction');
		});


--storage目录包含了编译过的Blade模板、基于文件的session、文件缓存，
			以及其它由框架生成的文件，
			该文件夹被隔离成app、framework和logs目录，
				app目录用于存放应用要使用的文件,

				framework目录用于存放框架生成的文件和缓存,

				logs目录包含应用的日志文件;


--tests目录包含自动化测试，其中已经提供了一个开箱即用的PHPUnit示例;
		<?php

		use Laravel\Lumen\Testing\DatabaseMigrations;
		use Laravel\Lumen\Testing\DatabaseTransactions;

		class ExampleTest extends TestCase
		{
		    /**
		     * A basic test example.
		     *
		     * @return void
		     */
		    public function testExample()
		    {
		        $this->get('/');

		        $this->assertEquals(
		            $this->app->version(), $this->response->getContent()
		        );
		    }
		}


--vendor目录包含Composer依赖;

##文件
--.env 		//配置文件

--.gitignore	//GIT忽略文件

--artisan 	//Artisan命令
	#!/usr/bin/env php
	<?php

	use Symfony\Component\Console\Input\ArgvInput;
	use Symfony\Component\Console\Output\ConsoleOutput;

	$app = require __DIR__.'/bootstrap/app.php';

	$kernel = $app->make(
    	'Illuminate\Contracts\Console\Kernel'
	);

	exit($kernel->handle(new ArgvInput, new ConsoleOutput));

--composer.json 	//该文件包含了项目的依赖和其它的一些元数据
		{
		    // "name": "laravel/lumen",		/包的名称
		    "description": "The Laravel Lumen Framework.",	//应用简介
		    "keywords": ["framework", "laravel", "lumen"],	//关键字
		    "license": "MIT",		//许可证
		    "type": "project",		//类型
		    "require": {			//安装依赖
		        "php": "^7.0",		//PHP7.0
		        "laravel/lumen-framework": "5.4.*",		//Lumen框架
		        "vlucas/phpdotenv": "~2.2",
		        "firebase/php-jwt": "^5.0",			//JWT(Json Web Token)
		        "symfony/var-dumper": "^3.3",		//调试高亮
		        "predis/predis": "^1.1",
		        "illuminate/redis": "^5.4",			//redis
		        "qiniu/php-sdk": "^7.1",			//七牛云服务
		        "maatwebsite/excel": "~2.1.0",		//Excel表格
		        "jpush/jpush": "^3.5"		//极光推送
		    },
		    //有些包依赖只会在开发过程中使用，正式发布的程序不需要这些包
		    "require-dev": {		 
		        "fzaninotto/faker": "~1.4",
		        "phpunit/phpunit": "~5.0",
		        "mockery/mockery": "~0.9"
		    },
		    "autoload": {		//自动加载
		        "psr-4": {
		            "App\\": "app/"
		        }
		    },
		    "autoload-dev": {
		        "classmap": [
		            "tests/",
		            "database/"
		        ]
		    },
		    "scripts": {
		        "post-root-package-install": [
		            "php -r \"copy('.env.example', '.env');\""
		        ]
		    },
		    "minimum-stability": "dev",
		    "prefer-stable": true
		}

--composer.lock 	
	//使用composer.lock（当然是和composer.json一起）来控制你的项目的版本
	--composer.lock文件的作用
	install 命令从当前目录读取 composer.json 文件,	 //composer install
	处理了依赖关系，并把其安装到 vendor 目录下。	

	如果当前目录下存在 composer.lock 文件，它会从此文件读取依赖版本，而不是根据 composer.json 文件去获取依赖。这确保了该库的每个使用者都能得到相同的依赖版本。
		如果没有 composer.lock 文件，composer 将在处理完依赖关系后创建它。
	为了获取依赖的最新版本，并且升级 composer.lock 文件，你应该使用 update 命令。//composer update


--deploy.php 		//自动部署
		<?php
		namespace Deployer;

		// require 'recipe/laravel.php';

		// Configuration

		set('repository', 'git@119.23.16.12:646608023/hcm_proxy.git');
		set('git_tty', true); // [Optional] Allocate tty for git on first deployment
		add('shared_files', []);
		add('shared_dirs', []);
		add('writable_dirs', []);
		set('allow_anonymous_stats', false);

		// Hosts

		host('agent.hcmchi.com')
		->set('deploy_path', '/data/wwwroot/agent-api-test.hcmchi.com');

		task('update', function () {
		    $res  = run('/home/www/shell/update_agent_api_test');
		    $res .= run('/home/www/shell/update_agent_test');
		    $res .= run('/home/www/shell/update_agent_api');
		    $res .= run('/home/www/shell/update_agent');
		    
		    writeln($res);
		});


--phpunit.xml 		//该文件包含了 PHPUnit 的配置项