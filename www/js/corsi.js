(function($) {
$(document).ready(function() {

	// Gestione click crocette/radio buttons per i docenti
	$("#tbl_insert_docenti [id^='docente_']").click(function(event) {
		if ($(this).attr('checked'))
			$($("#tbl_insert_docenti [name='tipodocente_" + $(this).val() + "']")[0]).attr('checked', true);
		else
			$("#tbl_insert_docenti [name='tipodocente_" + $(this).val() + "']").attr('checked', false)
	});

	$("#tbl_insert_docenti [name^='tipodocente_']").click(function(event) {
		var id = $(this).attr('name').split('_')[1];

		$("#docente_" + id).attr('checked', true);
	});


	// Validazione form corso
	var validator = $("#frm_corso").validate({
		rules: {
			nome: "required"
		},
		// Dove mettiamo gli errori
		errorPlacement: function(error, element) {
			element.after(error);
		}
	});



	var opzioni = {
		script_url : 'js/tiny_mce/tiny_mce.js',
		language: 'it',
		theme: 'advanced',
		plugins: "inlinepopups,insertdatetime,paste,fullscreen",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,|,forecolor,backcolor,|,sub,sup",
		theme_advanced_buttons2 : "cut,copy,paste,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,image,cleanup,removeformat,code,|,insertdate,|,fullscreen",
		theme_advanced_buttons3 : '',
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_path : false,
		theme_advanced_resizing : true,
		plugin_insertdate_dateFormat : "%d/%m/%Y",
	}
	$('textarea.tinymce').tinymce(opzioni);

        $("#facolta-dialog-form").dialog({
            autoOpen: false,
		height: 200,
		width: 600,
		modal: true,
                buttons: {
			"Chiudi": function() {
				$( this ).dialog( "close" );
			}
		}
        });
        
        $( "#link-nome-facolta" )
		.click(function() {
			$( "#facolta-dialog-form" ).dialog( "open" );
		});
                
        $("#docente-dialog-form").dialog({
            autoOpen: false,
		height: 200,
		width: 600,
		modal: true,
                buttons: {
			"Chiudi": function() {
				$( this ).dialog( "close" );
			}
		}
        });
        
        $( "#link-nome-docente" )
		.click(function() {
			$( "#docente-dialog-form" ).dialog( "open" );
		});
        
	$( "#news-dialog-form" ).dialog({
		autoOpen: false,
		height: 500,
		width: 600,
		modal: true,
		open: function() {
			$("#news-dialog-form [name='id_news']").val('');
			$('#testo-news').html('');
			$('#hide-news').attr('checked', false);
			$('#attachment').val('');
			if (typeof($('#testo-news').tinymce) != "undefined")
				$('#testo-news').tinymce().focus();
		},
		buttons: {
			"Chiudi": function() {
				$( this ).dialog( "close" );
			}
		}
	});

	$( "#link-gestione-news" )
		.click(function() {
			$( "#news-dialog-form" ).dialog( "open" );
		});

	$( "#lista-news" ).sortable({
		axis: 'y',
		distance: 3,
		containment: 'parent',
		cursor: 'move',
		tolerance: 'pointer', /* http://bugs.jqueryui.com/ticket/5772 */
		stop: function(event, ui) {
			$("#lista-news").mask("Salvataggio...", 200);

			$.get('ajax/news.php?action=savenewsorder&id_corso=' + $("[name='id_corso']").val() +
				'&' + $('#lista-news').sortable("serialize"),
				function(data) {
					$("#lista-news").unmask();
				}
			);
		}
	})
		.disableSelection();

	$("#btn-salva-news").click(function() {
		//$("#news-dialog-form form").submit();
		// !? Come mai non lo devo assegnare? Mistero.
	});

	var swap_eye_icon = function( data ) {
		
	}

	var save_visibility = function(elem) {
		var txt_nascosto, txt_visibile;
		var li_id = $(elem.currentTarget).parent("li").attr("id");
		var obj_type = li_id.split("_")[0];
		if (obj_type == 'file') {
			txt_nascosto = "File nascosto";
			txt_visibile = "File visibile";
		}
		else {
			txt_nascosto = "News nascosta";
			txt_visibile = "News visibile";
		}

		$("#" + li_id).mask("Caricamento...", 200);
		$.post('ajax/corsi.php?action=togglevisibility', {
			id: li_id.split("_")[1],
			obj_type: obj_type
			},
			function (data) {
				$("#" + li_id).unmask();
				if ( ! data || ! data.success ) {
					// Errore
					var txt = 'Errore';
					if ( data.error )
						txt += ": " + data.error;
					alert(txt);
					return;
				}
				// Cambio l'immaginetta
				$(elem.currentTarget)
					.find("img")
					.attr("src", 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'))
					.attr("alt", data.nascondi ? txt_nascosto : txt_visibile)
				.parent()
					.attr("title", data.nascondi ? txt_nascosto : txt_visibile);
				if (obj_type == 'file')
					caricaListaSezioni();
			},
		"json"
		);
	};


	$(".eyeicon").click(save_visibility)
        
        if ($("#facolta-dialog-form").size()) {
            $('#facolta-dialog-form form').iframePostForm({
		json: true,
		post: function() {
			$('#facolta-dialog-form').mask("Salvataggio...", 200);
		},
		complete: function(data) {
			$('#facolta-dialog-form').unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}
			else {
                            var nuovonome = $( "#facolta-dialog-form form #nome-facolta").val();
                            $('#link-nome-facolta').parent().before("<li><input type='radio' name='facolta' id='facolta_"+data.id_facolta+"' value='"+data.id_facolta+"' checked='checked'/><label for='facolta_"+data.id_facolta+"'> " + nuovonome + "</label></li>");
                            $( "#facolta-dialog-form" ).dialog( "close" );
                        }
                }
            });
        }
        
        if ($("#docente-dialog-form").size()) {
            $('#docente-dialog-form form').iframePostForm({
		json: true,
		post: function() {
			$('#docente-dialog-form').mask("Salvataggio...", 200);
		},
		complete: function(data) {
			$('#docente-dialog-form').unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}
			else {
                            var nuovonome = $( "#docente-dialog-form form #nome-docente").val();
                            $('#link-nome-docente').parent().parent().before("<tr><td><input type='checkbox' name='docente' id='docente_"+data.id_docente+"' value='"+data.id_docente+"' checked='checked'/><label for='docente_"+data.id_docente+"'> " + nuovonome + "</label></td><td class='option'><input type='radio' name='tipodocente_"+data.id_docente+"' value='0' checked='checked'/></td><td class='option'><input type='radio' name='tipodocente_"+data.id_docente+"' value='1' $checked/></td></tr>");
                            $( "#docente-dialog-form" ).dialog( "close" );
                        }
                }
            });
        }
        

	if ($("#news-dialog-form").size()) {
	$('#news-dialog-form form').iframePostForm({
		json: true,
		post: function() {
			$('#news-dialog-form').mask("Salvataggio...", 200);
			// Probabilmente eliminabile, ma non si sa mai, un refresh fa sempre bene.
			tinyMCE.triggerSave();
		},
		complete: function(data) {
			$('#news-dialog-form').unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}
			else {
				// Tutto OK
				if ($("#news-dialog-form [name='id_news']").val()) {
					// Modifica di una news gia' esistente
					var li = $("li#news_" + data.id);
					li.find(".eyeicon img")
						.attr("src", 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'))
						.attr("alt", data.nascondi ? 'News nascosta' : 'News visibile')
						.parent().attr("title", data.nascondi ? 'News nascosta' : 'News visibile');
					li.find(".testo").html(data.testo);
				}
				else {
					// Inserimento di una nuova news
					$("#lista-news")
						.prepend($("<li />", {
							class: 'ui-corner-all',
							id: 'news_' + data.id,
							css: {display: 'none'} // per farlo comparire dopo
						}))
						.find(":first")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink eyeicon',
									title: data.nascondi ? 'News nascosta' : 'News visibile',
									click: save_visibility
								}))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: data.nascondi ? 'News nascosta' : 'News visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink',
									title: 'Modifica news',
									click: function() {caricaNews(data.id)}
								}))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/newspaper_edit.png',
									alt: 'Modifica news'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink',
									title: 'Elimina news',
									click: function() {askEliminaNews(data.id);}
								}))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/newspaper_delete.png',
									alt: 'Elimina news'
									}))
							.parent()
							.append(" ")
							.append($("<span />", {
								class: 'testo'
								})
								.append(data.testo)
							)
						.animate({
							"height": "toggle",
							"opacity": "toggle"
							}, 600);
				}

				// Svuoto il form.
				$('#testo-news').html('');
				$('#hide-news').attr('checked', false);
				$('#attachment').val('');
				$("#news-dialog-form [name='id_news']").val('');
			}
		}
	});
	}

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
		width: 600,
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
							class: 'ui-corner-all',
							id: 'file_' + data.id_file,
							css: {display: 'none'}
						}))
						.find(":first")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink eyeicon',
									title: data.nascondi ? 'File nascosto' : 'File visibile',
									click: save_visibility
								}))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: data.nascondi ? 'File nascosto' : 'File visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink',
									title: 'Modifica'
								})
								.click(data.id_file, function(ev){apriDialogoFile(ev.data)})
								)
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_edit.png',
									alt: 'Modifica'
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

	$("#url-file").focus(function() {
		$("#tipourl_url").attr('checked', true);
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
							class: 'ui-corner-all',
							id: 'file_' + f.id_file
						}))
						.find(":last")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink eyeicon',
									title: f.nascondi ? 'File nascosto' : 'File visibile',
									click: save_visibility
								}))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (f.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: f.nascondi ? 'File nascosto' : 'File visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									class: 'iconalink',
									title: 'Modifica'
								})
								.click(f.id_file, function(ev){apriDialogoFile(ev.data)})
								)
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/page_edit.png',
									alt: 'Modifica'
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

