@extends('backEnd.layouts.master')
@section('title',' Home Admin Form')
@section('content')
<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        <?php if (!isset($admin)) {
                            $action = url('super-admin/home-admin/add/' . $home_id);
                            $label = 'Add';
                            $form_id = 'HomeAdminAddForm';
                        } else {
                            $action = url('super-admin/home-admin/edit/' . $home_admin_id);
                            $label = 'Edit';
                            $form_id = 'HomeAdminEditForm';
                            if (isset($del_status)) {
                                if ($del_status == '1') {
                                    $disabled = 'disabled';
                                    $task = 'View';
                                } else {
                                    $disabled = '';
                                }
                            }
                        } ?>
                        Home Admin
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Name</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="name" class="form-control" placeholder="name" value="{{ (isset($admin->name)) ? $admin->name : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Username</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="user_name" class="form-control" placeholder="username" value="{{ (isset($admin->user_name)) ? $admin->user_name : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                <label class="col-lg-2 control-label">Password</label>
                                <div class="col-lg-10">
                                    <input type="text" name="password" class="form-control" placeholder="Password" value="{{ (isset($system_admins->password)) ? $system_admins->password : '' }}" >
                                </div>
                            </div>         -->
                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Email</label>
                                    <div class="col-lg-9">
                                        <input type="email" name="email" class="form-control" placeholder="email" value="{{ (isset($admin->email)) ? $admin->email : '' }}" maxlength="255" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Mobile</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="contact" class="form-control" placeholder="Mobile" value="{{ (isset($admin->contact)) ? $admin->contact : '' }}" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Address</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="address" class="form-control" placeholder="address" value="{{ (isset($admin->address)) ? $admin->address : '' }}" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-3 control-label">Post Code</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="post_code" class="form-control" placeholder="post code" value="{{ (isset($admin->post_code)) ? $admin->post_code : '' }}" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>


                                <!-- <div class="form-group">
                                <label class="col-lg-2 control-label">Company</label>
                                <div class="col-lg-10">
                                    <input type="text" name="company" class="form-control" placeholder="company" value="{{ (isset($system_admins->company)) ? $system_admins->company : '' }}" maxlength="255">
                                </div>
                            </div> 
 -->
                                <?php
                                $image = env('ASSETS_URL') . adminImgPath . '/default_user.jpg';
                                if (isset($admin->image)) {
                                    if (!empty($admin->image)) {
                                        $image = env('ASSETS_URL') . adminImgPath . '/' . $admin->image;
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
                                    <div class="col-md-9">
                                        <input type="file" id="img_upload" name="image" val="" {{ (isset($del_status)) ? $disabled: '' }}>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <div class="row">
                                        <div class="col-lg-offset-3 col-lg-10">
                                            <div class="add-admin-btn-area">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit" class="btn btn-primary save-btn" name="submit1" {{ (isset($del_status)) ? $disabled: '' }}>Save</button>
                                                @if(isset($del_status))
                                                @if($del_status == '1')
                                                <a href="{{ url('/super-admin/home-admin/'.$home_id.'?user=archive') }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                                @else
                                                <a href="{{ url('/super-admin/home-admin/'.$home_id) }}">
                                                    <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                </a>
                                                @endif
                                                @else
                                                <a href="{{ url('/super-admin/home-admin/'.$home_id) }}">
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
    });
</script>
@endsection