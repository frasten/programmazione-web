<?php

function genera_random_string( $len ) {
	global $config;
	mt_srand( (double) microtime() * 1000000 );

	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789,.-;:_()+^=/!%';

	$str = '';
	for ( $i = 0; $i < $len; $i++ ) {
		$rnd = mt_rand( 0, strlen( $chars ) - 1 );
		$c = $chars{$rnd};
		// Ne randomizzo anche maiuscola/minuscola
		if ( mt_rand( 0, 1 ) ) $c = strtoupper( $c );
		$str .= $c;
	}
	return $str;
}

?>
