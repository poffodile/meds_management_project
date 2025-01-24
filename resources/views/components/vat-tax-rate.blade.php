<style>
    .tag_box {
    background: #f5f5f5;
    display: grid;
    border: 1px solid #dee2e6;
}
</style>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                <div class="text-center mt-3" id="message_vat_taxshow"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $formId }}" class="customerForm">
                                @csrf
                                <input type="hidden" name="id" id="{{ $id }}">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Tax Rate Name<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput VatTaxcheckError" id="{{ $name }}" name="name" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Tax Rate<span class="radStar ">*</span></label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control editInput VatTaxcheckError" id="{{ $taxRate }}" name="tax_rate" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="tag_box text-center">
                                            <span style="padding:3px">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="{{ $status }}" name="status">
                                            <option value="1" >Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">External Tax Code</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" id="{{ $taxCode }}" name="tax_code" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Expiry Date</label>
                                    <div class="col-sm-9">
                                        <input type="date" class="form-control editInput" id="{{ $expDate }}" name="exp_date" value="">
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
    $(document).on('click', '#{{$saveButtonId}}', function(){
        $(".VatTaxcheckError").each(function(){
            $(".VatTaxcheckError").css('border','');
            if($(this).val() == ''){
                $(this).css('border','1px solid red');
                return false;
            }
        })
        $.ajax({
                type: "POST",
                url: "{{ url('/save_tax_rate') }}",
                data: new FormData($("#{{$formId}}")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                if(response.vali_error){
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    }else if(response.success === true){
                        $(window).scrollTop(0);
                        $('#message_vat_taxshow').addClass('success-message').text(response.message).show();
                        getAllVatTaxRate(response);
                        setTimeout(function() {
                            $('#message_vat_taxshow').removeClass('success-message').text('').hide();
                            $("#{{ $modalId }}").modal('hide');
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        let errorMessage = 'Validation Errors:\n';
                        for (let field in errors) {
                            // errorMessage += `${errors[field].join(', ')}\n`;
                            alert(errors[field]);
                            return false;
                        }
                        // alert(errorMessage);
                    } else {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + error);
                    }
                }
            });
    });
</script>