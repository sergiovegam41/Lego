-- Insertar datos de ejemplo en example_crud
-- Estos registros demuestran las capacidades del sistema CRUD

INSERT INTO example_crud (name, sku, description, price, stock, min_stock, category, is_active) VALUES
('Ejemplo Item 1', 'EX-001', 'Este es un registro de ejemplo para demostrar el CRUD', 99.99, 15, 5, 'Categoría A', TRUE),
('Ejemplo Item 2', 'EX-002', 'Segundo registro de ejemplo con diferentes valores', 149.99, 30, 10, 'Categoría B', TRUE),
('Ejemplo Item 3', 'EX-003', 'Tercer registro con stock bajo para probar alertas', 79.99, 3, 5, 'Categoría A', TRUE),
('Ejemplo Item 4', 'EX-004', 'Cuarto registro de ejemplo', 199.99, 50, 15, 'Categoría C', TRUE),
('Ejemplo Item 5', 'EX-005', 'Quinto registro - inactivo para demostrar filtros', 59.99, 0, 5, 'Categoría B', FALSE),
('Ejemplo Item 6', 'EX-006', 'Sexto registro con stock moderado', 129.99, 20, 8, 'Categoría A', TRUE),
('Ejemplo Item 7', 'EX-007', 'Séptimo registro de ejemplo', 89.99, 40, 12, 'Categoría C', TRUE),
('Ejemplo Item 8', 'EX-008', 'Octavo registro para demostrar paginación', 169.99, 8, 5, 'Categoría B', TRUE),
('Ejemplo Item 9', 'EX-009', 'Noveno registro con precio alto', 299.99, 12, 5, 'Categoría A', TRUE),
('Ejemplo Item 10', 'EX-010', 'Décimo registro para completar la primera página', 119.99, 25, 10, 'Categoría C', TRUE),
('Ejemplo Item 11', 'EX-011', 'Undécimo registro - segunda página', 159.99, 18, 8, 'Categoría B', TRUE),
('Ejemplo Item 12', 'EX-012', 'Duodécimo registro final', 189.99, 6, 5, 'Categoría A', TRUE);
