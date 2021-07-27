<?php
/**
 * 王者荣耀小程序控制器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/4
 * Time: 16:45
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MPWangzheService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MPWangzheController extends Controller
{
    private $mpWangzheService;

    /**
     * FileController constructor.
     *
     * @param $mpWangzheService $mpWangzheService
     */
    public function __construct(MpWangzheService $mpWangzheService)
    {
        $this->mpWangzheService = $mpWangzheService;
    }

    /**
     * 获取我的皮肤碎片，没有数据认为是注册动作、有则认为登录动作
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMySkin()
    {
        return $this->mpWangzheService->getMySkin();
    }

    /**
     * 获取皮肤碎片详细日志
     *
     * @return mixed
     */
    public function getMySkinLogs()
    {
        return $this->mpWangzheService->getMySkinLogs();
    }


    /**
     * 主动动作的碎片更新（除了登录、注册等被动动作）
     *
     * @param $type
     * @return mixed
     */
    public function updateOtherSetSkin($type)
    {
        $allowTypes = array_keys($this->mpWangzheService::TYPE);
        if (in_array($type, $allowTypes) && $type !== 'register') {
            return $this->mpWangzheService->updateOtherSetSkin($type);
        }
    }

    /**
     * 抽奖活动列表（已完成、进行中）
     *
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrawList($type): \Illuminate\Http\JsonResponse
    {
        return $this->mpWangzheService->getDrawList($type);
    }

    /**
     * 参与抽奖
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinDraw($id)
    {
        return $this->mpWangzheService->joinDraw($id);
    }

    /**
     * 查看参与活动的用户id
     *
     * @param $id
     * @return mixed
     */
    public function getDrawUserList($id)
    {
        return $this->mpWangzheService->getDrawUserList($id);
    }

    /**
     * 我的中奖记录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyDrawList()
    {
        return $this->mpWangzheService->getMyDrawList();
    }

    public function giveSbSkin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'skin_num' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->mpWangzheService->giveSkin(
                $request->get('user_id'),
                $request->get('skin_num')
            );
        }
    }

}
