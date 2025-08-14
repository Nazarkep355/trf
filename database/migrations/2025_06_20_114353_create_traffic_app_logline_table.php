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
        Schema::create('traffic_app_logline', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->nullable()->index();
            $table->string('address')->nullable()->index();
            $table->text('url')->nullable()->index();
            $table->string('status_code')->nullable()->index();
            $table->string('response_size')->nullable()->index();
            $table->text('referer')->nullable()->index();
            $table->text('user_agent')->nullable()->index();
            $table->string('method')->nullable()->index();
            $table->string('protocol')->nullable()->index();
            $table->string('country')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->boolean('manual_parsed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traffic_app_logline');
    }
};
