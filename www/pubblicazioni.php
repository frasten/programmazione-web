<?php

require_once('inc/header.inc.php');
?>


<h3>Publications</h3>
<form method='post' action=''>

	Categoria:
	<select name='categoria' id='categoria'>
		<option value='rivista'>Pubblicazioni su riviste</option>
		<option value='libro'>Capitoli libro</option>
		<option value='congresso'>Congressi</option>
	</select>

	<br />

	Titolo:
	<input type='text' name='titolo' id='titolo' />

	<br />
	Autori:
	<input type='text' name='autori' id='autori' />

	<br />
	Anno:
	<input type='text' name='anno' id='anno' />

	<br />
	<label for='titolo_contesto'>Titolo Rivista</label>
	<input type='text' name='titolo_contesto' id='titolo_contesto' />

	<!-- INFO OPZIONALI, dipendenti dalla categoria di pubblicazione  -->
	<div id='opt_rivista'></div>

	<div id='opt_libro'>

		<label for='autori_libro'>Autori Libro</label>
		<input type='text' name='autori_libro' id='autori_libro' />

		<br />
		<label for='editore'>Editore</label>
		<input type='text' name='editore' id='editore' />

		<br />
		<label for='isbn'>ISBN/ISSN</label>
		<input type='text' name='isbn' id='isbn' />

	</div><!-- #opt_libro -->

	<div id='opt_congresso'>

		<label for='citta'>Citt&agrave;</label>
		<input type='text' name='citta' id='citta' />

		<br />
		<label for='nazione'>Nazione</label>
		<input type='text' name='nazione' id='nazione' />

	</div><!-- #opt_congresso -->
        <br />
        Informazioni Addizionali:
        <br/>
        <textarea rows="3" cols="50"></textarea>
        <br/>
        Allega File: <input type="file"></input>
</form>


<script type='text/javascript' src='js/pubblicazioni.js'></script>
<?php
require_once('inc/footer.inc.php');

?>
