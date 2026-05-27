@extends('frontEnd.layouts.master')
@section('title', 'Risk Management Plan')
@section('content')

<link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

<section id="container">
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="pull-right">
                    <div class="filter_buttons"
                        style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                        <a data-toggle="modal" href="#rmpModal" class="btn btn-primary col-6" id=''>Add New</a>
                    </div>
                </div>
            </div>
            <!-- page start-->
            <div class="row" style="margin-bottom:30px;">
                <div class="col-md-1 col-lg-1">
                    <a class="back_opt col-3" onclick="history.back()">
                        <i class="fa fa-angle-left"></i>
                    </a>
                </div>
                <!-- sourabh -->
                <div class="col-md-2 col-lg-2">
                    <select class="form-control" name="service_user" id="service_user" <?php if (isset($service_user_id)) {
                                                                                            echo 'disabled';
                                                                                        } ?>>
                        <option value="">Select Child</option>
                        @foreach ($service_users as $val)
                        <option <?php if (isset($service_user_id)) {
                                    if ($service_user_id == $val['id']) {
                                        echo 'Selected';
                                    }
                                } ?> value="{{ $val['id'] }}">{{ $val['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- sourabh -->
                <div class="col-md-2 col-lg-2" style="margin-left: -10px;">
                    <div class="form-group datepicker-sttng date-sttng">
                        <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                            class="input-group date">
                            <input id="date_range_input" style="cursor: pointer;" name="daterange"
                                value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text" value=""
                                readonly="" size="16" class="form-control log-book-datetime">
                            <span class="input-group-btn add-on datetime-picker2">
                                <button onclick="showDate()" class="btn btn-primary" type="button"><span
                                        class="glyphicon glyphicon-calendar"></span></button>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- sourabh -->
                <div class="col-md-2 col-lg-2" style="padding-bottom:10px; margin-left: 10px;">
                    <input type="text" class="form-control" id="keywordhr" onKeyPress="hrmyFunctionkey()"
                        name="keywordhr" placeholder="Keyword">
                </div>
                <!-- sourabh -->

            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="timeline">
                        <article class="timeline-item alt">
                            <div class="text-right">
                                <div class="time-show first">
                                    <a href="#" class="btn btn-primary" id="today">Today</a>
                                </div>
                            </div>
                        </article>
                        <div class="logged_rmp_btn">
                            <div class="modal-space modal-pading logged-rmp-plan-shown">
                                <!-- record shown using Ajax -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- page end-->
        </section>
    </section>
    <!--main content end-->

</section>

<!-- RMP Plan Add-->
<div class="modal fade my_plan_model" id="rmpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close rmp-modal-close" data-dismiss="modal"
                    aria-hidden="true">&times;</button>
                <h4 class="modal-title">{{ $labels['rmp']['label'] }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- <div class="form-group col-md-12 col-sm-12 col-xs-12 serch-btns text-right">
                            <button class="btn label-default add-new-btn active" type="button"> Add New </button>
                            <button class="btn label-default logged-btn active logged_rmp_btn" type="button"> Logged Plans</button>
                            <button class="btn label-default search-btn active" type="button"> Search </button>
                        </div> --}}
                    <!-- Add new Details -->
                    <div class="add-new-box risk-tabs custm-tabs">
                        <form method="post" action="" id="rmp_form">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Child: </label>
                                <div class="col-md-11 col-sm-11 col-xs-12">
                                    <div class="select-style">
                                        <select name="service_user_id" class="su_n_id">
                                            <!-- <option selected disabled> select a different child </option> -->
                                            @foreach ($service_users as $value)
                                            <option value="{{ $value['id'] }}"
                                                {{ $service_user_id == $value['id'] ? 'selected' : '' }}>
                                                {{ ucfirst($value['name']) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Add: </label>
                                <div class="col-md-11 col-sm-12 col-xs-12">
                                    <div class="select-style">
                                        <select name="dynamic_form_builder_id" class="dynamic_form_select">
                                            <option value="0"> Select Form </option>

                                            <?php

                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('rmp');
                                            foreach ($dynamic_forms as $value) {

                                                $location_ids_arr = explode(',', $value['location_ids']);

                                                if (in_array($this_location_id, $location_ids_arr)) {
                                            ?>
                                                    <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }}
                                                    </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                    <p class="help-block"> Choose a user and the type of form you want to fill. </p>
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="below-divider"></div>
                            </div>

                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')

                            <div class="dynamic-form-fields"><!-- Dynamic form fields will be shown here --></div>

                            <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="uploadPopImg mt-0 hideImageDiv"><img class="my-2 imagePreview" src="" width="100px"></div>
                                    </div> -->

                            <div class="modal-footer m-t-0 m-b-15 modal-bttm">
                                <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <!-- <input type="hidden" name="service_user_id" value="{{ $service_user_id }}"> -->
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="btn btn-default rmp-modal-close" type="button" data-dismiss="modal"
                                    aria-hidden="true"> Cancel </button>
                                <button class="btn btn-warning sbt-dyn-form-btn" type="submit"> Confirm </button>
                                <!-- sbt_rmp_btn -->
                            </div>
                        </form>
                    </div>

                    <!-- logged plans -->
                    <div class="logged-box risk-tabs custm-tabs">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h3 class="m-t-0 m-b-20 clr-blue"> Logged Records </h3>
                        </div>
                        <!-- alert messages -->
                        @include('frontEnd.common.popup_alert_messages')
                        <form method="post" id="edit-rmp-form">
                            <div class="modal-space modal-pading logged-rmp-plan-shown">
                                <!-- logged risk list be shown here using ajax -->

                            </div>
                        </form>
                        <div class="modal-footer m-t-0 recent-task-sec">
                            <button class="btn btn-default" type="button" data-dismi ss="modal" aria-hidden="true">
                                Cancel </button>
                            <button class="btn btn-warning sbt-edit-rmp-record" type="button"> Confirm</button>
                        </div>
                    </div>
                    <div class="search-box risk-tabs custm-tabs">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h3 class="m-t-0 m-b-20 clr-blue">Search</h3>
                        </div>
                        <!-- <div class="col-md-12 col-sm-12 col-xs-12 p-0 type-field">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Type: </label>
                                    <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15">
                                        <div class="select-style">
                                            <select name="dr_search_type" id="search_type">
                                                <option value="">Select</option>
                                                <option value='1'> Title </option>
                                                <option value='2'> Date </option>
                                            </select>
                                        </div>
                                        <input type="text" name="search_daily_record" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 p-0 search_title">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 cus-lbl"> Title: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12 m-b-15 title">
                                        <input type="text" name="search_rmp_record" class="form-control" maxlength="255">
                                    </div>
                                </div> -->
                        <div class="col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd search_date">
                            <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Date: </label>
                            <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15">

                                <input name="search_rmp_date" type="date" size="45" class="form-control">
                                <!-- <span class="input-group-btn add-on">
                                                <button class="btn clndr btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                            </span> -->

                            </div>
                        </div>
                        @include('frontEnd.common.popup_alert_messages')
                        <form id="searched-rmp-records-form" method="post">
                            <div class="modal-space modal-pading searched-records text-center">
                                <!--searched Record List using ajax -->
                            </div>
                        </form>
                        <div class="modal-footer m-t-0 recent-task-sec">
                            <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                Cancel </button>
                            <button class="btn btn-warning search-rmp-btn" type="button"> Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- RMP Plan Add End -->



<!-- View/Edit RMP Plan -->
<div class="modal fade" id="rmpModalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <a class="close mdl-back-btn" id="vw-r-pln-bkc-btn" href="" data-toggle="modal"
                    data-dismiss="modal" aria-hidden="true">
                    <i class="fa fa-arrow-left" title=""></i>
                </a>
                <h4 class="modal-title">{{ $labels['rmp']['label'] }} - View </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                        <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Child: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12">
                            <div class="select-style">
                                <select name="su_id" disabled="disabled">
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                        <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                        <div class="col-md-11 col-sm-11 col-xs-12">
                            <div class="select-style">
                                <select disabled="disabled">
                                    <option>{{ $labels['rmp']['label'] }}</option>
                                </select>
                            </div>
                            <p class="help-block"></p>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="below-divider"></div>
                    </div>

                    <!-- alert messages -->
                    @include('frontEnd.common.popup_alert_messages')

                    <!-- Add new Details -->
                    <div class="risk-tabs">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h3 class="m-t-0 m-b-20 clr-blue"> RMP Details </h3>
                        </div>
                        <form method="post" action="" id="edit_rmp_form">
                            <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                    <label class="col-md-1 col-sm-1 col-xs-12"> Title: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12 r-p-0 p-l-30">
                                        <div class="input-group popovr">
                                            <input name="edit_rmp_title" value=""
                                                class="form-control v-rmp_title" type="text"
                                                maxlength="255"><!-- v-rmp_title -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Sent To: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12 r-p-0 p-l-30">
                                        <div class="select-style">
                                            <select name="edit_sent_to">
                                                <option value="0">All contact</option>
                                                <option value="1">staff</option>
                                                <option value="2">relative</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="edit-dynamic-rmp-form-fields">
                                <!-- created form fields from controller will be placed here -->
                            </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer m-t-0">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="su_rmp_id" value="">
                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel
                </button>
                <button class="btn btn-warning sbt_edit_rmp_btn" id="vw-sbt-rmp-plan" type="button"
                    data-dismiss="modal" aria-hidden="true"> Continue </button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- View/Edit RMP Plan End -->

<script>
    // open rmp modal on click
    $(document).ready(function() {
        // $(document).on('click', '.rmp_plan_modal', function() {
        //     $('#rmpModal').modal('show');
        //     // $('input[name=\'search_rmp_record\']').val('');
        // });
        // FOR rmp back btn while view/edit
        $(document).on('click', '.view-rmp-back-btn', function() {
            $('#rmpModal').modal('show');
        });
        // FOR bmp/rmp back btn while view/edit
        $(document).on('click', '.plan-back-btn2', function() {
            $('#PlanRecordModal').modal('show');
        });
        // For rmp view modal submit
        $(document).on('click', '.sbt-rmp-back-btn', function() {
            $('#rmpModal').modal('show');
        });
        //For bmp/rmp view modal submit
        $(document).on('click', '.sbt-plan-back-btn', function() {
            $('#PlanRecordModal').modal('show');
        });
        $('#rmpModal').on('scroll', function() {
            $('.dpYears').datepicker('place')
        });
    });
</script>

<script>
    //logged view rmp title show
    $(document).ready(function() {
        // $('.logged_rmp_btn').on('click', function() {

        $('.loader').show();
        $('body').addClass('body-overflow');

        var service_user_id = "{{ $service_user_id }}";
        $.ajax({
            type: 'get',
            url: "{{ url('/service/rmp/view') }}" + '/' + service_user_id,
            success: function(resp) {
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                if (resp == '') {
                    $('.logged-rmp-plan-shown').html(
                        '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                    );
                } else {
                    $('.logged-rmp-plan-shown').html(resp);
                }

                // $('.logged-rmp-plan-shown').html(resp);
                $('.loader').hide();
                $('body').removeClass('body-overflow');
            }
        });
        return false;
        // });

    });

    function showDate() {
        $('#date_range_input').click();
    }

    $(function() {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            }
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end
                .format('YYYY-MM-DD'));
        });
    });
</script>

<script>
    //add new rmp
    $(document).ready(function() {
        $('.sbt_rmp_btn').on('click', function() {
            // alert('y'); return false;
            //var rmp_form_title = $('input[name=\'rmp_title_name\']').val();
            var rmp_form_title = $('#rmp_title').val();
            //alert(rmp_form_title); return false;
            error = 0;
            rmp_form_title = jQuery.trim(rmp_form_title);
            if (rmp_form_title == '') {
                $('input[name=\'rmp_title_name\']').addClass('red_border');
                error = 1;
            } else {
                $('input[name=\'rmp_title_name\']').removeClass('red_border');
            }
            if (error == 1) {
                return false;
            }

            var formdata = $('#rmp_form').serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/rmp/add') }}",
                data: formdata,
                dataType: 'json',
                success: function(resp) {
                    // alert(resp); return false;
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == '1') {
                        $('.dynamic-rmp-form-fields').find('input').val('');
                        $('.dynamic-rmp-form-fields').find('textarea').val('');
                        $('input[name=\'rmp_title_name\']').val('');

                        //show success message
                        $('span.popup_success_txt').text('RMP Details Added Successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                    } else {
                        $('span.popup_error_txt').text(
                            'Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>

<script>
    //remove rmp record
    $(document).ready(function() {
        $(document).on('click', '.delete_rmp', function() {
            //alert(1); return false;
            var su_rmp_id = $(this).attr('su_rmp_id');

            var this_record = $(this);
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/rmp/delete/') }}" + '/' + su_rmp_id,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    if (resp == 1) {
                        this_record.closest('.delete-row').remove();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        //show success delete message
                        $('span.popup_success_txt').text('RMP Deleted Successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);

                    } else {

                        //show delete message error
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');
                    }
                }
            });
            return false;
        });
    });
</script>

<script>
    //making editable click on edit 
    $(document).ready(function() {
        $(document).on('click', '.edit_rmp_details', function() {
            var su_rmp_id = $(this).attr('su_rmp_id');

            $('.edit_rmp_details_' + su_rmp_id).removeAttr('disabled');
            $('.edit_rmp_review_' + su_rmp_id).removeAttr('disabled');
            $('.edit_rmp_plan_' + su_rmp_id).removeAttr('disabled');
            $('.edit_rmp_id_' + su_rmp_id).removeAttr('disabled');
            $(this).closest('.cog-panel').find('.input-plusbox').toggle();
        });
    });

    function getFormData(data) {
        var service_user_id = "{{ $service_user_id }}";
        $.ajax({
            type: 'post',
            url: "{{ url('/service/rmp/view') }}" + '/' + service_user_id,
            data: data,
            success: function(resp) {
                console.log(resp)
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                console.log("resp from the ", resp);
                if (resp == '') {
                    $('.logged-rmp-plan-shown').html(
                        '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                    );
                } else {
                    $('.logged-rmp-plan-shown').html("");
                    $('.logged-rmp-plan-shown').html(resp);
                }
            }
        });
    }
</script>

<!-- Daterange Filter -->
<script>
    $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
        let staff_member = $('#staff_member').val();
        let start_date = picker.startDate.format('DD-MM-YYYY');
        let end_date = picker.endDate.format('DD-MM-YYYY');
        let service_user = $('#service_user').val();
        let keyword = $('#keyword').val();
        $(this).val(start_date + ' - ' + end_date);

        let today = new Date;
        let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) +
            "-" +
            today.getFullYear();

        if (start_date == todayFormat && end_date == todayFormat) {
            $('#today').text('Today');
        } else {
            $('#today').text(start_date + ' - ' + end_date);
        }

        let category_id = $("#select_category").val();

        if (category_id && category_id != 'all')
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': picker.startDate.format('YYYY-MM-DD'),
                'end_date': picker.endDate.format('YYYY-MM-DD'),
                'category_id': category_id,
                'filter': 1,
                'keyword': keyword
            };
        else
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': picker.startDate.format('YYYY-MM-DD'),
                'end_date': picker.endDate.format('YYYY-MM-DD'),
                'filter': 1,
                'keyword': keyword
            };


        getFormData(data);
        return false;

    });
</script>

<!-- {{-- Filter for child  --}} -->
<script type="text/javascript">
    $('#service_user').change(function() {
        let staff_member = $('#staff_member').val();
        let service_user = $('#service_user').val();
        let category_id = $('#select_category').val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        if (category_id && category_id != 'all')
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': start_date.format('YYYY-MM-DD'),
                'end_date': end_date.format('YYYY-MM-DD'),
                'category_id': category_id,
                'filter': 1,
                'keyword': keyword
            };
        else
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': start_date.format('YYYY-MM-DD'),
                'end_date': end_date.format('YYYY-MM-DD'),
                'filter': 1,
                'keyword': keyword
            };
        getFormData(data);
        return false;

    });
