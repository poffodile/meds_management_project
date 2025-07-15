<?php

use Illuminate\Support\Facades\DB; ?>
@extends('backEnd.layouts.master')

@section('title',': Form Builder Form')

@section('content')
<?php
if (isset($form)) {
    $action = url('admin/form-builder/edit/' . $form->id);
    $task = "Edit";
} else {
    $action = url('admin/form-builder/add');
    $task = "Add";
}
?>

</pre>


<style>
    .hidden {
        display: none;
    }

    .prient-btn input[type="button"] {
        background-color: #1fb5ad;
        border: none;
        color: #fff;
        padding: 7px 22px;
        border-radius: 5px;
        font-weight: 600;
        position: relative;
        /* top: -10px; */
        text-align: center;
        margin: 31px auto 20px auto;
        display: table;
    }


    .has-feedback.formio-component button {
        border-radius: 0px;
    }


    .component-btn-group {
        margin: 10px 0px 0px 0px;
    }

    .card-header {
        padding: 1px 0px 0px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        background: #f0f0f0;
    }

    .card {
        border: 1px solid #cdcdcd;
    }

    .component-settings .nav>li>a {
        padding: 8px 10px;
        font-size: 15px;
        border-radius: 0px;
        margin: 10px 0px 0px 0px;
    }

    .nav-tabs {
        border-bottom: none;
    }

    .form-control {
        height: 40px;
    }


    .card-body {
        padding: 10px 18px 20px 18px;
        background: #fff;
    }


    .has-feedback .form-control {
        border-radius: 0px;
        border: 1px solid #ced4da;
    }


    label {
        margin: 10px 0px 10px 0px;
        font-size: 14px;
        font-weight: 500;
    }

    .form-check-label span {
        line-height: 1.8;
    }


    .card-header.bg-default {
        padding: 13px 0px 13px 10px;
        font-size: 14px;
        background: rgba(0, 0, 0, .03);
        border: none !important;
    }


    .card.mb-2 {
        margin: 0px 0px 10px 0px;
    }


    .p-2 {
        padding: 0.5rem !important;
    }


    .btn-secondary.formio-button-remove-row {
        background: #1ca59e;
        color: #fff;
        padding: 8px 12px;
    }

    .editgrid-actions .btn.btn-primary {
        margin: 10px 10px 0px 0px;
    }

    .editgrid-actions .btn.btn-danger {
        margin: 10px 0px 0px 0px;
    }


    .panel {
        border-radius: 0;
    }

    .btn.btn-secondary {
        background: #1ca59e;
        color: #fff;
        border: none;
    }

    .btn.btn-success {
        background: #1ca59e;
        color: #fff;
        border: none;
    }

    .btn.btn-danger {
        background: #1ca59e;
        color: #fff;
        border: none;
    }


    .btn.btn-primary.btn-md {
        background: #1fb5ad;
        color: #fff;
        margin: 20px 0px 0px 15px;
    }

    .formcomponents.col-md-2 {
        width: 21%;
    }

    .formarea.col-md-10 {
        width: 79%;
    }

    .custom-from {
        padding: 30px 0px 0px 0px;
    }

    .p-t-15 {
        margin: 20px 0px 0px 0px;
    }

    .submit-btn-main-area .save-appointmnt-form {
        margin: 0px 10px 0px 0px;
    }

    .form-builder-group-header {
        padding: 1px 0px 0px 0px;
    }

    .input-group {
        display: flex;
    }

    .input-group-text {
        display: flex;
        -ms-flex-align: center;
        align-items: center;
        padding: 8px 15px;
        margin-bottom: 0;
        font-size: .9375rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        text-align: center;
        white-space: nowrap;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        font-size: 15px;
    }

    .input-group-text i {
        line-height: 22px;
    }

    .form-text {
        color: #a1a1a1;
        margin: 5px 0px 0px 0px;
        font-size: 14px;
    }
</style>



