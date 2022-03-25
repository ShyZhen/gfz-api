<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: shyZhen
 * Date: 2021/7/22
 * Time: 17:27
 */

namespace App\Repositories\Eloquent;

class MPWangzheHeroTutorialRepository extends Repository
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
        return 'App\Models\MPWangzheHeroTutorial';
    }

}
