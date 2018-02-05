<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagegroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagegroups', function (Blueprint $table) {
            $table->uuid('id');
            $table->string("slug");
            $table->string("title");
            $table->string("short_title")->nullable();

            $table->primary('id');
            $table->timestamps();
        });
    }
}
