<?php

// Mostra/nasconde la visualizzazione degli errori
// Consigliabile disabilitarlo in produzione.
$config['debug'] = true;


$config['db_host'] = 'localhost';
$config['db_user'] = 'pw';
$config['db_pass'] = '12345';
$config['db_name'] = 'pw2011';
$config['db_prefix'] = 'unibs_is_';

$config['hmac_psk'] = 'uXS-J_I8B0BtTQs)I;mV+htIMzXJ_lin91cfvTLg/oHr7aK2JB_ix!B5L1G/URg7';
$config['default_pass_len'] = 8;
$config['min_pass_len'] = 6;

$config['persistent_cookies_timeout'] = 60 * 60 * 24 * 7; // Una settimana

// Relativo alla root del sito
$config['upload_path'] = 'uploads/';

/*
 * COMANDI MYSQL PER LA CREAZIONE DEL DB
 * (NB: dati di test e autenticazione solo da localhost):

CREATE DATABASE pw2011;
CREATE USER 'pw'@'localhost' IDENTIFIED BY '12345';
GRANT ALL PRIVILEGES ON pw2011.* TO 'pw'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;

 * */

?>
