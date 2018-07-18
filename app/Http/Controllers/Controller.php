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

    /*
     * token验证方法
     * @return bool
     */
    public function verifyToken()
    {
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

    /*
     * 获取accessToken
     */
    public function accessToken()
    {
        $accessTokenData = (new WechatApi())->getAccessToken();
        return $accessTokenData;
    }

    public function test()
    {
        echo 1;die;
    }
}
