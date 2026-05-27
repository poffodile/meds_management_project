$(document).ready(function () {
    loadTimeSheetTable(); // Loads all data

    $('#timeSheetDt').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true, // Optional: close picker after selection
        todayHighlight: true // Optional: highlight today's date
    });
    $('#timeSheetDt').on('change', function () {
        $('#timeSheetDt').datepicker('hide');
    });
});

let timeSheetTable;

function loadTimeSheetTable(userId = '') {
    // Destroy existing table if already initialized
    if ($.fn.DataTable.isDataTable('#timeSheetTable')) {
        timeSheetTable.destroy();
        $('#timeSheetTable').empty(); // remove old header/footer
        $('#timeSheetTable').html(`
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Total Shift Hours</th>
                    <th>Category</th>
                    <th>Hours</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th style="text-align:right">Total:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        `);
    }

    timeSheetTable = $('#timeSheetTable').DataTable({
        ajax: {
            url: getData, // Laravel route
            type: 'POST',
            data: { user_id: userId },
            dataSrc: '',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'user', defaultContent: '' },
            {
                data: 'date',
                render: function (data) {
                    if (!data) return '';

                    const date = new Date(data);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                    const year = date.getFullYear();

                    return `${day}-${month}-${year}`; // Format: dd-mm-yyyy
                }
            },
            {
                data: 'total_shift_hours',
                render: function (data) {
                    const num = parseFloat(data) || 0;
                    return (num % 1 === 0) ? parseInt(num) : num.toFixed(2);
                }
            },
            { data: 'category_type' }, // Category name (Sleep, Disturbance, etc.)
            {
                data: 'hours',
                render: function (data) {
                    const num = parseFloat(data) || 0;
                    return (num % 1 === 0) ? parseInt(num) : num.toFixed(2);
                }
            },
            { data: 'comments' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <a href="#!" class="openTimeSheetModel"
                            data-action="edit"
                            data-id="${row.id}"
                            data-user="${row.user}"
                            data-user_id="${row.user_id}"
                            data-category_id="${row.category_id}"
                            data-date="${row.date}"
                            data-hours="${row.hours}"
                            data-comments="${row.comments}">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a> |
                        <a href="#!" onclick="deleteStaff(${row.id})" class="deleteBtn">
                            <i class="fa fa-trash radStar" aria-hidden="true"></i>
                        </a>
                    `;
                }
            }
        ],
        footerCallback: function (row, data, start, end, display) {
            const api = this.api();

            const getTotal = (columnIndex) => {
                return api
                    .column(columnIndex, { page: 'current' })
                    .data()
                    .reduce((a, b) => {
                        const val = parseFloat(b);
                        return a + (isNaN(val) ? 0 : val);
                    }, 0);
            };

            const formatTotal = (num) => {
                return num % 1 === 0 ? parseInt(num) : num.toFixed(2);
            };

            // Set footer cell totals
            $(api.column(3).footer()).html(formatTotal(getTotal(3))); // Total Shift Hours
            $(api.column(5).footer()).html(formatTotal(getTotal(5))); // Hours
        }
    });

    // Optional: fix table layout issues after redraw
    timeSheetTable.on('draw', function () {
        timeSheetTable.columns.adjust();
    });
}






$('#getDataOnUsers').on('change', function () {
    const selectedUserId = $(this).val(); // gets '' if none selected
    loadTimeSheetTable(selectedUserId);
});





$(document).ready(function () {
    $('#save_time_sheet').on('click', function (e) {
        e.preventDefault(); // prevent form from submitting normally
        var error=0;
        $('.checkInput').each(function(){
            var formfield=$(this).val();
            if(formfield == '' || formfield == undefined){
                error=1;
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            }else{
                $(this).css('border','');
                error=0;
            }
        });
        if(error == 1){
            return false;
        }
        const formArray = $('#time_sheet').serializeArray();
        const formData = {};
        formArray.forEach(item => {
            formData[item.name] = item.value;
        });

        var time_sheetId = $("#time_sheetId").val();
        var url = timeSheetSaveUrl;
        if (time_sheetId == '') {
            url = timeSheetEditUrl;
        }
        $.ajax({
            url: url, // Replace with your actual backend endpoint
            method: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function (response) {
                if (isAuthenticated(response) == false) {
                    return false;
                }
                console.log("response", response.message);

                $('#addTimeSheetModal').modal('hide');
                $('#time_sheet')[0].reset();
                alert(response.message);
                location.reload();
            },
            error: function (xhr, status, error) {
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
                            inputField.after(`<span class="text-danger">${value[0]}</span>`);
                        }

                        // Collect all messages for optional alert box
                        errorMessages += value[0] + "\n";
                    });

                    // Optional: show all errors in a single alert box
                    // alert("Please fix the following errors:\n" + errorMessages);
                } else {
                    alert("Something went wrong. Please try again.");
                }
            }
        });
    });
});

$(document).on('click', '.openTimeSheetModel', function () {
    const action = $(this).data('action');

    if (action === 'add') {
        $('#modalTitle').text('Add Time Sheet');
        $('#time_sheet')[0].reset();
    } else if (action === 'edit') {
        $('#modalTitle').text('Edit Time Sheet');
        $('#time_sheet_id').val($(this).data('id'));
        $('#user_id').val($(this).data('user_id'));
        const date = $(this).data('date');
        // $('#timeSheetDt').val($(this).data('date'));
        if (date) {
            // Convert from Y-m-d to d-m-Y
            const dateParts = date.split('-'); // [2025, 04, 24]
            const formattedDate = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`; // 24-04-2025

            $('#timeSheetDt').datepicker('setDate', formattedDate);
        }
        $('#hours').val($(this).data('hours'));
        $('#category_id').val($(this).data('category_id'));
        $('#comments').val($(this).data('comments'));
    }

    $('#addTimeSheetModal').modal('show');
});

function deleteStaff(id) {
    if (confirm('Are you sure you want to delete this record?')) {
        $.ajax({
            url: deleteTimeSheet + "/" + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                   if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                alert(response.message);
                location.reload(); // or remove the row from DOM
            },
            error: function (err) {
                alert(response.message);
            }
        });
    }
}