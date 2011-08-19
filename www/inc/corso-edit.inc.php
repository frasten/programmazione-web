<?php

if ( empty( $_GET['id'] ) ) return;

$id = intval( $_GET['id'] );

if ( ! isset( $_POST['salva'] ) ): // Mostro form per la modifica

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

	if ( isset( $_POST['docente'] ) ) {
		$docenti = array_map( 'intval', $_POST['docente'] );
		unset( $_POST['docente'] );
	}
	else $docenti = false;

	$_POST = array_map( 'mysql_real_escape_string', $_POST );

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

	if ( mysql_errno() ) {
		echo "Errore nel salvataggio, riprovare.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
		return;
	}

	// Per salvare la lista di docenti associati, la via piu' breve e' quella
	// di eliminare tutti i docenti associati e di aggiungere le associazioni
	// da zero.
	$query = <<<EOF
DELETE FROM `$config[db_prefix]docente_corso`
WHERE `id_corso` = '$id'
EOF;
	mysql_query( $query, $db );


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

	if ( mysql_errno() ) {
		echo "Errore nel salvataggio, riprovare.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
		return;
	}

	header( "Location: corso.php?id=$id" );
	exit;

endif;

?>
