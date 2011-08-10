<?php

require_once( 'db.inc.php' );
require_once( 'hmac.inc.php' );

function verifica_login( $user, $pass ) {
	global $config, $db;

	$user = mysql_real_escape_string( $user );

	// PAP con hash function HMAC e salted passwords.

	/* HMAC e il salt rendono inutili attacchi di dizionario o rainbow
	 * tables, e utenti con la stessa password avranno un valore differente
	 * di hash salvato nel database.
	 * */

	$query = "SELECT `salt`, `password` FROM `$config[db_prefix]login` WHERE BINARY `username` = '$user' LIMIT 1";
	$result = @mysql_query( $query );
	if ( $result && mysql_num_rows( $result ) > 0 ) {
		$row = mysql_fetch_assoc( $result );

		// H( password | salt )
		$hash_atteso = $row['password'];
		$hash_inserito = hmac_sha1( $config['hmac_psk'], "$pass$row[salt]" );
		if ( $hash_atteso == $hash_inserito ) {
			return true;
		}
		else return false;
	}
	else return false;
}

?>
