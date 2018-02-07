<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id');
            $table->string("slug");
            $table->string("title");
            $table->string("short_title")->nullable();
            $table->text("body_text")->nullable();

            $table->unsignedSmallInteger('pagegroup_order')->default(100);
            $table->string('pagegroup_id')->nullable();
            $table->foreign('pagegroup_id')
                ->references('id')
                ->on('pagegroups')
                ->onDelete('cascade');

            $table->primary('id');
            $table->timestamps();
        });
    }
}
