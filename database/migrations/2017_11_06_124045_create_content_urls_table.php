<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uri')->index();
            $table->string("domain")->nullable();

            $table->string("content_id")->nullable();
            $table->string("content_type")->nullable();
            $table->index(["content_id", "content_type"]);

            $table->dateTime('published_at')->nullable();
            $table->dateTime('unpublished_at')->nullable();
            $table->string('visibility')->default('OFFLINE');

            $table->unsignedInteger('parent_id')->nullable();
            $table->foreign('parent_id')
                ->references('id')
                ->on('content_urls')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }
}
