-- Agrega el apellido al usuario sin duplicar la columna si ya existe.
-- En la base del equipo la columna ya fue creada a mano: en ese caso esta
-- migracion no modifica nada y solo sirve para recrear el esquema en otra base.
-- Las cuentas anteriores quedan con apellido vacio (''); el alumno o el admin
-- puede completarlo desde "Mi perfil".

SET @forja_has_apellido = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'usuario'
      AND COLUMN_NAME = 'apellido'
);
SET @forja_sql = IF(
    @forja_has_apellido = 0,
    'ALTER TABLE usuario ADD COLUMN apellido VARCHAR(100) NOT NULL DEFAULT '''' AFTER nombre',
    'SELECT ''La columna apellido ya existe; no se modifica'' AS mensaje'
);
PREPARE forja_stmt FROM @forja_sql;
EXECUTE forja_stmt;
DEALLOCATE PREPARE forja_stmt;
