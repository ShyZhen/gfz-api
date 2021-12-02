<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2019/7/3
 * Time: 21:14
 */

namespace App\Services\OAuthService;

use App\Models\User;
use GuzzleHttp\Client;
use App\Services\Service;
use App\Services\FileService;
use Illuminate\Http\Response;
use App\Models\MPWangzhePlatform;

class WechatService extends Service
{
    private static $config;

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $platformUuid
     */
    private static function initConfig($platformUuid = '')
    {
        if (!self::$config) {
            // TODO 上线后兼容代码删掉
            self::$config = config('oauth.wechat');

            // 支持平台版本，更改appid、appsecret
            $row = MPWangzhePlatform::where('uuid', $platformUuid)->first();
            if ($row && $row->deleted == 'none') {
                self::$config['app_id'] = $row->app_id;
                self::$config['app_secret'] = $row->app_secret;
                self::$config['base_url'] = 'https://api.weixin.qq.com/sns/jscode2session';
            }
        }
    }

    /**
     * 根据Code换取用户open-id和union-id
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $code
     * @param $platformUuid
     *
     * @return bool|\Illuminate\Http\JsonResponse|mixed|string
     */
    private static function getOpenIdByCode($code, $platformUuid)
    {
        self::initConfig($platformUuid);

        if (!self::$config['app_id'] || !self::$config['app_secret']) {
            return response()->json(
                ['message' => 'Wechat AppID or AppSecret is Null!'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $authorizeUri = self::$config['base_url'] .
            '?appid=' . self::$config['app_id'] .
            '&secret=' . self::$config['app_secret'] .
            '&js_code=' . $code .
            '&grant_type=authorization_code';

        $client = new Client();
        $response = $client->get($authorizeUri);
        $result = $response->getBody()->getContents();

        if (empty($result) || empty($result = json_decode($result, true))) {
            return false;
        }

        if (array_key_exists('errcode', $result)) {
            return false;
        }

        return $result;
    }

    /**
     * 通过openid登录或创建新用户
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $code
     * @param $userInfo
     * @param $platformUuid
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function wechatLogin($code, $userInfo, $platformUuid)
    {
        $openIdArr = self::getOpenIdByCode($code, $platformUuid);

        if (is_array($openIdArr) && array_key_exists('openid', $openIdArr)) {
            $binding = false;
            $user = User::where('wechat_openid', $openIdArr['openid'])->first();

            // 已经使用微信登录过,但是可能没绑定自己的账号
            if ($user && $user->count()) {

                // 判断是否冻结用户
                if ($user->closure == 'none') {
                    $token = $user->createToken(env('APP_NAME'))->accessToken;

                    // 已经完成绑定逻辑
                    if ($user->email || $user->mobile) {
                        $binding = true;
                    }
                } else {
                    return response()->json(
                        ['message' => __('app.user_is_closure')],
                        Response::HTTP_BAD_REQUEST
                    );
                }
            } else {
                // 创建用户
                $uuid = self::uuid('user-');
                $user = User::create([
                    // 可以重名，使用微信名
//                    'name' => self::uuid($userInfo['nickName'] . '-'),
                    'name' => $userInfo['nickName'],
                    'password' => bcrypt(time()),
                    'uuid' => $uuid,
                    'gender' => $userInfo['gender'] === 1 ? 'male' : 'female',
                    'wechat_openid' => $openIdArr['openid'],
                    'avatar' => FileService::saveOriginAvatar($uuid, $userInfo['avatarUrl']),
                ]);
                $token = $user->createToken(env('APP_NAME'))->accessToken;
            }

            return json_encode(
                ['access_token' => $token, 'binding_status' => $binding],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.token_error')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
