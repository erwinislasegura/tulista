INSERT INTO usuarios (nombre, email, password, rol, estado)
SELECT 'Super Administrador', 'superadmin@tulista.local', '$2y$12$RfmGss4UGywSyo0mofOzHeuEPkHH7NZVYk5Xn.7NXpKYePN0/zmdS', 'admin', 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'superadmin@tulista.local'
);
