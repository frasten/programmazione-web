<h2>Pubblicazioni</h2>
<?php

$autori = load_autori();

stampa_pub( 'rivista' );
stampa_pub( 'libro' );
stampa_pub( 'conferenza' );
stampa_pub( 'monografia' );
stampa_pub( 'curatela' );



function load_autori() {
	$autori = array();

	global $config, $db;
	$query = <<<EOF
SELECT `$config[db_prefix]pubblicazione`.`id_pubblicazione`, `nome` FROM `$config[db_prefix]pubblicazione`
JOIN `$config[db_prefix]pubblicazione_pubautore`
ON `$config[db_prefix]pubblicazione`.`id_pubblicazione` = `$config[db_prefix]pubblicazione_pubautore`.`id_pubblicazione`
JOIN `$config[db_prefix]pubautore`
ON `$config[db_prefix]pubblicazione_pubautore`.`id_autore` = `$config[db_prefix]pubautore`.`id_autore`
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
	echo implode( ', ', $output );
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

	echo "<h3>{$titoli[$categoria]}</h3>\n";

	$query = <<<EOF
SELECT * FROM
	`$config[db_prefix]pubblicazione`
	LEFT JOIN `$config[db_prefix]pubblicazione_pubautore`
		ON `$config[db_prefix]pubblicazione`.`id_pubblicazione` = `$config[db_prefix]pubblicazione_pubautore`.`id_pubblicazione`
	LEFT JOIN `$config[db_prefix]pubautore`
		ON `$config[db_prefix]pubblicazione_pubautore`.`id_autore` = `$config[db_prefix]pubautore`.`id_autore`
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
		echo "<ul>\n";
		while ( $riga = mysql_fetch_assoc( $result ) ) {
			echo "<li>\n";
			call_user_func( "stampa_pub_$categoria", $riga );
			echo "</li>\n";
		}
		echo "</ul>\n";
	}


}


/* PUBBLICAZIONE SU RIVISTE */
function stampa_pub_rivista( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>";
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

	print_r($riga);
}

/* CAPITOLI DI LIBRO */
function stampa_pub_libro( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>";
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

	print_r($riga);
}

/* ATTI DI CONFERENZA */
function stampa_pub_conferenza( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>";
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

	print_r($riga);
}

/* MONOGRAFIE */
function stampa_pub_monografia( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>";
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

	print_r($riga);
}

/* CURATELE */
function stampa_pub_curatela( $riga ) {
	global $autori;
	// Titolo pubblicazione
	echo "<span class='evidenza'>";
	printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
	echo "</span><br />";


	// Dati pubblicazione
	echo "<span class='rientro'>";
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

	print_r($riga);
}

?>
