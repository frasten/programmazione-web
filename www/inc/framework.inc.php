<?php

@ session_start();

require_once( 'config.inc.php' );

if ( $config['debug'] ) {
	// abilitiamo il display degli errori per debug.
	if ( ! ini_get( 'display_errors' ) ) {
		ini_set( 'display_errors', 1 );
	}
	error_reporting( E_ALL );
}
else {
	// Disabilitiamo l'output per gli errori, fonte di preziose informazioni
	// per gli attaccanti.
	error_reporting( 0 );
}

// Ci permette di usare header() anche dopo l'inizio della pagina
// Attenzione se si volessero stampare grosse quantita' di dati!!!
ob_start();

// Trailing slash
if ( substr( $config['upload_path'], -1 ) !== '/' ) $config['upload_path'] .= '/';


require_once( 'db.inc.php' );

require_once( 'funzioni.inc.php' );

require_once( 'remember-login.inc.php' );

// Periodicamente eliminiamo i cookies mantenuti per il "Ricorda accesso"
elimina_persistent_cookies_scaduti();

controlla_persistent_login();



?>
