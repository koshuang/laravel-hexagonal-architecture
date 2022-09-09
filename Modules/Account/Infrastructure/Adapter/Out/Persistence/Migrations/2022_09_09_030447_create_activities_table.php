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
        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('owner_account_id');
            $table->unsignedBigInteger('source_account_id');
            $table->unsignedBigInteger('target_account_id');
            $table->integer('amount');
            $table->timestamps();

            $table->foreign('owner_account_id')->references('id')->on('accounts');
            $table->foreign('source_account_id')->references('id')->on('accounts');
            $table->foreign('target_account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
