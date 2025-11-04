-- Crear tabla example_crud_images (LEGO Framework - Imágenes para Example CRUD)
-- Tabla de relación uno-a-muchos: Un registro puede tener múltiples imágenes

CREATE TABLE IF NOT EXISTS example_crud_images (
    id SERIAL PRIMARY KEY,
    example_crud_id INTEGER REFERENCES example_crud(id) ON DELETE CASCADE,
    url VARCHAR(500) NOT NULL,
    key VARCHAR(500) NOT NULL,
    original_name VARCHAR(255),
    size INTEGER,
    mime_type VARCHAR(100),
    display_order INTEGER NOT NULL DEFAULT 0,
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para mejorar rendimiento
CREATE INDEX idx_example_crud_images_example_crud_id ON example_crud_images(example_crud_id);
CREATE INDEX idx_example_crud_images_is_primary ON example_crud_images(is_primary);
CREATE INDEX idx_example_crud_images_display_order ON example_crud_images(display_order);

-- Trigger para actualizar updated_at automáticamente
CREATE TRIGGER update_example_crud_images_updated_at BEFORE UPDATE ON example_crud_images
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
