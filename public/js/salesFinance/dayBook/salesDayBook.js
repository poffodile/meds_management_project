$(document).ready(function () {

    taxRate(document.getElementById('getDataOnTax'));

    $('#Date_input').datepicker({
        format: 'dd-mm-yyyy'
    });

    $('#Date_input').on('change', function () {
        $('#Date_input').datepicker('hide');
    });

    $("#salesDayBookModel").scroll(function () {
        $('#Date_input').datepicker('place');
    });

    // const table = $('#salesDayBookTable').DataTable({
    //     dom: 'Blfrtip',
    //     buttons: [
    //         {
    //             extend: 'csv',
    //             text: 'Export',
    //             bom: true,
    //             exportOptions: {
    //                 columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
    //             }
    //         }
    //     ],
    //     footerCallback: function (row, data, start, end, display) {
    //         var api = this.api();

    //         var intVal = function (i) {
    //             return typeof i === 'string'
    //                 ? parseFloat(i.replace(/[£,]/g, '')) || 0
    //                 : typeof i === 'number'
    //                 ? i
    //                 : 0;
    //         };

    //         // Columns to total: netAmount (4), vatAmount (5), grossAmount (6)
    //         var columnsToTotal = [4, 5, 6];

    //         columnsToTotal.forEach(function (colIdx) {
    //             var total = api
    //                 .column(colIdx, { page: 'current' })
    //                 .data()
    //                 .reduce(function (a, b) {
    //                     return intVal(a) + intVal(b);
    //                 }, 0);

    //             $(api.column(colIdx).footer()).html('£' + total.toFixed(2));
    //         });
    //     }
    // });

    const table = $('#salesDayBookTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'Export',
                bom: true,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                }
            }
        ],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();

            // Helper to parse £ values
            var intVal = function (i) {
                return typeof i === 'string'
                    ? parseFloat(i.replace(/[£,]/g, '')) || 0
                    : typeof i === 'number'
                        ? i
                        : 0;
            };

            // Columns to total: Net Amount (4), VAT Amount (5), Gross Amount (6)
            [4, 5, 6].forEach(function (colIdx) {
                var total = api
                    .column(colIdx, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer cell
                $(api.column(colIdx).footer()).html('£' + total.toFixed(2));
            });
        }
    });


    const selectedVatId = document.getElementById('getDataOnTax').value;
    loadSalesDayBookData(selectedVatId);
    // Load all data initially

    function loadSalesDayBookData(selectedTaxRate) {
        $.ajax({
            url: getSalesDayBook,
            method: 'GET',
            data: {
                tax_rate: selectedTaxRate  // only pass if filtering is needed
            },
            success: function (response) {
                if (typeof isAuthenticated === "function") {
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                }
                console.log("response.data", response.data);
                allSalesData = response.data;
                renderSalesDayBook(allSalesData);
            },
            error: function (xhr) {
                console.error("Error loading data", xhr);
            }
        });
    }

    $('#getDataOnTax').on('change', function () {
        // const selectedTaxRate = $('#getDataOnTax option:selected').data('tax-rate');
        const selectedVatId = $(this).val();
        loadSalesDayBookData(selectedVatId);
        if (typeof selectedTaxRate === 'undefined' || selectedTaxRate === 0) {
            renderSalesDayBook(allSalesData);
        } else {
            const filtered = allSalesData.filter(item => parseFloat(item.tax_rate) === parseFloat(selectedTaxRate));
            renderSalesDayBook(filtered);
        }
    });

    // function renderSalesDayBook(data) {
    //     table.clear();

    //     data.forEach((item, index) => {
    //         const netAmount = parseFloat(item.netAmount ?? 0);
    //         const vatAmount = parseFloat(item.vatAmount ?? 0);
    //         const grossAmount = parseFloat(item.grossAmount ?? 0);
    //         const reclaim = parseFloat(item.reclaim ?? 0);
    //         const notReclaim = parseFloat(item.not_reclaim ?? 0);
    //         const expenseAmount = parseFloat(item.expense_amount ?? 0);
    //         const finalAmount = netAmount + vatAmount;

    //         const actions = `
    //             <a href="#!" class="openPurchaseDayBookModel"
    //                 data-action="edit"
    //                 data-id="${item.id}"
    //                 data-supplier_id="${item.supplier_id}"
    //                 data-date="${item.date}"
    //                 data-netAmount="${item.netAmount}"
    //                 data-vat="${item.Vat}"
    //                 data-vatAmount="${item.vatAmount}"
    //                 data-grossAmount="${item.grossAmount}"
    //                 data-reclaim="${item.reclaim}"
    //                 data-not_reclaim="${item.not_reclaim}"
    //                 data-expense_type="${item.expense_type}"
    //                 data-expense_amount="${item.expense_amount}">
    //                 <i class="fa fa-pencil" aria-hidden="true"></i>
    //             </a> |
    //             <a href="#!" class="deleteBtn" data-id="${item.id}">
    //                 <i class="fa fa-trash radStar" aria-hidden="true"></i>
    //             </a>
    //         `;

    //         table.row.add([
    //             index + 1,
    //             item.customer_name ?? '',
    //             item.date ?? '',
    //             item.netAmount ?? '',
    //             item.vatAmount ?? '',
    //             '£' + (item.grossAmount ?? ''),
    //             item.tax_rate_name ?? '',
    //             '£' + ((netAmount + vatAmount) - reclaim).toFixed(2),
    //             reclaim ? '£' + reclaim : '',
    //             notReclaim ? '£' + notReclaim : '',
    //             item.title ?? '',
    //             expenseAmount ? '£' + expenseAmount : '',
    //             actions
    //         ]);
    //     });

    //     table.draw();
    // }


    function renderSalesDayBook(data) {
        table.clear();

        data.forEach((item, index) => {
            const netAmount = parseFloat(item.netAmount ?? 0);
            const vatAmount = parseFloat(item.vatAmount ?? 0);
            const grossAmount = parseFloat(item.grossAmount ?? 0);
            const finalAmount = netAmount + vatAmount;

            // totalNetAmount += netAmount;
            // totalVatAmount += vatAmount;
            // totalGrossAmount += grossAmount;
            // totalFinalAmount += finalAmount;

            const actions = `<a href="#!" class="openSalesDayBookModel"
                           data-action="edit"
                           data-id="${item.id}"
                           data-customer_id="${item.customer_id}"
                           data-date="${item.date}"
                           data-invoice_no="${item.invoice_no}"
                           data-netAmount="${item.netAmount}"
                           data-vat="${item.Vat}"
                           data-vatAmount="${item.vatAmount}"
                           data-grossAmount="${item.grossAmount}">
                           <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a> |
                        <a href="#!" class="deleteBtn" data-id="${item.id}">
                           <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                        </a>
        `;


            table.row.add([
                index + 1,
                item.customer_name ?? '',
                item.date ?? '',
                item.invoice_no ?? '',
                '£' + item.netAmount ?? '',
                '£' + (item.vatAmount ?? ''),
                '£' + (item.grossAmount ?? ''),
                item.tax_rate_name ?? '',
                // item.finalAmount ?? '',
                actions
            ]);

        });
        table.draw();

    }


    $("#saveSalesDayBookModal").on("click", function (e) {
        // alert();
        e.preventDefault(); // Prevent page reload
        var sales_day_book_id = $("#sales_day_book_id").val();
        var url = saveSalesDayBook;
        if (sales_day_book_id != '') {
            url = editSalesDayBook;
        }
        // alert(url)
        $.ajax({
            url: url, // Laravel route
            type: "POST",
            data: $('#salesDayBookForm').serialize(), // Serialize form data
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                if (isAuthenticated(response) == false) {
                    return false;
                }
                alert(response.message);
                window.location.reload();
            },
            error: function (xhr) {
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

    // $(".deleteBtn").on("click", function () {
    //     let salesBookId = $(this).data("id"); // Get ID from button
    //     let row = $("#row-" + salesBookId); // Select the row

    //     if (confirm("Are you sure you want to delete this record?")) {
    //         $.ajax({
    //             url: salesDayBook + "/" + salesBookId,
    //             type: "POST",
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //             },
    //             success: function (response) {
    //                 if (response.success) {
    //                     // row.find("td:nth-child(7)").text(response.deleted_at); // Update deleted_at column
    //                     alert(response.message);
    //                     window.location.reload();
    //                 }
    //             },
    //             error: function () {
    //                 alert("Something went wrong!");
    //             },
    //         });
    //     }
    // });
});

$(document).on("click", ".deleteBtn", function () {
    let salesBookId = $(this).data("id"); // Get ID from button
    let row = $("#row-" + salesBookId); // Select the row

    if (confirm("Are you sure you want to delete this record?")) {
        $.ajax({
            url: salesDayBook + "/" + salesBookId,
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (response) {
                if (isAuthenticated(response) == false) {
                    return false;
                }
                if (response.success) {
                    // row.find("td:nth-child(7)").text(response.deleted_at); // Update deleted_at column
                    alert(response.message);
                    window.location.reload();
                }
            },
            error: function () {
                alert("Something went wrong!");
            },
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    let vatInput = document.getElementById('vat_input');
    let netAmountInput = document.getElementById('net_amount');
    let vatAmountInput = document.getElementById('vat_amount');
    let grossAmountInput = document.getElementById('gross_amount');

    function calculateTax() {
        let netAmount = parseFloat(netAmountInput.value) || 0;
        let selectedOption = vatInput.options[vatInput.selectedIndex];
        let taxRate = parseFloat(selectedOption.getAttribute('data-tax-rate')) || 0;

        let vatAmount = (netAmount * taxRate) / 100;
        let grossAmount = netAmount + vatAmount;

        vatAmountInput.value = vatAmount.toFixed(2);
        grossAmountInput.value = grossAmount.toFixed(2);
    }

    // Trigger calculation on VAT change or Net Amount change
    vatInput.addEventListener('change', calculateTax);
    netAmountInput.addEventListener('input', calculateTax);
});

function getCustomerList() {
    $.ajax({
        url: customerList,
        method: 'GET',
        success: function (response) {
            if (isAuthenticated(response) == false) {
                return false;
            }
            console.log("Customer list data:", response.data);
            const selectedCustomerId = document.getElementById('customer_id').value;
            const customerSelect = document.getElementById('getCustomerList');
            customerSelect.innerHTML = '<option>Select Customer</option>';

            if (Array.isArray(response.data)) {
                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    if (selectedCustomerId && user.id == selectedCustomerId) {
                        option.selected = true;
                    }
                    customerSelect.appendChild(option);
                });
            } else {
                console.warn("response.data is not an array");
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
        }
    });
}

function taxRate(dropdown) {
    $.ajax({
        url: getTaxRate,
        method: 'GET',
        success: function (response) {
            if (isAuthenticated(response) == false) {
                return false;
            }
            console.log("response.data", response.data);
            if (Array.isArray(response.data)) {
                // const dropdown = document.getElementById('vat_input'); // Assumes ID is 'vat_input'
                if (!dropdown) {
                    console.error("Element with ID not found");
                    return;
                }

                dropdown.innerHTML = ''; // Clear existing options

                const optionInitial = document.createElement('option');
                const preTaxID = document.getElementById('tax_id').value;
                optionInitial.textContent = "Please Select";
                optionInitial.value = 0;
                dropdown.appendChild(optionInitial);

                response.data.forEach(code => {
                    const option = document.createElement('option');
                    option.value = code.id;
                    option.textContent = code.name + " (" + code.tax_rate + "%)";
                    option.setAttribute('data-tax-rate', code.tax_rate);
                    if (preTaxID && code.id == preTaxID) {
                        option.selected = true;
                    }
                    dropdown.appendChild(option);
                });
            } else {
                console.error("Invalid response format");
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {

    document.addEventListener('click', function (event) {
        if (event.target.closest('.openSalesDayBookModel')) {
            const btn = event.target.closest('.openSalesDayBookModel');

            getCustomerList();
            taxRate(document.getElementById('vat_input'));
            const action = btn.getAttribute('data-action');
            const modalTitle = document.getElementById('modalTitle');
            const sales_day_book_id = document.getElementById('sales_day_book_id');
            const customer_id = document.getElementById('customer_id');
            const Date_input = document.getElementById('Date_input');
            const Invoice_input = document.getElementById('Invoice_input');
            const net_amount = document.getElementById('net_amount');
            const tax_id = document.getElementById('tax_id');
            const vat_amount = document.getElementById('vat_amount');
            const gross_amount = document.getElementById('gross_amount');
            if (action === 'add') {
                modalTitle.textContent = 'Add Sales Day Book';
            } else if (action === 'edit') {
                modalTitle.textContent = 'Edit Sales Day Book';
                sales_day_book_id.value = btn.getAttribute('data-id');
                customer_id.value = btn.getAttribute('data-customer_id');
                // Date_input.value = btn.getAttribute('data-date');
                const originalDate = btn.getAttribute('data-date');
                let formattedDate = originalDate;
                if (originalDate && originalDate.includes('-')) {
                    const parts = originalDate.split('-'); // [yyyy, mm, dd]
                    formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`; // dd-mm-yyyy
                }
                Date_input.value = formattedDate;

                Invoice_input.value = btn.getAttribute('data-invoice_no');
                net_amount.value = btn.getAttribute('data-netAmount');
                tax_id.value = btn.getAttribute('data-vat');
                vat_amount.value = btn.getAttribute('data-vatAmount');
                gross_amount.value = btn.getAttribute('data-grossAmount');

            }

            $('#salesDayBookModel').modal('show');
        }
    });


});