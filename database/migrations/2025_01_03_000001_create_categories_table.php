<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('categories', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('categories');
    }
};
