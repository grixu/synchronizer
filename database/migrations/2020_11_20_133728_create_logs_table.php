<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    public function up()
    {
        Schema::create('synchronizer_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('batch_id');
            $table->string('changed');
            $table->string('type');
            $table->json('log');
            $table->boolean('reported')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('synchronizer_logs');
    }
}
