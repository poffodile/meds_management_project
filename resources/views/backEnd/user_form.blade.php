@extends('backEnd.layouts.master')

@section('title',':User Form')

@section('content')

<style type="text/css">
.position-center label {
font-size: 20px;
font-weight: 500;
} 

.position-center .assign-access {
font-size: 16px;
font-weight: 500;
}

.add_field_button-area {
 margin:20px 0px 0px 0px;   
}

.col-lg-offset-3 .btn.btn-primary {
margin: 0px 10px 0px 0px;   
}

.qual_upload {
margin:20px 0px 0px 0px;    
}


.input-group-addon {
border:none;    
}

.input-group-addon.remove-addon {
padding: 5px 0px 15px 0px;    
}
</style>


<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<?php
	if(isset($user_info)) {
		$action   = url('admin/users/edit/'.$user_info->id);
		$task     = "Edit";
		$form_id  = 'edit_user_form';
		$readonly = '';

        if(isset($del_status)) {
            if($del_status == '1') {
                $disabled = 'disabled';
                $task = 'View';
            } else {
                $disabled = '';
            }
        }

	} else {
		$action  = url('admin/users/add');
		$task    = "Add";
		$form_id = 'add_user_form';
	}
?>
 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                       {{ $task }}  User
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                            <label>Basic Info</label>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Name</label>
                                <div class="col-lg-9">
                                    <input type="text" name="name" class="form-control" placeholder="name" value="{{ (isset($user_info->name)) ? $user_info->name : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Username</label>
                                <div class="col-lg-9">
                                    <!-- <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($user_info->user_name)) ? $user_info->user_name : '' }}" > -->
                                    <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($user_info->user_name)) ? $user_info->user_name : '' }}" {{ (isset($user_info)) ? 'readonly': '' }} maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>                            
                                          
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Job Title</label>
                                <div class="col-lg-9">
                                    <input type="text" name="job_title" class="form-control" placeholder="job title" value="{{ (isset($user_info->job_title)) ? $user_info->job_title : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>                            
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Access Level</label>
                                <div class="col-lg-9">
                                    <!-- <input type="email" name="access_level" class="form-control" placeholder="access level" value="{{ (isset($user_info->access_level)) ? $user_info->access_level : '' }}"> -->
                                    <select class="form-control" name="access_level" {{ (isset($del_status)) ? $disabled: '' }}>
										<option value="">select level</option>

                                        <?php foreach($access_levels as $access_level){ ?>
                                            <option value="{{ $access_level['id'] }}" <?php if(isset($user_info->access_level)){ if($user_info->access_level == $access_level['id']){ echo 'selected'; } }  ?>>{{ $access_level['name'] }}</option>
                                        <?php } ?>
									</select>
                                </div>
                            </div>   
                            
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Department</label>
                                <div class="col-lg-9">
                                	<select class="form-control" name="department" id="">
                                        @foreach($company_departments as $company_department)
                                        <option value="{{ $company_department->id }}">{{ $company_department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Description</label>
                                <div class="col-lg-9">
                                	<textarea name="description" class="form-control" placeholder="description" rows="4" maxlength="1000" {{ (isset($del_status)) ? $disabled : '' }}>{{ (isset($user_info->description)) ? $user_info->description: '' }}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label">Payroll</label>
                                <div class="col-lg-9">
                                    <input type="text" name="payroll" class="form-control" placeholder="payroll" value="{{ (isset($user_info->payroll)) ? $user_info->payroll : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Holiday Entitlement</label>
                                <div class="col-lg-9">
                                    <input type="text" name="holiday_entitlement" class="form-control" placeholder="holiday entitlement" value="{{ (isset($user_info->holiday_entitlement)) ? $user_info->holiday_entitlement : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div> 

<!--                             <div class="form-group">
                                <label class="col-lg-2 control-label">Date of Joining</label>
                                <div class="col-lg-10">
                                   <input class="form-control date-format" type="text" value="{{ (isset($user_info->date_of_joining)) ? date('d-m-Y',strtotime($user_info->date_of_joining)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_joining" value="" maxlength="10" autocomplete="off" />

                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-2 control-label">Date of Leaving</label>
                                <div class="col-lg-10">
                                   <input class="form-control date-format" type="text" value="{{ (isset($user_info->date_of_leaving)) ? date('d-m-Y',strtotime($user_info->date_of_leaving)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_leaving" value="" maxlength="10" autocomplete="off"/>
                                </div>
                            </div> -->
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Date of Joining</label>
                                <div class="col-lg-9">
                                   <input class="form-control dpd1" type="text" value="{{ (isset($user_info->date_of_joining)) ? date('d-m-Y',strtotime($user_info->date_of_joining)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_joining" value="" id="joining-date" maxlength="10" autocomplete="off"/ {{ (isset($del_status)) ? $disabled :'' }}>
                                </div>
                            </div>

                            <div class="form-group check">
                                <label class="col-lg-3 control-label">Date of Leaving</label>
                                <div class="col-lg-9">
                                   <input class="form-control dpd2" type="text" value="{{ (isset($user_info->date_of_leaving)) ? date('d-m-Y',strtotime($user_info->date_of_leaving)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_leaving" value="" id="leaving-date" maxlength="10" autocomplete="off" class="custom-dtpikr"/ {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>

							<!--<div class="form-group">
                                <label for="inputEmail1" class="col-lg-2 control-label">Email</label>
                                <div class="col-lg-10">
                                    <input type="email" name="name" class="form-control" id="inputEmail1" placeholder="Email">
                                </div>
                            </div> -->

                            <?php
								$image = env('APP_URL').userProfileImagePath.'/default_user.jpg';
								if(isset($user_info->image))
								{
									$image = env('APP_URL').userProfileImagePath.'/'.$user_info->image;
								}
							?>
							<div class="form-group">
								<label class="col-lg-3 control-label"></label>
								<div class="col-lg-9">
									<img src="{{ $image }}" id="old_image" alt="No image" style="max-width: auto; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
								</div>
							</div>

							<div class="form-group choose-img-input-area">
								<label class="col-lg-3 control-label">Image</label>
								<div class="col-md-9">
									<input type="file" id="img_upload" name="image" val="" {{ (isset($del_status)) ? $disabled: '' }}>
								</div>
							</div>
							
							<div class="form-group">
								<label class="col-lg-3 control-label">Status</label>
								<div class="col-lg-9">
									<select name="status" class="form-control" {{ (isset($del_status)) ? $disabled: '' }}>
											<option value="">Select Status</option>
											<option value="1" <?php if(isset($user_info->status)) { if($user_info->status == '1'){ echo 'selected'; } }   ?>>Active
											
											</option>
											<option value="0" <?php if(isset($user_info->status)) { if($user_info->status == '0'){ echo 'selected'; } }   ?>>Inactive

											</option>			
									</select>
								</div>
							</div>
							<?php
                            if(isset($user_info)){
                                //$user_info->current_location = str_replace('<br />',"\r\n",$user_info->current_location);

                                $user_info->current_location      = preg_replace('#<br\s*/?>#i', "",$user_info->current_location); 
                                $user_info->personal_info         = preg_replace('#<br\s*/?>#i', "",$user_info->personal_info); 
                                $user_info->banking_info          = preg_replace('#<br\s*/?>#i', "",$user_info->banking_info); 
                                $user_info->qualification_info    = preg_replace('#<br\s*/?>#i', "",$user_info->qualification_info); 

                            } ?> 
                            <label>Contact Info</label>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Phone No.</label>
                                <div class="col-lg-9">
                                    <input type="text" name="phone_no" class="form-control" placeholder="Phone Number" value="{{ (isset($user_info->phone_no)) ? $user_info->phone_no : '' }}" maxlength="60" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Email</label>
                                <div class="col-lg-9">
                                    <input type="email" name="email" class="form-control" placeholder="email" value="{{ (isset($user_info->email)) ? $user_info->email : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                </div>
                            </div>  
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Current Location</label>
                                <div class="col-lg-9">
                                    <textarea name="current_location" class="form-control" placeholder="Current location" rows="4" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->current_location)) ? $user_info->current_location : '' }}</textarea>
                                </div>
                            </div>

							<label>More Info</label>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Personal Information</label>
                                <div class="col-lg-9">
                                    <textarea name="personal_info" class="form-control" placeholder="Personal information" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}><?php echo (isset($user_info->personal_info)) ? $user_info->personal_info : ''; ?> </textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-3 control-label">Banking Information</label>
                                <div class="col-lg-9">
                                    <textarea name="banking_info" class="form-control" placeholder="Banking information" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->banking_info)) ? $user_info->banking_info : '' }}</textarea>
                                </div>
                            </div>
                            <!-- <div class="form-group">
                                <label class="col-lg-2 control-label">Qualification Information</label>
                                <div class="col-lg-10">
                                    <textarea name="qualification_info" class="form-control" placeholder="Qualification information" rows="6" maxlength="2000">{{ (isset($user_info->qualification_info)) ? $user_info->qualification_info : '' }}</textarea>
                                </div>
                            </div> -->

                            @if(isset($user_info->certificates))
                            <div class="form-group">
                                <label class="col-lg-3 col-md-2 col-sm-2 col-xs-12 control-label ">Qualification Information</label>
                                <div class="col-lg-9 col-md-10 col-sm-10 col-xs-12 qualification">
                                 <div class="add-admin-btn-are">   
                                    <div class="input_fields">      
                                        @foreach($user_info->certificates as $certi)                    
                                            <div class="appended-whole-div" rel="{{$certi->id}}">
                                                <div class="multi-upload">
                                                    <div>
                                                        <input type="text" class="form-control" value="{{$certi->name}}" readonly="" / {{ (isset($del_status)) ? $disabled: '' }}>
                                                    </div>
                                                    <div class="input-group">
                                                        <a href="{{userQualificationImgPath.'/'.$certi->image}}" class="image save-btn clr-blue" target="blank">View Image</a>
                                                        <span class="input-group-addon remove-addon">
                                                            <button type="button" class="e_remove_field btn btn-danger save-btn" {{ (isset($del_status)) ? $disabled: '' }}>Remove</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                   </div>
                                    <div class="input_fields_wrap">
                                    </div>
                                    <div>
                                        <button class="add_field_button btn btn-primary" {{ (isset($del_status)) ? $disabled: '' }}>Add More Fields <i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>          
                            @else
                                <div class="form-group">
                                    <label class="col-lg-3 col-md-2 col-sm-2 col-xs-12 control-label ">Qualification Information
                                    </label>
                                    <div class="col-lg-9 col-md-10 col-sm-10 col-xs-12 qualification">
                                        <div class="input_fields_wrap">
                                            <!-- <div><button class="add_field_button btn btn-primary">Add More Fields</button></div> -->
                                            <div><input type="text" name="qualification[]" class="form-control" /></div>
                                            <div><input type="file" name="qualifiaction_cert[]"  class="qual_upload"/></div>
                                        </div>   
                                        <div class="add_field_button-area add-admin-btn-area">
                                            <button class="add_field_button btn btn-primary save-btn">Add More Fields</button>
                                        </div> 
                                    </div>
                                </div>
                            @endif  

                            <div class="form-group">
                                <label class="col-lg-3 control-label"></label>
                                <div class="col-lg-9">
                                    <div class="checkbox">
                                        <label class="assign-access"><input type="checkbox" value="yes" name="assign_right_check" {{ (isset($del_status)) ? $disabled: '' }}>Assign access rights according to the access level
                                        </label>
                                    </div>
                                </div>
                            </div>

							<div class="form-actions">
								<div class="row">
									<div class="col-lg-offset-3 col-lg-10">
                                     <div class="add-admin-btn-area">   
										<input type="hidden" name="_token" value="{{ csrf_token() }}">
										<input type="hidden" name="user_id" value="{{ (isset($user_info->id)) ? $user_info->id : '' }}">
										<button type="submit" class="btn btn-primary save-btn" name="submit1" {{ (isset($del_status)) ? $disabled: '' }}>Save</button>
                                        @if(isset($del_status))
                                            @if($del_status == '1') 
        										<a href="{{ url('admin/users/'.'?user=archive') }}">
        											<button type="button" class="btn btn-default" name="cancel">Cancel</button>
        										</a>
                                            @else
                                                <a href="{{ url('admin/users') }}">
                                                        <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                            @endif
                                        @else
                                        <a href="{{ url('admin/users') }}">
                                            <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                        </a>
                                        @endif
									</div>
                                 </div>
								</div>
							</div>
                        </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
	</section>
