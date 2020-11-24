<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateSynchronizerFieldsTable
 */
class CreateSynchronizerFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('synchronizer_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field');
            $table->string('model');
            $table->boolean('update_empty')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('synchronizer_fields');
    }
}
