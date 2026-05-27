@extends('frontEnd.layouts.master')
@section('title', 'Missing/Absent From Care')
@section('content')
    <link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">
    <style>
        .record_row .input-group {
            display: flex;
        }

        /* .record_row .input-group input{
                    width: 50%;
                        margin: 3px 0;
                } */
        .accodionOpen {
            background: #a5a5a5;
            padding: 6px 20px;
            width: 100%;
            display: block;
        }
    </style>

    <section id="container">
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="pull-right">
                        <div class="filter_buttons"
                            style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                            <a data-toggle="modal" href="#mfcModal" class="btn btn-primary col-6" id=''>Add New</a>
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
                        <input type="text" class="form-control" id="keywordhr" onKeyPress="mfcMyFunctionkey()"
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
                            <div class="mfc-logged-btn">
                                <div class="modal-space modal-pading logged-mfc-list">
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

    <!-- mfc Modal -->
    <div class="modal fade" id="mfcModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{{ $labels['mfc']['label'] }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{-- <div class="form-group col-md-12 col-sm-12 col-xs-12 serch-btns text-right">
                            <button class="btn label-default add-new-btn active" type="button"> Add New </button>
                            <button class="btn label-default logged-btn active mfc-logged-btn" type="button"> Logged
                                Records </button>
                            <button class="btn label-default search-btn active" type="button"> Search </button>
                        </div> --}}

                        <div class="add-new-box risk-tabs custm-tabs">
                            <form method="post" action="" id="mfc_form">

                                <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">

                                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                                                                <div class="col-md-11 col-sm-11 col-xs-12">
                                                                <div class="select-bi" style="width:100%;float:left;">
                                                                    <?php
                                                                    $mfc_options = App\MFC::where('home_id', Auth::user()->home_id)
                                                                        ->where('status', '1')
                                                                        ->where('is_deleted', '0')
                                                                        ->orderBy('id', 'desc')
                                                                        ->get()
                                                                        ->toArray();
                                                                    ?>
                                                                    <select class="js-example-placeholder-single-mfc form-control" style="width:100%;" name="mfc_id">
                                                                        <option value=""></option>
                                                                        @foreach ($mfc_options as $value)
    <option value="{{ $value['id'] }}">{{ $value['description'] }}</option>
    @endforeach
                                                                    </select>
                                                                </div>
                                                                    <p class="help-block"> Select MFC. </p>
                                                                </div>
                                                        </div> -->

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Child: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12">
                                        <div class="select-style">
                                            <select name="service_user_id" class="su_n_id" disabled="">
                                                <option value="0"> Select Child </option>
                                                @foreach ($service_users as $value)
                                                    <option value="{{ $value['id'] }}"
                                                        {{ $service_user_id == $value['id'] ? 'selected' : '' }}>
                                                        {{ ucfirst($value['name']) }}</option>
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
                                            $this_location_id = App\DynamicFormLocation::getLocationIdByTag('mfc');
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

                                <!--<div class="col-md-12 col-sm-12 col-xs-12">-->
                                <!--    <div class="below-divider"></div>-->
                                <!--</div>-->

                                <!-- alert messages -->
                                @include('frontEnd.common.popup_alert_messages')

                                <!-- <form method="post" action="{{ url('/service/daily-record/edit') }}" id="edit_record_form"> -->
                                <!-- risk-tabs -->
                                <!-- <div class="">
                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20">Details</h3>
                                                            </div>
                                                            <div class="dynamic-mfc-fields modal-space ">
                                                                    $form_pattern['su_mfc']
                                                            </div>
                                                        </div> -->
                                <div class="dynamic-form-fields"> </div>

                                <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="uploadPopImg mt-0 hideImageDiv"><img class="my-2 imagePreview" src="" width="100px"></div>
                                                    </div> -->

                                <div class="form-group modal-footer m-t-0 modal-bttm">
                                    <!-- <a class="bottm-btns" href="{{ url('/service/calendar/' . $service_user_id) }}" ><div class="pull-left"><i class="fa fa-calendar"></i></div></a> -->
                                    <input type='hidden' name='service_user_id' value='{{ $service_user_id }}'>
                                    <input type="hidden" name="location_id" value="{{ $this_location_id }}">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="btn btn-default" type="button" data-dismiss="modal"
                                        aria-hidden="true"> Cancel </button>
                                    <button class="btn btn-warning sbt-dyn-form-btn " type="submit"> Submit </button>
                                    <!-- sbmt-mfc-btn -->
                                </div>
                            </form>
                        </div>

                        <!-- logged plans -->
                        {{-- <div class="logged-box risk-tabs custm-tabs">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Logged Records </h3>
                            </div>
                            <!-- alert messages -->
                            @include('frontEnd.common.popup_alert_messages')
                            <form id="edit-daily-logged-form">
                                <div class="modal-space modal-pading logged-plan-shown logged-mfc-list">
                                    <!-- logged risk list be shown here using ajax -->
                                </div>
                            </form>
                            <div class="modal-footer m-t-0 recent-task-sec" style="visibility: hidden;">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                    Cancel </button>
                                <button class="btn btn-warning logged_daily_record_btn" type="button"> Confirm</button>
                            </div>
                        </div> --}}

                        <div class="search-box risk-tabs custm-tabs">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20">Search</h3>
                            </div>
                            <!-- <div class="col-md-12 col-sm-12 col-xs-12 p-0 type-field">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Type: </label>
                                    <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15">
                                        <div class="select-style">
                                            <select name="mfc_search_type">
                                                <option value='title' selected=""> Title </option>
                                                <option value='date'> Date </option>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                            <!-- <div class="col-md-12 col-sm-12 col-xs-12 p-0 srch-field">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Title: </label>
                                    <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15 title">
                                        <input type="text" name="search_mfc_record" class="form-control" maxlength="255">
                                    </div>
                                </div> -->
                            <div class="col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd srch-field">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 cus-lbl"> Date: </label>
                                <div class="col-md-9 col-sm-9 col-xs-12 p-0 m-b-15">
                                    <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""
                                        class="input-group date dpYears">
                                        <input name="mfc_date" type="text" value="" size="45"
                                            class="form-control" readonly="" maxlength="10">
                                        <span class="input-group-btn add-on">
                                            <button class="btn clndr btn-primary" type="button"><i
                                                    class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <form id="srchd-mfc-rcrds-form" method="post">
                                <div class="modal-space modal-pading srchd-mfc-rcrds text-center">
                                    <!--searched Record List using ajax -->
                                </div>
                            </form>
                            <div class="modal-footer m-t-0 recent-task-sec">
                                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true">
                                    Cancel </button>
                                <button class="btn btn-warning search_mfc_rcrd_btn" type="button"> Confirm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW MFC RECORD model -->
    <div class="modal fade" id="veMFCModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <a class="close view-mfc-back-btn" href="" data-toggle="modal" data-dismiss="modal"
                        data-target="" style="font-size:18px; padding-right:8px;">
                        <i class="fa fa-arrow-left" title=""></i>
                    </a>
                    <h4 class="modal-title"> {{ $labels['mfc']['label'] }} </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                            <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Add: </label>
                            <div class="col-md-11 col-sm-10 col-xs-10">
                                <input type="text" name="" value="" class="form-control ve-mfc"
                                    disabled="" maxlength="255">
                                <p class="help-block"> Selected MFC. </p>
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
                                <h3 class="m-t-0 m-b-20 clr-blue fnt-20"> Details </h3>
                            </div>
                            <form method='post' id='edit_mfc_form'>
                                <div class="ve-dynamic-mfc-fields modal-space">

                                </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-0">
                    <input type='hidden' name='service_user_id' value='{{ $service_user_id }}'>
                    <input type='hidden' name='su_mfc_id' value=''>
                    <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel
                    </button>
                    <button class="btn btn-warning sbmt_editd_mfc_btn" type="button"> Continue </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).on('click', '.sbmt_editd_mfc_btn', function() {

            var su_mfc_id = $('.ve-mfc-rcrd').attr('su_mfc_id');
            $('input[name=\'su_mfc_id\']').val(su_mfc_id);
            var formdata = $('#edit_mfc_form').serialize();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'post',
                url: '{{ url('service/mfc/edit') }}' + '/' + su_mfc_id,
                data: formdata,
                dataType: 'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    if (response == '1') {
                        $('.ve-dynamic-mfc-fields').find('input').val('');
                        $('.ve-dynamic-mfc-fields').find('select').val('');

                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        $('span.popup_success_txt').text('MFC Editted Successfully');
                        $('.popup_success').show();
                        setTimeout(function() {
                            $('.popup_success').fadeOut()
                        }, 10000);

                        $('#mfcModal').modal('show');
                        $('.mfc-logged-btn').click();
                        $('#veMFCModal').modal('hide');
                    } else {
                        $('span.popup_error_txt').text(COMMON_ERROR);
                        $('.popup_error').show();
                        setTimeout(function() {
                            $('.popup_error').fadeOut()
                        }, 5000);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
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
        $(document).ready(function() {

            $('input[name=\'search_mfc_record\']').keydown(function(event) {
                var keyCode = (event.keyCode ? event.keyCode : event.which);
                if (keyCode == 13) {
                    $('.search_mfc_rcrd_btn').click();
                    return false;
                }
            });

            $('select[name=\'mfc_search_type\']').change(function() {

                $('.srchd-mfc-rcrds').html('');
                var dr_src_title = $('input[name=\'search_mfc_record\']');
                var dr_src_date = $('input[name=\'mfc_date\']');

                var type = $(this).val();
                if (type == 'date') {

                    dr_src_date.closest('.srch-field').show();
                    dr_src_date.removeClass('red_border');
                    dr_src_title.closest('.srch-field').hide();

                    /*--- used to initalize calendar specially for EarningScheme webpage ----*/
                    $('.dpYears').datepicker({}).on('changeDate', function(e) {
                        $(this).datepicker('hide');
                    });
                } else {
                    dr_src_title.closest('.srch-field').show();
                    dr_src_title.removeClass('red_border');
                    dr_src_date.closest('.srch-field').hide();
                }
            });

            // $(document).on('click', '.search_mfc_rcrd_btn', function() {

            //     var mfc_search_type = $('select[name=\'mfc_search_type\']');
            //     var search_input = $('input[name=\'search_mfc_record\']');
            //     var mfc_search_date = $('input[name=\'mfc_date\']');

            //     var search = search_input.val();

            //     var mfc_date = mfc_search_date.val();
            //     var mfc_search_type = mfc_search_type.val();
            //     var mfc_search_type = 2;

            //     search = jQuery.trim(search);
            //     search = search.replace(/[&\/\\#,+()$~%.'":*?<>^@{}]/g, '');

            //     if (mfc_search_type == 'title') {
            //         if (search == '') {
            //             search_input.addClass('red_border');
            //             return false;
            //         } else {
            //             search_input.removeClass('red_border');
            //         }
            //     } else {
            //         if (mfc_date == '') {
            //             mfc_search_date.addClass('red_border');
            //             return false;
            //         } else {
            //             mfc_search_date.removeClass('red_border');
            //         }

            //     }
            //     //for editing functionality
            //     //check validations
            //     var error = 0;
            //     //var enabled = 0;
            //     $('.srchd-mfc-rcrds .edit_mfc_rcrd').each(function(index) {
            //         var is_disable = $(this).attr('disabled');
            //         if (is_disable == undefined) { //if it is not disabled
            //             var title = $(this).val();
            //             title = jQuery.trim(title);

            //             if (title == '' || title == '0') {
            //                 $(this).addClass('red_border');
            //                 error = 1;
            //             } else {
            //                 $(this).removeClass('red_border');
            //             }
            //             //enabled = 1;
            //         }
            //     });
            //     if (error == 1) {
            //         return false;
            //     }
            //     /*if(enabled == 0){
            //         return false;
            //     }*/
            //     var formdata = $('#srchd-mfc-rcrds-form').serialize();
            //     var service_user_id = $('.selected_su_id').val();
            //     if (service_user_id == undefined) {
            //         service_user_id = "{{ $service_user_id }}";
            //     }

            //     $('.loader').show();
            //     $('body').addClass('body-overflow');

            //     $.ajax({
            //         type: 'post',
            //         url: "{{ url('/service/mfc-records') }}" + '/' + service_user_id + '?search=' +
            //             search + '&mfc_date=' + mfc_date + '&mfc_search_type=' + mfc_search_type,
            //         data: formdata,
            //         success: function(resp) {
            //             if (isAuthenticated(resp) == false) {
            //                 return false;
            //             }
            //             if (resp == '') {

            //                 $('.srchd-mfc-rcrds').html('No Records found.');
            //             } else {

            //                 $('.srchd-mfc-rcrds').html(resp);
            //             }
            //             $('input[name=\'search\']').val('');
            //             $('.loader').hide();
            //             $('body').removeClass('body-overflow');
            //         }
            //     });
            //     return false;
            // });
        });
    </script>
    <script>
        $(document).ready(function() {

            //MFC Modal Open
            $('.mfc').click(function() {
                //alert(1); return false;
                var service_user_id = "{{ $service_user_id }}";

                $('.loader').show();
                $('body').addClass('body-overflow');

                $('.dynamic-mfc-fields').find('input').val('');
                $('#mfcModal').modal('show');

                $('.loader').hide();
                $('body').removeClass('body-overflow');
                return false;
            });

            // View Previous MFC Records
            // $('.mfc-logged-btn').click(function() {

            var service_user_id = $('.selected_su_id').val();
            if (service_user_id == undefined) {
                service_user_id = "{{ $service_user_id }}";
            }

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/mfc-records') }}" + '/' + service_user_id,
                // dataType:'json',
                success: function(resp) {

                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == '') {
                        $('.logged-mfc-list').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
                    } else {
                        $('.logged-mfc-list').html(resp);
                    }

                    // $('.logged-mfc-list').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            // });

            // Delete MFC Record
            $(document).on('click', '.delete-mfc-rcrd', function() {

                var su_mfc_id = $(this).attr('su_mfc_id');

                $(this).addClass('active_record');
                var mfc_token = $('input[name=\'_token\']').val();

                $('.loader').show();
                $('body').addClass('body-overflow');

                $.ajax({
                    type: 'post',
                    url: "{{ url('/service/mfc/delete') }}" + '/' + su_mfc_id,
                    data: {
                        'su_mfc_id': su_mfc_id,
                        '_token': mfc_token
                    },
                    success: function(resp) {

                        if (isAuthenticated(resp) == false) {
                            return false;
                        }

                        if (resp == 1) {
                            $('.active_record').closest('.record_row').html('');

                            //show delete message
                            $('span.popup_success_txt').text('Record Deleted Successsfully');

                            $('.loader').hide();
                            $('body').removeClass('body-overflow');

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
            });

            // View MFC Record
            $(document).on('click', '.ve-mfc-rcrd', function() {

                var su_mfc_id = $(this).attr('su_mfc_id');

                $('.loader').show();
                $('body').addClass('body-overflow');
                $.ajax({
                    type: 'get',
                    url: "{{ url('/service/mfc/view') }}" + '/' + su_mfc_id,
                    dataType: 'json',
                    success: function(resp) {

                        if (isAuthenticated(resp) == false) {
                            return false;
                        }
                        var response = resp['response'];
                        if (response == true) {

                            var su_mfc_id = resp['su_mfc_id'];
                            var su_mfc_description = resp['su_mfc_description'];
                            var su_mfc_form = resp['su_mfc_form'];

                            //$('.ve-mfc').find('option').text(su_mfc_description);
                            $('.ve-mfc').val(su_mfc_description);
                            $('input[name=\'su_mfc_id\']').val(su_mfc_id);

                            $('.ve-dynamic-mfc-fields').html(su_mfc_form);
                            $('#mfcModal').modal('hide');
                            $('#veMFCModal').modal('show');

                            $('.dpYears').datepicker({
                                //format: 'dd/mm/yyyy',
                            }).on('changeDate', function(e) {
                                $(this).datepicker('hide');
                            });

                        } else {
                            $('span.popup_error_txt').text('COMMON_ERROR.');
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

            // Modal Back Button
            $('.view-mfc-back-btn').click(function() {

                $('#veMFCModal').modal('hide');
                $('#mfcModal').modal('show');
                $('.mfc-logged-btn').click();
            });

        });
    </script>

    <script>
        //  Saving new MFC - Record
        $(document).ready(function() {
            $(document).on('click', '.sbmt-mfc-btn', function() {

                var service_user_id = "{{ $service_user_id }}";
                var mfc_id = $('select[name=\'mfc_id\']').val();
                var mfc_token = $('input[name=\'_token\']').val();

                var model_id = $(this).closest('.modal').attr('id');
                var form_builder = $('#' + model_id + ' .dynamic_form_select');
                //var service_user_id = service_user.val().trim();   
                var form_builder_id = form_builder.val().trim();
                var err = 0;

                if (form_builder_id == 0) {
                    form_builder.parent().addClass('red_border');
                    err = 1;
                } else {
                    form_builder.parent().removeClass('red_border');
                }

                if (mfc_id == '') {
                    $('select[name=\'mfc_id\']').parent().addClass('red_border');
                    return false;
                } else {
                    $('select[name=\'mfc_id\']').parent().removeClass('red_border');
                }

                var formdata = $('#mfc_form').serialize();

                $('.loader').show();
                $('body').addClass('body-overflow');

                $.ajax({
                    type: 'post',
                    /*url  : "{{ url('/service/mfc/add') }}",*/
                    url: "{{ url('/service/dynamic-form/save') }}",
                    data: formdata,
                    dataType: 'json',
                    success: function(resp) {

                        if (isAuthenticated(resp) == false) {
                            return false;
                        }

                        if (resp == true) {
                            $('span.popup_success_txt').text('MFC has been Added Successfully');
                            $('.popup_success').show();
                            setTimeout(function() {
                                $(".popup_success").fadeOut()
                            }, 5000);
                            $('#' + model_id + ' .dynamic_form_select').val('0');

                            $('#' + model_id + ' .dynamic-form-fields').html('');

                            $(".js-example-placeholder-single-mfc").select2({
                                dropdownParent: $('#mfcModal'),
                                placeholder: "Select Description"
                            });

                        } else {
                            $('span.popup_error_txt').text("{{ COMMON_ERROR }}");
                            $('.popup_error').show();
                            setTimeout(function() {
                                $(".popup_error").fadeOut()
                            }, 5000);
                        }

                        //loader
                        $('.loader').hide();
                        $('body').removeClass('body-overflow');

                        // $('#mfcModal').modal('hide');
                        // $('.dynamic-mfc-fields').find('input').val('');
                        // $('.dynamic-mfc-fields').find('textarea').val('');
                    }
                });
                return false;
            });
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $(".js-example-placeholder-single-mfc").select2({
                dropdownParent: $('#mfcModal'),
                placeholder: "Select Description"
            });
        });
    </script>

    <script>
        //pagination of MFC
        $(document).on('click', '.mfc_paginate .pagination li', function() {

            var new_url = $(this).children('a').attr('href');
            if (new_url == undefined) {
                return false;
            }

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: new_url,
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }

                    $('.logged-mfc-list').html(resp);
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    </script>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.daily-rcd-head', function() {
                $(this).next('.daily-rcd-content').slideToggle();
                $(this).find('i').toggleClass('fa-angle-down');
                $('.input-plusbox').hide();
            });
        });

        function getFormData(data) {

            var service_user_id = $('.selected_su_id').val();
            if (service_user_id == undefined) {
                service_user_id = "{{ $service_user_id }}";
            }
            $.ajax({
                type: 'post',
                url: "{{ url('/service/mfc-records') }}" + '/' + service_user_id,
                data: data,
                success: function(resp) {
                    console.log(resp)
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    console.log("resp from the ", resp);
                    if (resp == '') {
                        $('.logged-mfc-list').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
                    } else {
                        $('.logged-mfc-list').html("");
                        $('.logged-mfc-list').html(resp);
                    }
                }
            });
        }
    </script>

    <!-- Daterange Filter -->
    <script>
        $('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
            let start_date = picker.startDate.format('DD-MM-YYYY');
            let end_date = picker.endDate.format('DD-MM-YYYY');
            let service_user = "{{ $service_user_id }}";
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

            data = {
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

    <!-- {{-- Filter for keyword --}} -->
    <script>
        function mfcMyFunctionkey() {
            let service_user = "{{ $service_user_id }}";
            let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
            let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
            let keyword = $('#keyword').val();
            // alert(keyword)
                data = {
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

    <!-- <script>
        //3 tabs script
        $('.logged-box').hide();
        $('.search-box').hide();
        $('.logged-btn').removeClass('active');
        $('.search-btn').removeClass('active');

        $('.add-new-btn').on('click', function() {
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).closest('.modal-body').find('.add-new-box').show();
            $(this).closest('.modal-body').find('.add-new-box').siblings('.risk-tabs').hide();
        });
        $('.logged-btn').on('click', function() {
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).closest('.modal-body').find('.logged-box').show();
            $(this).closest('.modal-body').find('.logged-box').siblings('.risk-tabs').hide();
        });
        $('.search-btn').on('click', function() {
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).closest('.modal-body').find('.search-box').show();
            $(this).closest('.modal-body').find('.search-box').siblings('.risk-tabs').hide();
        });
    </script>
@endsection
