<?php


namespace Zhiyi\Plus\Lib\AlibabaCloud;


use AlibabaCloud\Client\AlibabaCloud;
use Exception;
use Zhiyi\Plus\Lib\Log\Elog;

/**
 * 阿里Dypnsapi服务
 * @link https://help.aliyun.com/document_detail/84541.html?spm=a2c4g.11186623.6.553.ffd77d500CiqHg
 *
 * Class DypnsService
 * @package Zhiyi\Plus\Lib\AlibabaCloud
 */
class DypnsService extends BaseService
{
    /**
     * 通过客户端获取的token从阿里获取手机号码
     *
     * @param string $token
     * @return array
     */
    public function getMobileByToken(string $token)
    {
        try {
            $query = [];
            $accessToken = $token;
            if (!$accessToken) {
                throw new Exception("accessToken参数不能为空", -1);
            }

            if (!$this->ak || !$this->sk) {
                throw new Exception("服务参数配置为空！");
            }

            AlibabaCloud::accessKeyClient($this->ak, $this->sk)
                //->regionId('cn-hangzhou')
                ->asDefaultClient();

            $query = [
                //'RegionId' => "cn-hangzhou",
                //'AccessToken' => "eyJjIjoiM05CYnAwZmFJOFBuZ3A0eWhUSW80bTZIejZHVzFlT2pRaWtERUVRazQzZjZnRE1KWTl6MzdoS1htVU45TFFCR3U4aUhvSE9XZHVoUFxuRVwvbmFtK2dBRW83TFZaZkxuTnF1a2JMZmhZSWJadm54OWlDeitwOEM2aUpxdmhkaThlUklQc2lBTU1jSUpHM1pHOW9XdHE2VG9KcHZcbnFaZDA5VTB2MzZPMDZxTkRcLzNlcmVtVFFHVW1yK3RSR1hNRzRKdk1NN3F2eVwvU21xUXVsZW90eDdxS2VHQno5QzVGd2ZBKzR4NlZ1XC9cblFkcWNiU0p6Vzlqa204VDFXTFNiWTZzZTl5aUh6K25VUm16S0ExM0gwdFpoemUrZFI2UVBKMGVNcmpDd1Y5VGFhOXR3TlBSZEJYTk5cbnQ0UDIycmQzZzdRb3dQeVd4WFlscFZBWWhleGptNm9hXC9rVEdtdEMrOG10eUR3ekNPQnNBT0FsQkh6RTE4THl0NHhZM0ZURFJVOGdIXG5yVzJ4Y3dVMTVkd2N6bUVJSEdTZVFXMFFtc3gyT2hXOTF3aEltREpacUcraUk2dlQ2anpiYmFjTGd3VVV3RVpJZXdiR2dsOUpQUmxJXG5sZkcrNnplQmZ1SlhkdU1zdlwvZjIrVFFLY0tpNEJOeXpjM28wRWxPZGVRMUVhZ2hqM0o4N0dUN3NxR1BIMk1vbmVqY0RIR211RVFoRVxuaWk1RnFUSmFxTmtPdTJxOG1YN2FmdVhGcGdTUUE2SnEyNjAyK290WmJiUHh2QVwvbVBUUVNHTTlhMENNMnc1Q3RkSUlsNVpvakN3QmFcbjdQVGVHa1ZZSkNVPVxuIiwiayI6Im5WR3QxU2szd3RndEVVcVRBUmRkMnpzNlBQbXRxZG9FMzB6T0FvMFpPaTJaR2FkNWhKVVZtQXJ1V0pvM0FDZjBzT0hqWEZSUVpXTFZ2eWw0OHhqU3BNRk9pVEh4eUZ6REh1d0ErT1RnODhrZDJvcnlaVzlaS0oxNjhYV1pkdUw0Y01ScE14aTVJd1RydXptd2NMOFprVzF4RFIrVWlFNGtlR1dGeDh1R2xnYlhPamU2SHZLV2Zzbm1XbGR5Yk81MUhhbDE0dFg5Z0ZNTisxbDFqdmd0dU9GYUtlWW9nNTNTMUtvT29SU0Nzb2hRcTFQdStJSUlFbzl1VzJLNXh4Q3lSXC9ydTFBdlY3TUdmZ2V5NEc4WVdGNmx0XC8yWVVvRTVCcjJ2NDc4XC9ibk56bVRVd0NrUnVDbWNVVDhrSzJybTJBWlRYdTNzQzJcL3JVK1hVTXBtS0JyeWc9PSIsIm8iOiJBbmRyb2lkIn0=",
                'AccessToken' => $accessToken,
                'OutId' => md5(rand(0, 10000) .  uniqid() . getmypid()),
            ];
            $result = AlibabaCloud::rpc()
                ->product('Dypnsapi')
                ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('GetMobile')
                ->method('POST')
                ->host('dypnsapi.aliyuncs.com')
                ->options([
                    'query' => $query
                ])
                ->request()->toArray();

            if ($result['Code'] == 'OK') {
                $data = [
                    'code' => 0,
                    'message' => 'ok',
                    'data' => [
                        'mobile' => $result['GetMobileResultDTO']['Mobile'],
                    ],
                ];
            } else {
                throw new Exception(
                    sprintf("【%s】%s-%s", $result['RequestId'], $result['Code'], $result['Message']), -1);
            }
        } catch (\Exception $e) {
            $data = [
                'code' => $e->getCode() ?: '-1',
                'message' => "远端服务异常",
                'data' => [],
            ];
            Elog::log('AlibabaCloud-getMobile', $e, $query, Elog::LEVEL_ERROR);
        }

        return $data;
    }
}
