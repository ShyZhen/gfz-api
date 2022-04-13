<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTianyitianRedpackage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tianyitian_redpackage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 32)->default('');
            $table->string('url', 160)->default('');
            $table->string('key', 160)->default('');
            $table->tinyInteger('is_deleted')->default(0); // 0正常，1删除
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
        Schema::dropIfExists('tianyitian_redpackage');
    }
}
