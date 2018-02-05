<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSidepanelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sidepanels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('layout');
            $table->string('link')->nullable();
            $table->text('body_text')->nullable();
            $table->unsignedSmallInteger('order')->default(100);

            $table->string("container_id");
            $table->string("container_type");
            $table->index(["container_id", "container_type"]);

            $table->string("related_content_id")->nullable();
            $table->string("related_content_type")->nullable();
            $table->index(["related_content_id", "related_content_type"]);

            $table->timestamps();
        });
    }
}
