<?php

require_once( '../inc/framework.inc.php' );

$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) esci( 'Accesso negato.' );

if ( empty( $_GET['action'] ) ) esci();


if ( $_GET['action'] == 'savenews' ) {
	/* Creazione di una nuova news */

	$id_corso = intval( $_POST['id_corso'] );
	if ( $id_corso <= 0 ) esci( 'ID non valido.' );

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

	if ( ! $id_news ) esci( 'Errore nel salvataggio.' );

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


	esci();
}
else if ( $_GET['action'] == 'savenewsorder' ) {
	$id_corso = intval( $_GET['id_corso'] );
	if ( $id_corso <= 0 ) esci( 'ID corso non valido.' );

	if ( ! is_array( $_GET['news'] ) ) esci( 'Nulla da fare.' );

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
	esci();
}
else if ( $_GET['action'] == 'togglevisibility' ) {
	$id = intval( $_POST['id'] );
	if ( $id <= 0 ) esci();

	if ( ! empty( $_POST['obj_type'] ) && $_POST['obj_type'] == 'file' ) {
		$tabella = "$config[db_prefix]file_materiale";
		$id_field = "id_file";
	}
	else {
		$tabella = "$config[db_prefix]news";
		$id_field = "id_news";
	}

	// Scambio la visibilita'
	$query = <<<EOF
UPDATE `$tabella`
SET `nascondi`= IF(`nascondi`='1', '0', '1')
WHERE `$id_field` = '$id'
LIMIT 1
EOF;
	mysql_query( $query, $db );

	$ok = mysql_affected_rows( $db );
	if ( $ok <= 0 ) {
		$json['error'] = "Salvataggio fallito.";
		esci();
	}

	// Prendo il dato salvato e lo restituisco all'utente
	$query = <<<EOF
SELECT `nascondi`
FROM `$tabella`
WHERE `$id_field` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );

	if ( ! $result || ! mysql_num_rows( $result ) ) {
		$json['error'] = "Errore interno.";
		esci();
	}

	$riga = mysql_fetch_assoc( $result );

	$json['nascondi'] = intval( $riga['nascondi'] );
	$json['success'] = 1;
	$json['id'] = $id;

	esci();
}
else if ( $_GET['action'] == 'getsezione' ) {
	if ( empty( $_POST['id'] ) ) esci();
	$id = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `titolo`, `note`
FROM `$config[db_prefix]sezione`
WHERE `id_sezione` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) esci( 'ID non valido.' );

	$riga = mysql_fetch_assoc( $result );
	$json['titolo'] = $riga['titolo'];
	$json['note'] = $riga['note'];

	$query = <<<EOF
