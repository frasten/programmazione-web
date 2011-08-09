<?php
// Includere questo file per garantire il solo accesso autenticato.

/* NB: per l'utilizzo di questo script, i magic_quotes DEVONO essere
 * disabilitati.
 * Vedere: http://www.php.net/manual/en/security.magicquotes.disabling.php
 */
require_once( 'hmac.inc.php' );
require_once( 'remember-login.inc.php' );


if ( empty( $db ) ) return 1;

// Se non sono loggato, mostro il form

@session_start();

// Se non ci sono ancora user e pass salvati, genero un nuovo utente.
check_default_user();

// Periodicamente eliminiamo i cookies mantenuti per il "Ricorda accesso"
elimina_persistent_cookies_scaduti();

if ( ! empty( $_SESSION['loggato'] ) ) {
	// Se sono gia' loggato:
	return 0;
}


// Se sto ricevendo i dati di login:
if ( ! empty( $_POST['username'] ) || ! empty( $_POST['password'] ) ) {
	$user = mysql_real_escape_string( $_POST['username'] );

	// PAP con hash function HMAC e salted passwords.

	/* HMAC e il salt rendono inutili attacchi di dizionario o rainbow
	 * tables, e utenti con la stessa password avranno un valore differente
	 * di hash salvato nel database.
	 * */

	$query = "SELECT `salt`, `password` FROM `$config[db_prefix]login` WHERE BINARY `username`='$user' LIMIT 1";
	$result = @mysql_query( $query );
	if ( $result && mysql_num_rows( $result ) > 0 ) {
		$row = mysql_fetch_assoc( $result );

		// H( password | salt )
		$hash_atteso = $row['password'];
		$hash_inserito = hmac_sha1( $config['hmac_psk'], "$_POST[password]$row[salt]" );
		if ( $hash_atteso == $hash_inserito ) {
			// LOGIN OK
			$_SESSION['loggato'] = true;
			$_SESSION['user'] = $_POST['username'];

			// In caso si voglia ricordare il login:
			if ( ! empty( $_POST['rememberlogin'] ) ) {
				crea_persistent_cookie( $_POST['username'] );
			}

			// FIXME: come mai non da errore? Ho gia' scritto in output delle cose,
			// dovrebbe darmi errore.
			header( "Location: $_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]" );

			return 0;
		}
		else {
			// Errore di autenticazione
			$err = true;
		}
	}
	else {
		// Errore di autenticazione
		$err = true;
	}

	unset( $user );
	unset( $pass );
}

if ( controlla_persistent_login() ) return 0;

// Sono arrivato qui, mostro il form.
?>
		<h2>Area riservata</h2>
		<?php
		if ( ! empty( $err ) ) echo "<p><strong>Errore di autenticazione.</strong></p>";
		?>

		<form id="loginform" action="<?php
		echo $_SERVER['PHP_SELF'];
		// htmlentities() per protezione da attacchi XSS
		echo '?' . htmlentities( "$_SERVER[QUERY_STRING]", ENT_NOQUOTES );
		// TODO: renderlo piu' carino con un po' di CSS
		?>" method="post" >
			<label for="username">Username:
				<input type="text" id="username" name="username" />
			</label>
			<label for="password">Password:
				<input type="password" id="password" name="password" />
			</label>
			<label for="rememberlogin">
				<input type="checkbox" id="rememberlogin" name="rememberlogin" />
				Ricorda l'accesso
			</label>
			<input type="submit" name="Entra" />
		</form>
<?php

esci();



function check_default_user() {
	global $db, $config;

	// Controlliamo se non esiste nessun utente nel DB
	$query = "SELECT 1 FROM `$config[db_prefix]login` LIMIT 1";
	$result = mysql_query( $query, $db );

	if ( ! $result ) return 1; // Errore
	if ( mysql_num_rows( $result ) == 0 ) {
		// Non esiste alcun utente, creiamolo.
		$user = 'admin';
		$pass = genera_random_string( $config['default_pass_len'] );
		$salt = sha1( uniqid( rand(), true ) );
		$salt = substr( $salt, 0, 20 ); // Solo i primi 20 caratteri

		$hash = hmac_sha1( $config['hmac_psk'], "$pass$salt" );

		$query = <<<EOF
INSERT INTO `$config[db_prefix]login`
(`username`, `salt`, `password`)
VALUES ('$user', '$salt', '$hash')
EOF;

		mysql_query( $query, $db );
		echo "<strong>Nuovo utente generato.</strong><br />";
		echo "Username: <strong>$user</strong><br />";
		echo "Password: <strong>$pass</strong><br />";

		echo "<strong>Importante: riporre questi dati in un luogo sicuro.</strong>";

		// Autentico automaticamente l'utente, per questa prima volta.
		$_SESSION['loggato'] = true;
		$_SESSION['user'] = $user;
	}
}

function esci() {
	echo str_repeat("</div>", 3); // #container, #centrale, #corpo
	echo "</body></html>";
	exit;
}

?>
