<?php
/**
 * 王者荣耀小程序后台控制器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2022/4/13
 * Time: 16:30
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\MPWangzheAdminService;

class MPWangzheAdminController extends Controller
{
    private $mpWangzheAdminService;

    /**
     * FileController constructor.
     *
     * @param $mpWangzheAdminService $mpWangzheAdminService
     */
    public function __construct(MPWangzheAdminService  $mpWangzheAdminService)
    {
        $this->mpWangzheAdminService = $mpWangzheAdminService;
    }

    /**
     * 首个页面，登录成功后才记录appid和uuid到storage，并进入功能列表页（抽奖列表查询、用户兑换记录查询）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginPlatform(Request $request)
    {
        $appId = $request->get('app_id');
        $platformUuid = $request->get('platform_id');

        if (!$appId || !$platformUuid) {
            return response()->json(
                ['message' => __('app.illegal_input')],
                Response::HTTP_BAD_REQUEST
            );
        }

        $platformId = $this->mpWangzheAdminService->checkPlatformAndReturn($appId, $platformUuid);
        if ($platformId == 0) {
            return response()->json(
                ['message' => __('app.illegal_input')],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return response()->json(
                ['data' => $platformId],
                Response::HTTP_OK
            );
        }
    }

    /**
     * 抽奖活动列表（已完成、进行中），点击进入更新页
     *
     * @param $request
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrawList(Request $request, $type): \Illuminate\Http\JsonResponse
    {
        $check = $this->loginPlatform($request);
        if ($check->status() == Response::HTTP_OK) {
            $platformId = $check->getData()->data;

            return $this->mpWangzheAdminService->getDrawList($type, $platformId);
        } else {
            return $check;
        }
    }

    /**
     * 更新抽奖信息
     * 结束的不许更新 只更新title/winnerId/limitUser三个字段
     *
     * @param Request $request
     * @param $drawId
     * @return \Illuminate\Http\JsonResponse
     */
    public function editDraw(Request $request, $drawId)
    {
        $check = $this->loginPlatform($request);
        if ($check->status() == Response::HTTP_OK) {
            $platformId = $check->getData()->data;

            $params['title'] = substr($request->get('title', ''), 0, 128);
            $params['winnerId'] = (int) $request->get('winner_id', 0);
            $params['limitUser'] = (int) $request->get('limit_user', 0);

            return $this->mpWangzheAdminService->editDraw($platformId, $drawId, $params);
        } else {
            return $check;
        }
    }

    /**
     * 添加抽奖信息
     * 最多10个每天，先上传图片，只更新title/winnerId/limitUser/image四个字段
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDraw(Request $request)
    {
        $check = $this->loginPlatform($request);
        if ($check->status() == Response::HTTP_OK) {
            $platformId = $check->getData()->data;

            $params['title'] = substr($request->get('title', ''), 0, 128);
            $params['winnerId'] = (int) $request->get('winner_id', 0);
            $params['limitUser'] = (int) $request->get('limit_user', 0);
            $params['image'] = substr($request->get('image', ''), 0, 128);

            if (!$params['title'] || !$params['limitUser'] || !$params['image']) {
                return response()->json(
                    ['message' => __('app.illegal_input')],
                    Response::HTTP_BAD_REQUEST
                );
            }

            return $this->mpWangzheAdminService->addDraw($platformId, $params);
        } else {
            return $check;
        }
    }

    /**
     * 查询用户兑换碎片记录
     * 给用户兑换前一定要看兑换记录的截图，检查小程序名字是不是自己的，避免出现给其他小程序兑换的情况
     *
     * @param Request $request
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkinDeteil(Request $request, $userUuid)
    {
        $check = $this->loginPlatform($request);
        if ($check->status() == Response::HTTP_OK) {
            $platformId = $check->getData()->data;

            return $this->mpWangzheAdminService->getSkinDeteil($platformId, $userUuid);
        } else {
            return $check;
        }
    }

    /**
     * 设置为已处理
     *
     * @param Request $request
     * @param $id
     * @param $userUuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSkinStatus(Request $request, $id, $userUuid)
    {
        $check = $this->loginPlatform($request);
        if ($check->status() == Response::HTTP_OK) {
            $platformId = $check->getData()->data;

            return $this->mpWangzheAdminService->setSkinStatus($platformId, $id, $userUuid);
        } else {
            return $check;
        }
    }

}
