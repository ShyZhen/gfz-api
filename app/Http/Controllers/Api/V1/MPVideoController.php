<?php
/**
 * 视频处理类 item collect等操作
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/4
 * Time: 16:45
 */

namespace App\Http\Controllers\Api\V1;

use App\Services\MPVideoService;
use App\Http\Controllers\Controller;

class MPVideoController extends Controller
{
    private $mpVideoService;

    /**
     * FileController constructor.
     *
     * @param MPVideoService $mpVideoService
     */
    public function __construct(MPVideoService $mpVideoService)
    {
        $this->mpVideoService = $mpVideoService;
    }

    public function getList($type): \Illuminate\Http\JsonResponse
    {
        if ($type) {
            $res = $this->mpVideoService->getListByType($type);
        } else {
            $res = $this->mpVideoService->getAllList();
        }
        return $res;
    }

    public function getScroll($type, $vid): \Illuminate\Http\JsonResponse
    {
        return $this->mpVideoService->getScroll($type, $vid);
    }


}
