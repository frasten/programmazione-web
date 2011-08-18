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

/* Impostiamo il charset UTF-8 */
if ( function_exists( 'mysql_set_charset' ) ) {
	// PHP >= 5.2
	mysql_set_charset( 'utf8', $db );
}
else {
	mysql_query( "SET NAMES 'utf8'", $db );
}


// Controlliamo che esistano le tabelle, in caso le creiamo.
db_check_tables();




/*
 * Questa funzione si occupa di controllare se le tabelle esistono nel
 * database, ed in caso contrario le genera automaticamente. */
function db_check_tables() {
	global $db, $config;

	/*******************
	 *  PUBBLICAZIONI  *
	 *******************/

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
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella Autori delle Pubblicazioni */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]pubautore` (
	`id_autore` INTEGER  NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id_autore`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per la relazione tra Pubblicazione e Autori */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]pubblicazione_pubautore` (
	`id_pubblicazione` INTEGER  NOT NULL,
	`id_autore` INTEGER  NOT NULL,
	PRIMARY KEY (`id_pubblicazione`, `id_autore`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per la lista di Journal */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]journal` (
	`id_journal` INTEGER  NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id_journal`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );



	/*****************
	 *     CORSI     *
	 *****************/
	/* Tabella per i corsi */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]corso` (
	`id_corso` INTEGER  NOT NULL AUTO_INCREMENT,
	`id_facolta` INTEGER  NOT NULL,
	`nome` VARCHAR(255) NOT NULL,
	`intestazione` TEXT,
	`orario` TEXT,
	`ricevimento` TEXT,
	`obiettivi` TEXT,
	`programma` TEXT,
	`esame` TEXT,
	`materiali` TEXT,
	PRIMARY KEY (`id_corso`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per le news */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]news` (
	`id_news` INTEGER  NOT NULL AUTO_INCREMENT,
	`id_corso` INTEGER NOT NULL,
	`ordine` INTEGER NOT NULL,
	`nascondi` ENUM('0','1') NOT NULL DEFAULT '0',
	`testo` TEXT NOT NULL,
	`file` VARCHAR(255),
	PRIMARY KEY (`id_news`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per le facolta' */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]facolta` (
	`id_facolta` INTEGER  NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255)  NOT NULL,
	PRIMARY KEY (`id_facolta`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );



	/*****************
	 *    DOCENTI    *
	 *****************/
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]docente` (
	`id_docente` INTEGER  NOT NULL AUTO_INCREMENT,
	`nome` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`id_docente`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]docente_corso` (
	`id_docente` INTEGER  NOT NULL,
	`id_corso` INTEGER  NOT NULL,
	PRIMARY KEY (`id_docente`,`id_corso`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );


	/***********************
	 * MATERIALE DIDATTICO *
	 ***********************/

	/* Tabella per le sezioni */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]sezione` (
	`id_sezione` INTEGER  NOT NULL AUTO_INCREMENT,
	`id_corso` INTEGER  NOT NULL,
	`titolo` VARCHAR(255)  NOT NULL,
	`note` TEXT  NOT NULL,
	PRIMARY KEY (`id_sezione`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per i file del materiale didattico */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]file_materiale` (
	`id_file` INTEGER  NOT NULL AUTO_INCREMENT,
	`id_sezione` INTEGER  NOT NULL,
	`titolo` VARCHAR(255)  NOT NULL,
	`url` VARCHAR(255)  NOT NULL,
	`aggiornato` ENUM('0','1')  NOT NULL DEFAULT '0',
	`nascondi` ENUM('0','1')  NOT NULL DEFAULT '0',
	`ordine` INTEGER NOT NULL DEFAULT 0
	PRIMARY KEY (`id_file`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/*****************
	 *     LOGIN     *
	 *****************/

	/* Tabella per il login */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]login` (
	`username` VARCHAR(20)  NOT NULL,
	`salt` VARCHAR(20)  NOT NULL,
	`password` VARCHAR(40)  NOT NULL,
	PRIMARY KEY (`username`)
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );

	/* Tabella per il "ricorda login" */
	$query = <<<EOF
CREATE TABLE IF NOT EXISTS `$config[db_prefix]persistent_login` (
	`username` VARCHAR(20)  NOT NULL,
	`salt` VARCHAR(20) NOT NULL,
	`tokenhash` VARCHAR(40) NOT NULL,
	`timestamp` DATETIME NOT NULL
)
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOF;
	mysql_query( $query, $db );


}


?>
