<?php

if ( sizeof( $_POST ) == 0 ) { // Mostro form per l'inserimento

	include 'corso-form.inc.php';

} else {
	// Salvo i dati nel database

	// Controlli di routine e pulizia
	$_POST['facolta'] = intval( $_POST['facolta'] );

	if ( isset( $_POST['docente'] ) ) {
		$docenti = array_map( 'intval', $_POST['docente'] );
		unset( $_POST['docente'] );
	}
	else $docenti = false;

	$_POST = array_map( 'mysql_real_escape_string', $_POST );

	// Salvo
	$query = <<<EOF
INSERT INTO `$config[db_prefix]corso`
(`id_facolta`,`nome`,`intestazione`,`orario`,
`ricevimento`,`obiettivi`,`programma`,
`esame`,`materiali`)
VALUES
('$_POST[facolta]','$_POST[nome]','$_POST[intestaz]','$_POST[orario]',
'$_POST[ricevimento]','$_POST[obiettivi]','$_POST[programma]',
'$_POST[esame]','$_POST[materiali]')
EOF;
	mysql_query( $query, $db );
	$id = mysql_insert_id( $db );
	if ( ! $id ) {
		echo "Errore nell'inserimento. Si prega di riprovare.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
		return;
	}

	// Salviamo i docenti
	if ( $docenti ) {
		$query = <<<EOF
INSERT INTO `$config[db_prefix]docente_corso`
(`id_docente`,`id_corso`)
VALUES 
EOF;
		$chunks = array();
		foreach ( $docenti as $doc ) {
			$chunks[] = "('$doc','$id')";
		}
		$query .= implode( ', ', $chunks );
		mysql_query( $query, $db );
	}

	header( "Location: corso.php?id=$id" );
	exit;
}

?>
