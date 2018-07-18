<?php
namespace App\Biz;

use \GuzzleHttp\Client;
/**
 * Created by Jianbin.Feng.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 20:44
 */
class WechatApi
{
    public $apiUrl = [
        'accessToken' => 'https://api.weixin.qq.com/cgi-bin/token',
    ];

    public $appId = 'wx4703e6551e4c0ad4';

    public $appsecret = 'b5827fd9b6e3c5a75f3f9d143b22d299';

    const TOKEN= 'fbiubiu';

    public function getAccessToken()
    {
        $paramsGet = 'grant_type=client_credential&appid='.$this->appId.'&secret='.$this->appsecret;
        $url = $this->apiUrl['accessToken'].'?'.$paramsGet;
        $client = new Client();
        $res = $client->request('GET',$url);
        $res = $res->getBody();
        return $res;
    }
}
