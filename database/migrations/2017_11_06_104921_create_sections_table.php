<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->string("title");
            $table->string("domain")->nullable();
            $table->string("short_title")->nullable();
            $table->string("slug");
            $table->boolean("is_root")->default(false);
            $table->unsignedSmallInteger("root_menu_order")->nullable();
            $table->text("heading_text")->nullable();
            $table->string("style_key")->nullable();
            $table->boolean("has_news")->default(false);

            $table->primary('id');
            $table->timestamps();
        });
    }
}
