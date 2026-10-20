-- Add role support without changing the database from application code.
ALTER TABLE usuario
    ADD COLUMN rol ENUM('ADMIN', 'USUARIO') NOT NULL DEFAULT 'USUARIO';
