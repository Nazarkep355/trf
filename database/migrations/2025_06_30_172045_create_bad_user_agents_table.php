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
        Schema::create('bad_user_agents', function (Blueprint $table) {
            $table->id();
            $table->string('user_agent')->unique()->index();
            $table->timestamp('banned_at')->nullable()->index();
            $table->string('status')->default('unbanned')->index();
            $table->text('cloud_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bad_user_agents');
    }
};
