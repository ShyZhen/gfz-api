<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWikiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wiki', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 32)->default('');
            $table->string('formula', 32)->default('')->comment('化学式');
            $table->string('bio', 64)->default('')->comment('简介，放在最上边，一眼看得出是不是好东西');
            $table->text('content')->comment('详细信息');
            $table->string('extra', 64)->default('')->comment('其他备注');
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
        Schema::dropIfExists('wiki');
    }
}
