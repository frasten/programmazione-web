<?php

require_once( 'inc/framework.inc.php' );

if ( ! empty( $_SESSION['persistent_hash'] ) ) {
	// Se sto salvando login con "ricorda accesso", lo invalido
	invalida_persistent_cookie();
}


session_destroy();
// Riporto l'utente nella pagina dove si trovava prima.
$url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : 'index.php';
header( "Location: $url" );
exit;

?>
