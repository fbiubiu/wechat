<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Biz\WechatApi;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function verifyToken()
    {
        umask(0);
        $params = request()->all();
        $token = WechatApi::TOKEN;
        $verifiedParams = [$params['timestamp'], $params['nonce'], $token];
        sort($verifiedParams, SORT_STRING);
        $verifiedStr = sha1(implode($verifiedParams));
        $filename = __DIR__.'/../../../storage/logs/wechat.log';

        file_put_contents($filename,$verifiedStr.':'.$params['signature']."\n".'echostr:'.$params['echostr']."\n",FILE_APPEND);
        if( $verifiedStr ==  $params['signature']){
            return $params['echostr'];
        }else{
            return false;
        }
    }

    public function accessToken()
    {
        (new WechatApi())->getAccessToken();
    }

    public function test()
    {
        echo 1;die;
    }
}
