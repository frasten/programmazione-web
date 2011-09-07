<?php

function genera_random_string( $len ) {
	global $config;
	mt_srand( ( double ) microtime() * 1000000 );

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


function admin_menu( $links = array( array( '', '', '' ) ) ) {
	if ( empty( $_SESSION['loggato'] ) ) return false;

	echo "<ul class='adminmenu'>\n";
	foreach ( $links as $l ) {
		list( $url, $testo, $icona ) = $l;
		stampa_admin_link( $url, $testo, $icona );
	}

	// Icone fisse
	$user = htmlspecialchars( $_SESSION['user'], ENT_NOQUOTES, 'UTF-8' );
	$right[] = array( 'admin.php', $user, 'user_suit.png', "Accedi all'area amministrativa" );
	$right[] = array( 'logout.php', 'Esci', 'user_go.png' );
	echo "<li><ul>\n";
	foreach ( $right as $l ) {
		@list( $url, $testo, $icona, $hint ) = $l;
		stampa_admin_link( $url, $testo, $icona, $hint );
	}
	echo "</ul></li>\n";

	echo "</ul>\n";
}


function stampa_admin_link( $url, $testo, $icona, $hint = '' ) {
	echo "<li>\n";
	if ( ! empty( $url ) ) {
		$style = '';
		if ( ! empty( $icona ) )
			$style = " style='background-image: url(img/icone/$icona)'";

		$title = '';
		if ( ! empty( $hint ) )
			$title = sprintf( " title='%s'", htmlspecialchars( $hint, ENT_QUOTES, 'UTF-8' ) );

		printf( "<a href='%s' class='linkconicona'%s%s>%s</a>",
			htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' ), $style, $title,
			htmlspecialchars( $testo, ENT_NOQUOTES, 'UTF-8' ) );
	}
	else {
		echo "&nbsp;";
	}
	echo "</li>\n";
}


if ( ! function_exists( 'mb_substr' ) ):
/**
 * mb_substr()
 * Funzione alternativa in caso in case mb_string() non sia disponibile
 */
function mb_substr( $str, $start, $length = null ) {
	global $locale_char_set;

	if ( ! $locale_char_set ) {
		$locale_char_set = 'utf-8';
	}
	if ( $locale_char_set == 'utf-8' ) {
		return ( $length === null ) ?
			utf8_encode( substr( utf8_decode( $str ), $start ) ) :
			utf8_encode( substr( utf8_decode( $str ), $start, $length ) );
	} else {
	return ( $length === null ) ?
		substr( $str, $start ) :
		substr( $str, $start, $length );
	}
}
endif;


function get_img_tipofile( $file ) {
	preg_match( "/([^.]+)$/", $file, $match );
	$estensione = $match[1];

	switch ( strtolower( $estensione ) ) {
		case 'pdf':
			$img = 'icon_pdf.png';
			break;
		case 'zip':
		case 'rar':
		case 'bz2':
		case 'gz':
			$img = 'icon_zip.png';
			break;
		case 'doc':
		case 'odt':
		case 'docx':
			$img = 'icon_doc.png';
			break;
		default:
			$img = 'file.png';
			break;
	}
	return $img;
}

?>
