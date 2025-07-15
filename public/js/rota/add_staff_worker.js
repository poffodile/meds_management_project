$(document).ready(function () {

     setTimeout(function() {
        // Hide all alerts after 3 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.display = 'none';
        });
    }, 3000); // 3000 milliseconds = 3 seconds
    
    $('#staffWorker').DataTable({
        dom: 'Bfrtip', // B = Buttons
        buttons: [
            {
                extend: 'csv',
                text: 'Export',
                bom: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23]
                }
            },
            'colvis'
        ]
    });

    $('#DOB').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true, // Optional: close picker after selection
        todayHighlight: true // Optional: highlight today's date
    });
    $('#DOB').on('change', function () {
        $('#DOB').datepicker('hide');
    });

    // Initialize Start Date
    $('#start_Date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        const startDate = e.date;

        // Set the minimum date for Leave Date to the next day
        const minLeaveDate = new Date(startDate.getTime() + 86400000);
        $('#leave_date').datepicker('setStartDate', minLeaveDate);

        // Clear any previously selected leave date
        $('#leave_date').val('');
    });

    $('#start_Date').on('change', function () {
        $('#start_Date').datepicker('hide');
    });


    // $('#probation_start_date').datepicker({
    //     format: 'dd-mm-yyyy',
    //     autoclose: true, // Optional: close picker after selection
    //     todayHighlight: true // Optional: highlight today's date
    // });
    // $('#probation_start_date').on('change', function () {
    //     $('#probation_start_date').datepicker('hide');
    // });

    // $('#probation_end_date').datepicker({
    //     format: 'dd-mm-yyyy',
    //     autoclose: true, // Optional: close picker after selection
    //     todayHighlight: true // Optional: highlight today's date
    // });
    // $('#probation_end_date').on('change', function () {
    //     $('#probation_end_date').datepicker('hide');
    // });

    // $('#probation_renew_date').datepicker({
    //     format: 'dd-mm-yyyy',
    //     autoclose: true, // Optional: close picker after selection
    //     todayHighlight: true // Optional: highlight today's date
    // });
    // $('#probation_renew_date').on('change', function () {
    //     $('#probation_renew_date').datepicker('hide');
    // });

    // START DATE
    $('#probation_start_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        const startDate = e.date;

        // Clear & reset End Date
        $('#probation_end_date').val('').datepicker('setStartDate', new Date(startDate.getTime() + 86400000));

        // Clear & reset Renew Date
        $('#probation_renew_date').val('').datepicker('setStartDate', null);
    });

    // END DATE
    $('#probation_end_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        const startDateStr = $('#probation_start_date').val();
        if (!startDateStr) {
            alert('Please select the Probation Start Date first.');
            $(this).val('');
            $('#probation_start_date').focus();
            return;
        }

        const [d, m, y] = startDateStr.split('-');
        const startDate = new Date(y, m - 1, d);
        const endDate = e.date;

        if (endDate <= startDate) {
            alert('End Date must be after Start Date.');
            $(this).val('');
            return;
        }

        // Clear & reset Renew Date
        $('#probation_renew_date').val('').datepicker('setStartDate', new Date(endDate.getTime() + 86400000));
    });

    // RENEW DATE
    $('#probation_renew_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        const endDateStr = $('#probation_end_date').val();
        if (!endDateStr) {
            alert('Please select the Probation End Date first.');
            $(this).val('');
            $('#probation_end_date').focus();
            return;
        }

        const [d, m, y] = endDateStr.split('-');
        const endDate = new Date(y, m - 1, d);
        const renewDate = e.date;

        if (renewDate <= endDate) {
            alert('Renew Date must be after End Date.');
            $(this).val('');
        }
    });


    // Initialize Leave Date
    $('#leave_date').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function (e) {
        const leaveDate = e.date;
        const startDateStr = $('#start_Date').val();

        if (!startDateStr) {
            alert('Please select the Start Date first.');
            $(this).val('');
            $('#start_Date').focus();
            return;
        }

        // Convert startDate string to Date
        const parts = startDateStr.split('-');
        const startDate = new Date(parts[2], parts[1] - 1, parts[0]);

        // Manual check (in case someone edits the input manually)
        if (leaveDate <= startDate) {
            alert('Leave Date must be greater than Start Date.');
            $(this).val('');
        }
    });

    $('#leave_date').on('change', function () {
        $('#leave_date').datepicker('hide');
    });


    $("#addStaffWorkerModal").scroll(function () {
        $('#DOB').datepicker('place');
        $('#start_Date').datepicker('place');
        $('#probation_start_date').datepicker('place');
        $('#probation_end_date').datepicker('place');
        $('#probation_renew_date').datepicker('place');
        $('#leave_date').datepicker('place');
    });

    // Validate leave date after it's picked
    $('#leave_date').on('changeDate', function () {
        let startDate = $('#start_Date').val();
        let leaveDate = $('#leave_date').val();

        // Check both dates are filled
        if (startDate && leaveDate) {
            // Convert dd-mm-yyyy to Date object
            let partsStart = startDate.split('-');
            let partsLeave = leaveDate.split('-');

            let start = new Date(partsStart[2], partsStart[1] - 1, partsStart[0]); // yyyy, mm-1, dd
            let leave = new Date(partsLeave[2], partsLeave[1] - 1, partsLeave[0]);

            if (leave <= start) {
                alert('Leave date must be greater than start date.');
                $('#leave_date').val(''); // Clear invalid leave date
            }
        }
    });

});

