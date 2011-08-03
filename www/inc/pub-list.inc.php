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
		print_r($riga);
		echo "</li>\n";
	}
	echo "</ul>\n";
}

?>
