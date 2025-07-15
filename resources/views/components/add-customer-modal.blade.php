<div class="modal fade" id="customerPop" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">Add Customer</h4>
            </div>
            <div class="modal-body">
            <div id="alert_message_customer" class="alert alert-success mt-3" style="display:none"></div>
                <form class="customerForm" id="AddCustomerModal">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                @csrf
                                <input type="hidden" id="customer_is_converted" name="is_converted" value="1">
                                <input type="hidden" id="customer_home_id" name="home_id" value="{{Auth::user()->home_id}}">
                                <div class="mb-3">
                                    <label>Customer Name <span class="radStar ">*</span></label>
                                    <input type="text" class="form-control editInput textareaInput CustomercheckError" id="customer_name" name="name" placeholder="Enter Customer Name">
                                </div>
                                <div class="mb-3">
                                    <label>Customer Type</label>
                                    <div class="row">
                                        <div class="col-md-10 pe-0">
                                            <select class="form-control editInput selectOptions get_customer_result" id="customer_type_id" name="customer_type_id">
                                                <option selected disabled>None</option>
                                                <?php foreach ($customer_types as $cust_type) { ?>
                                                    <option value="{{$cust_type->id}}">{{$cust_type->title}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="javascript:void(0)" class="formicon" onclick="open_customer_type_modal()">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div><!-- End off Customer -->
                                <div class="mb-3">
                                    <label>Contact Name <span class="radStar ">*</span></label>
                                    <input type="text" class="form-control editInput textareaInput CustomercheckError" placeholder="Enter Customer Contact Name" id="customer_contact_name" name="contact_name">
                                </div>
                                <div class="mb-3">
                                    <label>Job Title (Position)</label>
                                    <div class="row">
                                        <div class="col-md-10 pe-0">
                                            <select class="form-control editInput selectOptions get_job_title_result" id="customer_job_titile_id" name="job_title">
                                                <option selected disabled>Please Select </option>
                                                <?php foreach ($job_title as $val_title) { ?>
                                                    <option value="{{$val_title->id}}">{{$val_title->name}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2" id="job_title_plusIcon">
                                            <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('customer_job_titile_id')">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer Email" id="customer_email" name="email" onchange="customer_check_email()">
                                    <span id="customeremailErr" style="color:red"></span>
                                </div>
                                <div class="mb-3">
                                    <label>Telephone</label>
                                    <div class="row">
                                        <div class="col-md-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="customer_telephone_country_code" name="telephone_country_code">
                                                <option selected disabled>Please Select</option>
                                                @foreach($country as $custteleCode)
                                                    <option value="{{$custteleCode->id}}" <?php if($custteleCode->id == 230){echo 'selected';}?>>+{{$custteleCode->code}} - {{$custteleCode->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customer_phone" placeholder="Enter Customer Telephone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="CheckcustomerTelephoneErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Mobile</label>
                                    <div class="row">
                                        <div class="col-md-3 pe-0">
                                            <select class="form-control editInput selectOptions" id="customer_mobile_country_code" name="mobile_country_code">
                                                <option selected disabled>Please Select</option>
                                                @foreach($country as $custmobCode)
                                                    <option value="{{$custmobCode->id}}" <?php if($custmobCode->id == 230){echo 'selected';}?>>+{{$custmobCode->code}} - {{$custmobCode->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control editInput textareaInput" id="customer_mobile" placeholder="Enter Customer Mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57"><span style="color:red;display:none" id="CheckcustomerMobileErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Fax</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer Fax" id="customer_fax" name="fax">
                                </div>
                                <div class="mb-3">
                                    <label>Website</label>
                                    <div class="row">
                                        <div class="col-md-3 pe-0">
                                            <div class="tag_box">
                                                <span>http://</span>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer Website" id="customer_website" name="website">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Payment Terms</label>
                                    <div class="row">
                                        <div class="col-md-10 pe-0">
                                            <select class="form-control editInput selectOptions" id="customer_payment_terms" name="payment_terms">
                                                <option value="21">Defoult (21) </option>
                                                <?php for ($i = 1; $i < 21; $i++) { ?>
                                                    <option value="{{$i}}">{{$i}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="afterInputText">Days</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Currency</label>
                                    <select class="form-control editInput selectOptions" id="customer_currency_id" name="currency">
                                        <option selected disabled>Please Select</option>
                                        @foreach($currency as $currVal)
                                        <option value="{{$currVal->id}}" selected>{{$currVal->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="inputCounty">Credit Limit</label>
                                    <div class="row">
                                        <div class="col-md-2 pe-0">
                                            <div class="tag_box">
                                                <span>£</span>
                                            </div>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Credit Limit" id="customer_credit_limit" name="credit_limit" oninput="customervalidateDecimal(this)">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Discount</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Discount" id="customer_discount" name="discount">
                                </div>
                                <div class="mb-3">
                                    <label>Discount Type</label>
                                    <select class="form-control editInput selectOptions" id="customer_percentage" name="discount_type">
                                        <option selected disabled>Please Select</option>
                                        <option value="1">Percentage</option>
                                        <option value="2">Flat</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>VAT / Tax No.</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter VAT/TAX No." id="customer_vat" name="vat_tax_no">
                                </div>
                                <div class="mb-3">
                                    <label>Default Catalogue</label>
                                    <select class="form-control editInput selectOptions" id="customer_catalogue" name="catalogue_id">
                                        <option>None</option>
                                        <option value="ABCD">ABCD</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select class="form-control editInput selectOptions" id="customer_status" name="status">
                                        <option value='1'>Active</option>
                                        <option value='0'>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                <div class="mb-3">
                                    <label>Region</label>
                                    <div class="row">
                                        <div class="col-md-10 pe-0">
                                            <select class="form-control editInput selectOptions get_region_result" id="customer_region" name="region">
                                                <option>None</option>
                                                <?php foreach ($region as $region_val) { ?>
                                                    <option value="{{$region_val->id}}">{{$region_val->title}}</option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="javascript:void(0)" class="formicon" id="openPopupButton" onclick="openRegionModal('customer_region')">
                                                <i class="fa fa-plus-square"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Address <span class="radStar ">*</span></label>
                                    <textarea class="form-control textareaInput CustomercheckError" placeholder="Enter Customer Address" id="cuatomer_address" name="address" rows="1"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>City</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer City" id="customer_city" name="city">
                                </div>
                                <div class="mb-3">
                                    <label>County</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer County" id="customer_country_input" name="country">
                                </div>
                                <div class="mb-3">
                                    <label>Pincode</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Customer Pincode" id="customer_pincode" name="postal_code">
                                </div>
                                <div class="mb-3">
                                    <label>Country</label>
                                    <select class="form-control editInput selectOptions" id="customer_country_id" name="country_code">
                                        <option selected disabled>Select Coutry</option>
                                        <?php foreach($country as $countryval){?>
                                            <option value="{{$countryval->code}}" <?php if($countryval->id == 230){echo 'selected';}?>>{{$countryval->name}} ({{$countryval->code}})</option>
                                        <?php }?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Site Notes</label>
                                    <textarea class="form-control textareaInput" placeholder="Enter Site notes" rows="3" id="customer_site_note" name="site_notes"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="inputName">Sage Ref.</label>
                                    <input type="text" class="form-control editInput textareaInput" placeholder="Enter Sage Ref." id="customer_sage_ref" name="saga_ref">
                                </div>
                                <div class="mb-3">
                                    <label>Assign Products</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="customer_yes" checked>
                                        <label class="form-check-label checkboxtext" for="inlineRadio1">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="custoemr_no">
                                        <label class="form-check-label checkboxtext" for="inlineRadio2">No</label>
                                    </div>
                                    <input type="hidden" id="customer_assigned_product" name="assigned_product" value="1">
                                </div>
                                <div class="mb-3">
                                    <label>Notes</label>
                                    <textarea class="form-control textareaInput" placeholder="Enter Customer Notes" rows="3" id="customer_note" name="notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="save_customer()">Save</button>
                <!-- <button type="button" class="profileDrop" onclick="save_customerClose()">Save & Close</button> -->
            </div>
        </div>
    </div>
</div>

<script>
    function customer_check_email() {
        var email = $('#customer_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1) {
            $('#customeremailErr').text("Please enter correct email address");
            return false;
        } else {
            $('#customeremailErr').text("");
        }
    }

    function customervalidateDecimal(input) {
        input.value = input.value.replace(/[^0-9.]/g, '');
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.substring(0, input.value.lastIndexOf('.'));
        }
        if (input.value.length > 8) {
            input.value = input.value.substring(0, 8);
        }
    }

    function save_customer() {
        var assigned_product = 0;
        if ($("#customer_yes").is(':checked')) {
            assigned_product = 1;
        }
        $("#customer_assigned_product").val(assigned_product);
        var emailErr = $("#customeremailErr").text();
        $('.CustomercheckError').each(function() {
            if ($(this).val() === '') {
                alert($(this).val())
                $(this).css('border', '1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border', '');
            }
        });
        var customer_phone = $("#customer_phone").val();
        var customer_mobile = $("#customer_mobile").val();
        if (customer_phone != '' && customer_phone.length != 10) {
            $("#CheckcustomerTelephoneErr").show();
            return false;
        } else if (customer_mobile != '' && customer_mobile.length != 10) {
            $("#CheckcustomerTelephoneErr").hide();
            $("#CheckcustomerMobileErr").show();
            return false;
        }
        if (emailErr.length > 0) {
            $("#CheckcustomerTelephoneErr").hide();
            $("#CheckcustomerMobileErr").hide();
            $('#supplier_email').focus();
            return false;
        } else {
            $("#CheckcustomerTelephoneErr").hide();
            $("#CheckcustomerMobileErr").hide();
            $.ajax({
                type: "POST",
                url: "{{url('/customer_add_edit_save')}}",
                data: new FormData($("#AddCustomerModal")[0]),
                async: false,
                contentType: false,
                cache: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (isAuthenticated(response) == false) {
                        return false;
                    }
                    if (response.vali_error) {
                        alert(response.vali_error);
                        $(window).scrollTop(0);
                        return false;
                    } else {
                        $(window).scrollTop(0);
                        $('#alert_message_customer').text("Customer Added Succesfully Done!").show();
                        setTimeout(function() {
                            $('#alert_message_customer').text('').hide();
                            getAllCusomer(response);
                            $("#customerPop").modal('hide');
                        }, 3000);
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