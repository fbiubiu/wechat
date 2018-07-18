<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Biz\WechatApi;
use \Redis;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /*
     * 微信配置路由
     * @return bool
     */
    public function index()
    {
        return $this->responseMsg();
    }

    /*
     * token验证方法
     * @return bool
     */
    private function checkToken()
    {
        $filename = __DIR__.'/../../../storage/logs/wechat.log';
        $params = request()->all();
        file_put_contents($filename,'params:'.json_encode($params)."\n",FILE_APPEND);

        $token = WechatApi::TOKEN;
        $timestamp = $params['timestamp'];
        $nonce = $params['nonce'];
        $signature = $params['signature'];
        $echostr = $params['echostr'];

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
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->auth('fengphp@126');
        $key = 'wechat:access_token';
        $access_token = $redis->get($key);
        if(empty($access_token)){
            $accessTokenData = (new WechatApi())->getAccessToken();
            $accessTokenData = json_decode($accessTokenData,true);
            $access_token = $accessTokenData['access_token'];
            $expire_time = $accessTokenData['expires_in'];
            $redis->setex($key,$expire_time,$access_token);
        }

        return $access_token;
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $this->recordLog('访问成功');
        if (!empty($postStr)){
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[text]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
            if($postObj->MsgType=='event'){
                if($postObj->Event == 'CLICK'){
                    if($postObj->EventKey == 'V1001_TODAY_MUSIC'){

                        $contentStr = "微信连,www.phpos.net";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $contentStr);
                        echo $resultStr;
                    }
                }
            }

        }else {
            echo "success";
            exit;
        }
    }

    public function recordLog($content)
    {
        $filename = __DIR__.'/../../../storage/logs/wechat.log';
        file_put_contents($filename,'content:'.json_encode($content)."\n",FILE_APPEND);
    }

    public function createMenu()
    {
        $accessToken = $this->accessToken();
    }

}
