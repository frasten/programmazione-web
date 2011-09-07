<?php

require_once( 'inc/header.inc.php' );

// Pagina protetta da password
require_once( 'inc/proteggi.inc.php' );


if ( empty( $_GET['action'] ) ) {
	admin_menu();


?>
	<ul class='iconlist'>
		<li>
			<a href='?action=listusers' class='linkconicona' style='background-image: url(img/icone/user.png)'>
				Gestione utenti
			</a>
		</li>
		<li>
			<a href='pubblicazioni.php' class='linkconicona' style='background-image: url(img/icone/page.png)'>
				Gestione pubblicazioni
			</a>
		</li>
		<li>
			<a href='corso.php' class='linkconicona' style='background-image: url(img/icone/corso.png)'>
				Gestione corsi
			</a>
		</li>
	</ul>
<?php
}
else if ( $_GET['action'] == 'listusers' ||
          $_GET['action'] == 'changepassword' ||
          $_GET['action'] == 'newuser' ||
          $_GET['action'] == 'deleteuser' ) {
	require_once( 'inc/admin-user.inc.php' );
}



require_once( 'inc/footer.inc.php' );

?>
