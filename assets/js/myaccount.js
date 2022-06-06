function cancelappt(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to cancel your appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        cancelButtonText: 'No',
        confirmButtonText: 'Yes'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "./assets/php/updateappt.php?apt_id=" + id + "&event=cancel";
        }
    })
}

function updateappt(id, date, timeslot) {
    // getting all form data
    var id = id;
    // var date = $('#date').val();
    // var time = $('#time').val();
    var date = date.value;
    var time = timeslot.value;

    if (date == "") {
        Swal.fire({
            icon: 'error',
            title: 'Opps...',
            text: 'Please select a date.',
            timer: 1500,
            showConfirmButton: false,
            allowOutsideClick: false
        });
    } else if (timeslot.options[0].selected) {
        Swal.fire({
            icon: 'error',
            title: 'Opps...',
            text: 'Please select a time slot.',
            timer: 1500,
            showConfirmButton: false,
            allowOutsideClick: false
        });
    } else {
        $.ajax({
            url: './assets/php/update_apt.php',
            type: 'POST',
            data: {
                'id': id,
                'date': date,
                'time': time,
                'save': 1
            },
            cache: false,
            beforeSend: function () {
                Swal.fire({
                    icon: 'info',
                    title: 'Pleas Wait...',
                    text: 'Submitting your form',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            },
            success: function (response) {
                if (response == "taken") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: 'Your chosen time slot is already taken',
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else if (response == "late") {
                    Swal.fire({
                        icon: 'error',
                        title: 'Opps...',
                        text: "It's Already: " + formatAMPM(new Date),
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Update success',
                        text: response,
                        timer: 3000,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "myaccount.php";
                    });
                }
            },
            error: function (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Opps...',
                    text: 'Something went wrong',
                    timer: 1500,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
            }
        });
    }

}

$(document).ready(function () {
    // $('#updateapt').click(function () {
        
    // });

    

    // Getting time as 12 hour format
	function formatAMPM(date) {
		var hours = date.getHours();
		var minutes = date.getMinutes();
		var ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12;
		hours = hours ? hours : 12; // the hour '0' should be '12'
		minutes = minutes < 10 ? '0'+minutes : minutes;
		var strTime = hours + ':' + minutes + ' ' + ampm;
		return strTime;
	}
    $('div.reschedmodal').on('hidden.bs.modal', function () {
        $(this).find("input.date").val('').end();
        $('div.time-container').html("<h3 class='text-center'>Please Select a date...</h3>");
    
    });
    // fetch time slots available
    $('input.date').change(function () {
        // console.log("changed");
        // console.log($(this).val());
        var date = new Date($(this).val());
        var day = date.getDate();
        var month = date.getMonth() + 1;
        var year = date.getFullYear();
        var selectedDate = [year, month, day].join('-');
        // console.log(selectedDate);
        $.ajax({
            type: "POST",
            url: "./assets/php/fetch_time_slots.php",
            data: { date: selectedDate },
            dataType: 'html',
            success: function (data) {
                $('div.time-container').html(data);
            },
        });

    });
});
