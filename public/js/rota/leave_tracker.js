document.querySelectorAll('.openModalBtn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const action = this.getAttribute('data-action');
        if (action === 'add') {
            modalTitle.textContent = 'Add Annual Leave Tracker';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Edit Annual Leave Tracker';
        }

        $('#AddAnnualLeave').modal('show');
    });
});


function validateCouncilTaxForm() {
    let isValid = true;
    $('.text-danger').remove(); // Clear previous errors

    // Helper to show error
    function showError(field, message) {
        isValid = false;
        field.after(`<span class="text-danger">${message}</span>`);
    }

    // Account number validation
    const accountNumberField = $('[name="account_number"]');
    const accountNumber = parseInt(accountNumberField.val(), 20);
    if (!accountNumberField.val().trim()) {
        showError(accountNumberField, 'The account number field is required.');
    } else if (isNaN(accountNumber) || accountNumber < 0) {
        showError(accountNumberField, 'The account number must be a positive integer.');
    }
    // else if (accountNumber > 20) {
    //     showError(accountNumberField, 'The account number must be less than or equal to 20.');
    // } 

    const ownedByOmega = $('[name="owned_by_omega"]:checked');
    if (ownedByOmega.length === 0) showError($('[name="owned_by_omega"]').last(), 'The owned by omega field is required.');

    const exempt = $('[name="exempt"]:checked');
    if (exempt.length === 0) showError($('[name="exempt"]').last(), 'The exempt field is required.');

    // Bill period validation
    const startDateField = $('#bill_period_start_date');
    const endDateField = $('#bill_period_end_date');
    const startDate = new Date(startDateField.val());
    const endDate = new Date(endDateField.val());

    if (!startDateField.val().trim()) {
        showError(startDateField, 'The start date is required.');
    }

    if (!endDateField.val().trim()) {
        showError(endDateField, 'The end date is required.');
    } else if (startDateField.val() && endDate <= startDate) {
        showError(endDateField, 'The end date must be after the start date.');
    }

    // Last bill date validation
    const lastBillDateField = $('#last_bill_date');
    const lastBillDate = new Date(lastBillDateField.val());
    if (!lastBillDateField.val().trim()) {
        showError(lastBillDateField, 'The last bill date is required.');
    } else if (lastBillDateField.val() && lastBillDate > new Date()) {
        showError(lastBillDateField, 'The last bill date cannot be in the future.');
    }

    // Amount paid validation
    const amountPaidField = $('#amount_paid');
    const amountPaid = parseFloat(amountPaidField.val());
    if (!amountPaidField.val().trim()) {
        showError(amountPaidField, 'The amount paid field is required.');
    } else if (isNaN(amountPaid) || amountPaid < 0) {
        showError(amountPaidField, 'The amount paid must be a positive number.');
    }

    // Flat number validation
    const flatNumField = $('#flat_num');
    if (!flatNumField.val().trim()) {
        showError(flatNumField, 'The flat number field is required.');
    } else if (flatNumField.val().length > 10) {
        showError(flatNumField, 'The flat number must be less than 10 characters.');
    }

    // No of bedrooms validation
    const noOfBedroomsField = $('#no_of_bedrooms');
    const noOfBedrooms = parseInt(noOfBedroomsField.val(), 10);
    if (!noOfBedroomsField.val().trim()) {
        showError(noOfBedroomsField, 'The number of bedrooms field is required.');
    } else if (isNaN(noOfBedrooms) || noOfBedrooms < 0) {
        showError(noOfBedroomsField, 'The number of bedrooms must be a positive integer.');
    } else if (noOfBedrooms > 10) {
        showError(noOfBedroomsField, 'The number of bedrooms must be less than or equal to 10.');
    }

    // Address validation
    const addressField = $('#address');
    if (!addressField.val().trim()) {
        showError(addressField, 'The address field is required.');
    } else if (addressField.val().length > 100) {
        showError(addressField, 'The address must be less than 100 characters.');
    }

    // Postcode validation
    const postcodeField = $('#postcode');
    if (!postcodeField.val().trim()) {
        showError(postcodeField, 'The postcode field is required.');
    } else if (postcodeField.val().length > 10) {
        showError(postcodeField, 'The postcode must be less than 10 characters.');
    }

    // Council validation
    const councilField = $('#council');
    if (!councilField.val().trim()) {
        showError(councilField, 'The council field is required.');
    } else if (councilField.val().length > 50) {
        showError(councilField, 'The council must be less than 50 characters.');
    }


    // Additional notes validation
    const additionalNotesField = $('#additional_notes');
    if (additionalNotesField.val().length > 200) {
        showError(additionalNotesField, 'The additional notes must be less than 200 characters.');
    }

    return isValid;
}


