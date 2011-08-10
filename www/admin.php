<?php

require_once( 'inc/header.inc.php' );

// Pagina protetta da password
require_once( 'inc/proteggi.inc.php' );


if ( empty( $_GET['action'] ) ) {
	
}
else if ( $_GET['action'] == 'listusers' ||
          $_GET['action'] == 'changepassword' ||
          $_GET['action'] == 'newuser' ) {
	require_once( 'inc/admin-user.inc.php' );
}



require_once('inc/footer.inc.php');

?>
