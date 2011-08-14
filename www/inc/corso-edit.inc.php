<?php

if ( empty( $_GET['id'] ) ) return;

$id = intval( $_GET['id'] );

// Carico i dati del corso dal DB
$query = <<<EOF
SELECT *
FROM `$config[db_prefix]corso`
WHERE `id_corso` = '$id'
EOF;

$result = mysql_query( $query, $db );
if ( ! $result || ! mysql_num_rows( $result ) ) {
	echo "Errore interno.";
	return;
}

$corso = mysql_fetch_assoc( $result );

// Includo il form, che riempira' i vari campi con questi caricati dal DB.
include 'inc/corsi-form.inc.php';

?>
