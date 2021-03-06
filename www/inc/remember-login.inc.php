<?php

require_once( 'db.inc.php' );
require_once( 'hmac.inc.php' );

function controlla_persistent_login() {
	global $config, $db;
	// Se son gia' loggato non faccio niente
	if ( ! empty( $_SESSION['loggato'] ) ) return true;

	// Controlliamo se loggare l'utente tramite "Ricorda accesso"
	if ( ! empty( $_COOKIE[$config['persistent_cookie_name']] ) ) {
		list( $user, $token ) = explode( '|', $_COOKIE[$config['persistent_cookie_name']] );
		$myuser = mysql_real_escape_string( $user );
		$query = <<<EOF
SELECT *
FROM `$config[db_prefix]persistent_login`
WHERE BINARY `username` = '$myuser'
EOF;
		$result = mysql_query( $query, $db );
		// Cerco se esiste il token nel database

		$validhash = false;
		while ( $riga = mysql_fetch_assoc( $result ) ) {
			$hash = hmac_sha1( $config['hmac_psk'], "$token$riga[salt]" );
			if ( $hash == $riga['tokenhash'] ) {
				// Trovato, e' valido.
				$validhash = $hash;
				break;
			}
		}

		if ( $validhash ) {
			// Ok, login corretto
			$_SESSION['loggato'] = true;
			$_SESSION['user'] = $user;
			$_SESSION['persistent_hash'] = $validhash;

			// Invalido il cookie e il token precedenti
			invalida_persistent_cookie();
			// Genero un nuovo cookie
			crea_persistent_cookie( $user );

			return true;
		}
	}
	return false;
}

function elimina_persistent_cookies_scaduti() {
	global $config, $db;

	$query = <<<EOF
DELETE FROM `$config[db_prefix]persistent_login`
WHERE `timestamp` < NOW() - INTERVAL $config[persistent_cookies_timeout] SECOND
EOF;
	mysql_query( $query, $db );
}


function invalida_persistent_cookie() {
	global $config, $db;

	if ( ! empty( $_SESSION['persistent_hash'] ) ) {
		$user = mysql_real_escape_string( $_SESSION['user'] );
		$hash = mysql_real_escape_string( $_SESSION['persistent_hash'] );
		$query = <<<EOF
DELETE FROM `$config[db_prefix]persistent_login`
WHERE BINARY `username` = '$user' AND BINARY `tokenhash` = '$hash'
EOF;
		mysql_query( $query, $db );
	}

	// Elimino il cookie
	setcookie ( $config['persistent_cookie_name'], '', time() - 3600 * 24 * 30, '/' );
}

function crea_persistent_cookie( $user ) {
	global $config, $db;

	$token = genera_random_string( 30 );
	$salt = genera_random_string( 30 );

	// Salvo il cookie sull'utente
	$expire = time() + $config['persistent_cookies_timeout'];
	setcookie( $config['persistent_cookie_name'], "$user|$token", $expire, '/' );

	$hash = hmac_sha1( $config['hmac_psk'], "$token$salt" );
	$salt = mysql_real_escape_string( $salt );
	$user = mysql_real_escape_string( $user );
	$query = <<<EOF
INSERT INTO `$config[db_prefix]persistent_login`
(`username`,`salt`,`tokenhash`,`timestamp`)
VALUES
('$user', '$salt', '$hash', NOW())
EOF;
	mysql_query( $query, $db );
	$_SESSION['persistent_hash'] = $hash;
}



?>