SELECT `id_file`, `titolo`, `nascondi`
FROM `$config[db_prefix]file_materiale`
WHERE `id_sezione` = '$id'
ORDER BY `ordine` ASC
EOF;

	$result = mysql_query( $query, $db );
	$json['files'] = array();
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$riga['nascondi'] = intval( $riga['nascondi'] );
		$json['files'][] = $riga;
	}

	$json['success'] = 1;
	esci();
}
else if ( $_GET['action'] == 'savesezione' ) {
	if ( ! isset( $_POST['id'] ) ) esci();
	if ( empty( $_POST['id_corso'] ) ) esci();
	if ( ! isset( $_POST['note'] ) ) esci();
	if ( empty( $_POST['titolo'] ) ) esci( 'Inserire un titolo!' );

	$id = intval( $_POST['id'] );
	$id_corso = intval( $_POST['id_corso'] );
	$_POST['titolo'] = mysql_real_escape_string( $_POST['titolo'] );
	$_POST['note'] = mysql_real_escape_string( $_POST['note'] );

	if ( $id == 0 ) {
		// Nuova sezione
		$query = <<<EOF
INSERT INTO `$config[db_prefix]sezione`
(`id_corso`,`titolo`,`note`)
VALUES
('$id_corso','$_POST[titolo]','$_POST[note]')
EOF;
		mysql_query( $query, $db );
		$id = mysql_insert_id( $db );
		if ( ! $id ) esci( 'Errore nell\'inserimento.' );
		$json['id'] = $id;
	}
	else {
		// Aggiorniamo una sezione gia' esistente
		$query = <<<EOF
UPDATE `$config[db_prefix]sezione`
SET
	`titolo` = '$_POST[titolo]',
	`note` = '$_POST[note]'
WHERE `id_sezione` = '$id'
LIMIT 1
EOF;
		mysql_query( $query, $db );
		if ( mysql_errno() ) esci( 'Errore nel salvataggio.' );
	}

	$json['success'] = 1;
	esci();
}
else if ( $_GET['action'] == 'loadlistasezioni' ) {
	if ( empty( $_GET['id_corso'] ) ) esci( 'ID non valido.' );

	$id_corso = intval( $_GET['id_corso'] );

	$query = <<<EOF
SELECT `id_sezione`, `titolo`
FROM `$config[db_prefix]sezione`
WHERE `id_corso` = '$id_corso'
ORDER BY `id_sezione` ASC
EOF;
	$result = mysql_query( $query, $db );

	$sezioni = array();
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$s = array(
			'id'     => $riga['id_sezione'],
			'titolo' => $riga['titolo'],
			'files'      => array()
		);

		// Per ogni sezione carico eventuali files
		$query = <<<EOF
SELECT `id_file`, `titolo`
FROM `$config[db_prefix]file_materiale`
WHERE `id_sezione` = '$riga[id_sezione]' AND `nascondi` = '0'
ORDER BY `ordine` ASC
EOF;
		$resultfile = mysql_query( $query, $db );
		while ( $f = mysql_fetch_assoc( $resultfile ) ) {
			$s['files'][] = array(
				'id'     => $f['id_file'],
				'titolo' => $f['titolo']
			);
		}
		$sezioni[] = $s;
	}


	if ( sizeof( $sezioni ) ) {
		foreach ( $sezioni as $sez ) {
			printf( "<a href='javascript:void(0)' onclick='apriDialogoSezione(%d)' style='font-weight: bold'>%s</a>\n",
				intval( $sez['id'] ), htmlspecialchars( $sez['titolo'] ) );

			echo "<ul>\n";
			foreach ( $sez['files'] as $f ) {
				echo "<li>\n";
				printf( "<a href='javascript:void(0)' onclick='apriDialogoFile(%s)'>%s</a>\n",
					intval( $f['id'] ),
					htmlspecialchars( $f['titolo'] ) );
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	}
}
else if ( $_GET['action'] == 'savefileorder' ) {
	$id_sezione = intval( $_GET['id_sezione'] );
	if ( $id_sezione <= 0 ) esci( 'ID sezione non valido.' );

	if ( ! is_array( $_GET['file'] ) ) esci( 'Nulla da fare.' );

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
	esci();
}
else if ( $_GET['action'] == 'getfile' ) {
	if ( empty( $_POST['id'] ) ) esci();
	$id = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `titolo`, `url`, `aggiornato`, `nascondi`
FROM `$config[db_prefix]file_materiale`
WHERE `id_file` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) esci( 'ID non valido.' );

	$riga = mysql_fetch_assoc( $result );
	$riga['aggiornato'] = intval($riga['aggiornato']);
	$riga['nascondi'] = intval($riga['nascondi']);

	$json = array_merge( $json, $riga );

	$json['success'] = 1;
	esci();
}
else if ( $_GET['action'] == 'savefile' ) {

	$titolo = ! empty( $_POST['titolo'] ) ? strip_tags( $_POST['titolo'] ) : ''; // Anti XSS
	$aggiornato = empty( $_POST['aggiornato'] ) ? 0 : 1;
	$nascondi = empty( $_POST['nascondi'] ) ? 0 : 1;
	if ( ! isset( $_POST['tipourl'] ) ) $_POST['tipourl'] = 'url';
	$titolo_esc = mysql_real_escape_string( $titolo );

	if ( empty( $_POST['id_file'] ) ) {
		// Nuovo file
		if ( empty( $_POST['id_sezione'] ) ) esci( 'ID sezione non valido.' );
		$id_sezione = intval( $_POST['id_sezione'] );

		$query = <<<EOF
INSERT INTO `$config[db_prefix]file_materiale`
(`id_sezione`,`titolo`,`url`,`aggiornato`,`nascondi`)
VALUES
('$id_sezione','$titolo_esc','','$aggiornato','$nascondi')
EOF;
		mysql_query( $query, $db );

		$id = mysql_insert_id( $db );

		if ( ! $id ) esci( 'Errore nell\'inserimento.' );

		if ( $_POST['tipourl'] == 'url' ) {
			$url = $_POST['url'];
			// Ho inserito un path relativo
			if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $url ) )
				$url = "http://$url";
		}
		else {
			$url = gestisci_file_upload( "d$id", true );
		}

		if ( ! $url ) esci( 'Errore nel salvataggio del file.' );

		$url = mysql_real_escape_string( $url );
		$query = <<<EOF
UPDATE `$config[db_prefix]file_materiale`
SET `url` = '$url'
WHERE `id_file` = '$id'
LIMIT 1
EOF;
		mysql_query( $query, $db );
		if ( mysql_errno() ) esci( 'Errore nel salvataggio dei dati.' );


	}
	else {
		// Aggiorno un file esistente
		$id = intval( $_POST['id_file'] );

		// Salvataggio file
		$url = mysql_real_escape_string( gestisci_file_update( $id ) );
		if ( ! $url ) esci( 'Errore nel caricamento del file.' );

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
		if ( mysql_errno() ) esci( 'Errore nel salvataggio.' );

	}

	$json['titolo'] = $titolo;
	$json['nascondi'] = $nascondi;
	$json['aggiornato'] = $aggiornato;
	$json['id_file'] = $id;
	$json['success'] = 1;
	esci();

}



function esci( $msg = '' ) {
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
			esci( "Il file caricato &egrave; di un tipo non consentito." );
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
		if ( $return_relativo ) return $filename;

		// Nota: due volte dirname perche' dobbiamo risalire di un livello (siamo in /ajax/)
		return "http://$_SERVER[SERVER_NAME]" . dirname( dirname( $_SERVER['SCRIPT_NAME'] ) ) . "/$config[upload_path]$filename";
	} else{
		echo 'Errore nel caricamento del file, si prega di riprovare.';
		return false;
	}
}


/*
 * metto un URL
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
