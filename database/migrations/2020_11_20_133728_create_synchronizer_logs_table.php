<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSynchronizerLogsTable
 */
class CreateSynchronizerLogsTable extends Migration
{
    public function up()
    {
        Schema::create('synchronizer_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->unsignedBigInteger('model_id');
            $table->json('log');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('synchronizer_logs');
    }
}
