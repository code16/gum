<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('surtitle')->nullable();
            $table->text('heading_text')->nullable();
            $table->text('body_text')->nullable();
            $table->dateTime('published_at');
            $table->string('visibility')->default('OFFLINE');

            $table->timestamps();
        });
    }
}
