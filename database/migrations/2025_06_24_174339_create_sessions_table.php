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
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('last_activity')->index();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->integer('user_id')->nullable()->index();
            $table->text('payload')->nullable();
        });
    }
//CREATE TABLE sessions (
//id VARCHAR(40) NOT NULL,
//last_activity INT(10) NOT NULL,
//data TEXT NOT NULL,
//PRIMARY KEY (id)
//);
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
