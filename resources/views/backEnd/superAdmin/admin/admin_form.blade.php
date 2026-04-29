@extends('backEnd.layouts.master')

@section('title',' System Admin Form')

@section('content')

<style type="text/css">
    .add-admin-btn-area .save-btn {
        margin: 0px 10px 0px 0px;
    }
</style>

<?php
if (isset($system_admins)) {
    $action = url('admin/system-admin/edit/' . $system_admins->id);
    $task = "Edit";
    $form_id = 'edit_system_admins_form';

    if (isset($del_status)) {
        if ($del_status == '1') {
            $disabled = 'disabled';
            $task = 'View';
        } else {
            $disabled = '';
        }
    }
} else {
    $action = url('admin/system-admin/add');
    $task = "Add";
    $form_id = 'add_system_admins_form';
}
?>
<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{ $task }} Admin
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Name</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="name" class="form-control" placeholder="name" value="{{ (isset($system_admins->name)) ? $system_admins->name : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Username</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($system_admins->user_name)) ? $system_admins->user_name : '' }}" {{ (isset($system_admins)) ? 'readonly': '' }} maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Address</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="address" class="form-control" placeholder="Address" value="{{ (isset($system_admins->address)) ? $system_admins->address : '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Post Code</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="post_code" class="form-control" placeholder="Post Code" required value="{{ (isset($system_admins->post_code)) ? $system_admins->post_code : '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Email</label>
                                    <div class="col-lg-9">
                                        <input type="email" name="email" class="form-control" placeholder="email" value="{{ (isset($system_admins->email)) ? $system_admins->email : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Company</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="company" class="form-control" placeholder="company" value="{{ (isset($system_admins->company)) ? $system_admins->company : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <?php
                                $image = env('APP_URL') . adminImgPath . '/default_user.jpg';
                                if (isset($system_admins) && !empty($system_admins->image)) {
                                    $image_path = base_path() . adminbasePath . '/' . $system_admins->image;
                                    if (file_exists($image_path)) {
                                        $image = env('APP_URL') . adminImgPath . '/' . $system_admins->image;
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label"></label>
                                    <div class="col-lg-9">
                                        <img src="{{ $image }}" id="old_image" alt="No image" style="max-width: 200px; max-height: 150px; min-width: 150px; min-height: 100px; line-height: 100px;">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Image</label>
                                    <div class="col-md-8">
                                        <input type="file" id="img_upload" name="image" val="" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Home Address</label>
                                    <div class="col-lg-9">
                                        <textarea name="home_address" class="form-control" placeholder="address" rows="3" maxlength="1000">{{ (isset($system_admin_home->address)) ? $system_admin_home->address : '' }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Home Location</label>
                                    <div class="col-lg-9">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="is_home_area" id="is_home_area_checkbox" value="1" {{ (isset($system_admin_home->is_home_area) && $system_admin_home->is_home_area == 1) ? 'checked' : '' }}>
                                                (Check if this home have home area list)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id="home_area_list_section" style="display: {{ (isset($system_admin_home->is_home_area) && $system_admin_home->is_home_area == 1) ? 'block' : 'none' }};">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Home Area List</label>
                                        <div class="col-lg-9">
                                            <div id="home_area_inputs">
                                                @if(isset($home_areas) && count($home_areas) > 0)
                                                @foreach($home_areas as $area)
                                                <div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">
                                                    <input type="text" name="home_area_names[]" class="form-control" placeholder="Area name" value="{{ $area->area_name }}">
                                                    <button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>
                                                </div>
                                                @endforeach
                                                @else
                                                <div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">
                                                    <input type="text" name="home_area_names[]" class="form-control" placeholder="Area name">
                                                    <button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>
                                                </div>
                                                @endif
                                            </div>
                                            <button type="button" id="add_area_btn" class="btn btn-success btn-sm" style="margin-top:10px;"><i class="fa fa-plus"></i> Add Area</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Clock in/Clock out Range</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="clock_in_range" id="clock_in_range" class="form-control" placeholder="Clock in range" value="{{ (isset($system_admin_home->clock_in_range)) ? $system_admin_home->clock_in_range : '' }}" maxlength="255">
                                        <span class="help-block">(In meters or min 10 meters)</span>
                                    </div>
                                </div>

                                <?php $rota_time_format = (isset($system_admin_home->rota_time_format)) ? $system_admin_home->rota_time_format : ''; ?>
                                <?php
                                // $image = home . '/default_home.png';
                                $image = env('APP_URL') . home . '/default_home.png';

                                if (isset($system_admin_home->image)) {
                                    if (!empty($system_admin_home->image)) {
                                        $image = env('APP_URL') . home . '/' . $system_admin_home->image;
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Weekly Rate (Service Users)</label>
                                    <div class="col-lg-9">
                                        <input type="number" step="0.01" name="weekly_allowance_service_users" class="form-control" placeholder="Weekly Rate" value="{{ (isset($system_admin_home->weekly_allowance_service_users)) ? $system_admin_home->weekly_allowance_service_users : '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Monthly Rate (Service Users)</label>
                                    <div class="col-lg-9">
                                        <input type="number" step="0.01" name="monthly_allowance_service_users" class="form-control" placeholder="Monthly Rate" value="{{ (isset($system_admin_home->monthly_allowance_service_users)) ? $system_admin_home->monthly_allowance_service_users : '' }}">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">System Term for 'Staff'</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="staff_term" class="form-control" placeholder="e.g. Carer, Staff" value="{{ (isset($system_admin_home->staff_term)) ? $system_admin_home->staff_term : 'Staff' }}">
                                        <span class="help-block">This will change how staff members are referred to in the system.</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">System Term for 'Service User'</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="service_user_term" class="form-control" placeholder="e.g. Client, Child, Service User" value="{{ (isset($system_admin_home->service_user_term)) ? $system_admin_home->service_user_term : 'Service User' }}">
                                        <span class="help-block">This will change how service users are referred to in the system.</span>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-offset-2 col-lg-9">
                                            <div class="add-admin-btn-area">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="user_id" value="{{ (isset($system_admins->id)) ? $system_admins->id : '' }}">
                                                <button type="submit" class="btn btn-primary save-btn" name="submit1" {{ (isset($del_status)) ? $disabled: '' }}>Save</button>
                                                @if(isset($del_status))
                                                @if($del_status == '1')
                                                <a href="{{ url('admin/system-admins/'.'?user=archive') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                                @else
                                                <a href="{{ url('admin/system-admins') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                                @endif
                                                @else
                                                <a href="{{ url('admin/system-admins') }}">
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
    $(document).ready(function() {
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#old_image').attr('src', e.target.result);
                    //$('#old_image').attr('src', e.target.result).width(150).height(170);
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

        $('#is_home_area_checkbox').change(function() {
            if ($(this).is(':checked')) {
                $('#home_area_list_section').show();
            } else {
                $('#home_area_list_section').hide();
            }
        });

        $('#add_area_btn').click(function() {
            var areaInputHtml = '<div class="d-flex mb-2 area-input-group" style="display:flex; margin-bottom:10px;">' +
                '<input type="text" name="home_area_names[]" class="form-control" placeholder="Area name">' +
                '<button type="button" class="btn btn-danger btn-sm remove-area-btn" style="margin-left:10px;"><i class="fa fa-trash"></i></button>' +
                '</div>';
            $('#home_area_inputs').append(areaInputHtml);
        });
        $(document).on('click', '.remove-area-btn', function() {
            $(this).closest('.area-input-group').remove();
        });
    });
</script>

@endsection