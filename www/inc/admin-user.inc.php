<?php

require_once( 'funzioni-login.inc.php' );

if ( $_GET['action'] == 'listusers' ) {
	admin_menu( array(
		array( '?action=newuser', 'Nuovo utente', 'user_add.png' )
	) );

	$query = <<<EOF
SELECT *
FROM `$config[db_prefix]login`
ORDER BY `username` ASC
EOF;

	$result = mysql_query( $query, $db );
	if ( ! $result ) {
		echo "Errore interno.";
		return 1;
	}

	echo "<ul class='iconlist' style='margin-bottom: 20px;'>\n";
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		echo "<li>\n";
		printf( "<strong>%s</strong> ", htmlspecialchars( $riga['username'] ) );

		echo "<a href='$_SERVER[PHP_SELF]?action=changepassword&user=" . urlencode( $riga['username'] );
		echo "' class='iconalink' title='Cambia password'>";
		echo "<img src='img/icone/key.png' alt='Cambia password' />";
		echo "</a> ";

		if ( $riga['username'] != $_SESSION['user'] ) {
			// Non posso eliminare l'utente corrente.
			echo "<a href='$_SERVER[PHP_SELF]?action=deleteuser&user=" . urlencode( $riga['username'] );
			echo "' class='iconalink' title='Elimina'>";
			echo "<img src='img/icone/user_delete.png' alt='Elimina' />";
			echo "</a> ";
		}

		echo "</li>\n";
	}
	echo "</ul>\n";

}
else if ( $_GET['action'] == 'changepassword' ) {
	admin_menu( array(
		array( '?action=listusers', 'Gestione utenti', 'user_edit.png' )
	) );

	// Prima di tutto controllo che i dati siano validi e non ci sia un
	// attacco
	$query = "SELECT `salt` FROM `$config[db_prefix]login` WHERE username='" .
		mysql_real_escape_string( $_GET['user'] ) . "' LIMIT 1";
	$result = mysql_query( $query, $db );
	if ( ! $result || ! mysql_num_rows( $result ) ) {
		echo "Errore.";
		return 1;
	}

	if ( empty( $_POST['password'] ) ) {
		// Non l'ha ancora inviata, mostro un form per il cambiamento
		stampa_form_change_password();
	}
	else {
		// Richiede di salvarla, verifichiamo se e' tutto ok.
		if ( ! verifica_login( $_GET['user'], $_POST['oldpassword'] ) ) {
			echo 'Errore: password errata.';
		}
		else if ( $_POST['password'] != $_POST['repeatpassword'] ) {
			echo 'Errore: le password inserite non coincidono.';
		}
		else if ( strlen( $_POST['password'] ) < $config['min_pass_len'] ) {
			echo 'Errore: la nuova password &egrave; troppo corta.';
		}
		else {
			// Ok, possiamo cambiare la password.
			$riga = mysql_fetch_assoc( $result );
			$hash = hmac_sha1( $config['hmac_psk'], "$_POST[password]$riga[salt]" );

			$query = "UPDATE `$config[db_prefix]login` " .
				"SET `password` = '$hash' " .
				"WHERE BINARY `username` = '" . mysql_real_escape_string( $_GET['user'] ) . "' LIMIT 1";
			mysql_query( $query, $db );

			/* Se la password e' stata modificata, per sicurezza elimino tutti
			 * i permanent login (presenti su differenti computer) di questo
			 * utente */
			elimina_tutti_persistent_login( $_GET['user'] );

			echo "Password modificata.";
			echo "<br /><a href='?action=listusers'>Torna</a>";
			return 0;
		}
		stampa_form_change_password();
	}
}
else if ( $_GET['action'] == 'newuser' ) {
	admin_menu( array(
		array( '?action=listusers', 'Gestione utenti', 'user_edit.png' )
	) );

	if ( empty( $_POST['username'] ) ) {
		// Form
		includi_scripts_validazione();
		?>
		<form action='<?php
		echo htmlspecialchars( "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]", ENT_QUOTES );
		?>' method='post' id='frm-new-user'>
			<ul class='form_list'>
				<li>
					<label for='username'>Username:</label>
					<input type='text' id='username' name='username' />
				</li>
				<li>
					<label for='password'>Password:</label>
					<input type='password' id='password' name='password' />
				</li>
				<li>
					<label for='repeatpassword'>Ripeti password:</label>
					<input type='password' id='repeatpassword' name='repeatpassword' />
				</li>
			</ul>
			<input type='submit' class='submitbutton' value='Crea utente' />
		</form>
		<?php
	}
	else {
		// Richiesta di salvataggio
		$_POST['username'] = trim( $_POST['username'] );

		// Controlliamo se l'utente esiste gia'
		$query = "SELECT 1 FROM `$config[db_prefix]login` WHERE username='" .
			mysql_real_escape_string( $_POST['username'] ) . "' LIMIT 1";
		$result = mysql_query( $query, $db );

		if ( empty( $_POST['password'] ) ) {
			echo 'Errore: la password non pu&ograve; essere vuota.';
		}
		else if ( $_POST['password'] != $_POST['repeatpassword'] ) {
			echo 'Errore: le password inserite non coincidono.';
		}
		else if ( ! preg_match( '#^[a-z0-9^-_.,:@+-=()/!]+$#i', $_POST['username'] ) ) {
			echo 'Errore: il nome utente contiene caratteri non consentiti.';
		}
		else if ( mysql_num_rows( $result ) ) {
			echo 'Errore: il nome utente inserito non &egrave; disponibile.';
		}
		else {
			// Ok, salvo.
			$_POST['username'] = mysql_real_escape_string( $_POST['username'] );
			// Genero un salt
			$salt = sha1( uniqid( rand(), true ) );
			$salt = substr( $salt, 0, 20 ); // Solo i primi 20 caratteri

			// Calcolo l'hash
			$hash = hmac_sha1( $config['hmac_psk'], "$_POST[password]$salt" );

			$query = <<<EOF
INSERT INTO `$config[db_prefix]login`
(`username`,`salt`,`password`)
VALUES
('$_POST[username]','$salt','$hash')
EOF;
			mysql_query( $query, $db );
			echo "Utente salvato.<br /><a href='?action=listusers'>Torna</a>";
			return 0;
		}
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
	}
}
else if ( $_GET['action'] == 'deleteuser' ) {
	admin_menu( array(
		array( '?action=listusers', 'Gestione utenti', 'user_edit.png' )
	) );


	$query = "SELECT 1 FROM `$config[db_prefix]login` WHERE username='" .
		mysql_real_escape_string( $_GET['user'] ) . "' LIMIT 1";
	$result = mysql_query( $query, $db );
	if ( ! mysql_num_rows( $result ) ) {
		// Possibile attacco.
		echo "Errore.";
		return 1;
	}

	// Non posso eliminare l'utente corrente.
	if ( $_GET['user'] == $_SESSION['user'] ) {
		echo "Non &egrave; possibile eliminare l'utente corrente.";
		echo "<br /><a href='javascript:history.back()'>Torna</a>";
		return 1;
	}

	if ( empty( $_GET['conferma'] ) ) {
		// Chiediamo conferma
		echo "<p>Si &egrave; <strong>davvero</strong> sicuri di voler eliminare";
		echo " l'utente <em>" . htmlspecialchars( $_GET['user'] ) . "</em>?</p>";
		echo "<a href='?action=listusers'>Annulla</a>";
		echo " <a href='?$_SERVER[QUERY_STRING]&conferma=1' class='btn_pericolo'>Elimina</a>";
	}
	else {
		// Confermato, eliminazione vera e propria.
		$query = "DELETE FROM `$config[db_prefix]login` WHERE username='" .
			mysql_real_escape_string( $_GET['user'] ) . "' LIMIT 1";
		$result = mysql_query( $query, $db );

		elimina_tutti_persistent_login( $_GET['user'] );

		echo "Utente eliminato.";
		echo "<br /><a href='?action=listusers'>Torna</a>";
	}
}

