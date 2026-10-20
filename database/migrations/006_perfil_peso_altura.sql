-- Agrega peso y altura al perfil del alumno sin duplicar columnas existentes.
-- No depende de que exista una columna llamada dni para definir el orden fisico.

SET @forja_has_peso = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuario'
      AND COLUMN_NAME = 'peso_kg'
);
SET @forja_sql = IF(
    @forja_has_peso = 0,
    'ALTER TABLE usuario ADD COLUMN peso_kg DECIMAL(5,2) NULL',
    'SELECT ''La columna peso_kg ya existe; no se modifica'' AS mensaje'
);
PREPARE forja_stmt FROM @forja_sql;
EXECUTE forja_stmt;
DEALLOCATE PREPARE forja_stmt;

SET @forja_has_altura = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuario'
      AND COLUMN_NAME = 'altura_cm'
);
SET @forja_sql = IF(
    @forja_has_altura = 0,
    'ALTER TABLE usuario ADD COLUMN altura_cm DECIMAL(5,2) NULL',
    'SELECT ''La columna altura_cm ya existe; no se modifica'' AS mensaje'
);
PREPARE forja_stmt FROM @forja_sql;
EXECUTE forja_stmt;
DEALLOCATE PREPARE forja_stmt;
