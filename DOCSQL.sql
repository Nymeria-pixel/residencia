CREATE DATABASE IF NOT EXISTS sistema_residencia;
USE sistema_residencia;

CREATE TABLE IF NOT EXISTS solicitudes_residencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    email VARCHAR(100) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    sector VARCHAR(150) NOT NULL,
    vivienda VARCHAR(100) NOT NULL,
    tiempo_residencia INT NOT NULL,
    ruta_dni VARCHAR(255) NULL,
    ruta_recibo VARCHAR(255) NULL,
    motivo TEXT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);