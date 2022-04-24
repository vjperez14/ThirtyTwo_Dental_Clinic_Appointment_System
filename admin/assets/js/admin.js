function approve(apt_id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to approve this appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Approve'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "./assets/php/process_admin.php?apt_id="+apt_id+"&event=approve";
        }
    })
}

function complete(apt_id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This appointment is done?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Complete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "./assets/php/process_admin.php?apt_id="+apt_id+"&event=complete";
        }
    })
}

function decline(apt_id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this appointment?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Decline'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "./assets/php/process_admin.php?apt_id="+apt_id+"&event=decline";
        }
    })
}