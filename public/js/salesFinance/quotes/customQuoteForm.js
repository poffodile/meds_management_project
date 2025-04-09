var editor_config = {
    toolbar: [{
        name: 'basicstyles',
        items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
    },
    {
        name: 'format',
        items: ['Format']
    },
    {
        name: 'paragraph',
        items: ['Indent', 'Outdent', '-', 'BulletedList', 'NumberedList']
    },
    {
        name: 'link',
        items: ['Link', 'Unlink']
    },
    {
        name: 'undo',
        items: ['Undo', 'Redo']
    }
    ],
};

CKEDITOR.replace('textarea8', editor_config);
CKEDITOR.replace('textarea9', editor_config);
CKEDITOR.replace('textarea10', editor_config);
CKEDITOR.replace('textarea11', editor_config);

document.addEventListener("DOMContentLoaded", function () {

    const page_type = parseInt(document.getElementById('page_type').value, 10);
    console.log("page_type", page_type);

    if (page_type === 1) {
        document.getElementById("hideQuoteDetails").style.display = "none";
        document.getElementById("hideItemDetails").style.display = "none";
        document.getElementById("hideExtraInformation").style.display = "none";
        document.getElementById("hideDepositSection").style.display = "none";
    } else if (page_type === 2) {

        // getQuoteType(document.getElementById('quoteType').value);
        getQuoteType(document.getElementById('quoteType'));
        document.getElementById('hideCustomerDetails').style.display = "none";
        document.getElementById('yourQuoteSection').style.display = "none";
        document.getElementById('hidequoteTasks').style.display = "none";
    }

    const setCustomerId = document.getElementById('setCustomerId').value;

    $.ajax({
        url: getCustomerData,
        success: function (response) {
            console.log(response.data);
            var get_customer_type = document.getElementById('getCustomerListedit');
            // get_customer_type.innerHTML = '';

            response.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.text = user.name;
                if (user.id == setCustomerId) {
                    option.selected = true; // Mark as selected
                    document.getElementById('setCustomerNameInCustomerdetails').value = user.name;
                }
                get_customer_type.appendChild(option);
            });
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });


    const edit_customer_billing_id = document.getElementById('edit_customer_billing_id').value;
    if (setCustomerId === edit_customer_billing_id) {
        $.ajax({
            url: getCustomerBillingAddress,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: setCustomerId
            },
            success: function (response) {
                console.log(response.data);
                var billingDetailContact = document.getElementById('billingDetailContact');
                billingDetailContact.innerHTML = '';

                const optionDefault = document.createElement('option');
                optionDefault.value = setCustomerId;
                optionDefault.text = "Default";
                billingDetailContact.appendChild(optionDefault);

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.contact_name;
                    billingDetailContact.appendChild(option);
                });
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    } else {
        setCustomerBillingData(edit_customer_billing_id);
    }

    const edit_site_id = document.getElementById('edit_customer_billing_id').value;
    if (setCustomerId === edit_customer_billing_id) {
        $.ajax({
            url: getCustomerSiteAddressURL,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: setCustomerId
            },
            success: function (response) {
                console.log(response.data);

                const optionDefault = document.createElement('option');
                optionDefault.value = setCustomerId;
                optionDefault.text = "Same As Default";
                billingDetailContact.appendChild(optionDefault);

                response.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.text = user.site_name;
                    const option1 = option.cloneNode(true);
                    customerSiteDetails.appendChild(option);
                    customerSiteDelivery.appendChild(option1);
                });

            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }


    // start here js for time start and pause
    let timerInterval;
    let elapsedSeconds = 0;
    let isRunning = false;

    function toggleTimer() {
        if (isRunning) {
            // Pause the timer
            clearInterval(timerInterval);
            document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-play"></i> Start';
        } else {
            // Start the timer
            timerInterval = setInterval(function () {
                elapsedSeconds++;
                document.getElementById('timerDisplay').textContent = formatTime(elapsedSeconds);
                document.getElementById('start_time_timer').value = formatTime(elapsedSeconds);
            }, 1000);
            document.getElementById('toggleTimerBtn').innerHTML = '<i class="fa fa-stop"></i> Pause';
        }
        isRunning = !isRunning; // Toggle the running state
    }

    function formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
    }

    function pad(number) {
        return number < 10 ? '0' + number : number;
    }

    document.getElementById('toggleTimerBtn').addEventListener('click', toggleTimer);

    getQuoteTaskList(document.querySelector('#quoteTaskList tbody'));
    // document.getElementById('hideQuoteDiv').style.display = "none";
    // document.getElementById('hideDepositSection').style.display = "none";

    // Enable/disable "Delete Selected" button based on checkbox selection
    $(document).on("change", ".selectRow, #selectAll", function () {
        const anySelected = $(".selectRow:checked").length > 0;
        $("#deleteSelected").prop("disabled", !anySelected);

        if (this.id === "selectAll") {
            $(".selectRow").prop("checked", $(this).prop("checked"));
        }
    });

    // Handle delete selected rows
    $("#deleteSelected").click(function () {
        if (confirm("Are you sure you want to delete the selected rows?")) {
            // Collect all selected row IDs
            const ids = $(".selectRow:checked")
                .map(function () {
                    return $(this).closest("tr").data("id");
                })
                .get();

            if (ids.length > 0) {
                // Send AJAX request to delete rows
                $.ajax({
                    url: '{{ route("quote.ajax.deleteAttachment") }}', // Your server endpoint
                    method: 'POST', // HTTP method
                    data: {
                        ids: ids,
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function (response) {
                        if (response.success) {
                            // Remove rows from the table
                            $(".selectRow:checked").each(function () {
                                $(this).closest("tr").remove();
                            });
                            alert("Selected rows deleted successfully.");
                            $("#deleteSelected").prop("disabled", true);
                            $("#selectAll").prop("checked", false);
                        } else {
                            alert("Failed to delete the selected rows.");
                        }
                    },
                    error: function () {
                        alert("An error occurred while deleting the rows.");
                    }
                });
            }
        }
    });

    // Handle "Download Selected" button
    $("#downloadSelected").click(function () {
        const selectedFiles = [];

        // Collect selected files
        $(".selectRow:checked").each(function () {
            const row = $(this).closest("tr");
            const fileUrl = row.find("a[target='_blank']").attr("href");
            selectedFiles.push(fileUrl);
        });

        if (selectedFiles.length > 0) {
            downloadMultipleFiles(selectedFiles);
        }
    });

    // Function to trigger download for each file
    function downloadMultipleFiles(files) {
        files.forEach((fileUrl) => {
            const a = document.createElement("a");
            a.href = fileUrl;
            a.download = ""; // Optional: Set custom filename
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    // Enable/disable "Delete Selected" button based on checkbox selection
    $(document).on("change", ".selectRow, #selectAll", function () {
        const anySelected = $(".selectRow:checked").length > 0;
        $("#deleteSelected").prop("disabled", !anySelected);

        if (this.id === "selectAll") {
            $(".selectRow").prop("checked", $(this).prop("checked"));
        }
    });

    // Handle delete selected rows
    $("#deleteSelected").click(function () {
        if (confirm("Are you sure you want to delete the selected rows?")) {
            // Collect all selected row IDs
            const ids = $(".selectRow:checked")
                .map(function () {
                    return $(this).closest("tr").data("id");
                })
                .get();

            if (ids.length > 0) {
                // Send AJAX request to delete rows
                $.ajax({
                    url: '{{ route("quote.ajax.deleteAttachment") }}', // Your server endpoint
                    method: 'POST', // HTTP method
                    data: {
                        ids: ids,
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function (response) {
                        if (response.success) {
                            // Remove rows from the table
                            $(".selectRow:checked").each(function () {
                                $(this).closest("tr").remove();
                            });
                            alert("Selected rows deleted successfully.");
                            $("#deleteSelected").prop("disabled", true);
                            $("#selectAll").prop("checked", false);
                        } else {
                            alert("Failed to delete the selected rows.");
                        }
                    },
                    error: function () {
                        alert("An error occurred while deleting the rows.");
                    }
                });
            }
        }
    });

    // Handle "Download Selected" button
    $("#downloadSelected").click(function () {
        const selectedFiles = [];

        // Collect selected files
        $(".selectRow:checked").each(function () {
            const row = $(this).closest("tr");
            const fileUrl = row.find("a[target='_blank']").attr("href");
            selectedFiles.push(fileUrl);
        });

        if (selectedFiles.length > 0) {
            downloadMultipleFiles(selectedFiles);
        }
    });

    // Function to trigger download for each file
    function downloadMultipleFiles(files) {
        files.forEach((fileUrl) => {
            const a = document.createElement("a");
            a.href = fileUrl;
            a.download = ""; // Optional: Set custom filename
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    const $prevButton = $("#prevTab");
    const $nextButton = $("#nextTab");
    const $saveButton = $("#saveButton");
    const $cancelButton = $("#cancelButton");
    const $closeButton = $("#closeButton");

    // Initial setup
    updateButtons();

    // Event listeners for navigation
    $prevButton.click(function () {
        navigateTab(-1);
    });

    $nextButton.click(function () {
        navigateTab(1);
    });

    $saveButton.click(function () {
        const quote_id = document.getElementById('quote_id').value;
        var data = {
            quote_id: quote_id,
            customer_id: document.getElementById('setCustomerId').value,
            deposit_percantage: document.getElementById('deposit_percantage').value,
            amount: document.getElementById('deposit_amount').value,
            reference: document.getElementById('reference').value,
            description: document.getElementById('description').value,
            payment_type: document.getElementById('payment_type').value,
            deposit_date: document.getElementById('deposit_date').value,
            quote_deposit_id: document.getElementById('quote_deposit_id').value,
        };
        console.log(data);
        $.ajax({
            url: '{{ route("quote.ajax.saveQuoteDeposite") }}', // Your server endpoint
            method: 'POST', // HTTP method
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                // alert(response.data);
                $("#creaditDepositModal").modal("hide"); // Close the modal
                getDepositData(quote_id);
            },
            error: function () {
                alert("An error occurred while deleting the rows.");
            }
        });
    });

    function navigateTab(offset) {
        const $tabs = $("#modalTabs .nav-link");
        const $activeTab = $tabs.filter(".active");
        let currentIndex = $tabs.index($activeTab);
        let newIndex = currentIndex + offset;

        // Ensure the new index is within bounds
        if (newIndex >= 0 && newIndex < $tabs.length) {
            $tabs.eq(newIndex).tab("show"); // Bootstrap's tab method
            updateButtons();
        }
    }

    function updateButtons() {
        const $tabs = $("#modalTabs .nav-link");
        const $activeTab = $tabs.filter(".active");
        const currentIndex = $tabs.index($activeTab);

        // First Tab: Show Next and Close
        if (currentIndex === 0) {
            $prevButton.hide();
            $nextButton.show();
            $saveButton.hide();
            $closeButton.show();
        }

        // Second Tab: Show Previous, Save, and Cancel
        else if (currentIndex === 1) {
            $prevButton.show();
            $nextButton.hide();
            $saveButton.show();
            $cancelButton.show();
            $closeButton.hide();
        }
    }

    // Update buttons on tab change
    $("#modalTabs .nav-link").on("shown.bs.tab", function () {
        updateButtons();
    });


    getDepositData(document.getElementById('quote_id').value);
    // Enable/disable "Delete Selected" button based on checkbox selection
    $(document).on("change", ".selectRow, #selectAll", function () {
        const anySelected = $(".selectRow:checked").length > 0;
        $("#deleteSelected").prop("disabled", !anySelected);

        if (this.id === "selectAll") {
            $(".selectRow").prop("checked", $(this).prop("checked"));
        }
    });

    // Handle delete selected rows
    $("#deleteSelected").click(function () {
        if (confirm("Are you sure you want to delete the selected rows?")) {
            // Collect all selected row IDs
            const ids = $(".selectRow:checked")
                .map(function () {
                    return $(this).closest("tr").data("id");
                })
                .get();

            if (ids.length > 0) {
                // Send AJAX request to delete rows
                $.ajax({
                    url: '{{ route("quote.ajax.deleteAttachment") }}', // Your server endpoint
                    method: 'POST', // HTTP method
                    data: {
                        ids: ids,
                        _token: '{{ csrf_token() }}' // CSRF token for Laravel
                    },
                    success: function (response) {
                        if (response.success) {
                            // Remove rows from the table
                            $(".selectRow:checked").each(function () {
                                $(this).closest("tr").remove();
                            });
                            alert("Selected rows deleted successfully.");
                            $("#deleteSelected").prop("disabled", true);
                            $("#selectAll").prop("checked", false);
                        } else {
                            alert("Failed to delete the selected rows.");
                        }
                    },
                    error: function () {
                        alert("An error occurred while deleting the rows.");
                    }
                });
            }
        }
    });

    // Handle "Download Selected" button
    $("#downloadSelected").click(function () {
        const selectedFiles = [];

        // Collect selected files
        $(".selectRow:checked").each(function () {
            const row = $(this).closest("tr");
            const fileUrl = row.find("a[target='_blank']").attr("href");
            selectedFiles.push(fileUrl);
        });

        if (selectedFiles.length > 0) {
            downloadMultipleFiles(selectedFiles);
        }
    });

    // Function to trigger download for each file
    function downloadMultipleFiles(files) {
        files.forEach((fileUrl) => {
            const a = document.createElement("a");
            a.href = fileUrl;
            a.download = ""; // Optional: Set custom filename
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    // const $prevButton = $("#prevTab");
    // const $nextButton = $("#nextTab");

    // Initial setup
    updateButtons();

    // Event listeners for navigation
    $prevButton.click(function () {
        navigateTab(-1);
    });

    $nextButton.click(function () {
        navigateTab(1);
    });

    function navigateTab(offset) {
        const $tabs = $("#modalTabs .nav-link");
        const $activeTab = $tabs.filter(".active");
        let currentIndex = $tabs.index($activeTab);

        let newIndex = currentIndex + offset;

        // Ensure the new index is within bounds
        if (newIndex >= 0 && newIndex < $tabs.length) {
            $tabs.eq(newIndex).tab("show"); // Bootstrap's tab method
            updateButtons();
        }
    }

    function updateButtons() {
        const $tabs = $("#modalTabs .nav-link");
        const $activeTab = $tabs.filter(".active");
        const currentIndex = $tabs.index($activeTab);

        // Hide Previous button on the first tab
        if (currentIndex === 0) {
            $prevButton.hide();
        } else {
            $prevButton.show();
        }

        // Hide Next button on the last tab
        if (currentIndex === $tabs.length - 1) {
            $nextButton.hide();
        } else {
            $nextButton.show();
        }
    }

    // Update buttons on tab change
    $("#modalTabs .nav-link").on("shown.bs.tab", function () {
        updateButtons();
    });


    $('#getTaxtInvoiceRateValue').on('click', function () {
        $.ajax({
            url: getActiveTaxRateURL,
            method: 'GET',
            success: function (response) {
                console.log("response.data", response.data);
                if (Array.isArray(response.data)) {
                    // Iterate over all Account Code dropdowns and populate them
                    const dropdown = document.getElementById('getTaxRateValue');
                    dropdown.innerHTML = ''; // Clear existing options

                    const optionInitial = document.createElement('option');
                    optionInitial.textContent = "Please Select"; // Use appropriate key from your response
                    optionInitial.value = 0;
                    dropdown.appendChild(optionInitial);
                    // Append new options
                    response.data.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id; // Use appropriate key from your response
                        option.setAttribute('data-rate', code.tax_rate);
                        option.textContent = code.name; // Use appropriate key from your response
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
    });

    // document.getElementById('hideCustomerDetails').style.display = "none";
    // document.getElementById('hideTaskData').style.display = "none";
    // document.getElementById('yourQuoteSection').style.display = "none";
    getTags(document.getElementById('quoteTag'))

    $('#OpenQuoteTypeModel').on('click', function () {
        $('#quoteTypeModal').modal('show');
    });

    $('#OpenAddQuoteSourceModal').on('click', function () {
        $('#quoteSourceModal').modal('show');
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    getInvoiceDeposit(document.getElementById('quote_id').value);
    // getQuoteType(document.getElementById('quoteType').value);
    getQuoteAttachmentsOnPageLoad();

    $('#search-product').on('keyup', function () {
        let query = $(this).val();
        const divList = document.querySelector('.parent-container');

        if (query === '') {
            divList.innerHTML = '';
        }

        // Make an AJAX call only if query length > 2
        if (query.length > 2) {
            $.ajax({
                url: "{{ route('item.ajax.searchProduct') }}", // Laravel route
                method: 'GET',
                data: {
                    query: query
                },
                success: function (response) {
                    console.log(response);
                    // $('#results').html(response);
                    divList.innerHTML = "";
                    const div = document.createElement('div');
                    div.className = 'container'; // Optional: Add a class to the div for styling

                    // Step 2: Create a ul (unordered list)
                    const ul = document.createElement('ul');
                    ul.id = "productList";
                    // Step 3: Loop through the data and create li (list item) for each entry
                    response.forEach(item => {
                        const li = document.createElement('li'); // Create a new li element
                        li.textContent = item.product_name; // Set the text of the li item
                        li.id = item.id;
                        li.className = "editInput";
                        ul.appendChild(li); // Append the li to the ul
                    });

                    // Step 4: Append the ul to the div
                    div.appendChild(ul);

                    // Step 5: Append the div to the parent container in the HTML
                    divList.appendChild(div);

                    ul.addEventListener('click', function (event) {
                        divList.innerHTML = '';
                        document.getElementById('search-product').value = '';
                        // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                        if (event.target.tagName.toLowerCase() === 'li') {
                            const selectedId = event.target.id; // Get the ID of the clicked <li>
                            console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                            getProductData(selectedId);
                        }
                    });
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#results').empty(); // Clear results if the input is empty
        }
    });


    // get all product from quote_id
    const quote_id = document.getElementById('quote_id').value;
    $.ajax({
        url: getQuoteProductListURL,
        method: 'POST',
        data: {
            id: quote_id
        },
        success: function (response) {
            console.log(response.data);

            // response.data.forEach(data => {
            quoteProductTable(response.data, 'quoteProducts', 'edit');
            // });
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });



    $('#saveQuoteTag').on('click', function () {
        var quoteTag = document.getElementById('quoteTag');
        saveFormData(
            'add_quote_tag_form', // formId
            '{{ route("General.ajax.saveQuoteTag") }}', // saveUrl
            'quoteTagModal', // modalId
            getTags, // callback function after success
            quoteTag
        );
    });

    $('#saveQuoteTypeQuote').on('click', function () {
        var formData = $('#add_quote_type_form').serialize();
        $.ajax({
            url: '{{ route("quote.ajax.saveQuoteType") }}',
            method: 'POST',
            data: formData,
            success: function (response) {
                // alert(response.message);
                console.log(response.id);
                setSiteAddressDetails(response.id);
                $('#quoteTypeModal').modal('hide');
                // getQuoteType(document.getElementById('quoteType'));
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    $('#billingDetailContact').on('change', function () {
        var selected = document.getElementById('getCustomerList').value;
        console.log(selected);
        console.log("$(this).val()", $(this).val());
        if ($(this).val() === selected) {
            getBillingDetailsData($(this).val());
        } else {
            setCustomerBillingData($(this).val());
        }
    });



    $('#AddQuoteButton').on('click', function () {
        var customer = document.getElementById('getCustomerListedit').value;
        if (customer === "") {
            alert('Please select the customer');
        } else {
            getTags(document.getElementById('quoteTag'))
            // getRegions(document.getElementById('siteDeliveryRegions'));
            const selectCustomer = document.getElementById('getCustomerListedit');
            // const selectedText = selectCustomer.options[selectCustomer.selectedIndex].text;
            // document.getElementById('setCustomerNameInCustomerdetails').value = selectedText;
            // document.getElementById('yourQuoteSection').style.display = "none";
            // document.getElementById('hideQuoteDiv').style.display = "block";
            // document.getElementById('hideCustomerDetails').style.display = "none";
            // document.getElementById('hideQuoteDetails').style.display = "block";
        }
    });

    $('#saveCustomerContactData').on('click', function () {
        var formData = $('#add_customer_contact_form').serialize();
        $.ajax({
            url: '{{ route("customer.ajax.SaveCustomerContactData") }}',
            method: 'POST',
            data: formData,
            success: function (response) {
                console.log(response);
                alert(response.message);
                setCustomerBillingData(response.lastid);
                $('#add_customer_contact_modal').modal('hide');
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    // Ajax Call for saving Customer Type
    $('#saveCustomerSiteDetails').on('click', function () {
        var formData = $('#add_customer_site_details_form').serialize();
        $.ajax({
            url: '{{ route("customer.ajax.saveCustomerSiteAddress") }}',
            method: 'POST',
            data: formData,
            success: function (response) {
                alert(response.message);
                console.log(response.id);
                setSiteAddressDetails(response.id);
                // removeAddCustomerSiteAddress(document.getElementById('customerSiteDetails'),document.getElementById('customerSiteDelivery'), response.id);
                $('#add_site_address_modal').modal('hide');
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });

    $('#deposit_percantage').on('input', function () {
        let percentage = parseInt($(this).val(), 10);

        if (percentage > 100) {
            document.getElementById('deposit_percantage').value = 100;
            percentage = 100;
        }
        if (isNaN(percentage)) {
            percentage = 0;
        }
        let outsatandingAmount = document.getElementById('setOustandingCreditAmount').value;
        outsatandingAmount = parseFloat(outsatandingAmount.replace("£", ""))

        console.log(outsatandingAmount);
        console.log(percentage);

        deposit_amount = (percentage / 100) * outsatandingAmount;
        document.getElementById('deposit_amount').value = deposit_amount.toFixed(2);
    });

    $('#deposit_percentage_invoice').on('input', function () {
        let percentage = parseInt($(this).val(), 10);

        if (percentage > 100) {
            document.getElementById('deposit_percentage_invoice').value = 100;
            percentage = 100;
        }
        if (isNaN(percentage)) {
            percentage = 0;
        }
        let outsatandingAmount = document.getElementById('setDepositAmount').value;
        outsatandingAmount = parseFloat(outsatandingAmount.replace("% of  £", ""))

        console.log(outsatandingAmount);
        console.log(percentage);

        deposit_amount = (percentage / 100) * outsatandingAmount;
        document.getElementById('sub_total_invoice').value = deposit_amount.toFixed(2);
        depositInvoice = (deposit_amount * 20) / 100;
        document.getElementById('setDepositInvoiceAmountHidden').value = (deposit_amount + depositInvoice).toFixed(2);
        document.getElementById('setDepositInvoiceAmount').value = "£" + (deposit_amount + depositInvoice).toFixed(2);

    });

    $('#getTaxRateValue').on('change', function () {

        var id = $(this).val();
        const selectedOption = $(this).find(':selected');
        const rate = selectedOption.data('rate');
        document.getElementById('getTaxtRateHidden').value = rate;
        const sub_total_invoice = document.getElementById('sub_total_invoice').value;
        console.log("sub_total_invoice", sub_total_invoice);
        let total = (sub_total_invoice * rate) / 100;
        console.log("total", total);
        total = "£" + (parseFloat(sub_total_invoice) + parseFloat(total)).toFixed(2)
        console.log("total s", total);
        document.getElementById('setDepositInvoiceAmount').value = total;

    });

    $('#OpenAddCustomerContact').on('click', function () {
        getCustomerJobTitle(document.getElementById('customer_job_titile_id'));
        $('#add_customer_contact_modal').modal('show');
    });

    $('#OpenAddQuoteTag').on('click', function () {
        $('#quoteTagModal').modal('show');
    });

    $('#openCustomerSiteAddress').on('click', function () {
        var customer = document.getElementById('getCustomerListedit').value;
        if (customer === "") {
            alert('Please select the customer');
        } else {
            getRegions(document.getElementById('getSiteAddressRegion'));
            getCountriesListWithNameCode(document.getElementById('siteAddressCountry'));
            getCustomerJobTitle(document.getElementById('siteJobTitle'));
            getCountriesList(document.getElementById('siteAddressMobileCode'));
            getCountriesList(document.getElementById('siteAddressTelephoneCode'));
            $('#add_site_address_modal').modal('show');
        }
    });


    $('#saveInvoiceDepositAmount').on('click', function () {

        const subTotal = document.getElementById('sub_total_invoice').value;
        const varPer = document.getElementById('getTaxtRateHidden').value
        const quote_id = document.getElementById('quote_id').value;
        const vatAmount = (subTotal * varPer) / 100;

        data = {
            edit_customer_deposit_invoice: document.getElementById('edit_customer_deposit_invoice').value,
            quote_id: quote_id,
            customer_id: document.getElementById('setCustomerId').value,
            invoice_date: document.getElementById('invoice_date').value,
            due_date: document.getElementById('due_date').value,
            line_item: document.getElementById('line_item').value,
            description: document.getElementById('line_description').value,
            amount: document.getElementById('setDepositAmountHidden').value,
            deposit_percentage: document.getElementById('deposit_percentage_invoice').value,
            sub_total: document.getElementById('sub_total_invoice').value,
            VAT_amount: vatAmount,
            VAT_id: document.getElementById('getTaxRateValue').value,
            total: document.getElementById('setDepositInvoiceAmountHidden').value,
            outstanding: parseFloat(document.getElementById('setDepositAmountHidden').value - document.getElementById('setDepositInvoiceAmountHidden').value)
        };

        $.ajax({
            url: '{{ route("quote.ajax.saveInvoiceDeposite") }}', // Your server endpoint
            method: 'POST', // HTTP method
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log(response);
                // alert(response.data);
                $("#creaditDepositInvoiceModal").modal("hide"); // Close the modal
                getInvoiceDeposit(quote_id);
            },
            error: function () {
                alert("An error occurred while deleting the rows.");
            }
        });


    });

    $('#new_Attachment_open_model').on('click', function () {
        $('#new_Attachment_model').modal('show');
    });

    $('#saveAttachmentType').on('click', function (e) {

        let formData = new FormData($('#attachmentTypeForm')[0]);
        console.log(formData);

        $.ajax({
            url: '{{ route("quote.ajax.saveAttachmentData") }}', // Replace with your Laravel route URL
            type: 'POST',
            data: formData,
            contentType: false, // Required for FormData
            processData: false, // Required for FormData
            success: function (response) {
                // Handle success
                console.log(response.id);
                $('#new_Attachment_model').modal('hide'); // Hide the modal
                getQuoteAttachments(response.id);
            },
            error: function (xhr) {
                // Handle error
                const errors = xhr.responseJSON.errors || {
                    message: xhr.responseJSON.message
                };
                let errorMessage = 'Error saving the attachment:\n';
                for (let key in errors) {
                    errorMessage += `${errors[key]}\n`;
                }
                alert(errorMessage);
            }
        });
    });

});

function getInvoiceDeposit(id) {
    $.ajax({
        url: getQuoteInvoiceDeposit,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: 'POST',
        data: {
            quote_id: id
        },
        success: function (response) {
            const table = document.getElementById('invoiceDeposit');
            const tableBody = table.querySelector('tbody');

            if (!response.data) {
                response.data = []; // Set an empty array if data is null/undefined
            }

            setDataOnInvoiceDeposit(response.data, tableBody, table)
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function setDataOnInvoiceDeposit(data, tableBody, table) {

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

    let totalAmount = 0;
    data.forEach(item => {

        // Create a new row
        const row = document.createElement('tr');

        const invoiceRef = document.createElement('td');

        const link = document.createElement('a');
        link.textContent = item.invoice_ref; // Set the text of the link
        link.href = `/invoices/edit/${item.invoice_id}`; // Set the URL of the link
        link.target = '_blank'; // Optional: Opens the link in a new tab

        // Append the link to the <td> element
        invoiceRef.appendChild(link);

        // Append the <td> element to the row
        row.appendChild(invoiceRef);


        // invoiceRef.textContent = item.invoice_ref;
        // row.appendChild(invoiceRef);

        const invoice_date = document.createElement('td');
        invoice_date.innerHTML = moment(item.invoice_date).format('DD/MM/YYYY');
        row.appendChild(invoice_date);

        const due_date = document.createElement('td');
        due_date.innerHTML = moment(item.due_date).format('DD/MM/YYYY');
        row.appendChild(due_date);

        totalAmount += parseFloat(item.total);

        const sub_total = document.createElement('td');
        sub_total.textContent = '£' + item.sub_total;
        row.appendChild(sub_total);

        const VAT_amount = document.createElement('td');
        VAT_amount.textContent = '£' + item.VAT_amount;
        row.appendChild(VAT_amount);

        const total = document.createElement('td');
        total.textContent = '£' + item.total;
        row.appendChild(total);

        const outstanding = document.createElement('td');
        outstanding.textContent = '£' + item.total;
        row.appendChild(outstanding);

        const created_on = document.createElement('td');
        created_on.innerHTML = moment(item.created_on).format('DD/MM/YYYY HH:mm');
        row.appendChild(created_on);

        const status = document.createElement('td');
        status.textContent = item.status;
        row.appendChild(status);


        const idCell = document.createElement('td');
        idCell.innerHTML = `<a href="#!" class="nav-link dropdown-toggle profileDrop" data-bs-toggle="dropdown" aria-expanded="false">Action</a>
                            <div class="dropdown-menu fade-up m-0" style="">
                                <a href="#!" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#productModalBAC">Edit</a>
                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtTitle()">Preview</a>
                                <a href="#!" class="dropdown-item col-form-label" data-bs-toggle="modal" data-bs-target="#attachmentsPopup">Print</a>
                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtDescription()">Email</a>
                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtSection()">Duplicate</a>
                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtSection()">Record Payment</a>
                                <a href="#!" class="dropdown-item col-form-label" onclick="insrtSection()">CRM / History</a>
                            </div>`;
        row.appendChild(idCell);
        tableBody.appendChild(row);
    });

    const existingFoot = table.querySelector('tfoot');
    if (existingFoot) existingFoot.remove();

    depositeInvoiceFoot(totalAmount, table)
}

function depositeInvoiceFoot(amount, table) {

    const tfoot = document.createElement('tfoot');

    // Create the footer row
    const footerRow = document.createElement('tr');

    // Create the "Sub Total" cell
    const subtotalLabelCell = document.createElement('th');
    subtotalLabelCell.colSpan = 5; // Adjust colspan based on your table structure
    subtotalLabelCell.textContent = 'Sub Total';
    footerRow.appendChild(subtotalLabelCell);

    // Create the "total" cell
    const subtotalAmountCell = document.createElement('th');
    // subtotalAmountCell.colSpan = 3; // Adjust colspan based on your table structure
    subtotalAmountCell.textContent = `£${amount.toFixed(2)}`; // Use your calculated amount here

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'total_amount';
    // hiddenInput.name = 'subtotal_amount'; // Set the name for the hidden input
    hiddenInput.value = amount.toFixed(2);
    subtotalAmountCell.appendChild(hiddenInput);

    const outstandingAmountCell = document.createElement('th');
    outstandingAmountCell.colSpan = 3; // Adjust colspan based on your table structure
    outstandingAmountCell.textContent = `£${amount.toFixed(2)}`; // Use your calculated amount here

    const hiddenInputOutstanding = document.createElement('input');
    hiddenInputOutstanding.type = 'hidden';
    hiddenInputOutstanding.id = 'outstanding_amount';
    hiddenInputOutstanding.value = amount.toFixed(2);
    outstandingAmountCell.appendChild(hiddenInputOutstanding);

    footerRow.appendChild(subtotalAmountCell);
    footerRow.appendChild(outstandingAmountCell);

    // Append the row to the <tfoot> element
    tfoot.appendChild(footerRow);

    // Append the <tfoot> to the table
    table.appendChild(tfoot);
}

function updateQuoteDepositTotal() {
    let percentage = $(this).val();
    // Regex for non-alphanumeric validation
    const nonAlphanumericRegex = /^[^a-zA-Z0-9]+$/;

    // Check for non-alphanumeric characters
    if (!nonAlphanumericRegex.test(percentage)) {
        percentage = 100;
        return;
    }

    const numericValue = parseFloat(percentage);
    if (!isNaN(numericValue) && numericValue > 100) {
        percentage = 100;
        return;
    }
}

function saveFormData(formId, saveUrl, modalId, callback, callBackValue = null) {
    var formData = $('#' + formId).serialize();
    console.log(formData);

    $.ajax({
        url: saveUrl,
        method: 'POST',
        data: formData,
        success: function (response) {
            alert(response.message);
            $('#' + modalId).modal('hide');
            if (callback && typeof callback === 'function') {
                callback(callBackValue);
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function attachRowEventListeners(row, table) {
    // Attach change events for quantity, costPrice, price, etc.
    row.querySelector('.quantity')?.addEventListener('input', () => calculateRowsValue(table));
    row.querySelector('.costPrice')?.addEventListener('input', () => calculateRowsValue(table));
    row.querySelector('.price')?.addEventListener('input', () => calculateRowsValue(table));
    row.querySelector('.discount')?.addEventListener('input', () => calculateRowsValue(table));
    row.querySelector('.vat')?.addEventListener('change', () => calculateRowsValue(table));
}

// function getTaxRateOnTaxId(taxID) {
//     $.ajax({
//         url: '{{ route("invoice.ajax.getTaxRateOnTaxId") }}',
//         method: 'Post',
//         data: {
//             id: 2
//         },
//         success: function (response) {
//             console.log("response.data", response.data);
//             document.querySelector('.selectedTaxID').value = response.data;
//         },
//         error: function (xhr, status, error) {
//             console.error(error);
//         }
//     });
// }

function getTags(tags) {
    $.ajax({
        url: getTagsURL,
        method: 'GET',
        success: function (response) {
            console.log("jxcnjfjnfnk", response.data);
            tags.innerHTML = '';
            response.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.text = user.title;
                tags.appendChild(option);
            });
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function getProductData(selectedId) {
    $.ajax({
        url: getProductFromIdURL,
        method: 'Post',
        data: {
            id: selectedId
        },
        success: function (response) {
            console.log("response.data.product", response.data);
            quoteProductTable(response.data, 'quoteProducts', 'add');
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function tableFootForProduct(tableName) {
    const table = document.querySelector(`#${tableName}`);
    const quote_id = document.getElementById('quote_id').value;
    // getDepositData(quote_id, 2);
    if (!isFooterAppended) {
        const tableFoot = table.querySelector('.add_table_insrt33');
        tableFoot.innerHTML += `<tr>
                                    <td colspan="10" class="borderNone"></td>
                                    <td>Sub Total (exc. VAT) <input type="hidden" name="sub_total" id="InputFootAmount"></td>
                                    <td class="tableAmountRight" id="footAmount">£00.00</td>
                                </tr>
                                <tr>
                                    <td colspan="10" class="borderNone"></td>
                                    <td>
                                        <div class="discountInput">
                                            <span>Discount</span><input type="text" class="form-control editInput input50 discountInputField" id="discountInput" value="0" data-table="${tableName}">
                                            <span>%</span>
                                        </div>
                                    </td>
                                    <td class="tableAmountRight" id="footDiscount">£00.00</td>
                                </tr>
                                <tr>
                                    <td colspan="10" class="borderNone"></td>
                                    <td>
                                        <span id="markUpLinkRemove"><a href="javascript:void(0)" onclick="applyMarkup();"> Apply overall markup</a> </span>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="10" class="borderNone"></td>
                                    <td>VAT<input type="hidden" name="vat_amount" id="InputFootVatAmount"></td>
                                    <td class="tableAmountRight" id="footVatAmount">£00.00</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td style="border-bottom: 1px solid #000;"><strong>Total(inc.VAT)<input type="hidden" name="total" id="inputFootTotalDiscountVat"></strong></td>
                                    <td style="border-bottom: 1px solid #000;" class="tableAmountRight totleBold" id="footTotalDiscountVat">£00.00</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td>Profit<input type="hidden" name="profit" id="inputFootProfit"></td>
                                    <td class="tableAmountRight" id="footProfit">£00.00</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td>Margin <a href="" class="wrapper">
                                        <i class="fa  fa-info-circle"></i>
                                        <div class="custom-tooltip">Gross profit margin is a measure of profitability that shows the percentage of revenue that exceeds the cost of goods sold (COGS)</div>
                                        </a>
                                    </td>
                                    <td class="tableAmountRight" id="footMargin">00.00%</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td>Deposit</td>
                                    <td class="tableAmountRight" id="footDeposit"> £00.00</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td>Refund</td>
                                    <td class="tableAmountRight" id="footRefund">£00.00</td>
                                </tr>
                                <tr>
                                <td colspan="10" class="borderNone"></td>
                                    <td style="border-bottom: 1px solid #000;"><strong>Outstanding (inc.VAT)<input type="hidden" name="outstanding" id="inputFootOutstandingAmount"></strong></td>
                                    <td style="border-bottom: 1px solid #000;" class="tableAmountRight totleBold" id="footOutstandingAmount">£00.00</td>
                                </tr>`;
        isFooterAppended = true;

        // Ensure the input is correctly selected
        const discountInput = table.querySelector('#discountInput');
        if (!discountInput) {
            console.error('Discount input not found.');
            return;
        }

        // Attach the event listener
        discountInput.addEventListener('input', function () {
            const discountValue = parseFloat(this.value) || 0;

            // Update all elements with the class "discount"
            document.querySelectorAll('.discount').forEach(discountElement => {
                discountElement.value = discountValue.toFixed(2); // Format to 2 decimal places
            });

            // Call calculateRowsValue function
            if (typeof calculateRowsValue === 'function') {
                calculateRowsValue(table);
            } else {
                console.error('calculateRowsValue function is not defined.');
            }
        });

    }
}


function applyMarkup() {
    document.getElementById('markUpLinkRemove').innerHTML = '';
    document.getElementById('markUpLinkRemove').innerHTML = '<span>Markup</span> <input type="text" class="input50 form-control editInput footMarkup" name="mark" id="footMarkup"><span>%</span>';
}

$(document).on("change", ".vat", function () {
    let selectedRate = $(this).find("option:selected").data("rate");
    $(this).closest("tr").find(".selectedTaxID").val(selectedRate);
    console.log(selectedRate); // Output the selected VAT rate

    const table = document.querySelector(`#quoteProducts`);
    calculateRowsValue(table); // Call function with selected rate

});

$(document).on("change", ".discount_type_value", function () {
    const table = document.querySelector(`#quoteProducts`);
    calculateRowsValue(table); // Call function with selected rate
});

$(document).on("change", ".discount, .priceMarkup ", function () {
    const table = document.querySelector(`#quoteProducts`);
    calculateRowsValue(table); // Call function with selected rate
});

$(document).on("input", "#discountInput", function () {
    let discountValue = parseInt(this.value);
    document.querySelectorAll(".discount").forEach(element => {
        element.value = discountValue; // Set discount with %
    });

    document.querySelectorAll(".discount_type_value").forEach(select => {
        select.value = "%";
    });

    const table = document.querySelector(`#quoteProducts`);
    calculateRowsValue(table);
});

$(document).on("input", "#footMarkup", function () {
    console.log(this.value);
    let markValue = parseInt(this.value);

    document.querySelectorAll(".priceMarkup").forEach(element => {
        element.value = markValue;
    });

    const table = document.querySelector(`#quoteProducts`);
    calculateRowsValue(table);
});

function calculateDiscount(originalPrice, discountValue, discountType) {
    let finalPrice;

    if (discountType === "%") {
        // Percentage discount
        finalPrice = originalPrice * (discountValue / 100);
        console.log("finalPrice", finalPrice);
    } else if (discountType === "£") {
        // Fixed amount discount
        finalPrice = discountValue;
        console.log("finalPrice", finalPrice);
    }

    return finalPrice > 0 ? finalPrice : 0; // Ensure price doesn't go negative
}

function calculateRowsValue(table) {
    const rows = table.querySelectorAll('tbody tr');

    let totalQuantity = 0;
    let totalCostPrice = 0;
    let totalPrice = 0;
    let totalMarkup = 0;

    let totalVAT = 0;
    let vat = 20;

    let totalProfit = 0;
    let totalDiscount = 0;

    let profitElement;
    let profitValue;
    let numericProfit;
    let totalMargin = 0;

    let price = 0;

    const doller = `£`;

    rows.forEach(row => {

        // Get input values from the row
        totalQuantity = parseInt(row.querySelector('.quantity').value) || 0;
        totalPrice = parseFloat(row.querySelector('.price').value) || 0;
        discount = parseInt(row.querySelector('.discount').value) || 0;
        console.log("discount input", discount);
        discount_type_value = row.querySelector('.discount_type_value').value;
        console.log("discount_type_value", discount_type_value);
        discountAmount = calculateDiscount(totalPrice, discount, discount_type_value);
        totalCostPrice = parseFloat(row.querySelector('.costPrice').value) || 0;
        totalMarkup = parseInt(row.querySelector('.priceMarkup').value) || 0;
        vat = parseInt(row.querySelector('.selectedTaxID').value) || 0;

        priceWithDiscount = totalPrice - discountAmount;

        markupAmount = (priceWithDiscount * totalMarkup) / 100; // Percentage markup
        console.log("markupAmount", markupAmount);

        // Calculate selling price (Cost Price + Markup - Discount)
        sellingPrice = priceWithDiscount + markupAmount;
        console.log("sellingPrice", sellingPrice);
        totalDiscount += discountAmount;

        // Calculate Amount (Quantity × Selling Price)
        amount = totalQuantity * sellingPrice;
        console.log(amount);
        price += amount;

        // Calculate VAT amount
        vatAmount = (amount * vat) / 100;
        console.log(vatAmount);
        totalVAT += vatAmount;
        // Calculate Profit ((Selling Price - Cost Price) × Quantity)
        profit = (sellingPrice - totalCostPrice) * totalQuantity;
        console.log(sellingPrice);
        totalProfit += profit;

        // Calculate margin
        margin = parseFloat((profit / sellingPrice) * 100);
        totalMargin += margin;
        console.log(margin);

        row.querySelector('.amount').textContent = doller + amount.toFixed(2);

        // Update row output fields
        row.querySelector('.profit').textContent = doller + profit.toFixed(2);

        if (margin >= 0) {
            row.querySelector('.footRowMargin').classList.add('minusnmberGreen');
        } else {
            row.querySelector('.footRowMargin').classList.add('minusnmberRed');
        }
        row.querySelector('.footRowMargin').textContent = '(' + margin.toFixed(2) + '%' + ')';

    });
    console.log("Total Quantity: ", totalQuantity);
    console.log("Total Cost Price: ", totalCostPrice);
    console.log("Total Price: ", price);
    console.log("Total Markup: ", totalMarkup);
    console.log("Total VAT: ", totalVAT);
    console.log("Total Discount: ", totalDiscount);
    console.log("Total Profit: ", totalProfit);
    console.log("Total totalMargin: ", totalMargin);

    document.getElementById('footAmount').textContent = doller + price.toFixed(2);
    document.getElementById('InputFootAmount').value = price.toFixed(2);
    document.getElementById('footDiscount').textContent = doller + totalDiscount.toFixed(2);
    document.getElementById('footVatAmount').textContent = doller + totalVAT.toFixed(2);
    document.getElementById('InputFootVatAmount').value = totalVAT.toFixed(2);
    document.getElementById('footTotalDiscountVat').textContent = doller + (price + totalVAT).toFixed(2);
    document.getElementById('inputFootTotalDiscountVat').value = (price + totalVAT).toFixed(2);
    document.getElementById('footProfit').textContent = doller + totalProfit.toFixed(2);
    document.getElementById('inputFootProfit').value = totalProfit.toFixed(2);
    document.getElementById('footMargin').textContent = totalMargin.toFixed(2) + "%";
    document.getElementById('footOutstandingAmount').textContent = doller + (price + totalVAT).toFixed(2);
    document.getElementById('inputFootOutstandingAmount').value = (price + totalVAT).toFixed(2);

}

function taxRate() {
    $.ajax({
        url: getActiveTaxRateURL,
        method: 'GET',
        success: function (response) {
            console.log("response.data", response.data);
            if (Array.isArray(response.data)) {
                // Iterate over all Account Code dropdowns and populate them
                document.querySelectorAll('.getTaxRate').forEach(dropdown => {
                    dropdown.innerHTML = ''; // Clear existing options

                    const optionInitial = document.createElement('option');
                    optionInitial.textContent = "Please Select"; // Use appropriate key from your response
                    optionInitial.value = 0;
                    dropdown.appendChild(optionInitial);
                    // Append new options
                    response.data.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id; // Use appropriate key from your response
                        option.textContent = code.name; // Use appropriate key from your response
                        option.setAttribute('data-rate', code.tax_rate);
                        if (code.id === 2) {
                            option.selected = true; // Select the option where id = 2
                        }
                        dropdown.appendChild(option);
                    });
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

let isFooterAppended = false;
let rowIndex = 0;

function quoteProductTable(data, tableId, type) {
    const table = document.querySelector(`#${tableId}`);
    // Populate rows as usual if data is not empty
    data.forEach(item => {
        console.log("item", item);
        console.log("1", rowIndex);
        const tableBody = document.querySelector(`#${tableId} tbody`);
        const node = document.createElement("tr");
        taxRate();
        node.classList.add("add_table_insrt");
        node.innerHTML = `<td>
                <div class="CSPlus">
                    <span class="plusandText">
                        <a href="javascript:void(0)" class="formicon pt-0 me-2"> <i class="fa-solid fa-square-plus"></i> </a>
                        <input type="hidden" name="products[${rowIndex}][type]" value="${type}">
                        <input type="hidden" name="products[${rowIndex}][id]" value="${item.id}">
                        <input type="text" class="form-control editInput input80" name="products[${rowIndex}][product_code]" value="${item.product_code}">
                    </span>
                </div>
            </td>
            <td>
                <div class="">
                    <input type="text" class="form-control editInput" name="products[${rowIndex}][product_name]" value="${item.product_name}">
                </div>
            </td>
            <td>
                <div class="">
                    <textarea class="form-control textareaInput" id="inputAddress" name="products[${rowIndex}][description]" rows="2" placeholder="Description">${item.description}</textarea>
                </div>
            </td>
            <td>
                <div class="">
                <input type="hidden" value="${item.account_code}" class="selectedAccountCode">
                    <select class="form-control editInput selectOptions" onclick="getAccountCode();" name="products[${rowIndex}][account_code]" id="accoutCodeList">
                        <option>-No Department-</option> 
                    </select>
                </div>
            </td>
            <td>
                <div class=""><input type="text" class="form-control editInput input50 quantity" name="products[${rowIndex}][quantity]"  value="1"></div>
            </td>
            <td>
                <div class=""><input type="text" class="form-control editInput input50 costPrice" name="products[${rowIndex}][cost_price]" value="${parseFloat(item.cost_price || 0).toFixed(2)}"></div>
            </td>
            <td>
                <div class="calculatorIcon">
                    <span class="plusandText">
                        <a href="javascript:void(0)" class="formicon pt-0" data-bs-toggle="modal" data-bs-target="#calculatePop"> <span class="material-symbols-outlined">calculate </span> </a>
                    </span>
                </div>
            </td>
            <td>
                <div class="">
                    <input type="text" class="form-control editInput input50 price" name="products[${rowIndex}][price]" value="${parseFloat(item.price || 0).toFixed(2)}">
                </div>
            </td>
            <td>
                <div class="">
                    <input type="text" class="form-control editInput input50 priceMarkup" name="products[${rowIndex}][markup]" value="${parseFloat(item.markup || 0).toFixed(2)}">
                </div>
            </td>
            <td>
                <div class="">
                    <input type="hidden" class="selectedTaxID" value="${parseInt(item.VAT) || 0}">
                    <select class="form-control editInput selectOptions vat getTaxRate" name="products[${rowIndex}][VAT]" id="getTaxRate">
                        <option>Please Select</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="d-flex discount-container">
                    <input type="text" class="form-control editInput input50 me-2 discount" name="products[${rowIndex}][discount]" value="${parseInt(item.discount) || 0}">
                    <input type="hidden" class="selectedDiscountType" value="${item.discount_type || 0}">
                    <select class="form-control editInput selectOptions input50 discount_type_value" name="products[${rowIndex}][discount_type]" >
                        <option value="£">£</option>
                        <option value="%">%</option>
                    </select>
                </div>
            </td>
            <td>
                <span class="amount">£00.00</span>
            </td>
            <td>
                <span class="profit">£00.00</span>
                <div class="pt-1 footRowMargin">(00.00%)</div>
            </td>
            <td>
                <div class="statuswating">
                    <span class="oNOfswich">
                        <input type="checkbox">
                    </span>
                    <a href="javascript:void(0)" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                </div>
            </td>`;

        tableFootForProduct(tableId);
        isFooterAppended = true;
        rowIndex++;
        console.log('2', rowIndex);

        if (tableBody) {
            tableBody.appendChild(node);

            attachRowEventListeners(node, table)
            const closeButton = node.querySelector('.closeappend');
            closeButton.addEventListener('click', function () {
                node.remove(); // Remove the row when close button is clicked 
                clearFooter(table);
                calculateRowsValue(table);
            });
        } else {
            console.error("Table body with ID 'add_table_insrt' not found.");
        }

    });
    calculateRowsValue(table);
}



function getQuoteType(quoteType) {
    console.log("getQuoteType", quoteType);
    $.ajax({
        url: getQuoteTypesURL,
        success: function (response) {
            console.log("getQuoteType", response.data);
            quoteType.innerHTML = '';
            response.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.text = user.title;
                quoteType.appendChild(option);
            });
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function getQuoteAttachmentsOnPageLoad() {
    const quote_id = document.getElementById('quote_id').value;
    console.log("quote_id", quote_id);
    $.ajax({
        url: getAttachmentDataOnQuoteId, // Replace with your Laravel route URL
        type: 'POST',
        data: { quote_id: quote_id },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            // Handle success
            console.log(response);
            const tableBody = $('#attachmentTable tbody');
            console.log(tableBody);
            tableBody.empty(); // Clear existing rows
            if (response.data == "No data") {
                console.log(response.data);
            } else {
                // Assuming `response` contains an array of attachments
                const attachments = Array.isArray(response.data) ? response.data : [response.data];

                attachments.forEach(attachment => {
                    console.log(attachment);
                    // const attachmentTypeTitle = attachment.attachment_type ? attachment.attachment_type.title : '';
                    const customer_visible = attachment.customer_visible = 1 ? "grayCheck" : "grencheck";
                    const mobile_user_visible = attachment.mobile_user_visible = 1 ? "grayCheck" : "grencheck";

                    const id = attachment.id;
                    const row = `
                            <tr data-id="${id}">
                                <td><input type="checkbox" class="selectRow"></td>
                                <td>${attachment.attachmentType}</td>
                                <td>${attachment.title}</td>
                                <td>${attachment.description}</td>
                                <td>Quote</td>
                                <td> <span class="${customer_visible}"><i class="fa-solid fa-circle-check"></i></span> </td>
                                <td> <span class="${mobile_user_visible}"><i class="fa-solid fa-circle-check"></i></span></td>
                                <td>${attachment.original_name}</td>
                                <td>${attachment.mime_type} / ${attachment.size} KB</td>
                                <td>${new Date(attachment.created_at).toLocaleString()}</td>
                                <td><a href="${attachment.timestamp_name}" target="_blank"> <i class="fas fa-eye"></i></a> | <i class="fa fa-times"></i> | <a href="#!" onclick="downloadAttachmentFile('${attachment.timestamp_name}');"> <i class="fas fa-download"></i></a> | <a href="javascript:void(0)" onclick="deleteAttachmentFile('${attachment.id}');"> <i class="fas fa-trash-alt"></i></a> </td>
                            </tr>
                        `;
                    console.log(row);
                    tableBody.append(row);
                });
            }
        },
        error: function (xhr) {
            // Handle error
            const errors = xhr.responseJSON.errors || {
                message: xhr.responseJSON.message
            };
            let errorMessage = 'Error on getting the attachment:\n';
            for (let key in errors) {
                errorMessage += `${errors[key]}\n`;
            }
            alert(errorMessage);
        }
    });

}

function downloadAttachmentFile(fileName) {
    const fileUrl = fileName; // Construct the file URL dynamically
    const anchor = document.createElement('a');
    anchor.href = fileUrl;
    anchor.download = fileName; // Optional: Rename the file for the user
    anchor.click();
}

function getQuoteAttachments(attachment_id) {
    $.ajax({
        url: '{{ route("quote.ajax.getAttachmentData") }}', // Replace with your Laravel route URL
        type: 'POST',
        data: {
            attachment_id: attachment_id
        },
        success: function (response) {
            // Handle success
            const tableBody = $('#attachmentTable tbody');
            console.log(tableBody);

            if (response.data == "No data") {
                console.log(response.data);
            } else {
                // Assuming `response` contains an array of attachments
                const attachments = Array.isArray(response.data) ? response.data : [response.data];

                attachments.forEach(attachment => {
                    console.log(attachment);
                    const customer_visible = attachment.customer_visible = 1 ? "grayCheck" : "grencheck";
                    const mobile_user_visible = attachment.mobile_user_visible = 1 ? "grayCheck" : "grencheck";
                    const id = attachment.id;
                    const row = `
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>${attachment.attachmentType}</td>
                            <td>${attachment.title}</td>
                            <td>${attachment.description}</td>
                            <td>Quote</td>
                            <td> <span class="${customer_visible}"><i class="fa-solid fa-circle-check"></i></span> </td>
                            <td> <span class="${mobile_user_visible}"><i class="fa-solid fa-circle-check"></i></span></td>
                            <td>${attachment.original_name}</td>
                            <td>${attachment.mime_type} / ${attachment.size} KB</td>
                            <td>${new Date(attachment.created_at).toLocaleString()}</td>
                            <td><a href="${attachment.timestamp_name}" target="_blank"> <i class="fas fa-eye"></i></a> | <i class="fa fa-times"></i> | <a href="#!" onclick="deleteAttachmentFile('${attachment.id}');"> <i class="fas fa-trash-alt"></i></a></td>
                        </tr>
                    `;
                    console.log(row);
                    tableBody.append(row);
                });
            }

        },
        error: function (xhr) {
            // Handle error
            const errors = xhr.responseJSON.errors || {
                message: xhr.responseJSON.message
            };
            let errorMessage = 'Error on getting the attachment:\n';
            for (let key in errors) {
                errorMessage += `${errors[key]}\n`;
            }
            alert(errorMessage);
        }
    });
}

function deleteAttachmentFile(id) {

    // Confirm deletion
    if (confirm("Are you sure you want to delete this row?")) {
        // Make AJAX call to delete the row on the server
        $.ajax({
            url: '{{ route("quote.ajax.deleteAttachment") }}', // Replace with your server URL
            method: 'POST', // Replace with appropriate HTTP method
            data: { id: id },
            success: function (response) {
                if (response.success) {
                    // Remove the row from the table
                    row.remove();
                } else {
                    alert("Failed to delete the row.");
                }
            },
            error: function () {
                alert("An error occurred while trying to delete the row.");
            }
        });
    }
}

function setSiteAddressDetails(id) {
    $.ajax({
        url: '{{ route("customer.ajax.getCustomerSiteDetails") }}',
        method: 'POST',
        data: {
            id: id
        },
        success: function (response) {
            console.log(response.data);

            let selectElement = document.getElementById('customerSiteDetails'); // or document.querySelector('[name="mySelectName"]');
            let customerSiteDelivery = document.getElementById('customerSiteDelivery'); // or document.querySelector('[name="mySelectName"]');

            let newOption = document.createElement('option');
            newOption.value = response.data[0].id;
            newOption.text = response.data[0].site_name;
            const option1 = newOption.cloneNode(true);
            newOption.selected = true;
            selectElement.appendChild(newOption);
            customerSiteDelivery.appendChild(option1);


            // document.getElementById('customerSiteDetails');
            document.getElementById('siteCustomerId').value = response.data[0].id;
            document.getElementById('customerSiteName').value = response.data[0].contact_name;
            document.getElementById('customerSiteAddress').value = response.data[0].address;
            document.getElementById('customerSiteCity').value = response.data[0].city;
            document.getElementById('customerSiteCounty').value = response.data[0].country;
            document.getElementById('customerSitePostCode').value = response.data[0].post_code;
            document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
            document.getElementById('customerSiteMobile').value = response.data[0].mobile;
            document.getElementById('setSiteAddress').textContent = response.data[0].name;
            document.getElementById('customerSiteCompany').value = response.data[0].company_name;
            selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_id);
            selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
            selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function setCustomerBillingData(id) {
    $.ajax({
        url: getCustomerBillingAddressData,
        method: 'POST',
        data: {
            id: id
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log(response.data);

            let selectElement = document.getElementById('billingDetailContact'); // Get the select element

            // Create and append a new option
            let newOption = document.createElement('option');
            newOption.value = response.data[0].id;
            newOption.text = response.data[0].contact_name;
            selectElement.appendChild(newOption);

            // Set the new option as selected
            newOption.selected = true;
            setFieldValues(['billing_add_id', 'siteCustomerId'], response.data[0].id);

            // billing details data set
            document.getElementById('billingDetailsName').value = response.data[0].contact_name;
            document.getElementById('customer_contact_id').value = response.data[0].id;
            document.getElementById('billingDetailsAddress').value = response.data[0].address;
            document.getElementById('billingDetailsEmail').value = response.data[0].email;
            document.getElementById('billingCustomerCity').value = response.data[0].city;
            document.getElementById('billingCustomerCounty').value = response.data[0].county;
            document.getElementById('billingCustomerPostcode').value = response.data[0].pincode;
            document.getElementById('billingCustomerTelephone').value = response.data[0].telephone;
            document.getElementById('billingCustomerMobile').value = response.data[0].mobile;
            selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
            selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
            selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);


            if (response.default_billing === 1) {
                document.getElementById('site_delivery_add_id').value = response.data[0].id;
                document.getElementById('customerSiteName').value = response.data[0].contact_name;
                document.getElementById('siteCustomerId').value = response.data[0].id;
                document.getElementById('customerSiteAddress').value = response.data[0].address;
                document.getElementById('customerSiteCity').value = response.data[0].city;
                document.getElementById('customerSiteCounty').value = response.data[0].county;
                document.getElementById('customerSitePostCode').value = response.data[0].pincode;
                document.getElementById('customerSiteTelephone').value = response.data[0].telephone;
                document.getElementById('customerSiteMobile').value = response.data[0].mobile;
                // Customer Site Address Data Set
                selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
                selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
                selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function getBillingDetailsData(id) {
    $.ajax({
        url: getCustomerDetailsURL,
        method: 'POST',
        data: {
            id: id
        },
        success: function (response) {
            console.log("getCustomerDetails", response.data);
            var contactData = response.data[0];
            // billing details data set
            // setFieldValues([], contactData.id);

            setFieldValues(['billing_add_id', 'site_delivery_add_id', 'siteCustomerId', 'customer_id_site_delivery'], contactData.id);
            setFieldValues(['billingDetailsName', 'customerSiteName', 'customerSiteDeliveryName'], contactData.contact_name);
            setTextContent(['setCustomerName', 'setSiteAddress', 'customerSiteCompany', 'customerSiteDeliveryCompany', 'setSiteDeliveryAddress'], contactData.name);
            setFieldValues(['billingDetailsAddress', 'customerSiteAddress', 'customerSiteDeliveryAdd'], contactData.address);
            setFieldValues(['billingDetailsEmail', 'customerSiteDeliveryEmail'], contactData.email);
            setFieldValues(['billingCustomerCity', 'customerSiteCity'], contactData.city);
            setFieldValues(['billingCustomerCounty', 'customerSiteCounty'], contactData.country);
            setFieldValues(['billingCustomerPostcode', 'customerSitePostCode', 'customerSiteDeliveryPostCode'], contactData.postal_code);
            setFieldValues(['billingCustomerTelephone', 'customerSiteTelephone', 'customerSiteDeliveryTelephone'], contactData.telephone);
            setFieldValues(['billingCustomerMobile', 'customerSiteMobile', 'customerSiteDeliveryMobile'], contactData.mobile);
            // customer_contact_id

            selectPrevious(document.getElementById('billingCustomerTelephoneCode'), response.data[0].telephone_country_code);
            selectPrevious(document.getElementById('billingCustomerMobileCode'), response.data[0].mobile_country_code);
            selectPrevious(document.getElementById("billingCustomerCountry"), response.data[0].country_code);

            // Customer Site Address Data Set
            selectPrevious(document.getElementById('customerSiteDetailsCountry'), response.data[0].country_code);
            selectPrevious(document.getElementById("customerSiteTelephoneCode"), response.data[0].telephone_country_code);
            selectPrevious(document.getElementById("customerSiteMobileCode"), response.data[0].mobile_country_code);

            // Customer Site Delivery Address Data Set
            selectPrevious(document.getElementById('customerSiteDeliveryCountry'), response.data[0].country_code);
            selectPrevious(document.getElementById("customerSiteDeliveryTelephoneCode"), response.data[0].telephone_country_code);
            selectPrevious(document.getElementById("customerSiteDeliveryMobileCode"), response.data[0].mobile_country_code);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function selectPrevious(Select, previouslySelected) {
    // Loop through the options in the select field
    const options = Select.options;

    for (let i = 0; i < options.length; i++) {
        if (options[i].value === previouslySelected) {
            options[i].selected = true; // Set the previously selected country
            break;
        }
    }
}

function setFieldValues(fields, value) {
    fields.forEach(fieldId => {
        document.getElementById(fieldId).value = value;
    });
}

function setTextContent(fields, value) {
    fields.forEach(fieldId => {
        document.getElementById(fieldId).textContent = value;
    });
}

function upload() {
    var imgcanvas = document.getElementById("canv1");
    var fileinput = document.getElementById("finput");
    var image = new SimpleImage(fileinput);
    image.drawTo(imgcanvas);
}

function getDepositData(quote_id) {
    $.ajax({
        url: getDepositeData,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            quote_id: quote_id
        },
        success: function (response) {
            console.log(response);
            const table = document.getElementById('depositData'); // Replace with your table's ID
            const tableBody = table.querySelector('tbody'); // Select the tbody within the table
            setDepositTableData(response.data, tableBody, table)
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function setDepositTableData(data, tableBody, table) {

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

    let totalAmount = 0;
    data.forEach(item => {

        // Create a new row
        const row = document.createElement('tr');

        const depositDate = document.createElement('td');
        depositDate.textContent = item.deposit_date;
        row.appendChild(depositDate);

        const mode_of_payment = document.createElement('td');
        const icon = document.createElement('i');
        icon.classList.add('fa', 'fa-money');
        mode_of_payment.appendChild(icon);
        mode_of_payment.textContent = item.payment_type;
        row.appendChild(mode_of_payment);

        const refrences = document.createElement('td');
        refrences.innerHTML = item.reference;
        row.appendChild(refrences);

        const description = document.createElement('td');
        description.innerHTML = item.description;
        row.appendChild(description);

        const created_on = document.createElement('td');
        created_on.textContent = moment(item.created_at).format('DD/MM/YYYY HH:mm');
        row.appendChild(created_on);

        totalAmount += parseFloat(item.amount);

        const deposit_amount = document.createElement('td');
        deposit_amount.textContent = '£' + item.amount;
        row.appendChild(deposit_amount);

        const refunded = document.createElement('td');
        refunded.innerHTML = '-';
        row.appendChild(refunded);

        const idCell = document.createElement('td');
        idCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="edit"><i class="fa fa-edit"></i></a> <i class="fa fa-times"></i>`;
        row.appendChild(idCell);
        console.log(totalAmount);

        // Append the row to the table body
        tableBody.appendChild(row);

    });

    const existingFoot = table.querySelector('tfoot');
    if (existingFoot) existingFoot.remove();

    depositeFoot(totalAmount, table);
}

function depositeFoot(amount, table) {

    const tfoot = document.createElement('tfoot');

    // Create the footer row
    const footerRow = document.createElement('tr');

    // Create the "Sub Total" cell
    const subtotalLabelCell = document.createElement('th');
    subtotalLabelCell.colSpan = 5; // Adjust colspan based on your table structure
    subtotalLabelCell.textContent = 'Sub Total';
    footerRow.appendChild(subtotalLabelCell);

    // Create the "Amount" cell
    const subtotalAmountCell = document.createElement('th');
    subtotalAmountCell.colSpan = 3; // Adjust colspan based on your table structure
    subtotalAmountCell.textContent = `£${amount.toFixed(2)}`; // Use your calculated amount here

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'subtotal_amount';
    // hiddenInput.name = 'subtotal_amount'; // Set the name for the hidden input
    hiddenInput.value = amount.toFixed(2);
    subtotalAmountCell.appendChild(hiddenInput);
    footerRow.appendChild(subtotalAmountCell);

    // Append the row to the <tfoot> element
    tfoot.appendChild(footerRow);

    // Append the <tfoot> to the table
    table.appendChild(tfoot);
}

document.addEventListener('DOMContentLoaded', function () {

    const yesOnCheckbox = document.getElementById('yesOn');
    const optionsDiv = document.getElementById('optionsDiv');
    optionsDiv.style.display = 'none';
    // Add an event listener to the "Yes, ON" checkbox
    yesOnCheckbox.addEventListener('change', function () {
        if (this.checked) {
            optionsDiv.style.display = 'block'; // Show optionsDiv
        } else {
            optionsDiv.style.display = 'none'; // Hide optionsDiv
        }
    });
});

function appointmentType() {
    $.ajax({
        url: '{{ route("job.ajax.jobAppointment") }}',
        method: 'GET',
        success: function (response) {
            console.log("response.jobAppointment", response.data);

            const data = response.data;
            const appointments = document.querySelectorAll('.setAppointmentType');
            console.log("Appointments:", appointments);

            if (appointments.length === 0) {
                console.error("No elements found with class 'setAppointmentType'");
                return;
            }

            appointments.forEach(appointment => {
                // Clear existing options if needed
                appointment.innerHTML = ''; // Optional, uncomment if required

                data.forEach(item => {
                    console.log("Item:", item);

                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    appointment.appendChild(option);
                });
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
}

function getUsersData() {
    $.ajax({
        url: '{{ route("quote.ajax.getUsersData") }}',
        method: 'GET',
        success: function (response) {
            console.log("response.userData", response.data);

            const data = response.data;
            const users = document.querySelectorAll('.setUserData');
            console.log("Users:", users);

            if (users.length === 0) {
                console.error("No elements found with class 'setUserData'");
                return;
            }

            users.forEach(user => {

                // Clear existing options if needed
                user.innerHTML = ''; // Optional, uncomment if required
                const defaultOption = document.createElement('option');
                defaultOption.value = ''; // Use an empty value for the "Please Select" option
                defaultOption.textContent = 'Please Select';
                defaultOption.disabled = true; // Make it unselectable (optional)
                defaultOption.selected = true; // Pre-select it by default
                user.appendChild(defaultOption);

                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    user.appendChild(option);
                });
            });
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
        }
    });
}

function salesAppointment(table) {

    let existingTfoot = table.querySelector('tfoot');
    if (existingTfoot) {
        // Remove the existing footer
        existingTfoot.remove();
    }

    const tfoot = document.createElement('tfoot');

    const footerRow = document.createElement('tr');
    const footerCell = document.createElement('td');

    // Set the cell to span all columns (adjust colspan to your table structure)
    footerCell.colSpan = 8; // Change 8 to the number of columns in your table
    footerCell.innerHTML = `<a href="#!" class="profileDrop ms-3">Save Appointment(s)</a>`;

    // Append the cell to the row
    footerRow.appendChild(footerCell);

    // Append the row to the footer
    tfoot.appendChild(footerRow);

    // Append the footer to the table
    table.appendChild(tfoot);

}

//**************insrtTitle
function insrtAppoinment() {
    const node = document.createElement("tr");
    node.classList.add("add_insrtAppoinment");
    node.innerHTML = `<td>
                <div class="d-flex">
                    <p class="leftNum">1</p>
                    <select class="form-control editInput selectOptions setUserData" id="">
                        <option>Select user</option>
                    </select>
                    <a href="#!" class="callIcon"><i class="fa-solid fa-square-phone"></i></a>
                </div>
                <div class="alertBy">
                    <label><strong>Alert By :</strong></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="option1">
                        <label class="form-check-label" for="inlineCheckbox1">SMS</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="inlineCheckbox2" value="option2">
                        <label class="form-check-label" for="inlineCheckbox2">Email</label>
                    </div>
                </div>
            </td>
            <td>
                <div class="addDateAndTime">
                    <div class="startDate">
                        <input type="date" name="date" class=" editInput">
                        <input type="time" name="time" class=" editInput">
                    </div>
                </div>
                <div class="pt-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="floatingAppointment" value="option2">
                        <label class="form-check-label" for="floatingAppointment">Floating Appointment</label>
                    </div>
                </div>
            </td>
            <td>
                <div class="addDateAndTime">
                    <div class="endDate">
                        <input type="date" name="date" class=" editInput">
                        <input type="time" name="time" class=" editInput">
                    </div>
                </div>
            </td>
            <td>
                <div class="addTextarea">
                    <textarea cols="40" rows="5" placeholder="Type Notes...">Type Notes... </textarea>
                </div>
            </td>
            <td class="col-2">
                <div class="appoinment_type">
                    <select class="form-control editInput selectOptions setAppointmentType">
                    </select>
                </div>
                <div class="Priority">
                    <label>Priority :</label>
                    <select class="form-control editInput selectOptions" id="inputJobType">
                        <option value="1">Low</option>
                        <option value="2" selected>Medium</option>
                        <option value="3">High</option>
                    </select>
                </div>
            </td>
            <td>
                <div class="statuswating">
                    <label class="form-check-label" for="floatingAppointment">Awaiting </label>
                    <a href="#!" class="closeappend"><i class="fa-solid fa-circle-xmark"></i></a>
                </div>
                <div class="tabheadingTitle">
                    <a href="#" class="profileDrop me-3"> Notify</a>
                </div>
            </td>  
        `;

    appointmentType();
    getUsersData();

    const tableBody = document.querySelector(".add_insrtAppoinment");
    if (tableBody) {
        tableBody.appendChild(node);

        // Add event listener to the close button
        const closeButton = node.querySelector('.closeappend');
        closeButton.addEventListener('click', function () {
            node.remove(); // Remove the row when close button is clicked
        });
    } else {
        console.error("Table body with ID 'add_insrtAppoinment' not found.");
    }
    salesAppointment(tableBody);
}

function getQuoteTaskList(tableBody) {
    const quote_id = document.getElementById('quote_id').value;
    $.ajax({
        url: getQuoteTaskListURL,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        data: {
            quote_id: quote_id
        },
        success: function (response) {
            console.log(response.data);
            tableBody.innerHTML = '';
            if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                populateTableData(response.data, tableBody);
            } else {
                console.log("No data available"); // Optional: Log if data is empty
                tableBody.innerHTML = "<tr><td colspan='5'>No data found</td></tr>"; // Display message in table
            }
            // Call the function to populate the table with the data array
            // populateTable(response.data, tableBody);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

function saveQuoteTaskFormData() {

    const activeTab = document.querySelector('.taskTimer .nav-link.active');
    console.log(activeTab);
    let data;
    // Check the active tab by its ID
    if (activeTab.id === 'newTasks-tab') {
        // Task tab is active, set task fields
        console.log('Task fields set');
        data = {
            type: 'task',
            quote_id: document.getElementById('quote_id').value,
            edit_quote_task_id: document.getElementById('edit_quote_task_id').value,
            user_id: document.getElementById('quoteTaskUser').value,
            title: document.getElementById('title').value,
            task_type_id: document.getElementById('setTaskTypeData').value,
            start_date: document.getElementById('start_date').value,
            start_time: document.getElementById('start_time').value,
            end_date: document.getElementById('end_date').value,
            end_time: document.getElementById('end_time').value,
            is_recurring: document.getElementById('is_recurring').checked ? 1 : 0,
            yesOn: document.getElementById('yesOn').checked ? 1 : 0,
            notify_date: document.getElementById('notify_date').value,
            notify_time: document.getElementById('notify_time').value,
            notification: document.getElementById('notification').checked ? 1 : 0,
            email: document.getElementById('email').checked ? 1 : 0,
            sms: document.getElementById('sms').checked ? 1 : 0,
            notes: document.getElementById('notes').value,
        };
        console.log("data", data);
    } else if (activeTab.id === 'newTaskTimer-tab') {
        // Timer tab is active, set timer fields
        console.log('Timer fields set');
        data = {
            type: 'timer',
            quote_id: document.getElementById('quote_id').value,
            edit_quote_task_id: document.getElementById('edit_quote_task_id').value,
            user_id: document.getElementById('quoteTimerUser').value,
            title: document.getElementById('timerTitle').value,
            start_date: moment().format('YYYY-MM-DD'),
            start_time: document.getElementById('start_time_timer').value,
            task_type_id: document.getElementById('setTaskTypeOnTimer').value,
            notes: document.getElementById('timerNotes').value,
        };
    }



    $.ajax({
        url: '{{ route("quote.ajax.saveQuoteTask") }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        success: function (response) {
            alert(response.data);
            console.log(response.id);
            $('#newTaskModal').modal('hide');
            getQuoteTaskList(document.querySelector('#quoteTaskList tbody'));
        },
        error: function (xhr) {
            // Handle error
            const errors = xhr.responseJSON.errors || {
                message: xhr.responseJSON.message
            };
            let errorMessage = 'Error saving the task:\n';
            for (let key in errors) {
                errorMessage += `${errors[key]}\n`;
            }
            alert(errorMessage);
        }
    });
}

function populateTableData(data, tableBody) {
    // console.log("data", data);
    console.log("populateTable Data", data);
    data.forEach(item => {
        // Create a new row
        const row = document.createElement('tr');

        const created_at = moment(item.created_at).format('DD/MM/YYYY HH:mm');
        const date = moment(item.start_date, 'YYYY-MM-DD').format('DD/MM/YYYY');
        const time = moment(item.start_time, 'HH:mm:ss').format('HH:mm');

        // Create cells and append them to the row
        const dateCell = document.createElement('td');
        dateCell.textContent = date + " " + time;
        row.appendChild(dateCell);

        const related = document.createElement('td');
        related.innerHTML = item.quote_ref;
        row.appendChild(related);

        const nameCell = document.createElement('td');
        nameCell.innerHTML = item.userName;
        row.appendChild(nameCell);

        const quote_task_title = document.createElement('td');
        quote_task_title.textContent = item.task_type_id;
        row.appendChild(quote_task_title);

        const typeCell = document.createElement('td');
        typeCell.textContent = item.title;
        row.appendChild(typeCell);

        const notesCell = document.createElement('td');
        notesCell.innerHTML = item.notes;
        row.appendChild(notesCell);

        const create_time = document.createElement('td');
        create_time.innerHTML = created_at;
        row.appendChild(create_time);

        const idCell = document.createElement('td');
        idCell.innerHTML = `<a href="#" class="openAddNewTaskModel" data-id="${item.id}" data-type="edit"><i class="fa fa-edit"></i></a> <i class="fa fa-times"></i>`;
        row.appendChild(idCell);

        // Append the row to the table body
        tableBody.appendChild(row);
    });
}

$(document).ready(function () {

    getQuoteAttachmentsOnPageLoad();

    $('#search-product').on('keyup', function () {
        let query = $(this).val();
        const divList = document.querySelector('.parent-container');

        if (query === '') {
            divList.innerHTML = '';
        }

        // Make an AJAX call only if query length > 2
        if (query.length > 2) {
            $.ajax({
                url: "{{ route('item.ajax.searchProduct') }}", // Laravel route
                method: 'GET',
                data: {
                    query: query
                },
                success: function (response) {
                    console.log(response);
                    // $('#results').html(response);
                    divList.innerHTML = "";
                    const div = document.createElement('div');
                    div.className = 'container'; // Optional: Add a class to the div for styling

                    // Step 2: Create a ul (unordered list)
                    const ul = document.createElement('ul');
                    ul.id = "productList";
                    // Step 3: Loop through the data and create li (list item) for each entry
                    response.forEach(item => {
                        const li = document.createElement('li'); // Create a new li element
                        li.textContent = item.product_name; // Set the text of the li item
                        li.id = item.id;
                        li.className = "editInput";
                        ul.appendChild(li); // Append the li to the ul
                    });

                    // Step 4: Append the ul to the div
                    div.appendChild(ul);

                    // Step 5: Append the div to the parent container in the HTML
                    divList.appendChild(div);

                    ul.addEventListener('click', function (event) {
                        divList.innerHTML = '';
                        document.getElementById('search-product').value = '';
                        // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                        if (event.target.tagName.toLowerCase() === 'li') {
                            const selectedId = event.target.id; // Get the ID of the clicked <li>
                            console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                            getProductData(selectedId);
                        }
                    });

                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#results').empty(); // Clear results if the input is empty
        }
    });

    getTaskType(document.getElementById("setTaskTypeData"));
    getTaskType(document.getElementById("setTaskTypeOnTimer"));

});

$('#getCustomerListedit').on('click', function () {

    const billingDetailContact = document.getElementById('billingDetailContact');
    billingDetailContact.innerHTML = '';

    const getCustomerListValue = document.getElementById('getCustomerListedit');

    const option = document.createElement('option');
    option.value = getCustomerListValue.value;
    option.text = "Default";
    billingDetailContact.appendChild(option);

    getBillingDetailsData(getCustomerListValue.value);

    const customerSiteDetails = document.getElementById('customerSiteDetails');
    const customerSiteDelivery = document.getElementById('customerSiteDelivery');

    customerSiteDetails.innerHTML = '';
    customerSiteDelivery.innerHTML = '';

    const option3 = document.createElement('option');
    option3.value = getCustomerListValue.value;
    option3.text = "Same as customer";
    const option4 = option3.cloneNode(true);
    customerSiteDetails.appendChild(option3);
    customerSiteDelivery.appendChild(option4);

    removeAddCustomerSiteAddress(customerSiteDetails, customerSiteDelivery, getCustomerListValue.value);
});

function getAccountCode() {
    $.ajax({
        url: getActiveAccountCodeURL,
        method: 'GET',
        success: function (response) {
            console.log("response.getActiveAccountCode", response.data);
            // Ensure response.data contains the account codes
            if (Array.isArray(response.data)) {
                // Iterate over all Account Code dropdowns and populate them
                document.querySelectorAll('#accoutCodeList').forEach(dropdown => {
                    // dropdown.innerHTML = ''; // Clear existing options

                    const optionInitial = document.createElement('option');
                    optionInitial.textContent = "-No Department-"; // Use appropriate key from your response
                    optionInitial.value = "";
                    dropdown.appendChild(optionInitial);
                    // Append new options
                    response.data.forEach(code => {
                        const option = document.createElement('option');
                        option.value = code.id;
                        option.textContent = code.departmental_code + "-" + code.name; // Use appropriate key from your response
                        dropdown.appendChild(option);
                    });
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

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".quantity").forEach(function (input) {
        input.addEventListener("input", function () {
            this.value = this.value.replace(/\s/g, "").replace(/[^0-9]/g, "");
        });
    });
});

document.getElementById("submitBtn").addEventListener("click", function () {
    document.getElementById("myForm").submit();
});


$(document).ready(function() {
    // $('.discount-container').each(function() {
    //     let discountType = $(this).find('.selectedDiscountType').val();
    //     $(this).find('.discount_type_value').val(discountType);
    // });

    $('.discount-container').each(function() {
        let discountType = $(this).find('.selectedDiscountType').val();
        let discountSelect = $(this).find('.discount_type_value');
    
        // Set the selected value and trigger change event
        discountSelect.val(discountType).trigger('change');
    });
    
});


