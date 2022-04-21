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
			alert('Failed to book an appoinment');
			return;
		} else {
			alert("You have successfully booked an appointment");
			window.location.href = "index.php";
		}


		// getting all form data
		// var fname     =   $("#firstname").val();
		// var lname      =   $("#lastname").val();
		// var email  =   $("#email").val();
		// var password = $("#registerpassword").val();
		// var phonenumber = $("#phonenumber").val();
		// var name = $("#name").val();
		// var phone = $("#phone").val();
		// var email = $("#email").val();
		// var address = $("#adress").val();
		// var service = $("#service").val();
		// var date = $("#datepicker").val();
		// var time = $("#timepicker1").val();
		// var message = $("#message").val();
		
		// alert(service + " " + date + " " + message + " " + time + " " + name + " " + phone + " " + email);

		// // sending ajax request
		// // $.ajax({
		// // 	url: './php/book.php',
		// // 	type: 'post',
		// // 	data: {
		// // 			'name' : name,
		// // 			'phone' : phone,
		// // 			'email' : email,
		// // 			'address' : address,
		// // 			'service' : service,
		// // 			'date' : date,
		// // 			'time' : time,
		// // 			'message' : message,
		// // 			'save' : 1
		// // 	},
		// // // 	// on success response
		// // 	success:function(response) {
		// // 		// $("#result").html(response);

		// // // 		// reset form fields
		// // 		// $("#RegForm")[0].reset();
		// // 		alert('work');
		// // 	},

		// // // 	// error response
		// // 	error:function(e) {
		// // 		// $("#result").html("Some error encountered.");
		// // 		alert('error');
		// // 	}

		// // });
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

		if($("#adress").val() == "") {
			$("adress").after("<span id='error' class='text-danger'> Enter your Home Address.</span>");
			return 0;
		}
		if($("#phone").val() == "") {
			$("#phone").after("<span id='error' class='text-danger'> Enter your Contact Phone Number. </span>");
			return 0;
		}

		if($("#service").val() == "") {
			$("#service").after("<span id='error' class='text-danger'> Enter what kind of service you desire </span>");
			return 0;
		}


		if($("#datepicker").val() == "") {
			$("#datepicker").after("<span id='error' class='text-danger'> Enter Desired Date </span>");
			return 0;
		}

		if($("#timepicker1").val() == "") {
			$("#timepicker1").after("<span id='error' class='text-danger'> Enter Desired Time</span>");
			return 0;
		}
		if($("#message").val() == "") {
			$("#message").after("<span id='error' class='text-danger'> Elaborate your problem. </span>");
			return 0;
		}

		return 1;

	}


// ------------------- [ Email blur function ] -----------------

	// $("#email").blur(function() {

	// 	var email  		= 		$('#email').val();

	// 	// if email is empty then return
	// 	if(email == "") {
	// 		return;
	// 	}


	// // 	// send ajax request if email is not empty
	// 	$.ajax({
	// 			url: './php/book.php',
	// 			type: 'post',
	// 			data: {
	// 				'email':email,
	// 				'email_check':1,
	// 		},

	// 		success:function(response) {	

	// 			// clear span before error message
	// 			$("#email_error").remove();

	// 			// adding span after email textbox with error message
	// 			$("#email").after("<span id='email_error' class='text-danger'>"+response+"</span>");
	// 		},

	// 		error:function(e) {
	// 			$("#result").html("Something went wrong");
	// 		}

	// 	});
	// });
// -----------[ Clear span after clicking on inputs] -----------

$("#username").keyup(function() {
	$("#error").remove();
});


$("#email").keyup(function() {
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