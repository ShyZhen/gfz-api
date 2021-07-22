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
            $table->unsignedInteger('limit_user')->default(1000);  // 多少人参与后开奖
            $table->string('title', 128)->default('');
            $table->string('image', 128)->default('');
            $table->unsignedInteger('winner_id')->default(0);        // 中奖用户ID（只有一个奖）
            $table->tinyInteger('type')->default(0);     // 0进行中 1已完成
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
