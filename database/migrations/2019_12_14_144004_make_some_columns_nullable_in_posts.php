<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSomeColumnsNullableInPosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('departure')->nullable()->change();
            $table->string('destination')->nullable()->change();
            $table->integer('seat')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('departure')->nullable(false)->change();
            $table->string('destination')->nullable(false)->change();
            $table->integer('seat')->nullable(false)->change();
        });
    }
}
