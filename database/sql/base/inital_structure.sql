CREATE TABLE admin_users (
    id SERIAL PRIMARY KEY,
    name character varying,
    email VARCHAR(255),
    status VARCHAR(255),
    role_id VARCHAR(255)
);