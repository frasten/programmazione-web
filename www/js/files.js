(function($) {
$(document).ready(function() {

	// GESTIONE SEZIONI
	$( "#sezioni-dialog-form" ).dialog({
		autoOpen: false,
		height: 500,
		width: 540,
		modal: true,
		open: function() {
			var id = parseInt($("#id_sezione").val());
			if (!id) {
				// Nuova sezione, svuoto i campi
				$("#sezione-blocco-listafile").hide();
				$("#titolo-sezione").val("");
				$('#note-sezione').html('');
				$("#lista-file-sezione").empty();
				$(this).dialog("option", "height", 370);
			}
			else {
				$("#sezione-blocco-listafile").show();
				$(this).dialog("option", "height", 500);
				// Carico i dati dal DB
				caricaListaFile(id);
				$("#file-dialog-form input[name='id_sezione']").val(id)
			}
		},
		buttons: {
			"Annulla": function() {
				$( this ).dialog( "close" );
			},
			"Salva": function() {
				// Salvo i dati nel db
				$("#sezioni-dialog-form").mask("Salvataggio...", 100);
				$.post('ajax/corsi.php?action=savesezione', {
					id: $("#id_sezione").val(),
					id_corso: $("#sezioni-dialog-form [name='id_corso']").val(),
					titolo: $("#titolo-sezione").val(),
					note: $('#note-sezione').html()
					},
					function(data) {
						$("#sezioni-dialog-form").unmask();
						if ( ! data || ! data.success ) {
							// Errore
							var txt = 'Errore';
							if ( data.error )
								txt += ": " + data.error;
							alert(txt);
							return;
						}
						caricaListaSezioni();
						$( "#sezioni-dialog-form" ).dialog( "close" );
					},
				"json"
				);
			}
		}
	});


	$( "#link-nuova-sezione" )
		.click(function() {
			apriDialogoSezione(0);
		});

	$( "#lista-file-sezione" ).sortable({
		axis: 'y',
		distance: 3,
		containment: 'parent',
		cursor: 'move',
		tolerance: 'pointer', /* http://bugs.jqueryui.com/ticket/5772 */
		stop: function(event, ui) {
			salvaOrdineFiles();
		}
	})
		.disableSelection();

	caricaListaSezioni();


	$( "#file-dialog-form" ).dialog({
		autoOpen: false,
		height: 300,
		width: 470,
		modal: true,
		open: function() {
			var id = parseInt($("#id_file").val());
			if (!id) {
				// Nuovo file, svuoto i campi
				$("#titolo-file").val("");
				$("#tipourl_upload").attr('checked', true);
				$("#file-aggiornato").attr('checked', true);
				$("#nascondi-file").attr('checked', false);
				$("#url-file").val("");
				$("#file-dialog-form input[type='file']").val("");
			}
			else {
				// Carico i dati dal DB
				$("#file-dialog-form").mask("Caricamento...", 100);
				$.post('ajax/files.php?action=getfile', {
					id: id
					},
					function(data) {
						$("#file-dialog-form").unmask();
						if ( ! data || ! data.success ) {
							// Errore
							var txt = 'Errore';
							if ( data.error )
								txt += ": " + data.error;
							alert(txt);
							$( "#file-dialog-form" ).dialog( "close" );
							return;
						}
						// Riempio il form
						$("#titolo-file").val(data.titolo);
						$("#tipourl_url").attr('checked', true);
						$("#url-file").val(data.url);
						$("#file-aggiornato").attr('checked', data.aggiornato == true);
						$("#nascondi-file").attr('checked', data.nascondi == true);
						$("#file-dialog-form input[type='file']").val("");
					},
				"json"
				);
			}
		},
		buttons: {
			"Annulla": function() {
				$( this ).dialog( "close" );
			},
			"Salva": function() {
				$('#file-dialog-form form').submit();
			}
		}
	});


	if ($("#file-dialog-form").size()) {
	$('#file-dialog-form form').iframePostForm({
		json: true,
		post: function() {
			$('#file-dialog-form').mask("Salvataggio...",100);
		},
		complete: function(data) {
			$('#file-dialog-form').unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}
			// Tutto OK

			if ( $( "#sezioni-dialog-form" ).dialog("isOpen") ) {
				if (!parseInt($('#id_file').val())) {
					// Aggiorniamo la lista di files nell'altro dialogo
					$("#lista-file-sezione")
						.prepend($("<li />", {
							id: 'file_' + data.id_file,
							css: {display: 'none'}
						}).addClass("ui-corner-all"))
						.find(":first")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:;',
									title: data.nascondi ? 'File nascosto' : 'File visibile',
									click: save_visibility
								}).addClass("iconalink").addClass("eyeicon"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: data.nascondi ? 'File nascosto' : 'File visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:;',
									title: 'Modifica'
								}).addClass("iconalink")
								.click(data.id_file, function(ev){apriDialogoFile(ev.data)})
								)
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_edit.png',
									alt: 'Modifica'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:;',
									title: 'Elimina file',
									click: function() {askEliminaFile(data.id_file);}
								}).addClass("iconalink"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_delete.png',
									alt: 'Elimina file'
									}))
							.parent()
							.append(" " + data.titolo)
						.animate({
							"height": "toggle",
							"opacity": "toggle"
							}, 600);
					salvaOrdineFiles(); // che carica il refresh della lista sezioni
				} // fine nuovo file
				else {
					// modifica file esistente
					caricaListaFile(parseInt($( "#id_sezione" ).val()));
					caricaListaSezioni();
				}
			}
			else {
				caricaListaSezioni();
			}

			$( "#file-dialog-form" ).dialog( "close" );
		}
	});
	}

	$("#url-file").focus(function(event) {
		$("#tipourl_url").attr('checked', true);
	});

	$("#tipourl_url").click(function() {
		$("#url-file").focus();
	});

	function salvaOrdineFiles() {
		$("#lista-file-sezione").mask("Salvataggio...", 100);
		$.get('ajax/files.php?action=savefileorder&id_sezione=' + $("[name='id_sezione']").val() +
			'&' + $('#lista-file-sezione').sortable("serialize"),
			function(data) {
				$("#lista-file-sezione").unmask();
				if (data.success)
					caricaListaSezioni();
			},
			"json"
		);

	}

	function caricaListaFile(id_sezione) {
		$("#sezione-dialog-form").mask("Caricamento...", 100);
		$.post('ajax/corsi.php?action=getsezione', {
			id: id_sezione
			},
			function(data) {
				$("#sezione-dialog-form").unmask();
				if ( ! data || ! data.success ) {
					// Errore
					var txt = 'Errore';
					if ( data.error )
						txt += ": " + data.error;
					alert(txt);
					$( "#sezioni-dialog-form" ).dialog( "close" );
					return;
				}
				// Riempio il form
				$("#titolo-sezione").val(data.titolo);
				$('#note-sezione').html(data.note);
				$("#lista-file-sezione").empty();
				for (var key in data.files) {
					var f = data.files[key];
					$("#lista-file-sezione")
						.append($("<li />", {
							id: 'file_' + f.id_file
						}).addClass("ui-corner-all"))
						.find(":last")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:;',
									title: f.nascondi ? 'File nascosto' : 'File visibile',
									click: save_visibility
								}).addClass("iconalink").addClass("eyeicon"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (f.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: f.nascondi ? 'File nascosto' : 'File visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:;',
									title: 'Modifica'
								}).addClass("iconalink")
								.click(f.id_file, function(ev){apriDialogoFile(ev.data)})
								)
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_edit.png',
									alt: 'Modifica'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:;',
									title: 'Elimina file',
									click: function() {askEliminaFile(f.id_file);}
								}).addClass("iconalink"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_delete.png',
									alt: 'Elimina file'
									}))
							.parent()
							.append(" " + f.titolo);
				}
			},
		"json"
		);
	}


});
})(jQuery);


