<?php

#集合#
#简介
//Illuminate\Support\Collection 类提供一个流畅、便利的封装来操控数组数据
//如下面的示例代码，我们用 collect 函数从数组中创建新的集合实例，对每一个元素运行 strtoupper 函数，然后移除所有的空元素：
$collection = collect(['taylor', 'abigail', null])->map(function ($name) {
    return strtoupper($name);
})
->reject(function ($name) {
    return empty($name);
});
//Collection 类支持链式调用，一般来说，每一个 Collection 方法会返回一个全新的 Collection 实例，你可以放心地进行链接调用

#创建集合
//collect 辅助函数会利用传入的数组生成一个新的 Illuminate\Support\Collection 实例。所以要创建一个集合就这么简单：
$collection = collect([1, 2, 3]);

#可用的方法
#方法清单#

all()#
//返回该集合所代表的底层 数组：
collect([1, 2, 3])->all();
// [1, 2, 3]

avg()#
//返回集合中所有项目的平均值：
collect([1, 2, 3, 4, 5])->avg();
// 3

//如果集合包含了嵌套数组或对象，你可以通过传递「键」来指定使用哪些值计算平均值：
$collection = collect([
    ['name' => 'JavaScript: The Good Parts', 'pages' => 176],
    ['name' => 'JavaScript: The Definitive Guide', 'pages' => 1096],
]);

$collection->avg('pages');
// 636 


chunk()#
//将集合拆成多个指定大小的较小集合：
$collection = collect([1, 2, 3, 4, 5, 6, 7]);

$chunks = $collection->chunk(4);

$chunks->toArray();

// [[1, 2, 3, 4], [5, 6, 7]]

//这个方法在适用于网格系统如 Bootstrap 的 视图 。想像你有一个 Eloquent 模型的集合要显示在一个网格内：
@foreach ($products->chunk(3) as $chunk)
    <div class="row">
        @foreach ($chunk as $product)
            <div class="col-xs-4">{{ $product->name }}</div>
        @endforeach
    </div>
@endforeach


collapse()#
//将多个数组组成的集合合成单个数组集合：
$collection = collect([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);

$collapsed = $collection->collapse();

$collapsed->all();

// [1, 2, 3, 4, 5, 6, 7, 8, 9]

contains()#
//判断集合是否含有指定项目：
$collection = collect(['name' => 'Desk', 'price' => 100]);

$collection->contains('Desk');
// true

$collection->contains('New York');
// false

//你可以将一对键/值传入 contains 方法，用来判断该组合是否存在于集合内：
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
]);

$collection->contains('product', 'Bookcase');
// false

//最后，你也可以传入一个回调函数到 contains 方法内运行你自己的判断语句：
$collection = collect([1, 2, 3, 4, 5]);

$collection->contains(function ($key, $value) {
    return $value > 5;
});
// false


count()#
//返回该集合内的项目总数：
$collection = collect([1, 2, 3, 4]);

$collection->count();
// 4


diff()#
//将集合与其它集合或纯 PHP 数组进行比较：
$collection = collect([1, 2, 3, 4, 5]);

$diff = $collection->diff([2, 4, 6, 8]);

$diff->all();
// [1, 3, 5]


each()#
//遍历集合中的项目，并将之传入回调函数：
$collection = $collection->each(function ($item, $key) {
    //
});
//回调函数返回 false 以中断循环：
$collection = $collection->each(function ($item, $key) {
    if (/* some condition */) {
        return false;
    }
});


every()#
//创建一个包含每 第 n 个 元素的新集合：
$collection = collect(['a', 'b', 'c', 'd', 'e', 'f']);

$collection->every(4);
// ['a', 'e']

// 你可以选择性的传递偏移值作为第二个参数：
$collection->every(4, 1);
// ['b', 'f']

except()#
//返回集合中除了指定键的所有项目：
$collection = collect(['product_id' => 1, 'name' => 'Desk', 'price' => 100, 'discount' => false]);

