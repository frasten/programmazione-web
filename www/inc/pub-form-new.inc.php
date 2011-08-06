<h3>Inserisci nuova pubblicazione</h3>
<form method='post' action='?action=savenew'>

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
			<label for='titolo'>Titolo:</label>
			<input type='text' name='titolo' id='titolo' />
		</li>

		<li>
			<label for='autori'>Autori:</label>
			<input type='text' name='autori' id='autori' />
		</li>

		<li>
			<label for='anno'>Anno:</label>
			<input type='text' name='anno' id='anno' maxlength='4' />
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='titolo_contesto'>Titolo del journal:</label>
			<input type='text' name='titolo_contesto' id='titolo_contesto' />
		</li>

		<li class='opt_rivista'>
			<label for='volume'>Volume:</label>
			<input type='text' name='volume' id='volume' />
			<label for='numero' class='label_secondaria'>Numero:</label>
			<input type='text' name='numero' id='numero' />
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='pag_inizio'>Pag. inizio:</label>
			<input type='text' name='pag_inizio' id='pag_inizio' />
			<label for='pag_fine' class='label_secondaria'>Pag. fine:</label>
			<input type='text' name='pag_fine' id='pag_fine' />
		</li>

		<li class='opt_libro opt_monografia opt_curatela'>
			<label for='editore'>Editore:</label>
			<input type='text' name='editore' id='editore' />
		</li>

		<li class='opt_libro'>
			<label for='curatori_libro'>Curatori del libro:</label>
			<input type='text' name='curatori_libro' id='curatori_libro' />
		</li>

		<li class='opt_libro'>
			<label for='isbn'>ISBN/ISSN:</label>
			<input type='text' name='isbn' id='isbn' />
		</li>

		<li class='opt_monografia'>
			<label for='num_pagine'>Numero di pagine:</label>
			<input type='text' name='num_pagine' id='num_pagine' />
		</li>

		<li>
			<label for='abstract'>Abstract:</label>
			<textarea name='abstract' id='abstract' rows="3" cols="50"></textarea>
		</li>

		<li class='opt_rivista opt_libro opt_conferenza'>
			<label for='file'>Allega file:</label>
			<input type='file' name='file' id='file' />
		</li>

	</ul>

	<input type='submit' value='Salva' />

</form>
<!-- FAM FAM ICONS, pencil + cross -->

<script type='text/javascript' src='js/pubblicazioni.js'></script>

