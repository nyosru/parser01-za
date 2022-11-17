<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGood extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->id();

            $table->integer('cat-id')
                ->unsigned();

            $table->string('name')
                // ->unsigned()
            ;
            $table->string('uri')
                // ->unsigned()
            ;
            $table->string('img')
                ->nullable()
                // ->unsigned()
            ;
            $table->string('discount')
                ->nullable()
                // ->unsigned()
            ;
            $table->text('opis')
                ->nullable()
                // ->unsigned()
            ;
            $table->integer('price')
                ->nullable()
                ->unsigned();

            $table->integer('price-old')
                ->nullable()
                ->unsigned();

            $table->string('brand')
                ->nullable()
                // ->unsigned()
            ;

            $table->string('articul')
                ->nullable()
                // ->unsigned()
            ;

            $table->string('kod')
                ->nullable()
                // ->unsigned()
            ;

            $table->set('load-type', ['new', 'loaded', 'full'])
                ->default('new')
                ->comment('статус загрузки первая и с полной страницы');

            // `cat_id` INTEGER NOT NULL REFERENCES `cats` (`id`) ON DELETE CASCADE,
            // `name` varchar(200),
            // `link` varchar(200),
            // `discount` varchar(200),
            // `img` varchar(200),
            // `opis` text,
            // `price` varchar(200),
            // `price_old` varchar(200), 
            // `articul` varchar(50),
            // `kod` varchar(50), 
            // `status` varchar(10), 

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('good');
        Schema::dropIfExists('goods');
    }
}
