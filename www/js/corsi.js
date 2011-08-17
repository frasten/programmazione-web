(function($) {
$(document).ready(function() {

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


	$( "#news-dialog-form" ).dialog({
		autoOpen: false,
		height: 500,
		width: 600,
		modal: true,
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
			// TODO: dare un feedback grafico dei lavori in corso

			$.get('ajax/corsi.php?action=saveorder&id_corso=' + $("[name='id_corso']").val() +
				'&' + $('#lista-news').sortable("serialize"),
				function(data) {
					$('.result').html(data);
				}
			);
		}
	})
		.disableSelection();

	$("#btn-salva-news").click(function() {
		//$("#news-dialog-form form").submit();
		// !? Come mai non lo devo assegnare? Mistero.
	});

	var save_visibility = function(elem) {
		$.post('ajax/corsi.php?action=togglevisibility', {
			id_news: $(elem.currentTarget).parent("li").attr("id").split("_")[1]
			},
			function(data) {
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
					.attr("alt", data.nascondi ? 'News nascosta' : 'News visibile')
				.parent()
					.attr("title", data.nascondi ? 'News nascosta' : 'News visibile')
			},
		"json"
		);
	};


	$(".eyeicon").click(save_visibility)


	$('#news-dialog-form form').iframePostForm({
		json: true,
		post: function() {
			// Probabilmente eliminabile, ma non si sa mai, un refresh fa sempre bene.
			tinyMCE.triggerSave();
			console.log("Caricamento...");
		},
		complete: function(data) {
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
						.append(" " + data.testo)
					.animate({
						"height": "toggle",
						"opacity": "toggle"
						}, 600);

				// Svuoto il form.
				$('#testo-news').html('');
				$('#hide-news').attr('checked', false);
				$('#attachment').val('')
			}
		}
	});

});
})(jQuery);