$filtered = $collection->except(['price', 'discount']);

$filtered->all();
// ['product_id' => 1, 'name' => 'Desk']
与 except 相反的方法请查看 only。


filter()#
//使用回调函数筛选集合，只留下那些通过判断测试的项目：
$collection = collect([1, 2, 3, 4]);

$filtered = $collection->filter(function ($item) {
    return $item > 2;
});

$filtered->all();
// [3, 4]
与 filter 相反的方法可以查看 reject。

first()#
//返回集合第一个通过指定测试的元素：
collect([1, 2, 3, 4])->first(function ($key, $value) {
    return $value > 2;
});
// 3

//你也可以不传入参数使用 first 方法以获取集合中第一个元素。如果集合是空的，则会返回 null：
collect([1, 2, 3, 4])->first();
// 1

flatten()#
//将多维集合转为一维集合：
$collection = collect(['name' => 'taylor', 'languages' => ['php', 'javascript']]);

$flattened = $collection->flatten();

$flattened->all();

// ['taylor', 'php', 'javascript'];


flip()#
//将集合中的键和对应的数值进行互换：
$collection = collect(['name' => 'taylor', 'framework' => 'laravel']);

$flipped = $collection->flip();

$flipped->all();

// ['taylor' => 'name', 'laravel' => 'framework']


forget()#
//通过集合的键来移除掉集合中的一个项目：
$collection = collect(['name' => 'taylor', 'framework' => 'laravel']);

$collection->forget('name');

$collection->all();

// [framework' => 'laravel']
注意：与大多数其它集合的方法不同，forget 不会返回修改过后的新集合；它会直接修改调用它的集合

forPage()#
//返回可用来在指定页码显示项目的新集合：
$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);

$chunk = $collection->forPage(2, 3);

$chunk->all();

// [4, 5, 6]
//这个方法需要「页码」和「每页显示数量」。


get()#
//返回指定键的项目。如果该键不存在，则返回 null：
$collection = collect(['name' => 'taylor', 'framework' => 'laravel']);

$value = $collection->get('name');

// taylor

//你可以选择性地传入一个默认值作为第二个参数：
$collection = collect(['name' => 'taylor', 'framework' => 'laravel']);

$value = $collection->get('foo', 'default-value');

// default-value

//你甚至可以传入回调函数当默认值。如果指定的键不存在，就会返回回调函数的运行结果：
$collection->get('email', function () {
    return 'default-value';
});

// default-value

groupBy()#
//根据指定的「键」为集合内的项目分组：

$collection = collect([
    ['account_id' => 'account-x10', 'product' => 'Chair'],
    ['account_id' => 'account-x10', 'product' => 'Bookcase'],
    ['account_id' => 'account-x11', 'product' => 'Desk'],
]);

$grouped = $collection->groupBy('account_id');

$grouped->toArray();

/*
    [
        'account-x10' => [
            ['account_id' => 'account-x10', 'product' => 'Chair'],
            ['account_id' => 'account-x10', 'product' => 'Bookcase'],
        ],
        'account-x11' => [
            ['account_id' => 'account-x11', 'product' => 'Desk'],
        ],
    ]
*/

//除了传入字符串的「键」之外，你也可以传入回调函数。该函数应该返回你希望用来分组的键的值。
$grouped = $collection->groupBy(function ($item, $key) {
    return substr($item['account_id'], -3);
});

$grouped->toArray();

/*
    [
        'x10' => [
            ['account_id' => 'account-x10', 'product' => 'Chair'],
            ['account_id' => 'account-x10', 'product' => 'Bookcase'],
        ],
        'x11' => [
            ['account_id' => 'account-x11', 'product' => 'Desk'],
        ],
    ]
*/


has()#
//检查集合中是否含有指定的「键」：    
$collection = collect(['account_id' => 1, 'product' => 'Desk']);

$collection->has('email');

// false


