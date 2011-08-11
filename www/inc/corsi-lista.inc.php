<?php

$query = <<<EOF
SELECT
	f.nome AS nome_facolta,
	`id_corso`,
	c.nome AS nome_corso
FROM `$config[db_prefix]corso` AS c
JOIN `$config[db_prefix]facolta` AS f
	USING (`id_facolta`)
ORDER BY `id_facolta` ASC, nome_corso ASC
EOF;

$result = mysql_query( $query, $db );
if ( ! mysql_num_rows( $result ) ) {
	echo "Nessun corso presente.";
	return;
}

$old_facolta = false;
while ( $riga = mysql_fetch_assoc( $result ) ) {
	if ( $riga['nome_facolta'] != $old_facolta ) {
		printf( "<h2>%s</h2>", htmlspecialchars( $riga['nome_facolta'] ) );
	}
}



?>
