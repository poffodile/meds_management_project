<div class="modal fade" id="{{ $bulInvoiceModalId }}" tabindex="-1" aria-labelledby="recordDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalTitle }}">Bulk Invoice Received</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_recordDeliveryModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $bulInvoiceformId }}" class="customerForm pt-0">
                                @csrf
                                <div class="row">
                                    <div class="modal_search">
                                        <label for="inputPurchase" class=" col-form-label d-flex">Search: </label>
                                        <div class=" position-relative">
                                            <input type="search" placeholder="PO Ref" class="form-control" id="bulkInvoicePoSearch">
                                            <!-- <input type="hidden" id="selectedBulkInvoicePORefId" name="job_ref"> -->
                                            <div class="search-container bulkInvoice-po_ref-container"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="productDetailTable input_style">
                                        <table class="table" id="bulkInvoiceReceived_result">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="col-1">PO Ref</th>
                                                    <th class="col-1">Supplier</th>
                                                    <th>Date</th>
                                                    <th>Invoice Ref <span class="radStar">*</span></th>
                                                    <th>Net Amount</th>
                                                    <th>VAT(%)</th>
                                                    <th>VAT</th>
                                                    <th class="text-end">Amount</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- <tr>
                                                    <td colspan="9" class="text text-danger text-center">Sorry, no records to show</td>
                                                </tr> -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="6" class="border-0"></td>
                                                    <td class="text-end">Sub Total (exc. VAT)</td>
                                                    <th class="text-end" id="bulkInvoiceSubTotal">£0.00</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="border-0"></td>
                                                    <td class="text-end">VAT</td>
                                                    <th class="text-end" id="bulkInvoiceVat">£0.00</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" class="border-0"></td>
                                                    <th class="text-end">Total Amount</th>
                                                    <th class="text-end" id="bulkInvoiceTotal">£0.00</th>
                                                    <th class="border-0"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="{{ $saveButtonId }}" onclick="saveBulkInvoiceModal()">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#bulkInvoicePoSearch').on('input',function(){
        // alert($(this).val())
            let purchase_ref = $(this).val();
            const purchase_job_refdivList = document.querySelector('.bulkInvoice-po_ref-container');

            if (purchase_ref === '') {
                purchase_job_refdivList.innerHTML = '';
            }
            if (purchase_ref.length > 50) {
                $(this).val(purchase_ref.substring(0, 50));
            }
            if (purchase_ref.length > 2) {
                $.ajax({
                    url: "{{ url('searchPurchase_ref') }}",
                    method: 'post',
                    data: {
                        purchase_ref: purchase_ref,_token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // console.log(response);
                        // return false;
                        purchase_job_refdivList.innerHTML = "";
                        const div = document.createElement('div');
                        div.className = 'purchase_job_ref_container';

                      
                        const ul = document.createElement('ul');
                        ul.id = "purchase_job_refList";
                        if(response.data.length >0){
                            response.data.forEach(item => {
                                const li = document.createElement('li'); 
                                li.textContent = item.purchase_order_ref+' - '+item.suppliers.name; 
                                li.id = item.id;
                                li.name = item.purchase_order_ref;
                                li.className = "editInput";
                                ul.appendChild(li); 
                                // const hr = document.createElement('hr');
                                // hr.className='dropdown-divider';
                                // ul.appendChild(hr);
                            });

                            div.appendChild(ul);

                            purchase_job_refdivList.appendChild(div);

                            ul.addEventListener('click', function(event) {
                                purchase_job_refdivList.innerHTML = '';
                                document.getElementById('bulkInvoicePoSearch').value = '';
                                if (event.target.tagName.toLowerCase() === 'li') {
                                    const selectedBulkInvoicePORefId = event.target.id;
                                    getAllDetailPurchaseOrderBulkInvoice(selectedBulkInvoicePORefId);
                                    const selectedPurchaseJobName = event.target.name;
                                    // console.log('Selected Customer ID:', selectedBulkInvoicePORefId);
                                    // console.log('Selected Customer Name:', selectedPurchaseJobName);
                                    // $("#bulkInvoicePoSearch").val(selectedPurchaseJobName);
                                    // $("#selectedBulkInvoicePORefId").val(selectedBulkInvoicePORefId);
                                }
                            });
                        }else{
                            const Errorli = document.createElement('li'); 
                            Errorli.textContent = 'Sorry Data Not found'; 
                            Errorli.id = 'searchError';
                            Errorli.className = "editInput";
                            ul.appendChild(Errorli); 
                            div.appendChild(ul);
                            purchase_job_refdivList.appendChild(div);
                            setTimeout(function() {
                                purchase_job_refdivList.innerHTML = '';
                            }, 1000);
                        }

                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            } else {
                purchase_job_refdivList.innerHTML = '';
                $('#results').empty();
            }
    });
    function getAllDetailPurchaseOrderBulkInvoice(po_id){
        document.getElementById("bulkInvoicePoSearch").value = "";
        var token='<?php echo csrf_token();?>';
        var url=`{{ url('getPurchaesOrderProductDetail') }}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ id: po_id })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            // return false;
            if(data.success === true){
                var po_list=data.data[0];
                // console.log(po_list.tax);return false;
                const tableBody = document.querySelector(`#bulkInvoiceReceived_result tbody`);
                var purchase_order_products=po_list.product_details.purchase_order_products;
                var norecorderror=$("#norecorderror").text();
                if(norecorderror.length>1){
                    tableBody.innerHTML='';
                }
                var net_amount=0;
                purchase_order_products.forEach(product => {
                    const row = document.createElement('tr');

                    const po_refCell = document.createElement('td');
                    po_refCell.innerHTML = po_list.product_details.purchase_order_ref;
                    row.appendChild(po_refCell);

                    const supplierNameCell = document.createElement('td');
                    supplierNameCell.textContent = po_list.product_details.suppliers.name;
                    row.appendChild(supplierNameCell);

                    const dateCell = document.createElement('td');
                    // dateCell.textContent = po_list.product_details.suppliers.name;
                    const inputDate = document.createElement('input');
                    inputDate.type='date';
                    inputDate.className = 'bulkinvoice_date form-control';
                    inputDate.name = 'invoice_date[]';
                    inputDate.value = '';
                    dateCell.appendChild(inputDate);
                    row.appendChild(dateCell);
                    date_convertInFromat();

                    const invoiceRefCell = document.createElement('td');
                    const inputRef = document.createElement('input');
                    inputRef.type='text';
                    inputRef.className = ' form-control';
                    inputRef.name = 'inv_ref[]';
                    // inputDate.value = product.code;
                    invoiceRefCell.appendChild(inputRef);
                    row.appendChild(invoiceRefCell)

                    const netAmountCell = document.createElement('td');
                    const divElementNetAmount = document.createElement('div');
                    divElementNetAmount.className = 'tag_box_main';
                    const divElementNetAmountChildDiv = document.createElement('div');
                    divElementNetAmountChildDiv.className = 'tag_box text-center';
                    const divElementNetAmountChildDivSpan=document.createElement('span');
                    divElementNetAmountChildDivSpan.style='padding:3px';
                    divElementNetAmountChildDivSpan.textContent='£';
                    divElementNetAmountChildDiv.appendChild(divElementNetAmountChildDivSpan);
                    divElementNetAmount.appendChild(divElementNetAmountChildDiv);
                    const netAmountInputCell=document.createElement('input');
                    netAmountInputCell.type='text';
                    netAmountInputCell.className='form-control net_amount';
                    netAmountInputCell.name='net_amount[]';
                    var calculate=product.price*product.qty;
                    netAmountInputCell.value=net_amount=net_amount+calculate;
                    netAmountInputCell.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                        updateAmountBulkInvoice(row);
                    });
                    divElementNetAmount.appendChild(netAmountInputCell);
                    netAmountCell.appendChild(divElementNetAmount);
                    row.appendChild(netAmountCell)

                    const dropdownVat = document.createElement('td');
                    const selectDropdownVat = document.createElement('select');
                    selectDropdownVat.addEventListener('change', function() {
                        getIdVat($(this).val(),row);
                    });
                    selectDropdownVat.name = 'vat_id[]';
                    selectDropdownVat.className='vat_id form_control form-control';
                    const optionsVat =po_list.tax;
                    var tax_rate=0;
                    
                    optionsVat.forEach(optionVat => {
                    const optVat = document.createElement('option');
                    optVat.value = optionVat.id;
                    // if(optionVat.id == product.vat_id){
                    //     tax_rate=optionVat.tax_rate;
                    //     optVat.setAttribute("selected", "selected");
                    // }
                    if(tax_rate == 0){
                        tax_rate=optionVat.tax_rate;
                    }
                    optVat.textContent = optionVat.name;
                    selectDropdownVat.appendChild(optVat);
                    });
                    var amountCalculation=net_amount*tax_rate/100;
                    var total_amount=net_amount+amountCalculation;
                    const inputVatRate = document.createElement('input');
                    inputVatRate.type = 'hidden';
                    inputVatRate.className = 'vat_ratePercentage';
                    inputVatRate.name = 'vat_ratePercentage[]'; 
                    inputVatRate.value = tax_rate;
                    dropdownVat.appendChild(inputVatRate);
                    dropdownVat.appendChild(selectDropdownVat);
                    row.appendChild(dropdownVat);

                    const inputQty = document.createElement('input');
                    inputQty.type = 'hidden';
                    inputQty.className = 'qty input50 form-control';
                    inputQty.name = 'qty[]';
                    inputQty.value = product.qty;
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

                    const VatCell = document.createElement('td');
                    const divElementVat = document.createElement('div');
                    divElementVat.className = 'tag_box_main';
                    const divElementVatChildDiv = document.createElement('div');
                    divElementVatChildDiv.className = 'tag_box text-center';
                    const divElementVatChildDivSpan=document.createElement('span');
                    divElementVatChildDivSpan.style='padding:3px';
                    divElementVatChildDivSpan.textContent='£';
                    divElementVatChildDiv.appendChild(divElementVatChildDivSpan);
                    divElementVat.appendChild(divElementVatChildDiv);
                    const VatInputCell=document.createElement('input');
                    VatInputCell.type='text';
                    VatInputCell.className='form-control vat_amount';
                    VatInputCell.name='vat_amount[]';
                    VatInputCell.value=amountCalculation;
                    VatInputCell.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                    });
                    divElementVat.appendChild(VatInputCell);
                    VatCell.appendChild(divElementVat);
                    row.appendChild(VatCell)

                    const AmountCell = document.createElement('td');
                    const divElementAmount = document.createElement('div');
                    divElementAmount.className = 'tag_box_main';
                    const divElementAmountChildDiv = document.createElement('div');
                    divElementAmountChildDiv.className = 'tag_box text-center';
                    const divElementAmountChildDivSpan=document.createElement('span');
                    divElementAmountChildDivSpan.style='padding:3px';
                    divElementAmountChildDivSpan.textContent='£';
                    divElementAmountChildDiv.appendChild(divElementAmountChildDivSpan);
                    divElementAmount.appendChild(divElementAmountChildDiv);
                    const AmountInputCell=document.createElement('input');
                    AmountInputCell.type='text';
                    AmountInputCell.style='pointer-events: none;cursor: default;';
                    AmountInputCell.className='form-control gross_amount';
                    AmountInputCell.name='gross_amount[]';
                    AmountInputCell.value=total_amount;
                    AmountInputCell.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9.]/g, '');
                        if ((this.value.match(/\./g) || []).length > 1) {
                            this.value = this.value.slice(0, -1);
                        }
                    });
                    divElementAmount.appendChild(AmountInputCell);
                    AmountCell.appendChild(divElementAmount);
                    row.appendChild(AmountCell)
                    
                    const deleteCell = document.createElement('td');
                    const imageElement = document.createElement('img');
                    imageElement.style="cursor:pointer"
                    imageElement.src="{{url('public/frontEnd/jobs/images/delete.png')}}";
                    imageElement.className = 'bulkInvoice_delete';
                    deleteCell.appendChild(imageElement);
                    deleteCell.addEventListener('click', function() {
                        removeRow(this);
                    });
                    row.appendChild(deleteCell);

                    tableBody.appendChild(row);
                    updateAmountBulkInvoice(row)
                    date_convertInFromat()
                });
                
            }else{
                alert("Something went wrong");
                return false;
            }
            
        })
        // .catch(error => {
        //     alert("Something went wrong Please try later");
        // });
    }
    function getIdVat(vat_id,row){
        var token='<?php echo csrf_token();?>'
        $.ajax({
            type: "POST",
            url: "{{url('/vat_tax_details')}}",
            data: {vat_id:vat_id,_token:token},
            success: function(response) {
                // console.log(response);
                if(response){
                    const vat_value=Number(response.data);
                    const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value=vat_value;
                    updateAmountBulkInvoice(row);
                }else{
                    alert("Something went wrong");
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = xhr.status + ': ' + xhr.statusText;
                alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
            }
        });
    }
    function updateAmountBulkInvoice(row,) {
        // console.log(row);
        // return false;
        const net_amount = row.querySelector('.net_amount');
        const qtyInput = row.querySelector('.qty');
        const amountCell = row.querySelector('.gross_amount');
        const price = parseFloat(net_amount.value) || 0;
        const qty = parseInt(qtyInput.value);
        const vat_ratePercentage = row.querySelector('.vat_ratePercentage').value;
        const vat = row.querySelector('.vat_amount');
        const amount = price * qty;
        const percentage=amount*vat_ratePercentage/100;
        vat.value=percentage.toFixed(2);
        const finalAmount=percentage+amount;
        amountCell.value = finalAmount.toFixed(2);
        // alert(percentage)
        var subTotal=0;
        $(".net_amount").each(function(){
            const net_amount = $(this).val();
            const numeric_net_amount = parseFloat(net_amount.replace(/[^\d.]/g, ''));
            // console.log(typeof(net_amount));
            subTotal=subTotal+numeric_net_amount;
        });
        
        var vatTotal=0;
        $(".vat_amount").each(function(){
            const vat_amount = $(this).val();
            const numeric_vatTotal = parseFloat(vat_amount.replace(/[^\d.]/g, ''));
            // console.log(typeof(numeric_vatTotal));
            vatTotal=vatTotal+numeric_vatTotal;
        });
        
        var gross_amountTotal=0;
        $(".gross_amount").each(function(){
            const gross_amount = $(this).val();
            const numeric_gross_amount = parseFloat(gross_amount.replace(/[^\d.]/g, ''));
            // console.log(typeof(numeric_gross_amount));
            gross_amountTotal=gross_amountTotal+numeric_gross_amount;
        });
        $("#bulkInvoiceSubTotal").text('£'+subTotal.toFixed(2));
        $("#bulkInvoiceVat").text('£'+vatTotal.toFixed(2));
        $("#bulkInvoiceTotal").text('£'+gross_amountTotal.toFixed(2));
    }
    function removeRow(button){
        const table = document.getElementById("bulkInvoiceReceived_result");
        const tbody = table.querySelector("tbody");
        var row = button.parentNode;
        row.parentNode.removeChild(row);
        updateAmountBulkInvoice(row);
    }
    function saveBulkInvoiceModal(){
        var formData = new FormData(document.getElementById("{{ $bulInvoiceformId }}"));
        $.ajax({
            url: "{{url('saveBulkInvoiceModal')}}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                if(response.success === false){
                    alert(response.message);
                    return false;
                }else{
                    alert(response.message);
                    // location.reload();
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