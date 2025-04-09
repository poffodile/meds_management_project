<div class="modal fade" id="{{ $paymentTypeModalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="text-center mt-3" id="message_paymentTypeModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $paymentTypeformId }}" class="customerForm">
                                @csrf
                                <input type="hidden" name="id" id="{{ $paymentTypeId }}">
                                <div class="mb-2 row">
                                    <label for="inputName" class="col-sm-3 col-form-label">Payment Type <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput" id="{{ $inputPaymentType }}" name="title" value="">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label class="col-sm-3 col-form-label">Mob. User Visible</label>
                                    <div class="col-sm-9">

                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mobile_visible" value="1" id="{{ $radioYes }}">
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="mobile_visible" value="0" id="{{ $radioNo }}" checked>
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="{{ $selectStatus }}" name="status">
                                            <option value="1" >Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
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
    $("#{{ $saveButtonId }}").on('click',function(){
        var title=$("#{{ $inputPaymentType }}").val();
        if(title == ''){
            $("#{{ $inputPaymentType }}").css('border','1px solid red');
            return false;
        }else{
            $("#{{ $inputPaymentType }}").css('border','');
            $.ajax({
            type: "POST",
            url: "{{url('/save_payment_type')}}",
            data: new FormData($("#{{ $paymentTypeformId }}")[0]),
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
                        $('#message_paymentTypeModal').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_paymentTypeModal').removeClass('success-message').text('').hide();
                            $("#{{ $paymentTypeModalId }}").modal('hide');
                            getAllPaymentType(response.data);
                        }, 3000);
                    }else if(response.success === false){
                        $('#message_paymentTypeModal').addClass('error-message').text(response.message).show();
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
        
    });
</script>