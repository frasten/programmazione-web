<h1>Pubblicazioni</h1>
<?php

$query = <<<EOF
SELECT * FROM
	`$config[db_prefix]pubblicazione`
	LEFT JOIN `$config[db_prefix]pubblicazione_pubautore`
		ON `$config[db_prefix]pubblicazione`.`id` = `$config[db_prefix]pubblicazione_pubautore`.`id_pubblicazione`
	LEFT JOIN `$config[db_prefix]pubautore`
		ON `$config[db_prefix]pubblicazione_pubautore`.`id_autore` = `$config[db_prefix]pubautore`.`id`
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
		// Titolo pubblicazione
		echo "<span class='evidenza'>";
		printf($riga['file'] ? "<a href='$riga[file]'>%s</a>" : "%s", htmlentities( $riga['titolo'] ) );
		echo "</span><br />";

// Array ( [id] => [categoria] => rivista [titolo] => Titolo bellissismo [anno] => 2010 [titolo_contesto] => Bel titolo [info] => aspokdapsodk apsodk apsodk [autori_libro] => [editore] => [isbn] => [citta] => bienno [nazione] => italia [file] => [id_pubblicazione] => [id_autore] => [nome] => )

		// Dati pubblicazione
		echo "<span class='rientro'>";

		echo "</span><br />";

		print_r($riga);
		echo "</li>\n";
	}
	echo "</ul>\n";
}

?>
