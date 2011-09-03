(function($) {
$(document).ready(function() {

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
			$("#news-dialog-form fieldset").removeClass('esteso');
			$("#news-saved-attachment").hide();

			if (typeof($('#testo-news').tinymce) != "undefined")
				$('#testo-news').tinymce().focus();
			if ($("#lista-news").find("li").length == 0)
				$("#avviso-no-news").show();
			else
				$("#avviso-no-news").hide();
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
					if ($("#lista-news").find("li").length == 0)
						$("#avviso-no-news").hide();
					$("#lista-news")
						.prepend($("<li />", {
							id: 'news_' + data.id,
							css: {display: 'none'} // per farlo comparire dopo
						}).addClass("ui-corner-all"))
						.find(":first")
							.append("<span class='ui-icon ui-icon-arrowthick-2-n-s' />")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									title: data.nascondi ? 'News nascosta' : 'News visibile',
									click: save_visibility
								}).addClass("iconalink").addClass("eyeicon"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/' + (data.nascondi ? 'eye_no.png' : 'eye.png'),
									alt: data.nascondi ? 'News nascosta' : 'News visibile'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									title: 'Modifica news',
									click: function() {caricaNews(data.id)}
								}).addClass("iconalink"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/newspaper_edit.png',
									alt: 'Modifica news'
									}))
							.parent()
							.append(" ")
							.append($("<a/>", {
									href: 'javascript:void(0)',
									title: 'Elimina news',
									click: function() {askEliminaNews(data.id);}
								}).addClass("iconalink"))
								.find(":last")
								.append($("<img>", {
									src: 'img/icone/newspaper_delete.png',
									alt: 'Elimina news'
									}))
							.parent()
							.append(" ")
							.append(
								$("<span />")
									.addClass("testo")
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


});
})(jQuery);


function caricaNews(id) {
	$('#news-dialog-form').mask("Caricamento...", 200);
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
				if (data.file) {
					jQuery("#news-dialog-form fieldset").addClass('esteso');
					jQuery("#news-saved-attachment").show();
				}
				else {
					jQuery("#news-dialog-form fieldset").removeClass('esteso');
					jQuery("#news-saved-attachment").hide();
				}

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
					}, 600, function() {
						jQuery(this).remove()
						if (jQuery("#lista-news").find("li").length == 0)
							$("#avviso-no-news").show();
					});
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

