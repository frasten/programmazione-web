<?php

echo "<pre>" . print_r($_POST, true) . "</pre>";
#echo "<pre>" . print_r($_FILES, true) . "</pre>";
#echo "<pre>" . print_r($_SERVER, true) . "</pre>";

// TODO: VALIDAZIONE
// TODO: se chiamo senza inviare dati, non devo fare nulla


$_POST = array_map('mysql_real_escape_string', $_POST);

// Metto a NULL i dati opzionali, vengono eventualmente riempiti poco sotto.
$data['titolo_contesto'] = $data['volume'] = $data['numero'] =
$data['pag_inizio'] = $data['pag_fine'] = $data['editore'] =
$data['curatori_libro'] = $data['isbn'] = $data['num_pagine'] = 'NULL';


switch ( $_POST['categoria'] ) {
	case 'rivista':
		$data['titolo_contesto'] = "'$_POST[titolo_contesto]'";
		$data['volume'] = "'$_POST[volume]'";
		$data['numero'] = "'$_POST[numero]'";
		$data['pag_inizio'] = "'$_POST[pag_inizio]'";
		$data['pag_fine'] = "'$_POST[pag_fine]'";
		break;
	case 'libro':
		$data['titolo_contesto'] = "'$_POST[titolo_contesto]'";
		$data['pag_inizio'] = "'$_POST[pag_inizio]'";
		$data['pag_fine'] = "'$_POST[pag_fine]'";
		$data['curatori_libro'] = "'$_POST[curatori_libro]'";
		$data['editore'] = "'$_POST[editore]'";
		$data['isbn'] = "'$_POST[isbn]'";
		break;
	case 'conferenza':
		$data['titolo_contesto'] = "'$_POST[titolo_contesto]'";
		$data['pag_inizio'] = "'$_POST[pag_inizio]'";
		$data['pag_fine'] = "'$_POST[pag_fine]'";
		break;
	case 'monografia':
		$data['editore'] = "'$_POST[editore]'";
		$data['num_pagine'] = "'$_POST[num_pagine]'";
		break;
	case 'curatela':
		$data['editore'] = "'$_POST[editore]'";
		break;
	default:
		// Possibile rischio di attacco
		echo "Errore nei dati.";
		return;
}


$query = <<<EOF
INSERT INTO `$config[db_prefix]pubblicazione`
(`categoria`,`titolo`,`anno`,`titolo_contesto`,
`volume`,`numero`,`pag_inizio`,`pag_fine`,`abstract`,
`curatori_libro`,`editore`,`num_pagine`,`isbn`)
VALUES
('$_POST[categoria]','$_POST[titolo]','$_POST[anno]',$data[titolo_contesto],
$data[volume],$data[numero],$data[pag_inizio],$data[pag_fine],'$_POST[abstract]',
$data[curatori_libro],$data[editore],$data[num_pagine],$data[isbn])
EOF;

mysql_query( $query, $db );

$id_pub = mysql_insert_id( $db );


// AUTORI
$id_autori = get_id_autori( $_POST['autori'] );
salva_autori_pub( $id_pub, $id_autori );


// Upload del file
$uploaded_file = gestisci_file_upload( "p$id_pub" );
if ( $uploaded_file !== false ) {
	// Salvo questa impostazione nel database.
	// Lo facciamo in un secondo tempo, poiche' il nome del file salvato
	// dipende dall'ID assegnato alla pubblicazione.
	$uploaded_file = mysql_real_escape_string( $uploaded_file );
	$query = <<<EOF
UPDATE `$config[db_prefix]pubblicazione`
SET `file` = '$uploaded_file'
WHERE `id_pubblicazione` = '$id_pub'
LIMIT 1
EOF;
	mysql_query( $query, $db );
}






// Per ogni autore richiesto, cerco se esisteva gia', in caso contrario
// lo inserisco
function get_id_autori( $str ) {
	global $config, $db;

	$autori = explode( ',', $str );
	$autori = array_map( 'trim', $autori );

	$list = array();
	// Nota: qui gli autori sono gia` addslashati, viene fatto in precedenza.
	foreach ( $autori as $a ) {
		if ( strlen( $a ) == 0 ) continue;

		$query = "SELECT `id_autore` FROM `$config[db_prefix]pubautore` WHERE BINARY `nome` = '$a' LIMIT 1";
		$result = mysql_query( $query, $db );
		if ( ! $result ) continue;
		if ( mysql_num_rows( $result ) == 0 ) {
			// Nuovo autore, inseriamolo nel DB.
			$query = "INSERT INTO `$config[db_prefix]pubautore` (nome) VALUES ('$a')";
			mysql_query( $query, $db );
			$id_autore = mysql_insert_id( $db );
		}
		else {
			// autore gia' esistente
			$row = mysql_fetch_assoc( $result );
			$id_autore = $row['id_autore'];
		}
		$list[] = $id_autore;
	}
	return $list;
}



function salva_autori_pub( $id_pub, $id_autori ) {
	global $config, $db;
	foreach ( $id_autori as $id_a ) {
		$query = <<<EOF
INSERT INTO `$config[db_prefix]pubblicazione_pubautore`
(id_pubblicazione, id_autore)
VALUES
('$id_pub', '$id_a')
EOF;
		mysql_query( $query, $db );
	}
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
			echo "Errore: Il file caricato &egrave; di un tipo non consentito.";
			return false;
		}
	}

	// Trailing slash
	if ( substr( $config['upload_path'], -1 ) !== '/' ) $config['upload_path'] .= '/';

	// Ho caricato un file
	$path_uploaded_files = realpath( '.' ) . "/$config[upload_path]";

	if ( ! is_dir( $path_uploaded_files ) ) {
		// Directory non esistente, la creo.
		@mkdir( $path_uploaded_files, 0777 );
		// Creo anche un index file (vuoto), per evitare il listing dei files.
		@file_put_contents( $path_uploaded_files . 'index.php', '' );
	}

	if ( ! is_writable( $path_uploaded_files ) ) {
		echo 'Errore nel caricamento del file: ';
		echo 'accesso negato. Controllare i permessi di scrittura sul server!';
		return;
	}

	// Gestisco i files duplicati anteponendo l'ID della pubblicazione al
	// nome del file.
	// Es. 12_nomefile.pdf
	$filename = $prefix . '_' . $filename;

	$target_path = $path_uploaded_files . $filename;
	if( move_uploaded_file( $_FILES['file']['tmp_name'], $target_path ) ) {
		// File caricato correttamente.
		return "http://$_SERVER[SERVER_NAME]" . dirname( $_SERVER['SCRIPT_NAME'] ) . "/$config[upload_path]$filename";
	} else{
		echo 'Errore nel caricamento del file, si prega di riprovare.';
		return false;
	}
}
?>
