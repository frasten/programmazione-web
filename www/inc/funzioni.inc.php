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

function admin_menu( $links ) {
	if ( empty( $_SESSION['loggato'] ) ) return false;

	$links[] = array( 'logout.php', 'Esci', 'user_go.png');

	echo "<ul class='adminmenu'>\n";
	foreach ( $links as $l ) {
		list( $url, $testo, $icona ) = $l;
		echo "<li>\n";
		if ( ! empty( $icona ) )
			$style = " style='background-image: url(img/icone/$icona)'"; 
		printf( "<a href='%s' class='linkconicona'%s>%s</a>", htmlspecialchars( $url ), $style, htmlspecialchars( $testo ) );
		echo "</li>\n";
	}
	echo "</ul>\n";
}


?>