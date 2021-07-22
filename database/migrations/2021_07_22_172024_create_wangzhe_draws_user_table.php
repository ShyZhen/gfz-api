<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWangzheDrawsUserTable extends Migration
{
    /**
     * 参与抽奖用户表
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wangzhe_draws_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');        // 参与的用户ID
            $table->unsignedInteger('draw_id');        // 抽奖活动ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wangzhe_draws_user');
    }
}
