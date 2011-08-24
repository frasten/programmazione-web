<?php

$id = intval( $_GET['id'] );

admin_menu( array(
	array( "?action=edit&id=$id", 'Modifica corso', 'corso_edit.png' ),
	array( "?", 'Lista corsi', 'corso.png' )
) );

$query = <<<EOF
SELECT *
FROM `$config[db_prefix]corso`
WHERE `id_corso` = '$id'
LIMIT 1
EOF;
$result = mysql_query( $query, $db );
if ( ! $result || ! mysql_num_rows( $result ) ) {
	echo 'ID non valido.';
	return 1;
}

$corso = mysql_fetch_assoc( $result );
?>
<h2><?php echo htmlspecialchars( $corso['nome'] );
echo " - a.a. $corso[annoaccademico]/" . ( $corso['annoaccademico'] + 1 ) ?></h2>
<span class='docente'>
<?php
$query = <<<EOF
SELECT `nome`, `esercitatore`
FROM `$config[db_prefix]docente`
JOIN `$config[db_prefix]docente_corso`
	USING (`id_docente`)
WHERE `id_corso` = '$id'
EOF;
$result = mysql_query( $query, $db );
$esercitatori = array();
$docenti = array();
while( $riga = mysql_fetch_assoc( $result ) ) {
	if ( ! $riga['esercitatore'] )
		$docenti[] = "Prof. $riga[nome]";
	else
		$esercitatori[] = "$riga[nome]";
}
printf( "Docent%s: ", sizeof( $docenti ) == 1 ? 'e': 'i');
echo implode( ', ', $docenti );
unset( $docenti );
echo "<br />\n";

if ( sizeof( $esercitatori ) ) {
	echo "Esercitazioni: ";
	echo implode( ', ', $esercitatori );
}
unset( $esercitatori );

?></span>

<span class='lauree'><?php echo $corso['intestazione'] ?></span>

<h3>News</h3>
<?php
$query = <<<EOF
SELECT `testo`, `file`
FROM `$config[db_prefix]news`
WHERE `id_corso` = '$id' AND `nascondi` = '0'
ORDER BY `ordine` ASC
EOF;
$result = mysql_query( $query, $db );
if ( ! mysql_num_rows( $result ) ) {
	echo "Non ci sono avvisi per il momento.";
}
else {
	echo "<ul id='lista-news'>\n";
	while ( $news = mysql_fetch_assoc( $result ) ) {
		echo "<li class='avviso'>\n";
		if ( $news['file'] ) {
			$file = basename( $news['file'] );
			$img = get_img_tipofile( $file );
			echo "<span class='scaricafile'>";
			printf( "<a href='%s' class='iconalink' title='Scarica il file'>", htmlspecialchars( $news['file'], ENT_QUOTES ) );
			echo "<img src='img/icone/$img' alt='Scarica il file' />";
			echo "</a>";
			echo "</span>\n";
		}
		echo $news['testo'];
		echo "</li>\n";
	}
	echo "</ul>\n";
}

?>

<?php if ( trim( strip_tags( $corso['orario'] ) ) ): ?>
<h3>Orario delle lezioni</h3>
<?php
// Valutare se val la pena creare un sistema strutturato per l'inserimento del calendario
echo $corso['orario'];
?>
<?php endif; ?>


<?php if ( trim( strip_tags( $corso['ricevimento'] ) ) ): ?>
<h3>Orario di ricevimento</h3>
<?php echo $corso['ricevimento'] ?>
<?php endif; ?>


<?php if ( trim( strip_tags( $corso['obiettivi'] ) ) ): ?>
<h3>Obiettivi del corso</h3>
<?php echo $corso['obiettivi'] ?>
<?php endif; ?>


<?php if ( trim( strip_tags( $corso['programma'] ) ) ): ?>
<h3>Programma d'esame</h3>
<?php echo $corso['programma'] ?>
<?php endif; ?>


<?php if ( trim( strip_tags( $corso['esame'] ) ) ): ?>
<h3>Modalit&agrave; d'esame</h3>
<?php echo $corso['esame'] ?>
<?php endif; ?>


<?php if ( trim( strip_tags( $corso['materiali'] ) ) ): ?>
<h3>Materiale di riferimento</h3>
<?php echo $corso['materiali'] ?>
<?php endif; ?>


<?php
	$query = <<<EOF
SELECT
	s.`id_sezione`,
	s.`note`,
	s.`titolo` AS titolo_sez,
	f.`titolo` AS titolo_file,
	`aggiornato`,
	f.`url`
FROM `$config[db_prefix]sezione` AS s
JOIN `$config[db_prefix]file_materiale` AS f
	USING (`id_sezione`)
WHERE `id_corso` = '$id' AND `nascondi` = '0'
ORDER BY `id_sezione` ASC, `ordine` ASC
EOF;
	$result = mysql_query( $query, $db );

	if ( mysql_num_rows( $result ) ):
?>
<h3>Materiale didattico</h3>
(<?php updated_icon() ?> = aggiornati per l'anno accademico in corso)
<?php
	$oldsez = false;
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		if ( $oldsez != $riga['id_sezione'] ) {
			if ( $oldsez !== false ) echo "</ul>\n";
			printf( "<h4>%s</h4>\n", htmlspecialchars( $riga['titolo_sez'] ) );
			if ( trim( strip_tags( $riga['note'] ) ) )
				echo $riga['note'];

			echo "<ul class='listafiles'>\n";
			$oldsez = $riga['id_sezione'];
		}
		echo "<li>\n";

		$url = $riga['url'];
		if ( ! preg_match( "#^(?:http|https|ftp?)://#i", $url ) ) {
			// Url relativo, caricato attraverso form
			$url = basename( $url ); // per sicurezza eliminiamo sottodirectory
			$url = "http://$_SERVER[SERVER_NAME]" . dirname( $_SERVER['SCRIPT_NAME'] ) . "/$config[upload_path]$url";
		}

		$file = basename( $url );
		$img = get_img_tipofile( $file );

		printf( "<a href='%s' class='iconalink' title='Scarica il file'><img src='img/icone/%s' alt='Scarica il file' /></a>\n",
			htmlspecialchars( $url, ENT_QUOTES ),
			$img );
		echo htmlspecialchars( $riga['titolo_file'] ) . "\n";
		if ( $riga['aggiornato'] )
			updated_icon();
		echo "</li>\n";
	}
	echo "</ul>\n";
endif;



function updated_icon() {
	echo "<img src='img/icone/update.gif' alt='***Updated!***' />\n";
}

?>
