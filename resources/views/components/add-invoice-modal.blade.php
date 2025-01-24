<div class="modal fade" id="{{ $invoiceModalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalTitle }}"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_invoiceModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $invoiceformId }}" class="customerForm">
                                @csrf
                                <input type="hidden" name="id" id="{{ $invoiceId }}">
                                <input type="hidden" name="po_id" id="invoice_po_id">
                                <input type="hidden" name="supplier_id" id="invoice_supplier_id">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Purchase Order</label>
                                    <div class="col-sm-9">
                                        <p id="{{ $inputInvoiceRef }}">PO-0003 on 15/11/2024</p>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Supplier</label>
                                    <div class="col-sm-9">
                                        <p id="{{ $inputInvoiceSupplierName }}">ABC</p>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Invoice Ref<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" id="{{ $inputInvoiceRef }}" name="inv_ref" value="" placeholder="Invoice refrence,number">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Net Amount<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" id="{{ $invoiceNetAmount }}" name="net_amount" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject" class="col-sm-3 col-form-label">VAT<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                    <?php $rate=App\Models\Construction_tax_rate::getAllTax_rate(Auth::user()->home_id,'Active');?>
                                    <select class="form-control editInput selectOptions" id="{{ $invoiceVatId }}" name="vat_id">
                                        <option value="0" selected>Custom VAT Amount</option>
                                        @foreach($rate as $rate_vale)
                                            <option value="{{$rate_vale->tax_rate}}">{{$rate_vale->name}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">VAT Amount</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" id="{{ $invoiceVatAmount }}" name="vat_amount" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Gross Amount<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" id="{{ $invoiceGrossAmount }}" name="gross_amount" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Invoice Date<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control editInput" id="{{ $invoiceDate }}" name="invoice_date" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Due Date</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control editInput" id="{{ $invoiceDueDate }}" name="due_date" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Notes</label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput CustomercheckError" id="{{ $invoiceNotes }}" name="notes" rows="10" maxlength="500"></textarea>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-2 col-form-label">Attachments</label>
                                    <div class="col-sm-10">
                                        <input type="file" class="editInput" id="{{ $invoiceAttachemnt}}" name="file" value="">
                                        <p class="editInput">(Max file size 25 MB)</p>
                                        <p id="fileSizeError" style="color: red; display: none;">File larger than 25 MB.</p>
                                        <p id="file_name"></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="profileDrop" id="{{ $saveButtonId }}">Save</button>
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    const maxFileSize = 25 * 1024 * 1024;
    const fileInput = document.getElementById('{{ $invoiceAttachemnt}}');
    const errorMessage = document.getElementById('fileSizeError');

    fileInput.addEventListener('change', function() {
        errorMessage.style.display = 'none';
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size;
            if (fileSize > maxFileSize) {
                errorMessage.style.display = 'block';

                fileInput.value = '';
            }
        }
    });
    $("#{{ $invoiceNetAmount }}").on('keyup', function() {
        var amount = parseFloat($("#{{ $invoiceNetAmount }}").val());
        if (!isNaN(amount)) {
            $("#{{ $invoiceGrossAmount }}").prop('disabled', false);
            $("#{{ $invoiceVatAmount }}").val('0.00');
            $("#{{ $invoiceGrossAmount }}").val(amount.toFixed(2));
        } else {
            $("#{{ $invoiceGrossAmount }}").val('');
            $("#{{ $invoiceVatAmount }}").val('');
            $("#{{ $invoiceGrossAmount }}").prop('disabled', true);
        }
    });
    $("#{{ $invoiceVatId }}").on('change', function(){
        var vat=parseFloat($("#{{ $invoiceVatId }}").val());
        var amount = parseFloat($("#{{ $invoiceNetAmount }}").val());
        if(vat == 0){
            $("#{{ $invoiceVatAmount }}").val('0.00');
        }else{
            $("#{{ $invoiceVatAmount }}").val(vat);
        }
        var calculation=amount*vat/100;
        var gross_amount=amount+calculation;
        $("#{{ $invoiceGrossAmount }}").val(gross_amount.toFixed(2));
    });
    function calculate_vat(){
        var vat_amount=parseFloat($("#{{ $invoiceVatAmount }}").val());
        // alert(vat_amount)
        var amount = parseFloat($("#{{ $invoiceNetAmount }}").val());
        if (!isNaN(vat_amount)) {
            console.log(1);
            $("#{{ $invoiceVatId }}").val(0);
            var calculation=amount*vat_amount/100;
            var gross_amount=amount+calculation;
            $("#{{ $invoiceGrossAmount }}").val(gross_amount.toFixed(2));
        }else{
            console.log(2);
            $("#{{ $invoiceGrossAmount }}").val(amount.toFixed(2));
        }
    }
    $("#{{ $saveButtonId }}").on('click',function(){
        $.ajax({
            type: "POST",
            url: "{{url('purchaseOrderInviceRecieve')}}",
            data: new FormData($("#{{ $invoiceformId }}")[0]),
            async: false,
            contentType: false,
            cache: false,
            processData: false,
                success: function(response) {
                    // console.log(response);return false;
                    if(response.vali_error){
                        alert(response.vali_error);
                        $("#email").css('border','1px solid red');
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_invoiceModal').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_invoiceModal').removeClass('success-message').text('').hide();
                            getAllPurchaseInvices(response.data);
                        }, 3000);
                    }else{
                        // alert("Something went wrong");
                        $('#message_invoiceModal').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            // $('#error-message').text('').fadeOut();
                            $('#message_invoiceModal').removeClass('error-message').text('').hide();
                        }, 3000);
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                   var errorMessage = xhr.status + ': ' + xhr.statusText;
                    console.log('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
    });
</script>