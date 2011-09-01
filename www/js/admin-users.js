(function($) {
$(function() {
	$("#frm-edit-password").validate({
		rules: {
			oldpassword: "required",
			password: {
				required: true,
				minlength: 5
			},
			repeatpassword: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			}
		},
		messages: {
			repeatpassword: {
				equalTo: "Le password non coincidono."
			}
		},

		// Dove mettiamo gli errori
		errorPlacement: function(error, element) {
			error.appendTo( element.parent() );
		}
	});


	$("#frm-new-user").validate({
		rules: {
			username: "required",
			password: {
				required: true,
				minlength: 5
			},
			repeatpassword: {
				required: true,
				minlength: 5,
				equalTo: "#password"
			}
		},
		messages: {
			repeatpassword: {
				equalTo: "Le password non coincidono."
			}
		},

		// Dove mettiamo gli errori
		errorPlacement: function(error, element) {
			error.appendTo( element.parent() );
		}
	});

});
})(jQuery);

