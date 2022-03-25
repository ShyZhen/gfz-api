<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWangzheHeroTutorialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wangzhe_hero_tutorial', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('hero_id')->index();
            $table->string('ming', 255);      // 铭文推荐，json格式 {id:xx,name:xx,intro:xx}
            $table->string('ming_tips', 255); // 铭文小技巧
            $table->string('equipment', 765); // 出装推荐，json格式 {id:xx,name:xx,intro:xx}
            $table->string('equipment_tips', 255);  // 出装小技巧
            $table->string('counter_hero', 255);  // 压制英雄，json格式 {id:xx,name:xx,intro:xx}
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
        Schema::dropIfExists('wangzhe_hero_tutorial');
    }
}
