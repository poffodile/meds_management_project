<style>
    .contactsProfile {
        background: #f1f1f1;
        padding: 20px;
    }

    .contactsInformation h2 {
        font-size: 20px;
        font-weight: 600;
        color: #000000;
        margin: 0px;
        padding: 0 20px;
        text-transform: uppercase;
    }

    .contactsDetails {
        padding: 20px;
        color: #000000;
    }

    .personalprofile {
        background-color: #fafafa;
        padding: 2.5rem 0 1.5rem 0;
        height: 100%;
    }

    .personalprofile img {
        width: 12rem;
        height: 12rem;
        border: 0.2rem solid #dddddd;
        border-radius: 50%;
        display: block;
        margin: auto;
    }

    .personalprofile .name {
        text-align: center;
        margin-top: 1.3rem;
        color: #070707;
        font-size: 24px;
        font-weight: bold;
    }

    .personalprofile .country {
        text-align: center;
        font-size: 1.3rem;
        margin: 0.2rem;
        color: #000000;
    }

    .personalprofile .proFilesocial {
        text-align: center;
        color: #000000;
    }

    .personalprofile .proFilesocial a i {
        font-size: 3rem;
        margin: 1rem 0.4rem;
    }

    .personalprofile .proFilesocial a i.fa-facebook {
        color: #006fdd;
    }

    .personalprofile .proFilesocial a i.fa-twitter {
        color: #60B8FF;
    }

    .personalprofile .proFilesocial a i.fa-instagram {
        color: #ff2ca0;
    }

    .personalprofile .proFilesocial a i:hover {
        color: #000000;
    }

    .profileskills {
        background-color: #f5f2f2;
        padding: 1.6rem 1rem;
        margin-top: 20px;
    }
</style>


