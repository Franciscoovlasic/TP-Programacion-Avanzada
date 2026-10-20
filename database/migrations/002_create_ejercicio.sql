-- Las FK de asignación se definirán en la migración de dia_rutina_ejercicio.
CREATE TABLE ejercicio (
    id_ejercicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre_ejercicio VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
