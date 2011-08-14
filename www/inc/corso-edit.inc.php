<?php

if ( empty( $_GET['id'] ) ) return;

$id = intval( $_GET['id'] );

if ( sizeof( $_POST ) == 0 ): // Mostro form per la modifica

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
	include 'inc/corso-form.inc.php';

else: // Richiedo il salvataggio vero e proprio.

	// Controlli di routine e pulizia
	$_POST['facolta'] = intval( $_POST['facolta'] );
	if ( isset( $_POST['docente'] ) )
		$_POST['docente'] = array_map( 'intval', $_POST['docente'] );

	$_POST = array_map( 'mysql_real_escape_string', $_POST );

	// TODO: DOCENTI!!!

	// Salvo
	$query = <<<EOF
UPDATE `$config[db_prefix]corso`
SET
`id_facolta` = '$_POST[facolta]',
`nome` = '$_POST[nome]',
`intestazione` = '$_POST[intestaz]',
`orario` = '$_POST[orario]',
`ricevimento` = '$_POST[ricevimento]',
`obiettivi` = '$_POST[obiettivi]',
`programma` = '$_POST[programma]',
`esame` = '$_POST[esame]',
`materiali` = '$_POST[materiali]'
WHERE `id_corso` = '$id'
LIMIT 1
EOF;
	mysql_query( $query, $db);

	if ( ! mysql_errno() ) {
		echo "Modifiche salvate.";
	}
	else {
		echo "Errore nel salvataggio, riprovare.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
	}

endif;

?>