implode()#
//implode 方法合并集合中的项目。它的参数依集合中的项目类型而定。
//假如集合含有数组或对象，你应该传入你希望连接的属性的「键」，以及你希望放在数值之间的拼接字符串：
$collection = collect([
    ['account_id' => 1, 'product' => 'Desk'],
    ['account_id' => 2, 'product' => 'Chair'],
]);

$collection->implode('product', ', ');

// Desk, Chair


//假如集合只含有简单的字符串或数字，则只需要传入拼接的字符串作为该方法的唯一参数即可：
collect([1, 2, 3, 4, 5])->implode('-');

// '1-2-3-4-5'


intersect()#
//移除任何指定 数组 或集合内所没有的数值：
$collection = collect(['Desk', 'Sofa', 'Chair']);

$intersect = $collection->intersect(['Desk', 'Chair', 'Bookcase']);

$intersect->all();

// [0 => 'Desk', 2 => 'Chair']
// 相当于取「交集」。

isEmpty()#
//如果集合是空的，isEmpty 方法会返回 true：否则返回 false：
collect([])->isEmpty();

// true


keyBy()#
//以指定键的值作为集合项目的键：
$collection = collect([
    ['product_id' => 'prod-100', 'name' => 'desk'],
    ['product_id' => 'prod-200', 'name' => 'chair'],
]);

$keyed = $collection->keyBy('product_id');

$keyed->all();

/*
    [
        'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
        'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]
*/

//如果多个项目有同样的键，只有最后一个会出现在新的集合内。

//你也可以传入自己的回调函数，该函数应该返回集合的键的值：
$keyed = $collection->keyBy(function ($item) {
    return strtoupper($item['product_id']);
});

$keyed->all();

/*
    [
        'PROD-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
        'PROD-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]
*/


keys()#
//返回该集合所有的键：
$collection = collect([
    'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
    'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
]);

$keys = $collection->keys();

$keys->all();

// ['prod-100', 'prod-200']


last()#
//返回集合中，最后一个通过指定测试的元素：
collect([1, 2, 3, 4])->last(function ($key, $value) {
    return $value < 3;
});

// 2

//你也可以不传入参数使用 last 方法以获取集合中最后一个元素。如果集合是空的，则会返回 null：
collect([1, 2, 3, 4])->last();

// 4

map()#
//遍历整个集合并将每一个数值传入回调函数。回调函数可以任意修改并返回项目，形成修改过的项目组成的新集合：
$collection = collect([1, 2, 3, 4, 5]);

$multiplied = $collection->map(function ($item, $key) {
    return $item * 2;
});

$multiplied->all();

// [2, 4, 6, 8, 10]

注意：正如集合大多数其它的方法一样，map 返回一个新集合实例；它并没有修改被调用的集合。假如你想改变原始的集合，得使用 transform 方法。

max()#
//计算指定键的最大值：
$max = collect([['foo' => 10], ['foo' => 20]])->max('foo');
// 20

$max = collect([1, 2, 3, 4, 5])->max();
// 5


merge()#
//合并数组进集合。数组「键」对应的数值会覆盖集合「键」对应的数值：
$collection = collect(['product_id' => 1, 'name' => 'Desk']);

$merged = $collection->merge(['price' => 100, 'discount' => false]);

$merged->all();

// ['product_id' => 1, 'name' => 'Desk', 'price' => 100, 'discount' => false]

//如果指定数组的「键」为数字，则「值」将会合并到集合的后面：
$collection = collect(['Desk', 'Chair']);

$merged = $collection->merge(['Bookcase', 'Door']);

$merged->all();

// ['Desk', 'Chair', 'Bookcase', 'Door']


min()#
//计算指定「键」的最小值：
$min = collect([['foo' => 10], ['foo' => 20]])->min('foo');
// 10

$min = collect([1, 2, 3, 4, 5])->min();
// 1


