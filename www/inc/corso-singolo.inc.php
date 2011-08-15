<?php

$id = intval( $_GET['id'] );

admin_menu( array(
	array( "?action=edit&id=$id", 'Modifica corso', 'table_edit.png' ),
	array( "?", 'Lista corsi', 'table.png' )
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
<h2><?php echo htmlspecialchars( $corso['nome'] ) /* TODO: anno scolastico */?></h2>
<span class='docente'>Docenti:
<?php
$query = <<<EOF
SELECT `nome`
FROM `$config[db_prefix]docente`
JOIN `$config[db_prefix]docente_corso`
	USING (`id_docente`)
WHERE `id_corso` = '$id'
EOF;
$result = mysql_query( $query, $db );
$stampa = array();
while( $doc = mysql_fetch_assoc( $result ) ) {
	$stampa[] = "Prof. $doc[nome]";
}
echo implode( ', ', $stampa );
unset( $stampa );

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
	while ( $news = mysql_fetch_assoc( $result ) ) {
		// TODO
		echo "";
		echo "$news[testo]";
		echo "";
	}
}

?>

<h3>Orario delle lezioni</h3>
<?php
// Valutare se val la pena creare un sistema strutturato per l'inserimento del calendario
echo $corso['orario'];
?>

<h3>Orario di ricevimento</h3>
<?php echo $corso['ricevimento'] ?>

<h3>Obiettivi del corso</h3>
<?php echo $corso['obiettivi'] ?>

<h3>Programma d'esame</h3>
<?php echo $corso['esame'] ?>

<h3>Materiale di riferimento</h3>
<?php echo $corso['materiali'] ?>

<h3>Materiale didattico</h3>
TODO
