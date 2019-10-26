<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ThemeVersions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('theme_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('theme_id')->index();
            $table->foreign('theme_id')->references('id')->on('themes')->onDelete('cascade');
            $table->string('version');
            $table->char('hash_sha256', 64)->unique();
            $table->char('hash_sha1', 40)->unique();
            $table->char('hash_md5', 32)->unique();
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
        Schema::dropIfExists('theme_versions');
    }
}
