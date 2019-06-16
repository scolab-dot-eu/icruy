CREATE USER 'camineria'@'localhost' IDENTIFIED BY 'camineria';
CREATE DATABASE `camineria` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
GRANT ALL PRIVILEGES ON camineria.* TO camineria@localhost;
FLUSH PRIVILEGES;