only()#
//返回集合中指定键的所有项目：
$collection = collect(['product_id' => 1, 'name' => 'Desk', 'price' => 100, 'discount' => false]);

$filtered = $collection->only(['product_id', 'name']);

$filtered->all();

// ['product_id' => 1, 'name' => 'Desk']
//与 only 相反的方法请查看 except。

pluck()#
//获取所有集合中指定「键」对应的值：
$collection = collect([
    ['product_id' => 'prod-100', 'name' => 'Desk'],
    ['product_id' => 'prod-200', 'name' => 'Chair'],
]);

$plucked = $collection->pluck('name');

$plucked->all();

// ['Desk', 'Chair']

//你也可以指定要怎么给最后出来的集合分配键：
$plucked = $collection->pluck('name', 'product_id');

$plucked->all();

// ['prod-100' => 'Desk', 'prod-200' => 'Chair']


pop()#
//移除并返回集合最后一个项目：
$collection = collect([1, 2, 3, 4, 5]);

$collection->pop();
// 5

$collection->all();
// [1, 2, 3, 4]


prepend()#
//在集合前面增加一个项目：
$collection = collect([1, 2, 3, 4, 5]);

$collection->prepend(0);

$collection->all();

// [0, 1, 2, 3, 4, 5]

//你可以传递选择性的第二个参数来设置前置项目的键：
$collection = collect(['one' => 1, 'two', => 2]);

$collection->prepend(0, 'zero');

$collection->all();

// ['zero' => 0, 'one' => 1, 'two', => 2]


pull()#
//把「键」对应的值从集合中移除并返回：
$collection = collect(['product_id' => 'prod-100', 'name' => 'Desk']);

$collection->pull('name');

// 'Desk'

$collection->all();

// ['product_id' => 'prod-100']


push()#
//在集合的后面新添加一个元素：
$collection = collect([1, 2, 3, 4]);

$collection->push(5);

$collection->all();

// [1, 2, 3, 4, 5]


put()#
//在集合内设置一个「键/值」：
$collection = collect(['product_id' => 1, 'name' => 'Desk']);

$collection->put('price', 100);

$collection->all();

// ['product_id' => 1, 'name' => 'Desk', 'price' => 100]


random()#
//random 方法从集合中随机返回一个项目：
$collection = collect([1, 2, 3, 4, 5]);

$collection->random();
// 4 - (随机返回)

//你可以选择性地传入一个整数到 random。如果该整数大于 1，则会返回一个集合：
$random = $collection->random(3);

$random->all();
// [2, 4, 5] - (随机返回)

reduce()#
//reduce 方法将集合缩减到单个数值，该方法会将每次迭代的结果传入到下一次迭代：
$collection = collect([1, 2, 3]);

$total = $collection->reduce(function ($carry, $item) {
    return $carry + $item;
});
// 6

//第一次迭代时 $carry 的数值为 null；然而你也可以传入第二个参数进 reduce 以指定它的初始值：
$collection->reduce(function ($carry, $item) {
    return $carry + $item;
}, 4);

// 10


reject()#
//reject 方法以指定的回调函数筛选集合。该回调函数应该对希望从最终集合移除掉的项目返回 true：
$collection = collect([1, 2, 3, 4]);

$filtered = $collection->reject(function ($item) {
    return $item > 2;
});

$filtered->all();
// [1, 2]
//与 reject 相反的方法可以查看 filter 方法。

reverse()#
//reverse 方法倒转集合内项目的顺序：
$collection = collect([1, 2, 3, 4, 5]);

$reversed = $collection->reverse();

$reversed->all();
// [5, 4, 3, 2, 1]


search()#
//search 方法在集合内搜索指定的数值并返回找到的键。假如找不到项目，则返回 false：
$collection = collect([2, 4, 6, 8]);

$collection->search(4);
// 1


//搜索是用「宽松」比对来进行。要使用严格比对，就传入 true 为该方法的第二个参数：
$collection->search('4', true);

