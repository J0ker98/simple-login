# Simple Login

Basic login system with password hasing and salt

- Setup database connection info in config.ini.php
 
 Run the following SQL query to create members database table:
 
 CREATE TABLE IF NOT EXISTS members (
    id VARCHAR(13) NOT NULL,
    user VARCHAR(25) NOT NULL,
    password TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=INNODB
