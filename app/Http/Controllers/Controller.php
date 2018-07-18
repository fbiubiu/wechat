<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Biz\WechatApi;
use \Redis;
use GuzzleHttp\Client;

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
        if(1){
            $accessTokenData = (new WechatApi())->getAccessToken();
            $accessTokenData = json_decode($accessTokenData,true);
            $access_token = $accessTokenData['access_token'];
            $expire_time = $accessTokenData['expires_in'];
            $redis->setex($key,$expire_time,$access_token);
        }

        return $access_token;
    }

    public function responseMsg1()
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
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken;

        $requesData = [
            'button' => [
                [
                    'type' => 'click',
                    'name' => '每日一词',
                    'key' =>  'V1001_DAILY_WORD'
                ],
                [
                    'type' => 'click',
                    'name' => '机经',
                    'key' =>  'V1001_jijing'
                ]
            ],

        ];

        $client = new Client();
        $res = $client->request('POST',$url, $requesData);
        $res = $res->getBody();
        $res = json_decode($res,true);

        if($res['errcode'] == 0){
            echo 'success';
        }else{
            echo 'error';
        }
        return $res;

    }

	// 回复客户发送的消息
    public function responseMsg()
    {
		//get post data, May be due to the different environments
        // 接受微信服务器端传递过来的客户发送的数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

      	//判断客户端发送的消息是否为空
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                   // 防止xml实体攻击,提高安全性
                libxml_disable_entity_loader(true);
                // 将xml数据转化为对象
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                // 获取客户传递的数据
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);   // 用户发送的消息内容
                $time = time();
                // 拼接出回复客户端的xml数据格式
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>"; 
                            // 判断发送的消息是否为空,不为空则进行回复            
				if(!empty( $keyword )) 
                {    // 回复的消息类型
              		$msgType = "text";
                    // 回复的内容
                	$contentStr = "Welcome to wechat world!";
                    // 替换xml中占位符,完善发送消息的xml数据
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    // 向客户端输出消息
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }

	

}
