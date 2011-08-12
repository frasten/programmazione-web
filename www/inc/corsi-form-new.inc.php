<form>
	Corso:
	<label for=''>Nome corso:</label>
	<input type='text' />

	<label for=''>Facolt&agrave;:</label>
	<ul>
	<?php /* Elenco di facolta', con radio button */
	$query = <<<EOF
SELECT `id_facolta`, `nome`
FROM `$config[db_prefix]facolta`
ORDER BY `id_facolta` ASC
EOF;
	$result = mysql_query( $query, $db );

	$gia_segnato = false;
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		echo "<li>\n";

		// Mettiamo il pallino sul primo
		$checked = '';
		if ( ! $gia_segnato ) {
			$checked = 'checked="checked" ';
			$gia_segnato = true;
		}
		echo "<input type='radio' name='facolta' id='facolta_$riga[id_facolta]' $checked/>\n";

		printf( "<label for='facolta_$riga[id_facolta]' value='%d'>%s</label>\n",
			$riga['id_facolta'],
			htmlspecialchars( $riga['nome'] )
		);
		echo "</li>\n";
	}
	?>
	</ul>

	<label for=''>Docenti:</label>
	<ul>
	<?php /* Elenco dei docenti, con checkbox */ ?>
	</ul>

	<!-- Contenuti editabili liberamente -->
	<h3>Pagina corso:</h3>
	

</form>
