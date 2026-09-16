<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * datenbank schema für die produkte im warenkorb
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string( coloumn: 'name', length: 255); //name des produkts, required field
            $table->decimal(column: 'price', total:6, place: 2); //preis des produkts, max. 6 ziffern, 2 nachkommastellen ,required field
            $table->string( coloumn: 'image', length: 1000)->nullable(); //bild des produkts, optional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
