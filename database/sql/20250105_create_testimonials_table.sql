-- Create Testimonials Table
CREATE TABLE IF NOT EXISTS testimonials (
    id BIGSERIAL PRIMARY KEY,
    author VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS testimonials_is_active_idx ON testimonials (is_active);
CREATE INDEX IF NOT EXISTS testimonials_created_at_idx ON testimonials (created_at);
