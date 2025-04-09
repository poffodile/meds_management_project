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

document.getElementById('submit_main_form').addEventListener('click', function () {
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
    }
    // else {
    //     emailError.textContent = '';
    // }

    // Telephone validation
    const phoneRegex = /^\d{10}$/;
    if (!phoneRegex.test(phoneInput.value)) {
        phoneError.textContent = 'Please enter a valid 10-digit telephone number.';
        valid = false;
    }
    // else {
    //     phoneError.textContent = '';
    // }

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
    const notifyDate = document.getElementById('notifyDate');
    const notifyTime = document.getElementById('notifyTime');

    mainCheckbox.addEventListener('change', function () {
        if (mainCheckbox.checked) {
            optionsDiv.style.display = 'block';
            notifyDate.removeAttribute("disabled");
            notifyTime.removeAttribute("disabled");

        } else {
            optionsDiv.style.display = 'none';
            notifyDate.disabled = true;
            notifyTime.disabled = true;
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
    const hiddenTaskContact = document.getElementById('hiddenTaskContact').value;
    const hiddenTaskPhone = document.getElementById('hiddenTaskPhone').value;
    var contact_name = $(this).data('contact_name');
    var contact_phone = $(this).data('contact_phone');

    
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
    $('.modal-title text').text('');
    $('#saveChanges').text('');
    $('#canatact_name').val(hiddenTaskContact);
    $('#phone_num').val(hiddenTaskPhone);
    


    if (itemId) {
        $('#canatact_name').val(contact_name);
        $('#phone_num').val(contact_phone);

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
        $('.modal-title text').text('Edit Lead Task ');
        $('#saveChanges').text('Save Changes');
    } else {
        // Adding new record (clear form fields if needed)

        $('.modal-title text').text('Add Lead Task');
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
                $('#addTask')[0].reset();
                // document.querySelector('form').reset();
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
});

function getLeadTask(lead_ref) {
    $.ajax({
        url: getLeadTaskDataURL,
        method: 'POST',
        data: { lead_ref: lead_ref },
        success: function (response) {
            console.log(response.data);
            const table = document.getElementById('taskTableData'); // Replace with your table's ID
            const tableBody = table.querySelector('tbody'); // Select the tbody within the table

            setleadTaskTableData(response, tableBody, table)
            // setleadTaskTableData(response.close, tableBody, table)
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function setleadTaskTableData(data, tableBody, table) {

    console.log(data);
    tableBody.innerHTML = '';
    if (data.length === 0) {
        // Handle the case where there is no data
        const errorRow = document.createElement('tr');
        const errorCell = document.createElement('td');
        errorCell.colSpan = 8; // Adjust this based on the number of columns in your table
        errorCell.classList.add('red_sorryText');
        errorCell.textContent = 'Sorry, no records to show ';
        errorCell.style.textAlign = 'center'; // Optional: Center the text
        errorRow.appendChild(errorCell);
        tableBody.appendChild(errorRow);
        return; // Exit the function
    }

    appendDataInTable(data.open, tableBody, "Open Tasks");
    appendDataInTable(data.close, tableBody, "Close Tasks");
}

function appendDataInTable(data, tableBody, text) {

    const taskRow = document.createElement('tr');
    const taskCell = document.createElement('td');
    taskCell.colSpan = 10; // Adjust this based on the number of columns in your table
    // errorCell.classList.add('red_sorryText');
    const strongText = document.createElement('strong');
    strongText.textContent = text;
    taskCell.appendChild(strongText);
    // errorCell.style.textAlign = 'center'; // Optional: Center the text
    taskRow.appendChild(taskCell);
    tableBody.appendChild(taskRow);

    let countValue = 1;

    data.forEach(item => {

        // Create a new row
        const row = document.createElement('tr');

        const count = document.createElement('td');
        count.textContent = countValue;
        row.appendChild(count);

        // const now = moment(); // Current date and time
        
        const create_date = moment(item.create_date); // Convert to Moment.js object
        const create_time = moment(item.create_time, 'HH:mm'); // Convert to Moment.js object
        
        // Format create_date and create_time
        const create_date_format = create_date.format('DD/MM/YYYY'); // e.g., 29/01/2025
        const create_time_format = create_time.format('HH:mm'); // e.g., 00:10
        
        const created_on = document.createElement('td');
        created_on.textContent = create_date_format + " " + create_time_format;
        row.appendChild(created_on);

        const name = document.createElement('td');
        name.innerHTML = item.name;
        row.appendChild(name);

        const task_type_title = document.createElement('td');
        task_type_title.innerHTML = item.task_type_title;
        row.appendChild(task_type_title);

        const title = document.createElement('td');
        title.textContent = item.title;
        row.appendChild(title);

        const contact_name = document.createElement('td');
        contact_name.innerHTML = item.contact_name ? item.contact_name : lead_contact;
        row.appendChild(contact_name);

        const telephone = document.createElement('td');
        telephone.innerHTML = lead_telephone;
        row.appendChild(telephone);

        if (item.notification === 1 || item.sms === 1 || item.email === 1) {

            const notifyDate = item.notify_date; // Example: '2025-01-24'
            const notifyTime = item.notify_time; // Example: '03:40'

            // Combine date and time
            const dateTime = `${notifyDate} ${notifyTime}`;
            const formattedDateTime = moment(dateTime, 'YYYY-MM-DD HH:mm').format('DD/MM/YYYY HH:mm');

            const text = document.createElement('td');
            text.innerHTML = `Yes, On  <br> ${formattedDateTime}`;
            row.appendChild(text);
        } else {
            const text = document.createElement('td');
            text.innerHTML = "No";
            row.appendChild(text);
        }


        const notes = document.createElement('td');
        notes.innerHTML = item.notes;
        row.appendChild(notes);

        const idCell = document.createElement('td');
        idCell.innerHTML = `<div class="nav-item dropdown tableActionBtn">
                <a href="#" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown">Action</a>
                <div class="dropdown-menu fade-up m-0">
                    <a href="javaScript:void(0);" class="dropdown-item"  onclick="mark_as_complete_task(${item.id}, ${lead_id}, '${item.lead_ref}')">Mark As Completed</a>
                    <hr class="dropdown-divider">
                    <a href="#" class="dropdown-item open-modal" data-bs-toggle="modal" data-bs-target="#tasksModel" data-id="${item.id}" data-user_id="${item.user_id}" data-title="${item.title}" data-task_type_id="${item.lead_task_type_id}" data-create_date="${item.create_date}" data-create_time="${item.create_time}" data-notify_date="${item.notify_date}" data-notify_time="${item.notify_time}" data-notes="${item.notes}" data-notification="${item.notification}" data-email_notify="${item.email_notify}" data-sms_notify="${item.sms_notify}">Edit Task</a>
                    <a href="${baseDeleteURL.replace('__TASK_ID__', item.id)}" class="dropdown-item">Delete Task</a>
                </div>
            </div>`;
        row.appendChild(idCell);

        // Append the row to the table body
        tableBody.appendChild(row);
        countValue++;
    });
}

function mark_as_complete_task(task_id, lead_id, lead_ref) {
    console.log("task_id", task_id + " " + lead_ref);
    // Ask for confirmation
    const isConfirmed = confirm("Are you sure you want to complete this task?");
    if (!isConfirmed) {
        return; // Exit if user cancels
    }

    const url = markAsComplete.replace(':task', task_id);

    // Perform AJAX request to delete the row
    fetch(url, {
        method: "GET",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json",
        },
    })
        .then((response) => {
            getLeadTask(lead_ref);
            if (response.ok) {
                // Remove the table row
                // const row = button.closest("tr");
                // row.remove();
                // alert("Row deleted successfully.");
            } else {
                throw new Error("Failed to delete the row.");
            }
        })
        .catch((error) => {
            alert(error.message);
        });
}


flatpickr(".dateField", {
    dateFormat: "d/m/Y", // Specify the format as dd/mm/yyyy
});

flatpickr(".current_date_only", {
    dateFormat: "d/m/Y", // Specify the format as dd/mm/yyyy
    minDate: "today",    // Disallow selecting dates before today
});


$('#openNext30days').on('click', function () {


    $(".table.mb-3 thead.dynamic-thead").remove(); // Remove thead added dynamically
    $(".table.mb-3 tbody.dynamic-tbody").remove(); // Remove tbody added dynamically


    // alert("dfdf30");
    $.ajax({
        url: get30DaysLead,
        success: function (response) {
            console.log(response.data);
            var data = response.data;
            const existingTable = document.querySelector(".table.mb-0");

            for (const [date, recordData] of Object.entries(data)) {
                const list = recordData.records;

                // Create a new table for each date
                const newTable = document.createElement("table");
                newTable.className = "table mb-3"; // Add custom classes

                // Append thead
                appendThead(newTable, recordData.date, recordData.count);

                // Append tbody
                appendTbody(newTable, list);

                // Append the new table to the DOM after the existing table
                existingTable.insertAdjacentElement("afterend", newTable);
            }

            function appendThead(table, date, appointments) {
                const thead = document.createElement("thead");
                thead.className = "table-light dynamic-thead";
                thead.innerHTML = `
                    <tr>
                        <th style="width: 192px;">${date}</th>
                        <th>Appointments: ${appointments}</th>
                        <th colspan="2"></th>
                    </tr>
                `;
                table.appendChild(thead);
            }

            function appendTbody(table, rows) {
                const tbody = document.createElement("tbody");
                tbody.className = "dynamic-tbody";
                rows.forEach(row => {

                    var formattedTime = row.prefer_time ? moment(row.prefer_time, "HH:mm").format("H:mm") : "00:00";
                    var address = row.address ? row.address : "";

                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${row.name}</td>
                        <td>${row.contact_name}</td>
                        <td style="width: 260px;">${address}</td>
                        <td>${formattedTime}</td>
                    `;
                    tbody.appendChild(tr);
                });
                table.appendChild(tbody);
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
});

document.getElementById("printButton").addEventListener("click", function () {
    const printContents = document.getElementById("printableDiv").innerHTML; // Get the content of the div
    const originalContents = document.body.innerHTML; // Store the original page content

    // Replace the page content with the div content
    document.body.innerHTML = printContents;

    // Trigger the print dialog
    window.print();

    // Restore the original page content after printing
    document.body.innerHTML = originalContents;
    location.reload(); // Reload to restore event listeners and other states
});
