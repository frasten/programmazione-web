<?php

@ session_start();

// Ci permette di usare header() anche dopo l'inizio della pagina
// Attenzione se si volessero stampare grosse quantita' di dati!!!
ob_start();


// TEMP: abilitiamo il display degli errori per debug.
if ( ! ini_get( 'display_errors' ) ) {
	ini_set( 'display_errors', 1 );
}



require_once( 'config.inc.php' );
require_once( 'db.inc.php' );

require_once( 'funzioni.inc.php' );

require_once( 'remember-login.inc.php' );

// Periodicamente eliminiamo i cookies mantenuti per il "Ricorda accesso"
elimina_persistent_cookies_scaduti();

controlla_persistent_login();



?>
