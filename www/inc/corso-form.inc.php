<?php
	if ( empty( $corso ) )
		$corso = false;
	else {
		echo "<script type='text/javascript'>var id_corso = $corso[id_corso];</script>\n";
	}
?>
<form action='?<?php
	echo htmlspecialchars( $_SERVER['QUERY_STRING'], ENT_QUOTES );
	?>' method='post' id='frm_corso'>
<?php
	if ( $corso )
		printf( "<input type='hidden' name='id' value='%d' />\n", $corso['id_corso'] );
?>
	<h3>Corso:</h3>
	<label for='nome' class='etichetta'>Nome corso:</label>
	<input type='text' class='testo' name='nome' id='nomecorso' value='<?php riempi( $corso['nome'], 'attr' ) ?>'/>

	<p>
		<label for='annoaccademico' class='etichetta'>Anno accademico:</label>
		<select name='annoaccademico' id='annoaccademico'>
<?php
	$questanno = intval( date( 'Y' ) );
	for ( $i = $questanno - $config['anni_accademici_passati']; $i <= $questanno + $config['anni_accademici_futuri']; $i++ ) {
		$annocheck = '';
		if ( ! $corso ) {
			if ( intval( date( 'n' ) ) >= 5 ) {
				// Da maggio in poi consideriamo per i nuovi corsi l'anno accademico futuro
				$annocheck = $questanno;
			}
			else // Prima di maggio, l'anno accademico in corso.
				$annocheck = $questanno - 1;
		}
		else {
			$annocheck = $corso['annoaccademico'];
		}

		$selected = $i == $annocheck ? 'selected="selected" ' : '';
		printf( "<option value='%d' %s>%d/%d</option>\n", $i, $selected, $i, $i + 1 );
	}
?>
		</select>
	</p>

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
			<li>
				<a class="linksenzaicona" href="javascript:;" id="link-nome-facolta">Nuova Facolt&agrave;</a>
			</li>
		</ul>
	</div>

	<div id='lista_docenti'>
		<table id='tbl_insert_docenti'>
			<thead>
				<th style='text-align: left'>Docenti:</th>
				<th>Docente</th>
				<th>Esercitat.</th>
			</thead>
			<tbody>
		<?php /* Elenco dei docenti, con checkbox */

		// Carico i docenti di questo corso, se ne sto modificando uno gia' esistente:
		if ( $corso ) {
			$query = <<<EOF
SELECT `id_docente`,`esercitatore`
FROM `$config[db_prefix]docente_corso`
WHERE `id_corso` = '$corso[id_corso]'
EOF;
			$result = mysql_query( $query, $db );
			$docenti = array();
			while ( $riga = mysql_fetch_assoc( $result ) ) {
				$docenti[$riga['id_docente']] = $riga['esercitatore'];
			}
		}

		$query = <<<EOF