function validateStaffWorkerForm() {

    let isValid = true;
    $('.text-danger').remove(); // Clear previous errors

    // Helper to show error
    function showError(field, message) {
        isValid = false;
        console.log('Error isValid:', isValid);
        console.log('Error message:', message);

        field.after(`<span class="text-danger">${message}</span>`);
    }

    // Validate each required field
    const surname = $('[name="surname"]');
    const surnameValue = surname.val().trim();
    if (!surnameValue) {
        showError(surname, 'The surname field is required.');
    } else if (surnameValue.length < 2) {
        showError(surname, 'The surname must be at least 2 characters long.');
    } else if (surnameValue.length > 50) {
        showError(surname, 'The surname must not exceed 50 characters.');
    }

    const forename = $('[name="forename"]');
    const forenameValue = forename.val().trim();
    if (!forenameValue) {
        showError(forename, 'The forename field is required.');
    } else if (forenameValue.length < 2) {
        showError(forename, 'The forename must be at least 2 characters long.');
    } else if (forenameValue.length > 50) {
        showError(forename, 'The forename must not exceed 50 characters.');
    }

    const address = $('[name="address"]');
    const addressValue = address.val().trim();
    if (!addressValue) {

        showError(address, 'The address field is required.');
    } else if (addressValue.length < 2) {
        showError(address, 'The address must be at least 2 characters long.');
    } else if (addressValue.length > 100) {
        showError(address, 'The address must not exceed 100 characters.');
    }

    const postcode = $('[name="postCode"]');
    const postcodeValue = postcode.val().trim();
    if (!postcodeValue) {
        showError(postcode, 'The postcode field is required.');
    } else if (postcodeValue.length < 2) {
        showError(postcode, 'The postcode must be at least 2 characters long.');
    } else if (postcodeValue.length > 10) {
        showError(postcode, 'The postcode must not exceed 10 characters.');
    }

    const accountNum = $('[name="account_num"]');
    const accountNumValue = accountNum.val().trim();
    if (!accountNumValue) {
        showError(accountNum, 'The account number field is required.');
    } else if (accountNumValue.length < 2) {
        showError(accountNum, 'The account number must be at least 2 characters long.');
    } else if (accountNumValue.length > 20) {
        showError(accountNum, 'The account number must not exceed 20 characters.');
    }

    const sortCode = $('[name="sort_code"]');
    const sortCodeValue = sortCode.val().trim();
    if (!sortCodeValue) {
        showError(sortCode, 'The sort code field is required.');
    } else if (sortCodeValue.length < 2) {
        showError(sortCode, 'The sort code must be at least 2 characters long.');
    } else if (sortCodeValue.length > 20) {
        showError(sortCode, 'The sort code must not exceed 20 characters.');
    }

    const rateOfPay = $('[name="rate_of_pay"]');
    const rateOfPayValue = rateOfPay.val().trim();
    if (!rateOfPayValue) {
        showError(rateOfPay, 'The rate of pay field is required.');
    } else if (isNaN(rateOfPayValue)) {
        showError(rateOfPay, 'The rate of pay must be a number.');
    } else if (parseFloat(rateOfPayValue) < 0) {
        showError(rateOfPay, 'The rate of pay must be a positive number.');
    } else if (parseFloat(rateOfPayValue) > 100000) {
        showError(rateOfPay, 'The rate of pay must not exceed 100000.');
    }

    const level = $('[name="level"]');
    const levelValue = level.val().trim();
    if (!levelValue) {
        showError(level, 'The level field is required.');
    } else if (levelValue.length < 2) {
        showError(level, 'The level must be at least 2 characters long.');
    } else if (levelValue.length > 20) {
        showError(level, 'The level must not exceed 20 characters.');
    }

    const jobRole = $('[name="job_role"]');
    const jobRoleValue = jobRole.val().trim();
    if (!jobRoleValue) {
        showError(jobRole, 'The job role field is required.');
    } else if (jobRoleValue.length < 2) {
        showError(jobRole, 'The job role must be at least 2 characters long.');
    } else if (jobRoleValue.length > 50) {
        showError(jobRole, 'The job role must not exceed 50 characters.');
    }

    const NIN = $('[name="NIN"]');
    const NINValue = NIN.val().trim();
    if (!NINValue) {
        showError(NIN, 'The NIN field is required.');
    } else if (NINValue.length < 2) {
        showError(NIN, 'The NIN must be at least 2 characters long.');
    } else if (NINValue.length > 20) {
        showError(NIN, 'The NIN must not exceed 20 characters.');
    }


    const starterDeclaration = $('[name="starter_declaration"]');
    const starterDeclarationValue = starterDeclaration.val();

    if (starterDeclarationValue === "" || !['1', '2', '3'].includes(starterDeclarationValue)) {
        showError(starterDeclaration, 'Please select a valid starter declaration (Yes-A, Yes-B, or Yes-C).');
    }


    const studentLoan = $('[name="student_loan"]');
    const studentLoanValue = studentLoan.val().trim();

    if (!studentLoanValue) {
        showError(studentLoan, 'The student loan field is required.');
    } else if (!['no_student_loan', 'postgraduate', 'plan_1', 'plan_2', 'plan_4'].includes(studentLoanValue)) {
        showError(studentLoan, 'Invalid student loan option selected.');
    }


    const dbsNumber = $('[name="dbs_number"]');
    const dbsNumberValue = dbsNumber.val().trim();
    if (!dbsNumberValue) {

        showError(dbsNumber, 'The DBS number field is required.');
    } else if (dbsNumberValue.length < 2) {
        showError(dbsNumber, 'The DBS number must be at least 2 characters long.');
    } else if (dbsNumberValue.length > 20) {
        showError(dbsNumber, 'The DBS number must not exceed 20 characters.');
    } else if (!/^\d+$/.test(dbsNumberValue)) {
        showError(dbsNumber, 'The DBS number must be a number.');
    } else if (dbsNumberValue.length < 10 || dbsNumberValue.length > 15) {
        showError(dbsNumber, 'The DBS number must be between 10 and 15 digits long.');
    }

    const leaveDate = $('[name="leave_date"]');
    const leaveDateValue = leaveDate.val().trim();
    if (leaveDateValue) {
        const leaveDateObj = new Date(leaveDateValue);
        const today = new Date();
        if (leaveDateObj < today) {
            showError(leaveDate, 'The leave date must be today or in the future.');
        }
    }

    const probationStartDate = $('[name="probation_start_date"]');
    const probationStartDateValue = probationStartDate.val().trim();
    if (probationStartDateValue) {
        const probationStartDateObj = new Date(probationStartDateValue);
        const today = new Date();
        if (probationStartDateObj < today) {

            showError(probationStartDate, 'The probation start date must be today or in the future.');
        }
    }
    const probationEndDate = $('[name="probation_end_date"]');
    const probationEndDateValue = probationEndDate.val().trim();
    if (probationEndDateValue) {
        const probationEndDateObj = new Date(probationEndDateValue);
        const today = new Date();
        if (probationEndDateObj < today) {
            showError(probationEndDate, 'The probation end date must be today or in the future.');
        }
    }
    const probationRenewDate = $('[name="probation_renew_date"]');
    const probationRenewDateValue = probationRenewDate.val().trim();
    if (probationRenewDateValue) {
        const probationRenewDateObj = new Date(probationRenewDateValue);
        const today = new Date();
        if (probationRenewDateObj < today) {
            showError(probationRenewDate, 'The probation renew date must be today or in the future.');
        }
    }

    // const probationEnrolledYes = $('[name="probation_enrollered_yes"]');
    // const probationEnrolledNo = $('[name="probation_enrollered_no"]');
    // if (!probationEnrolledYes.is(':checked') && !probationEnrolledNo.is(':checked')) {

    //     showError(probationEnrolledYes, 'The probation enrolled field is required.');
    // }

    // const dbsClearYes = $('[name="dbs_clear_yes"]');
    // const dbsClearNo = $('[name="dbs_clear_no"]');
    // if (!dbsClearYes.is(':checked') && !dbsClearNo.is(':checked')) {
    //     showError(dbsClearYes, 'The DBS clear field is required.');
    // }

    // const dbsUpdateYes = $('[name="DBS_Update_yes"]');
    // const dbsUpdateNo = $('[name="DBS_Update_no"]');
    // if (!dbsUpdateYes.is(':checked') && !dbsUpdateNo.is(':checked')) {
    //     showError(dbsUpdateYes, 'The DBS update field is required.');
    // }


    const email = $('#email');
    const emailValue = email.val().trim();

    if (!emailValue) {
        showError(email, 'The email field is required.');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
        showError(email, 'Please enter a valid email address.');
    }
    console.log('isValid:', isValid); // Debugging line

    return isValid;

}



