<div id="contacts" class="tab-pane">
    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div class="prf-contacts">
                <h2> <span><i class="fa fa-map-marker"></i></span> Current location <a href="javascript:void(0)" class="location-edit-btn" clmn-name="current_location"><i class="fa fa-pencil profile"></i> </a> </h2>
                <div class="location-info current_location"><p>{!! $patient->current_location !!}</p></div>

                <div class="location-info ">    
                    <strong style="color:#3399CC;">Previous Location</strong><br>
                    <div class="previous_location"><p>{!! $patient->previous_location !!}</p></div>
                    
                </div>
                <h2> <span><i class="fa fa-phone"></i></span> contacts <a href="javascript:void(0)" class="contact-edit-btn" phone_no="{{ $patient->phone_no }}" mobile="{{ $patient->mobile }}" email="{{ $patient->email }}"><i class="fa fa-pencil profile"></i></a> </h2>
                <div class="location-info">
                    <p>
                        <strong style="color:#3399CC; display:inline-block; margin-bottom: 10px;">Phone</strong> : {!! $patient->phone_no !!} <br>
                        <strong style="color:#3399CC; display:inline-block; margin-bottom: 10px;">Mobile</strong> : {!! $patient->mobile !!}<br>
                        <strong style="color:#3399CC; display:inline-block; margin-bottom: 10px;">Email</strong> : {!! $patient->email !!}<br>

                <?php foreach($social_app as $key => $value) {
                    $app_name      = $value['name'];
                    $social_app_id = $value['id'];

                    $field_id = (isset($social_app_val[$social_app_id]['id'])) ? $social_app_val[$social_app_id]['id'] : '';
                    $field_value = (isset($social_app_val[$social_app_id]['value'])) ? $social_app_val[$social_app_id]['value'] : '';
                    ?>
                        
                        <strong style="color:#3399CC; display:inline-block; margin-bottom: 10px;">{{ $app_name }}</strong> : {{ $field_value }} <br>
                        
                <?php } ?>                                             
                    </p>
                </div>
            </div>
            <?php
                foreach ($su_contact as $contact) {
                    $image = contactsPath.'/default_user.jpg';
                    if(!empty($contact->image)){
                        $image = contactsPath.'/'.$contact->image;
                    }
            ?>
            <div class=" wk-progress tm-membr contct-list">
                <div class="tm-avatar">
                    <a href="#" class="" data-toggle="modal" data-target="#contact_us_View_{{ $contact->id }}"><img src="{{ $image }}" alt=""></img>
                    </a>
                </div>
                <div class="col-md-7 col-xs-7 p-0">
                    <a href="#" class="" data-toggle="modal" data-target="#contact_us_View_{{ $contact->id }}"><span class="tm">{{ ucfirst($contact->name) }}</span></a>
                    <p>{{ ucfirst($contact->job_title_id) }}</p>
                    <!-- <p>{{ $contact->phone_no }}</p> -->
                    <!-- <p></p> -->
                </div>
            </div>
            <?php } ?>

        </div>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <div id="map-canvas"></div>
        </div>
    </div>
</div>

<!-- Current Location -->
<div class="modal fade" id="location_info_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Location Information</h4>
            </div>
            <div class="modal-body">
                <div class="row">
            <form method="post" action="{{ url('/service/user/edit-location-details') }}" enctype="multipart/form-data" id='edit_location_info'>

                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group p-0 col-md-12 col-sm-12 col-xs-12 add-rcrd">
                            <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 location-info-label">Current Location</label>
                            <div class="col-md-9 col-sm-9 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <textarea name="current_location" required class="form-control edit_current_location" rows="5" maxlength="1000" id="curr_loc_info"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group p-0 col-md-12 col-sm-12 col-xs-12 add-rcrd">
                            <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 location-info-label">Previous Location</label>
                            <div class="col-md-9 col-sm-9 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <textarea name="previous_location" class="form-control edit_previous_location" rows="5" maxlength="1000" id="prev_loc_info"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer modal-bttm m-t-0">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                        <button class="btn btn-default cancel-btn" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <button class="btn btn-warning" type="submit"> Confirm </button>
                    </div>
                </div>
            </form>
            </div>           
        </div>
    </div>
</div>

