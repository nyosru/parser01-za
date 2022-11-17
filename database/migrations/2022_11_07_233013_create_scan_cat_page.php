<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScanCatPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('scan_cat_page', function (Blueprint $table) {
        //     $table->id();
        //     $table->integer('cat_uri');
        //     $table->integer('page')
        //         ->unsigned();
        //     $table->set('status', ['new', 'ok'])
        //         ->default('new')
        //         ->comment('статус парсинга страницы');
        //     // `cat_id` INTEGER NOT NULL REFERENCES `cats`( `id` ) ON DELETE CASCADE,
        //     // `page` INT,
        //     // `status` varchar(10) NOT NULL  
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
        Schema::dropIfExists('scan_cat_page');
    }
}
