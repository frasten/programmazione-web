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
	$id = intval( $_POST['id_news'] );
	if ( $id <= 0 ) esci();

	// Scambio la visibilita'
	$query = <<<EOF
UPDATE `$config[db_prefix]news`
SET `nascondi`= IF(`nascondi`='1', '0', '1')
WHERE `id_news` = '$id'
LIMIT 1
EOF;
	mysql_query( $query, $db );

	$ok = mysql_affected_rows( $db );
	if ( $ok <= 0 ) {
		$json['error'] = "Salvataggio fallito.";
		esci();
	}

	$query = <<<EOF
SELECT `nascondi`
FROM `$config[db_prefix]news`
WHERE `id_news` = '$id'
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
SELECT
	s.`id_sezione`,
	s.`titolo` AS titolo_sez,
	`id_file`,
	f.`titolo` AS titolo_file,
	`nascondi`
FROM `$config[db_prefix]sezione` AS s
LEFT JOIN `$config[db_prefix]file_materiale` AS f
	USING (`id_sezione`)
WHERE `id_corso` = '$id_corso'
ORDER BY `id_sezione` ASC, `ordine` ASC
EOF;
	$result = mysql_query( $query, $db );

	if ( mysql_num_rows( $result ) ) {
		$oldsez = false;
		while ( $riga = mysql_fetch_assoc( $result ) ) {
			if ( $oldsez != $riga['id_sezione'] ) {
				if ( $oldsez !== false ) echo "</ul>\n";
				printf( "<a href='javascript:void(0)' onclick='apriDialogoSezione(%d)' style='font-weight: bold'>%s</a>\n",
					$riga['id_sezione'], htmlspecialchars( $riga['titolo_sez'] ) );
				echo "<ul>\n";
				$oldsez = $riga['id_sezione'];
				if ( ! $riga['id_file'] ) continue;
			}
			echo "<li>\n";
			printf( "<a href='javascript:void(0)' onclick='apriDialogoFile(%s)'>%s</a>\n",
				intval( $riga['id_file'] ),
				htmlspecialchars( $riga['titolo_file'] ) );
			echo "</li>\n";
		}
		echo "</ul>\n";
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



function esci( $msg = '' ) {
	global $json;
	if ( $msg ) $json['error'] = $msg;
	echo json_encode( $json );
	exit;
}

function gestisci_file_upload( $prefix ) {
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

	// Trailing slash
	if ( substr( $config['upload_path'], -1 ) !== '/' ) $config['upload_path'] .= '/';

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
		// Nota: due volte dirname perche' dobbiamo risalire di un livello (siamo in /ajax/)
		return "http://$_SERVER[SERVER_NAME]" . dirname( dirname( $_SERVER['SCRIPT_NAME'] ) ) . "/$config[upload_path]$filename";
	} else{
		echo 'Errore nel caricamento del file, si prega di riprovare.';
		return false;
	}
}

?>
