CREATE USER 'places'@'%' IDENTIFIED WITH mysql_native_password BY 'places';
GRANT ALL PRIVILEGES ON * . * TO 'places'@'%';
FLUSH PRIVILEGES;