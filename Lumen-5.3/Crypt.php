<?php

// 在使用 Lumen 的加密器前，你应该先设置 bootstrap/app.php 配置文件中的 APP_KEY 选项，设置值需要是 32 个字符的随机字符串。如果没有适当地设置这个值，所有被 Lumen 加密的值都将是不安全的

# Basic use

## encrypt
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    /**
     * 保存用户的机密消息
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function storeSecret(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->fill([
            'secret' => Crypt::encrypt($request->secret)
        ])->save();
    }
}

## decrypt
use Illuminate\Contracts\Encryption\DecryptException;

try{
    $decrypted = Crypt::decrypt($encryptedValue);
} catch (DecryptException $e) {
    //
}











