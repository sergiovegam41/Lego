<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('flower_images', function ($table) {
            $table->id();
            $table->unsignedBigInteger('flower_id');
            $table->string('image_url', 500);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            // Foreign key
            $table->foreign('flower_id')
                  ->references('id')
                  ->on('flowers')
                  ->onDelete('cascade');

            // Indexes
            $table->index('flower_id');
            $table->index('sort_order');
            $table->index('is_primary');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('flower_images');
    }
};
