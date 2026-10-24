<?php

use Database\Seeders\PriceOptionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preise pro Studio.
 *
 * price_options  → Effekte, Verzierungen, Längen, Materialien mit Standardpreis
 * studio_prices  → abweichender Preis eines Studios für ein Muster oder eine Option
 *
 * Gibt es für ein Studio keinen Eintrag in studio_prices, gilt der Standardpreis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_options', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30);              // effekt | verzierung | laenge | material
            $table->string('key', 60);                   // z. B. "chrome", "long", "gel"
            $table->string('name');
            $table->decimal('default_price', 8, 2)->default(0);
            $table->string('unit', 20)->default('pauschal'); // nagel | pauschal
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'key']);
        });

        Schema::create('studio_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nail_studio_id')->constrained()->cascadeOnDelete();
            $table->morphs('priceable');                 // priceable_type + priceable_id
            $table->decimal('price', 8, 2);
            $table->timestamps();

            // Pro Studio genau ein Preis je Muster/Option
            $table->unique(['nail_studio_id', 'priceable_type', 'priceable_id'], 'studio_prices_unique');
        });

        // Standardpreise direkt einspielen (auf dem Server laufen keine Seeder)
        app(PriceOptionSeeder::class)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_prices');
        Schema::dropIfExists('price_options');
    }
};