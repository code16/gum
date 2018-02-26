<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTileblocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tileblocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('layout');
            $table->string('layout_variant')->nullable();
            $table->string("style_key")->nullable();

            $table->unsignedSmallInteger('order')->default(100);
            $table->string('section_id')->nullable();
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }
}
