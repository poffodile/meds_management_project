$(document).ready(function() {
    // getRegions(document.getElementById('invoiceRegions'));
    getTags(document.getElementById('invoice_tags'));
    getAllNewTaskList(invoice_id, getAllInvoiceNewTaskListUrl);
    if(reminder_dataCount > 0){
        $(".setRiminderTable").show();
    }
});
function getTags(tags) {
    $.ajax({
        url: tagURL,
        method: 'GET',
        success: function(response) {
            // console.log("jxcnjfjnfnk", response.data);
            tags.innerHTML = '';
            response.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.text = user.title;
                tags.appendChild(option);
            });
        },
        error: function(xhr, status, error) {
            console.error(error);
        }
    });
}
function bgColorChange(button) {
    $('.bgColour').removeAttr('style');
    $("#recurringHideShow").hide();
    $("#taskHideShow").hide();
    if (button == 1) {
        $("#taskHideShow").show();
        $("#task_active_inactive").css('background-color', '#474747');
    } else {
        $("#recurringHideShow").show();
        $("#recurring_active_inactive").css('background-color', '#474747');
    }
}
    //Text Editer

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

    CKEDITOR.replace('invoice_customer_notes', editor_config);
    CKEDITOR.replace('invoice_terms_notes', editor_config);
    CKEDITOR.replace('invoice_internal_notes', editor_config);
    //Text Editer

function get_modal(modal){
    var customer_id=$("#invoice_customer_id").val();
    if(modal == 1){  
        $("#AddCustomerModal")[0].reset();
        $("#job_title_plusIcon").hide();
        $("#customerPop").modal('show');
    }else if(modal ==2 && customer_id !=null){
        if (customer_id == '' || customer_id == null) {
            $("#HideShowFieldText").hide();
            $("#HideShowFieldSelect").show();
        } else {
            $("#HideShowFieldText").show();
            $("#HideShowFieldSelect").hide();
        }
        $("#project_form")[0].reset();
        $("#project_modal").modal('show');
    }else if(modal ==3 && customer_id !=null){
        $("#contact_form")[0].reset();
        $('#contactModalLabel').text("Add Customer Contact");
        $('#contactLabel').text("Customer");
        $('#userType').val(1);
        $('#contact_customer_id').val(customer_id);
        $("#contact_billing_radio").hide();
        $("#contact_modal").modal('show');
    }else if(modal ==4 && customer_id !=null){
        itemsAddProductModal(1);
    }else if(modal == 5){
        $("#vattaxrateform")[0].reset();
        $("#VatTaxRateModal").modal('show');
    }else if (modal == 6) {
        if (invoice_id == '') {
            if (confirm("Invoice details should be saved before attaching any files. Do you want to save the invoice now?")) {
                save_all_data();
            }
        } else {
            $("#purchase_Attachmentform")[0].reset();
            $("#Invoice_ref").val(invoice_ref);
            $("#invoice_id").val(invoice_id);
            $("#purchase_model").modal('show');
        }
    }else if(modal == 7){
        $("#add_tag_form")[0].reset();
        $("#TagModal").modal('show');
    }else if(modal == 8){
        $("#newTaskform")[0].reset();
        get_customer_details();
        $("#modal_label_title").text('Customer');
        $("#related_To").text(invoice_ref);
        $("#task_invoice_id").val(invoice_id);
        $("#NewTaskModal").modal('show');
    }else{
        alert("Please Select Customer");
        return false;
    }
    
}
function open_customer_type_modal() {
    $('#cutomer_type_modal').modal('show');
}
$("#invoice_customer_id").on('change',function(){
    $("#invoice_project_id").removeAttr('disabled');
    $("#invoice_contact_id").removeAttr('disabled');
    $("#invoice_site_id").removeAttr('disabled');
});

