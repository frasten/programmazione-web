<h3>Inserisci nuova pubblicazione</h3>
<form enctype="multipart/form-data" method='post' action='?action=savenew' id='form-pubblicazione'>

	<ul class='form_list'>
		<li>
			<label for='categoria'>Categoria:</label>
			<select name='categoria' id='categoria'>
				<option value='rivista'>Pubblicazioni su riviste</option>
				<option value='libro'>Capitoli libro</option>
				<option value='conferenza'>Atti di conferenza</option>
				<option value='monografia'>Monografia</option>
				<option value='curatela'>Curatela</option>
			</select>
		</li>

		<li>
			<label for='titolo'>Titolo: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='titolo' id='titolo' />
		</li>

		<li>
			<label for='autori'>Autori: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='autori' id='autori' />
		</li>

		<li>
			<label for='anno'>Anno: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='anno' id='anno' maxlength='4' />
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='titolo_contesto'>Titolo del journal: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='titolo_contesto' id='titolo_contesto' />
		</li>

		<li class='opt_rivista'>
			<label for='volume'>Volume: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='volume' id='volume' />
			<label for='numero' class='label_secondaria'>Numero:</label>
			<input type='text' class='testo' name='numero' id='numero' />
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='pag_inizio'>Pag. inizio: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='pag_inizio' id='pag_inizio' autocomplete="off" />
			<label for='pag_fine' class='label_secondaria'>Pag. fine: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='pag_fine' id='pag_fine' autocomplete="off" />
		</li>

		<li class='opt_libro opt_monografia opt_curatela'>
			<label for='editore'>Editore: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='editore' id='editore' />
		</li>

		<li class='opt_libro'>
			<label for='curatori_libro'>Curatori del libro: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='curatori_libro' id='curatori_libro' />
		</li>

		<li class='opt_libro'>
			<label for='isbn'>ISBN/ISSN:</label>
			<input type='text' class='testo' name='isbn' id='isbn' />
		</li>

		<li class='opt_monografia'>
			<label for='num_pagine'>Numero di pagine: <em class='richiesto'>*</em></label>
			<input type='text' class='testo' name='num_pagine' id='num_pagine' />
		</li>

		<li>
			<label for='abstract'>Abstract:</label>
			<textarea name='abstract' class='testo' id='abstract' rows="3" cols="50"></textarea>
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='file'>Allega file:</label>
			<input type='file' name='file' id='file' />
		</li>

	</ul>

	<p><em class='richiesto'>*</em><em>: campo obbligatorio.</em></p>

	<input type='submit' class='submitbutton' name='salva' value='Salva' />

</form>
<!-- FAM FAM ICONS, pencil + cross -->

<script type='text/javascript'>
/* <![CDATA[ */
var lista_journal = [
<?php
	$query = <<<EOF
SELECT DISTINCT `titolo_contesto`
FROM `$config[db_prefix]pubblicazione`
WHERE `categoria` = 'rivista'
ORDER BY `titolo_contesto` ASC;
EOF;
	$result = mysql_query( $query, $db );
	$pub = array();
	while ( $riga = mysql_fetch_assoc( $result ) ) {
		$pub[] = '"' . addslashes( $riga['titolo_contesto'] ) . '"';
	}
	echo implode( ",\n", $pub );
?>

];
/* ]]> */
</script>
<script type='text/javascript' src='js/pubblicazioni.js'></script>

