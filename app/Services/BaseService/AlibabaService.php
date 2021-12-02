<?php
/**
 * AlibabaService
 *
 * @author huaixiu.zhen
 * @link https://www.litblc.com
 * 2021/12/1 10:07
 **/

namespace App\Services\BaseService;

use OSS\OssClient;
use App\Services\Service;
use OSS\Core\OssException;
use Illuminate\Http\Response;
use AlibabaCloud\Facebody\Facebody;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\SDK\ViapiUtils\ViapiUtils;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;


class AlibabaService extends Service
{
    private static $accessKey;
    private static $accessSecret;

    private static $ossConfig;

    /**
     * 初始化人脸编辑、动漫风配置
     */
    private static function initImg()
    {
        if (!self::$accessKey || !self::$accessSecret) {
            self::$accessKey = env('ImgAccessKeyId');
            self::$accessSecret = env('ImgAccessKeySecret');
        }
    }

    /**
     * 初始化oss配置
     */
    private static function initOss()
    {
        if (!self::$ossConfig) {
            self::$ossConfig = config('filesystems.aliyun_oss');
        }
    }

    /**
     * 上传图片到阿里OSS，返回路径
     *
     * @param $file
     * @return \Illuminate\Http\JsonResponse
     */
    public static function uploadFile($file)
    {
        self::initOss();
        if ($file->isValid()) {
            if (self::$ossConfig) {
                try {
                    $url = '';
                    $fileName = self::uuid('anime-').'.'.$file->extension();
                    $filePath = $file->path();
                    $ossClient = new OssClient(self::$ossConfig['accessKey'], self::$ossConfig['secretKey'], self::$ossConfig['endpoint']);
                    $res = $ossClient->uploadFile(self::$ossConfig['bucket'], $fileName, $filePath);
                    if (array_key_exists('info', $res)) {
                        $url = $res['info']['url'];
                    }

                    return response()->json(
                        ['data' => $url],
                        Response::HTTP_OK
                    );
                } catch (OssException $e) {
                    return response()->json(
                        ['message' => $e->getMessage()],
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

    }

    /**
     * 获取上传的临时目录
     *
     * @param $filePath
     * @return false|string
     */
    public static function getUrl($filePath)
    {
        self::initImg();
        try {
            return ViapiUtils::upload(self::$accessKey, self::$accessSecret, $filePath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 调用第三方 进行转换图片
     *
     * @param $algoType
     * @param $file
     * @return \Illuminate\Http\JsonResponse
     * @throws ClientException
     */
    public static function handleImage($algoType, $file)
    {
        self::initImg();
        if ($file->isValid()) {
            // $fileUrl = self::getUrl($file->path());
            $fileUrl = self::uploadFile($file);


            if (!$fileUrl->getData()) {
                return response()->json(
                    ['message' => __('app.upload_file_qiniu_fail')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $url = $fileUrl->getData()->data;
            //$url = str_replace('http://', 'https://', $url);

            // 处理
            AlibabaCloud::accessKeyClient(self::$accessKey, self::$accessSecret)
                ->regionId('cn-shanghai')
                ->asDefaultClient();
            try {
                $request = Facebody::v20191230()->generateHumanAnimeStyle();
                $result = $request->withImageURL($url)
                    ->withAlgoType($algoType)
                    ->debug(env('APP_DEBUG'))
                    ->connectTimeout(1)
                    ->timeout(1)
                    ->request()
                    ->toArray();

                if (is_array($result) && array_key_exists('Data', $result)) {
                    $res = str_replace('http://', 'https://', $result['Data']['ImageURL']);

                    return response()->json(
                        ['data' => $res],
                        Response::HTTP_OK
                    );
                } else {
                    return response()->json(
                        ['message' => __('app.cover_image_fail')],
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }

            } catch (ClientException $exception) {
                // $message = $exception->getMessage();
                $message = __('app.cover_image_fail');
                return response()->json(
                    ['message' => $message],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            } catch (ServerException $exception) {
                // $message = 'Server:' . $exception->getMessage();
                $message = __('app.cover_image_fail');
                return response()->json(
                    ['message' => $message],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
