<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id')->unique(); // Relación con libro
            $table->integer('quantity')->default(0); // Stock total
            $table->integer('reserved_quantity')->default(0); // Unidades reservadas
            $table->integer('available_quantity')->virtualAs('quantity - reserved_quantity'); // Calculado
            $table->integer('version')->default(0); // Para optimistic locking
            $table->timestamps();
            
            // Índices
            $table->index('book_id');
            $table->index('available_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}