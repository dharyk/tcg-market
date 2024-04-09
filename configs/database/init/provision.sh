echo "** Creating default DB and users"

mysql -uroot -p$MYSQL_ROOT_PASSWORD --execute \
"CREATE DATABASE `${MYSQL_DATABASE}` /*\!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */;
CREATE USER 'root'@'${MYSQL_ROOT_HOST}' IDENTIFIED WITH caching_sha2_password BY '${MYSQL_ROOT_PASSWORD}';
GRANT ALL ON *.* TO 'root'@'${MYSQL_ROOT_HOST}' WITH GRANT OPTION;
CREATE USER '${MYSQL_USER}'@'${MYSQL_HOST}' IDENTIFIED WITH caching_sha2_password BY '${MYSQL_PASSWORD}';
GRANT ALL PRIVILEGES ON `${MYSQL_DATABASE}`.* TO '${MYSQL_USER}'@'${MYSQL_HOST}';
FLUSH PRIVILEGES;"

echo "** Finished creating default DB and users"