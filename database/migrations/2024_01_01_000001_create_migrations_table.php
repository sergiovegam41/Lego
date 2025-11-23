<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migration: Create migrations table
 * 
 * This is the foundational migration that creates the migrations tracking table.
 * This table keeps track of which migrations have been executed.
 * 
 * IMPORTANT: This migration is self-bootstrapping and will be executed first.
 */
return new class {
    
    public function up()
    {
        // Only create if it doesn't exist
        if (!Capsule::schema()->hasTable('migrations')) {
            Capsule::schema()->create('migrations', function ($table) {
                $table->id();
                $table->string('migration', 255);
                $table->integer('batch')->default(1);
                $table->timestamps();
            });
            
            echo "✓ Tabla 'migrations' creada exitosamente\n";
        } else {
            echo "⏭️  Tabla 'migrations' ya existe\n";
        }
    }
    
    public function down()
    {
        Capsule::schema()->dropIfExists('migrations');
        echo "✓ Tabla 'migrations' eliminada\n";
    }
};