function apriDialogoSezione(id) {
	jQuery( "#id_sezione" ).val( id );
	jQuery( "#sezioni-dialog-form" ).dialog( "open" );
}

function apriDialogoFile(id) {
	jQuery( "#id_file" ).val( id );
	jQuery( "#file-dialog-form" ).dialog( "open" );
}

function caricaListaSezioni() {
	if ( typeof(id_corso) == 'undefined' ) return;
	jQuery("#lista-sezioni").mask("Caricamento...", 200);
	jQuery("#lista-sezioni")
		.load('ajax/corsi.php?action=loadlistasezioni&id_corso=' + id_corso, function() {
			jQuery("#lista-sezioni").unmask();
		})
}

function eliminaFile(id) {
	var li = $("li#file_" + id);
	li.mask("Eliminazione...", 200);
	jQuery.post('ajax/files.php?action=eliminafile', {
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

				// Faccio sparire il file
				li.animate({
					"height": "toggle",
					"opacity": "toggle"
					}, 600, function() {
						jQuery(this).remove()
					});
				caricaListaSezioni();
			},
		"json"
		);
}


function askEliminaFile(id) {
	jQuery("<p>Si &egrave; sicuri di voler eliminare questo file?</p>")
		.dialog({
			resizable: false,
			height: 130,
			modal: true,
			buttons: {
				"Annulla": function() {
					jQuery( this ).dialog( "close" );
				},
				"Elimina": function() {
					eliminaFile(id);
					jQuery( this ).dialog( "close" );
				}
			}
		});
}
