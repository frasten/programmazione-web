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
		containment: 'parent'
	});
	$( "#lista-news" ).disableSelection();

});
})(jQuery);
