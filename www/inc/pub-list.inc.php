		<h2>Pubblicazioni</h2>
<?php

if ( ! empty( $_SESSION['loggato'] ) )
	echo "<script type='text/javascript' src='js/pubblicazioni.js'></script>\n";


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
SELECT p.`id_pubblicazione`, a.`nome`
FROM `$config[db_prefix]pubblicazione` AS p
JOIN `$config[db_prefix]pubblicazione_pubautore`
	USING (`id_pubblicazione`)
JOIN `$config[db_prefix]pubautore` AS a
	USING (`id_autore`)
ORDER BY p.`id_pubblicazione` ASC, `ordine` ASC
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
		if ( strpos( $a, ' ' ) ) {
			list( $nome, $resto ) = explode( ' ', $a, 2 );
			$a = mb_substr( $nome, 0, 1, 'UTF-8' ) . ". $resto";
		}
		$output[] = $a;
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
		echo "<p>Non &egrave; presente alcuna pubblicazione.</p>";
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
			echo str_repeat( "\t", 6 ) . "<li id='pubblicazione_$riga[id_pubblicazione]'>\n";
			// Icona elimina
			if ( ! empty( $_SESSION['loggato'] ) ) {
				echo "<a href='javascript:void(0)' class='iconalink' onclick='askEliminaPubblicazione($riga[id_pubblicazione])' title='Elimina pubblicazione'>\n";
				echo "<img src='img/icone/newspaper_delete.png' alt='Elimina pubblicazione' />\n";
				echo "</a>\n ";
			}
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

	// Nome del journal
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

	echo "$riga[anno].";

	if ( ! empty( $riga['abstract'] ) )
		printf( "<br /><em>Abstract:</em> %s\n", htmlspecialchars( $riga['abstract'] ) );

	echo "</span>\n";
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

	if ( ! empty( $riga['isbn'] ) )
		printf( "ISBN/ISSN: %s, ", htmlspecialchars( $riga['isbn'] ) );

	echo "$riga[anno].";

	if ( ! empty( $riga['abstract'] ) )
		printf( "<br /><em>Abstract:</em> %s\n", htmlspecialchars( $riga['abstract'] ) );

	echo "</span>\n";
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

	if ( ! empty( $riga['abstract'] ) )
		printf( "<br /><em>Abstract:</em> %s\n", htmlspecialchars( $riga['abstract'] ) );

	echo "</span>\n";
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

	echo "n. of pages: $riga[num_pagine], ";

	echo "$riga[anno].";

	if ( ! empty( $riga['abstract'] ) )
		printf( "<br /><em>Abstract:</em> %s\n", htmlspecialchars( $riga['abstract'] ) );

	echo "</span>\n";
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

	if ( ! empty( $riga['abstract'] ) )
		printf( "<br /><em>Abstract:</em> %s\n", htmlspecialchars( $riga['abstract'] ) );

	echo "</span>\n";
}

?>
