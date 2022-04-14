<?php

namespace App\Services;

use Illuminate\Http\Response;
use App\Models\MPWangzhePlatform;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\MPWangzheDrawRepository;
use App\Repositories\Eloquent\MPWangzheSkinConvertRepository;

class MPWangzheAdminService extends Service
{
    private $redisService;
    private $mPWangzheDrawRepository;
    private $mPWangzheSkinConvertRepository;

    /**
     * @param RedisService $redisService
     * @param MPWangzheDrawRepository $mPWangzheDrawRepository
     * @param MPWangzheSkinConvertRepository $mPWangzheSkinConvertRepository
     */
    public function __construct(
        RedisService $redisService,
        MPWangzheDrawRepository $mPWangzheDrawRepository,
        MPWangzheSkinConvertRepository $mPWangzheSkinConvertRepository
    ) {
        $this->redisService = $redisService;
        $this->mPWangzheDrawRepository = $mPWangzheDrawRepository;
        $this->mPWangzheSkinConvertRepository = $mPWangzheSkinConvertRepository;
    }

    /**
     * @param $appId
     * @param $platformUuid
     * @return int
     */
    public function checkPlatformAndReturn($appId, $platformUuid): int
    {
        $platformId = 0;
        $key = 'check:times:' . $appId;

        // 如果错误多次，则直接返回 0
        if ($this->verifyLimitTwo($key)) {
            return $platformId;
        }

        $row = MPWangzhePlatform::where(['uuid' => $platformUuid, 'app_id' => $appId])->first();
        if ($row && $row->deleted == 'none') {
            $platformId = $row->id;
        } else {
            // 不正常加入限制，3次就冻结
            $this->verifyLimitOne($key);
        }
        return $platformId;
    }

    /**
     * 抽奖活动列表（已完成、进行中）
     *
     * @param $type
     * @param $platformId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrawList($type, $platformId)
    {
        $data = $this->mPWangzheDrawRepository->model()
            ::where('platform_id', $platformId)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * @param $platformId
     * @param $drawId
     * @param $params
     * @return \Illuminate\Http\JsonResponse
     */
    public function editDraw($platformId, $drawId, $params)
    {
        $data = $this->mPWangzheDrawRepository->model()
            ::where(['platform_id' => $platformId, 'id' => $drawId])
            ->first();

        // 已结束的不让更新
        if ($data->type != 0) {
            return response()->json(
                ['message' => __('app.illegal_input')],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($params['title']) {
            $data->title = $params['title'];
        }

        if ($params['winnerId']) {
            $data->winner_id = $params['winnerId'];
        }

        if ($params['limitUser']) {
            $data->limit_user = $params['limitUser'];
        }

        $data->save();
        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * @param $platformId
     * @param $params
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDraw($platformId, $params)
    {
        if ($this->verifyAddLimit($platformId)) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        $data = $this->mPWangzheDrawRepository->create([
                'platform_id' => $platformId,
                'limit_user' => $params['limitUser'],
                'title' => $params['title'],
                'image' => $params['image'],
                'winner_id' => $params['winnerId'],
            ]);

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 查询某个用户的兑换记录，不同项目之间的用户ID不会重复
     * 给用户兑换前一定要看兑换记录的截图，检查小程序名字是不是自己的，避免出现给其他小程序兑换的情况
     *
     * @param $platformId
     * @param $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSkinDeteil($platformId, $userUuid)
    {
        $data = $this->mPWangzheSkinConvertRepository->model()
            ::select(['id', 'user_id', 'user_uuid', 'convert_num', 'status', 'created_at'])
            ->where('user_uuid', $userUuid)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 兑换完记得要设置兑换成功，避免重复兑换的情况
     *
     * @param $platformId
     * @param $id
     * @param $userUuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSkinStatus($platformId, $id, $userUuid)
    {
        $data = $this->mPWangzheSkinConvertRepository->model()
            ::where(['user_id' => $id, 'user_uuid' => $userUuid])
            ->first();

        if ($data) {
            $data->status = 'success';
            $data->save();
            return response()->json(
                null,
                Response::HTTP_NO_CONTENT
            );
        } else {
            return response()->json(
                ['message' => __('app.illegal_input')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 登录限制
     *
     * @param $key
     * @return false
     */
    private function verifyLimitOne($key)
    {
        $ttl = 7200;
        if ($this->redisService->isRedisExists($key)) {
            $this->redisService->redisIncr($key);
            return false;
        } else {
            $this->redisService->setRedis($key, 1, 'EX', $ttl);
            return false;
        }
    }

    /**
     * 登录限制
     *
     * @param $key
     * @return boolean
     */
    private function verifyLimitTwo($key)
    {
        if ($this->redisService->getRedis($key) > 3) {
            return true;
        }

        return false;
    }

    /**
     * 每天只能新增10个最多
     *
     * @param $account
     * @return bool
     */
    private function verifyAddLimit($account)
    {
        $limit = 10;
        $ttl = 86400;
        $key = 'adddraw:times:' . $account;

        if ($this->redisService->isRedisExists($key)) {
            $this->redisService->redisIncr($key);

            if ($this->redisService->getRedis($key) > $limit) {
                return true;
            }

            return false;
        } else {
            $this->redisService->setRedis($key, 1, 'EX', $ttl);

            return false;
        }
    }

}
