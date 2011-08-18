<?php
	if ( empty( $corso ) )
		$corso = false;
	else {
		echo "<script type='text/javascript'>var id_corso = $corso[id_corso];</script>\n";
	}
?>
<form action='<?php echo "$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]" ?>' method='post' id='frm_corso'>
<?php
	if ( $corso )
		printf( "<input type='hidden' name='id' value='%d' />\n", $corso['id_corso'] );
?>
	<h3>Corso:</h3>
	<label for='nome' class='etichetta'>Nome corso:</label>
	<input type='text' name='nome' id='nomecorso' value='<?php riempi( $corso['nome'], 'attr' ) ?>'/>

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
			if ( ! $corso && ! $gia_segnato ) {
				$checked = 'checked="checked" ';
				$gia_segnato = true;
			}
			else if ( $corso && $corso['id_facolta'] == $riga['id_facolta'] ) {
				$checked = 'checked="checked" ';
			}
			echo "<input type='radio' name='facolta' id='facolta_$riga[id_facolta]' value='$riga[id_facolta]' $checked/>\n";

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

		// Carico i docenti di questo corso, se ne sto modificando uno gia' esistente:
		if ( $corso ) {
			$query = <<<EOF
SELECT `id_docente`
FROM `$config[db_prefix]docente_corso`
WHERE `id_corso` = '$corso[id_corso]'
EOF;
			$result = mysql_query( $query, $db );
			$docenti = array();
			while ( $riga = mysql_fetch_assoc( $result ) ) {
				$docenti[]  = $riga['id_docente'];
			}
		}

		$query = <<<EOF
SELECT `id_docente`, `nome`
FROM `$config[db_prefix]docente`
ORDER BY `nome` ASC
EOF;
		$result = mysql_query( $query, $db );

		while ( $riga = mysql_fetch_assoc( $result ) ) {
			echo "<li>\n";

			$checked = '';
			if ( $corso && in_array( $riga['id_docente'], $docenti ) )
				$checked = 'checked="checked" ';
			echo "<input type='checkbox' name='docente[]' id='docente_$riga[id_docente]' value='$riga[id_docente]' $checked/>\n";

			printf( "<label for='docente_$riga[id_docente]'>%s</label>\n", htmlspecialchars( $riga['nome'] ) );
			echo "</li>\n";
		}
		?>
		</ul>
	</div>

	<div class='clear'></div>

	<div class='contenuti'>
		<h3>Pagina corso:</h3>
		<?php if ( $corso ): ?>
		<a href='javascript:void(0)' class='linkconicona' id='link-gestione-news' style='background-image: url(img/icone/newspaper.png)'>
			Gestione News</a><br />
		<?php endif; ?>

		<!-- Contenuti editabili liberamente -->

		<!-- TinyMCE -->
		<script type="text/javascript" src="js/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="js/corsi.js"></script>

		<label for='intestaz'>Intestazione:</label>
		<textarea class='tinymce' cols='90' rows='6' name='intestaz' id='intestaz'><?php riempi( $corso['intestazione'], 'html' ) ?></textarea>

		<label for='orario'>Orario lezione:</label>
		<textarea class='tinymce' cols='90' rows='6' name='orario' id='orario'><?php riempi( $corso['orario'], 'html' ) ?></textarea>

		<label for='ricevimento'>Orario di ricevimento:</label>
		<textarea class='tinymce' cols='90' rows='6' name='ricevimento' id='ricevimento'><?php riempi( $corso['ricevimento'], 'html' ) ?></textarea>

		<label for='obiettivi'>Obiettivi del corso:</label>
		<textarea class='tinymce' cols='90' rows='6' name='obiettivi' id='obiettivi'><?php riempi( $corso['obiettivi'], 'html' ) ?></textarea>

		<label for='programma'>Programma del corso:</label>
		<textarea class='tinymce' cols='90' rows='6' name='programma' id='programma'><?php riempi( $corso['programma'], 'html' ) ?></textarea>

		<label for='esame'>Modalit&agrave; esame:</label>
		<textarea class='tinymce' cols='90' rows='6' name='esame' id='esame'><?php riempi( $corso['esame'], 'html' ) ?></textarea>

		<label for='materiali'>Testi (materiali di riferimento):</label>
		<textarea class='tinymce' cols='90' rows='6' name='materiali' id='materiali'><?php riempi( $corso['materiali'], 'html' ) ?></textarea>
	</div>

