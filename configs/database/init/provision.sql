CREATE DATABASE `tcg_market` /*\!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */;
CREATE USER 'root'@'172.19.0.1' IDENTIFIED WITH caching_sha2_password BY 'password';
GRANT ALL ON *.* TO 'root'@'172.19.0.1' WITH GRANT OPTION;
CREATE USER 'tcg_market'@'%' IDENTIFIED WITH caching_sha2_password BY '1qa2ws3ed4rf';
GRANT ALL PRIVILEGES ON `tcg_market`.* TO 'tcg_market'@'%';
FLUSH PRIVILEGES;