<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 8/8/25
 * Time: 23:25
 */

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\MPWangzheDrawRepository;
use App\Repositories\Eloquent\MPWangzheSkinRepository;
use App\Repositories\Eloquent\MPWangzheSkinLogRepository;
use App\Repositories\Eloquent\MPWangzheDrawUserRepository;
use App\Repositories\Eloquent\MPWangzheSkinConvertRepository;

class MPWangzheService extends Service
{
    // 获取皮肤碎片的数量
    const SKIN = [
        'register' => 30,
        'login' => 2,
        'share' => 2,
        'banner' => 2,
        'ad' => 3,
    ];

    // 操作类型 1注册 2登录 3分享 4看广告 5点击banner 6赠送 7获赠 9兑换
    const TYPE = [
        'register' => 1,
        'login' => 2,
        'share' => 3,
        'ad' => 4,
        'banner' => 5,
        'give' => 6,
        'get' => 7,
        'used' => 9,
    ];

    // 对应操作类型 每天 的有效次数
    const LIMIT = [
        '1' => 1,  // register
        '2' => 1,  // login
        '3' => 3,  // share
        '4' => 3,  // ad
        '5' => 2,  // banner
    ];

    const TYPE_ON = 0;  // 活动进行中
    const TYPE_OFF = 1; // 活动已结束

    private $redisService;
    private $mPWangzheDrawRepository;
    private $mPWangzheSkinRepository;
    private $mPWangzheSkinLogRepository;
    private $mPWangzheDrawUserRepository;
    private $mPWangzheSkinConvertRepository;

    /**
     * @param RedisService $redisService
     * @param MPWangzheDrawRepository $mPWangzheDrawRepository
     * @param MPWangzheSkinRepository $mPWangzheSkinRepository
     * @param MPWangzheSkinLogRepository $mPWangzheSkinLogRepository
     * @param MPWangzheDrawUserRepository $mPWangzheDrawUserRepository
     * @param MPWangzheSkinConvertRepository $mPWangzheSkinConvertRepository
     */
    public function __construct(
        RedisService $redisService,
        MPWangzheDrawRepository $mPWangzheDrawRepository,
        MPWangzheSkinRepository $mPWangzheSkinRepository,
        MPWangzheSkinLogRepository $mPWangzheSkinLogRepository,
        MPWangzheDrawUserRepository $mPWangzheDrawUserRepository,
        MPWangzheSkinConvertRepository $mPWangzheSkinConvertRepository
    ) {
        $this->redisService = $redisService;
        $this->mPWangzheDrawRepository = $mPWangzheDrawRepository;
        $this->mPWangzheSkinRepository = $mPWangzheSkinRepository;
        $this->mPWangzheSkinLogRepository = $mPWangzheSkinLogRepository;
        $this->mPWangzheDrawUserRepository = $mPWangzheDrawUserRepository;
        $this->mPWangzheSkinConvertRepository = $mPWangzheSkinConvertRepository;
    }

