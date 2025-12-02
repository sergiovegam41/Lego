<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Migration: Seed tools table with sample data
 */
return new class {
    
    public function up()
    {
        // Sample tools
        $tools = [
            [
                'name' => 'Martillo de Carpintero',
                'description' => 'Martillo profesional con mango de madera ergonómico. Ideal para trabajos de carpintería y construcción.',
                'is_active' => true,
                'features' => [
                    'Cabeza de acero forjado',
                    'Mango de madera de roble',
                    'Peso: 500g',
                    'Garantía de por vida'
                ]
            ],
            [
                'name' => 'Destornillador Eléctrico',
                'description' => 'Destornillador inalámbrico con batería de litio recargable. Incluye set de 20 puntas.',
                'is_active' => true,
                'features' => [
                    'Batería Li-Ion 3.6V',
                    '20 puntas incluidas',
                    'LED de trabajo integrado',
                    'Velocidad ajustable',
                    'Estuche de transporte'
                ]
            ],
            [
                'name' => 'Sierra Circular',
                'description' => 'Sierra circular de 7-1/4" con motor de 1400W. Cortes precisos en madera y derivados.',
                'is_active' => true,
                'features' => [
                    'Motor 1400W',
                    'Disco de 185mm',
                    'Profundidad de corte: 65mm',
                    'Guía láser'
                ]
            ],
            [
                'name' => 'Taladro Percutor',
                'description' => 'Taladro percutor profesional de 800W. Ideal para concreto, ladrillo y madera.',
                'is_active' => true,
                'features' => [
                    'Motor 800W',
                    'Percusión variable',
                    'Mandril de 13mm',
                    'Velocidad: 0-2800 RPM',
                    'Incluye maletín'
                ]
            ],
            [
                'name' => 'Nivel Láser',
                'description' => 'Nivel láser autonivelante con líneas cruzadas. Precisión profesional.',
                'is_active' => false,
                'features' => [
                    'Autonivelante ±4°',
                    'Alcance: 15 metros',
                    'Líneas cruzadas',
                    'Trípode incluido'
                ]
            ]
        ];

        foreach ($tools as $toolData) {
            $features = $toolData['features'];
            unset($toolData['features']);
            
            // Insert tool
            $toolId = Capsule::table('tools')->insertGetId([
                'name' => $toolData['name'],
                'description' => $toolData['description'],
                'is_active' => $toolData['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Insert features
            foreach ($features as $order => $feature) {
                Capsule::table('tool_features')->insert([
                    'tool_id' => $toolId,
                    'feature' => $feature,
                    'display_order' => $order,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        echo "✓ Datos de ejemplo insertados en 'tools' y 'tool_features'\n";
    }
    
    public function down()
    {
        Capsule::table('tool_features')->truncate();
        Capsule::table('tools')->truncate();
        echo "✓ Datos de 'tools' y 'tool_features' eliminados\n";
    }
};

