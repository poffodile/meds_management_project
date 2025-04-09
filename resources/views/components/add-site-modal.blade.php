<div class="modal fade" id="site_modal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content add_Customer">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Site Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="alert alert-success mt-3" id="alert_message_site" style="display:none"></div>
            <div class="modal-body">
                <form id="site_form">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                
                                    @csrf
                                    <div class="mb-2 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Customer</label>
                                        <div class="col-sm-9">
                                            <p id="site_customer_name"><?php if(!empty($contact_name)){echo $contact_name->contact_name;}?></p>
                                        </div>
                                    </div>
                                    <input type="hidden" id="site_customer_id" name="customer_id" value="<?php if(!empty($contact_name) && $contact_name !=''){echo $contact_name->id;}?>">
                                
                                    <div class="mb-2 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Site Name <span class="radStar ">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput SitecheckError" id="site_name" name="site_name" placeholder="Enter Site Name">
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Contact Name <span class="radStar ">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput SitecheckError" placeholder="Enter Contact Name" name="contact_name" id="site_contact_name">
                                        </div>
                                    </div>

                                    <div class="mb-2 row">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Job Title (Position)</label>
                                        <div class="col-sm-4">
                                            <select class="form-control editInput selectOptions get_job_title_result" id="site_job_titile_id" name="title_id">
                                                <option selected disabled>Please Select </option>
                                                <?php foreach($job_title as $site_val_title){?>
                                                    <option value="{{$site_val_title->id}}">{{$site_val_title->name}}</option>
                                                    <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('site_job_titile_id')">
                                                <i class="fa-solid fa-square-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputName" class="col-sm-3 col-form-label">Company Name</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Company Name" name="company_name" id="site_company_name">
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputEmail" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Email" name="email" id="site_email" onchange="site_check_email()">
                                            <span id="siteemailErr" style="color:red"></span>
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="customer_phone" class="col-sm-3 col-form-label">Telephone</label>
                                            <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="site_telephone_country_code" name="telephone_country_code">
                                                <option selected disabled>Please Select</option>
                                                @foreach($country as $siteteleCode)
                                                    <option value="{{$siteteleCode->id}}" >+{{$siteteleCode->code}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" id="site_phone" placeholder="Enter Telephone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                <span style="color:red;display:none" id="ChecksiteTelephoneErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="customer_mobile" class="col-sm-3 col-form-label">Mobile</label>
                                            <div class="col-sm-2">
                                            <select class="form-control editInput selectOptions" id="site_mobile_country_code" name="mobile_country_code">
                                                <option selected disabled>Please Select</option>
                                                @foreach($country as $sitemobCode)
                                                    <option value="{{$sitemobCode->id}}" >+{{$sitemobCode->code}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control editInput textareaInput" id="site_mobile" placeholder="Enter Customer Mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                                <span style="color:red;display:none" id="ChecksiteMobileErr">Please enter 10 digit number</span>
                                        </div>
                                    </div>

                                    <div class="mb-2 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Fax</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site Fax" id="site_fax" name="fax">
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="formDtail">
                                    <div class="mb-2 row">
                                        <label for="inputProject" class="col-sm-3 col-form-label">Region</label>
                                        <div class="col-sm-7">
                                            <select class="form-control editInput selectOptions get_region_result" id="site_region" name="region">
                                                <option selected disabled>None</option>
                                                <?php foreach($region as $siteregion_val){?>
                                                    <option value="{{$siteregion_val->id}}">{{$siteregion_val->title}}</option>
                                                <?php }?>
                                            </select>
                                        </div>
                                        <div class="col-sm-2">
                                            <a href="javascript:void(0)" class="formicon" id="openPopupButton" onclick="openRegionModal('site_region')">
                                                <i class="fa-solid fa-square-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputAddress" class="col-sm-3 col-form-label">Address <span class="radStar ">*</span></label>
                                        <div class="col-sm-9">
                                            <textarea class="form-control textareaInput SitecheckError" placeholder="Enter Site Address" id="site_address" name="address" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-2 row">
                                        <label for="inputCity" class="col-sm-3 col-form-label">City</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site City" id="site_city" name="city">
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputCounty" class="col-sm-3 col-form-label">County</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site County" name="country" id="site_country_input">
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputPincode" class="col-sm-3 col-form-label">Postcode</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site Pincode" name="post_code" id="site_pincode">
                                        </div>
                                    </div>

                                    <div class="mb-2 row">
                                        <label for="inputCountry" class="col-sm-3 col-form-label">Country</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="site_modal_country_id" name="country_id">
                                                <option selected disabled>Select Coutry</option>
                                                <?php foreach($country as $sitecountryval){?>
                                                <option value="{{$sitecountryval->id}}" class="site_modal_country_id" <?php if($sitecountryval->id == 230){echo 'selected';}?>>{{$sitecountryval->name}} ({{$sitecountryval->code}})</option>
                                                <?php }?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2 row">
                                        <label for="inputCountry" class="col-sm-3 col-form-label">Default Catalogue</label>
                                        <div class="col-sm-9">
                                            <select class="form-control editInput selectOptions" id="site_catalogue_id" name="catalogue">
                                                <option selected disabled>None</option>
                                                <option value="ABCD">ABCD</option>
                                            </select>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="mb-2 notes_input">
                                <label for="inputCountry" class="col-sm-3 col-form-label">Notes</label>
                                <textarea name="notes" placeholder="Enter Site Notes" id="site_notes" class="form-control textareaInput" rows="3" cols="15"></textarea>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">

                <button type="button" class="profileDrop" onclick="save_site()">Save</button>
                <!-- <button type="button" class="profileDrop" onclick="save_siteClose()">Save &
                    Close</button> -->
                <button type="button" class="profileDrop" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    function site_check_email(){
        var email= $('#site_email').val();
        validRegExp = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if (email.search(validRegExp) == -1){
            $('#siteemailErr').text("Please enter correct email address");
            return false;
        }else{
            $('#siteemailErr').text("");
        }
  }
    function save_site(){
        var siteemailErr=$("#siteemailErr").text();
        var site_phone=$("#site_phone").val();
        var site_mobile=$("#site_mobile").val();
        $('.SitecheckError').each(function() {
            if ($(this).val() === '' || $(this).val() == null) {
                $(this).css('border','1px solid red');
                $(this).focus();
                return false;
            } else {
                $(this).css('border','');
            }
        });
        if(site_phone !='' && site_phone.length !=10){
            $('#ChecksiteTelephoneErr').show();
            $('#ChecksiteMobileErr').hide();
            return false;
        }else if(site_mobile !='' && site_mobile.length !=10){
            $('#ChecksiteTelephoneErr').hide();
            $('#ChecksiteMobileErr').show();
            return false;
        }else if(siteemailErr.length>0){
            $("#site_email").css('border','1px solid red');
            $('#ChecksiteTelephoneErr').hide();
            $('#ChecksiteMobileErr').hide();
            return false;
        }else{
            $('#ChecksiteTelephoneErr').hide();
            $('#ChecksiteMobileErr').hide();
            $("#site_email").css('border','');
            $.ajax({
                type: "POST",
                url: "{{url('/site_save')}}",
                data: new FormData($("#site_form")[0]),
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
                    }else if($.trim(response)=="error"){
                        alert("Something went wrong. Please try later!");
                    }else{
                        $(window).scrollTop(0);
                        $('#alert_message_site').text("Site Added Succesfully Done!").show();
                        setTimeout(function() {
                            $('#alert_message_site').text('').hide();
                            getAllsite(response);
                            $("#site_modal").modal('hide');
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