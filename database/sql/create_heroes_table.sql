-- Create Heroes Table for Landing Page
-- Run this SQL directly in your PostgreSQL database if migrations don't work

CREATE TABLE IF NOT EXISTS heroes (
    id BIGSERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    background_image VARCHAR(500),
    cta_label VARCHAR(100),
    cta_link VARCHAR(500),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for performance
CREATE INDEX IF NOT EXISTS heroes_is_active_sort_order_idx
ON heroes (is_active, sort_order);

-- Insert default hero
INSERT INTO heroes (title, subtitle, background_image, cta_label, cta_link, sort_order, is_active, created_at, updated_at)
VALUES (
    'Welcome to Flora Fresh',
    'TIME TO BLOSSOM',
    'https://cdn.example.com/landing/hero-flora.jpg',
    'Ver Colección',
    '/tienda',
    0,
    TRUE,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);

-- Verify
SELECT * FROM heroes;
