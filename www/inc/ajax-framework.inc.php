<?php

$CONTENT_TYPE = 'application/json';

require_once( 'framework.inc.php' );

require_once( 'json.inc.php' );

function ajax_esci( $msg = '' ) {
	global $json;
	if ( $msg ) $json['error'] = $msg;
	echo json_encode( $json );
	exit;
}


function gestisci_file_upload( $prefix, $return_relativo = false ) {
	global $config;

	// Se non ho caricato nessun file, non faccio nulla
	if ( empty( $_FILES['file']['tmp_name'] ) ) return false;

	// Controllo le estensioni consentite. (ad es. escludere files .php)
	$filename = basename( $_FILES['file']['name'] );
	if ( preg_match( '/\.(.+)$/', $filename, $match ) ) {
		$blacklist_extensions = array( 'php' );
		if ( in_array( strtolower( $match[1] ), $blacklist_extensions ) ) {
			ajax_esci( "Il file caricato &egrave; di un tipo non consentito." );
		}
	}

	// Ho caricato un file
	$path_uploaded_files = realpath( '..' ) . "/$config[upload_path]";

	if ( ! is_dir( $path_uploaded_files ) ) {
		// Directory non esistente, la creo.
		@mkdir( $path_uploaded_files, 0777 );
		// Creo anche un index file (vuoto), per evitare il listing dei files.
		@file_put_contents( $path_uploaded_files . 'index.php', '' );
	}

	if ( ! is_writable( $path_uploaded_files ) ) {
		ajax_esci(
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
		if ( $return_relativo ) return $filename;

		// Nota: due volte dirname perche' dobbiamo risalire di un livello (siamo in /ajax/)
		return "http://$_SERVER[SERVER_NAME]" . dirname( dirname( $_SERVER['SCRIPT_NAME'] ) ) . "/$config[upload_path]$filename";
	} else{
		echo 'Errore nel caricamento del file, si prega di riprovare.';
		return false;
	}
}


function elimina_file( $path ) {
	global $config;

	// Per motivi di sicurezza non sono ammesse sottocartelle
	$path = basename( $path );

	$upload_path = realpath( '..' ) . "/$config[upload_path]";
	$path = "$upload_path/$path";
	if ( ! is_file( $path ) ) return false;
	unlink( $path );
}

?>
