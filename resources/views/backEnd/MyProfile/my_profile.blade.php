@extends('backEnd.layouts.master')
@section('title',' My Profile')
@section('content')
<!-- script src="//cdn.ckeditor.com/4.5.10/basic/ckeditor.js" -->
<script src="http://localhost/scits/public/frontEnd/js/jquery.validate.js"></script>
 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                <div class="panel-body">
                    <header class="panel-heading">
                        My Profile
                    </header>
                    <div class="clearfix">
                        @include('backEnd.common.alert_messages')
                    </div>
                    
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ url('admin/profile/edit/') }}" id="admin_profile_form" enctype="multipart/form-data">

                                <label>Basic Information</label>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Name</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="name" class="form-control" placeholder="Name" value="{{ (isset($profile->name)) ? $profile->name : '' }}" maxlength="255">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Email</label>
                                    <div class="col-lg-10">
                                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ (isset($profile->email)) ? $profile->email : '' }}" maxlength="255">
                                    </div>
                                </div>

                                <?php
                                    $image = adminImgPath.'/default_user.jpg';
                                        
                                    if(!empty($profile->image)){
                                        $image = adminImgPath.'/'.$profile->image;
                                    }
                                    if(Session::has('scitsAgentSession')){ 
                                        $image = userProfileImagePath.'/'.$profile->image;
                                        // echo "<pre>"; print_r($profile->image); die;
                                    }
                                ?>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label"></label>
                                    <div class="col-md-8">
                                        <img src="{{ $image }}" id="old_image"  alt="No image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Image</label>
                                    <div class="col-lg-10">
                                        <input type="file" id="img_upload" name="image" val="">
                                    </div>
                                </div>
                                @if(!empty($company))
                                <label>Company Information</label>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Company</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" placeholder="Company" name="company" value="{{ $company }}" disabled="">
                                    </div>
                                </div>
                                @endif
                                @if(!empty($home))
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Home</label>
                                    <div class="col-lg-10">
                                        <input type="text" class="form-control" placeholder="Company" value="{{ $home }}" disabled="">
                                    </div>
                                </div>
                                @endif
                                
                                <label>Account Credentials</label>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Username</label>
                                    <div class="col-lg-10">
                                        
                                        <input type="text" name="user_name" class="form-control" disabled placeholder="username" value="{{ (isset($profile->user_name)) ? $profile->user_name : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-2 control-label"></label>
                                    <div class="col-lg-10">
                                        <!-- <a data-toggle="modal" href="#changePasswordModal" class="clr-yellow chnge_passwrd_btn" style='font-size:15px'>Change Password</a> -->
                                        <button type="button" class="btn btn-primary chnge_passwrd_btn" data-toggle="modal" href="#changePasswordModal">Change Password <i class="fa fa-key"></i></button>
                                    </div>
                                </div>   
    							
                                <div class="form-actions">
    								<div class="row">
    									<div class="col-lg-offset-2 col-lg-10">
    										<input type="hidden" name="_token" value="{{ csrf_token() }}">
    										<input type="hidden" name="user_id" value="{{ (isset($profile->id)) ? $profile->id : '' }}">
    										<button type="submit" class="btn btn-primary" name="submit1">Save</button>
    										<a href="{{ url('admin/') }}">
    											<button type="button" class="btn btn-default" name="cancel">Cancel</button>
    										</a>
    									</div>
    								</div>
    							</div>
                            </form>
                        </div>
                    </div>
                </div>
                </section>

                <!-- BACKEND Forgot Password Modal -->
                <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="changePasswordModal" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content" style="float:left; width:100%; background-color: #fff;">
                            <form method="post" action="{{ url('admin/profile/change-password') }}" id="change_password_form">
                                <div class="modal-header ">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title">Change Password ?</h4>
                                </div>
                                
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 m-t-20 form-horizontal">
                                    <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Current Password : </label>
                                    <div class="col-md-10 col-sm-9 col-xs-12">
                                        <div class="" style="width: 100%">
                                            <input type="password" name="current_password" auto-complete="off" required placeholder="Current Password" class="form-control placeholder-no-fix">
                                            <!-- <input type="text" name="current_password" required placeholder="Current Password" class="form-control placeholder-no-fix"> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 form-horizontal">
                                    <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">New Password: </label>
                                    <div class="col-md-10 col-sm-9 col-xs-12">
                                        <div class="" style="width: 100%">
                                            <input type="password" name="new_password" auto-complete="off" required placeholder="New Password" id="new_password" class="form-control placeholder-no-fix">
                                            <!-- <input type="text" name="new_password" required placeholder="New Password" id="new_password" class="form-control placeholder-no-fix"> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 form-horizontal m-t-10">
                                    <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label">Confirm Password : </label>
                                    <div class="col-md-10 col-sm-9 col-xs-12">
                                        <div class="" style="width: 100%">
                                            <input type="password" name="confirm_password" auto-complete="off" placeholder="Confirm Password" class="form-control placeholder-no-fix">
                                            <!-- <input type="text" name="confirm_password" placeholder="Confirm Password" class="form-control placeholder-no-fix"> -->
                                        </div>
                                    </div>                                    
                                </div>

                                <div class="modal-footer" style="float:left; width:100%; background-color: #fff;">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                                    <button class="btn btn-primary" type="submit">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- modal -->

            </div>
        </div>
	</section>
</section>						

<script>
    $(function(){
        $('#change_password_form').validate({
            rules: {
                password:"required",
                new_password:{
                    required:true,
                    minlength:4,
                    maxlength:20,
                    regex:"^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{4,}$"
                },
                confirm_password: {
                    required:true,
                    equalTo:"#new_password"
                }
            },
            messages: {
                new_password:{
                    regex:"New password contain at least 1 capital letter, 1 small and 1 number."
                },
                confirm_password:"New password and confirm password mismatch."   
            },
            submitHandler: function(form) {
              form.submit();
            }
        })
        return false;  
    });
</script>

<!-- <script>
    $(function(){
        $('#change_password_form').validate({
            
            rules: {
                current_password: "required",
                new_password: "required",
                confirm_password: {
                    equalTo : "#new_password"
                }
            },
            submitHandler: function(form) {
                form.submit();
            }
        })
        return false;  
    });
</script> -->

<script>
$(document).ready(function() {

  function readURL(input) 
    {
      	if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e) 
                {
                    //$('#old_image').attr('src', e.target.result).width(150).height(170);
                    $('#old_image').attr('src', e.target.result);
                };
            reader.readAsDataURL(input.files[0]);
        }
    }

  	$("#img_upload").change(function(){	
        
      	var img_name = $(this).val();
      	if(img_name != "" && img_name!=null)
      	{
        	var img_arr=img_name.split('.');
        	var ext = img_arr.pop();
        	ext     = ext.toLowerCase();
        	if(ext =="jpg" || ext =="jpeg" || ext =="gif" || ext =="png")
			{
	            input=document.getElementById('img_upload');
	            if(input.files[0].size > 2097152 || input.files[0].size <  10240)
	            {
	              $(this).val('');
	              $("#img_upload").removeAttr("src");
	              alert("image size should be at least 10KB and upto 2MB");
	              return false;
	            }
	            else
	            {
	              readURL(this);
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
});
</script>

@endsection