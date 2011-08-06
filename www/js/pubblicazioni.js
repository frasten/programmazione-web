(function($) {

	// Lista di professori
	var professori = [
		"Devis Bianchini",
		"Valeria De Antonellis",
		"Michele Melchiori",
		"Denise Salvi"
	];
	
	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}

	$( "#autori" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			minLength: 0,
			source: function( request, response ) {
				// delegate back to autocomplete, but extract the last term
				response( $.ui.autocomplete.filter(
					professori, extractLast( request.term ) ) );
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );
				// add placeholder to get the comma-and-space at the end
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
			}
		});


	function update_pub_fields(field) {
		var current_sel = $(field).val();
		$(field).find("option").each(function() {
			if (this.selected) {
				$(".opt_" + this.value).show(500);

				var titolo = '';
				switch(this.value) {
					case 'rivista':
						titolo = 'Nome del journal:';
						break;
					case 'libro':
						titolo = 'Titolo del libro:';
						break;
					case 'conferenza':
						titolo = 'Nome della conferenza:';
						break;
				}
				$("label[for=titolo_contesto]").html(titolo)
			}
			else {
				$(".opt_" + this.value).not(".opt_" + current_sel).hide(500);
			}
		});
	}


	$('#categoria').change(function() {
		update_pub_fields(this);
	});


	// Impostiamo i fields all'avvio
	update_pub_fields($('#categoria'));

})(jQuery);

