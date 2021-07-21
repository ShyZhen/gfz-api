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
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\MPWangzheSkinRepository;
use App\Repositories\Eloquent\MPWangzheSkinLogRepository;

class MPWangzheService extends Service
{
    // 获取皮肤碎片的数量
    const SKIN = [
        'register' => 20,
        'login' => 2,
        'share' => 5,
        'ad' => 5,
    ];

    // 操作类型 1注册 2每日登录 3每日分享3个新用户 4每日看5个广告 9兑换
    const TYPE = [
        'register' => 1,
        'login' => 2,
        'share' => 3,
        'ad' => 4,
        'used' => 9,
    ];

    // 对应操作的最大有效次数
    const LIMIT = [
        '1' => 1,  // register
        '2' => 1,  // login
        '3' => 3,  // share
        '4' => 5,  // ad
    ];

    private $redisService;
    private $mPWangzheSkinRepository;
    private $mPWangzheSkinLogRepository;

    /**
     * @param RedisService $redisService
     * @param MPWangzheSkinRepository $mPWangzheSkinRepository
     * @param MPWangzheSkinLogRepository $mPWangzheSkinLogRepository
     */
    public function __construct(
        RedisService $redisService,
        MPWangzheSkinRepository $mPWangzheSkinRepository,
        MPWangzheSkinLogRepository $mPWangzheSkinLogRepository
    ) {
        $this->redisService = $redisService;
        $this->mPWangzheSkinRepository = $mPWangzheSkinRepository;
        $this->mPWangzheSkinLogRepository = $mPWangzheSkinLogRepository;
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
     * @param $type [register,login,share,ad ...]
     * @return mixed
     */
    public function updateOtherSetSkin($type)
    {
        $userId = Auth::id();
        $skin = $this->mPWangzheSkinRepository->findBy('user_id', $userId);

        $this->updateSkin($skin, self::SKIN[$type], self::TYPE[$type]);

        return response()->json(
            null,
            Response::HTTP_NO_CONTENT
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
     * 其他更新碎片的动作
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

            // 仅能一次的 直接退出
            if (in_array($type, [self::TYPE['login']])) {
                return true;
            }

            // 多次的 计算出对应次数进行判断是否超过
            if (in_array($type, [self::TYPE['share'], self::TYPE['ad']])) {
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
            }

            return false;
        } else {
            $this->redisService->setRedis('user:' . $skin->user_id . ':type:' . $type, 1, 'EX', $expireTime);

            return false;
        }
    }
}