$(document).ready(function () {

    $("#saveAnnualLeave").on("click", function (e) {
        // alert("hii");
        e.preventDefault(); // Prevent default form submission

        if (!validateCouncilTaxForm()) {
            return false; // Stop if validation fails
        }

        var council_tax_id = $("#council_tax_id").val();
        var url = saveData;
        if (council_tax_id != '') {
            url = editData;
        }

        console.log($('#addLeaveTrackerForm').serialize());
        $.ajax({
            url: url, // Laravel route or API endpoint
            method: "POST",
            data: $('#addCouncilTaxForm').serialize(),
            dataType: "json",
            success: function (response) {
                console.log(response);
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.success) {
                    alert("Success: " + response.message);
                    // alert("Data saved successfully!");
                    $("#AddCouncilTax").modal("hide"); // Hide modal
                    $("#addCouncilTaxForm")[0].reset(); // Reset form
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr) {
                // alert("Something went wrong. Please try again.");
                if (xhr.status === 422) {
                    // Validation error
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = '';

                    // Clear old errors first
                    $('.text-danger').remove();

                    $.each(errors, function (key, value) {
                        // Display message under each input field
                        let inputField = $(`[name="${key}"]`);
                        if (inputField.length) {
                            inputField.after(`<p class="text-danger mb-0">${value[0]}</p>`);
                        }

                        // Collect all messages for optional alert box
                        errorMessages += value[0] + "\n";
                    });

                    // Optional: show all errors in a single alert box
                    alert("Please fix the following errors:\n" + errorMessages);
                } else {
                    alert("Something went wrong. Please try again.");
                }
            }
        });
    });
});
$(document).on('click', '.deleteBtn', function () {
    var id = $(this).data('id');

    if (confirm('Are you sure you want to delete this record?')) {
        $.ajax({
            url: deleteURL + id,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') // for Laravel CSRF protection
            },
            success: function (response) {
                if (isAuthenticated(response) == false) {
                    return false;
                }
                alert('Record deleted successfully!');
                location.reload();
            },
            error: function (xhr) {
                alert('Something went wrong.');
            }
        });
    }
});

$(document).ready(function () {

    $('#Leave_startDate').datepicker({
        format: 'dd-mm-yyyy'
    });

    $('#Leave_startDate').on('change', function () {
        $('#Leave_startDate').datepicker('hide');
    });


});

$(document).ready(function () {
    $('#leaveTracker').DataTable({
        dom: 'Bfrtip',
        buttons: [{
            extend: 'csv',
            text: 'Export' // Rename button
        }
        ]
    });
});

//  $(document).ready(function () {
//         $('#user_id').on('change', function () {
//             var userId = $(this).val();

//             $.ajax({
//                 url: getUserData,
//                 method: 'POST',
//                 data: {
//                     id: userId, // make sure you're passing the correct ID
//                     _token: $('meta[name="csrf-token"]').attr('content')
//                 },
//                 success: function (response) {
//                     console.log('User Data:', response.data);
//                     // document.getElementById('start_date').value = response.data.date_of_joining;
//                     // $('#start_date').datepicker('setDate', response.data.date_of_joining);
//                     $(function () {
//                         $('#start_date').datepicker({
//                             dateFormat: 'yy-mm-dd' // format should match the date string
//                         });

//                         // Then set the date
//                         $('#start_date').datepicker('setDate', response.data.date_of_joining);
//                     });

//                     document.getElementById('entitlement').value = response.data.holiday_entitlement;
//                 },
//                 error: function (xhr) {
//                     if (xhr.status === 422) {
//                         // Laravel validation error
//                         const errors = xhr.responseJSON.errors;
//                         let errorMessage = '';
//                         for (const key in errors) {
//                             if (errors.hasOwnProperty(key)) {
//                                 errorMessage += errors[key][0] + '\n';
//                             }
//                         }
//                         alert(errorMessage);
//                     } else {
//                         alert('An error occurred. Please try again.');
//                     }
//                 }
//             });
//         });
//     });