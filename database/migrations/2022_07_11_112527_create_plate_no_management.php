<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlateNoManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plate_no_management', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('empno');
            $table->string('empname');
            $table->string('plate_no');
            $table->unsignedTinyInteger('logdel')->default(0)->comment = '0-show,1-hide';
            // $table->string('walk_in');
             $table->string('walk_in')->nullable(); //migrate NULL default value
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
        Schema::dropIfExists('plate_no_management');
    }
}