</script>

<!-- {{-- Filter for keyword --}} -->
<script>
    function myFunctionkey() {
        let staff_member = $('#staff_member').val();
        let service_user = $('#service_user').val();
        let category_id = $('#select_category').val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        // alert(keyword)
        if (category_id && category_id != 'all')
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': start_date.format('YYYY-MM-DD'),
                'end_date': end_date.format('YYYY-MM-DD'),
                'category_id': category_id,
                'filter': 1,
                'keyword': keyword
            };
        else
            data = {
                'staff_member': staff_member,
                'service_user': service_user,
                'start_date': start_date.format('YYYY-MM-DD'),
                'end_date': end_date.format('YYYY-MM-DD'),
                'filter': 1,
                'keyword': keyword
            };
        getFormData(data);
        return false;
    }
</script>

<script>
    //saving editable record in rmp
    $(document).ready(function() {

        $(document).on('click', '.sbt-edit-rmp-record', function() {
            var enabled = 0;
            $('.logged-rmp-plan-shown .edit_rcrd').each(function(index) {
                var is_disable = $(this).attr('disabled');
                if (is_disable == undefined) {
                    enabled = 1;
                }
            });
            if (enabled == 0) {
                return false;
            }
            //var service_user_id = "{{ $service_user_id }}";
            var formdata = $('#edit-rmp-form').serialize();
            console.log("formdata", formdata);
            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/rmp/edit') }}",
                data: formdata,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    $('.logged-rmp-plan-shown').html(resp);
                    $('span.popup_success_txt').text('Updated Successsfully');
                    $('.popup_success').show();
                    setTimeout(function() {
                        $(".popup_success").fadeOut()
                    }, 5000);

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>


<script>
    // view details of rmp form
    $(document).ready(function() {
        $(document).on('click', '.rmp_view', function() {

            var view_btn = $(this);
            var su_rmp_id = view_btn.attr('su_rmp_id');


            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/rmp/view_rmp/') }}" + '/' + su_rmp_id,
                dataType: 'json',
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == true) {
                        var su_rmp_id = resp['su_rmp_id'];
                        var rmp_form_title = resp['rmp_title_name'];
                        var rmp_form = resp['rmp_form'];
                        var rmp_sent_to = resp['rmp_sent_to'];

                        $('input[name=\'su_rmp_id\']').val(su_rmp_id);
                        $('input[name=\'edit_rmp_title\']').val(rmp_form_title);
                        $('select[name=\'edit_sent_to\']').val(rmp_sent_to);
                        $('.edit-dynamic-rmp-form-fields').html(rmp_form);

                        $('.dpYears').datepicker({
                            //format: 'dd/mm/yyyy',
                        }).on('changeDate', function(e) {
                            $(this).datepicker('hide');
                        });
                        $('#rmpModalView').on('scroll', function() {
                            $('.dpYears').datepicker('place')
                        });

                        var model_name = $(view_btn).closest('.my_plan_model').attr('id');

                        if (model_name == 'PlanRecordModal') {

                            $('#vw-r-pln-bkc-btn').attr('class',
                                'close mdl-back-btn plan-back-btn2');
                            $('#vw-sbt-rmp-plan').attr('class',
                                'btn btn-warning sbt_edit_rmp_btn sbt-plan-back-btn');


                            // if($('.logged-plan-btn').hasClass('active')){
                            //     alert('log');
                            // } else{
                            //     alert('src');
                            // }

                        } else if (model_name == 'rmpModal') {
                            $('#vw-r-pln-bkc-btn').attr('class',
                                'close mdl-back-btn view-rmp-back-btn');
                            $('#vw-sbt-rmp-plan').attr('class',
                                'btn btn-warning sbt_edit_rmp_btn sbt-rmp-back-btn');

                        }


                        // $('#rmpModal').modal('hide');
                        $('#rmpModalView').modal('show');
                    } else {
                        $('span.popup_error_txt').text(
                            'Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>

<script>
    //save edit rmp record
    $(document).ready(function() {

        $(document).on('click', '.sbt_edit_rmp_btn', function() {

            var edit_rmp_title = $('input[name=\'edit_rmp_title\']').val();
            var su_rmp_id = $('input[name=\'su_rmp_id\']').val();

            error = 0;
            edit_rmp_title = jQuery.trim(edit_rmp_title);
            if (edit_rmp_title == '' || edit_rmp_title == null) {
                $('input[name=\'edit_rmp_title\']').addClass('red_border');
                error = 1;
            } else {
                $('input[name=\'edit_rmp_title\']').removeClass('red_border');
            }
            if (error == 1) {
                return false;
            }
            var formdata = $('#edit_rmp_form').serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/rmp/edit_rmp/') }}" + '/' + su_rmp_id,
                data: formdata,
                dataType: 'json',
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == '1') {
                        $('#rmpModalView').modal('hide');

                        //know which tab is currently active logged or search  rmp tab
                        if ($('.logged_rmp_btn').hasClass('active')) {
                            $('.logged_rmp_btn').click();
                        } else {
                            update_search_list();
                        }
                        // know which tab is currently active logged or search  bmp/rmp in daily record tab
                        if ($('.rmp-plan-record').hasClass('active')) {
                            $('.rmp-plan-record').click();
                        } else {
                            $('.search-bmp-rmp-btn').click();
                        }

                        // $('#rmpModal').modal('show');

                        //show success message
                        $('span.popup_success_txt').text(
                            'RMP Details Editted Successfully.');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $(".popup_success").fadeOut()
                        }, 5000);
                    } else {
                        $('span.popup_error_txt').text(
                            'Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
        });

        //when enter press on search box
        $('input[name=\'search_rmp_record\']').keydown(function(event) {
            var keyCode = (event.keyCode ? event.keyCode : event.which);
            if (keyCode == 13) {
                $('.search-rmp-btn').click();
                return false;
            }
        });

        //when rmp search confirm button is clicked
        $(document).on('click', '.search-rmp-btn', function() {

            update_search_list()
            return false;
        });

        function update_search_list() {

            // var searchType = document.getElementById('search_type').value;
            var searchType = 2;
            if (searchType == 1) {
                var search_input = $('input[name=\'search_rmp_record\']');
                var search = search_input.val();
            } else if (searchType == 2) {
                var search_input = $('input[name=\'search_rmp_date\']');
                var search = search_input.val();
            }

            // var search_input = $('input[name=\'search_rmp_record\']');
            // var search = search_input.val();

            search = jQuery.trim(search);
            search = search.replace(/[&\/\\#,+()$~%.'":*?<>^@{}]/g, '');

            if (search == '') {
                search_input.addClass('red_border');
                return false;
            } else {
                search_input.removeClass('red_border');
            }

            var formdata = $('#searched-rmp-records-form').serialize();
            //alert(formdata); //return false;
            var service_user_id = "{{ $service_user_id }}";

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: "{{ url('/service/rmp/view/') }}" + '/' + service_user_id + '?search=' + search +
                    '&searchType=' + searchType,
                data: formdata,
                success: function(resp) {
                    console.log(resp);
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.searched-records').html('No Records found.');
                    } else {
                        $('.searched-records').html(resp);
                    }
                    //$('input[name=\'search_rmp_record\']').val('');
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        }

    });
</script>

<script>
    // pagination in rmp
    $(document).ready(function() {
        //$(document).on('click','.rmp_paginate .pagination li', function(){
        //logged-rmp-plan-shown
        //var new_url = $(this).children('a').attr('href');
        //var service_user_id = "{{ $service_user_id }}";
        //alert(new_url); return false;
        // if(new_url == undefined) {
        //     return false;
        // }

        $(document).on('click', '#rmpModal .pagination li', function() {

            var page_no = $(this).children('a').text();

            if (page_no == '') {
                return false;
            }

            if (isNaN(page_no)) {
                var new_url = $(this).children('a').attr('href');
                page_no = new_url[new_url.length - 1];
            }

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                //url  : new_url,
                url: "{{ url('service/rmp/view/') }}" + '/' + "{{ $service_user_id }}" +
                    "?page=" + page_no,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    //alert(resp);
                    $('.logged-rmp-plan-shown').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>
@endsection