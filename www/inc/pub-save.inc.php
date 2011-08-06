<?php

echo "<pre>" . print_r($_POST, true) . "</pre>";

// TODO: VALIDAZIONE


$_POST = array_map('mysql_real_escape_string', $_POST);

// Metto a NULL i dati opzionali, vengono eventualmente riempiti poco sotto.
$data['autori_libro'] = $data['editore'] = $data['isbn'] = $data['citta'] = $data['nazione'] = 'NULL';


switch ( $_POST['categoria'] ) {
	case 'rivista':
		break;
	case 'libro':
		$data['autori_libro'] = "'$_POST[autori_libro]'";
		$data['editore'] = "'$_POST[editore]'";
		$data['isbn'] = "'$_POST[isbn]'";
		break;
	case 'conferenza':
		$data['citta'] = "'$_POST[citta]'";
		$data['nazione'] = "'$_POST[nazione]'";
		break;
	case 'monografia':
		$data['citta'] = "'$_POST[citta]'";
		$data['nazione'] = "'$_POST[nazione]'";
		break;
	case 'curatela':
		$data['citta'] = "'$_POST[citta]'";
		$data['nazione'] = "'$_POST[nazione]'";
		break;
	default:
		// Possibile rischio di attacco
		echo "Errore nei dati.";
		return;
}

// TODO: autori

// TODO: caricamento file

$query = <<<EOF
INSERT INTO `$config[db_prefix]pubblicazione`
(`categoria`,`titolo`,`anno`,`titolo_contesto`,`info`,
`autori_libro`,`editore`,`isbn`,`citta`,`nazione`,`file`)
VALUES
('$_POST[categoria]','$_POST[titolo]','$_POST[anno]','$_POST[titolo_contesto]','$_POST[info_addizionali]',
$data[autori_libro],$data[editore],$data[isbn],$data[citta],$data[nazione], NULL)
EOF;

mysql_query( $query, $db );

$id_pub = mysql_insert_id( $db );




?>
