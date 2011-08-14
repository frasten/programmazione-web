<?php

require_once( 'inc/header.inc.php' );

// La visualizzazione della lista dei corsi non deve essere
// protetta da password.
if ( ! empty( $_GET['action'] ) )
	require_once( 'inc/proteggi.inc.php' );



if ( empty( $_GET['action'] ) ) {
	if ( empty( $_GET['id'] ) ) {
		// Mostro una lista dei corsi
		admin_menu( array(
			array( '?action=new', 'Nuovo corso', 'page_add.png' )
		) );
		include 'inc/corsi-lista.inc.php';
	}
	else {
		// Pagina di un corso
		include 'inc/corso-singolo.inc.php';
	}
}
else if ( $_GET['action'] == 'new' ) {
	// Mostro il form per l'inserimento di un nuovo corso
	admin_menu( array(
		array( '?', 'Lista corsi', 'table.png' )
	) );
	include 'inc/corso-form.inc.php';
}
else if ( $_GET['action'] == 'edit' ) {
	// Mostro il form per la modifica dei dati di un corso
	admin_menu( array(
		array( '?', 'Lista corsi', 'table.png' )
	) );
	include 'inc/corso-edit.inc.php';
}


require_once('inc/footer.inc.php');

?>