document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addStaffWorkerForm');
    const saveButton = document.getElementById('saveSatffWorkerModel'); // Make sure this ID is correct

    if (form && saveButton) {
        saveButton.addEventListener('click', async function (e) {
            e.preventDefault(); // Prevent the default button action

            if (!validateStaffWorkerForm()) {
                return false; // Stop if validation fails
            }

            const formData = new FormData(form);

            try {
                form.action = addStaffWorker; // Make sure `addStaffWorker` is a valid URL

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                });
                // Always parse the response body
                const result = await response.json();
                console.log('Form data:', result); // Debugging line

                if (response.status === 422) {
                    // Clear any existing error messages
                    $('.text-danger').remove();

                    const errors = result.errors;
                    let errorMessages = '';

                    Object.entries(errors).forEach(([key, value]) => {
                        const inputField = document.querySelector(`[name="${key}"]`);
                        if (inputField) {
                            const errorEl = document.createElement('p');
                            errorEl.classList.add('text-danger', 'mb-0');
                            errorEl.textContent = value[0];
                            inputField.after(errorEl);
                        }
                        errorMessages += value[0] + "\n";
                    });

                    // alert("Please fix the following errors:\n" + errorMessages);
                    return;
                }

                if (!response.ok) {
                    throw new Error(result.message || 'An unknown error occurred.');
                }

                // Success block
                if (result.success || result.status === 'success') {
                    console.log(result.success);
                    alert(result.message);
                    form.reset();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Something went wrong.'));
                }

            } catch (error) {
                console.error('There was a problem with the fetch operation:', error);
                alert('An error occurred. Please try again.');
            }
        });
    }
});


