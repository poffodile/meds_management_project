// $.ajaxSetup({
//     headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     }
// });

$('#saveAttachmentType').on('click', function () {
    event.preventDefault();
    var form = document.getElementById('imageUploadForm');
    var imageInput = document.getElementById('file');
    var errorMessage = document.getElementById('error-message');
    errorMessage.textContent = '';

    // Check if an image is selected
    if (!imageInput.files || imageInput.files.length === 0) {
        event.preventDefault(); // Prevent form submission
        errorMessage.textContent = 'Please upload an image.';
    } else {
        console.log("submit form");
        // var formData = $('#imageUploadForm').serialize();
        var formData = new FormData(form);

        $.ajax({
            url: saveLeadAttachmentUrl,
            method: 'POST',
            contentType: false, // Prevent jQuery from setting Content-Type header
            processData: false, // Prevent jQuery from processing data
            data: formData,
            success: function (response) {
                alert(response.message);
                $('#attechmentModel').modal('hide');
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
});

document.getElementById('submit_main_form').addEventListener('click', function() {
    const formID = document.getElementById('add_leads_form');
    const fullNameInput = document.getElementById('inputName');
    const emailInput = document.getElementById('inputEmail');
    const phoneInput = document.getElementById('inputTelephone');

    const fullNameError = document.getElementById('fullNameError');
    const emailError = document.getElementById('emailError');
    const phoneError = document.getElementById('phoneError');
    console.log("check ");
    let valid = true;
    // Full Name validation
    const fullNameRegex = /^[a-zA-Z\s]+$/;
    if (!fullNameRegex.test(fullNameInput.value)) {
        fullNameError.textContent = 'Please enter a valid full name (letters and spaces only).';
        valid = false;
    } else {
        fullNameError.textContent = '';
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailInput.value)) {
        emailError.textContent = 'Please enter a valid email address.';
        valid = false;
    } else {
        emailError.textContent = '';
    }

    // Telephone validation
    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(phoneInput.value)) {
        phoneError.textContent = 'Please enter a valid 10-digit telephone number.';
        valid = false;
    } else {
        phoneError.textContent = '';
    }

    if (valid == true) {
        // e.preventDefault(); // Prevent form submission if any field is invalid
        formID.submit();
    } 
});

document.addEventListener('DOMContentLoaded', function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var inputField = document.getElementById('lead_id');
    var hiddenDiv = document.getElementById('hiddenDiv');

    if (inputField.value.trim() !== '') {
        hiddenDiv.style.display = 'block'; // Show the div
    } else {
        hiddenDiv.style.display = 'none'; // Hide the div if input is empty
    }


    $('#addNotesType').on('click', function () {

        var title = document.getElementById('title').value;
        var status = document.getElementById('status').value;
        $.ajax({
            url: addNotesTypeURL,
            method: 'POST',
            data: {
                title: title,
                status: status
            },
            success: function (response) {
                alert(response.message);
                $('#notesModel').modal('hide');
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    $('#saveLeadNotes').on('click', function () {
        var notes_type = document.getElementById('notes_type').value;
        var notes = document.getElementById('notes').value;
        var lead_id = document.getElementById('lead_id').value;

        $.ajax({
            url: saveLeadNotes,
            method: 'POST',
            data: {
                notes_type_id: notes_type,
                notes: notes,
                lead_id: lead_id
            },
            success: function (response) {
                alert(response.message);
                $('#notesModel').modal('hide');
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    var mainCheckbox = document.getElementById('yeson');
    var optionsDiv = document.getElementById('optionsDiv');

    mainCheckbox.addEventListener('change', function () {
        if (mainCheckbox.checked) {
            optionsDiv.style.display = 'block';
        } else {
            optionsDiv.style.display = 'none';
        }
    });

});

$('.open-modal').on('click', function () {
    var itemId = $(this).data('id');
    var itemUserId = $(this).data('user_id');
    var itemTitle = $(this).data('title');
    var itemLeadTaskTypeId = $(this).data('lead_task_type_id');
    var itemCreateDate = $(this).data('create_date');
    var itemCreateTime = $(this).data('create_time');
    var itemNotifyDate = $(this).data('notify_date');
    var itemNotifyTime = $(this).data('notify_time');
    var itemNotes = $(this).data('notes');
    var itemNotification = $(this).data('notification');
    var itemEmailNotify = $(this).data('email_notify');
    var itemSmsNotify = $(this).data('sms_notify');
    const notifyCheckbox = document.getElementById('yeson');
    const notificationCheckbox = document.getElementById('notificationCheckbox');
    const emailCheckbox = document.getElementById('emailCheckbox');
    const smsCheckbox = document.getElementById('smsCheckbox');
    var optionsDiv = document.getElementById('optionsDiv');
    const userSelect = document.getElementById('user_id');
    optionsDiv.style.display = 'none';
    notifyCheckbox.checked = false;

    $('#lead_task_id').val('');
    $('#task_title').val('');
    $('#create_date').val('');
    $('#create_time').val('');
    $('#notify_date').val('');
    $('#notify_time').val('');
    $('#notes').val('');
    $('.modal-title').text('');
    $('#saveChanges').text('');

    if (itemId) {
        $('#lead_task_id').val(itemId);

        const option = userSelect.querySelector(`option[value="${itemUserId}"]`);
        if (option) {
            option.selected = true;
        }

        const taskSelect = document.getElementById('lead_task_type_id');
        const optionTask = taskSelect.querySelector(`option[value="${itemLeadTaskTypeId}"]`);
        if (optionTask) {
            optionTask.selected = true;
        }
        $('#create_date').val(itemCreateDate);
        $('#create_time').val(itemCreateTime);
        $('#task_title').val(itemTitle);

        if (itemNotification === 1 || itemEmailNotify === 1 || itemSmsNotify === 1) {
            optionsDiv.style.display = 'block';
            notifyCheckbox.checked = true;
        } else {
            optionsDiv.style.display = 'none';
            notifyCheckbox.checked = false;
        }
        notificationCheckbox.checked = itemNotification === 1;
        emailCheckbox.checked = itemEmailNotify === 1;
        smsCheckbox.checked = itemSmsNotify === 1;

        $('#notify_date').val(itemNotifyDate);
        $('#notify_time').val(itemNotifyTime);
        $('#notes').val(itemNotes);
        $('.modal-title').text('Edit Lead Task ');
        $('#saveChanges').text('Save Changes');
    } else {
        // Adding new record (clear form fields if needed)

        $('.modal-title').text('Add Lead Task');
        $('#saveChanges').text('Add');
    }
});

document.getElementById('saveAddTask').addEventListener('click', function (event) {
    const yeson = document.getElementById('yeson').checked;
    const notifyDate = document.getElementById('notifyDate').value;
    const notifyTime = document.getElementById('notifyTime').value;
    const createDate = document.getElementById('create_date').value;
    const createTime = document.getElementById('create_time').value;
    const task_title = document.getElementById('task_title').value;
    
    console.log({ yeson, notifyDate, notifyTime, createDate, createTime, task_title });
    
    if (yeson && (!notifyDate.trim() || !notifyTime.trim())) {
        alert('Please select the notification date and time.');
        event.preventDefault(); // Prevent form submission
    } else if (!createDate & !createTime) {
        alert('Please select the date and time.');
        event.preventDefault(); // Prevent form submission
    } else if (!task_title) {
        alert('Please select title.');
        event.preventDefault(); // Prevent form submission
    } else {
        var formData = $('#addTask').serialize();
        console.log(formData);
        console.log("addLeadTaskUrl", addLeadTaskUrl);
        $.ajax({
            url: addLeadTaskUrl,
            method: 'POST',
            data: formData,
            success: function (response) {
                console.log(response);
                $('#tasksModel').modal('hide');
                getLeadTask(response.data);
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
});

function getLeadTask(lead_ref){
    $.ajax({
        url: getLeadTaskDataURL,
        method: 'POST',
        data: {lead_ref:lead_ref},
        success: function (response) {
            console.log(response.data);
            const table = document.getElementById('taskTableData'); // Replace with your table's ID
            const tableBody = table.querySelector('tbody'); // Select the tbody within the table
            setleadTaskTableData(response.data, tableBody, table)
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}