function openProductmodal() {
    var customer_id=$("#invoice_customer_id").val();
    if (customer_id == null) {
        alert("Please Select Customer first");
        return false;
    } else {
        openProductListModal();
    }
}
$(document).ready(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#search-product').on('keyup', function() {
        let query = $(this).val();
        const divList = document.querySelector('.parent-container');

        if (query === '') {
            divList.innerHTML = '';
        }

        // Make an AJAX call only if query length > 2
        if (query.length > 2) {
            $.ajax({
                url: get_itemUrl, // Laravel route
                method: 'GET',
                data: {
                    query: query
                },
                success: function(response) {
                    // console.log(response);
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
                        // const hr = document.createElement('hr');
                        // hr.className='dropdown-divider';
                        // ul.appendChild(hr);
                    });

                    // Step 4: Append the ul to the div
                    div.appendChild(ul);

                    // Step 5: Append the div to the parent container in the HTML
                    divList.appendChild(div);

                    ul.addEventListener('click', function(event) {
                        divList.innerHTML = '';
                        document.getElementById('search-product').value = '';
                        // Check if the clicked element is an <li> (to avoid triggering on other child elements)
                        if (event.target.tagName.toLowerCase() === 'li') {
                            const selectedId = event.target.id; // Get the ID of the clicked <li>
                            // console.log('Selected Product ID:', selectedId); // Print the ID of the selected product
                            getProductData(selectedId);
                        }
                    });

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#results').empty(); // Clear results if the input is empty
        }
    });

});
function getProductData(selectedId) {
    selectProduct(selectedId);
}
function selectProduct(id) {
    var key = 'order';
    $.ajax({
        type: "POST",
        url: result_product_calculationUrl,
        data: {
            id: id,
            key: key,
            _token: token
        },
        success: function(data) {
            // console.log(data);return false;
            const tableBody = document.querySelector(`#result tbody`);

            if (data.length === 0) {
                const noDataRow = document.createElement('tr');
                noDataRow.id = 'EmptyError'
                const noDataCell = document.createElement('td');

                noDataCell.setAttribute('colspan', 4);
                noDataCell.textContent = 'No products found';
                noDataCell.style.textAlign = 'center';

                noDataRow.appendChild(noDataCell);
                tableBody.appendChild(noDataRow);
            } else {
                const emptyErrorRow = document.getElementById('EmptyError');
                if (emptyErrorRow) {
                    emptyErrorRow.remove();
                }
                const row = document.createElement('tr');
                
                const codeCell = document.createElement('td');
                // codeCell.textContent = data.product_detail.product_code;
                const inputCode = document.createElement('input');
                inputCode.className = 'product_code form_control';
                inputCode.name = 'product_code[]';
                inputCode.value = '';
                codeCell.appendChild(inputCode);
                row.appendChild(codeCell);
                
                const nameCell = document.createElement('td');
                nameCell.innerHTML = data.product_detail.product_name;
                row.appendChild(nameCell);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.className = 'product_id';
                hiddenInput.name = 'product_id[]';
                hiddenInput.value = data.product_detail.id;
                row.appendChild(hiddenInput);

                const descriptionCell = document.createElement('td');
                const inputDescription = document.createElement('textarea');
                inputDescription.className = 'description form_control';
                inputDescription.setAttribute('rows', '1');
                inputDescription.name = 'description[]';
                // inputDescription.value = data.product_detail.description;
                inputDescription.value = '';
                inputDescription.addEventListener('input', function() {
                    auto_grow(this);
                });
                descriptionCell.appendChild(inputDescription);
                row.appendChild(descriptionCell);

                const dropdownAccountCode = document.createElement('td');
                const selectDropdownAccountCode = document.createElement('select');
                selectDropdownAccountCode.className = 'accountCode_id form_control';
                selectDropdownAccountCode.name = 'accountCode_id[]';

                const optionsAccountCode = data.accountCode;

                const defaultOptionAccountCode = document.createElement('option');
                defaultOptionAccountCode.value = '';
                defaultOptionAccountCode.text = '-No Department-';
                selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                optionsAccountCode.forEach(optionJob => {
                    const optAccountCode = document.createElement('option');
                    optAccountCode.value = optionJob.id;
                    optAccountCode.textContent = optionJob.name;
                    selectDropdownAccountCode.appendChild(optAccountCode);
                });
                dropdownAccountCode.appendChild(selectDropdownAccountCode);
                row.appendChild(dropdownAccountCode);

                const qtyCell = document.createElement('td');
                const inputQty = document.createElement('input');
                inputQty.type = 'text';
                inputQty.className = 'qty input50 form_control';
                inputQty.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    if ((this.value.match(/\./g) || []).length > 1) {
                        this.value = this.value.slice(0, -1);
                    }
                    updateAmount(row);
                });
                inputQty.name = 'qty[]';
                inputQty.value = '1';
                qtyCell.appendChild(inputQty);
                row.appendChild(qtyCell);

                const priceCell = document.createElement('td');
                const inputPrice = document.createElement('input');
                inputPrice.type = 'text';
                inputPrice.className = 'product_price input50 form_control';
                // inputPrice.addEventListener('input', function() {
                //     updateAmount(row);
                // });
                inputPrice.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                    if ((this.value.match(/\./g) || []).length > 1) {
                        this.value = this.value.slice(0, -1);
                    }
                    updateAmount(row);
                });
                inputPrice.name = 'price[]';
                inputPrice.value = data.product_detail.price;
                priceCell.appendChild(inputPrice);
                row.appendChild(priceCell);

                const calcIconCell = document.createElement('td');
                const calcIcon = document.createElement('i');
                calcIcon.className = 'fa fa-calculator fs-4';
                calcIcon.style='cursor: pointer;color: #c4e3f3;';
                calcIcon.name = 'icon[]';
                calcIconCell.appendChild(calcIcon);
                row.appendChild(calcIconCell);

                const amountCell = document.createElement('td');
                amountCell.innerHTML = '£' + parseFloat(data.product_detail.price).toFixed(2);
                amountCell.className = "price";
                row.appendChild(amountCell);

                const discountCell = document.createElement('td');
                const discountDiv = document.createElement('div');
                discountDiv.className = " d-flex gap-2";
                discountCell.className = "discount";
                const input = document.createElement('input');
                input.type = "text";
                input.className = "discount-input form-control";
                input.value = "0";
                input.name = "discount[]";

                const select = document.createElement('select');
                select.className = "discount-select form_control";
                select.name = "discount_type[]";

                const option1 = document.createElement('option');
                option1.value = "1";
                option1.textContent = "%";

                const option2 = document.createElement('option');
                option2.value = "2";
                option2.textContent = "£";
                select.appendChild(option1);
                select.appendChild(option2);
                discountDiv.appendChild(input);
                discountDiv.appendChild(select);
                discountCell.appendChild(discountDiv);
                row.appendChild(discountCell);

                const dropdownVat = document.createElement('td');
                const selectDropdownVat = document.createElement('select');
                selectDropdownVat.addEventListener('change', function() {
                    // alert(`You selected: ${this.options[this.selectedIndex].text}`);
                    getIdVat($(this).val(), row);
                });
                selectDropdownVat.name = 'vat_id[]';
                selectDropdownVat.className = 'vat_id form_control';
                const optionsVat = data.tax;
                var tax_rate = '00';
                optionsVat.forEach(optionVat => {
                    const optVat = document.createElement('option');
                    optVat.value = optionVat.id;
                    if (optionVat.id == data.product_detail.tax_rate) {
                        tax_rate = optionVat.tax_rate;
                        optVat.setAttribute("selected", "selected");
                    }
                    optVat.textContent = optionVat.name;
                    selectDropdownVat.appendChild(optVat);
                });
                const inputVatRate = document.createElement('input');
                inputVatRate.type = 'hidden';
                inputVatRate.className = 'vat_ratePercentage';
                inputVatRate.name = 'vat_ratePercentage[]';
                inputVatRate.value = tax_rate;
                dropdownVat.appendChild(inputVatRate);
                dropdownVat.appendChild(selectDropdownVat);
                row.appendChild(dropdownVat);

                const vatCell = document.createElement('td');
                const inputVat = document.createElement('input');
                inputVat.type = 'text';
                inputVat.className = 'vat form_control';
                inputVat.style = "max-width:70px;";
                inputVat.setAttribute('disabled', 'disabled');
                inputVat.addEventListener('input', function() {
                    updateAmount(row);
                });
                inputVat.name = 'vat[]';
                inputVat.value = parseFloat(tax_rate).toFixed(2);
                vatCell.appendChild(inputVat);
                row.appendChild(vatCell);

                const delveriQTYCell = document.createElement('td');
                delveriQTYCell.innerHTML = '-';
                delveriQTYCell.className = 'text-center';
                row.appendChild(delveriQTYCell);

                const spanCheckboxCell = document.createElement('td');
                const spanCheckbox = document.createElement('span');
                spanCheckbox.className = 'oNOfswich';

                const innerCheckbox = document.createElement('input');
                innerCheckbox.type = 'checkbox';
                innerCheckbox.name = 'show_temp';
                innerCheckbox.id = 'show_temp';
                innerCheckbox.value = 1;
                innerCheckbox.checked = true;

                spanCheckbox.appendChild(innerCheckbox);
                spanCheckboxCell.appendChild(spanCheckbox);
                row.appendChild(spanCheckboxCell);

                const deleteCell = document.createElement('td');
                deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;cursor: pointer;"></i>';
                deleteCell.addEventListener('click', function() {
                    removeRow(this);
                });
                row.appendChild(deleteCell);

                tableBody.appendChild(row);
                updateAmount(row)
                $("#product_calculation").show();
            }

        }
    });
}
function getProductDetail(id,url){
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            id: id,
            _token: token
        },
        success: function(response) {
            // console.log(response);
            // return false;
            var data = response.data[0];
            const tableBody = document.querySelector(`#result tbody`);
            var invoice_products = data.product_details.invoice_products;
            // console.log(invoice_products);return false;
            if (invoice_products.length === 0) {
                const noDataRow = document.createElement('tr');
                noDataRow.id = 'EmptyError'
                const noDataCell = document.createElement('td');

                noDataCell.setAttribute('colspan', 4);
                noDataCell.textContent = 'No products found';
                noDataCell.style.textAlign = 'center';

                noDataRow.appendChild(noDataCell);
                tableBody.appendChild(noDataRow);
            } else {
                const emptyErrorRow = document.getElementById('EmptyError');
                if (emptyErrorRow) {
                    emptyErrorRow.remove();
                }
                var paid_amount = response.paid_amount;
                $("#paid_amount").text("-£" + parseFloat(paid_amount).toFixed(2));
                invoice_products.forEach(product => {
                    const row = document.createElement('tr');

                    const codeCell = document.createElement('td');
                    // codeCell.textContent = data.purchase_order_products_detail.product_code;
                    const inputCode = document.createElement('input');
                    inputCode.className = 'product_code form-control';
                    inputCode.name = 'product_code[]';
                    inputCode.value = product.code;
                    codeCell.appendChild(inputCode);
                    row.appendChild(codeCell);

                    const nameCell = document.createElement('td');
                    nameCell.innerHTML = data.invoice_products_detail.product_name;
                    row.appendChild(nameCell);
                   
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.className = 'product_id';
                    hiddenInput.name = 'product_id[]';
                    hiddenInput.value = data.invoice_products_detail.id;
                    row.appendChild(hiddenInput);
                    
                    const hiddenID = document.createElement('input');
                    hiddenID.type = 'hidden';
                    hiddenID.className = 'purchase_product_id';
                    hiddenID.name = 'invoice_product_id[]';
                    hiddenID.value = product.id;
                    row.appendChild(hiddenID);

                    const descriptionCell = document.createElement('td');
                    const inputDescription = document.createElement('textarea');
                    inputDescription.className = 'description form-control';
                    inputDescription.setAttribute('rows', '1');
                    inputDescription.name = 'description[]';
                    inputDescription.value = product.description;
                    inputDescription.addEventListener('input', function() {
                        auto_grow(this);
                    });
                    descriptionCell.appendChild(inputDescription);
                    row.appendChild(descriptionCell);
                    
                    const dropdownAccountCode = document.createElement('td');
                    const selectDropdownAccountCode = document.createElement('select');
                    selectDropdownAccountCode.className = 'accountCode_id form_control';
                    selectDropdownAccountCode.name = 'accountCode_id[]';
                    // selectDropdownAccountCode.addEventListener('click', function() {
                    //     var elements = document.getElementsByClassName('accountCode_id');
                    //     getAccountCode(elements);
                    // });

                    const optionsAccountCode = data.accountCode;

                    const defaultOptionAccountCode = document.createElement('option');
                    defaultOptionAccountCode.value = '';
                    defaultOptionAccountCode.text = '-No Department-';
                    selectDropdownAccountCode.appendChild(defaultOptionAccountCode);

                    optionsAccountCode.forEach(optionJob => {
                        const optAccountCode = document.createElement('option');
                        optAccountCode.value = optionJob.id;
                        optAccountCode.textContent = optionJob.name;
                        selectDropdownAccountCode.appendChild(optAccountCode);
                    });
                    dropdownAccountCode.appendChild(selectDropdownAccountCode);
                    row.appendChild(dropdownAccountCode);
                    
                    const qtyCell = document.createElement('td');
                    const inputQty = document.createElement('input');
                    inputQty.type = 'text';
                    inputQty.className = 'qty input50 form-control';
                    inputQty.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                        updateAmount(row, paid_amount);
                    });
                    inputQty.name = 'qty[]';
                    inputQty.value = product.qty;
                    qtyCell.appendChild(inputQty);
                    row.appendChild(qtyCell);

                    const priceCell = document.createElement('td');
                    const inputPrice = document.createElement('input');
                    inputPrice.type = 'text';
                    inputPrice.className = 'product_price input50 form-control';
                    inputPrice.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                        updateAmount(row, paid_amount);
                    });
                    inputPrice.name = 'price[]';
                    inputPrice.value = product.price;
                    priceCell.appendChild(inputPrice);
                    row.appendChild(priceCell);

                    const calcIconCell = document.createElement('td');
                    const calcIcon = document.createElement('i');
                    calcIcon.className = 'fa fa-calculator fs-4';
                    calcIcon.style='cursor: pointer;color: #c4e3f3;';
                    calcIcon.name = 'icon[]';
                    calcIconCell.appendChild(calcIcon);
                    row.appendChild(calcIconCell);

                    const amountCell = document.createElement('td');
                    amountCell.innerHTML = '£' + parseFloat(product.price).toFixed(2);
                    amountCell.className = "price";
                    row.appendChild(amountCell);

                    const discountCell = document.createElement('td');
                    const discountDiv = document.createElement('div');
                    discountDiv.className = " d-flex gap-2";
                    discountCell.className = "discount";
                    const input = document.createElement('input');
                    input.type = "text";
                    input.className = "discount-input form-control";
                    input.value = "0";
                    input.name = "discount[]";

                    const select = document.createElement('select');
                    select.className = "discount-select form_control";
                    select.name = "discount_type[]";

                    const option1 = document.createElement('option');
                    option1.value = "1";
                    option1.textContent = "%";

                    const option2 = document.createElement('option');
                    option2.value = "2";
                    option2.textContent = "£";
                    select.appendChild(option1);
                    select.appendChild(option2);
                    discountDiv.appendChild(input);
                    discountDiv.appendChild(select);
                    discountCell.appendChild(discountDiv);
                    row.appendChild(discountCell);

                    const dropdownVat = document.createElement('td');
                    const selectDropdownVat = document.createElement('select');
                    selectDropdownVat.addEventListener('change', function() {
                        getIdVat($(this).val(), row, paid_amount);
                    });
                    selectDropdownVat.name = 'vat_id[]';
                    selectDropdownVat.className = 'vat_id form_control';
                    const optionsVat = data.tax;
                    var tax_rate = '00';
                    optionsVat.forEach(optionVat => {
                        const optVat = document.createElement('option');
                        optVat.value = optionVat.id;
                        if (optionVat.id == product.vat_id) {
                            tax_rate = optionVat.tax_rate;
                            optVat.setAttribute("selected", "selected");
                        }
                        optVat.textContent = optionVat.name;
                        selectDropdownVat.appendChild(optVat);
                    });
                   
                    const inputVatRate = document.createElement('input');
                    inputVatRate.type = 'hidden';
                    inputVatRate.className = 'vat_ratePercentage';
                    inputVatRate.name = 'vat_ratePercentage[]';
                    inputVatRate.value = tax_rate;
                    dropdownVat.appendChild(inputVatRate);
                    dropdownVat.appendChild(selectDropdownVat);
                    row.appendChild(dropdownVat);

                    const vatCell = document.createElement('td');
                    const inputVat = document.createElement('input');
                    inputVat.type = 'text';
                    inputVat.className = 'vat form-control';
                    inputVat.style = "max-width:70px;";
                    inputVat.setAttribute('disabled', 'disabled');
                    inputVat.addEventListener('input', function() {
                        updateAmount(row, paid_amount);
                    });
                    inputVat.name = 'vat[]';
                    inputVat.value = parseFloat(tax_rate).toFixed(2);
                    vatCell.appendChild(inputVat);
                    row.appendChild(vatCell);

                    const delveriQTYCell = document.createElement('td');
                    delveriQTYCell.innerHTML = '-';
                    delveriQTYCell.className = 'text-center';
                    row.appendChild(delveriQTYCell);

                    const spanCheckboxCell = document.createElement('td');
                    const spanCheckbox = document.createElement('span');
                    spanCheckbox.className = 'oNOfswich';

                    const innerCheckbox = document.createElement('input');
                    innerCheckbox.type = 'checkbox';
                    innerCheckbox.name = 'show_temp';
                    innerCheckbox.id = 'show_temp';
                    innerCheckbox.value = 1;
                    innerCheckbox.checked = true;

                    spanCheckbox.appendChild(innerCheckbox);
                    spanCheckboxCell.appendChild(spanCheckbox);
                    row.appendChild(spanCheckboxCell);

                    const deleteCell = document.createElement('td');
                    deleteCell.innerHTML = '<i class="fas fa-times fa-2x deleteRow" style="color: red;"></i>';
                    deleteCell.addEventListener('click', function() {
                        removeRow(this, product.id);
                    });
                    row.appendChild(deleteCell);

                    tableBody.appendChild(row);
                    updateAmount(row, paid_amount)
                    // console.log(row)
                });
                $("#product_calculation").show();

            }

            // var paginationProductDetails = response.pagination;

            // var paginationControlsProductDetail = $("#pagination-controls-Produc-details");
            // paginationControlsProductDetail.empty();
            // if (paginationProductDetails.prev_page_url) {
            //     paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.prev_page_url + '\')">Previous</button>');
            // }
            // if (paginationProductDetails.next_page_url) {
            //     paginationControlsProductDetail.append('<button type="button" class="profileDrop" onclick="getProductDetail(' + id + ', \'' + paginationContact.next_page_url + '\')">Next</button>');
            // }
        },
        error: function(xhr, status, error) {
            console.error(error);
            // location.reload();
        }
    });

}
function auto_grow(element) {
    // console.log("Here")
    element.style.height = "5px";
    element.style.height = (element.scrollHeight) + "px";
}
var check_paid_amount = 0;

    function updateAmount(row, paid_amount = 0) {
        // console.log(row)
        // return false;
        // const priceInput = row.querySelector('.price');
        // alert(typeof(paid_amount))

        if (paid_amount != 0) {
            check_paid_amount = paid_amount;
        }
        const priceInput = row.querySelector('.product_price');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('td:nth-last-child(3)');
        const price = parseFloat(priceInput.value) || 0;
        const qty = parseInt(qtyInput.value) || 1;
        const amount = price * qty;
        amountCell.textContent = '£' + amount.toFixed(2);
        const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value;
        const vat = row.querySelector('.vat');
        const percentage = amount * vat_ratePercentage / 100;
        // alert(typeof(percentage))
        vat.value = percentage.toFixed(2);

        var calculation = 0;
        $('.price').each(function() {
            const priceText = $(this).text();
            const numericValue = parseFloat(priceText.replace(/[^\d.]/g, ''));
            // console.log(typeof(numericValue));
            calculation = calculation + numericValue;
        });
        var vat_amount = 0;
        $('.vat').each(function() {
            const vat = $(this).val();
            vat_amount = vat_amount + Number(vat);
        });
        totalAmount = calculation;
        // console.log(typeof(vat_amount));
        // document.getElementById('GrandTotalAmount').innerHTML='$'+totalAmount.toFixed(2);
        $("#productPrice").val(totalAmount.toFixed(2));
        $("#exact_vat").text('£' + totalAmount.toFixed(2));
        $("#vat").text('£' + vat_amount.toFixed(2));
        var total_vat = totalAmount + vat_amount;
        $("#total_vat").text('£' + total_vat.toFixed(2));
        var outstanding_amount = total_vat - check_paid_amount;
        $("#outstanding_vat").text('£' + outstanding_amount.toFixed(2));
    }
    function getIdVat(vat_id, row, paid_amount = 0) {
        $.ajax({
            type: "POST",
            url: vat_tax_detailsUrl,
            data: {
                vat_id: vat_id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                if (response) {
                    const vat_value = Number(response.data);
                    const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value = vat_value;
                    // var td=row.querySelector('td:nth-last-child(4)');
                    // var input = td.querySelector('.vat');
                    // // console.log(typeof(vat_value));
                    // input.value = vat_value.toFixed(2) || 0;
                    updateAmount(row, paid_amount);
                } else {
                    alert("Something went wrong");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
    function removeRow(button, id = null) {
        // console.log(button);
        const table = document.getElementById("result");
        const tbody = table.querySelector("tbody");
        const rowCount = tbody ? tbody.rows.length : 0;
        if (rowCount <= 1) {
            $("#product_calculation").hide();
        }
        var row = button.parentNode;

        if (id) {
            $.ajax({
                type: "POST",
                url: invoice_productsDeleteUrl,
                data: {
                    id: id,
                    _token: token
                },
                success: function(data) {
                    // console.log(data);
                    if (data.success != true) {
                        alert("Something went wrong! Please try later");
                        return false;
                    } else {
                        row.parentNode.removeChild(row);
                        updateAmount(row);
                    }
                }
            });
        } else {
            row.parentNode.removeChild(row);
            updateAmount(row);
        }
    }
    function invoice_check_email() {
        var email = $('#invoice_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1) {
            $('#invoiceemailErr').text("Please enter correct email address");
            return false;
        } else {
            $('#invoiceemailErr').text("");
        }
    }
    function save_all_data() {
        for (var instance in CKEDITOR.instances) {
            CKEDITOR.instances[instance].updateElement();
        }
        var emailErr = $("#invoiceemailErr").text();
        $('.InvoicecheckError').each(function() {
            if ($(this).val() === '' || $(this).val() == null) {
                $(this).css('border', '1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border', '');
            }
        });
        var invoice_telephone = $("#invoice_telephone").val();
        var invoice_mobile = $("#invoice_mobile").val();
        var invoice_siteTelephone = $("#invoice_siteTelephone").val();
        var invoice_site_mobile = $("#invoice_site_mobile").val();
        if (invoice_telephone != '' && invoice_telephone.length != 10) {
            $("#InvoiceTelephoneErr").show();
            return false;
        } else if (invoice_mobile != '' && invoice_mobile.length != 10) {
            $("#InvoiceTelephoneErr").hide();
            $("#InvoiceMobileErr").show();
            return false;
        } else if (invoice_siteTelephone != '' && invoice_siteTelephone.length != 10) {
            $("#InvoiceTelephoneErr").hide();
            $("#InvoiceMobileErr").hide();
            $("#InvoiceSiteTelephoneErr").show();
            return false;
        } else if (invoice_site_mobile != '' && invoice_site_mobile.length != 10) {
            $("#InvoiceTelephoneErr").hide();
            $("#InvoiceMobileErr").hide();
            $("#InvoiceSiteTelephoneErr").hide();
            $("#InvoiceSiteMobileErr").show();
            return false;
        } else if (emailErr.length > 0) {
            $("#InvoiceTelephoneErr").hide();
            $("#InvoiceMobileErr").hide();
            $("#InvoiceSiteTelephoneErr").hide();
            $("#InvoiceSiteMobileErr").hide();
            return false;
        } else {
            $("#InvoiceTelephoneErr").hide();
            $("#InvoiceMobileErr").hide();
            $("#InvoiceSiteTelephoneErr").hide();
            $("#InvoiceSiteMobileErr").hide();
            $.ajax({
                type: "POST",
                url: invoice_saveUrl,
                data: new FormData($("#all_data")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else if (response.success === true) {
                        $(window).scrollTop(0);
                        $('#message_save').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_save').removeClass('success-message').text('').hide();
                            // var id = parseInt(response.data.id, 10) || 0;
                            // var encodedId = btoa(unescape(encodeURIComponent(id)));
                            // location.href = '<?php echo url('purchase_order_edit'); ?>?key=' + encodedId;
                            location.href = redirectUrl
                        }, 3000);
                    } else if (response.success === false) {
                        $('#message_save').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            $('#error-message').text('').fadeOut();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
    }
    var invoice_name = '';
    var invoicesite_companyName = '';
    var invoice_address = '';
    var invoice_city = '';
    var invoice_county = '';
    var invoice_Postcode = '';
    var invoice_telephoneCode;
    var invoice_telephone = '';
    var invoice_mobile_code;
    var invoice_mobile = '';
    var invoice_email = '';
    var invoice_siteName = '';
    var invoicesite_companyName = '';

    function get_customer_details() {
        var customer_id = $("#invoice_customer_id").val();
        $.ajax({
            type: "POST",
            url: get_customer_details_frontUrl,
            data: {
                customer_id: customer_id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                // return false;
                if (data.customers && data.customers.length > 0) {
                    var customerData = data.customers[0];
                    var project = '<option value="0" selected disabled>None</option>';
                    if (customerData.customer_project && Array.isArray(customerData.customer_project)) {
                        for (let i = 0; i < customerData.customer_project.length; i++) {
                            project += '<option value="' + customerData.customer_project[i].id + '">' + customerData.customer_project[i].project_name + '</option>';
                        }
                    }
                    document.getElementById('invoice_project_id').innerHTML = project;

                    var contact = '<option value="0" selected disabled>None</option>';
                    if (customerData.additional_contact && Array.isArray(customerData.additional_contact)) {
                        for (let i = 0; i < customerData.additional_contact.length; i++) {
                            contact += '<option value="' + customerData.additional_contact[i].id + '">' + customerData.additional_contact[i].contact_name + '</option>';
                        }
                    }
                    document.getElementById('invoice_contact_id').innerHTML = contact;

                    var site = '<option value="0">Same as customer</option>';
                    if (customerData.sites && Array.isArray(customerData.sites)) {
                        for (let i = 0; i < customerData.sites.length; i++) {
                            site += '<option value="' + customerData.sites[i].id + '">' + customerData.sites[i].site_name + '</option>';
                        }
                    }
                    document.getElementById('invoice_site_id').innerHTML = site;
                    $("#project_customer_name").text(customerData.name);
                    $("#site_customer_name").text(customerData.name);
                    $(".customer_name").text(customerData.name);
                    $("#task_customer_id").val(customer_id);
                    invoice_name = customerData.name;
                    invoicesite_companyName = customerData.contact_name;
                    invoice_address = customerData.address;
                    invoice_city = customerData.city;
                    invoice_county = customerData.country;
                    invoice_Postcode = customerData.postal_code;
                    invoice_telephoneCode = customerData.telephone_country_code ?? 230;
                    invoice_telephone = customerData.telephone;
                    invoice_mobile_code = customerData.mobile_country_code ?? 230;
                    invoice_mobile = customerData.mobile;
                    invoice_siteName = customerData.name;
                    invoice_email = customerData.email;
                    $("#invoice_name").val(invoice_name);
                    $("#invoice_address").val(invoice_address);
                    $("#invoice_city").val(invoice_city);
                    $("#invoice_county").val(invoice_county);
                    $("#invoice_Postcode").val(invoice_Postcode);
                    $("#invoice_telephoneCode").val(invoice_telephoneCode);
                    $("#invoice_telephone").val(invoice_telephone);
                    $("#invoice_mobile_code").val(invoice_mobile_code);
                    $("#invoice_mobile").val(invoice_mobile);
                    $("#invoice_email").val(invoice_email);
                    $("#invoice_siteName").val(invoice_siteName);
                    $("#invoicesite_companyName").val(invoicesite_companyName);
                    $("#invoice_site_address").val(invoice_address);
                    $("#invoice_site_city").val(invoice_city);
                    $("#invoice_site_county").val(invoice_county);
                    $("#invoice_site_postcode").val(invoice_Postcode);
                    $("#invoice_siteTelephoneCode").val(invoice_telephoneCode);
                    $("#invoice_siteTelephone").val(invoice_telephone);
                    $("#invoice_siteMobileCode").val(invoice_mobile_code);
                    $("#invoice_site_mobile").val(invoice_mobile);

                    // $('#invoice_project_id').removeAttr('disabled');
                    // $('#invoice_site_id').removeAttr('disabled');
                }
            },
            error: function(xhr, status, error) {
                console.log(error);
            }
        });
    }
    function siteDetail() {
        var id = $("#invoice_site_id").val();
        $.ajax({
            url: getCustomerSiteDetailsUrl,
            method: 'POST',
            data: {
                id: id
            },
            success: function(response) {
                console.log(response.data);
                // return false;
                if (id == 0) {
                    $("#invoice_siteName").val(invoice_siteName);
                    $("#invoicesite_companyName").val(invoicesite_companyName);
                    $("#invoice_site_address").val(invoice_address);
                    $("#invoice_site_city").val(invoice_city);
                    $("#invoice_site_county").val(invoice_county);
                    $("#invoice_site_postcode").val(invoice_Postcode);
                    $("#invoice_siteTelephoneCode").val(invoice_telephoneCode);
                    $("#invoice_siteTelephone").val(invoice_telephone);
                    $("#invoice_siteMobileCode").val(invoice_mobile_code);
                    $("#invoice_site_mobile").val(invoice_mobile);
                } else {
                    $("#invoice_siteName").val(response.data[0].contact_name);
                    $("#invoicesite_companyName").val(response.data[0].company_name);
                    $("#invoice_site_address").val(response.data[0].address);
                    $("#invoice_site_city").val(response.data[0].city);
                    $("#invoice_site_county").val(response.data[0].country);
                    $("#invoice_site_postcode").val(response.data[0].post_code);
                    $("#invoice_siteTelephoneCode").val(response.data[0].telephone_country_code ?? 230);
                    $("#invoice_siteTelephone").val(response.data[0].telephone);
                    $("#invoice_siteMobileCode").val(response.data[0].mobile_country_code ?? 230);
                    $("#invoice_site_mobile").val(response.data[0].mobile);
                }

            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }
    $("#saveTag").on('click', function() {
        var title = $("#tag_title").val().trim();
        var status = $.trim($('#tag_status option:selected').val());

        if (title.includes(',')) {
            alert("Comma not allowed in the tag, please use _ or - instead");
            return false;
        } else if (title == '') {
            $("#tag_title").css('border', '1px solid red');
            return false;
        } else {
            $("#tag_title").css('border', '');
            $.ajax({
                type: "POST",
                url: save_tagUrl,
                data: new FormData($("#add_tag_form")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    // console.log(response);
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else if (response.data && response.data.original && response.data.original.error) {
                        alert(response.data.original.error);
                        return false;
                    } else if (response.success === true) {
                        // $(window).scrollTop(0);
                        // $('#message_save').text(response.message).show();
                        // setTimeout(function() {
                        //     $('#message_save').text('').hide();
                        // }, 3000);
                        $("#TagModal").modal('hide');
                        $("#invoice_tags").append('<option value="' + response.data.id + '">' + response.data.title + '</option>')

                    } else {
                        alert("Something went wrong! Please try later");
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
    });
    function getAllAttachment(data) {
        getAttachment(data.invoice_id, getAttachmentPageUrl)
    }

    function getAttachment(id, getAttachmentPageUrl) {
        $.ajax({
            url: getAttachmentPageUrl,
            method: 'POST',
            data: {
                id: id,
                _token: token
            },
            success: function(response) {
                // console.log(response);return false;
                var paginationAttachment = response.pagination;
                var data = response.data.data;
                // const attachments = response.data.data[0].po_attachments || [];
                const attachments = data;
                // console.log(attachments);
                const tbody = $('#attachments_result');
                tbody.empty();
                attachments.forEach(attachment => {
                    // $("#deleteSelectedRows").show();
                    const attachmentType = attachment.attachment_type?.title || '';
                    const title = attachment.title || '';
                    const description = attachment.description || '';
                    const section = attachment.Purchase_ref || 'Invoice';
                    const fileName = attachment.original_file_name || '';
                    const mime_type = attachment.mime_type || '';
                    const size = attachment.size || '';
                    const created_at = attachment.created_at || '';
                    var customer_visible = attachment.customer_visible || 0;
                    var mobile_user_visible = attachment.mobile_user_visible || 0;
                    var customer_visible_icon='';
                    var mobile_user_visible_icon='';
                    if(customer_visible == 0){
                        customer_visible_icon='<span class="grayCheck" onclick="customer_visible(' + attachment.id +',1)"><i class="fa-solid fa-circle-check"></i></span>';
                    }else{
                        customer_visible_icon='<span class="grencheck" onclick="customer_visible(' + attachment.id + ',0)"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    if(mobile_user_visible ==0){
                        mobile_user_visible_icon='<span class="grayCheck" onclick="mobile_user_visible(' + attachment.id + ',1)"><i class="fa-solid fa-circle-check"></i></span>';
                    }else{
                        mobile_user_visible_icon='<span class="grencheck" onclick="mobile_user_visible(' + attachment.id + ',0)"><i class="fa-solid fa-circle-check"></i></span>';
                    }
                    var date = moment(created_at).format('DD/MM/YYYY HH:mm');
                    var imag_url = attachmentsFileURL + '/' + attachment.file;
                    tbody.append(`
                        <tr>
                            <td><input type="checkbox" id="" class="delete_checkbox" value="` + attachment.id + `"></td>
                            <td>${attachment.id}</td>
                            <td>${attachmentType}</td>
                            <td><input type="hidden" name="purchaseattachment_id[]" value="${attachment.id}"><input type="text" class="form-control" name="purchaseattachment_title[]" value="${title}"></td>
                            <td>${description}</td>
                            <td>${section}</td>
                            <td>${customer_visible_icon}</td>
                            <td>${mobile_user_visible_icon}</td>
                            <td>${fileName}</td>
                            <td>${mime_type} / ${size}</td>
                            <td>${date}</td>
                            <td><div class="d-flex align-items-center"><a href="` + imag_url + `" target="_blank"><i class="fa fa-eye"></i></a> &emsp; <img src="` +delete_image+ `" alt="" class="attachment_delete image_style" data-delete=` + attachment.id + `></div></td>
                        </tr>
                    `);
                });
                var paginationControlsAttachment = $("#pagination-controls-Attachments");
                paginationControlsAttachment.empty();
                if (paginationAttachment.prev_page_url) {
                    paginationControlsAttachment.append('<button type="button" class="profileDrop" onclick="getAttachment(' + id + ', \'' + paginationAttachment.prev_page_url + '\')">Previous</button>');
                }
                if (paginationAttachment.next_page_url) {
                    paginationControlsAttachment.append('<button type="button" class="profileDrop" onclick="getAttachment(' + id + ', \'' + paginationAttachment.next_page_url + '\')">Next</button>');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
    }
function customer_visible(id,customer_visibleData){
    $.ajax({
            url: customer_visibleURL,
            method: 'POST',
            data: {
                id: id,
                customer_visibleData:customer_visibleData,
                _token: token
            },
            success: function(response) {
                // console.log(response);return false;
                if(response.success === true){
                    location.reload();
                }else{
                    alert("Something went wrong!");
                    return false;
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
}
function mobile_user_visible(id,mobile_user_visibleData){
    $.ajax({
            url: mobile_user_visibleURL,
            method: 'POST',
            data: {
                id: id,
                mobile_user_visibleData:mobile_user_visibleData,
                _token: token
            },
            success: function(response) {
                // console.log(response);return false;
                if(response.success === true){
                    location.reload();
                }else{
                    alert("Something went wrong!");
                    return false;
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                // location.reload();
            }
        });
}
$(document).on('click', '.attachment_delete', function() {
    var id = $(this).data('delete');
    if (confirm("Are you sure you want to delete this row?")) {
        $(this).closest('tr').remove();
        $.ajax({
            type: "POST",
            url: deleteInvoiceAttachmentURL,
            data: {
                id: id,
                _token: token
            },
            success: function(data) {
                console.log(data);
            }
        });
    }
});
function openReminderModal(invoice_id = null) {
    $("#reminderform")[0].reset();
    $("#reminder_id").val('');
    $("#clickyesno").removeClass('unclicked');
    $("#reminder_invoice_id").val(invoice_id);
    $("#ReminderModal").modal('show');
}
function getAllReminder(data){
    $(".setRiminderTable").show();
        $("#reminder_data").append(`<tr>
            <td>` + data.title + `</td>    
            <td>` + data.reminder_date + `</td>    
            <td>` + data.reminder_time + `</td>    
            <td><span class="iconColrRad">Pending</span></td>    
            <td>
                <a href="javascript:void(0)" class="iconColrGreen fecth_data" data-id="` + data.id + `" data-invoice_id="` + data.invoice_id +`" data-title="` + data.title + `" data-user_id="` + data.user_id + `" data-reminder_date="` + data.reminder_date + `" data-reminder_time="` + data.reminder_time + `" data-notification="` + data.notification + `" data-sms="` + data.sms + `" data-email="` + data.email + `" data-notes="` + data.notes + `" data-icon="edit"><i class="material-symbols-outlined">edit</i></a>
                <a href="javascript:void(0)" class="iconColrRad reminder_delete" data-delete="` + data.id + `"><i class="material-symbols-outlined">close</i></a>
            </td>    
        </tr>`);
}
$(document).on('click', '.fecth_data', function() {
    $("#ReminderModal").modal('show');
    var id = $(this).data('id');
    var title = $(this).data('title');
    var user_id = $(this).data('user_id');
    // console.log(user_id);return false;
    var reminder_date = $(this).data('reminder_date');
    var reminder_time = $(this).data('reminder_time');
    var notification = $(this).data('notification');
    var sms = $(this).data('sms');
    var email = $(this).data('email');
    var notes = $(this).data('notes');
    var icon = $(this).data('icon');
    var invoice_id = $(this).data('invoice_id');
    if (icon === 'eye') {
        $("#reminder_date").attr('disabled', 'disabled');
        $("#clickyesno").addClass('unclicked');
        $("#reminder_notification").attr('disabled', 'disabled');
        $("#reminder_sms").attr('disabled', 'disabled');
        $("#reminder_email").attr('disabled', 'disabled');
        $("#reminder_title").attr('disabled', 'disabled');
        $("#reminder_notes").attr('disabled', 'disabled');
    } else {
        $("#reminder_date").removeAttr('disabled', 'disabled');
        $("#clickyesno").removeClass('unclicked');
        $("#reminder_notification").removeAttr('disabled', 'disabled');
        $("#reminder_sms").removeAttr('disabled', 'disabled');
        $("#reminder_email").removeAttr('disabled', 'disabled');
        $("#reminder_title").removeAttr('disabled', 'disabled');
        $("#reminder_notes").removeAttr('disabled', 'disabled');
    }


    $("#reminder_id").val(id);
    $("#reminder_date").val(reminder_date);
    $("#reminder_time").val(reminder_time);
    $("#reminder_title").val(title);
    $("#reminder_notes").val(notes);
    $("#reminder_invoice_id").val(invoice_id);

    $("#reminder_notification").prop('checked', notification == 1);
    $("#reminder_sms").prop('checked', sms == 1);
    $("#reminder_email").prop('checked', email == 1);

    if (user_id) {
        var length = user_id.toString().length;
        $('.multiselect-dropdown').hide();
        var userArray = [];
        if(length > 1){
            userArray = user_id.toString().split(',');
        }else{
            userArray = [user_id.toString()];
        }
        $("#reminder_user option").each(function() {
            $(this).prop('selected', userArray.includes($(this).val()));
        });
    } else {
        $("#reminder_user option").prop('selected', false);
    }
    userArray.forEach(function(userId) {
        $(`#reminder_user option[value="${userId}"]`).prop('selected', true);
        $(`#reminder_user + .multiselect-container input[type="checkbox"][value="${userId}"]`).prop('checked', true);
    });
    MultiselectDropdown();

});
$(document).on('click', '.reminder_delete', function(){
    var id = $(this).data('delete');
    var row = $(this).closest("tr");
    if (id) {
        $.ajax({
            type: "POST",
            url: invoice_ReminderDeleteUrl,
            data: {
                id: id,
                _token: token
            },
            success: function(data) {
                console.log(data);
                if (data.success != true) {
                    alert("Something went wrong! Please try later");
                    return false;
                } else {
                    row.remove();
                }
            }
        });
    }
});
function getAllNewTask(data){
    getAllNewTaskList(data.invoice_id, getAllInvoiceNewTaskListUrl);
}
function getAllNewTaskList(id, pageUrl) {
    $.ajax({
        url: pageUrl,
        method: 'POST',
        data: {
            id: id,
            _token: token
        },
        success: function(response) {
            // console.log(response);
            var paginationNewTask = response.pagination;
            const newTask = response.data;
            // console.log(newTask);
            const tbody = $('#newtask_result');
            tbody.empty();
            var taskCount = 1;
            newTask.forEach(task => {
                const created_at = moment(task.created_at).format('DD/MM/YYYY HH:mm');
                const date = moment(task.date).format('DD/MM/YYYY HH:mm');
                var executed = task.executed;
                // alert(executed)
                var exe_icon='';
                if(executed == 0){
                    exe_icon='-';
                }else{
                    exe_icon='<i class="fa fa-check text-success" aria-hidden="true"></i>';
                }
                const imag_path = edit_image;
                tbody.append(`
                    <tr>
                        <td>${date}</td>
                        <td>${task.ref}</td>
                        <td>${task.user}</td>
                        <td>${task.type}</td>
                        <td>${task.title}</td>
                        <td>${task.notes || ''}</td>
                        <td>${created_at}</td>
                        <td>${exe_icon}</td>
                        <td><img src="${imag_path}" height="16px" alt="" data-bs-toggle="modal" data-bs-target="#NewTaskModal" class="modal_dataTaskFetch image_style" data-id="${task.id}" data-invoice_id="${task.invoice_id}" data-customer_id="${task.customer_id}" data-user_id="${task.user_id}" data-title="${task.title}" data-task_type_id="${task.task_type_id}" data-start_date="${task.date}" data-start_time="${task.start_time}" data-end_date="${task.end_date}" data-end_time="${task.end_time}" data-is_recurring="${task.is_recurring}" data-notify="${task.notify}" data-notify_date="${task.notify_date}" data-notify_time="${task.notify_time}" data-notification="${task.notification}" data-email="${task.email}" data-sms="${task.sms}" data-notes="${task.notes}"></td>
                    </tr>
                `);
                taskCount++;
            });
            var paginationControlsNewTask = $("#pagination-controls-New-task");
            paginationControlsNewTask.empty();
            if (paginationNewTask.prev_page_url) {
                paginationControlsNewTask.append('<button type="button" class="profileDrop" onclick="getAllNewTaskList(' + id + ', \'' + paginationNewTask.prev_page_url + '\')">Previous</button>');
            }
            if (paginationNewTask.next_page_url) {
                paginationControlsNewTask.append('<button type="button" class="profileDrop" onclick="getAllNewTaskList(' + id + ', \'' + paginationNewTask.next_page_url + '\')">Next</button>');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            // location.reload();
        }
    });
}
$(document).on('click', '.modal_dataTaskFetch', function() {
    $("#completeBTN").show();
    var taskId = $(this).data('id');
    var task_invoice_id = $(this).data('invoice_id');
    // alert(task_invoice_id)
    var task_customer_id = $(this).data('customer_id');
    var title = $(this).data('title');
    var userId = $(this).data('user_id');
    var taskTypeId = $(this).data('task_type_id');
    var startDate = $(this).data('start_date');
    var startTime = $(this).data('start_time');
    var endDate = $(this).data('end_date');
    var endTime = $(this).data('end_time');
    var isRecurring = $(this).data('is_recurring');
    var notify = $(this).data('notify');
    var notification = $(this).data('notification');
    var email = $(this).data('email');
    var sms = $(this).data('sms');
    var notifyDate = $(this).data('notify_date');
    var notifyTime = $(this).data('notify_time');
    var notes = $(this).data('notes');

    get_customer_details();
    $('#task_id').val(taskId);
    $('#task_invoice_id').val(task_invoice_id);
    $('#task_customer_id').val(task_customer_id);
    $('#task_user_id').val(userId);
    $('#taskTitle').val(title);
    $('#taskTypeId').val(taskTypeId);
    $('#taskStartDate').val(startDate);
    $('#taskStartTime').val(startTime);
    $('#taskEndDate').val(endDate).removeAttr('disabled');
    $('#taskEndTime').val(endTime);
    $('#notify_date').val(notifyDate);
    $('#notify_time').val(notifyTime);
    $('#taskNotesText').val(notes);
    // $(taskEndDate)
    if (isRecurring == 1) {
        $('#isRecurring').prop('checked', true);
    } else {
        $('#isRecurring').prop('checked', false);
    }
    if (notify == 1) {
        $('#yeson').prop('checked', true);
    } else {
        $('#yeson').prop('checked', false);
    }
});