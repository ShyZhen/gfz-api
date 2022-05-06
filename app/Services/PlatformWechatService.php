<?php
/**
 * 微信平台相关接口
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Response;
use App\Models\MPWangzhePlatform;
use App\Services\BaseService\RedisService;

class PlatformWechatService extends Service
{
    public const SUBSCRIBE_SEND = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?';  // 服务器端发起消息订阅
    public const OAUTH_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?';    // 获取access_token

    private $redisService;

    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    public function sendSubscribe($platformId, $userId, $arr): bool
    {
        $openid = $appId = $appSecret = '';

        $platformRow = MPWangzhePlatform::find($platformId);
        $userRow = User::find($userId);

        if ($userRow) {
            $openid = $userRow->wechat_openid;
        }

        if ($platformRow) {
            $appId = $platformRow->app_id;
            $appSecret = $platformRow->app_secret;
        }

        // 没配置直接返回
        if (!$platformRow->subscribe_key) {
            return true;
        }

        // 发送订阅消息
        if ($openid && $appId && $appSecret) {
            $accessToken = $this->getAccessToken($appId, $appSecret, $platformId);
            $params = [
                'access_token' => $accessToken,
            ];

            $requestParams = $this->urlParams($params);
            $url = self::SUBSCRIBE_SEND . $requestParams;

            // 模板参数
            $template = "{
                  \"touser\": \"{$openid}\",
                  \"template_id\": \"nxVbi1PgbolShJP3y2Pox8nJPnZwRHfjRwztAPMkINs\",
                  \"page\": \"index\",
                  \"miniprogram_state\":\"formal\",
                  \"lang\":\"zh_CN\",
                  \"data\": {
                      \"thing10\": {
                          \"value\": \"{$arr['title']}\"
                      },
                      \"thing18\": {
                          \"value\": \"{$arr['remark']}\"
                      }
                  }
                }";

            $result = $this->curlPost($url, $template);
            // dd($result);
            return true;
        }
    }


    /**
     * 通过appid和secret获取AccessToken
     *
     * @param $appId
     * @param $appSecret
     * @param $platformIdKey
     * @return string
     */
    public function getAccessToken($appId, $appSecret, $platformIdKey): string
    {
        $key = 'wechattoken:'.$platformIdKey;
        if ($this->redisService->isRedisExists($key)) {
            return $this->redisService->getRedis($key);
        }

        $params = [
            'grant_type' => 'client_credential',
            'appid' => $appId,
            'secret' => $appSecret,
        ];

        $request_params = $this->urlParams($params);
        $url = self::OAUTH_TOKEN_URL . $request_params;

        $result = $this->httpRequest($url, [], false);

        if (!is_array($result) || !isset($result['access_token'])) {
            return '';
        } else {
            $this->redisService->setRedis($key, $result['access_token'], 'EX', 7000);

            return $result['access_token'];
        }
    }

    public function urlParams($params)
    {
        $buff = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                $buff .= $k . '=' . $v . '&';
            }
        }
        $buff = trim($buff, '&');

        return $buff;
    }

    /**
     * @param string $url
     * @param array  $params
     * @param bool   $post
     */
    public function httpRequest($url, $params, $post = true)
    {
        $header = [
            'Content-Type: application/json; charset=utf-8',
        ];

        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        } elseif (is_array($params) && 0 < count($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . json_encode($params, JSON_UNESCAPED_UNICODE));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $data = curl_exec($ch);
        if (curl_error($ch)) {
            trigger_error(curl_error($ch));

            return null;
        }
        curl_close($ch);

        return json_decode($data, true);
    }

    private function curlPost($url,$data=array()){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
