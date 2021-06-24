<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpVideoItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_video_items', function (Blueprint $table) {
            $table->id();
            $table->string('vid', 128)->default('');
            $table->string('title', 128)->default('');
            $table->string('image', 128)->default('');
            $table->string('desc', 128)->default('');
            $table->tinyInteger('type')->default(0);    // 视频类型
            $table->tinyInteger('vip_type')->default(0);
            $table->timestamps();
        });

//        type
//        1 => '搞笑',
//        2 => '小品',
//        3 => '电影',
//        4 => '美女',
//        5 => '新闻',
//        6 => '科技',
//        7 => '悬疑',
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mp_video_items');
    }
}
