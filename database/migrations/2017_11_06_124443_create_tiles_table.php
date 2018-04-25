<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('surtitle')->nullable();
            $table->text('body_text')->nullable();

            $table->string("linkable_id")->nullable();
            $table->string("linkable_type")->nullable();
            $table->index(["linkable_id", "linkable_type"]);

            $table->string('free_link_url')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('unpublished_at')->nullable();
            $table->string('visibility')->default('OFFLINE');

            $table->unsignedSmallInteger('order')->default(100);

            $table->unsignedInteger('tileblock_id');
            $table->foreign('tileblock_id')
                ->references('id')
                ->on('tileblocks')
                ->onDelete('cascade');

            $table->unsignedInteger('content_url_id')->nullable();
            $table->foreign('content_url_id')
                ->references('id')
                ->on('content_urls')
                ->onDelete('set null');

            $table->timestamps();
        });
    }
}
