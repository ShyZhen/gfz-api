<?php


namespace Zhiyi\Plus\Lib\AlibabaCloud;


use AlibabaCloud\Client\AlibabaCloud;
use Exception;
use Zhiyi\Plus\Lib\Log\Elog;

/**
 * 阿里afs服务
 *
 * @link https://help.aliyun.com/document_detail/122071.html?spm=a2c4g.11186623.6.566.6180284anIjBvn
 * Class AfsService
 * @package Zhiyi\Plus\Lib\AlibabaCloud
 */
class AfsService extends BaseService
{
    /**
     * @param string $token
     * @return mixed
     * @throws Exception
     */
    public function analyzeNvc(string $token)
    {
        $result = $this->_analyzeNvc($token);

        return $result['BizCode'];
    }

    /**
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function analyzeNvcSecond(string $token)
    {
        $result = $this->_analyzeNvc($token);

        if (!in_array($result['BizCode'], [100, 200])) {
            throw new \Exception("滑块二次验证失败[{$result['BizCode']}]");
        }

        return true;
    }

    /**
     * @param $token
     * @return array
     * @throws Exception
     */
    protected function _analyzeNvc($token)
    {
        $query = [
            'ScoreJsonStr' => '{"200":"PASS","400":"NC","800":"BLOCK"}',
            'Data' => $token,
        ];

        try {
            AlibabaCloud::accessKeyClient($this->ak, $this->sk)
                ->regionId("cn-hangzhou")
                ->asDefaultClient();

            $result = AlibabaCloud::rpc()
                ->product('afs')
                ->scheme('https') // https | http
                ->version('2018-01-12')
                ->action('AnalyzeNvc')
                ->method('POST')
                ->host('afs.aliyuncs.com')
                ->options([
                    'query' => $query
                ])
                ->request()
                ->toArray();

        } catch (Exception $e) {
            Elog::log('alibabaCloud-afsAnalyzeNvc', $e, $query, Elog::LEVEL_ERROR);
            throw new Exception('远端服务异常', '-2');
        }

        return $result;
    }

    /**
     * @param string $sessionId
     * @param string $token
     * @param string $sig
     * @param string $scene
     * @param string $remoteIp
     * @throws Exception
     * @return bool
     */
    public function authenticateSigRequest(string $sessionId, string $token, string $sig, string $scene, string $remoteIp)
    {
        $appKey = config("aliyun.afs.appKey", 'FFFF0N00000000009ED3');

        $query = [
            "SessionId" => $sessionId,
            "Token" => $token,
            "Sig" => $sig,
            "Scene" => $scene,    //'nvc_message_h5',
            "AppKey" => $appKey,  //'FFFF0N00000000009ED3',
            "RemoteIp" => $remoteIp,
        ];

        try {
            AlibabaCloud::accessKeyClient($this->ak, $this->sk)
                ->regionId("cn-hangzhou")
                ->asDefaultClient();

            $result = AlibabaCloud::rpc()
                ->product('Afs')
                ->scheme('https') // https | http
                ->version('2018-01-12')
                ->action('AuthenticateSig')
                ->method('POST')
                ->host('afs.aliyuncs.com')
                ->options([
                    'query' => $query
                ])
                ->request()
                ->toArray();
        } catch (\Exception $e) {
            Elog::log('alibabaCloud-afsAuthenticateSig', $e, $query, Elog::LEVEL_ERROR);
            throw new Exception('远端服务异常', '-2');
        }

        if ($result['Code'] != 100) {
            throw new Exception(sprintf("【%s】%s", $result['RequestId'], $result['Msg']), $result['Code']);
        }

        return true;
    }
}
