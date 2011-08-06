<?php

echo "<pre>" . print_r($_POST, true) . "</pre>";

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


// TODO: caricamento file

$query = <<<EOF
INSERT INTO `$config[db_prefix]pubblicazione`
(`categoria`,`titolo`,`anno`,`titolo_contesto`,
`volume`,`numero`,`pag_inizio`,`pag_fine`,`abstract`,
`curatori_libro`,`editore`,`num_pagine`,`isbn`,`file`)
VALUES
('$_POST[categoria]','$_POST[titolo]','$_POST[anno]',$data[titolo_contesto],
$data[volume],$data[numero],$data[pag_inizio],$data[pag_fine],'$_POST[abstract]',
$data[curatori_libro],$data[editore],$data[num_pagine],$data[isbn],NULL)
EOF;

mysql_query( $query, $db );

$id_pub = mysql_insert_id( $db );


// AUTORI
$id_autori = get_id_autori( $_POST['autori'] );
print_r( $id_autori );
salva_autori_pub( $id_pub, $id_autori );







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

		$query = "SELECT `id_autore` FROM `$config[db_prefix]pubautore` WHERE `nome` = '$a' LIMIT 1";
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

?>
