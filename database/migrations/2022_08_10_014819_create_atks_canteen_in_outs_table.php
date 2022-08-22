<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAtksCanteenInOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('atks_canteen_in_outs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('empno');
            $table->string('empname');
            $table->string('department');
            $table->string('plate_no');
            $table->date('date');
            $table->string('time');
            $table->TinyInteger('status')->comment = '1-time_in,2-time_out';
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
        Schema::dropIfExists('atks_canteen_in_outs');
    }
}