<div id="profile_settings" class="tab-pane active">
    <div id="settings" class="tab-pane  active">
        <div class="position-center">
            <form role="form" class="form-horizontal" action="{{ url('/my-profile/edit') }}" enctype="multipart/form-data" method="post" id="edit_my_profile">

                <div class="row">

                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                        <div class="personalprofile">

                            <?php
                            $image = asset(userProfileImagePath . '/default_user.jpg');
                            if (!empty($manager_profile->image)) {
                                $image = asset(userProfileImagePath . '/' . $manager_profile->image);
                            }
                            ?>

                            <div class="avatar_upload">
                                <div class="avatar-edit">
                                    <input type='file' id="imageUpload" name="image" accept=".png, .jpg, .jpeg" />
                                    <label for="imageUpload"></label>
                                </div>
                                <div class="avatar-preview">
                                    <div id="imagePreview" style="background-image: url('{{ $image }}');">
                                    </div>
                                </div>
                            </div>
                            <!-- <a href="#!"><img src="http://localhost/socialcareitsolution/public/images/userProfileImages/1775716684.jpg" alt="Michaelprinceojoajogwu" /></a> -->
                            <h1 class="name">{{ $manager_profile->name }}</h1>
                            <p class="country">{{ $manager_profile->job_title }}</p>

                            <div class="proFilesocial">
                                <a href="{{$facebook_slug}}" title="Share on Facebook"><i class="fa fa-facebook"></i></a>
                                <a href="{{$twitter_slug}}" title="Share on Twitter"><i class="fa fa-twitter"></i></a>
                                <a href="{{$instagram_slug}}" title="Share on Instagram"><i class="fa fa-instagram"></i></a>
                            </div>

                            <div class="profileskills">

                                <div class="contactsInformation">
                                    <h2> Contacts Information</h2>
                                </div>
                                <div class="contactsDetails">
                                    <p><b>Phone:</b> {{ $manager_profile->phone_no }}</p>
                                    <p><b>Email:</b> {{ $manager_profile->email }}</p>
                                    <p><b>Current Location:</b> {{ $manager_profile->current_location }}</p>
                                </div>
                            </div>


                            <div id="my_profile_info" class="tab-pane">
                                <div class="position-center">
                                    <div class="prf-contacts sttng stf-details">

                                        <h2 class="accordion-header">Personal Information</h2>
                                        <div class="accordion-content full-info persnl-detail">
                                            {!! $manager_profile->personal_info !!}
                                        </div>

                                        <h2 class="accordion-header">Banking Information</h2>
                                        <div class="accordion-content full-info">
                                            {!! $manager_profile->banking_info !!}
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">

                        <div class="prf-contacts sttng">
                            <h2> Profile Info</h2>
                        </div>

                        <!-- <div class="form-group">
                            <label class="col-lg-2 control-label">Image</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <input type="file" id="my_profile_img" name="image" val="">
                            </div>
                        </div> -->
                        <div class="row form-group">
                            <label class="col-lg-2 control-label">Name</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input name="name" placeholder="" id="name" class="form-control" type="text" maxlength="255" value="{{ $manager_profile->name }}" required="">
                            </div>

                            <label class="col-lg-2 control-label pe-0">Job Title</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input name="job_title" placeholder="" id="name" class="form-control" type="text" maxlength="255" value="{{ $manager_profile->job_title }}" readonly="">
                            </div>
                        </div>
                        <div class="row form-group">
                            <label class="col-lg-2 control-label">Payroll</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input name="payroll" placeholder="" id="name" class="form-control" type="text" maxlength="255" value="{{ $manager_profile->payroll }}" readonly="">
                            </div>

                            <label class="col-lg-2 control-label pe-0">Holiday Entitlement</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input name="holiday_entitlement" placeholder="" id="name" class="form-control" type="text" maxlength="255" value="{{ $manager_profile->holiday_entitlement }}" readonly="">
                            </div>
                        </div>

                        <div class="row form-group">
                            <label class="col-lg-2 control-label">Description</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <textarea rows="6" class="form-control" id="" name="description" maxlength="1000" required="">{{ $manager_profile->description }}</textarea>
                            </div>
                        </div>

                        <div class="prf-contacts sttng">
                            <h2> A/C Credentials </h2>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Username</label>
                            <div class="col-md-4 col-sm-10 col-xs-12">
                                <input name="user_name" placeholder="" id="name" class="form-control" type="text" maxlength="255" value="{{ $manager_profile->user_name }}" readonly="">
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <a data-toggle="modal" href="#changePasswordModal" class="clr-blue chnge_passwrd_btn" style='font-size:15px'><button class="btn allBtnUseColor" type="submit">Change Password</button></a>
                            </div>
                        </div>

                        <div class="prf-contacts sttng">
                            <h2>Contact</h2>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Phone</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input placeholder=" " name="phone_no" class="form-control" type="text" value="{{ $manager_profile->phone_no }}" required="">
                            </div>
                            <label class="col-lg-2 control-label">Email</label>
                            <div class="col-md-4 col-sm-4 col-xs-12">
                                <input name="email" id="email" class="form-control" type="text" value="{{ $manager_profile->email }}" required="">
                            </div>
                        </div>

                        <?php

                        $manager_profile->current_location      = preg_replace('#<br\s*/?>#i', "", $manager_profile->current_location);
                        $manager_profile->personal_info         = preg_replace('#<br\s*/?>#i', "", $manager_profile->personal_info);
                        $manager_profile->banking_info          = preg_replace('#<br\s*/?>#i', "", $manager_profile->banking_info);

                        ?>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Current Location</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <textarea name="current_location" class="form-control" placeholder="Current location" rows="4" maxlength="2000" required="">{{ $manager_profile->current_location }}</textarea>
                            </div>
                        </div>

                        <div class="prf-contacts sttng">
                            <h2>More Info</h2>
                        </div>
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Personal Info</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <textarea rows="6" class="form-control" id="" name="personal_info" maxlength="1000" placeholder="Personal Information">{{ $manager_profile->personal_info }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-lg-2 control-label">Banking Info</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <textarea rows="5" class="form-control" id="" name="banking_info" maxlength="1000" placeholder="Banking Information">{{ $manager_profile->banking_info }}</textarea>
                            </div>
                        </div>
                        @if(!$my_qualification->isEmpty())
                        <div class="form-group">
                            <label class="col-lg-2 control-label">Qualification info</label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
                                <div class="input_fields_wrap row cus-from-group">
                                    @foreach($my_qualification as $qualification)
                                    <div class="form-group col-lg-12 col-md-12 col-sm-10 col-xs-12 p-0 rem-cert" rel="{{$qualification->id}}">
                                        <div class="col-md-9 col-sm-9 col-xs-12 p-0">
                                            <input name="" class="form-control" type="text" value="{{ $qualification->name }}" readonly="">
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12 p-t-5">
                                            <a class="image" target="blank" href="{{ asset(userQualificationImgPath.'/'.$qualification->image) }}">View Image</a>
                                        </div>
                                    </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <button class="btn allBtnUseColor" type="submit">Save</button>
                                <input type="hidden" name="manager_id" value="{{ $manager_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <a href="#"><button class="btn btn-default" type="button">Cancel</button></a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>


    <script>
        $(document).ready(function() {
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#staff_img_old').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#my_profile_img").change(function() {

                var img_name = $(this).val();

                if (img_name != "" && img_name != null) {
                    var img_arr = img_name.split('.');
                    var ext = img_arr.pop();
                    ext = ext.toLowerCase();

                    if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
                        if (this.files[0].size > 2097152 || this.files[0].size < 10240) {
                            $(this).val('');
                            $("#my_profile_img").removeAttr("src");
                            alert("image size should be at least 10KB and upto 2MB");
                            return false;
                        }
                    } else {
                        $(this).val('');
                        alert('Please select an image .jpg, .png, .gif file format type.');
                    }
                }
                return true;
            });
        });
    </script>

    <script>
        $(function() {
            $("#edit_my_profile").validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                    name: {
                        required: true,
                        regex: /^[a-zA-Z'.\s]{1,40}$/
                    },
                    phone_no: {
                        required: true,
                        regex: /^[0-9\s]{10,13}/
                    },
                    current_location: "required",
                    description: "required",
                },
                messages: {
                    name: {
                        required: "This field is required.",
                        regex: "This Field should contain alphabetss only."
                    },
                    email: {
                        required: "This field is required.",
                        regex: "This Email is not valid."
                    },
                    phone_no: {
                        required: "This field is required.",
                        regex: "Only numerical value allowed."
                    },
                    current_location: "This field is required.",
                    description: "This field is required.",
                },
                submitHandler: function(form) {
                    form.submit();
                }
            })
            return false;
        });
    </script>

    <script>
        $(document).ready(function() {
            $(document).on('change', '.qual_upload', function() {
                var img_name = $(this).val();
                if (img_name != "" && img_name != null) {
                    var img_arr = img_name.split('.');
                    var ext = img_arr.pop();
                    ext = ext.toLowerCase();
                    if (ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'pdf' || ext == 'doc' || ext == 'docx') {
                        if (this.files[0].size > 2097152 || this.files[0].size < 10240) {
                            $(this).val('');
                            $(".qual_upload").removeAttr("src");
                            alert("file size should be at least 10KB and upto 2MB");
                            return false;
                        }
                    } else {
                        $(this).val('');
                        alert('Please select an image .jpg, .png, .pdf, .doc, .docx, .jpeg file format type.');
                    }
                }
                return true;
            });

        });
    </script>

    <script>
        // upload Profile Image Js
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').css('background-image', 'url(' + e.target.result + ')');
                    $('#imagePreview').hide();
                    $('#imagePreview').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $("#imageUpload").change(function() {
            var img_name = $(this).val();
            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();

                if (ext == "jpg" || ext == "jpeg" || ext == "png") {
                    if (this.files[0].size > 2097152 || this.files[0].size < 10240) {
                        $(this).val('');
                        alert("image size should be at least 10KB and upto 2MB");
                        return false;
                    }
                } else {
                    $(this).val('');
                    alert('Please select an image .jpg, .png, .jpeg file format type.');
                    return false;
                }
            }
            readURL(this);
        });
    </script>

    <!-- Accordion js -->
    <script>
        $(document).ready(function() {
            $('.stf-details .full-info').hide();
            $('.stf-details .persnl-detail').show();
            $('.stf-details .accordion-header').click(function() {
                let content = $(this).next('.full-info');

                $('.stf-details .full-info').hide();
                if (content.length) {
                    content.show();
                } else {
                    console.log('Next element not found'); // debug
                }
            });
        });
    </script>