<?php
/**
 * AlibabaService
 *
 * @author huaixiu.zhen
 * @link https://www.litblc.com
 * 2021/12/1 10:07
 **/

namespace App\Services\BaseService;

use App\Services\Service;
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

    private static function init()
    {
        if (!self::$accessKey || !self::$accessSecret) {
            self::$accessKey = env('ImgAccessKeyId');
            self::$accessSecret = env('ImgAccessKeySecret');
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
        self::init();
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
        self::init();
        if ($file->isValid()) {
            $fileUrl = self::getUrl($file->path());

            if (!$fileUrl) {
                return response()->json(
                    ['message' => __('app.upload_file_qiniu_fail')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // 处理
            AlibabaCloud::accessKeyClient(self::$accessKey, self::$accessSecret)
                ->regionId('cn-shanghai')
                ->asDefaultClient();
            try {
                $request = Facebody::v20191230()->generateHumanAnimeStyle();
                $result = $request->withImageURL($fileUrl)
                    ->withAlgoType($algoType)
                    ->debug(env('APP_DEBUG'))
                    ->connectTimeout(1)
                    ->timeout(1)
                    ->request()
                    ->toArray();

                if (is_array($result) && array_key_exists('Data', $result)) {
                    return response()->json(
                        ['data' => $result['Data']],
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
