<?php

$config['db_host'] = 'localhost';
$config['db_user'] = 'pw';
$config['db_pass'] = '12345';
$config['db_name'] = 'pw2011';

/*
 * COMANDI MYSQL PER LA CREAZIONE DEL DB
 * (NB: dati di test e autenticazione solo da localhost):

CREATE DATABASE pw2011;
CREATE USER 'pw'@'localhost' IDENTIFIED BY '12345';
GRANT ALL PRIVILEGES ON pw2011.* TO 'pw'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

 * */


?>
