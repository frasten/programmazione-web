<?php

require_once( '../inc/ajax-framework.inc.php' );


$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) ajax_esci( 'Accesso negato.' );

if ( empty( $_GET['action'] ) ) ajax_esci();



if ( $_GET['action'] == 'savenews' ) {
	/********************
	 * SALVATAGGIO NEWS *
	 ********************/

	if ( empty( $_POST['testo'] ) ) ajax_esci( 'Inserire il contenuto della news.' );
	if ( empty( $_POST['id_corso'] ) ) ajax_esci( 'ID non valido.' );

	$id_corso = intval( $_POST['id_corso'] );

	$nascondi = 0;
	if ( ! empty( $_POST['hide-news'] ) )
		$nascondi = 1;

	$testo = mysql_real_escape_string( $_POST['testo'] );

	if ( empty( $_POST['id_news'] ) ) {
		/**************************
		 * INSERIMENTO NUOVA NEWS *
		 **************************/

		// La mettiamo nella posizione 0, quindi facciamo scorrere di 1 le
		// precedenti news per questo corso.
		$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET `ordine` = (`ordine`+1)
WHERE `id_corso` = '$id_corso'
EOF;
		mysql_query( $query, $db );

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
	}
	else {
	/**********************************
	 * MODIFICA DI UNA NEWS ESISTENTE *
	 **********************************/

		$id_news = intval( $_POST['id_news'] );
		$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET
	`nascondi` = '$nascondi',
	`testo` = '$testo'
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
		$result = mysql_query( $query, $db );
		if ( ! $result ) ajax_esci( 'Errore nel salvataggio.' );

		// Controllo se aveva un file precedentemente salvato, e se lo sto
		// sovrascrivendo.
		if ( ! empty( $_FILES['file']['tmp_name'] ) ) {
			$query = <<<EOF
SELECT `file`
FROM `$config[db_prefix]news`
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
			$result = mysql_query( $query, $db );
			$riga = mysql_fetch_assoc( $result );
			if ( ! empty( $riga['file'] ) ) {
				// In tal caso devo eliminare il vecchio file.
				elimina_file( $riga['file'] );
			}
		}
	}


	// Eventuale salvataggio file
	// Upload del file
	$uploaded_file = gestisci_file_upload( "n$id_news" ); // n = news
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
	/***************************
	 * SALVATAGGIO ORDINE NEWS *
	 ***************************/

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
else if ( $_GET['action'] == 'getnews' ) {
	/******************************
	 * RICHIESTA DATI DI UNA NEWS *
	 ******************************/

	if ( empty( $_POST['id'] ) ) ajax_esci( 'ID non valido.' );
	$id_news = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `id_news`, `nascondi`, `testo`, `file`
FROM `$config[db_prefix]news`
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) ajax_esci( 'ID non valido.' );
	$news = mysql_fetch_assoc( $result );

	$json = array_merge( $json, $news );

	$json['file'] = ! empty( $json['file'] );

	$json['success'] = 1;
	ajax_esci();
}
else if ( $_GET['action'] == 'eliminanews' ) {
	/****************************
	 * ELIMINAZIONE DI UNA NEWS *
	 ****************************/

	if ( empty( $_POST['id'] ) ) ajax_esci( 'ID non valido.' );
	$id_news = intval( $_POST['id'] );

	// Controllo che l'id sia valido e gia' che ci sono prendo l'eventuale
	// file allegato salvato, che andrà dunque eliminato.
	$query = <<<EOF
SELECT `file`
FROM `$config[db_prefix]news`
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) ajax_esci( 'ID non valido.' );
	$riga = mysql_fetch_assoc( $result );
	if ( ! empty( $riga['file'] ) ) {
		elimina_file( $riga['file'] );
	}

	$query = <<<EOF
DELETE FROM `$config[db_prefix]news`
WHERE `id_news` = '$id_news'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( mysql_errno() ) ajax_esci( 'Errore nell\'eliminazione.' );

	$json['id'] = $id_news;
	$json['success'] = 1;
	ajax_esci();
}
else {
	ajax_esci();
}

?>
