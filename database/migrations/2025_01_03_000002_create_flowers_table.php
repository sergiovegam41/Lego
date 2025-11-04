<?php

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->create('flowers', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable(); // Rich text HTML
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            // Indexes
            $table->index('category_id');
            $table->index('is_active');
            $table->index('name');
            $table->index('price');
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('flowers');
    }
};