</section>						

<script>
/*$(document).ready(function() {
    $('.date-format').datepicker({  

        format : 'dd-mm-yyyy', 
    });
});*/
</script>

<script type="text/javascript">
    $(document).ready(function(){
        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
        var joining_date = $('#joining-date').datepicker({
            format: 'dd-mm-yyyy',
            onRender: function (date) {
                //to compare "joining_date" and "leaving_date"
                return date.valueOf();
            }
        }).on('changeDate', function (ev) {
            var newDate = new Date(ev.date);
            newDate.setDate(newDate.getDate() + 1);
            leaving_date.setValue(newDate);
          
            joining_date.hide();

            $('#add_user_form').bootstrapValidator('revalidateField', 'date_of_joining');
            $('#add_user_form').bootstrapValidator('revalidateField', 'date_of_leaving');
            $('#edit_user_form').bootstrapValidator('revalidateField', 'date_of_joining');
            $('#edit_user_form').bootstrapValidator('revalidateField', 'date_of_leaving');

        /** 'commented' : Focus immediately transfers control to "LEAVING DATE" without complete selection of "JOINING DATE"  
            ** $('#leaving-date')[0].focus();
        **/
        }).data('datepicker');
        //console.log(joining_date);
        var leaving_date = $('#leaving-date').datepicker({
          format: 'dd-mm-yyyy',
          onRender: function (date) {
            return date.valueOf() <= joining_date.date.valueOf() ? 'disabled' : '';
          }
        }).on('changeDate', function (ev) {
          leaving_date.hide();
        }).data('datepicker');
    });
