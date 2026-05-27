@extends('frontEnd.layouts.master')
@section('title', 'Risk')
@section('content')
    <style type="text/css">

        i.fontam {
            font-size: 25px
        }

        .space_bottom {
            margin-bottom: 7px !important;
        }
    </style>



    @php
        function time_diff_string($from, $to, $full = false)
        {
            $from = new DateTime($from);
            $to = new DateTime($to);
            $diff = $to->diff($from);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = [
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            ];
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) {
                $string = array_slice($string, 0, 1);
            }
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }
    @endphp

    <!--Core CSS -->
    <!-- <link href="{{ url('public/frontEnd/daily_logs/bs3/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ url('public/frontEnd/daily_logs/css/bootstrap-reset.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ url('public/frontEnd/daily_logs/font-awesome/css/font-awesome.css') }}" rel="stylesheet"  type="text/css"> -->

    <!-- Custom styles for this template -->
    <!-- <link href="{{ url('public/frontEnd/daily_logs/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ url('public/frontEnd/daily_logs/css/style-responsive.css') }}" rel="stylesheet" type="text/css"> -->

    <section id="container">

        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="pull-right">
                        <div class="filter_buttons"
                            style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                            <!-- <a data-toggle="modal" href="#riskDesc" class="btn btn-primary  col-6" id='add_new_log'>Add New</a>             -->
                            <!-- <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6" id='add_new_log' style="background-color:#d9534f;color:white;">PDF Export</a>             -->
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
                                    if ($service_user_id == $val->id) {
                                        echo 'Selected';
                                    }
                                } ?> value="{{ $val->id }}">{{ $val->name }}</option>
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
                    <!-- <div class="col-md-3 col-lg-2" style="padding-bottom:10px; margin-left: -84px;">
                    <div class="form-group datepicker-sttng date-sttng">
                        <label class="col-md-3 col-sm-1 col-xs-12 p-t-7" style="display: none;"> Category: </label>
                        <div class="col-md-7 col-sm-10 col-xs-12">
                            <select class="form-control" style="min-width:200px;" id="select_category" name="category_timeline" required />
                                 <option disabled value> -- select an option -- </option> --
                                <option selected value="all">All</option>
                               
                            </select>
                        </div>
                    </div>
                </div> -->
                    <!-- sourabh -->
                    <div class="col-md-2 col-lg-2" style="padding-bottom:10px; margin-left: 10px;">
                        <input type="text" class="form-control" id="keywordhr" onKeyUp="hrmyFunctionkey()"
                            name="keywordhr" placeholder="Keyword">
                    </div>
                    <!-- sourabh -->
                    <!-- <div class="col-md-4 filter_buttons" style="text-align:right;padding-right:150px;display:inline-block;">
                    <a data-toggle="modal" href="#addLogModal" class="btn btn-primary  col-6" id='add_new_log'>Add New</a>
                    <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6" id='add_new_log' style="background-color:#d9534f;color:white;">PDF Export</a>
                </div> -->
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
                            <div id="logs_articles">
                                @foreach ($log_book_records as $key)
                                    @if ($loop->iteration % 2 == 0)
                                        <article class="timeline-item">
                                            <div class="timeline-desk">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <span class="arrow"></span>

                                                        <span class="badge badge-pill red-bg"
                                                            style="position:absolute;right:30px;"></span>


                                                        @if ($key['status'] == 1)
                                                            <span data-toggle="tooltip" data-placement="right"
                                                                title="" class="timeline-icon orange-clr">
                                                            @elseif($key['status'] == 2)
                                                                <span data-toggle="tooltip" data-placement="right"
                                                                    title="" class="timeline-icon red-clr">
                                                                @else
                                                                    <span data-toggle="tooltip" data-placement="right"
                                                                        title=""
                                                                        class="timeline-icon darkgreen-clr">
                                                        @endif
                                                        <i class="{{ $key['icon'] }} fontam"></i>
                                                        </span>
                                                        <span class="time_abbre" data-toggle="tooltip"
                                                            data-placement="top"
                                                            title="{{ $key['created_at'] }}">{{ time_diff_string(date('d-m-Y H:i', strtotime($key['created_at'])), 'now') }}
                                                            <span style="color:black;font-weight:400;font-size:14px;">by
                                                                {{ $key['staff_name'] }}</span></span>
                                                              <span class="viewEditIcon">
                                                            <a href="#!" class="dyn-form-view-data" id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}"><i
                                                                    class="fa fa-eye"></i></a>
                                                            {{-- <a href="#!"><i class="fa fa-eye"></i></a> --}}
                                                        </span>
                                                        <!-- <h1 class="title_time_log"><span class="log_title">{{ $key['title'] or null }}</span></h1> -->
                                                        @if ($key['description'])
                                                            <h1 class="title_time_log"><span
                                                                    style="color:#1ca59e;">{{ $key['description'] }}</span>
                                                                | <span class="log_title">{{ $key['title'] }}</span></h1>
                                                        @else
                                                            <h1 class="title_time_log"><span
                                                                    class="log_title">{{ $key['title'] }}</span></h1>
                                                        @endif
                                                        <p class="space_bottom">{{ $key['details'] }}</p>
                                                        <p class="daily_log_time">
                                                            {{ $key['created_at'] }}

                                                        </p>
                                                        <h6 hidden>{{ $key['id'] }}</h6>



                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    @else
                                        <article class="timeline-item alt">
                                            <div class="timeline-desk">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <span class="arrow-alt"></span>
                                                        @if ($key['status'] == 1)
                                                            <span data-toggle="tooltip" data-placement="right"
                                                                title="" class="timeline-icon orange-clr">
                                                            @elseif($key['status'] == 2)
                                                                <span data-toggle="tooltip" data-placement="right"
                                                                    title="" class="timeline-icon red-clr">
                                                                @else
                                                                    <span data-toggle="tooltip" data-placement="right"
                                                                        title=""
                                                                        class="timeline-icon darkgreen-clr">
                                                        @endif
                                                        <i class="{{ $key['icon'] }} fontam"></i>
                                                        </span>
                                                        <span class="time_abbre" data-toggle="tooltip"
                                                            data-placement="top"
                                                            title="{{ $key['created_at'] }}">{{ time_diff_string(date('d-m-Y H:i', strtotime($key['created_at'])), 'now') }}
                                                            <span style="color:black;font-weight:400;font-size:14px;">by
                                                                {{ $key['staff_name'] }}</span></span>
                                                              <span class="viewEditIcon">
                                                            <a href="#!" class="dyn-form-view-data" id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}"><i
                                                                    class="fa fa-eye"></i></a>
                                                            {{-- <a href="#!"><i class="fa fa-eye"></i></a> --}}
                                                        </span>
                                                        @if ($key['description'])
                                                            <h1 class="title_time_log"><span
                                                                    style="color:#1ca59e;">{{ $key['description'] }}</span>
                                                                | <span class="log_title">{{ $key['title'] }}</span></h1>
                                                        @else
                                                            <h1 class="title_time_log"><span
                                                                    class="log_title">{{ $key['title'] }}</span></h1>
                                                        @endif
                                                        <!-- <h1 class="title_time_log"><span class="log_title">{{ $key['title'] or null }}</span></h1> -->

                                                        <p class="space_bottom">{{ $key['details'] }}</p>
                                                        <p class="daily_log_time">
                                                            {{ $key['created_at'] }}

                                                        </p>
                                                        <h6 hidden>{{ $key['id'] }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    @endif
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
                <!-- page end-->
            </section>
        </section>
        <!--main content end-->

    </section>
    <!-- risk view modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="riskViewModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                     <a href="#" data-toggle="modal" data-dismiss="modal" data-target="#riskDesc" class="close p-r-10" > <i class="fa fa-arrow-left"></i></a> -->
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <a class="close view-logged" href="" data-toggle="modal" data-dismiss="modal" data-target=""
                        style="font-size:18px; padding-right:8px;">
                        <i class="fa fa-arrow-left" title=""></i>
                    </a>

                    <h4 class="modal-title risk-desc-modal">Risk Details - View</h4>
                </div>
                <div class="modal-body p-b-0">
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">

                            <form id="change_risk_status_form1">

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Risk: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                        <input type="text" disabled="" class="form-control trans"
                                            name="v-risk_name" maxlength="255" />
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right">Changed to: </label>
                                    <div class="col-md-11 col-sm-11 col-xs-12 p-l-20">
                                        <div class="select-style">
                                            <select class="form-control new_risk_status trans" name="v-risk_new_status"
                                                disabled="">
                                                <option value="">Select Risk </option>
                                                <option value="0">No Risk </option>
                                                <option value="1">Historic </option>
                                                <option value="2">Live Risk </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 text-right"> Form: </label>
                                    <div class="col-md-11 col-sm-12 col-xs-12 p-l-20">
                                        <div class="select-style">
                                            <select name="dynamic_form_builder_id"
                                                class="dynamic_form_select form-control" disabled="">
                                                <option value="0"> Select Form </option>
                                                <?php

                                                $this_location_id = App\DynamicFormLocation::getLocationIdByTag('risk_change');
                                                foreach($dynamic_forms as $value) {
                                                
                                                    $location_ids_arr = explode(',',$value['location_ids']);

                                                    if(in_array($this_location_id,$location_ids_arr)) { 
                                                    ?>
                                                <option value="{{ $value['id'] }}"> {{ ucfirst($value['title']) }}
                                                </option>
                                                <?php } 
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="icon-div-icons pull-right p-r-15">
                                        <button class="btn btn-risk group-ico view-risk-rmp w-75" su_rmp_id=""
                                            name="View RMP"><i class=""></i>RMP</button>
                                        <button class="btn btn-risk group-ico ve-risk-inc-rep w-75" su_risk_id=""
                                            name="View Incident Report"><i class=""></i>Report</button>
                                        <a href="" title="Body Map" class="btn btn-risk group-ico body-map"><i
                                                class="fa fa-male"></i></a>
                                    </div>
                                </div>


                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div class="below-divider"></div>
                                </div>
                                <!-- alert messages -->
                                @include('frontEnd.common.popup_alert_messages')

                                <div class="dynamic-form-fields"> </div>

                                <!-- @include('frontEnd.common.popup_alert_messages')
                                    <div class="modal-space">
                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7"> Risk Detail: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" disabled="" class="form-control trans" name="v-risk_name" maxlength="255"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 p-l-0"> Changed to: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" class="form-control trans" name="v-risk_new_status_txt" disabled="" maxlength="255" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                                <label class="col-md-1 col-sm-1 col-xs-12 p-t-7 p-l-0"> Date: </label>
                                                <div class="col-md-11 col-sm-11 col-xs-12 r-p-0">
                                                    <div class="input-group popovr">
                                                        <input type="text" class="form-control trans" name="v-risk_date" disabled="" value="" maxlength="10" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-lg-6  col-xs-12">
                                            <h3 class="m-t-0 m-b-20 m-l-30 clr-blue risk_name fnt-20" >Incident Explanation</h3>
                                        </div>
                             
                                        <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                            <div class="icon-div-icons pull-right p-r-15">
                                                <button class="btn btn-risk group-ico view-risk-rmp w-75" su_rmp_id="" ><i class=""></i>RMP</button>
                                                <button class="btn btn-risk group-ico ve-risk-inc-rep w-75"><i class=""></i>IR</button>
                                            </div>
                                        </div>

                                        <div class="v-dynamic-risk-fields">
                                        </div>
                                    </div> -->

                                <div class="modal-footer m-t-0 recent-task-sec p-b-0">
                                    <!-- <input type="hidden" name="service_user_id" value="{{ $service_user_id }}" />
                                        <input type="hidden" name="risk_id" value="" />
                                        <input type="hidden" name="new_status" value="" />
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">-->

                                    <input type="hidden" name="su_risk_id" value="">
                                    <button class="btn btn-default" type="button" data-dismiss="modal"
                                        aria-hidden="true"> Cancel </button><!--  -->
                                    <button class="btn btn-warning" data-toggle="modal" data-target="#riskDesc"
                                        type="button" data-dismiss="modal"> Confirm </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <!-- end risk view modal -->


    <!-- Date Range Initialization -->
    <script>
        /**
         * Adding tooltips
         */
        $(document).ready(function() {
            $('.timeline-icon').tooltip();
            $('.time_abbre').tooltip();
        });

        /**
         * Sanitizer Function
         */

        function escapeHtml(str) {
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(
                /'/g, "&#039;");
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
        function showDate() {
            $('#date_range_input').click();
        }

        function get_dates() {
            let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
            let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
            let categoy_id = $("#select_category").val();
            // return [newFormat, newFormat2, selected_category];
            return [start_date.format('YYYY-MM-DD'), end_date.format('YYYY-MM-DD'), parseInt(categoy_id)];
        }

        function pdf() {
            // get_dates();
            var start = get_dates()[0];
            var end = get_dates()[1];
            var category_id = parseInt(get_dates()[2]);
            var link = document.getElementById("pdf");
            let url =
                `{{ url('/service/logbook/download?end=${end}&start=${start}&category_id=${category_id}&format=pdf&service_user_id=' . $service_user_id) }}`;
            url = url.replaceAll('&amp;', '&')
            link.setAttribute("href", url);
            return false;
        }
    </script>

    <!-- Daily Log Comments -->


    <!-- Comment Created Duration -->
    <script>
        function time_ago(time) {
            switch (typeof time) {
                case 'number':
                    break;
                case 'string':
                    time = +new Date(time);
                    break;
                case 'object':
                    if (time.constructor === Date) time = time.getTime();
                    break;
                default:
                    time = +new Date();
            }
            var time_formats = [
                [60, 'seconds', 1], // 60
                [120, '1 minute ago', '1 minute from now'], // 60*2
                [3600, 'minutes', 60], // 60*60, 60
                [7200, '1 hour ago', '1 hour from now'], // 60*60*2
                [86400, 'hours', 3600], // 60*60*24, 60*60
                [172800, 'Yesterday', 'Tomorrow'], // 60*60*24*2
                [604800, 'days', 86400], // 60*60*24*7, 60*60*24
                [1209600, 'Last week', 'Next week'], // 60*60*24*7*4*2
                [2419200, 'weeks', 604800], // 60*60*24*7*4, 60*60*24*7
                [4838400, 'Last month', 'Next month'], // 60*60*24*7*4*2
                [29030400, 'months', 2419200], // 60*60*24*7*4*12, 60*60*24*7*4
                [58060800, 'Last year', 'Next year'], // 60*60*24*7*4*12*2
                [2903040000, 'years', 29030400], // 60*60*24*7*4*12*100, 60*60*24*7*4*12
                [5806080000, 'Last century', 'Next century'], // 60*60*24*7*4*12*100*2
                [58060800000, 'centuries', 2903040000] // 60*60*24*7*4*12*100*20, 60*60*24*7*4*12*100
            ];
            var seconds = (+new Date() - time) / 1000,
                token = 'ago',
                list_choice = 1;

            if (seconds == 0) {
                return 'Just now'
            }
            if (seconds < 0) {
                seconds = Math.abs(seconds);
                token = 'from now';
                list_choice = 2;
            }
            var i = 0,
                format;
            while (format = time_formats[i++])
                if (seconds < format[0]) {
                    if (typeof format[2] == 'string') {
                        return format[list_choice];
                    } else {
                        return Math.floor(seconds / format[2]) + ' ' + format[1] + ' ' + token;
                    }

                }
            return time;
        }
    </script>

    <!-- Category Filter -->





    <script>
        function removeAllChildNodes(parent) {
            while (parent.firstChild) {
                parent.removeChild(parent.firstChild);
            }
        }
    </script>

    <script>
        $('#date_range_input').on('apply.daterangepicker', function(ev, picker) {
            let start_date = picker.startDate.format('YYYY-MM-DD');
            let end_date = picker.endDate.format('YYYY-MM-DD');
            let keyword = $('#keywordhr').val();
            var service_user_id = "{{ $service_user_id }}";
            $(this).val(start_date + ' - ' + end_date);

            let today = new Date;
            let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) +
                "-" + today.getFullYear();

            if (start_date == todayFormat && end_date == todayFormat) {
                $('#today').text('Today');
            } else {
                $('#today').text(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
            }
            //alert(service_user_id)   
            data = {
                'service_user_id': service_user_id,
                'start_date': start_date,
                'end_date': end_date,
                'filter': 1,
                'keyword': keyword
            };
            $.ajax({
                type: 'get',
                url: "{{ url('/service/risks') }}" + '/' + {{ $service_user_id }},
                data: data,
                success: function(resp) {
                    console.log(resp)
                    //return false;
                    //$('.first').css('display','none')
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == 0) {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();
                    } else {
                        const container = document.querySelector('#logs_articles');
                        removeAllChildNodes(container);
                        let previous_date = '';
                        for (var i = 0; i < resp.log_book_records.length; i++) {
                            if (i % 2 != 0) {
                                var log_atricles = document.getElementById("logs_articles");
                                var article_left = document.createElement("article");
                                article_left.setAttribute("class", "timeline-item");

                                var timeline_desk = document.createElement("div");
                                timeline_desk.setAttribute("class", "timeline-desk");

                                var pannel = document.createElement("div");
                                pannel.setAttribute("class", "panel");
                                // pannel.setAttribute("onclick", "view_risk(`${resp.log_book_records[i]['id']}`)");

                                var pannel_body = document.createElement("div");
                                pannel_body.setAttribute("class", "panel-body");

                                var arrow = document.createElement("span");
                                arrow.setAttribute("class", "arrow");

                                pannel_body.append(arrow);

                                var timeline_icon = document.createElement("span");
                                if (resp.log_book_records[i]['status'] == 1) {
                                    timeline_icon.setAttribute("class", "timeline-icon orange-clr");
                                } else if (resp.log_book_records[i]['status'] == 2) {
                                    timeline_icon.setAttribute("class", "timeline-icon red-clr");
                                } else {
                                    timeline_icon.setAttribute("class", "timeline-icon darkgreen-clr");
                                }


                                timeline_icon.setAttribute("data-toggle", "tooltip");
                                timeline_icon.setAttribute("data-placement", "left");
                                timeline_icon.setAttribute("title", "");

                                var fa_check = document.createElement("i");
                                fa_check.setAttribute("class",
                                    `${resp.log_book_records[i]['icon']} fontam`);

                                timeline_icon.append(fa_check);

                                pannel_body.append(timeline_icon);

                                var created_at = document.createElement("span");
                                created_at.setAttribute("class", "time_abbre");
                                created_at.setAttribute("data-toggle", "tooltip");
                                created_at.setAttribute("data-placement", "top");
                                created_at.setAttribute("title", moment.utc(resp.log_book_records[i][
                                    'created_at'
                                ]).format('DD-MM-YYYY HH:mm'));

                                var created_at_text = document.createTextNode(moment.utc(resp
                                    .log_book_records[i]['created_at']).fromNow());
                                created_at.append(created_at_text);
                                $(created_at).append($(
                                    `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                                    ));


                                pannel_body.append(created_at);
                                if (previous_date != moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                    $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                    previous_date = moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                                }

                                if (resp.log_book_records[i]['description'])
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span style="color:#1ca59e">${resp.log_book_records[i]['description']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));
                                else
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));

                                var details = document.createElement("p");
                                details.setAttribute("class", "space_bottom");
                                var details_text = document.createTextNode(resp.log_book_records[i][
                                    'details'
                                ]);
                                details.append(details_text)

                                pannel_body.append(details);

                                var date_field = document.createElement("p");
                                date_field.setAttribute("class", "daily_log_time");
                                var date_text = document.createTextNode(resp.log_book_records[i][
                                    'created_at'
                                ]);

                                date_field.prepend(date_text);


                                pannel_body.append(date_field);



                                pannel.append(pannel_body);

                                timeline_desk.append(pannel);

                                article_left.append(timeline_desk);
                                log_atricles.append(article_left);
                            } else {
                                var log_atricles = document.getElementById("logs_articles");
                                var article_left = document.createElement("article");
                                article_left.setAttribute("class", "timeline-item alt");

                                var timeline_desk = document.createElement("div");
                                timeline_desk.setAttribute("class", "timeline-desk");

                                var pannel = document.createElement("div");
                                pannel.setAttribute("class", "panel");
                                // pannel.setAttribute("onclick", "view_risk("+resp.log_book_records[i]['id']+")");
                                var pannel_body = document.createElement("div");
                                pannel_body.setAttribute("class", "panel-body");

                                var arrow = document.createElement("span");
                                arrow.setAttribute("class", "arrow-alt");

                                pannel_body.append(arrow);



                                var timeline_icon = document.createElement("span");

                                if (resp.log_book_records[i]['status'] == 1) {
                                    timeline_icon.setAttribute("class", "timeline-icon orange-clr");
                                } else if (resp.log_book_records[i]['status'] == 2) {
                                    timeline_icon.setAttribute("class", "timeline-icon red-clr");
                                } else {
                                    timeline_icon.setAttribute("class", "timeline-icon darkgreen-clr");
                                }

                                timeline_icon.setAttribute("data-toggle", "tooltip");
                                timeline_icon.setAttribute("data-placement", "right");

                                timeline_icon.setAttribute("title", "");

                                var fa_check = document.createElement("i");
                                fa_check.setAttribute("class",
                                    `${resp.log_book_records[i]['icon']} fontam`);

                                timeline_icon.append(fa_check);

                                pannel_body.append(timeline_icon);

                                var created_at = document.createElement("span");
                                created_at.setAttribute("class", "time_abbre");
                                created_at.setAttribute("data-toggle", "tooltip");
                                created_at.setAttribute("data-placement", "top");
                                created_at.setAttribute("title", moment.utc(resp.log_book_records[i][
                                    'created_at'
                                ]).format('DD-MM-YYYY HH:mm'));

                                var created_at_text = document.createTextNode(moment.utc(resp
                                    .log_book_records[i]['created_at']).fromNow());
                                created_at.append(created_at_text);
                                $(created_at).append($(
                                    `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                                    ));


                                pannel_body.append(created_at);

                                if (previous_date != moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                    $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                    previous_date = moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                                }

                                if (resp.log_book_records[i]['description'])
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span style="color:#1ca59e">${resp.log_book_records[i]['description']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));
                                else
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));

                                var details = document.createElement("p");
                                details.setAttribute("class", "space_bottom");
                                var details_text = document.createTextNode(resp.log_book_records[i][
                                    'details'
                                ]);
                                details.append(details_text)

                                pannel_body.append(details);

                                var date_field = document.createElement("p");
                                date_field.setAttribute("class", "daily_log_time");
                                var date_text = document.createTextNode(resp.log_book_records[i][
                                    'created_at'
                                ]);

                                date_field.prepend(date_text);


                                pannel_body.append(date_field);

                                pannel.append(pannel_body);

                                timeline_desk.append(pannel);

                                article_left.append(timeline_desk);

                                log_atricles.append(article_left);
                            }

                        }

                        /**
                         * Adding tooltips after filtering
                         */

                        $('.timeline-icon').tooltip();
                        $('.time_abbre').tooltip();
                        return true;
                    }
                }
            });
            return false;
        });
    </script>
    <script>
        function hrmyFunctionkey() {
            let start_date = $('#date_range_input').data('daterangepicker').startDate;
            let end_date = $('#date_range_input').data('daterangepicker').endDate;
            let keyword = $('#keywordhr').val();
            var service_user_id = "{{ $service_user_id }}";
            $(this).val(start_date + ' - ' + end_date);

            let today = new Date;
            let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" +
                today.getFullYear();

            if (start_date == todayFormat && end_date == todayFormat) {
                $('#today').text('Today');
            } else {
                $('#today').text(start_date.format('DD-MM-YYYY') + ' - ' + end_date.format('DD-MM-YYYY'));
            }
            data = {
                'service_user_id': service_user_id,
                'start_date': start_date.format('YYYY-MM-DD'),
                'end_date': end_date.format('YYYY-MM-DD'),
                'filter': 1,
                'keyword': keyword
            };
            //alert(service_user_id)
            //alert(data)
            $.ajax({
                type: 'get',
                url: "{{ url('/service/risks') }}" + '/' + {{ $service_user_id }},
                data: data,
                success: function(resp) {
                    console.log(resp)
                    //return false;
                    //$('.first').css('display','none')
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == 0) {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();
                    } else {
                        const container = document.querySelector('#logs_articles');
                        removeAllChildNodes(container);
                        let previous_date = '';
                        for (var i = 0; i < resp.log_book_records.length; i++) {
                            if (i % 2 != 0) {
                                var log_atricles = document.getElementById("logs_articles");
                                var article_left = document.createElement("article");
                                article_left.setAttribute("class", "timeline-item");

                                var timeline_desk = document.createElement("div");
                                timeline_desk.setAttribute("class", "timeline-desk");

                                var pannel = document.createElement("div");
                                pannel.setAttribute("class", "panel");

                                var pannel_body = document.createElement("div");
                                pannel_body.setAttribute("class", "panel-body");

                                var arrow = document.createElement("span");
                                arrow.setAttribute("class", "arrow");

                                pannel_body.append(arrow);

                                var timeline_icon = document.createElement("span");
                                if (resp.log_book_records[i]['status'] == 1) {
                                    timeline_icon.setAttribute("class", "timeline-icon orange-clr");
                                } else if (resp.log_book_records[i]['status'] == 2) {
                                    timeline_icon.setAttribute("class", "timeline-icon red-clr");
                                } else {
                                    timeline_icon.setAttribute("class", "timeline-icon darkgreen-clr");
                                }
                                timeline_icon.setAttribute("data-toggle", "tooltip");
                                timeline_icon.setAttribute("data-placement", "left");
                                timeline_icon.setAttribute("title", "");

                                var fa_check = document.createElement("i");
                                fa_check.setAttribute("class", `${resp.log_book_records[i]['icon']} fontam`);

                                timeline_icon.append(fa_check);

                                pannel_body.append(timeline_icon);

                                var created_at = document.createElement("span");
                                created_at.setAttribute("class", "time_abbre");
                                created_at.setAttribute("data-toggle", "tooltip");
                                created_at.setAttribute("data-placement", "top");
                                created_at.setAttribute("title", moment.utc(resp.log_book_records[i][
                                    'created_at'
                                ]).format('DD-MM-YYYY HH:mm'));

                                var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[
                                    i]['created_at']).fromNow());
                                created_at.append(created_at_text);
                                $(created_at).append($(
                                    `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                                    ));


                                pannel_body.append(created_at);
                                if (previous_date != moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                    $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                    previous_date = moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                                }

                                if (resp.log_book_records[i]['description'])
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span style="color:#1ca59e">${resp.log_book_records[i]['description']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));
                                else
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));

                                var details = document.createElement("p");
                                details.setAttribute("class", "space_bottom");
                                var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                                details.append(details_text)

                                pannel_body.append(details);

                                var date_field = document.createElement("p");
                                date_field.setAttribute("class", "daily_log_time");
                                var date_text = document.createTextNode(resp.log_book_records[i]['created_at']);

                                date_field.prepend(date_text);


                                pannel_body.append(date_field);



                                pannel.append(pannel_body);

                                timeline_desk.append(pannel);

                                article_left.append(timeline_desk);
                                log_atricles.append(article_left);
                            } else {
                                var log_atricles = document.getElementById("logs_articles");
                                var article_left = document.createElement("article");
                                article_left.setAttribute("class", "timeline-item alt");

                                var timeline_desk = document.createElement("div");
                                timeline_desk.setAttribute("class", "timeline-desk");

                                var pannel = document.createElement("div");
                                pannel.setAttribute("class", "panel");

                                var pannel_body = document.createElement("div");
                                pannel_body.setAttribute("class", "panel-body");

                                var arrow = document.createElement("span");
                                arrow.setAttribute("class", "arrow-alt");

                                pannel_body.append(arrow);



                                var timeline_icon = document.createElement("span");
                                if (resp.log_book_records[i]['status'] == 1) {
                                    timeline_icon.setAttribute("class", "timeline-icon orange-clr");
                                } else if (resp.log_book_records[i]['status'] == 2) {
                                    timeline_icon.setAttribute("class", "timeline-icon red-clr");
                                } else {
                                    timeline_icon.setAttribute("class", "timeline-icon darkgreen-clr");
                                }
                                timeline_icon.setAttribute("data-toggle", "tooltip");
                                timeline_icon.setAttribute("data-placement", "right");

                                timeline_icon.setAttribute("title", "");

                                var fa_check = document.createElement("i");
                                fa_check.setAttribute("class", `${resp.log_book_records[i]['icon']} fontam`);

                                timeline_icon.append(fa_check);

                                pannel_body.append(timeline_icon);

                                var created_at = document.createElement("span");
                                created_at.setAttribute("class", "time_abbre");
                                created_at.setAttribute("data-toggle", "tooltip");
                                created_at.setAttribute("data-placement", "top");
                                created_at.setAttribute("title", moment.utc(resp.log_book_records[i][
                                    'created_at'
                                ]).format('DD-MM-YYYY HH:mm'));

                                var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[
                                    i]['created_at']).fromNow());
                                created_at.append(created_at_text);
                                $(created_at).append($(
                                    `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                                    ));


                                pannel_body.append(created_at);

                                if (previous_date != moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                    $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                    previous_date = moment.utc(resp.log_book_records[i]['created_at'],
                                        'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                                }

                                if (resp.log_book_records[i]['description'])
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span style="color:#1ca59e">${resp.log_book_records[i]['description']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));
                                else
                                    $(pannel_body).append($(
                                        `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`
                                        ));

                                var details = document.createElement("p");
                                details.setAttribute("class", "space_bottom");
                                var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                                details.append(details_text)

                                pannel_body.append(details);

                                var date_field = document.createElement("p");
                                date_field.setAttribute("class", "daily_log_time");
                                var date_text = document.createTextNode(resp.log_book_records[i]['created_at']);

                                date_field.prepend(date_text);


                                pannel_body.append(date_field);

                                pannel.append(pannel_body);

                                timeline_desk.append(pannel);

                                article_left.append(timeline_desk);

                                log_atricles.append(article_left);
                            }

                        }

                        /**
                         * Adding tooltips after filtering
                         */

                        $('.timeline-icon').tooltip();
                        $('.time_abbre').tooltip();
                        return true;
                    }
                }
            });
            return false;

        }
    </script>
    <script>
        function view_risk(risk_id) {
            // $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url: "{{ url('/service/risk/view') }}" + '/' + risk_id,
                dataType: "json",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    var response = resp['response'];
                    console.log(response);
                    if (response == true) {

                        var su_risk_id = resp['sur_id'];
                        var su_rmp_id = resp['su_rmp_id'];
                        var risk_txt = resp['risk_txt'];
                        var risk_status = resp['risk_status'];
                        //var risk_status_txt = resp['risk_status_txt'];
                        var risk_dyn_form = resp['risk_form'];
                        var risk_date = resp['created_at'];

                        var form_builder_id = resp['form_builder_id'];
                        var dynamic_form_id = resp['dynamic_form_id'];
                        var incident_report_id = resp['incident_report_id'];
                        var sel_injury_parts = resp['sel_injury_parts'];
                        // console.log(sel_injury_parts);

                        var modal = ('#riskViewModal');

                        $(modal + ' .dynamic-form-fields').html(risk_dyn_form);
                        $(modal + ' .dynamic_form_select').val(form_builder_id);

                        $('input[name=\'su_risk_id\']').val(su_risk_id);
                        $(modal + ' input[name=\'v-risk_name\']').val(risk_txt);
                        $(modal + ' .new_risk_status').val(risk_status);
                        //alert(risk_status);
                        //settings plan buttons
                        $('.view-risk-rmp').attr('su_rmp_id', su_rmp_id);
                        $('.ve-risk-inc-rep').attr('su_risk_id', su_risk_id);
                        //june 23
                        // var body_url = "{{ url('/service/body-map') }}"+'/'+su_risk_id;
                        // $('.body-map').attr('href',body_url);
                        $('.body-map').attr('href', '#bodyMapModal');
                        $('.body-map').attr('data-toggle', 'modal');
                        $('.body-map').attr('data-dismiss', 'modal');
                        // $('input[name=sel_injury_parts]').attr('sel_injury_parts',sel_injury_parts);
                        var obj = JSON.parse(sel_injury_parts);
                        var len = obj.length;
                        for (var i = 0; i < len; i++) {

                            var sel_body_map_id = obj[i].sel_body_map_id;
                            $('#' + sel_body_map_id).attr('class', 'active');
                        }
                        $('input[name=su_rsk_id]').val(su_risk_id);

                        //$('input[name=\'v-risk_new_status_txt\']').val(risk_status_txt);
                        //$('input[name=\'v-risk_date\']').val(risk_date);
                        //$('.v-dynamic-risk-fields').html(risk_dyn_form);
                        $('.v-rmp-btn').attr('su_rmp_id', su_rmp_id);

                        setTimeout(function() {
                            autosize($("textarea"));
                        }, 200);

                    } else if (response == 'AUTH_ERR') {
                        $('#riskViewModal').modal('hide');
                        $('.ajax-alert-err').find('.msg').text('{{ UNAUTHORIZE_ERR }}');
                        $('.ajax-alert-err').show();
                        setTimeout(function() {
                            $(".ajax-alert-err").fadeOut()
                        }, 5000);
                        //return false;

                    } else {

                        $('span.popup_error_txt').text('Some Error Occurred. Please try again later.');
                        $('.popup_error').show();
                        setTimeout(function() {
                            $(".popup_error").fadeOut()
                        }, 5000);
                    }
                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
        }
    </script>



    <!--  -->

@endsection
