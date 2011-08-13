<form action='' method='post' id='frm_corso'>
	<h3>Corso:</h3>
	<label for='nome' class='etichetta'>Nome corso:</label>
	<input type='text' name='nome' id='nomecorso' />

	<div id='lista_facolta'>
		<span class='etichetta'>Facolt&agrave;:</span>
		<ul class='lista_checkbox'>
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

			printf( "<label for='facolta_$riga[id_facolta]'>%s</label>\n", htmlspecialchars( $riga['nome'] ) );
			echo "</li>\n";
		}
		?>
		</ul>
	</div>

	<div id='lista_docenti'>
		<span class='etichetta'>Docenti:</span>
		<ul class='lista_checkbox'>
		<?php /* Elenco dei docenti, con checkbox */
		$query = <<<EOF
SELECT `id_docente`, `nome`
FROM `$config[db_prefix]docente`
ORDER BY `nome` ASC
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
			echo "<input type='checkbox' name='docente[]' id='docente_$riga[id_docente]' value='$riga[id_docente]' />\n";

			printf( "<label for='docente_$riga[id_docente]'>%s</label>\n", htmlspecialchars( $riga['nome'] ) );
			echo "</li>\n";
		}
		?>
		</ul>
	</div>

	<div class='clear'></div>

	<!-- Contenuti editabili liberamente -->
	<div class='contenuti'>
		<h3>Pagina corso:</h3>

		<!-- TinyMCE -->
		<script type="text/javascript" src="js/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="js/corsi.js"></script>

		<label for='intestaz'>Intestazione:</label>
		<textarea class='tinymce' cols='90' rows='6' name='intestaz' id='intestaz'></textarea>

		<label for='orario'>Orario lezione:</label>
		<textarea class='tinymce' cols='90' rows='6' name='orario' id='orario'></textarea>

		<label for='ricevimento'>Orario di ricevimento:</label>
		<textarea class='tinymce' cols='90' rows='6' name='ricevimento' id='ricevimento'></textarea>

		<label for='obiettivi'>Obiettivi del corso:</label>
		<textarea class='tinymce' cols='90' rows='6' name='obiettivi' id='obiettivi'></textarea>

		<label for='programma'>Programma del corso:</label>
		<textarea class='tinymce' cols='90' rows='6' name='programma' id='programma'></textarea>

		<label for='esame'>Modalit&agrave; esame:</label>
		<textarea class='tinymce' cols='90' rows='6' name='esame' id='esame'></textarea>

		<label for='materiali'>Testi (materiali di riferimento):</label>
		<textarea class='tinymce' cols='90' rows='6' name='materiali' id='materiali'></textarea>
	</div>


	<input type='submit' value='Salva' class='invio' />
</form>