(function($) {
$(document).ready(function() {

	// Tolgo il tipo di docente se necessario, o ne imposto uno di default,
	// su evento 'abilita docente'
	function autoRadioDocenti(event) {
		if ($(this).attr('checked'))
			$($("#tbl_insert_docenti [name='tipodocente_" + $(this).val() + "']")[0]).attr('checked', true);
		else
			$("#tbl_insert_docenti [name='tipodocente_" + $(this).val() + "']").attr('checked', false)
	}

	// Abilito automaticamente il docente su click sul tipo docente
	function autoCheckboxDocenti(event) {
		var id = $(this).attr('name').split('_')[1];
		$("#docente_" + id).attr('checked', true);
	}


	$("#tbl_insert_docenti [id^='docente_']").click(autoRadioDocenti);
	$("#tbl_insert_docenti [name^='tipodocente_']").click(autoCheckboxDocenti);


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


	// TinyMCE (editor WYSIWYG)
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
	if ($('textarea.tinymce').length > 0)
		$('textarea.tinymce').tinymce(opzioni);


	// Inserimento nuova Facolta'
	$("#facolta-dialog-form").dialog({
		autoOpen: false,
		height: 200,
		width: 600,
		modal: true,
		buttons: {
			"Annulla": function() {
				$( this ).dialog( "close" );
			},
			"Salva": function() {
				$( this ).find( "form" ).submit();
			}
		}
	});

	$( "#link-nome-facolta" )
		.click(function() {
			$( "#facolta-dialog-form" ).dialog( "open" );
		});


	// Inserimento nuovo docente
	$("#docente-dialog-form").dialog({
		autoOpen: false,
		height: 200,
		width: 600,
		modal: true,
		buttons: {
			"Annulla": function() {
				$( this ).dialog( "close" );
			},
			"Salva": function() {
				$( this ).find( "form" ).submit();
			}
		}
	});

	$( "#link-nome-docente" )
		.click(function() {
			$( "#docente-dialog-form" ).dialog( "open" );
		});


	// Dialogo per l'inserimento di nuove facolta'
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
				$("#nome-facolta").focus();
				return;
			}
			else {
				var nuovonome = $( "#facolta-dialog-form form #nome-facolta").val();
				$('#link-nome-facolta')
					.parent()
					.before("<li><input type='radio' name='facolta' id='facolta_"+data.id_facolta+"' value='"+data.id_facolta+"' checked='checked'/><label for='facolta_"+data.id_facolta+"'> " + nuovonome + "</label></li>");
				$( "#facolta-dialog-form form #nome-facolta").val('');
				$( "#facolta-dialog-form" ).dialog( "close" );
			}
			}
		});
	}

	// Dialogo per l'inserimento di nuovi docenti
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
					$("#nome-docente").focus()
					return;
				}
				else {
					var nuovonome = $( "#docente-dialog-form form #nome-docente").val();
					$('#link-nome-docente')
						.parent().parent()
						.before("<tr><td><input type='checkbox' name='docente' id='docente_"+data.id_docente+"' value='"+data.id_docente+"' checked='checked'/><label for='docente_"+data.id_docente+"'> " + nuovonome + "</label></td><td class='option'><input type='radio' name='tipodocente_"+data.id_docente+"' value='0' checked='checked'/></td><td class='option'><input type='radio' name='tipodocente_"+data.id_docente+"' value='1' /></td></tr>");
					// Assegno gli eventi
					$("#tbl_insert_docenti [id^='docente_']").filter(":last").click(autoRadioDocenti);
					$("#tbl_insert_docenti [name^='tipodocente_']").slice(-2).click(autoCheckboxDocenti);

					$( "#docente-dialog-form form #nome-docente").val('');
					$( "#docente-dialog-form" ).dialog( "close" );
				}
			}
		});
	}

	$(".eyeicon").click(save_visibility)

});
})(jQuery);


function eliminaCorso(id) {
	var li = $("li#corso_" + id);
	li.mask("Eliminazione...", 200);
	jQuery.post('ajax/corsi.php?action=eliminacorso', {
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

				// Faccio sparire il corso
				li.animate({
							"height": "toggle",
							"opacity": "toggle"
							}, 600, function() { jQuery(this).remove(); });
			},
		"json"
		);
}

function askEliminaCorso(id) {
	jQuery("<p>Si &egrave; sicuri di voler eliminare questo corso?</p>")
		.dialog({
			resizable: false,
			height: 130,
			modal: true,
			buttons: {
				"Annulla": function() {
					jQuery( this ).dialog( "close" );
				},
				"Elimina": function() {
					eliminaCorso(id);
					jQuery( this ).dialog( "close" );
				}
			}
		});
}


function save_visibility(elem) {
	var txt_nascosto, txt_visibile;
	var li_id = jQuery(elem.currentTarget).parent("li").attr("id");
	var obj_type = li_id.split("_")[0];
	if (obj_type == 'file') {
		txt_nascosto = "File nascosto";
		txt_visibile = "File visibile";
	}
	else {
		txt_nascosto = "News nascosta";
		txt_visibile = "News visibile";
	}

	jQuery("#" + li_id).mask("Caricamento...", 200);
	jQuery.post('ajax/corsi.php?action=togglevisibility', {
		id: li_id.split("_")[1],
		obj_type: obj_type
		},
		function (data) {
			jQuery("#" + li_id).unmask();
			if ( ! data || ! data.success ) {
				// Errore
				var txt = 'Errore';
				if ( data.error )
					txt += ": " + data.error;
				alert(txt);
				return;
			}
			// Cambio l'immaginetta
			jQuery(elem.currentTarget)
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


