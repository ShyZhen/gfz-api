<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWangzheSkinConvertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wangzhe_skin_convert', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->string('user_uuid', 64);
            $table->unsignedInteger('convert_num')->default(0);  // 本次兑换数量，先减用户碎片，加入到这里，防止对方提交申请后赠送给别人
            $table->enum('status', ['success', 'wait'])->default('wait');  // 本次状态
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
        Schema::dropIfExists('wangzhe_skin_convert');
    }
}