function caricaNews(id) {
	$('#news-dialog-form').mask("Salvataggio...", 200);
	jQuery.post('ajax/news.php?action=getnews', {
			id: id
			},
			function (data) {
				$("#news-dialog-form").unmask();
				if ( ! data || ! data.success ) {
					// Errore
					var txt = 'Errore';
					if ( data.error )
						txt += ": " + data.error;
					alert(txt);
					return;
				}
				// Carico i dati nel form
				jQuery('#testo-news').html(data.testo);
				jQuery('#hide-news').attr('checked', data.nascondi == '1');
				jQuery('#attachment').val('');
				jQuery('#testo-news').tinymce().focus();
				jQuery("#news-dialog-form [name='id_news']").val(data.id_news);
			},
		"json"
		);
}

function eliminaNews(id) {
	var li = $("li#news_" + id);
	li.mask("Eliminazione...", 200);
	jQuery.post('ajax/news.php?action=eliminanews', {
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
				// Race condition:
				// Se sto modificando una news, e prima di salvarla clicco su
				// elimina? Cercherei di updatare una news non piu' valida,
				// generando un errore.
				// Soluzione: se l'id della news appena eliminata e' uguale a
				// quello della news correntemente in modifica, azzerare quest'ultimo.
				// cosi' facendo verra' creata una nuova news.
				if ( data.id == jQuery("#news-dialog-form [name='id_news']").val())
					jQuery("#news-dialog-form [name='id_news']").val('');

				// Faccio sparire la news
				li.animate({
							"height": "toggle",
							"opacity": "toggle"
							}, 600, function() { jQuery(this).remove(); });
			},
		"json"
		);
}

function askEliminaNews(id) {
	jQuery("<p>Si &egrave; sicuri di voler eliminare questa news?</p>")
		.dialog({
			resizable: false,
			height: 130,
			modal: true,
			buttons: {
				"Annulla": function() {
					jQuery( this ).dialog( "close" );
				},
				"Elimina": function() {
					eliminaNews(id);
					jQuery( this ).dialog( "close" );
				}
			}
		});
}
