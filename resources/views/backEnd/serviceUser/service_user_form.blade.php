@extends('backEnd.layouts.master')
@section('title',' Child Form')
@section('content')

<?php
if (isset($user_info)) {
    $action = url('admin/service-users/edit/' . $user_info->id);
    $task = "Edit";
    $form_id = 'edit_service_user_form';

    if (isset($del_status)) {
        if ($del_status == '1') {
            $disabled = 'disabled';
            $task = 'View';
        } else {
            $disabled = '';
        }
    }
} else {
    $action = url('admin/service-users/add');
    $task = "Add";
    $form_id = 'add_service_user_form';
}
?>
<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{ $task }} Child Form
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <label class="form-heading-size">Basic Info</label>
                                <div class="form-group">

                                    <label class="col-lg-3 control-label">Name</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="name" class="form-control" placeholder="Name" value="{{ (isset($user_info->name)) ? $user_info->name : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Username</label>
                                    <div class="col-lg-9">
                                        <!-- <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($user_info->user_name)) ? $user_info->user_name : '' }}" > -->
                                        <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($user_info->user_name)) ? $user_info->user_name : '' }}" {{ (isset($user_info)) ? 'readonly': '' }} maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <?php
                                $image = env('APP_URL') . serviceUserProfileImagePath . '/default_user.jpg';
                                if (isset($user_info->image)) {
                                    if (!empty($user_info->image)) {
                                        $image = env('APP_URL') . serviceUserProfileImagePath . '/' . $user_info->image;
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"></label>
                                    <div class="col-md-9">
                                        <img src="{{ $image }}" id="old_image" alt="No image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                    </div>
                                </div>
                                <div class="form-group choose-img-input-area">
                                    <label class="col-lg-3 control-label">Image</label>
                                    <div class="col-lg-9">
                                        <input type="file" id="img_upload" name="image" val="" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Admission Number</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="admission_number" class="form-control" placeholder="Admission number" value="{{ (isset($user_info->admission_number)) ? $user_info->admission_number : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Section</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="section" class="form-control" placeholder="Section" value="{{ (isset($user_info->section)) ? $user_info->section : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Date of Birth</label>
                                    <div class="col-lg-9">
                                        <input class="form-control date-of-birth" type="text" value="{{ (isset($user_info->date_of_birth)) ? date('d-m-Y',strtotime($user_info->date_of_birth)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_birth" value="" autocomplete="off" maxlength="10" / {{ (isset($del_status)) ? $disabled: '' }}>

                                        <!-- <input class="form-control default-date-picker" type="text" value="{{ (isset($user_info->date_of_birth)) ? date('d-m-Y',strtotime($user_info->date_of_birth)) : '' }}" placeholder="DD-MM-YYYY" name="date_of_birth" value="" maxlength="10" /> -->
                                    </div>
                                </div>
                                <!-- Child type -->
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Child Type</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="child_type" id="home_type">
                                            <option value="">Select Type</option>
                                            <option value="residential" {{ isset($user_info->child_type) && $user_info->child_type == "residential" ? "selected" : ''}}>Residential</option>
                                            <option value="accommodation" {{ isset($user_info->child_type) && $user_info->child_type == "accommodation" ? "selected" : ''}}>Supported Accomodation</option>
                                            <option value="leavers" {{ isset($user_info->child_type) && $user_info->child_type == "leavers" ? "selected" : ''}}>Leavers</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Residential rooms -->
                                <div class="form-group" id="residential_rooms" style="display: none;">
                                    <label class="col-lg-3 control-label">Residential Rooms Type</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="room_type">
                                            <option value="">Select Type </option>
                                            <option value="1" {{ isset($user_info->room_type) && $user_info->room_type == "1" ? "selected" : '' }}>1 Bed Placement </option>
                                            <option value="2" {{ isset($user_info->room_type) && $user_info->room_type == "2" ? "selected" : '' }}>2 Bed Placement </option>
                                            <option value="3" {{ isset($user_info->room_type) && $user_info->room_type == "3" ? "selected" : '' }}>3 Bed Placement </option>
                                            <option value="4" {{ isset($user_info->room_type) && $user_info->room_type == "4" ? "selected" : '' }}>4 Bed Placement </option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Supported Accommodation rooms -->
                                <div class="form-group" id="accommodation_rooms" style="display: none;">
                                    <label class="col-lg-3 control-label">Supported Accomodation Rooms Type</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="room_type">
                                            <option value="">Select Type</option>
                                            <option value="1" {{ isset($user_info->room_type) && $user_info->room_type == "1" ? "selected" : '' }}>Group Living</option>
                                            <option value="2" {{ isset($user_info->room_type) && $user_info->room_type == "2" ? "selected" : '' }}>Seperate Flats </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Weekly Rate</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="weekly_rate" class="form-control" placeholder="Weekly Rate" value="{{ isset($user_info->weekly_rate) ? $user_info->weekly_rate : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Subs </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="subs" class="form-control" placeholder="Subs" value="{{ isset($user_info->subs) ? $user_info->subs : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Extra </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="extra" class="form-control" placeholder="Extra" value="{{ isset($user_info->extra) ? $user_info->extra : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Start Date</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="start_date" class="form-control date-of-birth" placeholder="DD-MM-YYYY" value="{{ isset($user_info->start_date) ? $user_info->start_date : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">End Date</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="end_date" class="form-control date-of-birth" value="{{ isset($user_info->end_date) ? $user_info->end_date : '' }}" placeholder="DD-MM-YYYY">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Local Authority </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="local_authority" class="form-control" placeholder="Local Authority" value="{{ isset($user_info->local_authority) ? $user_info->local_authority : '' }}" maxlength="255">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Height</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="height" class="form-control" placeholder="Height" value="{{ (isset($user_info->height)) ? $user_info->height : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Weight</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="weight" class="form-control" placeholder="Weight" value="{{ (isset($user_info->weight)) ? $user_info->weight : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Hair & Eyes</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="hair_and_eyes" class="form-control" placeholder="Hair &  Eyes" value="{{ (isset($user_info->hair_and_eyes)) ? $user_info->hair_and_eyes : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Markings</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="markings" class="form-control" placeholder="Markings" value="{{ (isset($user_info->markings)) ? $user_info->markings : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Ethnicity</label>
                                    <div class="col-lg-9">
                                        <select class="form-control" name="ethnicity_id" {{ (isset($del_status)) ? $disabled: '' }}>
                                            <option value="">Select Ethnicity</option>
                                            @foreach($ethnicity as $value)
                                            <option value="{{ $value->id }}" <?php if (isset($user_info->ethnicity_id)) { ?>{{ ($user_info->ethnicity_id == $value->id) ? 'selected' : '' }} <?php } ?>>{{ $value->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Short Description</label>
                                    <div class="col-lg-9">
                                        <textarea name="short_description" class="form-control" placeholder="Short description" rows="4" maxlength="1000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->short_description)) ? $user_info->short_description : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Status</label>
                                    <div class="col-lg-9">
                                        <select name="status" class="form-control" {{ (isset($del_status)) ? $disabled: '' }}>
                                            <option value="">Select Status</option>
                                            <option value="1" <?php if (isset($user_info->status)) {
                                                                    if ($user_info->status == '1') {
                                                                        echo 'selected';
                                                                    }
                                                                }   ?>>Active

                                            </option>
                                            <option value="0" <?php if (isset($user_info->status)) {
                                                                    if ($user_info->status == '0') {
                                                                        echo 'selected';
                                                                    }
                                                                }   ?>>Inactive

                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <?php
                                if (isset($user_info)) {
                                    //$user_info->current_location = str_replace('<br />',"\r\n",$user_info->current_location);

                                    $user_info->current_location      = preg_replace('#<br\s*/?>#i', "", $user_info->current_location);
                                    $user_info->previous_location     = preg_replace('#<br\s*/?>#i', "", $user_info->previous_location);
                                    $user_info->personal_info         = preg_replace('#<br\s*/?>#i', "", $user_info->personal_info);
                                    $user_info->education_history     = preg_replace('#<br\s*/?>#i', "", $user_info->education_history);
                                    $user_info->bereavement_issues    = preg_replace('#<br\s*/?>#i', "", $user_info->bereavement_issues);
                                    $user_info->drug_n_alcohol_issues = preg_replace('#<br\s*/?>#i', "", $user_info->drug_n_alcohol_issues);
                                    $user_info->mental_health_issues  = preg_replace('#<br\s*/?>#i', "", $user_info->mental_health_issues);
                                } ?>
                                <label class="form-heading-size">Contact Info</label>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Phone</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="phone_no" class="form-control" placeholder="Phone Number" value="{{ (isset($user_info->phone_no)) ? $user_info->phone_no : '' }}" maxlength="60" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Mobile</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="mobile" class="form-control" placeholder="Mobile" value="{{ (isset($user_info->mobile)) ? $user_info->mobile : '' }}" maxlength="15" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Email</label>
                                    <div class="col-lg-9">
                                        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ (isset($user_info->email)) ? $user_info->email : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Current Location</label>
                                    <div class="col-lg-9">
                                        <textarea name="current_location" class="form-control" placeholder="Current location" rows="4" maxlength="1000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->current_location)) ? $user_info->current_location : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Previous Location</label>
                                    <div class="col-lg-9">
                                        <textarea name="previous_location" class="form-control" placeholder="Previous location" rows="4" maxlength="1000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->previous_location)) ? $user_info->previous_location : '' }}</textarea>
                                    </div>
                                </div>
                                <!--  <div class="form-group">
                                <label class="col-lg-2 control-label">Facebook</label>
                                <div class="col-lg-10">
                                    <input type="text" name="facebook" class="form-control" placeholder="Facebook" value=" maxlength="255">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Twitter</label>
                                <div class="col-lg-10">
                                    <input type="text" name="twitter" class="form-control" placeholder="Twitter" value="" maxlength="255">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-lg-2 control-label">Skype</label>
                                <div class="col-lg-10">
                                    <input name="skype" type="text" class="form-control" placeholder="Skype" value="" maxlength="255">
                                </div>
                            </div> -->
                                <?php if (empty($social_app)) { ?>
                                <?php } else {  ?><label class="form-heading-size">Social App's</label>
                                <?php    }

                                foreach ($social_app as $key => $value) {
                                    $app_name      = $value['name'];
                                    $social_app_id = $value['id'];

                                    $field_id    = (isset($social_app_val[$social_app_id]['id'])) ? $social_app_val[$social_app_id]['id'] : '';
                                    $field_value = (isset($social_app_val[$social_app_id]['value'])) ? $social_app_val[$social_app_id]['value'] : '';
                                ?>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">{{ $app_name }}</label>
                                        <div class="col-lg-9">
                                            <input name="social_app[{{ $key }}][social_app_id]" type="hidden" value="{{ $social_app_id }}" {{ (isset($del_status)) ? $disabled: '' }}>
                                            <input name="social_app[{{ $key }}][su_app_id]" type="hidden" value="{{ $field_id }}" {{ (isset($del_status)) ? $disabled: '' }}>
                                            <input name="social_app[{{ $key }}][value]" type="text" class="form-control" value="{{ $field_value }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>

                                        </div>
                                    </div>
                                <?php } ?>

                                <label class="form-heading-size">More Info</label>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Personal Information</label>
                                    <div class="col-lg-9">
                                        <textarea name="personal_info" class="form-control" placeholder="Personal information" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}><?php echo (isset($user_info->personal_info)) ? $user_info->personal_info : ''; ?> </textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Education History</label>
                                    <div class="col-lg-9">
                                        <textarea name="education_history" class="form-control" placeholder="Education history" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->education_history)) ? $user_info->education_history : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Bereavement issues</label>
                                    <div class="col-lg-9">
                                        <textarea name="bereavement_issues" class="form-control" placeholder="Bereavement issues" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->bereavement_issues)) ? $user_info->bereavement_issues : '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Drug & alcohol issues</label>
                                    <div class="col-lg-9">
                                        <textarea name="drug_n_alcohol_issues" class="form-control" placeholder="Drug & alcohol issues" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->drug_n_alcohol_issues)) ? $user_info->drug_n_alcohol_issues: '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Mental Health Issues</label>
                                    <div class="col-lg-9">
                                        <textarea name="mental_health_issues" class="form-control" placeholder="Mental health issues" rows="6" maxlength="2000" {{ (isset($del_status)) ? $disabled: '' }}>{{ (isset($user_info->mental_health_issues)) ? $user_info->mental_health_issues : '' }}</textarea>
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
                                                <a href="{{ url('admin/service-users/'.'?user=archive') }}"><button type="button" class="btn btn-default" name="cancel">Cancel</button></a>
                                                @else
                                                <a href="{{ url('admin/service-users') }}"><button type="button" class="btn btn-default" name="cancel">Cancel</button></a>
                                                @endif
                                                @else
                                                <a href="{{ url('admin/service-users') }}"><button type="button" class="btn btn-default" name="cancel">Cancel</button></a>
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
    $(document).ready(function() {

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    //$('#old_image').attr('src', e.target.result).width(150).height(170);
                    $('#old_image').attr('src', e.target.result);
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#img_upload").change(function() {

            var img_name = $(this).val();
            if (img_name != "" && img_name != null) {
                var img_arr = img_name.split('.');
                var ext = img_arr.pop();
                ext = ext.toLowerCase();
                if (ext == "jpg" || ext == "jpeg" || ext == "gif" || ext == "png") {
                    input = document.getElementById('img_upload');
                    if (input.files[0].size > 2097152 || input.files[0].size < 10240) {
                        $(this).val('');
                        $("#img_upload").removeAttr("src");
                        alert("image size should be at least 10KB and upto 2MB");
                        return false;
                    } else {
                        readURL(this);
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
    $(document).ready(function() {
        $('#home_type').change(function() {
            var selectedType = $(this).val();
            if (selectedType === 'residential') {
                $('#residential_rooms').show().find('select').prop('disabled', false);
                $('#accommodation_rooms').hide().find('select').prop('disabled', true);
            } else if (selectedType === 'accommodation') {
                $('#accommodation_rooms').show().find('select').prop('disabled', false);
                $('#residential_rooms').hide().find('select').prop('disabled', true);
            } else {
                $('#residential_rooms, #accommodation_rooms').hide().find('select').prop('disabled', true);
            }
        });

        // ✅ Trigger change once on page load to apply correct state
        $('#home_type').trigger('change');
    });

    // before set room type we have to check child type check child type if residential then 1 bed 2 bed shows


    $(document).ready(function() {

        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
        /*var date = new Date();
        var currentMonth = date.getMonth();
        var currentDate = date.getDate();
        var currentYear = date.getFullYear();*/
        //console.log(date);
        $('.date-of-birth').datepicker({
            format: 'dd-mm-yyyy',

            onRender: function(date) {
                return date.valueOf() > now.valueOf() ? 'disabled' : '';
            }
        });
    });
</script>

<script>
    /*$(document).ready(function() {
    var date = new Date();
    var currentMonth = date.getMonth();
    var currentDate = date.getDate();
    var currentYear = date.getFullYear();
    //console.log(date);
    $('.default-date-picker').datepicker({
            format : 'dd-mm-yyyy',
            //maxDate: '01-01-2017'
            //maxDate : date
            //maxDate: new Date(currentYear, currentMonth, currentDate)
            //endDate : date
            //maxDate: 'now'
            //maxDate:'+1d'
            //maxDate:'2017/04/04'
            maxDate:'0'
        });
    });*/
</script>

<!-- setDate: new Date(2006, 11, 24)
'maxDate':'13-02-2017',
            'setDate':'13-02-2018'
format: 'yyyy-mm-dd'
            format : 'dd-mm-yyyy',

            var date = new Date();
            var currentMonth = date.getMonth();
            var currentDate = date.getDate();
            var currentYear = date.getFullYear();
            $('#datetimepicker,#datetimepicker1').datetimepicker({
                                    pickTime: false,
                format: "DD-MM-YYYY",   
                maxDate: new Date(currentYear, currentMonth, currentDate)
            });

 -->

@endsection