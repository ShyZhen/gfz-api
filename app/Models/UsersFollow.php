<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersFollow extends Model
{
    // 关注、粉丝关系表
    protected $table = 'users_follow';

    protected $fillable = [
        'master_user_id', 'following_user_id', 'both_status',
    ];

    /**
     * 文章预加载用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userFollow()
    {
        return $this->belongsTo('App\Models\User', 'master_user_id', 'id')
            ->select(['id', 'uuid', 'name', 'avatar', 'bio']);
    }

    /**
     * 文章预加载用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userFans()
    {
        return $this->belongsTo('App\Models\User', 'following_user_id', 'id')
            ->select(['id', 'uuid', 'name', 'avatar', 'bio']);
    }
}
