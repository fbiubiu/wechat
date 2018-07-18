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
    public function index()
    {
        $params = request()->all();
        $filename = __DIR__.'/../../../storage/logs/wechat.log';
        file_put_contents($filename,'params:'.json_encode($params)."\n",FILE_APPEND);

        $token = WechatApi::TOKEN;
        $timestamp = $params['timestamp'] ?? '';
        $nonce = $params['nonce'] ?? '';
        $signature = $params['signature'] ?? '';
        $echostr = $params['echostr'] ?? '';

        $verifiedParams = [$timestamp, $nonce, $token];
        sort($verifiedParams, SORT_STRING);
        $verifiedStr = sha1(implode($verifiedParams));
        file_put_contents($filename,'verifyData:'.$verifiedStr.':'.$signature."\n".'echostr:'.$echostr."\n",FILE_APPEND);
        if( $verifiedStr ==  $signature){
            return $echostr;
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


}
