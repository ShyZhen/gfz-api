<?php

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * 为防止安全问题，不再自动生成管理员，admin_users表已经废弃
     * 后台使用laravel-admin
     *
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        AdminUser::truncate();
        AdminUser::create([
            'username' => env('APP_NAME', 'fmock'),
            'password' => bcrypt(time()),
            'name' => 'Administrator',
        ]);
    }
}
