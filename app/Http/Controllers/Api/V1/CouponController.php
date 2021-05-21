<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\BaseService\RedisService;

class CouponController extends Controller
{
    private $redisService;

    /**
     * @param RedisService $redisService
     */
    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;
    }

    public function getCoupons(): \Illuminate\Http\JsonResponse
    {
        $coupons = [
            [
                'id' => 1,
                'name' => '饿了么每日专享红包',
                'icon' => '/static/coupon/elm_icon.png',
                'bannerPic' => '/static/coupon/elmZX.png',
                'url' => 'https://s.click.ele.me/quDa1ru',
                'minapp' => [
                    'appid' => 'wxece3a9a4c82f58c9',
                    'path' => 'taoke/pages/shopping-guide/index?scene=kvnz0ru',
                ],
                'originImage' => '/static/detail/mp-zx.jpg'
            ],
            [
                'id' => 2,
                'name' => '饿了么吃货联盟红包',
                'icon' => '/static/coupon/elm_icon.png',
                'bannerPic' => '/static/coupon/elmCHLM.png',
                'url' => 'https://s.click.ele.me/GyR1jqu',
                'minapp' => [
                    'appid' => 'wxece3a9a4c82f58c9',
                    'path' => 'pages/sharePid/web/index?scene=s.click.ele.me%2FGyR1jqu',
                ],
                'originImage' => '/static/detail/mp-ch.jpg'
            ],
            [
                'id' => 3,
                'name' => '饿了么果蔬商超红包',
                'icon' => '/static/coupon/elm_icon.png',
                'bannerPic' => '/static/coupon/elmCS2.png',
                'url' => 'https://s.click.ele.me/wUZhiqu',
                'minapp' => [
                    'appid' => 'wxece3a9a4c82f58c9',
                    'path' => 'pages/sharePid/web/index?scene=s.click.ele.me%2FwUZhiqu',
                ],
                'originImage' => '/static/detail/mp-cs.jpg'
            ],
            [
                'id' => 4,
                'name' => '美团外卖每日红包',
                'icon' => '/static/coupon/mt_icon.png',
                'bannerPic' => '/static/coupon/mtHB.png',
                'url' => 'https://c.mktdatatech.com/track.php?site_id=450533&aid=10124&euid=&t=https%3A%2F%2Fi.meituan.com&dm_fid=16079',
                'minapp' => [
                    'appid' => 'wxde8ac0a21135c07d',
                    'path' => '/waimai/pages/h5/h5?f_token=1&weburl=https%3A%2F%2Fdpurl.cn%2FvoNQNGKz',
                ],
                'originImage' => '/static/detail/mp-mt.jpg'
            ],
        ];

        return response()->json(
            ['data' => $coupons],
            Response::HTTP_OK
        );
    }


    /**
     * 数据库返回红包数据
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCouponsNew(): \Illuminate\Http\JsonResponse
    {
        $res = [];
        $coupons = Coupon::all();
        foreach ($coupons as $coupon) {
            $temp['id'] = $coupon['id'];
            $temp['name'] = $coupon['name'];
            $temp['icon'] = $coupon['icon'];
            $temp['bannerPic'] = $coupon['banner_pic'];
            $temp['url'] = $coupon['url'];
            $temp['minapp']['appid'] = $coupon['app_id'];
            $temp['minapp']['path'] = $coupon['path'];
            $temp['originImage'] = $coupon['origin_image'];
            $res[] = $temp;
        }

        return response()->json(
            ['data' => $res],
            Response::HTTP_OK
        );
    }
}
