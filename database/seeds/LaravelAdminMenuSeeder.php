<?php

use Illuminate\Database\Seeder;

class LaravelAdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $seeder = [
            ['id' => 4, 'parent_id' => 0, 'order' => '4', 'title' => '用户', 'icon' => 'fas fa-user-tag', 'uri' => 'users'],
            ['id' => 5, 'parent_id' => 0, 'order' => '5', 'title' => '日记', 'icon' => 'fas fa-list-alt', 'uri' => 'timelines'],
            ['id' => 6, 'parent_id' => 0, 'order' => '6', 'title' => '举报', 'icon' => 'fas fa-angry', 'uri' => 'reports'],
            ['id' => 7, 'parent_id' => 0, 'order' => '7', 'title' => '反馈', 'icon' => 'fas fa-exclamation', 'uri' => 'report-apps'],
            ['id' => 8, 'parent_id' => 0, 'order' => '8', 'title' => '评论', 'icon' => 'fas fa-comment-alt', 'uri' => 'comments'],
            ['id' => 9, 'parent_id' => 0, 'order' => '9', 'title' => '外卖红包', 'icon' => 'fas fa-money-check-alt', 'uri' => 'coupons'],
            ['id' => 10, 'parent_id' => 0, 'order' => '10', 'title' => '腾讯视频', 'icon' => 'fas fa-video', 'uri' => 'm-p-video-items'],
            ['id' => 11, 'parent_id' => 0, 'order' => '11', 'title' => '王者幸运星', 'icon' => 'fab fa-android', 'uri' => ''],
            ['id' => 12, 'parent_id' => 11, 'order' => '1', 'title' => '王者抽奖活动', 'icon' => 'fas fa-gift', 'uri' => 'm-p-wangzhe-draws'],
            ['id' => 13, 'parent_id' => 11, 'order' => '2', 'title' => '用户碎片列表', 'icon' => 'fab fa-microsoft', 'uri' => 'm-p-wangzhe-skins'],
            ['id' => 14, 'parent_id' => 11, 'order' => '3', 'title' => '碎片领取日志', 'icon' => 'fas fa-file-alt', 'uri' => 'm-p-wangzhe-skin-logs'],
            ['id' => 15, 'parent_id' => 11, 'order' => '4', 'title' => '碎片兑换日志', 'icon' => 'fas fa-chess-queen', 'uri' => 'm-p-wangzhe-skin-converts'],
            ['id' => 16, 'parent_id' => 11, 'order' => '5', 'title' => '平台配置', 'icon' => 'fas fa-cloud', 'uri' => 'm-p-wangzhe-platforms'],
        ];

        \Illuminate\Support\Facades\DB::table('laravel_admin_menu')->insert($seeder);
    }
}
