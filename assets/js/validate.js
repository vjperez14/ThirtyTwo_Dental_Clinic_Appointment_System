$(document).ready(function() {

	// form autocomplete off		
	$(":input").attr('autocomplete', 'off');

	// remove box shadow from all text input
	$(":input").css('box-shadow', 'none');



	// save button click function
	$("#savebtn").click(function() {

		// calling validate function
		var response =  validateForm();
		
		// alert("test");
		// if form validation fails			
		if(response == 0) {
			return;
		} else {
			window.location.href = "registersuccess.php";
		}


		// getting all form data
		var fname     =   $("#firstname").val();
		var lname      =   $("#lastname").val();
		var email  =   $("#registeremail").val();
		var password = $("#registerpassword").val();
		var phonenumber = $("#phonenumber").val();
		


		// sending ajax request
		$.ajax({

			url: './php/registration.php',
			type: 'post',
			data: {
					'firstname' : fname,
					'lastname' : lname,
					'registeremail' : email,
					'registerpassword' : password,
					'phonenumber' : phonenumber,
					'save' : 1
				},

			// before ajax request
			beforeSend: function() {
				$("#result").html("<p class='text-success'> Please wait.. </p>");
			},	

			// on success response
			success:function(response) {
				$("#result").html(response);

				// reset form fields
				$("#RegForm")[0].reset();
			},

			// error response
			error:function(e) {
				$("#result").html("Some error encountered.");
			}

		});
	});




// ------------- form validation -----------------

	function validateForm() {

		// removing span text before message
		$("#error").remove();

		if($("#firstname").val() == "") {
			$("#firstname").after("<span id='error' class='text-danger'>Enter your First Name</span>");
			return 0;
		}

		if ($("#lastname").val() == "") {
			$("#lastname").after("<span id='error' class='text-danger'>Enter your Last Name</span>");
			return 0;
		}


		// validating input if empty
		if($("#registeremail").val() == "") {
			$("#registeremail").after("<span id='error' class='text-danger'> Enter your email </span>");
			return 0;
		}

		if($("#registerpassword").val() == "") {
			$("#registerpassword").after("<span id='error' class='text-danger'> Enter your password </span>");
			return 0;
		}

		if($("#confirmpassword").val() == "") {
			$("#confirmpassword").after("<span id='error' class='text-danger'> Re-enter your password </span>");
			return 0;
		}

		if($("#confirmpassword").val() != $("#registerpassword").val()) {
			$("#confirmpassword").after("<span id='error' class='text-danger'> Password not matched! </span>");
			return 0;
		}

		return 1;

	}


// ------------------- [ Email blur function ] -----------------

	$("#registeremail").blur(function() {

		var email  		= 		$('#registeremail').val();

		// if email is empty then return
		if(email == "") {
			return;
		}


		// send ajax request if email is not empty
		$.ajax({
				url: './php/registration.php',
				type: 'post',
				data: {
					'email':email,
					'email_check':1,
			},

			success:function(response) {	

				// clear span before error message
				$("#registeremail_error").remove();

				// adding span after email textbox with error message
				$("#registeremail").after("<span id='email_error' class='text-danger'>"+response+"</span>");
			},

			error:function(e) {
				$("#result").html("Something went wrong");
			}

		});
	});
// -----------[ Clear span after clicking on inputs] -----------

$("#username").keyup(function() {
	$("#error").remove();
});


$("#registeremail").keyup(function() {
	$("#error").remove();
	$("span#email_error").remove();
});

$("#registerpassword").keyup(function() {
	$("#error").remove();
});

$("#confirmpassword").keyup(function() {
	$("#error").remove();
});

}); 