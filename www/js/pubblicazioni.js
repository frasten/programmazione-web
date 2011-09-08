(function($) {

	// Carichiamo la lista di autori
	var pub_autori;
	$.getJSON('ajax/pubblicazioni.php?action=get_lista_autori', function(data) {
		if ( ! data || ! data.success ) {
			// Errore
			var txt = 'Errore';
			if ( data.error )
				txt += ": " + data.error;
			alert(txt);
			pub_autori = new Array();
			return;
		}
		pub_autori = data.autori;
	});

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
			delay: 100,
			source: function( request, response ) {
				var matches = $.map( pub_autori, function(tag) {
					request.term = extractLast(request.term);
					var parole = tag.split(' ');
					var corrisponde = false;
					for ( var i in parole ) {
						var p = parole[i];
						if ( p.toUpperCase().indexOf(request.term.toUpperCase()) === 0 ) {
							corrisponde = true;
							break;
						}
					}
					if (corrisponde) return tag;
				});
				response(matches);
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


	// AUTOCOMPLETAMENTO NOMI JOURNAL
	$( "#titolo_contesto" )
		// don't navigate away from the field on tab when selecting an item
		.bind( "keydown", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB &&
					$( this ).data( "autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			minLength: 0,
			delay: 100,
			source: function( request, response ) {
				if ($('#categoria').val() != "rivista") return;
				var matches = $.map( lista_journal, function(tag) {
					var parole = tag.split(' ');
					var corrisponde = false;
					for ( var i in parole ) {
						var p = parole[i];
						if ( p.toUpperCase().indexOf(request.term.toUpperCase()) === 0 ) {
							corrisponde = true;
							break;
						}
					}
					if (corrisponde) return tag;
				});
				response(matches);
			},
			focus: function() {
				// prevent value inserted on focus
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
				$("label[for=titolo_contesto]").html(titolo + " <em class='richiesto'>*</em>")
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





	// VALIDAZIONE DEL FORM
	$.validator.addMethod("lessThan",
		function(value, element, param) {
			var target = $(param).unbind(".validate-lessThan").bind("blur.validate-moreThan", function() {
				$(element).valid();
			});
			var i = parseFloat(value);
			var j = parseFloat(target.val());
			if (target.val() == '') return true;
			return i <= j;
		}
	);
	$.validator.addMethod("moreThan",
		function(value, element, param) {
			var target = $(param).unbind(".validate-moreThan").bind("blur.validate-lessThan", function() {
				$(element).valid();
			});
			var i = parseFloat(value);
			var j = parseFloat(target.val());
			return i >= j;
		}
	);
	jQuery.extend(jQuery.validator.messages, {
		integer: "Inserire un numero intero."
	});

	var validator = $("#form-pubblicazione").validate({
		rules: {
			titolo: "required",
			autori: "required",
			anno: {
				required: true,
				integer: true,
				range: [1900,(new Date).getFullYear()]
			},
			volume: {
				required: true,
				integer: true
			},
			titolo_contesto: "required",
			pag_inizio: {
				required: true,
				integer: true,
				lessThan: "#pag_fine"
			},
			pag_fine: {
				required: true,
				integer: true,
				moreThan: "#pag_inizio"
			},
			numero: {
				integer: true
			},
			editore: "required",
			curatori_libro: "required",
			num_pagine: {
				integer: true
			}
		},
		messages: {
			pag_inizio: {
				lessThan: "Inserire un numero minore della pagina di fine."
			},
			pag_fine: {
				moreThan: "Inserire un numero maggiore della pagina d'inizio."
			}
		},
		/* Ignoro i campi invisibili perche' non applicabili a questa
		 * categoria di pubblicazione: */
		ignore: ":hidden",

		// Dove mettiamo gli errori
		errorPlacement: function(error, element) {
			if ( element.attr('id') == 'volume' ||
			     element.attr('id') == 'pag_inizio'
			)
				element.after(error);
			else
				error.appendTo( element.parent() );
		}
	});


})(jQuery);

function eliminaPubblicazione(id) {
	var li = $("li#pubblicazione_" + id);
	li.mask("Eliminazione...", 200);
	jQuery.post('ajax/pubblicazioni.php?action=eliminapubblicazione', {
		id: id
		},
		function (data) {
			li.unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}

			// Faccio sparire la pubblicazione
			var lista = li.parent()
			li.animate({
				"height": "toggle",
				"opacity": "toggle"
				}, 600, function() {
					li.remove();
				}
			);

			if (lista.find("li").length == 1) {
				// Era l'unica pubblicazione di quell'anno

				// Elimino il titolo
				var titolo = lista.prevAll("h4").first();
				titolo.animate({
					"height": "toggle",
					"opacity": "toggle"
					}, 600, function () {
						// Controllo se quell'anno era l'unico di tale tipo di pubblicazione
						if (! titolo.prevAll("h4, h3").first().is("h4") &&
								! titolo.nextAll("h4, h3").first().is("h4") ) {
							titolo.prevAll("h3").first().after("Non &egrave; presente alcuna pubblicazione.")
						}
						titolo.remove();
					}
				);
				// Elimino la lista
				lista.remove();
			}

		},
		"json"
	);
}

function askEliminaPubblicazione(id) {
	jQuery("<p>Si &egrave; sicuri di voler eliminare questa pubblicazione?</p>")
		.dialog({
			resizable: false,
			height: 'auto',
			modal: true,
			buttons: {
				"Annulla": function() {
					jQuery( this ).dialog( "close" );
				},
				"Elimina": function() {
					eliminaPubblicazione(id);
					jQuery( this ).dialog( "close" );
				}
			}
		});
}