<!--Edit Contacts popup -->
<div class="modal fade in" id="contact_info_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Edit Contact Details</h4>
            </div>
            <div class="modal-body">
                <div class="row">         
            <form method="post" action="{{ url('/service/user/edit-contact-details') }}" id='edit_contact_info'>

                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Phone :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="text" class="form-control edit-contact-phone" name="phone_no" required value="" maxlength="255" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Mobile :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="text" class="form-control edit-contact-mobile" name="mobile" value="" maxlength="255" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Email :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="email" class="form-control edit-contact-email" name="email" required value="" maxlength="255" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php foreach($social_app as $key => $value) {
                    $app_name      = $value['name'];
                    $social_app_id = $value['id'];

                    $field_id = (isset($social_app_val[$social_app_id]['id'])) ? $social_app_val[$social_app_id]['id'] : '';
                    $field_value = (isset($social_app_val[$social_app_id]['value'])) ? $social_app_val[$social_app_id]['value'] : '';
                    ?>
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">{{ $app_name }}:</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input name="social_app[{{ $key }}][value]" type="text" class="form-control" value="{{ $field_value }}" maxlength="255">
                                    <input name="social_app[{{ $key }}][social_app_id]" type="hidden" value="{{ $social_app_id }}">
                                    <input name="social_app[{{ $key }}][su_app_id]" type="hidden" value="{{ $field_id }}">
                                </div>
                            </div>
                        </div>
                    </div>                        
                    <?php } ?> 

                   <!--  <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Skype :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="text" class="form-control edit-contact-skype" name="skype" value="" maxlength="255"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Facebook :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="url" class="form-control edit-contact-facebook" name="facebook" value="" maxlength="255"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7"> Twitter :</label>
                            <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                <div class="input-group popovr">
                                    <input type="url" class="form-control edit-contact-twitter" name="twitter" value="" maxlength="255s" />
                                </div>
                            </div>
                        </div>
                    </div>
 -->
                    <div class="modal-footer modal-bttm m-t-0">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="service_user_id" value="{{ $service_user_id }}">
                        <button class="btn btn-default cancel-btn" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                        <button class="btn btn-warning" type="submit"> Confirm </button>
                    </div>
                </div>
            </form>
            </div>           
        </div>
    </div>
</div>


