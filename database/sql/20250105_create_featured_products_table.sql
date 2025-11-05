-- Create Featured Products Table
CREATE TABLE IF NOT EXISTS featured_products (
    id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL,
    tag VARCHAR(100),
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT featured_products_product_id_foreign
        FOREIGN KEY (product_id)
        REFERENCES flowers(id)
        ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS featured_products_product_id_idx ON featured_products (product_id);
CREATE INDEX IF NOT EXISTS featured_products_tag_idx ON featured_products (tag);
CREATE INDEX IF NOT EXISTS featured_products_is_active_sort_order_idx ON featured_products (is_active, sort_order);
