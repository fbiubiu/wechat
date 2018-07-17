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

    public $appId = 'wx4381134f02842d5e';

    public $appsecret = '2dda7e52485e0c66269aacb62961f709';

    public function getAccessToken()
    {
        $paramsGet = 'grant_type=client_credential&appid='.$this->appId.'&secret='.$this->appsecret;
        $url = $this->apiUrl['accessToken'].'?'.$paramsGet;
        $client = new Client();
        $res = $client->request('GET',$url);
        print_r($res->getBody());die;
    }
}