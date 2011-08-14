<?php

require_once( '../inc/framework.inc.php' );

$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) esci();

if ( empty( $_GET['action'] ) ) esci();


if ( $_GET['action'] == 'savenews' ) {
	/* Creazione di una nuova news */

	$id_corso = intval( $_POST['id_corso'] );
	if ( ! $id_corso ) esci();

	$nascondi = 0;
	if ( ! empty( $_POST['hide-news'] ) )
		$nascondi = 1;

	// La mettiamo nella posizione 1, quindi facciamo scorrere di 1 le
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
('$id_corso','1','$nascondi','$testo')
EOF;
	mysql_query( $query, $db );
	$id_news = mysql_insert_id( $db );

	if ( ! $id_news ) esci( '' );

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
	$json['success'] = 1;


	esci();
}


function esci( $msg = '' ) {
	global $json;
	if ( $msg ) $json['error'] = $msg;
	echo json_encode( $json );
	exit;
}

function gestisci_file_upload( $prefix ) {
	global $path_uploaded_dir;

	// Se non ho caricato nessun file, non faccio nulla
	if ( empty( $_FILES['file']['tmp_name'] ) ) return false;

	// Controllo le estensioni consentite. (ad es. escludere files .php)
	$filename = basename( $_FILES['file']['name'] );
	if ( preg_match( '/\.(.+)$/', $filename, $match ) ) {
		$blacklist_extensions = array( 'php' );
		if ( in_array( strtolower( $match[1] ), $blacklist_extensions ) ) {
			esci( "Errore: Il file caricato &egrave; di un tipo non consentito." );
		}
	}

	// Trailing slash
	if ( substr( $path_uploaded_dir, -1 ) !== '/' ) $path_uploaded_dir .= '/';

	// Ho caricato un file
	$path_uploaded_files = realpath( '.' ) . "/$path_uploaded_dir";

	if ( ! is_dir( $path_uploaded_files ) ) {
		// Directory non esistente, la creo.
		@mkdir( $path_uploaded_files, 0777 );
		// Creo anche un index file (vuoto), per evitare il listing dei files.
		@file_put_contents( $path_uploaded_files . 'index.php', '' );
	}

	if ( ! is_writable( $path_uploaded_files ) ) {
		esci(
			'Errore nel caricamento del file: ' .
			'accesso negato. Controllare i permessi di scrittura sul server!'
		);
	}

	// Gestisco i files duplicati anteponendo l'ID della pubblicazione al
	// nome del file.
	// Es. 12_nomefile.pdf
	$filename = $prefix . '_' . $filename;

	$target_path = $path_uploaded_files . $filename;
	if( move_uploaded_file( $_FILES['file']['tmp_name'], $target_path ) ) {
		// File caricato correttamente.
		return "http://$_SERVER[SERVER_NAME]" . dirname( $_SERVER['SCRIPT_NAME'] ) . "/$path_uploaded_dir$filename";
	} else{
		echo 'Errore nel caricamento del file, si prega di riprovare.';
		return false;
	}
}

?>
