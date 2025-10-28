<?php

use App\Models\Product;

/**
 * Seeder: Datos de ejemplo para products
 *
 * FILOSOFÍA LEGO:
 * Datos iniciales para probar el CRUD.
 */
return new class {

    public function up()
    {
        $products = [
            [
                'name' => 'Laptop HP Pavilion',
                'description' => 'Laptop de alto rendimiento con procesador Intel Core i7, 16GB RAM, 512GB SSD',
                'price' => 899.99,
                'stock' => 15,
                'category' => 'Electrónica',
                'is_active' => true
            ],
            [
                'name' => 'Mouse Logitech MX Master 3',
                'description' => 'Mouse ergonómico inalámbrico con sensor de alta precisión',
                'price' => 99.99,
                'stock' => 50,
                'category' => 'Accesorios',
                'is_active' => true
            ],
            [
                'name' => 'Teclado Mecánico RGB',
                'description' => 'Teclado mecánico gaming con switches Cherry MX e iluminación RGB',
                'price' => 129.99,
                'stock' => 30,
                'category' => 'Accesorios',
                'is_active' => true
            ],
            [
                'name' => 'Monitor Samsung 27" 4K',
                'description' => 'Monitor LED 27 pulgadas, resolución 4K UHD, HDR10',
                'price' => 349.99,
                'stock' => 8,
                'category' => 'Electrónica',
                'is_active' => true
            ],
            [
                'name' => 'Webcam Logitech C920',
                'description' => 'Cámara web Full HD 1080p con micrófono estéreo',
                'price' => 79.99,
                'stock' => 25,
                'category' => 'Accesorios',
                'is_active' => true
            ],
            [
                'name' => 'Auriculares Sony WH-1000XM4',
                'description' => 'Auriculares inalámbricos con cancelación de ruido',
                'price' => 279.99,
                'stock' => 12,
                'category' => 'Audio',
                'is_active' => true
            ],
            [
                'name' => 'SSD Samsung 1TB',
                'description' => 'Unidad de estado sólido NVMe M.2, velocidades de hasta 7000MB/s',
                'price' => 149.99,
                'stock' => 40,
                'category' => 'Almacenamiento',
                'is_active' => true
            ],
            [
                'name' => 'Impresora HP LaserJet',
                'description' => 'Impresora láser multifunción con WiFi',
                'price' => 199.99,
                'stock' => 5,
                'category' => 'Oficina',
                'is_active' => true
            ],
            [
                'name' => 'Router TP-Link WiFi 6',
                'description' => 'Router inalámbrico de doble banda, WiFi 6, hasta 3000 Mbps',
                'price' => 89.99,
                'stock' => 20,
                'category' => 'Redes',
                'is_active' => true
            ],
            [
                'name' => 'Cable HDMI 2.1',
                'description' => 'Cable HDMI 2.1 de 2 metros, soporte 4K@120Hz',
                'price' => 19.99,
                'stock' => 100,
                'category' => 'Accesorios',
                'is_active' => true
            ],
            [
                'name' => 'Silla Gamer Ergonómica',
                'description' => 'Silla gaming con soporte lumbar, reposabrazos ajustables',
                'price' => 259.99,
                'stock' => 0,
                'category' => 'Mobiliario',
                'is_active' => false
            ],
            [
                'name' => 'Micrófono Blue Yeti',
                'description' => 'Micrófono USB profesional para streaming y podcasts',
                'price' => 129.99,
                'stock' => 18,
                'category' => 'Audio',
                'is_active' => true
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        echo "✓ Seed de " . count($products) . " productos creado exitosamente\n";
    }

    public function down()
    {
        Product::truncate();
        echo "✓ Datos de productos eliminados\n";
    }
};