SELECT `id_docente`, `nome`
FROM `$config[db_prefix]docente`
ORDER BY `nome` ASC
EOF;
		$result = mysql_query( $query, $db );

		while ( $riga = mysql_fetch_assoc( $result ) ) {
			echo "<tr>\n";
			echo "<td>\n";
			$checked = '';
			if ( $corso && array_key_exists( $riga['id_docente'], $docenti ) )
				$checked = 'checked="checked" ';
			echo "<input type='checkbox' name='docente[]' id='docente_$riga[id_docente]' value='$riga[id_docente]' $checked/>\n";

			printf( "<label for='docente_$riga[id_docente]'>%s</label>\n", htmlspecialchars( $riga['nome'] ) );
			echo "</td>\n";

			// Docente
			$checked = $corso && isset( $docenti[$riga['id_docente']] ) && $docenti[$riga['id_docente']] == '0' ? 'checked="checked" ' : '';
			echo "<td class='option'>\n";
			echo "<input type='radio' name='tipodocente_$riga[id_docente]' value='0' $checked/>\n";
			echo "</td>\n";
			// Esercitatore
			$checked = $corso && isset( $docenti[$riga['id_docente']] ) && $docenti[$riga['id_docente']] == '1' ? 'checked="checked" ' : '';
			echo "<td class='option'>\n";
			echo "<input type='radio' name='tipodocente_$riga[id_docente]' value='1' $checked/>\n";
			echo "</td>\n";

			echo "</tr>\n";
		}
		?>
				<tr>
					<td>
						<a class="linksenzaicona" href="javascript:;" id="link-nome-docente">Nuovo Docente</a>
					</td>
					<td></td>
					<td></td>
				</tr>
			</tbody>
		</table>

	</div>

	<div class='clear'></div>

	<div class='contenuti'>
		<h3>Pagina corso:</h3>
		<?php if ( $corso ): ?>
		<a href='javascript:;' class='linkconicona' id='link-gestione-news' style='background-image: url(img/icone/newspaper.png)'>
			Gestione News</a><br />
		<?php endif; ?>

		<!-- Contenuti editabili liberamente -->

		<!-- TinyMCE -->
		<script type="text/javascript" src="js/tiny_mce/jquery.tinymce.js"></script>
		<script type="text/javascript" src="js/corsi.js"></script>

		<?php if ( $corso ): ?>
		<script type="text/javascript" src="js/news.js"></script>
		<script type="text/javascript" src="js/files.js"></script>
		<?php endif; ?>

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
		<a href='javascript:;' class='linkconicona' id='link-nuova-sezione' style='background-image: url(img/icone/page_add.png)'>
			Nuova sezione</a>
	</p>
	<div id='lista-sezioni'></div>

<?php endif;/* materiale didattico */ ?>


	<input type='submit' value='Salva' name='salva' class='invio submitbutton' />
</form>

<script type='text/javascript' src='js/jquery.iframe-post-form.js'></script>


<!-- Dialogo per la creazione di una nuova facolta' -->
<div id="facolta-dialog-form" title='Nuova Facolt&agrave;'>
	<form action="ajax/corsi.php?action=newfacolta" method="post">
		<fieldset>
			<h4>Inserisci una nuova facolt&agrave;</h4>
			<p style='margin-bottom: 15px'>
				<label for="nome-facolta" class='etichetta'>Nome Facolt&agrave;:</label>
				<input type="text" size="50" id ="nome-facolta" name='nome-facolta'></input>
			</p>
		</fieldset>
	</form>
</div> <!-- /#facolta-dialog-form -->


<!-- Dialogo per l'aggiunta di un nuovo Docente -->
<div id="docente-dialog-form" title='Nuovo Docente'>
	<form action="ajax/corsi.php?action=newdocente" method="post">
		<fieldset>
			<h4>Inserisci un nuovo docente</h4>
			<p style='margin-bottom: 15px'>
				<label for="nome-docente" class='etichetta'>Nome Docente:</label>
				<input type="text" size="50" id ="nome-docente" name='nome-docente'></input>
			</p>
		</fieldset>
	</form>
</div> <!-- /#docente-dialog-form -->



<?php if ( $corso ): ?>


