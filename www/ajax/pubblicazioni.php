<?php

require_once( '../inc/ajax-framework.inc.php' );


$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) ajax_esci( 'Accesso non autorizzato.' );

if ( empty( $_GET['action'] ) ) ajax_esci();

if ( $_GET['action'] == 'get_lista_autori' ) {
	/****************
	 * LISTA AUTORI *
	 ****************/

	$query = <<<EOF
SELECT `nome`
FROM `$config[db_prefix]pubautore`
ORDER BY `nome` ASC
EOF;
	$result = mysql_query( $query, $db );
	if ( ! $result ) ajax_esci( 'Errore interno.' );
	$autori = array();
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$autori[] = $riga['nome'];
	}

	$json['autori'] = $autori;
	$json['success'] = 1;
	ajax_esci();
}
else if ( $_GET['action'] == 'eliminapubblicazione' ) {
	/*************************
	 * ELIMINA PUBBLICAZIONE *
	 *************************/

	if ( empty( $_POST['id'] ) ) ajax_esci( 'ID non valido.' );
	$id_pubblicazione = intval( $_POST['id'] );

	// Controllo che l'id sia valido e gia' che ci sono prendo l'eventuale
	// file allegato salvato, che andrà dunque eliminato.
	$query = <<<EOF
SELECT `file`
FROM `$config[db_prefix]pubblicazione`
WHERE `id_pubblicazione` = '$id_pubblicazione'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) ajax_esci( 'ID non valido.' );
	$riga = mysql_fetch_assoc( $result );
	if ( ! empty( $riga['file'] ) ) {
		elimina_file( $riga['file'] );
	}

	$query = <<<EOF
DELETE FROM `$config[db_prefix]pubblicazione`
WHERE `id_pubblicazione` = '$id_pubblicazione'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( mysql_errno() ) ajax_esci( 'Errore nell\'eliminazione.' );

	$json['id'] = $id_pubblicazione;
	$json['success'] = 1;
	ajax_esci();
}
else {
	ajax_esci();
}


?>
