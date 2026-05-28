CREATE TABLE IF NOT EXISTS datos_clima (
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperatura DECIMAL(5,2),
    humedad INT,
    presion INT,
    velocidad_viento DECIMAL(5,2),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS umbrales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(20) NOT NULL,
    operador VARCHAR(2) NOT NULL,
    valor DECIMAL(6,2) NOT NULL,
    activo TINYINT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

INSERT INTO usuarios (nombre_usuario, contrasena) VALUES ('admin', 'admin');