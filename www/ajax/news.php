<?php

require_once( '../inc/framework.inc.php' );
require_once( '../inc/ajax-functions.inc.php' );

$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) ajax_esci( 'Accesso negato.' );

if ( empty( $_GET['action'] ) ) ajax_esci();



if ( $_GET['action'] == 'savenews' ) {
	/* Creazione di una nuova news */

	if ( empty( $_POST['testo'] ) ) ajax_esci( 'Inserire il contenuto della news.' );

	$id_corso = intval( $_POST['id_corso'] );
	if ( $id_corso <= 0 ) ajax_esci( 'ID non valido.' );

	$nascondi = 0;
	if ( ! empty( $_POST['hide-news'] ) )
		$nascondi = 1;

	// La mettiamo nella posizione 0, quindi facciamo scorrere di 1 le
	// precedenti news per questo corso.
	$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET `ordine` = (`ordine`+1)
WHERE `id_corso` = '$id_corso'
EOF;
	mysql_query( $query, $db );

	$testo = mysql_real_escape_string( $_POST['testo'] );
	// Inseriamo la news
	$query = <<<EOF
INSERT INTO `$config[db_prefix]news`
(`id_corso`,`ordine`,`nascondi`,`testo`)
VALUES
('$id_corso','0','$nascondi','$testo')
EOF;
	mysql_query( $query, $db );
	$id_news = mysql_insert_id( $db );

	if ( ! $id_news ) ajax_esci( 'Errore nel salvataggio.' );

	// Eventuale salvataggio file
	// Upload del file
	$uploaded_file = gestisci_file_upload( "c$id_news" ); // c = corso
	if ( $uploaded_file !== false ) {
		// Salvo questa impostazione nel database.
		// Lo facciamo in un secondo tempo, poiche' il nome del file salvato
		// dipende dall'ID assegnato alla pubblicazione.
		$uploaded_file = mysql_real_escape_string( $uploaded_file );
		$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET `file` = '$uploaded_file'
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
		mysql_query( $query, $db );

	}
	$json['nascondi'] = $nascondi;
	$json['success'] = 1;
	$json['testo'] = strip_tags( $_POST['testo'] );
	$json['id'] = $id_news;


	ajax_esci();
}
else if ( $_GET['action'] == 'savenewsorder' ) {
	$id_corso = intval( $_GET['id_corso'] );
	if ( $id_corso <= 0 ) ajax_esci( 'ID corso non valido.' );

	if ( ! is_array( $_GET['news'] ) ) ajax_esci( 'Nulla da fare.' );

	foreach ( $_GET['news'] as $pos => $id_news ) {
		$id_news = intval( $id_news );
		$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET `ordine` = '$pos'
WHERE `id_news` = '$id_news' AND `id_corso` = '$id_corso'
LIMIT 1
EOF;
		mysql_query( $query, $db );
	}

	$json['success'] = 1;
	ajax_esci();
}



?>
