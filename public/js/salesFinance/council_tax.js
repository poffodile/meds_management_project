document.querySelectorAll('.openModalBtn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const action = this.getAttribute('data-action');
        const council_tax_id = document.getElementById('council_tax_id');
        const modalTitle = document.getElementById('modalTitle');
        const flat_num = document.getElementById('flat_num');
        const address = document.getElementById('address');
        const postcode = document.getElementById('postcode');
        const council = document.getElementById('council');
        const additional_notes = document.getElementById('additional_notes');
        const amount_paid = document.getElementById('amount_paid');
        const bill_period_end_date = document.getElementById('bill_period_end_date');
        const bill_period_start_date = document.getElementById('bill_period_start_date');
        const last_bill_date = document.getElementById('last_bill_date');
        const account_number = document.getElementById('account_number');
        const exemptyes = document.getElementById('exemptyes');
        const exemptno = document.getElementById('exemptno');
        const occupancy = document.getElementById('occupancy');
        const ownedByOmegayes = document.getElementById('ownedByOmegayes');
        const ownedByOmegano = document.getElementById('ownedByOmegano');
        const no_of_bedrooms = document.getElementById('no_of_bedrooms');

        if (action === 'add') {
            modalTitle.textContent = 'Add Council Tax';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Edit Councli Tax';
            council_tax_id.value = this.getAttribute('data-id');
            flat_num.value = this.getAttribute('data-flat-number');
            address.value = this.getAttribute('data-address');
            additional_notes.value = this.getAttribute('data-additional');
            postcode.value = this.getAttribute('data-post_code');
            council.value = this.getAttribute('data-council');
            no_of_bedrooms.value = this.getAttribute('data-no_of_bedrooms');
            if (this.getAttribute('data-owned_by_omega') == 1) {
                ownedByOmegayes.checked = true;
            } else if (this.getAttribute('data-owned_by_omega') == 0) {
                ownedByOmegano.checked = true;
            }
            occupancy.value = this.getAttribute('data-occupancy');

            if (this.getAttribute('data-exempt') == 1) {
                exemptyes.checked = true;
            } else if (this.getAttribute('data-exempt') == 0) {
                exemptno.checked = true;
            }

            account_number.value = this.getAttribute('data-account_number');
            // last_bill_date.value = this.getAttribute('data-last_bill_date');

            const originalLastDate = this.getAttribute('data-last_bill_date');
            let formattedLastBillDate = originalLastDate;
            if (originalLastDate && originalLastDate.includes('-')) {
                const parts = originalLastDate.split('-'); // [yyyy, mm, dd]
                formattedLastBillDate = `${parts[2]}-${parts[1]}-${parts[0]}`; // dd-mm-yyyy
            }
            last_bill_date.value = formattedLastBillDate;

            // bill_period_start_date.value = this.getAttribute('data-bill_period_start_date');
            const originalStartDate = this.getAttribute('data-bill_period_start_date');
            let formattedStartDate = originalStartDate;
            if (originalStartDate && originalStartDate.includes('-')) {
                const parts = originalStartDate.split('-'); // [yyyy, mm, dd]
                formattedStartDate = `${parts[2]}-${parts[1]}-${parts[0]}`; // dd-mm-yyyy
            }
            bill_period_start_date.value = formattedStartDate;

            // bill_period_end_date.value = this.getAttribute('data-bill_period_end_date');

            const originalEndDate = this.getAttribute('data-bill_period_end_date');
            let formattedEndDate = originalEndDate;
            if (originalStartDate && originalEndDate.includes('-')) {
                const parts = originalEndDate.split('-'); // [yyyy, mm, dd]
                formattedEndDate = `${parts[2]}-${parts[1]}-${parts[0]}`; // dd-mm-yyyy
            }
            bill_period_end_date.value = formattedEndDate;

            amount_paid.value = this.getAttribute('data-amount_paid');
        }

        $('#AddCouncilTax').modal('show');
    });
});

const startDateInput = document.getElementById('bill_period_start_date');
const endDateInput = document.getElementById('bill_period_end_date');

startDateInput.addEventListener('change', function () {
    const startDate = new Date(this.value);

    if (this.value) {
        // Enable end date input and set min to start date
        endDateInput.disabled = false;
        endDateInput.min = this.value;

        // Optional: Clear previously selected end date if it's before the new start date
        if (new Date(endDateInput.value) < startDate) {
            endDateInput.value = '';
        }
    } else {
        // If start date is cleared, disable end date again
        endDateInput.disabled = true;
        endDateInput.value = '';
        endDateInput.min = '';
    }
});






function validateCouncilTaxForm() {
    let isValid = true;
    $('.text-danger').remove(); // Clear previous errors

    // Helper to show error
    function showError(field, message) {
        isValid = false;
        field.after(`<span class="text-danger">${message}</span>`);
    }

    // Validate each required field
    // const address = $('[name="address"]');
    // if (!address.val().trim()) showError(address, 'The address field is required.');

    // const postCode = $('[name="post_code"]');
    // if (!postCode.val().trim()) showError(postCode, 'The post code field is required.');

    // const council = $('[name="council"]');
    // if (!council.val().trim()) showError(council, 'The council field is required.');

    // const accountNumber = $('[name="account_number"]');
    // if (!accountNumber.val().trim()) showError(accountNumber, 'The account number field is required.');

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

    $("#saveCouncilTax").on("click", function (e) {
        // alert("hii");
        e.preventDefault(); // Prevent default form submission

        if (!validateCouncilTaxForm()) {
            return false; // Stop if validation fails
        }

        var council_tax_id=$("#council_tax_id").val();
        var url=saveData;
        if(council_tax_id !=''){
            url=editData;
        }

        console.log($('#addCouncilTaxForm').serialize());
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
    $(document).ready(function () {
        $('#council_tax').DataTable({
            dom: 'Blfrtip',
            buttons: [{
                extend: 'csv',
                text: 'Export' // Rename button
            },
                'colvis'
            ]
        });
    });


    // Last Bill date 
    $('#last_bill_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
        endDate: new Date()
    });

    $('#openCalendarLastBillBtn').click(function () {
        $('#last_bill_date').focus();
    });

    // Bill Period Start Date 
    $('#bill_period_start_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
    });

    $('#openCalendarBillPeriodStartBtn').click(function () {
        $('#bill_period_start_date').focus();
    });

    // Bill Period End Date 
    $('#bill_period_end_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true,
    }).on('changeDate', function (e) {
        var startDate = $('#bill_period_start_date').val(); 
        if (!startDate) {
            alert('Please select the Start Period first.');
            $(this).val('');
            $('#bill_period_start_date').focus();
        }
    });

    $('#openCalendarBillPeriodEndBtn').click(function () {
        $('#bill_period_end_date').focus();
    });
});

