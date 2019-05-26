<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOdds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('odds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('odd_id');
            $table->string('home_od')->nullable();
            $table->string('away_od')->nullable();
            $table->string('odd_ss')->nullable();
            $table->string('time_str')->nullable();
            $table->string('add_time')->nullable();
            $table->string('handicap')->nullable();
            $table->string('odd_market');
            $table->unsignedInteger('event_id');
            $table->boolean('is_checked');
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
        Schema::dropIfExists('odds');
    }
}
