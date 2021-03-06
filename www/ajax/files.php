<?php

require_once( '../inc/ajax-framework.inc.php' );


$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) ajax_esci( 'Accesso negato.' );

if ( empty( $_GET['action'] ) ) ajax_esci();


if ( $_GET['action'] == 'savefileorder' ) {
	/********************************
	 * SALVATAGGIO ORDINE DEI FILES *
	 ********************************/

	$id_sezione = intval( $_GET['id_sezione'] );
	if ( $id_sezione <= 0 ) ajax_esci( 'ID sezione non valido.' );

	if ( ! is_array( $_GET['file'] ) ) ajax_esci( 'Nulla da fare.' );

	foreach ( $_GET['file'] as $pos => $id_file ) {
		$id_file = intval( $id_file );
		$query = <<<EOF
UPDATE `$config[db_prefix]file_materiale`
SET `ordine` = '$pos'
WHERE `id_file` = '$id_file' AND `id_sezione` = '$id_sezione'
LIMIT 1
EOF;
		mysql_query( $query, $db );
	}

	$json['success'] = 1;
	ajax_esci();
}
else if ( $_GET['action'] == 'getfile' ) {
	/***********************************
	 * CARICAMENTO DEI DATI DI UN FILE *
	 ***********************************/

	if ( empty( $_POST['id'] ) ) ajax_esci();
	$id = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `titolo`, `url`, `aggiornato`, `nascondi`
FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) ajax_esci( 'ID non valido.' );

	$riga = mysql_fetch_assoc( $result );
	$riga['aggiornato'] = intval( $riga['aggiornato'] );
	$riga['nascondi'] = intval( $riga['nascondi'] );

	$json = array_merge( $json, $riga );

	$json['success'] = 1;
	ajax_esci();
}
else if ( $_GET['action'] == 'savefile' ) {
	/**************************
	 * SALVATAGGIO DI UN FILE *
	 **************************/

	if ( empty( $_POST['titolo'] ) ) ajax_esci( 'Inserire un titolo per il file.' );
	$titolo = strip_tags( $_POST['titolo'] ); // Anti XSS
	$aggiornato = empty( $_POST['aggiornato'] ) ? 0 : 1;
	$nascondi = empty( $_POST['nascondi'] ) ? 0 : 1;
	if ( ! isset( $_POST['tipourl'] ) ) $_POST['tipourl'] = 'url';
	if ( $_POST['tipourl'] != 'url' ) $_POST['tipourl'] = 'upload';

	if ( $_POST['tipourl'] == 'url' ) {
		if ( empty( $_POST['url'] ) ) ajax_esci( 'Inserire un indirizzo.' );
	}
	else {
		if ( empty( $_FILES['file']['name'] ) ) ajax_esci( 'Selezionare un file da caricare.' );
	}


	$titolo_esc = mysql_real_escape_string( $titolo );

	if ( empty( $_POST['id_file'] ) ) {
		/**************
		 * FILE NUOVO *
		 *************/

		if ( empty( $_POST['id_sezione'] ) ) ajax_esci( 'ID sezione non valido.' );
		$id_sezione = intval( $_POST['id_sezione'] );

		$query = <<<EOF
INSERT INTO `$config[db_prefix]file_materiale`
(`id_sezione`,`titolo`,`url`,`aggiornato`,`nascondi`)
VALUES
('$id_sezione','$titolo_esc','','$aggiornato','$nascondi')
EOF;
		mysql_query( $query, $db );

		$id = mysql_insert_id( $db );

		if ( ! $id ) ajax_esci( 'Errore nell\'inserimento.' );

		if ( $_POST['tipourl'] == 'url' ) {
			$url = $_POST['url'];
			// Ho inserito un path relativo
			if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $url ) )
				$url = "http://$url";
		}
		else {
			$url = gestisci_file_upload( "d$id", true );
		}

		if ( ! $url ) {
			// Annullo l'inserimento:
			$query = <<<EOF
DELETE FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
			mysql_query( $query, $db );
			ajax_esci( 'Errore nel salvataggio del file.' );
		}

		$url = mysql_real_escape_string( $url );
		$query = <<<EOF
UPDATE `$config[db_prefix]file_materiale`
SET `url` = '$url'
WHERE `id_file` = '$id'
LIMIT 1
EOF;
		mysql_query( $query, $db );
		if ( mysql_errno() ) ajax_esci( 'Errore nel salvataggio dei dati.' );


	}
	else {
		/***********************
		 * FILE GIA` ESISTENTE *
		 ***********************/

		$id = intval( $_POST['id_file'] );

		// Salvataggio file
		$url = mysql_real_escape_string( gestisci_file_update( $id ) );
		if ( ! $url ) ajax_esci( 'Errore nel caricamento del file.' );

		$query = <<<EOF
UPDATE `$config[db_prefix]file_materiale`
SET
	`titolo` = '$titolo_esc',
	`aggiornato` = '$aggiornato',
	`nascondi` = '$nascondi',
	`url` = '$url'
WHERE `id_file` = '$id'
LIMIT 1
EOF;
		$result = mysql_query( $query, $db );
		if ( mysql_errno() ) ajax_esci( 'Errore nel salvataggio.' );

	}

	$json['titolo'] = $titolo;
	$json['nascondi'] = $nascondi;
	$json['aggiornato'] = $aggiornato;
	$json['id_file'] = $id;
	$json['success'] = 1;
	ajax_esci();

}
else if ( $_GET['action'] == 'eliminafile' ) {
	/***************************
	 * ELIMINAZIONE DI UN FILE *
	 ***************************/

	if ( empty( $_POST['id'] ) ) ajax_esci();
	$id = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `url`
FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	$riga = mysql_fetch_assoc( $result );
	if ( empty( $riga ) ) ajax_esci( 'ID non valido.' );

	if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $riga['url'] ) ) {
		// Era un path relativo, un file caricato con il form di upload
		elimina_file( $riga['url'] );
	}

	$query = <<<EOF
DELETE FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );


	if ( mysql_errno() ) ajax_esci( 'Errore nell\'eliminazione.' );

	$json['id'] = $id;
	$json['success'] = 1;
	ajax_esci();
}
else {
	ajax_esci();
}



/*
 * Chiedo di salvare un URL:
 *  => era uguale => non faccio niente
 *  => e' diverso
 *    => era un path relativo => cancello il vecchio file => scrivo il nuovo url (assoluto)
 *    => era un path assoluto => sovrascrivo l'url e basta
 * uploado un file
 *  => era un path relativo => cancello il vecchio file => salvo il nuovo => scrivo il nuovo url relativo
 *  => era un path assoluto => salvo il nuovo file => scrivo il nuovo url relativo
 * */
function gestisci_file_update( $id ) {
	global $config, $db;

	$query = <<<EOF
SELECT `url`
FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	$riga = mysql_fetch_assoc( $result );
	if ( $_POST['tipourl'] == 'url' && $riga['url'] == $_POST['url'] ) return $_POST['url'];

	if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $riga['url'] ) ) {
		// Era un path relativo, un file caricato con il form di upload
		elimina_file( $riga['url'] );
	}

	if ( $_POST['tipourl'] == 'upload' ) {
		return gestisci_file_upload( "d$id", true ); // d = didattica
	}

	if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $_POST['url'] ) )
		$_POST['url'] = "http://$_POST[url]";
	return $_POST['url'];
}


?>