<div id="news-dialog-form" title='Gestione news'>
	<!-- Inserimento di una nuova news -->
	<form action='ajax/news.php?action=savenews' method='post' enctype="multipart/form-data">
		<input type='hidden' name='id_corso' value='<?php riempi( $corso['id_corso'], 'int' ) ?>' />
		<input type='hidden' name='id_news' value='' />
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

			<span id='news-saved-attachment'>(Allegato gi&agrave; salvato)</span>

			<button class='ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only' id='btn-news' style='margin-left: 4em;'>
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
			echo "<span id='avviso-no-news'>Nessuna news presente.</span>\n";
			echo "<ul id='lista-news' class='lista-dnd'>\n";

			while ( $riga = mysql_fetch_assoc( $result ) ) {
				echo "<li class='ui-corner-all' id='news_$riga[id_news]'>\n";
				echo "<span class='ui-icon ui-icon-arrowthick-2-n-s'></span>\n";

				// Icona mostra/nascondi
				$title = $riga['nascondi'] ? 'News nascosta' : 'News visibile';
				echo "<a href='javascript:;' class='iconalink eyeicon' title='$title'>\n";
				if ( ! $riga['nascondi'] )
					echo "<img src='img/icone/eye.png' alt='$title' />\n";
				else
					echo "<img src='img/icone/eye_no.png' alt='$title' />\n";
				echo "</a>\n ";

				// Icona modifica
				echo "<a href='javascript:;' class='iconalink' onclick='caricaNews($riga[id_news])' title='Modifica news'>\n";
				echo "<img src='img/icone/newspaper_edit.png' alt='Modifica news' />\n";
				echo "</a>\n ";

				// Icona elimina
				echo "<a href='javascript:;' class='iconalink' onclick='askEliminaNews($riga[id_news])' title='Elimina news'>\n";
				echo "<img src='img/icone/newspaper_delete.png' alt='Elimina news' />\n";
				echo "</a>\n ";

				echo "<span class='lbltesto'>";
				echo strip_tags( $riga['testo'] );
				echo "</span>";
				echo "</li>\n";
			}
			echo "</ul>\n";
		?>
</div><!-- /#news-dialog-form -->


<div id="sezioni-dialog-form" title='Sezione per materiale didattico:'>
	<form action='javascript:;' method='post'>
		<input type='hidden' name='id_corso' value='<?php riempi( $corso['id_corso'], 'int' ) ?>' />
		<input type='hidden' id='id_sezione' name='id_sezione' value='' />
		<h4>Sezione:</h4>
		<label for="titolo-sezione" class='etichetta'>Titolo sezione:</label>
		<input type='text' class='testo' name='titolo' id='titolo-sezione' /><br />

		<p style='margin-bottom: 4px'>
			<label for="note-sezione" class='etichetta'>Note:</label>
		</p>
		<textarea class="tinymce" name='note' id='note-sezione'></textarea>
	</form>

	<div id='sezione-blocco-listafile'>
		<!-- Lista di files -->
		<h4>File in questa sezione:</h4>
			<a href='javascript:;' class='linkconicona' onclick='apriDialogoFile(0)' style='background-image: url(img/icone/file_add.png)'>Aggiungi nuovo file</a>
			<?php
				echo "<ul id='lista-file-sezione' class='lista-dnd'></ul>\n";
			?>
	</div>
</div><!-- /#sezioni-dialog-form -->

<div id="file-dialog-form" title='File:'>
	<form action='ajax/files.php?action=savefile' method='post' enctype="multipart/form-data">
		<input type='hidden' name='id_sezione' value='' />
		<input type='hidden' id='id_file' name='id_file' value='' />

		<h4>File:</h4>

		<p>
			<label for="titolo-file" class='etichetta'>Titolo file:</label>
			<input type='text' class='testo dx' style='left: 6em; margin-top: -0.22em' name='titolo' id='titolo-file'class='dx' />
		</p>

		<p>
			<input type='radio' name='tipourl' id='tipourl_url' value='url' />
			<label for='tipourl_url' class='etichetta'>
				URL:
				<input type='text' class='testo dx' style='left: 6em' name='url' id='url-file' class='dx'/>
			</label>
		</p>

		<p>
			<input type='radio' name='tipourl' id='tipourl_upload' value='upload' />
			<label for='tipourl_upload' class='etichetta'>
				Carica file:
				<input type='file' class='dx' name='file' />
			</label>
		</p>

		<p style='margin-bottom: 3px'>
			<input type='checkbox' name='aggiornato' id='file-aggiornato'/>
			<label for='file-aggiornato' class='etichetta'>Aggiornato</label>
		</p>

		<p style='margin-top: 3px'>
			<input type='checkbox' name='nascondi' id='nascondi-file'/>
			<label for='nascondi-file' class='etichetta'>Nascondi</label>
		</p>

	</form>

</div><!-- /#file-dialog-form -->

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
