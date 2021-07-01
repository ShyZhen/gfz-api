<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class MPVideoItemRepository extends Repository
{
    /**
     * 实现抽象函数获取模型
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return string
     */
    public function model()
    {
        return 'App\Models\MPVideoItem';
    }

    /**
     * 获取前$limit或者后$limit
     *
     * @param $type
     * @param $id
     * @param $limit
     * @return mixed
     */
    public function get10Items($type, $id, $limit)
    {
        // 前
        if ($type == 1) {
            $res = $this->model
                ->select('*')
                ->where('id', '>', $id)
                ->orderBy('id')
                ->limit($limit)
                ->get();
        } else {
            // 后 (后面是旧的)
            $res = $this->model
                ->select('*')
                ->where('id', '<', $id)
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        }
        return $res;
    }

}