<section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
            <div class="col-lg-12">
                <section class="panel">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="cus-head">
                                <header class="panel-heading">
                                    {{ $task }} Form
                                </header>
                                <div class="cus-bg">
                                    @include('backEnd.common.alert_messages')
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-offset-2 col-md-8 col-sm-offset-1 col-sm-10 col-xs-12 m-t-20">
                                <!-- alert messages -->
                                <div class="form-horizontal cus-plus">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Title</label>
                                        <div class="col-lg-9 col-xs-11">
                                            <input type="text" name="form_title_add" class="form-control" placeholder="Form Title" value="{{ (isset($form->title)) ? $form->title : '' }}" maxlength="255">
                                            <!-- <span class="group-ico cus-plus-icn add_form_desc">
                                            <i class="fa fa-plus"></i>
                                        </span> -->
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Homes
                                            <span data-toggle="tooltip" title="Select locations where you want to show this form.">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </label>

                                        <div class="col-lg-9">
                                            <?php
                                                $homeId = App\Home::where('id', Session::get('scitsAdminSession')->home_id)->value('admin_id');
                                                $homes = App\Home::where('admin_id', $homeId)->where('is_deleted', 0)->get();                                           
                                                $selected_home_str = (isset($form->home_ids)) ? $form->home_ids : '';
                                                $selected_home = explode(',', $selected_home_str);  
                                            ?>
                                            @foreach($homes as $value)
                                            <div class="checkbox">
                                                <label> <input type="checkbox" name="home_ids[]" value="{{ $value['id'] }}" class="home_ids" {{ (in_array($value['id'],$selected_home)) ? 'checked' : '' }} />{{ $value['title'] }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Locations
                                            <span data-toggle="tooltip" title="Select locations where you want to show this form.">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </label>

                                        <div class="col-lg-9">
                                            <?php
                                            $selected_locations_str = (isset($form->location_ids)) ? $form->location_ids : '';
                                            $selected_locations = explode(',', $selected_locations_str);  ?>
                                            @foreach($locations as $value)
                                            <div class="checkbox">
                                                <label> <input type="checkbox" name="location_ids[]" value="{{ $value['id'] }}" class="location_ids" {{ (in_array($value['id'],$selected_locations)) ? 'checked' : '' }} />{{ $value['name'] }}</label>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <!--sourabh25-05-2023-->
                                    <?php
                                    $selected_logtype = (isset($form->logtype)) ? $form->logtype : '';
                                    $selectedlogtype = explode(',', $selected_logtype);  ?>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Log Type
                                            <span data-toggle="tooltip" title="Select Log type where you want to save this form.">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </label>

                                        <div class="col-lg-9">
                                            <?php $logtype = App\LogType::where('status', '1')->get();
                                            // dd($logtype);
                                            foreach ($logtype as $logvalue) {
                                            ?>
                                                <div class="checkbox">
                                                    <label> <input type="checkbox" name="logtype[]" value="{{$logvalue->id}}" class="logtype" {{ (in_array($logvalue->id,$selectedlogtype)) ? 'checked' : '' }} />{{$logvalue->name}}</label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="form-group description_form">
                                        <label class="col-lg-3 control-label">Comments</label>
                                        <div class="col-lg-9">
                                            <textarea name="form_detail_add" class="form-control" placeholder="Enter details" rows="3" maxlength="1000">{{ (isset($form->detail)) ? $form->detail : '' }}</textarea>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Reminder days
                                            <span data-toggle="tooltip" title="If you set its value greater then 0 then it will keep staff memebers reminding after every entered day.">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div class="col-lg-9">
                                            <input type="number" name="add_form_reminder_day" class="form-control" placeholder="Enter alert reminder days" value="{{ (isset($form->reminder_day)) ? $form->reminder_day : '0' }}" maxlength="5" min="0">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-lg-3 control-label">Send to
                                            <span data-toggle="tooltip" title="If send to option will be set to yes, then a send to field will be shown in the dynamic form">
                                                <i class="fa fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div class="col-lg-9">
                                            <select class="form-control send_to" name="send_to1">
                                                <?php //echo $form->send_to; die; 
                                                ?>
                                                <option value="N" <?php if (isset($form->send_to)) {
                                                                        if ($form->send_to == 'N') {
                                                                            echo "selected";
                                                                        }
                                                                    } ?>>
                                                    No</option>
                                                <option value="Y" <?php if (isset($form->send_to)) {
                                                                        if ($form->send_to == 'Y') {
                                                                            echo "selected";
                                                                        }
                                                                    } ?>>
                                                    Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <h4 class="text-center m-t-0 m-b-30">Select Field You want to Add</h4>
                                    <!-- <b>Select Field You want to Add</b> -->
                                    <div class="prient-btn">
                                        <input type="button" onclick="PrintDiv();" value="download PDF" />
                                    </div>
                                    <div class="form-group">
                                        <div class="col-lg-12" id="builder"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="custom-from m-t-50 p-t-30">
                                <div class="col-md-offset-2 col-md-8 col-sm-offset-1 col-sm-10 col-xs-12">
                                    <form class="form-horizontal" id="" method="post" action="{{ $action }}" enctype="multipart/form-data">
                                    
                                        <div class="form-group">
                                            <!-- <label class="col-lg-2 control-label mrtp70">Select an image to upload </label>
                                            <div class="col-lg-6">
                                                <input type="file" class="form-control mrtp80" id="image" name="form_image">
                                            </div> -->

                                            <label class="col-lg-2 control-label">Check if image upload </label>
                                            <div class="col-lg-6">
                                                <input type="checkbox" class="" id="image" value="1" {{ isset($form->is_image) && $form->is_image == 1 ? 'checked' : '' }} name="is_imageUpload">
                                            </div>

                                            @if(isset($form->image))
                                                <!-- <div class="col-lg-4">
                                                    <div class="uploadPopImg"><img id="preview" class="my-2" src="{{ url('public/images/formio/'. $form->image)}}" width="100px"></div>
                                                    <input type="hidden" class="form-control mrtp80" id="form_img" name="form_img" value="{{ $form->image }}">
                                                </div> -->
                                            @else
                                                <!-- <div class="col-lg-4">
                                                    <div class="uploadPopImg"><img id="preview" class="my-2" src="" width="100px"></div>
                                                </div> -->
                                            @endif

                                        </div>

                                        <div class="form-group">
                                            <label class="col-lg-2 control-label">Alert Field</label>
                                            <div class="col-lg-8">
                                                <select class="form-control show_alert_date" name="alert_field">
                                                    <option value="0" <?php if (isset($form->alert_field)) {
                                                                            if ($form->alert_field == '0') {
                                                                                echo "selected";
                                                                            }
                                                                        } ?>>
                                                        No</option>
                                                    <option value="1" <?php if (isset($form->alert_field)) {
                                                                            if ($form->alert_field == '1') {
                                                                                echo "selected";
                                                                            }
                                                                        } ?>>
                                                        Yes</option>
                                                </select>
                                                <p class="p-t-15"><u>Note: Above are the default fields. Dynamic fields will be shown below.</u></p>
                                            </div>
                                        </div>
                                        <div class="form-group <?php echo ($task != "Add") ? 'cus-btns' : ''; ?>">
                                            <label class="col-lg-2 control-label"></label>
                                            <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12">
                                                <input type="hidden" name="form_title" value="" />
                                                <input type="hidden" name="form_detail" value="" />
                                                <input type="hidden" name="form_location_ids" value="" />
                                                <input type="hidden" name="form_home_ids" value="" />
                                                <input type="hidden" name="logtypes" value="" />
                                                <input type="hidden" name="form_reminder_day" value="" />
                                                <input type="hidden" name="send_to" value="N" />
                                                <input type="hidden" name="dynamic_form_builder_id" value="{{ (isset($form->id)) ? $form->id : '' }}">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="formdata" value="{{ (isset($form->pattern)) ? $form->pattern : '' }}" id="setformdata">

                                                <div class="submit-btn-main-area">
                                                    <button type="submit" class="btn green btn-primary save-appointmnt-form " name="submit1"> Save </button>

                                                    <a href="{{ url('admin/form-builder') }}">
                                                        <button type="button" class="btn btn-default" name="cancel">Cancel</button>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
<!--  -->
<!-- <script src='https://cdn.form.io/formiojs/formio.full.min.js'></script> -->
<!-- <script>
    console.log(builder.schema.);
</script> -->
<script>
    $(document).ready(function() {
        var element = document.querySelector('[data-type="custom"]');
        if (element) {
            element.classList.add('hidden');
        }
    });
    $('.toogle_alert_date_div').hide();
    $(document).on('change', '.show_alert_date', function() {

        var alert_value = $('select[name=alert_field]').val();
        if (alert_value == '1') {
            $('.toogle_alert_date_div').show();
        } else {
            $('.toogle_alert_date_div').hide();
        }
    });
    $(document).on('click', '.save-appointmnt-form', function() {

        $('input[name=\'form_title\']').val($('input[name=\'form_title_add\']').val());
        $('input[name=\'form_detail\']').val($('textarea[name=\'form_detail_add\']').val());
        var reminder_day = $('input[name=\'form_reminder_day\']').val($(
            'input[name=\'add_form_reminder_day\']').val());

        var location_ids = [];
        $('.location_ids').each(function($key) {
            if ($(this).is(':checked')) {
                var location_id = $(this).val();
                location_ids.push(location_id);
            }
        });

        $('input[name=\'form_location_ids\']').val(location_ids);

        var home_ids = [];
        $('.home_ids').each(function($key) {
            if ($(this).is(':checked')) {
                var home_id = $(this).val();
                home_ids.push(home_id);
            }
        });

        $('input[name=\'form_home_ids\']').val(home_ids);

        var logtypes = [];
        $('.logtype').each(function($key) {
            if ($(this).is(':checked')) {
                var logtype = $(this).val();
                logtypes.push(logtype);
            }
        });

        $('input[name=\'logtypes\']').val(logtypes);

        var error = 0;
        if ($('input[name=\'form_title_add\']').val() == '') {
            $('input[name=\'form_title_add\']').addClass('red_border');
            error = 1;
        } else {
            $('input[name=\'form_title_add\']').removeClass('red_border');
        }

        if (error == 1) {
            return false;
        }
    });
</script>
<script type="text/javascript">
    $(document).on('change', '.send_to', function() {
        $('input[name=send_to]').val($(this).val());
    });
</script>

@endsection