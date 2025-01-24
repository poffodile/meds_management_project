<div class="modal fade" id="{{ $rejectModalId }}" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">{{ $modalTitle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                <div class="text-center mt-3" id="message_rejectModal" style="display:none"></div>
                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <div class="formDtail">
                            <form id="{{ $rejectformId }}" class="customerForm pt-0">
                                @csrf
                                <input type="hidden" name="id" id="{{ $rejectId }}">
                                <input type="hidden" name="po_id" id="reject_po_id">
                                <div class="row">
                                    <label for="inputName" class="col-md-12 col-lg-12 col-xl-12 col-sm-12 col-form-label">Would you like to notify anyone that this purchase order '<span id="rejectpurchaseOrderRef"></span>' has been Rejected?</label>
                                </div>
                                <div class="mb-2 row">
                                <label for="inputName" class="col-md-3 col-lg-3 col-xl-3 col-sm-3 col-form-label">Notify?</label>
                                    <div class="col-sm-9">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="notify_radio" id="{{ $radioNo }}" value="0" checked="">
                                            <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="notify_radio" value="1" id="{{ $radioYes }}">
                                            <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-2 row notificationHideShow" style="display:none">
                                    <label for="inputProject"
                                        class="col-sm-3 col-form-label">Notify Who?</label>
                                    <div class="col-sm-9">
                                        <?php $rejectusers = App\User::where('home_id', Auth::user()->home_id)->select('id', 'name','email','phone_no')->where('is_deleted', 0)->get();?>
                                        <select class="form-control editInput selectOptions" id="{{ $rejectNotifyWho }}" name="notify_user_id">
                                            <option value=""></option>
                                            <option value="{{Auth::user()->id}}">Me - {{Auth::user()->email}} / {{Auth::user()->phone_no ?? 'No Mobile'}}</option>
                                            @foreach($rejectusers as $value)
                                            @if($value->id != Auth::user()->id)
                                            <option value="{{ $value->id }}">{{ $value->name }} - {{$value->email}} / {{$value->phone_no ?? 'No Mobile'}}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row notificationHideShow" style="display:none">
                                    <label class="col-sm-3 col-form-label">Send As<span class="radStar ">*</span> </label>
                                    <div class="col-sm-9">
                                        <label for="purchase_notify_who1" class="editInput">
                                            <input type="checkbox" name="notification" id="{{ $rejectNotification }}" value="1" checked=""> Notification (User Only)
                                        </label>
                                        <label for="purchase_notify_who2" class="editInput">
                                            <input type="checkbox" name="sms" id="{{ $rejectSms }}" value="1" checked=""> SMS
                                        </label>
                                        <label for="purchase_notify_who3" class="editInput">
                                            <input type="checkbox" name="email" id="{{ $rejectEmail }}" value="1" checked=""> Email
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="inputAddress" class="col-sm-3 col-form-label">Resion for Rejecting?<span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput CustomercheckError" id="{{ $inputRejectMessage }}" name="message" rows="10" maxlength="500"></textarea>
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
        $.ajax({
            type: "POST",
            url: "{{url('purchaseOrderreject')}}",
            data: new FormData($("#{{ $rejectformId }}")[0]),
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
                        $('#message_rejectModal').addClass('success-message').text(response.message).show();
                        setTimeout(function() {
                            $('#message_rejectModal').removeClass('success-message').text('').hide();
                            getAllPurchaseInvices(response.data);
                        }, 3000);
                    }else{
                        // alert("Something went wrong");
                        $('#message_rejectModal').addClass('error-message').text(response.message).show();
                        setTimeout(function() {
                            // $('#error-message').text('').fadeOut();
                            $('#message_rejectModal').removeClass('error-message').text('').hide();
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