// false

//另外，你可以传入你自己的回调函数来搜索第一个通过你判断测试的项目：
$collection->search(function ($item, $key) {
    return $item > 5;
});
// 2

shift()#
//shift 方法移除并返回集合的第一个项目：
$collection = collect([1, 2, 3, 4, 5]);

$collection->shift();
// 1

$collection->all();
// [2, 3, 4, 5]


shuffle()#
//shuffle 方法随机排序集合的项目：
$collection = collect([1, 2, 3, 4, 5]);

$shuffled = $collection->shuffle();

$shuffled->all();
// [3, 2, 5, 1, 4] // (generated randomly)


slice()#
//slice 方法返回集合从指定索引开始的一部分切片：

$collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

$slice = $collection->slice(4);

$slice->all();

// [5, 6, 7, 8, 9, 10]
//如果你想限制返回切片的大小，就传入想要的大小为方法的第二个参数：
$slice = $collection->slice(4, 2);

$slice->all();
// [5, 6]
//返回的切片将会有以数字索引的新键。假如你希望保留原始的键，传入 true 为方法的第三个参数。

sort()#
//对集合排序：
$collection = collect([5, 3, 1, 2, 4]);

$sorted = $collection->sort();

$sorted->values()->all();

// [1, 2, 3, 4, 5]
//排序过的集合保有原来的数组键。在这个例子中我们用了 values 方法重设键为连续的数字索引。

//要排序内含数组或对象的集合，见 sortBy 和 sortByDesc 方法。

//假如你需要更高级的排序，你可以传入回调函数以你自己的算法进行排序。参考 PHP 文档的 usort，这是集合的 sort 方法在背后所调用的函数。

sortBy()#
//以指定的键排序集合：
$collection = collect([
    ['name' => 'Desk', 'price' => 200],
    ['name' => 'Chair', 'price' => 100],
    ['name' => 'Bookcase', 'price' => 150],
]);

$sorted = $collection->sortBy('price');

$sorted->values()->all();

/*
    [
        ['name' => 'Chair', 'price' => 100],
        ['name' => 'Bookcase', 'price' => 150],
        ['name' => 'Desk', 'price' => 200],
    ]
*/

//排序过的集合保有原来的数组键。在这个例子中我们用了 values 方法重设键为连续的数字索引
//你也可以传入自己的回调函数以决定如何排序集合数值：
$collection = collect([
    ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
    ['name' => 'Chair', 'colors' => ['Black']],
    ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
]);

$sorted = $collection->sortBy(function ($product, $key) {
    return count($product['colors']);
});

$sorted->values()->all();

/*
    [
        ['name' => 'Chair', 'colors' => ['Black']],
        ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
        ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
    ]
*/    


sortByDesc()#
//与 sortBy 有着一样的形式，但是会以相反的顺序来排序集合：


splice()#
//返回从指定的索引开始的一小切片项目，原本集合也会被切除：
$collection = collect([1, 2, 3, 4, 5]);

$chunk = $collection->splice(2);

$chunk->all();
// [3, 4, 5]

$collection->all();
// [1, 2]

//你可以传入第二个参数以限制大小：
$collection = collect([1, 2, 3, 4, 5]);

$chunk = $collection->splice(2, 1);

$chunk->all();
// [3]

$collection->all();
// [1, 2, 4, 5]

//此外，你可以传入含有新项目的第三个参数以取代集合中被移除的项目：
$collection = collect([1, 2, 3, 4, 5]);

$chunk = $collection->splice(2, 1, [10, 11]);

$chunk->all();
// [3]

$collection->all();
// [1, 2, 10, 11, 4, 5]


sum()#
//返回集合内所有项目的总和：
collect([1, 2, 3, 4, 5])->sum();
// 15

//如果集合包含数组或对象，你应该传入一个「键」来指定要用哪些数值来计算总合：
$collection = collect([
    ['name' => 'JavaScript: The Good Parts', 'pages' => 176],
    ['name' => 'JavaScript: The Definitive Guide', 'pages' => 1096],
]);

