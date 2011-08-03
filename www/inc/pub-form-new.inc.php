<h3>Inserisci nuova pubblicazione</h3>
<form method='post' action='?action=savenew'>

	<ul class='form_list'>
		<li>
			<label for='categoria'>Categoria:</label>
			<select name='categoria' id='categoria'>
				<option value='rivista'>Pubblicazioni su riviste</option>
				<option value='libro'>Capitoli libro</option>
				<option value='congresso'>Congressi</option>
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

		<li>
			<label for='titolo_contesto'>Titolo Rivista:</label>
			<input type='text' name='titolo_contesto' id='titolo_contesto' />
		</li>

	<!-- INFO OPZIONALI, dipendenti dalla categoria di pubblicazione  -->
		<li class='opt_libro'>
			<label for='autori_libro'>Autori Libro:</label>
			<input type='text' name='autori_libro' id='autori_libro' />
		</li>

		<li class='opt_libro'>
			<label for='editore'>Editore:</label>
			<input type='text' name='editore' id='editore' />
		</li>

		<li class='opt_libro'>
			<label for='isbn'>ISBN/ISSN:</label>
			<input type='text' name='isbn' id='isbn' />
		</li>



		<li class='opt_congresso'>
			<label for='citta'>Citt&agrave;:</label>
			<input type='text' name='citta' id='citta' />
		</li>

		<li class='opt_congresso'>
			<label for='nazione'>Nazione:</label>
			<input type='text' name='nazione' id='nazione' />
		</li>

		<li>
			<label for='info_addizionali'>Informazioni Addizionali:</label>
			<textarea name='info_addizionali' id='info_addizionali' rows="3" cols="50"></textarea>
		</li>

		<li>
			<label for='file'>Allega file:</label>
			<input type='file' name='file' id='file' />
		</li>

	</ul>

	<input type='submit' value='Salva' />

</form>
<!-- FAM FAM ICONS, pencil + cross -->

<script type='text/javascript' src='js/pubblicazioni.js'></script>

