ALTER TABLE pedidos
    MODIFY COLUMN estado ENUM('pendiente','empaquetado','despachado','transito','entregado','cancelado') NOT NULL DEFAULT 'pendiente';

UPDATE pedidos SET estado = 'empaquetado' WHERE estado = 'preparacion';
UPDATE pedidos SET estado = 'despachado' WHERE estado = 'enviado';