</script>

<script>
$(document).ready(function()
{
  function readURL(input) 
    {
      	if (input.files && input.files[0])
        {
            var reader = new FileReader();
            reader.onload = function (e) 
                {
                    $('#old_image').attr('src', e.target.result);
                    //$('#old_image').attr('src', e.target.result).width(150).height(170);
                };
            reader.readAsDataURL(input.files[0]);
        }
    }
  	$("#img_upload").change(function()
  	{	
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
<script type="text/javascript">
    $(document).ready(function() {
        var max_fields      = 10; //maximum input boxes allowed
        var wrapper         = $(".input_fields_wrap"); //Fields wrapper
        var add_button      = $(".add_field_button"); //Add button ID
       
        var x = 1; //initlal text box count
        $(add_button).click(function(e){ //on add input button click
            e.preventDefault();
            //if(x < max_fields){ //max input box allowed
                x++; //text box increment
                $(wrapper).append('<div class="appended-whole-div"><div class="multi-upload"><div"><input type="text" name="qualification[]" class="form-control" /></div><div class="input-group"><input type="file" name="qualifiaction_cert[]" class="qual_upload" /><span class="input-group-addon remove-addon"><a href="#" class="remove_field btn btn-danger">Remove</a></span></div></div></div>'); //add input box
            //ss}
        });
       
        $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
            e.preventDefault(); 
            $(this).closest ('.appended-whole-div').remove(); x--;
        })
    });


    /*For removing certificates of users*/
    var wrapper  = $(".input_fields");
    $(wrapper).on("click",".e_remove_field", function(e){ //user click on remove text
        e.preventDefault(); 

        var e_remove_btn = $(this);
        
        $('.loader').show();
        $('body').addClass('body-overflow');
        var id = $(this).closest('.appended-whole-div').attr('rel');
        $.ajax({
            url:"{{url('admin/users/certificates/delete')}}" + "/" +id,
            post:"GET",
            success: function(data)
            {
                alert('{{ DEL_RECORD }}');
                e_remove_btn.closest('.appended-whole-div').remove();
                $('.loader').hide();
                $('body').removeClass('body-overflow');

            },
            error: function()
            {
                alert('{{ COMMON_ERROR }}');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
    });
</script>

<script>
$(document).ready(function()
{
    // $(".qual_upload").change(function()
    $(document).on('change','.qual_upload',function()
    {   
        var img_name = $(this).val();
        if(img_name != "" && img_name!=null)
        {
            var img_arr=img_name.split('.');
            var ext = img_arr.pop();
            ext     = ext.toLowerCase();
            if(ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'pdf' || ext == 'doc' || ext == 'docx')
            {
                input=document.getElementsByClassName('qual_upload');
                if(input.files[0].size > 2097152 || input.files[0].size <  10240)
                {
                  $(this).val('');
                  $(".qual_upload").removeAttr("src");
                  alert("file size should be at least 10KB and upto 2MB");
                  return false;
                }
             }
           else
            {
                $(this).val('');
                alert('Please select an image .jpg, .png, .pdf, .doc, .docx, .jpeg file format type.');
            }
        }
    return true;
    }); 

});
</script>

@endsection