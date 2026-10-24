<?php

use Database\Seeders\DesignTemplateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vorgefertigte Designs (Vorlagen) für den Design-Editor.
 *
 * design_templates      → die Vorlagen
 * design_template_tag   → Verbindung Vorlage ↔ Tag (dieselben Tags wie bei den Mustern)
 *
 * nails = was auf welchem Nagel liegt, z. B.
 * {
 *   "alle": { "color": "Vanille", "design": "French Tip" },
 *   "ring": { "design": "Goldfolie" }
 * }
 *
 * Die Vorlagen kommen aus database/data/design_templates.json und
 * werden direkt mit eingespielt (auf dem Server laufen keine Seeder).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('design_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('nails');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('design_template_tag', function (Blueprint $table) {
            $table->foreignId('design_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            $table->primary(['design_template_id', 'tag_id']);
        });

        // Vorlagen + Tags direkt einspielen
        app(DesignTemplateSeeder::class)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('design_template_tag');
        Schema::dropIfExists('design_templates');
    }
};