<?php

Laravel 的哈希加密#
简介#
Laravel 通过 Hash facade 提供 Bcrypt 加密来保存用户密码
// 如果您在当前的 Laravel 应用程序中使用了内置的 LoginController 和 RegisterController 类，它们将自动使用 Bcrypt 进行注册和身份验证。

//由于 Bcrypt 的 「加密系数（word fator）」可以任意调整，这使它成为最好的加密选择。这代表每一次加密的次数可以随着硬件设备的升级而增加。


基本用法#
//你可以通过调用 Hash facade 的 make 方法加密一个密码：

<?php

namespace App\Http\Controllers;

use Illminate\Http\Request;
use Illminate\Support\Facades\Hash;
use Illminate\Http\Controllers\Controller;

class UpdatePasswordController extends Controller
{
    /**
     *  更新用户密码
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        // Validate the new password length...

        $request->user()->fill([
            'password' => Hash::make($request->newPassword)
        ])->save();
    }
}

根据哈希值验证密码#
check 方法允许你通过一个指定的纯字符串跟哈希值进行验证
//如果你目前正使用 Laravel 内含的 LoginController , 你可能不需要直接使用该方法，它已经包含在控制器当中并且会被自动调用：
if (Hash::check('plain-text', $hashedPassword)) {
    // 密码对比...
}

验证密码是否须重新加密#
//needsRehash 函数允许你检查已加密的密码所使用的加密系数是否被修改：
if (Hash::needsRehash($hashed)) {
    $hashed = Hash::make('plain-text');
}