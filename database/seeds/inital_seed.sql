INSERT INTO auth_users (name, email,password, status, auth_group_id, role_name) 
VALUES 
('Sergio', 'sergio@example.com', '12345678','active', 'group123', 'admin')
RETURNING *;
