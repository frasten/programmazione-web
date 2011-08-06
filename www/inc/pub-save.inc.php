<?php

echo "<pre>" . print_r($_POST, true) . "</pre>";

// TODO: VALIDAZIONE


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
// TODO: autori

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




?>
