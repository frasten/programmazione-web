<?php

require_once( 'config.inc.php' );

/* Evitiamo comportamenti non voluti delle magic quotes di PHP
 * (deprecate) */
function stripslashes_deep( &$value ) {
	$value = is_array( $value ) ?
	  array_map( 'stripslashes_deep', $value ) :
	  stripslashes( $value );

	return $value;
}

if( ( function_exists( 'get_magic_quotes_gpc' ) && get_magic_quotes_gpc() ) ||
    ( ini_get( 'magic_quotes_sybase' ) && ( strtolower( ini_get( 'magic_quotes_sybase' ) ) != 'off' ) ) ) {
	stripslashes_deep( $_GET );
	stripslashes_deep( $_POST );
	stripslashes_deep( $_COOKIE );
}



/***** CONNESSIONE AL DB *****/
$db = @mysql_connect( $config['db_host'],
                      $config['db_user'],
                      $config['db_pass'] );
@mysql_select_db( $config['db_name'], $db );


if ( ! $db ) {
	echo "Errore nella connessione al database.";
	return 1;
}

db_check_tables();






/*
 * Questa funzione si occupa di controllare se le tabelle esistono nel
 * database, ed in caso contrario le genera automaticamente. */
function db_check_tables() {
	global $db, $config;

	/* Tabella Pubblicazioni */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]pubblicazione` (
  `id_pubblicazione` INTEGER  NOT NULL AUTO_INCREMENT,
  `categoria` ENUM('rivista','libro','conferenza','monografia','curatela')  NOT NULL,
  `titolo` VARCHAR(255) NOT NULL,
  `anno` INTEGER  NOT NULL,
  `titolo_contesto` VARCHAR(255),
  `volume` INTEGER,
  `numero` INTEGER,
  `pag_inizio` INTEGER,
  `pag_fine` INTEGER,
  `abstract` TEXT  NOT NULL,
  `curatori_libro` TEXT ,
  `editore` VARCHAR(255) ,
  `num_pagine` INTEGER,
  `isbn` VARCHAR(255) ,
  `file` VARCHAR(255) ,
  PRIMARY KEY (`id_pubblicazione`)
);
EOF;
	mysql_query( $query, $db );

	/* Tabella Autori Pubblicazioni */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]pubautore` (
  `id_autore` INTEGER  NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255)  NOT NULL,
  PRIMARY KEY (`id_autore`)
);
EOF;
	mysql_query( $query, $db );

	/* Tabella per la relazione tra Pubblicazione e Autori */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]pubblicazione_pubautore` (
  `id_pubblicazione` INTEGER  NOT NULL,
  `id_autore` INTEGER  NOT NULL,
  PRIMARY KEY (`id_pubblicazione`, `id_autore`)
);
EOF;
	mysql_query( $query, $db );

	/* Tabella per la lista di Journal */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]journal` (
  `id_journal` INTEGER  NOT NULL,
  `nome` INTEGER  NOT NULL,
  PRIMARY KEY (`id_journal`)
);
EOF;
	mysql_query( $query, $db );

	/* Tabella per il login */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]login` (
  `username` VARCHAR(20)  NOT NULL,
  `salt` VARCHAR(20)  NOT NULL,
  `password` VARCHAR(40)  NOT NULL,
  PRIMARY KEY (`username`)
);
EOF;
	mysql_query( $query, $db );


}


?>
