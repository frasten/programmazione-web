<form>
	<h3>Corso:</h3>
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

	<!-- TinyMCE -->
	<script type="text/javascript" src="js/tiny_mce/jquery.tinymce.js"></script>
	<script type="text/javascript" src="js/corsi.js"></script>

	<label for='intestaz'>Intestazione:</label>
	<textarea class='tinymce' cols='80' rows='6' name='intestaz' id='intestaz'></textarea>

	<label for='orario'>Orario lezione:</label>
	<textarea class='tinymce' cols='80' rows='6' name='orario' id='orario'></textarea>

	<label for='ricevimento'>Orario di ricevimento:</label>
	<textarea class='tinymce' cols='80' rows='6' name='ricevimento' id='ricevimento'></textarea>

	<label for='obiettivi'>Obiettivi del corso:</label>
	<textarea class='tinymce' cols='80' rows='6' name='obiettivi' id='obiettivi'></textarea>

	<label for='programma'>Programma del corso:</label>
	<textarea class='tinymce' cols='80' rows='6' name='programma' id='programma'></textarea>

	<label for='esame'>Modalit&agrave; esame:</label>
	<textarea class='tinymce' cols='80' rows='6' name='esame' id='esame'></textarea>

	<label for='materiali'>Testi (materiali di riferimento):</label>
	<textarea class='tinymce' cols='80' rows='6' name='materiali' id='materiali'></textarea>

</form>
