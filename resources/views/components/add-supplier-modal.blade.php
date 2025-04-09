<style>
    .tag_box{
        background: #f5f5f5;
        display: grid;
        border: 1px solid #dee2e6;
    }
</style>
<div class="modal fade" id="supplierPop" tabindex="-1" aria-labelledby="aupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="aupplierModalLabel">New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="alert alert-success mt-3" id="alert_message_supplier" style="display:none"></div>
            <div class="modal-body">
                <form id="supplier_form_data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="supplier_name" class="col-sm-3 col-form-label">Supplier Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput suppliercheckError" placeholder="Enter Supplier Name" id="supplier_name" name="name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_code" class="col-sm-3 col-form-label">Supplier Code</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Code" id="supplier_code" name="code">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="contact_name" class="col-sm-3 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput suppliercheckError" placeholder="Enter Contact Name" id="supplier_contact_name" name="contact_name">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="email" class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Email" id="supplier_email" name="email" onchange="supplier_check_email()">
                                        <span style="color:red" id="supplieremailErr"></span>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label for="supplier_telephone" class="col-sm-3 col-form-label">Telephone</label>
                                        <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" id="supplier_telephone_code_id" name="telephone_code_id">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $teleCode)
                                                <option value="{{$teleCode->id}}" <?php if($teleCode->id == 230){echo 'selected';}?>>+{{$teleCode->code}} - {{$teleCode->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control editInput textareaInput" id="supplier_telephone" placeholder="Enter Supplier Telephone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="ChecksupplierTelephoneErr">Please enter 10 digit number</span>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label for="supplier_mobile" class="col-sm-3 col-form-label">Mobile</label>
                                        <div class="col-sm-2">
                                        <select class="form-control editInput selectOptions" id="supplier_mobile_code_id" name="mobile_code_id">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $mobCode)
                                                <option value="{{$mobCode->id}}" <?php if($mobCode->id == 230){echo 'selected';}?>>+{{$mobCode->code}} - {{$mobCode->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control editInput textareaInput" id="supplier_mobile" placeholder="Enter Supplier Mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="ChecksupplierMobileErr">Please enter 10 digit number</span>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label for="supplier_fax" class="col-sm-3 col-form-label">Fax</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Fax" id="supplier_fax" name="fax">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_website" class="col-sm-3 col-form-label">Website</label>
                                    <div class="col-sm-2">
                                        <div class="tag_box text-center">
                                            <span style="padding:3px">http://</span> 
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Website" id="supplier_website" name="website">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_status" class="col-sm-3 col-form-label">Status</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="supplier_status" name="status">
                                            <option value='1'>Active</option>
                                            <option value='0'>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-2 row">
                                    <label for="supplier_address" class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control textareaInput suppliercheckError" maxlength="300" placeholder="Enter Supplier Address" id="supplier_address" name="address" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label for="supplier_city" class="col-sm-3 col-form-label">City</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier City" id="supplier_city" name="city">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_county" class="col-sm-3 col-form-label">County</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier County" id="supplier_county" name="county">
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_postcode"
                                        class="col-sm-3 col-form-label">Postcode</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Postcode" id="supplier_postcode" name="postcode">
                                    </div>
                                </div>

                                <div class="mb-2 row">
                                    <label for="supplier_country_id"
                                        class="col-sm-3 col-form-label">Country</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="supplier_country_id" name="country_id">
                                            <option selected disabled>Select Country</option>
                                            <?php foreach($country as $countryval){?>
                                            <option value="{{$countryval->code}}" <?php if(isset($supplier) && $supplier->country_id == $countryval->id){echo 'selected';} else if($countryval->id == 230){echo 'selected';}?>>{{$countryval->name}} ({{$countryval->code}})</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <label for="supplier_account_ref" class="col-sm-3 col-form-label">Account Ref.</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Account Ref." id="supplier_account_ref" name="account_ref">
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="supplier_currency_id" class="col-sm-3 col-form-label">Currency</label>
                                    <div class="col-sm-9">
                                        <select class="form-control editInput selectOptions" id="supplier_currency_id" name="currency_id">
                                            @foreach($currency as $curr)
                                            <option value="{{$curr->id}}" selected>{{$curr->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3 row">
                                    <label for="supplier_creadit_limit" class="col-sm-3 col-form-label">Credit Limit</label>
                                    <div class="col-sm-1">
                                        <div class="tag_box text-center">
                                            <span style="padding:3px">£</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control editInput textareaInput" placeholder="Enter Supplier Credit Limit" name="creadit_limit" id="supplier_creadit_limit" value="<?php if(isset($supplier) && $supplier !=''){echo $supplier->creadit_limit;}else{echo '0.00';}?>" oninput="suppliervalidateDecimal(this)" maxlength="8">
                                    </div>
                                </div>   
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="mb-2 notes_input">
                                <label for="supplier_note" class="col-form-label">Notes</label>
                                <textarea class="form-control textareaInput" placeholder="Enter Supplier Notes" rows="3" id="supplier_note" name="notes"></textarea>
                            </div>
                        </div>
                        
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" onclick="save_supplier()">Save</button>
                <!-- <button type="button" class="profileDrop" onclick="save_supplierClose()">Save &
                    Close</button> -->
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function supplier_check_email(){
        var email= $('#supplier_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1){
            $('#supplieremailErr').text("Please enter correct email address");
            return false;
        }else{
            $('#supplieremailErr').text("");
        }
  }

    function suppliervalidateDecimal(input) {
        input.value = input.value.replace(/[^0-9.]/g, '');
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.substring(0, input.value.lastIndexOf('.'));
        }
        if (input.value.length > 8) {
            input.value = input.value.substring(0, 8);
        }
    }
    function save_supplier(){
        var emailErr=$("#supplieremailErr").text();
        $('.suppliercheckError').each(function() {
            if ($(this).val() === '') {
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border','');
            }
        });
        var supplier_telephone=$("#supplier_telephone").val();
        var supplier_mobile=$("#supplier_mobile").val();
        if(supplier_telephone !='' && supplier_telephone.length !=10){
            $("#ChecksupplierTelephoneErr").show();
            return false;
        }else if(supplier_mobile !='' && supplier_mobile.length !=10){
            $("#ChecksupplierTelephoneErr").hide();
            $("#ChecksupplierMobileErr").show();
            return false;
        }
        if(emailErr.length>0){
            $("#ChecksupplierTelephoneErr").hide();
            $("#ChecksupplierMobileErr").hide();
            $('#supplier_email').focus();
            return false;
        }else{
            $("#ChecksupplierTelephoneErr").hide();
            $("#ChecksupplierMobileErr").hide();
            $.ajax({
                type: "POST",
                url: "{{url('/supplier_save')}}",
                data: new FormData($("#supplier_form_data")[0]),
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
                        $('#alert_message_supplier').text(response.message).show();
                        setTimeout(function() {
                            $('#alert_message_supplier').text('').hide();
                            getAllSupplier(response.supplier);
                            $("#supplierPop").modal('hide');
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