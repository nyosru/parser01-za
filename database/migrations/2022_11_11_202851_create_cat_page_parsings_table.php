<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatPageParsingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cat_page_parsings', function (Blueprint $table) {
            $table->id();
            $table->string('cat_uri');
            $table->integer('page')
                ->unsigned();
            $table->set('status', [
                'new',
                'loaded', 
                'parsing_ok',
                'ok'
            ])
                ->default('new')
                ->comment('статус парсинга страницы');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cat_page_parsings');
    }
}