<?php    
    foreach ($su_contact as $contact) { 
        $image1 = contactsPath.'/default_user.jpg';
        if(!empty($contact->image)) {
            $image1 = contactsPath.'/'.$contact->image;
    }
?>

<!--View Contact Us popup -->
<div class="modal fade" id="contact_us_View_{{ $contact->id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-btn-edit" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a href="" style="font-size:18px; padding-right:8px;" class="close edit_contact_form"><i class="fa fa-pencil" title="Edit"></i></a>
                <a href="{{ url('/service/user/contact-us/delete/'.$contact->id) }}" style="font-size:18px; padding-right:8px;" class="close delete_contact_btn" ><i class="fa fa-trash" title="Delete"></i></a>
                <h4 class="modal-title">Contact Us</h4>
            </div>
            <div class="modal-body">
                <div class="row">         
                    <form method="post" action="{{ url('/service/user/contact-us/edit') }}" enctype="multipart/form-data" id='edit_contact_us_validation'>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Name :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" name="contact_name" class="form-control edit_contact" disabled="disabled" value="{{ $contact->name }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7  r-p-0"> Job Title </label>
                                <div class="col-md-9 col-sm-10 col-xs-12 r-p-0 p-b-5">
                                    <div class="input-group popovr">
                                        <input type="text" name="contact_job_title" class="form-control edit_contact" disabled="disabled" value="{{ $contact->job_title_id }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Phone No.</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" name="contact_phone_no" class="form-control edit_contact" disabled="disabled" value="{{ $contact->phone_no }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Email :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="input-group popovr">
                                        <input type="text" class="form-control edit_contact" name="contact_email" disabled="disabled" value="{{ $contact->email }}" maxlength="255" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Address :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0 ">
                                    <div class="input-group popovr">
                                        <textarea name="contact_address" class="form-control edit_contact" disabled="disabled" rows="3" cols="20" maxlength="1000">{{ $contact->address }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>     

                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">         
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7">Image :</label>
                                <div class="col-md-9 col-sm-11 col-xs-12 r-p-0">
                                    <div class="fileupload fileupload-new input-group popovr" data-provides="fileupload">
                                        <div class="fileupload-new thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                            <img src="{{ $image1 }}" alt="No Image" class="temp_img" id="old_image1"/>
                                        </div>
                                        <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                        </div>
                                        <div class="btn-file edit-contact-image" style="display:none;">
                                            <span class="btn btn-white ">
                                            <span class="fileupload-new"><i class="fa fa-paper-clip"></i> Select image</span>
                                            <span class="fileupload-exists"><i class="fa fa-undo"></i> Change</span>
                                            </span>
                                            <input name="contact_image" type="file" class="default" id="contact_img_upload">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer modal-bttm m-t-0 m-b-10" style="visibility:hidden">
                            <input type="hidden" name="contact_us_id" value="{{ $contact->id }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="btn btn-default cancel-btn-edit" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>
                            <button class="btn btn-warning" type="submit"> Submit </button>
                        </div>
                    </form>
                </div>
            </div>           
        </div>
    </div>
</div>
<?php } ?>

<script>
    $(document).ready(function(){
        $('.location-edit-btn').click(function() {
            var current_location = $('.current_location').text();
            var previous_location = $('.previous_location').text();

            $('.edit_current_location').val(current_location);
            $('.edit_previous_location').val(previous_location);
            $('#location_info_model').modal('show');
            setTimeout(function () {
                var elmnt = document.getElementById("prev_loc_info");
                var scroll_height = elmnt.scrollHeight;
                console.log(scroll_height);
                $('#prev_loc_info').height(scroll_height);
            },200);
            setTimeout(function () {
                var elmnt = document.getElementById("curr_loc_info");
                var scroll_height = elmnt.scrollHeight;
                console.log(scroll_height);
                $('#curr_loc_info').height(scroll_height);
            },200);
        });
    });
</script>

<script>
    $(document).ready(function(){
        $('.contact-edit-btn').click(function()
        {   
            var phone_no = $(this).attr('phone_no');
            var mobile = $(this).attr('mobile');
            var email = $(this).attr('email');
          /*  var skype = $(this).attr('skype');
            var facebook = $(this).attr('facebook');
            var twitter = $(this).attr('twitter');
*/
            $('.edit-contact-phone').val(phone_no);
            $('.edit-contact-mobile').val(mobile);
            $('.edit-contact-email').val(email);
           /* $('.edit-contact-skype').val(skype);
            $('.edit-contact-facebook').val(facebook);
            $('.edit-contact-twitter').val(twitter);*/
            $('#contact_info_model').modal('show');
        });
    });
</script>

<script>
    $(function() {
    
        $("#edit_location_info").validate({
            rules: {
                current_location: {
                    required: true,
                    regex: /^[a-zA-Z0-9'"-=+,.&# \n]{1,200}$/
                },
                previous_location: {
                    //required: true,
                    regex: /^[a-zA-Z0-9'"-=+,.&# \n]{1,200}$/
                },
            },
            messages: {
                current_location: {
                    required: "This field is required.",
                    regex: "Special charaters are not allowed."
                },
                previous_location: {
                    //required: "This field is required.",
                    regex: "Special charaters are not allowed."
                }
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;   
    });
</script>

<script>
    $(function() {
        $("#edit_contact_info").validate({
            rules: {
                phone_no: {
                    required: true,
                    regex: /^[0-9 +]{8,15}$/
                },
                mobile: {
                    //required: true,
                    regex: /^[0-9 +]{10,15}$/
                },
            },
            messages: {
                 phone_no: {
                    //required: "This field is required.",
                    regex: "This field must contain 8 to 15 digits"
                },
                mobile: {
                    required: "This field is required.",
                    regex: "This field must contain 10 to 15 digits"
                }
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;   
    });
</script>


<script>
    //making editable 
    $(document).ready(function() {
       $(document).on('click','.edit_contact_form', function(){

        $(this).parent().next('div').find('.modal-footer').removeAttr('style');
        $(this).parent().next('div').find('.edit_contact').removeAttr('disabled');
      //  $('#select-j-title').removeAttr('disabled');
        $('.edit-contact-image').css('display','block');

        return false;
       });
    });
</script>

<script>
    $(document).ready(function(){
        
        $("#contact_img_upload").change(function()
        {   
            var img_name = $(this).val();
            if(img_name != "" && img_name!=null)
            {
                var img_arr=img_name.split('.');
                var ext = img_arr.pop();
                ext     = ext.toLowerCase();
                if(ext =="jpg" || ext =="jpeg" || ext =="gif" || ext =="png")
                {
                    input=document.getElementById('contact_img_upload');
                    if(input.files[0].size > 2097152 || input.files[0].size <  10240)
                    {
                      $(this).val('');
                      $("#contact_img_upload").removeAttr("src");
                      alert("image size should be at least 10KB and upto 2MB");
                      return false;
                    }
                 }
               else
                {
                    $(this).val('');
                    alert('Please select an image .jpg, .png, .gif file format type.');
                }
            }
        return true;
        });

        //delete contact
        $(document).ready(function(){
            $('.delete_contact_btn').click(function(){
               if(confirm('Do you want to delete this contact ?')){
                //window.location="{{ url('/service/care_team/delete/') }}"+'/'+care_team_id;
                }
                else{
                    return false;
                }

            });
        });
    });
</script>

<script>
    $(function(){
        $('#edit_contact_us_validation').validate({
            rules: {
                contact_name : {
                    required : true,  
                    regex: /^[a-zA-Z0-9\s]{1,40}$/ 
                },
                contact_job_title : {
                    required : true,  
                    regex: /^[a-zA-Z0-9\s]{1,40}$/  
                },
                contact_phone_no : {
                    required: true,
                    regex: /^[0-9+\s]{8,13}$/
                },
                contact_address :{ 
                    required: true,
                    regex: /^[a-zA-Z0-9'#-,.\s]{1,100}$/
                }
                // ,
                // contact_email : {
                //     required: true,
                //     email: true
                // }
            },
            messages: {
                contact_name : {
                    required: "This field is required",
                    regex: "Invalid character."
                },
                contact_job_title : {
                    required: "This field is required",
                    regex: "Invalid character."
                },
                contact_phone_no : {
                    required: "This field is required",
                    regex: "Minimum intergers 8."
                },
                phone_no:{
                    required: "This field is required.",
                    regex: "Phone number is invalid."
                },
                contact_address : {
                    required: "This field is required",
                    regex: "Invalid character."
                }
                // ,
                // email: "This field is required."
            },
            submitHandler: function(form) {
              form.submit();
            }
        });
        return false;
    });
</script>