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
        Schema::create('traffic_app_baduser', function (Blueprint $table) {
            $table->string('ip_address', 45)->primary();
            $table->timestamp('banned_at')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('status');
            $table->timestamp('updated_at')->nullable();
            $table->text('cloud_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traffic_app_baduser');
    }
};