function stampa_form_change_password() {
	includi_scripts_validazione();
			?>
		<p>
			<strong>Cambio password per l'utente <em><?php echo htmlspecialchars( $_GET['user'] ) ?></em></strong>
		</p>
		<form id='frm-edit-password' action='<?php
			echo htmlspecialchars( "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]", ENT_QUOTES );
		?>' method='post'>
			<ul class='form_list'>
				<li>
					<label for='oldpassword'>Vecchia password:</label>
					<input type='password' name='oldpassword' id='oldpassword' />
				</li>
				<li>
					<label for='password'>Nuova password:</label>
					<input type='password' name='password' id='password' />
				</li>
				<li>
					<label for='repeatpassword'>Ripeti password:</label>
					<input type='password' name='repeatpassword' id='repeatpassword' />
				</li>
			</ul>
			<input type='submit' class='submitbutton' value='Salva' style='margin-left: 100px' />
		</form>
		<script type='text/javascript'>
		</script>
		<?php
}

function elimina_tutti_persistent_login( $user ) {
	global $config, $db;
	$user = mysql_real_escape_string( $user );
	$query = <<<EOF
DELETE FROM `$config[db_prefix]persistent_login`
WHERE BINARY `username` = '$user'
EOF;
	mysql_query( $query, $db );
}

function includi_scripts_validazione() {
	echo <<<EOF
	<script type='text/javascript' src='js/admin-users.js'></script>
EOF;
}
?>
