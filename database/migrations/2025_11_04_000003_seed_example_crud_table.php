<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migration: Seed example_crud table
 * 
 * Insert sample data to demonstrate CRUD capabilities.
 * Converted from: 20251104_seed_example_crud_table.sql
 */
return new class {
    
    public function up()
    {
        $data = [
            ['name' => 'Ejemplo Item 1', 'description' => 'Este es un registro de ejemplo para demostrar el CRUD', 'price' => 99.99, 'stock' => 15, 'min_stock' => 5, 'category' => 'Categoría A', 'is_active' => true],
            ['name' => 'Ejemplo Item 2', 'description' => 'Segundo registro de ejemplo con diferentes valores', 'price' => 149.99, 'stock' => 30, 'min_stock' => 10, 'category' => 'Categoría B', 'is_active' => true],
            ['name' => 'Ejemplo Item 3', 'description' => 'Tercer registro con stock bajo para probar alertas', 'price' => 79.99, 'stock' => 3, 'min_stock' => 5, 'category' => 'Categoría A', 'is_active' => true],
            ['name' => 'Ejemplo Item 4', 'description' => 'Cuarto registro de ejemplo', 'price' => 199.99, 'stock' => 50, 'min_stock' => 15, 'category' => 'Categoría C', 'is_active' => true],
            ['name' => 'Ejemplo Item 5', 'description' => 'Quinto registro - inactivo para demostrar filtros', 'price' => 59.99, 'stock' => 0, 'min_stock' => 5, 'category' => 'Categoría B', 'is_active' => false],
            ['name' => 'Ejemplo Item 6', 'description' => 'Sexto registro con stock moderado', 'price' => 129.99, 'stock' => 20, 'min_stock' => 8, 'category' => 'Categoría A', 'is_active' => true],
            ['name' => 'Ejemplo Item 7', 'description' => 'Séptimo registro de ejemplo', 'price' => 89.99, 'stock' => 40, 'min_stock' => 12, 'category' => 'Categoría C', 'is_active' => true],
            ['name' => 'Ejemplo Item 8', 'description' => 'Octavo registro para demostrar paginación', 'price' => 169.99, 'stock' => 8, 'min_stock' => 5, 'category' => 'Categoría B', 'is_active' => true],
            ['name' => 'Ejemplo Item 9', 'description' => 'Noveno registro con precio alto', 'price' => 299.99, 'stock' => 12, 'min_stock' => 5, 'category' => 'Categoría A', 'is_active' => true],
            ['name' => 'Ejemplo Item 10', 'description' => 'Décimo registro para completar la primera página', 'price' => 119.99, 'stock' => 25, 'min_stock' => 10, 'category' => 'Categoría C', 'is_active' => true],
            ['name' => 'Ejemplo Item 11', 'description' => 'Undécimo registro - segunda página', 'price' => 159.99, 'stock' => 18, 'min_stock' => 8, 'category' => 'Categoría B', 'is_active' => true],
            ['name' => 'Ejemplo Item 12', 'description' => 'Duodécimo registro final', 'price' => 189.99, 'stock' => 6, 'min_stock' => 5, 'category' => 'Categoría A', 'is_active' => true],
        ];
        
        $timestamp = date('Y-m-d H:i:s');
        foreach ($data as $item) {
            $item['created_at'] = $timestamp;
            $item['updated_at'] = $timestamp;
            Capsule::table('example_crud')->insert($item);
        }
        
        echo "✓ Datos de ejemplo insertados en 'example_crud' (12 registros)\n";
    }
    
    public function down()
    {
        Capsule::table('example_crud')->truncate();
        echo "✓ Datos de ejemplo eliminados de 'example_crud'\n";
    }
};
