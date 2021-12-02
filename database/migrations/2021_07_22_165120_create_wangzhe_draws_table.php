<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWangzheDrawsTable extends Migration
{
    /**
     * 抽奖活动表
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wangzhe_draws', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('platform_id')->default(0)->index();  // 多平台改造，添加平台ID
            $table->unsignedInteger('limit_user')->default(1000);  // 多少人参与后开奖
            $table->unsignedInteger('join_num')->default(0);  // 当前已有多少人参与
            $table->string('title', 128)->default('');
            $table->string('image', 128)->default('');
            $table->unsignedInteger('winner_id')->default(0);        // 中奖用户ID（只有一个奖）
            $table->tinyInteger('type')->default(0);     // 0进行中 1已结束
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
        Schema::dropIfExists('wangzhe_draws');
    }
}
