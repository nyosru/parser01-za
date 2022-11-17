<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('cat', function (Blueprint $table) {
        //     $table->id();

        //     $table->string('name');
        //     $table->string('uri');
        //     $table->integer('cat_up_id');
        //     // $table->foreign('cat_up_id')->references('id')->on('cat');

        //     $table->integer('pages')->unsigned();

        //     // $sql = 'CREATE TABLE IF NOT EXISTS `cats` (
        //     //     `id` INTEGER PRIMARY KEY ,
        //     //     `name` varchar(200) NOT NULL,
        //     //     `uri` varchar(200) NOT NULL
        //     //     ,
        //     //     `cat_up_id` INTEGER NULL REFERENCES `cats`( `id` ) ON DELETE CASCADE
        //     //     ,
        //     //     `pages` INTEGER NULL
        //     //     )
        //     //     ';
        //     // $db->exec($sql);

        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat');
    }
}
