<?php

if ( ! isset( $_POST['salva'] ) ) { // Mostro form per l'inserimento

	include 'corso-form.inc.php';

} else {
	// Salvo i dati nel database

	if ( empty( $_POST['nome'] ) ) {
		echo "Errore: il campo 'nome corso' &egrave; vuoto.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
		return;
	}

	// Controlli di routine e pulizia
	if ( isset( $_POST['facolta'] ) )
		$_POST['facolta'] = intval( $_POST['facolta'] );
	else
		$_POST['facolta'] = 1;


	$questanno = intval( date( 'Y' ) );
	if ( isset( $_POST['annoaccademico'] ) )
		$_POST['annoaccademico'] = intval( $_POST['annoaccademico'] );
	else
		$_POST['annoaccademico'] = $questanno;


	if ( $_POST['annoaccademico'] < $questanno - $config['anni_accademici_passati'] ||
	     $_POST['annoaccademico'] > $questanno + $config['anni_accademici_futuri'] ) {
		echo "Anno non valido.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
	}


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
`esame`,`materiali`,`annoaccademico`)
VALUES
('$_POST[facolta]','$_POST[nome]','$_POST[intestaz]','$_POST[orario]',
'$_POST[ricevimento]','$_POST[obiettivi]','$_POST[programma]',
'$_POST[esame]','$_POST[materiali]','$_POST[annoaccademico]')
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
(`id_docente`,`id_corso`,`esercitatore`)
VALUES 
EOF;
		$chunks = array();
		foreach ( $docenti as $doc ) {
			$es = 0;
			if ( isset( $_POST["tipodocente_$doc"] ) )
				$es = intval( $_POST["tipodocente_$doc"] );
			$chunks[] = "('$doc','$id','$es')";
		}
		$query .= implode( ', ', $chunks );
		mysql_query( $query, $db );
	}

	header( "Location: corso.php?id=$id" );
	exit;
}

?>
