<?php

require_once( '../inc/ajax-framework.inc.php' );


$json = array(
	'success' => 0,
	'error' => ''
);

// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) ajax_esci( 'Accesso negato.' );

if ( empty( $_GET['action'] ) ) ajax_esci();


if ( $_GET['action'] == 'togglevisibility' ) {
	$id = intval( $_POST['id'] );
	if ( $id <= 0 ) ajax_esci();

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
		ajax_esci();
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
		ajax_esci();
	}

	$riga = mysql_fetch_assoc( $result );

	$json['nascondi'] = intval( $riga['nascondi'] );
	$json['success'] = 1;
	$json['id'] = $id;

	ajax_esci();
}
else if ( $_GET['action'] == 'getsezione' ) {
	if ( empty( $_POST['id'] ) ) ajax_esci();
	$id = intval( $_POST['id'] );

	$query = <<<EOF
SELECT `titolo`, `note`
FROM `$config[db_prefix]sezione`
WHERE `id_sezione` = '$id'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) ajax_esci( 'ID non valido.' );

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
	ajax_esci();
}
else if ( $_GET['action'] == 'savesezione' ) {
	if ( ! isset( $_POST['id'] ) ) ajax_esci();
	if ( empty( $_POST['id_corso'] ) ) ajax_esci();
	if ( ! isset( $_POST['note'] ) ) ajax_esci();
	if ( empty( $_POST['titolo'] ) ) ajax_esci( 'Inserire un titolo!' );

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
		if ( ! $id ) ajax_esci( 'Errore nell\'inserimento.' );
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
		if ( mysql_errno() ) ajax_esci( 'Errore nel salvataggio.' );
	}

	$json['success'] = 1;
	ajax_esci();
}
else if ( $_GET['action'] == 'loadlistasezioni' ) {
	if ( empty( $_GET['id_corso'] ) ) ajax_esci( 'ID non valido.' );

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
			printf( "<a href='javascript:;' onclick='apriDialogoSezione(%d)' style='font-weight: bold'>%s</a>\n",
				intval( $sez['id'] ), htmlspecialchars( $sez['titolo'] ) );

			echo "<ul>\n";
			foreach ( $sez['files'] as $f ) {
				echo "<li>\n";
				printf( "<a href='javascript:;' onclick='apriDialogoFile(%s)'>%s</a>\n",
					intval( $f['id'] ),
					htmlspecialchars( $f['titolo'] ) );
				echo "</li>\n";
			}
			echo "</ul>\n";
		}
	}
}
else if( $_GET['action'] == 'newfacolta' ) {
	if ( empty( $_POST['nome-facolta'] ) ) ajax_esci( 'Inserire il nome della Facolt&agrave;' );

	$nome = mysql_real_escape_string( $_POST['nome-facolta'] );

	$query = <<<EOF
INSERT INTO `$config[db_prefix]facolta`
(`nome`)
VALUES
('$nome')
EOF;
	mysql_query( $query, $db );
	$id_facolta = mysql_insert_id( $db );

	if ( ! $id_facolta ) ajax_esci( 'Errore nel salvataggio.' );

	$json['success'] = 1;
	$json['id_facolta'] = $id_facolta;
	ajax_esci();
}
else if( $_GET['action'] == 'newdocente' ) {
	if ( empty( $_POST['nome-docente'] ) ) ajax_esci( 'Inserire il nome del Docente' );

	$nome = mysql_real_escape_string( $_POST['nome-docente'] );

	$query = <<<EOF
INSERT INTO `$config[db_prefix]docente`
(`nome`)
VALUES
('$nome')
EOF;
	mysql_query( $query, $db );
	$id_docente = mysql_insert_id( $db );

	if ( ! $id_docente ) ajax_esci( 'Errore nel salvataggio.' );

	$json['success'] = 1;
	$json['id_docente'] = $id_docente;
	ajax_esci();
}
else if ( $_GET['action'] == 'eliminacorso' ) {
	if ( empty( $_POST['id'] ) ) ajax_esci( 'ID non valido.' );
	$id_corso = intval( $_POST['id'] );

	//Cancello l'eventuale file allegato alle news presenti
	$query = <<<EOF
SELECT `file`
FROM `$config[db_prefix]news`
WHERE `id_corso` = '$id_corso'
EOF;

	$result = mysql_query( $query, $db );
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		elimina_file( $riga['file'] );
	}

	//Cancello eventuali file delle sezioni
	$query = <<<EOF
SELECT `url`
FROM `$config[db_prefix]file_materiale` AS fm, `$config[db_prefix]sezione` AS s
WHERE s.`id_corso` = '$id_corso' AND s.`id_sezione` = fm.`id_sezione`
EOF;
	$result = mysql_query( $query, $db );
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $riga['url'] ) ) {
			// Era un path relativo, un file caricato con il form di upload
			elimina_file( $riga['url'] );
		}
	}

	$query = <<<EOF
DELETE FROM `$config[db_prefix]corso`
WHERE `id_corso` = '$id_corso'
LIMIT 1
EOF;
	$result = mysql_query( $query, $db );
	if ( mysql_errno() ) ajax_esci( 'Errore nell\'eliminazione.' );


	$json['id'] = $id_corso;
	$json['success'] = 1;
	ajax_esci();
}
else {
	ajax_esci();
}


?>
