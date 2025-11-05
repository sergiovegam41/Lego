<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('testimonials', function ($table) {
            $table->id();
            $table->string('author', 255);
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('author');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('testimonials');
    }
};
