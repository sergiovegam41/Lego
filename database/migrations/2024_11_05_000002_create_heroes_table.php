<?php
/**
 * Migration: Create Heroes Table
 *
 * Stores hero/banner sections for landing page
 */

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        if (Capsule::schema()->hasTable('heroes')) {
            echo "⚠️  Table 'heroes' already exists. Skipping.\n";
            return;
        }

        Capsule::schema()->create('heroes', function ($table) {
            $table->id();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->string('background_image', 500)->nullable();
            $table->string('cta_label', 100)->nullable();
            $table->string('cta_link', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        echo "✅ Table 'heroes' created successfully.\n";

        // Insert default hero
        Capsule::table('heroes')->insert([
            'title' => 'Welcome to Flora Fresh',
            'subtitle' => 'TIME TO BLOSSOM',
            'background_image' => 'https://cdn.example.com/landing/hero-flora.jpg',
            'cta_label' => 'Ver Colección',
            'cta_link' => '/tienda',
            'sort_order' => 0,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo "✅ Default hero inserted.\n";
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('heroes');
        echo "✅ Table 'heroes' dropped.\n";
    }
};