<?php if ( $corso ): ?>
	<h3>Materiale didattico</h3>
	<p>
		<a href='javascript:void(0)' class='linkconicona' id='link-nuova-sezione' style='background-image: url(img/icone/page_add.png)'>
			Nuova sezione</a>
	</p>
	<div id='lista-sezioni'></div>

<?php endif;/* materiale didattico */ ?>


	<input type='submit' value='Salva' name='salva' class='invio' />
</form>

<?php if ( $corso ): ?>
<script type='text/javascript' src='js/jquery.iframe-post-form.js'></script>
<div id="news-dialog-form" title='Gestione news'>
	<!-- Inserimento di una nuova news -->
	<form action='ajax/corsi.php?action=savenews' method='post' enctype="multipart/form-data">
		<input type='hidden' name='id_corso' value='<?php riempi( $corso['id_corso'], 'int' ) ?>' />
		<fieldset>
			<h4>Inserisci una nuova news</h4>
			<p style='margin-bottom: 15px'>
				<label for="testo-news" class='etichetta'>Testo news:</label>
				<textarea class="tinymce" rows='5' cols='50' id='testo-news' name='testo'></textarea>
			</p>

			<label for="attachment" class='etichetta'>Allegato:</label>
			<input type="file" name='file' id='attachment' class="ui-corner-all" />

			<input type="checkbox" name='hide-news' id='hide-news' />
			<label for="hide-news">Nascondi questa news</label>

			<button class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' id='btn--news' style='margin-left: 4em'>
				<span class="ui-button-text">Salva</span>
			</button>
		</fieldset>
	</form>

	<!-- Lista news esistenti -->
	<h4>News correnti:</h4>

		<?php
			$query = <<<EOF
SELECT `id_news`,`nascondi`,`testo`
FROM `$config[db_prefix]news`
WHERE `id_corso` = '$corso[id_corso]'
ORDER BY `ordine` ASC
EOF;
			$result = mysql_query( $query, $db );
			if ( ! mysql_num_rows( $result ) ) {
				echo "Nessuna news presente.";
			}
			echo "<ul id='lista-news' class='lista-dnd'>\n";

			while ( $riga = mysql_fetch_assoc( $result ) ) {
				echo "<li class='ui-corner-all' id='news_$riga[id_news]'>\n";
				echo "<span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n";
				$title = $riga['nascondi'] ? 'News nascosta' : 'News visibile';
				echo "<a href='javascript:void(0)' class='iconalink eyeicon' title='$title'>\n";
				if ( ! $riga['nascondi'] )
					echo "<img src='img/icone/eye.png' alt='News visibile' />\n";
				else
					echo "<img src='img/icone/eye_no.png' alt='News nascosta' />\n";
				echo "</a>\n ";
				echo strip_tags( $riga['testo'] );
				echo "</li>\n";
			}
			echo "</ul>\n";
		?>
</div><!-- /#news-dialog-form -->


<div id="sezioni-dialog-form" title='Sezione per materiale didattico:'>
	<form action='ajax/corsi.php?action=' method='post'>
		<input type='hidden' name='id_corso' value='<?php riempi( $corso['id_corso'], 'int' ) ?>' />
		<input type='hidden' id='id_sezione' name='id_sezione' value='' />
		<h4>Sezione:</h4>
		<label for="titolo-sezione" class='etichetta'>Titolo sezione:</label>
		<input type='text' name='titolo' id='titolo-sezione' /><br />

		<p style='margin-bottom: 4px'>
			<label for="note-sezione" class='etichetta'>Note:</label>
		</p>
		<textarea class="tinymce" name='note' id='note-sezione'></textarea>
	</form>

	<!-- Lista di files -->
	<h4>File in questa sezione:</h4>
		<a href='javascript:void(0)'>Aggiungi nuovo file</a>
		<?php
			echo "<ul id='lista-file-sezione' class='lista-dnd'>\n";
			echo "</ul>\n";
		?>
</div><!-- /#sezioni-dialog-form -->

<?php
endif; // if corso


// Funzione di comodita', per evitare duplicati di codice
function riempi( $valore, $tipo ) {
	global $corso;

	if ( ! $corso ) return;

	$out = '';
	switch( $tipo ) {
		case 'int':
			$out = intval( $valore );
			break;
		case 'attr':
			$out = htmlspecialchars( $valore, ENT_QUOTES );
			break;
		case 'html':
			$out = htmlspecialchars( $valore );
			break;
	}

	echo $out;
}

?>