$collection->sum('pages');
// 1272

//此外，你可以传入自己的回调函数来决定要用哪些数值来计算总合：
$collection = collect([
    ['name' => 'Chair', 'colors' => ['Black']],
    ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
    ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
]);

$collection->sum(function ($product) {
    return count($product['colors']);
});

// 6


take()#
//返回有着指定数量项目的集合：
$collection = collect([0, 1, 2, 3, 4, 5]);

$chunk = $collection->take(3);

$chunk->all();

// [0, 1, 2]

//你也可以传入负整数以获取从集合后面来算指定数量的项目：
$collection = collect([0, 1, 2, 3, 4, 5]);

$chunk = $collection->take(-2);

$chunk->all();

// [4, 5]


toArray()#
//将集合转换成纯 PHP 数组。假如集合的数值是 Eloquent 模型，也会被转换成数组：
$collection = collect(['name' => 'Desk', 'price' => 200]);

$collection->toArray();

/*
    [
        ['name' => 'Desk', 'price' => 200],
    ]
*/
    注意：toArray 也会转换所有内嵌的对象为数组。假如你希望获取原本的底层数组，改用 all 方法。

toJson()#
//将集合转换成 JSON：    
$collection = collect(['name' => 'Desk', 'price' => 200]);

$collection->toJson();

// '{"name":"Desk","price":200}'


transform()#
//遍历集合并对集合内每一个项目调用指定的回调函数。集合的项目将会被回调函数返回的数值取代掉：
$collection = collect([1, 2, 3, 4, 5]);

$collection->transform(function ($item, $key) {
    return $item * 2;
});

$collection->all();

// [2, 4, 6, 8, 10]


unique()#
//unique 方法返回集合中所有唯一的项目：
$collection = collect([1, 1, 2, 2, 3, 4, 2]);

$unique = $collection->unique();

$unique->values()->all();

// [1, 2, 3, 4]

//排序过的集合保有原来的数组键。在这个例子中我们用了 values 方法重设键为连续的数字索引。

//当处理内嵌的数组或对象，你可以指定用来决定唯一性的键：
$collection = collect([
    ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
    ['name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'],
    ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
    ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
    ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
]);

$unique = $collection->unique('brand');

$unique->values()->all();

/*
    [
        ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
    ]
*/


//你可以传入自己的回调函数来决定项目的唯一性：
$unique = $collection->unique(function ($item) {
    return $item['brand'].$item['type'];
});

$unique->values()->all();

/*
    [
        ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
        ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
        ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
    ]
*/


values()#
//返回「键」重新被设为「连续整数」的新集合：    
$collection = collect([
    10 => ['product' => 'Desk', 'price' => 200],
    11 => ['product' => 'Desk', 'price' => 200]
]);

$values = $collection->values();

$values->all();

/*
    [
        0 => ['product' => 'Desk', 'price' => 200],
        1 => ['product' => 'Desk', 'price' => 200],
    ]
*/


where()#
//以一对指定的「键／数值」筛选集合：
$collection = collect([
    ['product' => 'Desk', 'price' => 200],
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Bookcase', 'price' => 150],
    ['product' => 'Door', 'price' => 100],
]);

$filtered = $collection->where('price', 100);

$filtered->all();

/*
[
    ['product' => 'Chair', 'price' => 100],
    ['product' => 'Door', 'price' => 100],
]
*/
//以严格比对检查数值。使用 whereLoose 方法以宽松比对进行筛选。


whereLoose()#
//这个方法与 where 方法有着一样的形式；但是会以「宽松」比对来比对数值：


zip()#
//zip 方法将集合与指定数组同样索引的值合并在一起：
$collection = collect(['Chair', 'Desk']);

$zipped = $collection->zip([100, 200]);

$zipped->all();

// [['Chair', 100], ['Desk', 200]]

