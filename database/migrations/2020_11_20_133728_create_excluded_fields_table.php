<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcludedFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('synchronizer_excluded_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field');
            $table->string('model');
            $table->boolean('update_empty')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('synchronizer_excluded_fields');
    }
}
