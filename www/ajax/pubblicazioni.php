<?php

require_once( '../inc/json.inc.php' );
require_once( '../inc/db.inc.php' );

session_start();


// Protezione contro accessi non autorizzati
if ( empty( $_SESSION['loggato'] ) ) die( '-1' );

if ( empty( $_GET['action'] ) ) die( '-1' );

if ( $_GET['action'] == 'get_lista_autori' ) {
	$query = <<<EOF
SELECT `nome`
FROM `$config[db_prefix]pubautore`
ORDER BY `nome` ASC
EOF;
	$result = mysql_query( $query, $db );
	if ( ! $result ) die( -1 );
	$ret = array();
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$ret[] = $riga['nome'];
	}

	echo json_encode( $ret );
}


?>
