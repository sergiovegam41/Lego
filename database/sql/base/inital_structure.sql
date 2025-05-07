CREATE TABLE auth_users (
    id SERIAL PRIMARY KEY,
    name character varying,
    email VARCHAR(255),
    password VARCHAR(255),
    status VARCHAR(255),
    auth_group_id VARCHAR(255),
    role_id VARCHAR(255)
);

CREATE TABLE auth_user_sessions (
    id SERIAL PRIMARY KEY,
    auth_user_id INT NOT NULL,
    device_id VARCHAR(255) NOT NULL,
    refresh_token TEXT NOT NULL,
    access_token TEXT NOT NULL,
    firebase_token TEXT, -- Para notificaciones push
    expires_at TIMESTAMP NOT NULL, -- Expiración del access_token
    refresh_expires_at TIMESTAMP NOT NULL, -- Expiración del refresh_token
    is_active BOOLEAN DEFAULT TRUE, -- Para invalidar sesiones manualmente si es necesario
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE (auth_user_id, device_id)
);

INSERT INTO auth_users (name, email,password, status, auth_group_id, role_id) 
VALUES 
('admin', 'admin@lego.com', '$2a$12$4QQvg9HzVZN5eZRY8kpMyuAVFqO7B5gMkGPqNFLcsKtPPA5KjXwRq','active', 'ADMINS', 'SUPERADMIN')
RETURNING *;


