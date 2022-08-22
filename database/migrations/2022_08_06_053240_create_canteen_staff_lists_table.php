<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCanteenStaffListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('canteen_staff_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('empno');
            $table->string('empname');
            $table->string('section');
            $table->unsignedTinyInteger('logdel')->default(0)->comment = '0-show,1-hide';
            // $table->string('walk_in')->nullable(); //migrate NULL default value
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
        Schema::dropIfExists('canteen_staff_lists');
    }
}
