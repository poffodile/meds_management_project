<div class="modal fade" id="contact_modal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="contactModalLabel"></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" id="alert_message_contactModal" style="display:none"></div>
                <form id="contact_form">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label id="contactLabel"></label>
                                <p id="contact_customer_name"></p>
                            </div>
                            <input type="hidden" id="contact_customer_id" name="customer_id">
                            <input type="hidden" id="userType" name="userType">
                            <div class="mb-3" id="contact_billing_radio">
                                <label>Default Billing</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_yes">
                                        <label class="form-check-label checkboxtext"
                                            for="inlineRadio1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="contact_radio" id="contact_default_no" checked>
                                        <label class="form-check-label checkboxtext"
                                            for="inlineRadio2">No</label>
                                    </div>
                                    <input type="hidden" id="default_billing" name="default_billing" value="0">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Contact Name <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput" id="contact_contact_name" name="contact_name">
                            </div>
                            <div class="mb-3" id="contact_job_title_field">
                                <label>Job Title (Position)</label>
                                <div class="row">
                                    <div class="col-sm-10 pe-0">
                                        <select class="form-control editInput selectOptions get_job_title_result"
                                            id="contact_job_titile_id" name="job_title_id">
                                            <option selected disabled>Please Select</option>
                                            <?php foreach($job_title as $con_val_title){?>
                                                <option value="{{$con_val_title->id}}">{{$con_val_title->name}}</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('contact_job_titile_id')">
                                        <i class="fa fa-plus-square"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="text" class="form-control editInput" id="contact_email" name="email" onchange="CheckContactEmail()">
                                <span style="color:red" id="CheckContactEmailErr"></span>
                            </div>
                            <div class="mb-3">
                                <label>Telephone</label>
                                <div class="row">
                                    <div class="col-sm-4 pe-0">
                                        <select class="form-control editInput selectOptions" id="contact_telephone_code" name="telephone_country_code">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $contacttelCode)
                                                <option value="{{$contacttelCode->id}}" <?php if($contacttelCode->id == 230){echo 'selected';}?>>+{{$contacttelCode->code}} - {{$contacttelCode->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput"
                                            id="contact_phone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="CheckContactTelephoneErr">Please enter 10 digit number</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Mobile</label>
                                <div class="row">
                                    <div class="col-sm-4 pe-0">
                                        <select class="form-control editInput selectOptions" id="contact_mobile_code" name="mobile_country_code">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $contactmobCode)
                                                <option value="{{$contactmobCode->id}}" <?php if($contactmobCode->id == 230){echo 'selected';}?>>+{{$contactmobCode->code}} - {{$contactmobCode->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput" id="contact_mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="CheckContactMobileErr">Please enter 10 digit number</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Fax</label>
                                <input type="text" class="form-control editInput" id="contact_fax" name="fax">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Address Details</label>
                                <div class="d-flex">same as default
                                    <div class="form-check form-check-inline ms-2">
                                        <input class="form-check-input" type="checkbox" name="inlineRadioOptions" id="contact_default_address" onchange="default_address()">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Address <span class="radStar ">*</span></label>
                                <textarea class="form-control textareaInput" id="contact_address" name="address" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>City</label>
                                <input type="text" class="form-control editInput" id="contact_city" name="city">
                            </div>
                            <div class="mb-3">
                                <label>County</label>
                                <input type="text" class="form-control editInput" id="contact_country_input" name="country">
                            </div>
                            <div class="mb-3">
                                <label>Pincode</label>
                                <input type="text" class="form-control editInput" id="contact_pincode" name="postcode">
                            </div>
                            <div class="mb-3">
                                <label>Country</label>
                                <select class="form-control editInput selectOptions"
                                    id="contact_country_id" name="country_id">
                                    <option selected disabled>Select Coutry</option>
                                    <?php foreach($country as $countryval){?>
                                    <option value="{{$countryval->id}}" <?php if($countryval->id == 230){echo 'selected';}?> class="contact_country_id">{{$countryval->name}} ({{$countryval->code}})</option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="save_contact()">Save</button>
                <!-- <button type="button" class="profileDrop" onclick="save_contactClose()">Save & Close</button> -->
            </div>
        </div>
    </div>
 </div>

<script>
    function CheckContactEmail(){
        var email= $('#contact_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1){
            $('#CheckContactEmailErr').text("Please enter correct email address");
            return false;
        }else{
            $('#CheckContactEmailErr').text("");
        }
  }
    function default_address(){
        var check;
        if ($('#contact_default_address').is(':checked')) {
            check=1;
        }else {
            check=0;
        }
        var token='<?php echo csrf_token();?>'
        var customer_id=$("#contact_customer_id").val();
        var userType=$("#userType").val();
        if(customer_id == null || customer_id == ''){
            alert("please select Customer");
            $('#contact_default_address').prop('checked',false);
            return false;
        }
        $.ajax({
            type: "POST",
            url: "{{url('/default_address')}}",
            data: {check:check,customer_id:customer_id,userType:userType,_token:token},
            success: function(response) {
                console.log(response);
                if(check == 1){
                    var data=response.details;
                    $("#contact_address").val(data.address);
                    $("#contact_city").val(data.city);
                    $("#contact_country_input").val(data.country ?? data.county);
                    $("#contact_pincode").val(data.postal_code ?? data.postcode);
                }else{
                    $("#contact_address").val('');
                    $("#contact_city").val('');
                    $("#contact_country_input").val('');
                    $("#contact_pincode").val('');
                }
                if(response.reslut){
                    $("#contact_country_id").html(response.reslut);
                }
            }
        });
    }
    function save_contact(){
        var CheckContactEmailErr=$("#CheckContactEmailErr").text();
        var token='<?php echo csrf_token();?>'
        if ($('#contact_default_yes').is(':checked')) {
            $("#default_billing").val(1);
        }else {
            $("#default_billing").val(0);
        }
        var customer_id=$("#contact_customer_id").val();
        var contact_name=$("#contact_contact_name").val();
        var telephone=$("#contact_phone").val();
        var mobile=$("#contact_mobile").val();
        var address=$("#contact_address").val();
        var userType=$("#userType").val();
        if(customer_id == '' || customer_id == null){
            $("#contact_customer_id").css('border','1px solid red');
                alert("Something went wrong! PLease try later");
            return false;
        }else if(contact_name == ''){
            $("#contact_customer_id").css('border','');
            $('#contact_contact_name').css('border','1px solid red');
            return false;
        }else if(CheckContactEmailErr.length>0){
            alert("Please fill correct Email");
            $('#contact_contact_name').css('border','');
            $("#contact_email").css('border','1px solid red');
            return false;
        }else if(telephone !='' && telephone.length !=10){
            $("#contact_email").css('border','');
            $("#CheckContactTelephoneErr").show();
            return false;
        }else if(mobile !='' && mobile.length !=10){
            $("#CheckContactTelephoneErr").hide();
            $("#CheckContactMobileErr").show();
            return false;
        }else if(address == ''){
            $("#CheckContactTelephoneErr").hide();
            $("#CheckContactMobileErr").hide();
            $('#contact_contact_name').css('border','');
            $("#contact_address").css('border','1px solid red');
            return false;
        }else {
            $("#CheckContactTelephoneErr").hide();
            $("#CheckContactMobileErr").hide();
            $("#contact_customer_id").css('border','');
            $("#contact_contact_name").css('border','');
            $("#contact_address").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/save_contact')}}",
                data: new FormData($("#contact_form")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    console.log(data);
                    if (isAuthenticated(data) == false) {
                        return false;
                    }
                    if(data.vali_error){
                        alert(data.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    }else if(data.success === true){
                        $(window).scrollTop(0);
                        $('#alert_message_contactModal').text(data.message).show();
                        setTimeout(function() {
                            GetAllContact(data);
                            $('#alert_message_contactModal').text('').hide();
                            $("#contact_modal").modal('hide');
                        }, 3000);
                    }else{
                        alert("Something went wrong! Please try later");
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + xhr.responseJSON.message);
                }
            });
        }
    }
</script>