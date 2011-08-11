<?php

require_once( 'inc/header.inc.php' );

// La visualizzazione della lista delle pubblicazioni non deve essere
// protetta da password.
if ( ! empty( $_GET['action'] ) )
	require_once( 'inc/proteggi.inc.php' );



if ( empty( $_GET['action'] ) ) {
	// Mostro una lista di pubblicazioni
	admin_menu( array(
		array( '?action=new', 'Nuova pubblicazione', 'page_add.png' )
	) );
	include 'inc/pub-list.inc.php';
}
else if ( $_GET['action'] == 'new' ) {
	// Mostro il form per l'inserimento di una pubblicazione
	admin_menu( array(
		array( '?', 'Lista pubblicazioni', 'table.png' )
	) );
	include 'inc/pub-form-new.inc.php';
}
else if ( $_GET['action'] == 'savenew' ) {
	// Effettuo il salvataggio nel database di una pubblicazione
	include 'inc/pub-save.inc.php';
}




?>


<?php
require_once('inc/footer.inc.php');

?>
