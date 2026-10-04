CREATE DATABASE IF NOT EXISTS kurmigo_hris
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'kurmigo_hris'@'localhost'
  IDENTIFIED BY 'KurmigoHris@2026';

GRANT ALL PRIVILEGES ON kurmigo_hris.* TO 'kurmigo_hris'@'localhost';

FLUSH PRIVILEGES;

SHOW DATABASES LIKE 'kurmigo_hris';
