		<h2>Pubblicazioni</h2>
<?php

$autori = load_autori();

stampa_pub( 'rivista' );
stampa_pub( 'libro' );
stampa_pub( 'conferenza' );
stampa_pub( 'monografia' );
stampa_pub( 'curatela' );



function load_autori() {
	global $config, $db;

	$autori = array();

	$query = <<<EOF
SELECT `$config[db_prefix]pubblicazione`.`id_pubblicazione`, `nome`
FROM `$config[db_prefix]pubblicazione`
JOIN `$config[db_prefix]pubblicazione_pubautore`
	USING (`id_pubblicazione`)
JOIN `$config[db_prefix]pubautore`
	USING (`id_autore`)
EOF;
	$result = mysql_query( $query, $db );
	if ( ! $result ) return $autori;

	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$autori[$riga['id_pubblicazione']][] = $riga['nome'];
	}
	return $autori;
}


function stampa_autori( $autori, $id ) {
	if ( empty( $autori[$id] ) ) return;

	$output = array();
	foreach ( $autori[$id] as $a ) {
		list( $nome, $resto ) = explode( ' ', $a, 2 );
		$output[] = $nome{0} . ". $resto";
	}
	if ( sizeof( $output ) == 1 )
		echo $output[0];
	else {
		echo implode( ', ', array_slice( $output, 0, sizeof( $output ) - 1 ) );
		echo " and " . $output[sizeof( $output ) - 1];
	}
}

function stampa_pub( $categoria ) {
	global $config, $db;

	$titoli = array(
		'rivista'    => 'Pubblicazioni su riviste internazionali',
		'libro'      => 'Capitoli in libro',
		'conferenza' => 'Pubblicazioni in atti di conferenza',
		'monografia' => 'Monografie',
		'curatela'   => 'Curatele'
	);

	echo "\t\t\t<h3>{$titoli[$categoria]}</h3>\n";

	$query = <<<EOF
SELECT *
FROM `$config[db_prefix]pubblicazione`
WHERE `categoria` = '$categoria'
ORDER BY `$config[db_prefix]pubblicazione`.`anno` DESC
EOF;
	$result = mysql_query( $query, $db );
	if ( ! $result ) {
		echo "Errore interno.";
		return 1;
	}

	if ( mysql_num_rows( $result ) == 0 ) {
		echo "Non &egrave; presente alcuna pubblicazione.";
	}
	else {
		$old_anno = -1;
		while ( $riga = mysql_fetch_assoc( $result ) ) {
			// Raggruppo le pubblicazioni per anno
			if ( $old_anno != $riga['anno'] ) {
				if ( $old_anno != -1 ) // se non sono all'inizio della lista di pubblicazioni
					echo str_repeat( "\t", 5 ) . "</ul>\n"; // devo chiudere il vecchio elenco
				printf( "%s<h4>%s</h4>\n", str_repeat( "\t", 4), intval( $riga['anno'] ) );
				echo str_repeat( "\t", 5 ) . "<ul>\n";
			}
			echo str_repeat( "\t", 6 ) . "<li>\n";
			call_user_func( "stampa_pub_$categoria", $riga );
			echo str_repeat( "\t", 6 ) . "</li>\n";
			$old_anno = $riga['anno'];
		}
		echo str_repeat( "\t", 5 ) . "</ul>\n";
	}


}


/* PUBBLICAZIONE SU RIVISTE */
function stampa_pub_rivista( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>\n";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlspecialchars( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>\n";
	stampa_autori( $autori, $riga['id_pubblicazione'] );
	echo ", " . "";

	if ( $riga['titolo_contesto'] ) {
		echo htmlspecialchars( $riga['titolo_contesto'] );
		echo ", ";
	}

	/* 13(1-2):3-31 */
	echo intval( $riga['volume'] );
	if ( $riga['numero'] ) {
		echo "($riga[numero])";
	}

	if ( $riga['pag_inizio'] && $riga['pag_fine'] ) {
		echo ":$riga[pag_inizio]-$riga[pag_fine]";
	}
	echo ", ";

	// TODO: Nome del journal

	echo "$riga[anno].";

	echo "</span><br />";
}

/* CAPITOLI DI LIBRO */
function stampa_pub_libro( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>\n";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlspecialchars( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>\n";
	stampa_autori( $autori, $riga['id_pubblicazione'] );
	echo ", " . "";

	if ( $riga['titolo_contesto'] ) {
		echo htmlspecialchars( $riga['titolo_contesto'] );
		echo ", ";
	}

	if ( $riga['curatori_libro'] )
		printf( "(ed. %s), ", htmlspecialchars( $riga['curatori_libro'] ) );

	echo htmlspecialchars( $riga['editore'] ) . ", ";

	if ( $riga['pag_inizio'] && $riga['pag_fine'] ) {
		echo "pages $riga[pag_inizio]-$riga[pag_fine], ";
	}

	echo "$riga[anno].";

	echo "</span><br />";
}

/* ATTI DI CONFERENZA */
function stampa_pub_conferenza( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>\n";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlspecialchars( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>\n";
	stampa_autori( $autori, $riga['id_pubblicazione'] );
	echo ", " . "";

	if ( $riga['titolo_contesto'] ) {
		echo "Proc. of " . htmlspecialchars( $riga['titolo_contesto'] );
		echo ", ";
	}

	if ( $riga['pag_inizio'] && $riga['pag_fine'] ) {
		echo "pages $riga[pag_inizio]-$riga[pag_fine], ";
	}

	echo "$riga[anno].";

	echo "</span><br />";
}

/* MONOGRAFIE */
function stampa_pub_monografia( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>\n";
	echo htmlspecialchars( $riga['titolo'] );
	echo "</span><br />\n";


	// Dati pubblicazione
	echo "<span class='rientro'>\n";
	stampa_autori( $autori, $riga['id_pubblicazione'] );
	echo ", " . "";

	echo htmlspecialchars( $riga['editore'] ) . ", ";

	echo "n. pagine: $riga[num_pagine], ";

	echo "$riga[anno].";

	echo "</span><br />";
}

/* CURATELE */
function stampa_pub_curatela( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>\n";
	echo htmlspecialchars( $riga['titolo'] );
	echo "</span><br />\n";


	// Dati pubblicazione
	echo "<span class='rientro'>\n";
	stampa_autori( $autori, $riga['id_pubblicazione'] );
	echo ", " . "";

	if ( $riga['editore'] ) {
		echo htmlspecialchars( $riga['editore'] ) . ", ";
	}

	echo "$riga[anno].";

	echo "</span><br />";
}

?>
