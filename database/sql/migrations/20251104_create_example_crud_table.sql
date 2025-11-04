-- Crear tabla example_crud (LEGO Framework - Ejemplo de CRUD)
-- Esta tabla sirve como template/referencia para construir otros CRUDs

CREATE TABLE IF NOT EXISTS example_crud (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100),
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    stock INTEGER NOT NULL DEFAULT 0,
    min_stock INTEGER NOT NULL DEFAULT 5,
    category VARCHAR(100),
    image_url VARCHAR(500),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_example_crud_sku ON example_crud(sku);
CREATE INDEX idx_example_crud_category ON example_crud(category);
CREATE INDEX idx_example_crud_is_active ON example_crud(is_active);
CREATE INDEX idx_example_crud_created_at ON example_crud(created_at DESC);

-- Trigger para actualizar updated_at automáticamente
CREATE TRIGGER update_example_crud_updated_at BEFORE UPDATE ON example_crud
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
