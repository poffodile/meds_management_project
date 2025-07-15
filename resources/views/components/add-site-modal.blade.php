<div class="modal fade" id="site_modal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="customerModalLabel">Add Site Address</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-success" id="alert_message_site" style="display:none"></div>
                <form id="site_form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Customer</label>
                                <p id="site_customer_name"><?php if(!empty($contact_name)){echo $contact_name->contact_name;}?></p>
                                <input type="hidden" id="site_customer_id" name="customer_id" value="<?php if(!empty($contact_name) && $contact_name !=''){echo $contact_name->id;}?>">
                            </div>
                            <div class="mb-3">
                                <label>Site Name <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput textareaInput SitecheckError" id="site_name" name="site_name" placeholder="Enter Site Name">
                            </div>
                            <div class="mb-3">
                                <label>Contact Name <span class="radStar ">*</span></label>
                                <input type="text" class="form-control editInput textareaInput SitecheckError" placeholder="Enter Contact Name" name="contact_name" id="site_contact_name">
                            </div>
                            <div class="mb-3">
                                <label>Job Title (Position)</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions get_job_title_result" id="site_job_titile_id" name="title_id">
                                            <option selected disabled>Please Select </option>
                                            <?php foreach($job_title as $site_val_title){?>
                                                <option value="{{$site_val_title->id}}">{{$site_val_title->name}}</option>
                                                <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" onclick="openjobTitleModal('site_job_titile_id')">
                                            <i class="fa fa-plus-square"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Company Name</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Company Name" name="company_name" id="site_company_name">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Email" name="email" id="site_email" onchange="site_check_email()">
                                <span id="siteemailErr" style="color:red"></span>
                            </div>
                            <div class="mb-3">
                                <label>Telephone</label>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <select class="form-control editInput selectOptions" id="site_telephone_country_code" name="telephone_country_code">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $siteteleCode)
                                                <option value="{{$siteteleCode->id}}" >+{{$siteteleCode->code}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" id="site_phone" placeholder="Enter Telephone" name="telephone" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="ChecksiteTelephoneErr">Please enter 10 digit number</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Mobile</label>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <select class="form-control editInput selectOptions" id="site_mobile_country_code" name="mobile_country_code">
                                            <option selected disabled>Please Select</option>
                                            @foreach($country as $sitemobCode)
                                                <option value="{{$sitemobCode->id}}" >+{{$sitemobCode->code}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control editInput textareaInput" id="site_mobile" placeholder="Enter Customer Mobile" name="mobile" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <span style="color:red;display:none" id="ChecksiteMobileErr">Please enter 10 digit number</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Fax</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site Fax" id="site_fax" name="fax">
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-6">
                            <div class="mb-3">
                                <label>Region</label>
                                <div class="row">
                                    <div class="col-sm-10">
                                        <select class="form-control editInput selectOptions get_region_result" id="site_region" name="region">
                                            <option selected disabled>None</option>
                                            <?php foreach($region as $siteregion_val){?>
                                                <option value="{{$siteregion_val->id}}">{{$siteregion_val->title}}</option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <a href="javascript:void(0)" class="formicon" id="openPopupButton" onclick="openRegionModal('site_region')">
                                            <i class="fa fa-plus-square"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Address <span class="radStar ">*</span></label>
                                <textarea class="form-control textareaInput SitecheckError" placeholder="Enter Site Address" id="site_address" name="address" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>City</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site City" id="site_city" name="city">
                            </div>
                            <div class="mb-3">
                                <label>County</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site County" name="country" id="site_country_input">
                            </div>
                            <div class="mb-3">
                                <label>Postcode</label>
                                <input type="text" class="form-control editInput textareaInput" placeholder="Enter Site Pincode" name="post_code" id="site_pincode">
                            </div>
                            <div class="mb-3">
                                <label>Country</label>
                                <select class="form-control editInput selectOptions" id="site_modal_country_id" name="country_id">
                                    <option selected disabled>Select Coutry</option>
                                    <?php foreach($country as $sitecountryval){?>
                                    <option value="{{$sitecountryval->id}}" class="site_modal_country_id" <?php if($sitecountryval->id == 230){echo 'selected';}?>>{{$sitecountryval->name}} ({{$sitecountryval->code}})</option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Default Catalogue</label>
                                <select class="form-control editInput selectOptions" id="site_catalogue_id" name="catalogue">
                                    <option selected disabled>None</option>
                                    <option value="ABCD">ABCD</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Notes</label>
                                <textarea name="notes" placeholder="Enter Site Notes" id="site_notes" class="form-control textareaInput" rows="2" cols="15"></textarea>
                            </div>
                        </div>
                    </div> <!-- End row -->
                </form>
            </div>
            <div class="modal-footer customer_Form_Popup">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="save_site()">Save</button>
                <!-- <button type="button" class="profileDrop" onclick="save_siteClose()">Save &
                    Close</button> -->
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