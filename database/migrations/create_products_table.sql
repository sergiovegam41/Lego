-- =====================================================================
-- Migration: create_products_table
-- Descripción: Crea tabla products para ProductsCrudV3
-- Fecha: 2025-01-XX
-- =====================================================================

-- Crear tabla products (si no existe)
CREATE TABLE IF NOT EXISTS products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NULL,
    description TEXT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NULL DEFAULT 0,
    category VARCHAR(100) NULL,
    image_url VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_name (name),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla product_images (si no existe)
CREATE TABLE IF NOT EXISTS product_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    url VARCHAR(500) NOT NULL,
    `key` VARCHAR(500) NOT NULL COMMENT 'MinIO key para storage',
    original_name VARCHAR(255) NULL,
    size BIGINT UNSIGNED NULL COMMENT 'Tamaño en bytes',
    mime_type VARCHAR(100) NULL,
    `order` INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id),
    INDEX idx_order (product_id, `order`),
    INDEX idx_is_primary (product_id, is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar productos de ejemplo (solo si la tabla está vacía)
INSERT INTO products (name, description, price, stock, category, is_active)
SELECT * FROM (SELECT
    'Laptop Dell XPS 15' as name,
    'Laptop de alto rendimiento con procesador Intel i7' as description,
    1299.99 as price,
    15 as stock,
    'electronics' as category,
    TRUE as is_active
) AS tmp
WHERE NOT EXISTS (
    SELECT id FROM products LIMIT 1
);

INSERT INTO products (name, description, price, stock, category, is_active)
SELECT * FROM (SELECT
    'Mouse Logitech MX Master 3' as name,
    'Mouse inalámbrico ergonómico para productividad' as description,
    99.99 as price,
    50 as stock,
    'electronics' as category,
    TRUE as is_active
) AS tmp
WHERE (SELECT COUNT(*) FROM products) = 1;

INSERT INTO products (name, description, price, stock, category, is_active)
SELECT * FROM (SELECT
    'Teclado Mecánico Keychron K2' as name,
    'Teclado mecánico inalámbrico compacto' as description,
    89.99 as price,
    30 as stock,
    'electronics' as category,
    TRUE as is_active
) AS tmp
WHERE (SELECT COUNT(*) FROM products) = 2;

INSERT INTO products (name, description, price, stock, category, is_active)
SELECT * FROM (SELECT
    'Monitor LG UltraWide 34"' as name,
    'Monitor ultrawide 21:9 WQHD IPS' as description,
    449.99 as price,
    12 as stock,
    'electronics' as category,
    TRUE as is_active
) AS tmp
WHERE (SELECT COUNT(*) FROM products) = 3;

INSERT INTO products (name, description, price, stock, category, is_active)
SELECT * FROM (SELECT
    'Webcam Logitech C920' as name,
    'Webcam Full HD 1080p para streaming' as description,
    79.99 as price,
    25 as stock,
    'electronics' as category,
    TRUE as is_active
) AS tmp
WHERE (SELECT COUNT(*) FROM products) = 4;

-- Verificar inserción
SELECT 'Productos insertados:' as info, COUNT(*) as total FROM products;
