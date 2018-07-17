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
        $params = request()->all();
        $token = WechatApi::TOKEN;
        $verifiedParams = [$params['timestamp'], $params['nonce'], $token];
        sort($verifiedParams, SORT_STRING);
        $verifiedStr = sha1(implode($verifiedParams));
        file_put_contents('/server/website/wechat/storage/logs/wechat.log',$verifiedStr.':'.$params['signature']."\n",FILE_APPEND);
        if( $verifiedStr ==  $params['signature']){
            return true;
        }else{
            return false;
        }
    }

    public function accessToken()
    {
        (new WechatApi())->getAccessToken();
    }
}
