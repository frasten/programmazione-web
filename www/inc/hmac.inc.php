<?php

function hmac_md5($key, $message) {
	$blocksize = 64; // 64 per MD5/SHA-1
	if (strlen($key) > $blocksize) {
		// TODO:
		// Non credo vada fatto cosi', quindi evitiamo chiavi + lunghe del block
		$key = md5($key, true);
	}
	if (strlen($key) < $blocksize) {
		$key = str_pad($key, $blocksize, chr(0x00)); // Padding iniziale
	}

	$o_key_pad = str_repeat(chr(0x5c), $blocksize) ^ $key;
	$i_key_pad = str_repeat(chr(0x36), $blocksize) ^ $key;
	return md5($o_key_pad . md5($i_key_pad . $message, true));
}


function hmac_sha1($key, $message) {
	$blocksize = 64; // 64 per MD5/SHA-1
	if (strlen($key) > $blocksize) {
		// TODO:
		// Non credo vada fatto cosi', quindi evitiamo chiavi + lunghe del block
		$key = sha1($key, true);
	}
	if (strlen($key) < $blocksize) {
		$key = str_pad($key, $blocksize, chr(0x00)); // Padding iniziale
	}

	$o_key_pad = str_repeat(chr(0x5c), $blocksize) ^ $key;
	$i_key_pad = str_repeat(chr(0x36), $blocksize) ^ $key;
	return sha1($o_key_pad . sha1($i_key_pad . $message, true));
}


?>