    /**
     * 获取皮肤碎片详细日志
     *
     * @return mixed
     */
    public function getMySkinLogs()
    {
        $userId = Auth::id();
        $data = $this->mPWangzheSkinLogRepository->model()
            ::where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 获取我的皮肤碎片
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMySkin(): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        $skin = $this->mPWangzheSkinRepository->findBy('user_id', $userId);

        // 如果没有该数据，证明是首次登录，需要给注册礼包
        if (!$skin) {
            $data = $this->createSkin($userId, self::SKIN['register'], self::TYPE['register']);
        } else {
            $data = $this->updateSkin($skin, self::SKIN['login'], self::TYPE['login']);
        }

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 主动动作的碎片更新（除了登录、注册等被动动作）
     *
     * @param $type [share,ad ...]
     * @return mixed
     */
    public function updateOtherSetSkin($type)
    {
        $userId = Auth::id();
        $skin = $this->mPWangzheSkinRepository->findBy('user_id', $userId);

        $data = $this->updateSkin($skin, self::SKIN[$type], self::TYPE[$type]);

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 注册更新碎片的动作
     *
     * @param $userId
     * @param $num
     * @param $type
     * @return mixed
     */
    private function createSkin($userId, $num, $type)
    {
        $res = $this->mPWangzheSkinRepository->create([
            'user_id' => $userId,
            'skin_patch' => $num,
        ]);

        $res && $this->writeSkinLog($userId, $num, $type);
        return $num;
    }

    /**
     * 其他更新碎片的动作（除了注册）
     *
     * @param $skin
     * @param $num
     * @param $type
     * @return mixed
     */
    private function updateSkin($skin, $num, $type)
    {
        // 判断是否被限制次数
        if (!$this->updateSkinLimit($skin, $type)) {
            $skin->skin_patch += $num;
            $skin->save() && $this->writeSkinLog($skin->user_id, $num, $type);
        }

        return $skin->skin_patch;
    }

    /**
     * 写日志
     *
     * @param $userId
     * @param $num
     * @param $type
     * @return mixed
     */
    private function writeSkinLog($userId, $num, $type)
    {
        return $this->mPWangzheSkinLogRepository->create([
            'user_id' => $userId,
            'num' => $num,
            'type' => $type
        ]);
    }

    /**
     * 每天次数限制，当天24点结束
     *
     * @param $skin
     * @param $type
     * @return bool
     */
    private function updateSkinLimit($skin, $type): bool
    {
        // 当天24小时过期
        $expireTime = Carbon::tomorrow()->timestamp - time();

        if ($this->redisService->isRedisExists('user:' . $skin->user_id . ':type:' . $type)) {
            $this->redisService->redisIncr('user:' . $skin->user_id . ':type:' . $type);

            // 多次的 计算出对应次数进行判断是否超过
            $limit = self::LIMIT[$type];

            if ($this->redisService->getRedis('user:' . $skin->user_id . ':type:' . $type) > $limit) {
                // 本地环境关闭该限制
                /*
                if (env('APP_ENV') == 'local') {
                    return false;
                }
                */

                return true;
            }


            return false;
        } else {
            $this->redisService->setRedis('user:' . $skin->user_id . ':type:' . $type, 1, 'EX', $expireTime);

            return false;
        }
    }

    /**
     * 抽奖活动列表（已完成、进行中）
     *
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDrawList($type)
    {
        $data = $this->mPWangzheDrawRepository->model()
            ::where('type', $type)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 我的中奖记录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyDrawList()
    {
        $userId = Auth::id();
        $data = $this->mPWangzheDrawRepository->model()
            ::where('winner_id', $userId)
            ->where('type', 1)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 参与抽奖活动
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function joinDraw($id)
    {
        $userId = Auth::id();
        $draw = $this->mPWangzheDrawRepository->find($id);
        if ($draw && $draw->type == self::TYPE_ON) {

            // 是否参与过
            $item = $this->mPWangzheDrawUserRepository->model()
                ::where([
                    'user_id' => $userId,
                    'draw_id' => $id,
                ])
                ->first();

            if ($item) {
                return response()->json(
                    ['message' => __('app.has_already_join')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $res = $this->mPWangzheDrawUserRepository->create([
                'user_id' => $userId,
                'draw_id' => $draw->id,
            ]);
            $draw->join_num += 1;
            $res && $draw->save();

            // 人满自动开奖 更改活动结束状态 不加事务,可以多于limit_user数量
            $this->handleDrawEnd($draw);

            return response()->json(
                null,
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.draw_end')],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * 查看参与了该活动的用户id列表
     *
     * @param $id
     * @return mixed
     */
    public function getDrawUserList($id)
    {
        $data = $this->mPWangzheDrawUserRepository->model()
            ::where('draw_id', $id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

    /**
     * 人满自动开奖 并更改活动结束状态
     *
     * @param $draw
     */
    private function handleDrawEnd($draw)
    {
        if ($draw->join_num >= $draw->limit_user) {
            $randItem = $this->mPWangzheDrawUserRepository->model()
                ::where('draw_id', $draw->id)
                ->orderBy(DB::raw('RAND()'))
                ->take(1)
                ->pluck('user_id');

            $winnerId = $randItem[0];

            if ($draw->winner_id == 0) {
                $draw->winner_id = $winnerId;
            }

            $draw->type = self::TYPE_OFF;
            $draw->save();
        }
    }

    /**
     * 赠送碎片
     *
     * @param $userId
     * @param $skinNum
     * @return \Illuminate\Http\JsonResponse
     */
    public function giveSkin($userId, $skinNum)
    {
        // 当前用户先减skinNum，成功后再加给对方90%（有这个人的话）
        // 不开事务，记碎片日志，没成功的话根据日志找回
        $myId = Auth::id();
        $mySkin = $this->mPWangzheSkinRepository->findBy('user_id', $myId);

        if ($myId == $userId) {
            return response()->json(
                ['message' => __('app.can_not_give_self')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // 不够
        if ($skinNum > $mySkin->skin_patch) {
            return response()->json(
                ['message' => __('app.skin_not_enough')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // 扣减自己的，并写日志
        $flag = false;
        $mySkin->skin_patch -= $skinNum;
        $mySkin->save() && $this->writeSkinLog($myId, $skinNum, self::TYPE['give']) && $flag = true;

        // 加给对方，并写日志
        $otherSkin = $this->mPWangzheSkinRepository->findBy('user_id', $userId);
        if ($flag && $otherSkin) {
            $tempSkin = $skinNum * 0.9;
            $otherSkin->skin_patch += $tempSkin;
            $otherSkin->save() && $this->writeSkinLog($userId, $tempSkin, self::TYPE['get']);
        }

        return response()->json(
            ['data' => $mySkin->skin_patch],
            Response::HTTP_OK
        );
    }

    /**
     * 兑换碎片申请动作
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert()
    {
        // 500碎片 = 50点券 = ￥5
        $base = 500;
        $myId = Auth::id();
        $mySkin = $this->mPWangzheSkinRepository->findBy('user_id', $myId);

        // 基数不够
        if ($mySkin->skin_patch < $base) {
            return response()->json(
                ['message' => __('app.base_skin_not_enough')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // 写入兑换记录
        // 先减用户碎片 防止对方提交申请后赠送给别人
        $uuid = Auth::user()->uuid;
        $convertSkin = floor($mySkin->skin_patch / 100) * 100;

        // 扣减成功后 写入兑换记录表
        // 人工兑换成功后更改状态、写入日志
        DB::beginTransaction();
        try {
            $mySkin->skin_patch -= $convertSkin;
            $mySkin->save();

            $this->mPWangzheSkinConvertRepository->create([
                'user_id' => $myId,
                'user_uuid' => $uuid,
                'convert_num' => $convertSkin,
            ]);

            DB::commit();

            return response()->json(
                ['data' => $mySkin->skin_patch],
                Response::HTTP_OK
            );

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 碎片兑换历史列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function convertList()
    {
        $userId = Auth::id();
        $data = $this->mPWangzheSkinConvertRepository->model()
            ::select(['id', 'user_id', 'convert_num', 'status', 'created_at'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(
            ['data' => $data],
            Response::HTTP_OK
        );
    }

}
