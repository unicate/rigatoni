DROP DATABASE IF EXISTS rigatoni_test;

DROP USER IF EXISTS 'rigatoni_app'@'localhost';
DROP USER IF EXISTS 'rigatoni_app'@'%';

CREATE DATABASE rigatoni_test CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'rigatoni_app'@'localhost' IDENTIFIED BY '123456';
CREATE USER 'rigatoni_app'@'%' IDENTIFIED BY '123456';

GRANT ALL ON rigatoni_test.* TO 'rigatoni_app'@'localhost';
GRANT ALL ON rigatoni_test.* TO 'rigatoni_app'@'%';

FLUSH PRIVILEGES;

use rigatoni_test;