-- Migracion adaptada para una base existente.
-- Crea las tablas solo si faltan y agrega descanso_segundos solo si falta.
-- Requiere que existan previamente las tablas rutina y ejercicio.

CREATE TABLE IF NOT EXISTS dia_rutina (
    id_dia INT AUTO_INCREMENT PRIMARY KEY,
    id_rutina INT NOT NULL,
    numero_dia INT NOT NULL,
    nombre_dia VARCHAR(100) NOT NULL,
    UNIQUE KEY uq_rutina_numero_dia (id_rutina, numero_dia),
    CONSTRAINT fk_dia_rutina FOREIGN KEY (id_rutina)
        REFERENCES rutina(id_rutina) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dia_rutina_ejercicio (
    id_dia INT NOT NULL,
    id_ejercicio INT NOT NULL,
    series INT NOT NULL DEFAULT 3,
    repeticiones INT NOT NULL DEFAULT 10,
    descanso_segundos INT NOT NULL DEFAULT 90,
    orden INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_dia, id_ejercicio),
    CONSTRAINT fk_dre_dia FOREIGN KEY (id_dia)
        REFERENCES dia_rutina(id_dia) ON DELETE CASCADE,
    CONSTRAINT fk_dre_ejercicio FOREIGN KEY (id_ejercicio)
        REFERENCES ejercicio(id_ejercicio) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Si la tabla ya existia antes de esta version y no tenia la columna,
-- la agrega sin borrar ni recrear los datos existentes.
SET @forja_has_descanso = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'dia_rutina_ejercicio'
      AND COLUMN_NAME = 'descanso_segundos'
);
SET @forja_sql = IF(
    @forja_has_descanso = 0,
    'ALTER TABLE dia_rutina_ejercicio ADD COLUMN descanso_segundos INT NOT NULL DEFAULT 90',
    'SELECT ''La columna descanso_segundos ya existe; no se modifica'' AS mensaje'
);
PREPARE forja_stmt FROM @forja_sql;
EXECUTE forja_stmt;
DEALLOCATE PREPARE forja_stmt;
