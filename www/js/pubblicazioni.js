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


	$('#categoria').change(function() {
		$(this).find("option").each(function() {
			if (this.selected) {
				$("#opt_" + this.value).show(500);

				$("label[for=titolo_contesto]").html("Titolo " + this.value[0].toUpperCase() + this.value.substr(1))
			}
			else {
				$("#opt_" + this.value).hide(500);
			}
		});
	});


})(jQuery);

