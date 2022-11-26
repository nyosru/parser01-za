<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPoleStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->enum('load-type', ['new', 'loaded', 'full', 'error'])
                ->default('new')
                ->comment('статус загрузки первая и с полной страницы')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods', function (Blueprint $table) {
            $table->enum('load-type', ['new', 'loaded', 'full'])
                ->default('new')
                ->comment('статус загрузки первая и с полной страницы')
                ->change();
        });
    }
}
