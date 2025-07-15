<div class="modal fade" id="{{ $bulkRecordPaymentModalId }}" tabindex="-1" aria-labelledby="recordDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="{{ $modalTitle }}">Record Payment</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_recordDeliveryModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $bulkRecordPaymentformId }}">
                                @csrf
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="modal_search">
                                            <label for="inputPurchase" class=" col-form-label">Search: </label>
                                            <div class=" position-relative">
                                                <input type="search" placeholder="PO Ref" class="form-control" id="bulkRecordPoSearch">
                                                <!-- <input type="hidden" id="selectedBulkInvoicePORefId" name="job_ref"> -->
                                                <div class="search-container bulkRecord-po_ref-container"></div>
                                            </div>
                                        </div>
                                        <div class="productDetailTable table-responsive input_style">
                                            <table class="table border-top border-bottom" id="bulkRecordPayment_result">
                                                <thead>
                                                    <tr class="white_space_nowrap">
                                                        <th class="col-1">PO Ref</th>
                                                        <th class="col-1">Supplier</th>
                                                        <th class="col-2">Mode of Payment</th>
                                                        <th>Payment Date</th>
                                                        <th>Referance</th>
                                                        <th>Outstanding Amount</th>
                                                        <th class="text-end">Received Amount</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- <tr>
                                                    <td colspan="9" class="text text-danger text-center">Sorry, no records to show</td>
                                                </tr> -->
                                                </tbody>
                                                <tfoot>
                                                    <!-- <tr class="white_space_nowrap">
                                                    <td colspan="6" class="border-0"></td>
                                                    <td class="text-end">Sub Total (exc. VAT)</td>
                                                    <th class="text-end" id="bulkInvoiceSubTotal">£0.00</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                                <tr class="white_space_nowrap">
                                                    <td colspan="6" class="border-0"></td>
                                                    <td class="text-end">VAT</td>
                                                    <th class="text-end" id="bulkInvoiceVat">£0.00</th>
                                                    <th class="border-0"></th>
                                                </tr> -->
                                                    <tr class="white_space_nowrap">
                                                        <td colspan="5" class="border-0"></td>
                                                        <th class="text-end">Total Amount</th>
                                                        <th class="text-end" id="bulkRecordPaymentTotal">£0.00</th>
                                                        <th class="border-0"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="{{ $saveButtonId }}" onclick="saveBulkRecordPaymentModal()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#bulkRecordPoSearch').on('input', function() {
        // alert($(this).val())
        let purchase_ref = $(this).val();
        const bulkRecordPayment_divList = document.querySelector('.bulkRecord-po_ref-container');

        if (purchase_ref === '') {
            bulkRecordPayment_divList.innerHTML = '';
        }
        if (purchase_ref.length > 50) {
            $(this).val(purchase_ref.substring(0, 50));
        }
        if (purchase_ref.length > 2) {
            $.ajax({
                url: "{{ url('searchPurchase_ref') }}",
                method: 'post',
                data: {
                    purchase_ref: purchase_ref,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // console.log(response);
                    // return false;
                    bulkRecordPayment_divList.innerHTML = "";
                    const div = document.createElement('div');
                    div.className = 'purchase_job_ref_container';


                    const ul = document.createElement('ul');
                    ul.id = "purchase_job_refList";
                    if (response.data.length > 0) {
                        response.data.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item.purchase_order_ref + ' - ' + item.suppliers.name;
                            li.id = item.id;
                            li.name = item.purchase_order_ref;
                            li.className = "editInput";
                            ul.appendChild(li);
                            // const hr = document.createElement('hr');
                            // hr.className='dropdown-divider';
                            // ul.appendChild(hr);
                        });

                        div.appendChild(ul);

                        bulkRecordPayment_divList.appendChild(div);

                        ul.addEventListener('click', function(event) {
                            bulkRecordPayment_divList.innerHTML = '';
                            document.getElementById('bulkRecordPoSearch').value = '';
                            if (event.target.tagName.toLowerCase() === 'li') {
                                const selectedBulkRecordPaymentPORefId = event.target.id;
                                getAllDetailPurchaseOrder(selectedBulkRecordPaymentPORefId);
                                const selectedPurchaseJobName = event.target.name;
                                // console.log('Selected Customer ID:', selectedBulkRecordPaymentPORefId);
                                // console.log('Selected Customer Name:', selectedPurchaseJobName);
                                // $("#bulkRecordPoSearch").val(selectedPurchaseJobName);
                                // $("#selectedBulkInvoicePORefId").val(selectedBulkRecordPaymentPORefId);
                            }
                        });
                    } else {
                        const Errorli = document.createElement('li');
                        Errorli.textContent = 'Sorry Data Not found';
                        Errorli.id = 'searchError';
                        Errorli.className = "editInput";
                        ul.appendChild(Errorli);
                        div.appendChild(ul);
                        bulkRecordPayment_divList.appendChild(div);
                        setTimeout(function() {
                            bulkRecordPayment_divList.innerHTML = '';
                        }, 1000);
                    }

                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            bulkRecordPayment_divList.innerHTML = '';
            $('#results').empty();
        }
    });

    function getAllDetailPurchaseOrder(po_id) {
        document.getElementById("bulkRecordPoSearch").value = "";
        var token = '<?php echo csrf_token(); ?>';
        var url = `{{ url('getPurchaesOrderProductDetail') }}`;
        fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    id: po_id
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                // return false;
                if (data.success === true) {
                    var po_list = data.data[0];
                    // console.log(po_list.tax);return false;
                    const tableBody = document.querySelector(`#bulkRecordPayment_result tbody`);
                    var purchase_order_products = po_list.product_details.purchase_order_products;
                    var norecorderror = $("#norecorderrorRecordPayment").text();
                    if (norecorderror.length > 1) {
                        tableBody.innerHTML = '';
                    }
                    purchase_order_products.forEach(product => {
                        const row = document.createElement('tr');

                        const po_refCell = document.createElement('td');
                        po_refCell.innerHTML = po_list.product_details.purchase_order_ref;
                        row.appendChild(po_refCell);

                        const supplierNameCell = document.createElement('td');
                        supplierNameCell.textContent = po_list.product_details.suppliers.name;
                        row.appendChild(supplierNameCell);

                        const dropdownPaymentMode = document.createElement('td');
                        const selectDropdownPaymentMode = document.createElement('select');
                        selectDropdownPaymentMode.name = 'record_payment_type[]';
                        selectDropdownPaymentMode.className = 'record_payment_type form_control form-control';
                        const optionsPaymentMode = po_list.payment_type;

                        optionsPaymentMode.forEach(optionVat => {
                            const optPaymentMode = document.createElement('option');
                            optPaymentMode.value = optionVat.id;

                            optPaymentMode.textContent = optionVat.title;
                            selectDropdownPaymentMode.appendChild(optPaymentMode);
                        });
                        dropdownPaymentMode.appendChild(selectDropdownPaymentMode);
                        row.appendChild(dropdownPaymentMode);

                        const dateCell = document.createElement('td');
                        // dateCell.textContent = po_list.product_details.suppliers.name;
                        const inputDate = document.createElement('input');
                        inputDate.type = 'date';
                        inputDate.className = 'bulkinvoice_date form-control';
                        inputDate.name = 'record_payment_date[]';
                        inputDate.value = '';
                        dateCell.appendChild(inputDate);
                        row.appendChild(dateCell);
                        date_convertInFromat();

                        const invoiceRefCell = document.createElement('td');
                        const inputRef = document.createElement('input');
                        inputRef.type = 'text';
                        inputRef.className = ' form-control';
                        inputRef.name = 'record_reference[]';
                        // inputDate.value = product.code;
                        invoiceRefCell.appendChild(inputRef);
                        row.appendChild(invoiceRefCell)

                        const netAmountCell = document.createElement('td');
                        const netAmountInputCell = document.createElement('span');
                        netAmountInputCell.className = 'net_amount';
                        netAmountInputCell.textContent = '£' + po_list.product_details.outstanding_amount;
                        netAmountCell.appendChild(netAmountInputCell);
                        row.appendChild(netAmountCell)

                        const hiddenOutstandingAmount = document.createElement('input');
                        hiddenOutstandingAmount.type = 'hidden';
                        hiddenOutstandingAmount.className = 'outstanding';
                        hiddenOutstandingAmount.name = 'outstanding_amount[]';
                        hiddenOutstandingAmount.value = po_list.product_details.outstanding_amount;
                        row.appendChild(hiddenOutstandingAmount);

                        const inputQty = document.createElement('input');
                        inputQty.type = 'hidden';
                        inputQty.className = 'qty input50 form-control';
                        inputQty.name = 'record_type';
                        inputQty.value = 1;
                        row.appendChild(inputQty);

                        const inputPo_id = document.createElement('input');
                        inputPo_id.type = 'hidden';
                        inputPo_id.className = 'form-control';
                        inputPo_id.name = 'po_id[]';
                        inputPo_id.value = product.id;
                        row.appendChild(inputPo_id);

                        const inputSupplier_id = document.createElement('input');
                        inputSupplier_id.type = 'hidden';
                        inputSupplier_id.className = 'form-control';
                        inputSupplier_id.name = 'supplier_id[]';
                        inputSupplier_id.value = po_list.product_details.suppliers.id;
                        row.appendChild(inputSupplier_id);

                        const AmountCell = document.createElement('td');
                        const divElementAmount = document.createElement('div');
                        divElementAmount.className = 'tag_box_main';
                        const divElementAmountChildDiv = document.createElement('div');
                        divElementAmountChildDiv.className = 'tag_box text-center';
                        const divElementAmountChildDivSpan = document.createElement('span');
                        divElementAmountChildDivSpan.style = 'padding:3px';
                        divElementAmountChildDivSpan.textContent = '£';
                        divElementAmountChildDiv.appendChild(divElementAmountChildDivSpan);
                        divElementAmount.appendChild(divElementAmountChildDiv);
                        const AmountInputCell = document.createElement('input');
                        AmountInputCell.type = 'text';
                        AmountInputCell.className = 'form-control gross_amount';
                        AmountInputCell.name = 'record_amount_paid[]';
                        AmountInputCell.value = po_list.product_details.outstanding_amount;
                        AmountInputCell.addEventListener('input', function() {
                            this.value = this.value.replace(/[^0-9.]/g, '');
                            if ((this.value.match(/\./g) || []).length > 1) {
                                this.value = this.value.slice(0, -1);
                            }
                            updateAmount(row)
                        });
                        divElementAmount.appendChild(AmountInputCell);
                        AmountCell.appendChild(divElementAmount);
                        row.appendChild(AmountCell)

                        const deleteCell = document.createElement('td');
                        const imageElement = document.createElement('img');
                        imageElement.style = "cursor:pointer"
                        imageElement.src = "{{url('public/frontEnd/jobs/images/delete.png')}}";
                        imageElement.className = 'bulkRecordPayment_delete';
                        deleteCell.appendChild(imageElement);
                        deleteCell.addEventListener('click', function() {
                            removeRow(this);
                        });
                        row.appendChild(deleteCell);

                        tableBody.appendChild(row);
                        updateAmount(row)
                        date_convertInFromat()
                    });

                } else {
                    alert("Something went wrong");
                    return false;
                }

            })
        // .catch(error => {
        //     alert("Something went wrong Please try later");
        // });
    }

    function getIdVat(vat_id, row) {
        var token = '<?php echo csrf_token(); ?>'
        $.ajax({
            type: "POST",
            url: "{{url('/vat_tax_details')}}",
            data: {
                vat_id: vat_id,
                _token: token
            },
            success: function(response) {
                // console.log(response);
                if (response) {
                    const vat_value = Number(response.data);
                    const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value = vat_value;
                    updateAmount(row);
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

    function updateAmount(row, ) {
        // console.log(row);
        // return false;
        // const net_amount = row.querySelector('.net_amount');
        // const qtyInput = row.querySelector('.qty');
        // const amountCell = row.querySelector('.gross_amount');
        // const price = parseFloat(net_amount.value) || 0;
        // const qty = parseInt(qtyInput.value);
        // const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value;
        // const vat = row.querySelector('.vat_amount');
        // const amount = price * qty;
        // const percentage=amount*vat_ratePercentage/100;
        // vat.value=percentage.toFixed(2);
        // const finalAmount=percentage+amount;
        // amountCell.value = finalAmount.toFixed(2);


        var gross_amountTotal = 0;
        $(".gross_amount").each(function() {
            const gross_amount = $(this).val();
            const numeric_gross_amount = parseFloat(gross_amount.replace(/[^\d.]/g, ''));
            // console.log(typeof(numeric_gross_amount));
            gross_amountTotal = gross_amountTotal + numeric_gross_amount;
        });
        $("#bulkRecordPaymentTotal").text('£' + gross_amountTotal.toFixed(2));
    }

    function removeRow(button) {
        const table = document.getElementById("bulkRecordPayment_result");
        const tbody = table.querySelector("tbody");
        var row = button.parentNode;
        row.parentNode.removeChild(row);
        updateAmount(row);
    }

    function saveBulkRecordPaymentModal() {
        var formData = new FormData(document.getElementById("{{ $bulkRecordPaymentformId }}"));
        $.ajax({
            url: "{{url('saveBulkRecordPaymentModal')}}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                // return false;
                if (response.success === false) {
                    alert(response.message);
                    return false;
                } else if (response.success === true) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Something went wrong");
                    return false;
                }
            },
            error: function(xhr, status, error) {
                alert('Something went wrong.');
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                console.log('Error - ' + errorMessage + "\nMessage: " + error);
            }
        });
    }
</script>