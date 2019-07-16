<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMarketOddToCheckedOddsLiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checked_odds_live', function (Blueprint $table) {
            $table->string('market_odd');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checked_odds_live', function (Blueprint $table) {
            $table->dropColumn('market_odd');
        });
    }
}
