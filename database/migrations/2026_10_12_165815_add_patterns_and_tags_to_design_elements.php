<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Erweitert den bestehenden Katalog "design_elements" um Muster-Bilder,
 * Studio-Zugehörigkeit und Tags.
 *
 * Bestehende Einträge bleiben unverändert: Alle neuen Spalten sind optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('design_elements', function (Blueprint $table) {

            // Wem gehört das Muster? NULL = Standard-Muster für alle Studios
            $table->foreignId('nail_studio_id')
                ->nullable()
                ->after('id')
                ->constrained('nail_studios')
                ->cascadeOnDelete();

            // Muster-Bild, das per Maske auf den Nagel gelegt wird.
            // NULL = Element ohne Bild (z. B. die bisherigen Einträge)
            $table->string('image_path')
                ->nullable()
                ->after('price_per_nail');

            // Studios können Muster ausblenden, ohne sie zu löschen
            $table->boolean('is_active')
                ->default(true)
                ->after('image_path');
        });

        // Tags (Suchbegriffe)
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Verknüpfung: ein Element hat viele Tags, ein Tag gehört zu vielen Elementen
        Schema::create('design_element_tag', function (Blueprint $table) {
            $table->foreignId('design_element_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['design_element_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_element_tag');
        Schema::dropIfExists('tags');

        Schema::table('design_elements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nail_studio_id');
            $table->dropColumn(['image_path', 'is_active']);
        });
    }
};