// document.addEventListener('DOMContentLoaded', function () {
//     const form = document.getElementById('addStaffWorkerForm');
//     const saveButton = document.getElementById('saveSatffWorkerModel'); // Make sure this ID is correct

//     if (form && saveButton) {
//         // alert('Form and Save button found!'); // Debugging line

//         saveButton.addEventListener('click', async function (e) {
//             e.preventDefault(); // Prevent the default button action
//             const formData = new FormData(form);
//             console.log('Form data:', formData); // Debugging line

//             try {
//                 form.action = addStaffWorker; // Make sure `addStaffWorker` is a defined URL
//                 const response = await fetch(form.action, {
//                     method: 'POST',
//                     body: formData,
//                     headers: {
//                         'X-Requested-With': 'XMLHttpRequest',
//                         'Accept': 'application/json' // Important to get proper JSON from Laravel
//                     },
//                 });

//                 // if (response.status === 422) {
//                 //     const errorData = await response.json();
//                 //     const errors = errorData.errors;
//                 //     const errorMessages = Object.values(errors).map(arr => arr.join(', ')).join('\n');
//                 //     alert("Validation errors:\n" + errorMessages);
//                 //     return; // Stop further execution
//                 // }

//                 if (response.status === 422) {
//                     // Validation error
//                     let errors = xhr.responseJSON.errors;
//                     let errorMessages = '';

