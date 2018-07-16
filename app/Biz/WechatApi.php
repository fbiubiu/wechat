<?php
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

    public function getAccessToken()
    {
        $url = $this->apiUrl['accessToken'];
        $client = new Client();
        $client->request('get',$url);
    }
}