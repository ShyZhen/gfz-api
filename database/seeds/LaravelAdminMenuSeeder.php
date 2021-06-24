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
            ['parent_id' => 0, 'order' => '4', 'title' => '用户', 'icon' => 'fas fa-user-tag', 'uri' => 'users'],
            ['parent_id' => 0, 'order' => '5', 'title' => '日记', 'icon' => 'fas fa-list-alt', 'uri' => 'timelines'],
            ['parent_id' => 0, 'order' => '6', 'title' => '举报', 'icon' => 'fas fa-angry', 'uri' => 'reports'],
            ['parent_id' => 0, 'order' => '7', 'title' => '反馈', 'icon' => 'fas fa-exclamation', 'uri' => 'report-apps'],
            ['parent_id' => 0, 'order' => '8', 'title' => '评论', 'icon' => 'fas fa-comment-alt', 'uri' => 'comments'],
            ['parent_id' => 0, 'order' => '9', 'title' => '外卖红包', 'icon' => 'fas fa-money-check-alt', 'uri' => 'coupons'],
            ['parent_id' => 0, 'order' => '10', 'title' => '腾讯视频', 'icon' => 'fas fa-video', 'uri' => 'm-p-video-items'],
        ];

        \Illuminate\Support\Facades\DB::table('laravel_admin_menu')->insert($seeder);
    }
}
