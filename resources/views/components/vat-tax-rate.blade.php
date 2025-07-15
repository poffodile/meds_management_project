<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">{{ $modalTitle }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_vat_taxshow"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <form id="{{ $formId }}" class="customerForm">
                            @csrf
                            <input type="hidden" name="id" id="{{ $id }}">
                            <div class="mb-3">
                                <label>Tax Rate Name <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput VatTaxcheckError" id="{{ $name }}" name="name" value="">
                            </div>
                            <div class="mb-3">
                                <label>Tax Rate <span class="radStar ">*</span></label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control editInput VatTaxcheckError" id="{{ $taxRate }}" name="tax_rate" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="tag_box">
                                            <span>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Status</label>
                                <select class="form-control editInput selectOptions" id="{{ $status }}" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>External Tax Code</label>
                                <input type="text" class="form-control editInput" id="{{ $taxCode }}" name="tax_code" value="" onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode === 46">
                            </div>
                            <div class="mb-3">
                                <label>Expiry Date</label>
                                <input type="date" class="form-control editInput" id="{{ $expDate }}" name="exp_date" value="">
                            </div>
                        </form>
                    </div>
                </div> <!-- End row -->
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a href="javascript:void(0)" id="{{ $saveButtonId }}" class="btn btn-warning"> Save</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '#{{$saveButtonId}}', function() {
        $(".VatTaxcheckError").each(function() {
            $(".VatTaxcheckError").css('border', '');
            if ($(this).val() == '') {
                $(this).css('border', '1px solid red');
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
                if (response.vali_error) {
                    alert(response.vali_error);
                    $(window).scrollTop(0);
                    return false;
                } else if (response.success === true) {
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