//                     // Clear old errors first
//                     $('.text-danger').remove();

//                     $.each(errors, function (key, value) {
//                         // Display message under each input field
//                         let inputField = $(`[name="${key}"]`);
//                         if (inputField.length) {
//                             inputField.after(`<p class="text-danger mb-0">${value[0]}</p>`);
//                         }

//                         // Collect all messages for optional alert box
//                         errorMessages += value[0] + "\n";
//                     });

//                     // Optional: show all errors in a single alert box
//                     alert("Please fix the following errors:\n" + errorMessages);
//                 } else {
//                     alert("Something went wrong. Please try again.");
//                 }



//                 if (!response.ok) {
//                     throw new Error('Network response was not ok');
//                 }

//                 const data = await response.json();

//                 if (data.success || data.status === 'success') {
//                     alert(data.message);
//                     location.reload(); // Reload the page to see the new staff worker
//                     form.reset(); // Reset the form
//                 } else {
//                     alert('Error: ' + (data.message || 'Something went wrong.'));
//                 }

//             } catch (error) {
//                 console.error('There was a problem with the fetch operation:', error);
//                 alert('An error occurred. Please try again.');
//             }
//         });
//     }
// });

document.querySelectorAll('.openAddStaffModel').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const action = this.getAttribute('data-action');
        const modalTitle = document.getElementById('modalTitle');
        const staffId = document.getElementById('staff_id');
        const surname = document.getElementById('surname');
        const forename = document.getElementById('forename');
        const address = document.getElementById('address');
        const postcode = document.getElementById('postCode');
        let dob = document.getElementById('DOB');
        const account_num = document.getElementById('account_num');
        const sort_code = document.getElementById('sort_code');
        const status = document.getElementById('status');
        const rate_of_pay = document.getElementById('rate_of_pay');
        const level = document.getElementById('level');
        let start_Date = document.getElementById('start_Date');
        const job_role = document.getElementById('job_role');
        const NIN = document.getElementById('NIN');
        const starter_declaration = document.getElementById('starter_declaration');
        let probation_start_date = document.getElementById('probation_start_date');
        let probation_end_date = document.getElementById('probation_end_date');
        let probation_renew_date = document.getElementById('probation_renew_date');
        const probation_enrollered_yes = document.getElementById('probation_enrollered_yes');
        const probation_enrollered_no = document.getElementById('probation_enrollered_no');
        const student_loan = document.getElementById('student_loan');
        const dbs_clear_yes = document.getElementById('dbs_clear_yes');
        const dbs_clear_no = document.getElementById('dbs_clear_no');
        const DBS_Update_yes = document.getElementById('DBS_Update_yes');
        const DBS_Update_no = document.getElementById('DBS_Update_no');
        const dbs_number = document.getElementById('dbs_number');
        const leave_date = document.getElementById('leave_date');
        const email = document.getElementById('email');
        const mobile = document.getElementById('mobile');
        if (action === 'add') {
            modalTitle.textContent = 'Add Staff Worker';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Edit Staff Worker';
            const staffData = JSON.parse(this.getAttribute('data-staff'));
            staffId.value = staffData.id || '';
            surname.value = staffData.surname || '';
            forename.value = staffData.forename || '';
            address.value = staffData.address || '';
            postcode.value = staffData.postCode || '';
            dob = staffData.DOB || '';
            if (dob) {
                // Convert from Y-m-d to d-m-Y
                const dateParts = dob.split('-'); // [2025, 04, 24]
                const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`; // 24-04-2025

                $('#DOB').datepicker('setDate', formattedDate);
            }
            account_num.value = staffData.account_num || '';
            sort_code.value = staffData.sort_code || '';
            status.value = staffData.status || '';
            rate_of_pay.value = staffData.rate_of_pay || '';
            level.value = staffData.level || '';
            start_Date = staffData.start_date || '';
            if (start_Date) {
                // Convert from Y-m-d to d-m-Y
                const datePartsStartDate = start_Date.split('-'); // [2025, 04, 24]
                const formattedStartDate = `${datePartsStartDate[2]}-${datePartsStartDate[1]}-${datePartsStartDate[0]}`; // 24-04-2025

                $('#start_Date').datepicker('setDate', formattedStartDate);
            }
            job_role.value = staffData.job_role || '';
            NIN.value = staffData.NIN || '';
            starter_declaration.value = staffData.starter_declaration || '';
            probation_start_date = staffData.probation_start_date || '';
            if (probation_start_date) {
                // Convert from Y-m-d to d-m-Y
                const datePartsProStart = probation_start_date.split('-'); // [2025, 04, 24]
                const formattedDateProStart = `${datePartsProStart[2]}-${datePartsProStart[1]}-${datePartsProStart[0]}`; // 24-04-2025

                $('#probation_start_date').datepicker('setDate', formattedDateProStart);
            }
            probation_end_date = staffData.probation_end_date || '';
            if (probation_end_date) {
                // Convert from Y-m-d to d-m-Y
                const datePartsProEnd = probation_end_date.split('-'); // [2025, 04, 24]
                const formattedDateProEnd = `${datePartsProEnd[2]}-${datePartsProEnd[1]}-${datePartsProEnd[0]}`; // 24-04-2025

                $('#probation_end_date').datepicker('setDate', formattedDateProEnd);
            }
            probation_renew_date = staffData.probation_renew_date || '';
            if (probation_renew_date) {
                // Convert from Y-m-d to d-m-Y
                const datePartsRenew = probation_renew_date.split('-'); // [2025, 04, 24]
                const formattedDatePro = `${datePartsRenew[2]}-${datePartsRenew[1]}-${datePartsRenew[0]}`; // 24-04-2025

                $('#probation_renew_date').datepicker('setDate', formattedDatePro);
            }
            probation_enrollered_yes.checked = staffData.probation_enrollered == 1;
            probation_enrollered_no.checked = staffData.probation_enrollered == 0;
            student_loan.value = staffData.student_loan || '';
            dbs_clear_yes.checked = staffData.dbs_clear == 1;
            dbs_clear_no.checked = staffData.dbs_clear == 0;
            DBS_Update_yes.checked = staffData.dbs_service_update == 1;
            DBS_Update_no.checked = staffData.dbs_service_update == 0;
            dbs_number.value = staffData.dbs_number || '';
            leave_date.value = staffData.leave_date || '';
            email.value = staffData.email || '';
            mobile.value = staffData.mobile || '';
        }
        $('#addStaffWorkerModal').modal('show');
    });
});

function deleteStaff(id) {
    if (confirm('Are you sure you want to delete this staff?')) {
        $.ajax({
            url: deleteStaffWorker + "/" + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                alert(response.message);
                location.reload(); // or remove the row from DOM
            },
            error: function (err) {
                alert(response.message);
            }
        });
    }
}
