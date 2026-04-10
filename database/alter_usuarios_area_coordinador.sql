-- Texto libre para el área del coordinador (no crea ni enlaza filas en la tabla `area` de horarios).
-- Ejecutar una vez en MySQL/MariaDB si la columna aún no existe.
ALTER TABLE usuarios
  ADD COLUMN area_coordinador VARCHAR(255) NULL DEFAULT NULL
  COMMENT 'Área del coordinador (informativa, independiente del catálogo area)'
  AFTER id_area;
