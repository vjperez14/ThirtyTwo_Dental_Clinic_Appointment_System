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
			window.location.href = "index.php";
		}


		// getting all form data
		// var fname     =   $("#firstname").val();
		// var lname      =   $("#lastname").val();
		// var email  =   $("#email").val();
		// var password = $("#registerpassword").val();
		// var phonenumber = $("#phonenumber").val();
		var name     =   $("#name").val();
		var phone     =   $("#phone").val();
		var email  =   $("#email").val();
		var address = $("#hadress").val();
		var service = $("#service").val();
		var date = $("#datepicker").val();
		var time = $("#timepicker1").val();
		var issue = $("#message").val();
		


		// sending ajax request
		$.ajax({

			url: '../php/book.php',
			type: 'post',
			data: {
					'name' : name,
					'phone' : lname,
					'email' : email,
					'address' : address,
					'service' : service,
					'date' : date,
					'time' : time
					'issue' : issue,
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


		// validating input if empty
		if($("#email").val() == "") {
			$("#email").after("<span id='error' class='text-danger'> Enter your email </span>");
			return 0;
		}

		if($("#hadress").val() == "") {
			$("#hadress").after("<span id='error' class='text-danger'> Enter your password </span>");
			return 0;
		}
		if($("#phone").val() == "") {
			$("#phone").after("<span id='error' class='text-danger'> Enter your password </span>");
			return 0;
		}
		if($("#service").val() == "") {
			$("#service").after("<span id='error' class='text-danger'> Enter your password </span>");
			return 0;
		}


		if($("#datepicker").val() == "") {
			$("#datepicker").after("<span id='error' class='text-danger'> Re-enter your password </span>");
			return 0;
		}

		if($("#timepicker1").val() == "") {
			$("#timepicker1").after("<span id='error' class='text-danger'> Password not matched! </span>");
			return 0;
		}
		if($("#message").val() == "") {
			$("#message").after("<span id='error' class='text-danger'> Password not matched! </span>");
			return 0;
		}

		return 1;

	}


// ------------------- [ Email blur function ] -----------------

	$("#email").blur(function() {

		var email  		= 		$('#email').val();

		// if email is empty then return
		if(email == "") {
			return;
		}


		// send ajax request if email is not empty
		$.ajax({
				url: '../php/book.php',
				type: 'post',
				data: {
					'email':email,
					'email_check':1,
			},

			success:function(response) {	

				// clear span before error message
				$("#email_error").remove();

				// adding span after email textbox with error message
				$("#email").after("<span id='email_error' class='text-danger'>"+response+"</span>");
			},

			error:function(e) {
				$("#result").html("Something went wrong");
			}

		});
	});
// -----------[ Clear span after clicking on inputs] -----------

// $("#username").keyup(function() {
// 	$("#error").remove();
// });


// $("#email").keyup(function() {
// 	$("#error").remove();
// 	$("span#email_error").remove();
// });

// $("#registerpassword").keyup(function() {
// 	$("#error").remove();
// });

// $("#confirmpassword").keyup(function() {
// 	$("#error").remove();
// });

// }); 