<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traffic_app_securitychecklog', function (Blueprint $table) {
            $table->id();
            $table->timestamp('check_time')->nullable();
            $table->integer('time_scope')->nullable();
            $table->string('address', 45)->nullable();
            $table->integer('number_of_requests')->nullable();
            $table->string('country')->nullable();
            $table->string('user_status')->nullable();
            $table->unique(['address','check_time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traffic_app_securitychecklog');
    }
};
