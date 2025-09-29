CREATE TABLE `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `direccion` VARCHAR(255) NOT NULL,
  `perfil` VARCHAR(100) DEFAULT NULL,
  `clave` VARCHAR(200) NOT NULL,
  `token` VARCHAR(100) DEFAULT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estado` INT(11) NOT NULL DEFAULT 1,
  `rol` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`rol`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `carpetas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `fecha_create` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_usuario` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `carpetas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `archivos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `tipo` VARCHAR(100) NOT NULL,
  `fecha_create` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_carpeta` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_carpeta` (`id_carpeta`),
  CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_carpeta`) REFERENCES `carpetas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `detalle_archivos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fecha_add` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `correo` VARCHAR(100) NOT NULL,
  `estado` INT(11) NOT NULL DEFAULT 1,
  `id_carpeta` INT(11) NOT NULL,
  `id_archivo` INT(11) NOT NULL,
  `id_usuario` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_archivo` (`id_archivo`),
  KEY `id_carpeta` (`id_carpeta`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `detalle_archivos_ibfk_1` FOREIGN KEY (`id_archivo`) REFERENCES `archivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_archivos_ibfk_2` FOREIGN KEY (`id_carpeta`) REFERENCES `carpetas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_archivos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hojas_ruta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo_hoja_ruta` VARCHAR(50) NOT NULL UNIQUE,
  `asunto` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_actual` VARCHAR(50) NOT NULL,
  `id_archivo` INT(11) NOT NULL UNIQUE,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hojas_ruta_archivos` FOREIGN KEY (`id_archivo`) REFERENCES `archivos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `movimientos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_hoja_ruta` INT(11) NOT NULL,
  `id_usuario_origen` INT(11) NOT NULL,
  `id_usuario_destino` INT(11) NOT NULL,
  `fecha_movimiento` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `descripcion_movimiento` TEXT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mov_hoja_ruta` FOREIGN KEY (`id_hoja_ruta`) REFERENCES `hojas_ruta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_usuario_origen` FOREIGN KEY (`id_usuario_origen`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_usuario_destino` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `notificaciones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario_destino` INT(11) NOT NULL,
  `id_hoja_ruta` INT(11) NULL,
  `mensaje` VARCHAR(255) NOT NULL,
  `leida` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_notif_usuario` FOREIGN KEY (`id_usuario_destino`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notif_hoja_ruta` FOREIGN KEY (`id_hoja_ruta`) REFERENCES `hojas_ruta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;