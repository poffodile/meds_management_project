@extends('frontEnd.layouts.master')
@section('title', 'Health Record')
@section('content')

    <link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

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

    <style>
        .message-body.msg-in.leftmsg {
            right: 358px;
        }

        .message-body.msg-in.rightmsg {
            left: 318px;
            top: 6px;
        }

        .timeline-messages .msg-in.rightmsg .arrow {
            border-right: 8px solid #969494 !important;
            left: -15px;
            transform: rotate(1deg);
            border-left: 8px solid #96949400 !important;
        }

        .msg-time-chat .msg-in .text .second {
            justify-content: space-between;
        }
    </style>

    <section id="container">
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="pull-right">
                        <div class="filter_buttons"
                            style="text-align:right;padding-right:150px;display:inline-block; padding-bottom: 10px;">
                            <a data-toggle="modal" href="#dynmicFormModalhealthrecord" class="btn btn-primary  col-6"
                                id='add_new_log'>Add New</a>
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
                    <div class="col-md-3 col-lg-3" style="margin-left: -10px;">
                        <div class="form-group datepicker-sttng date-sttng">
                            <label class="col-md-2 col-sm-1 col-xs-12 p-t-7" style="display: none;"> Date: </label>
                            <div class="col-md-10 col-sm-10 col-xs-12">
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
                    <div class="col-md-2 col-lg-2" style="padding-bottom:10px; margin-left: -10px;">
                        <input type="text" class="form-control" id="keywordhr" onKeyPress="hrmyFunctionkey()"
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

                            <div class="timeline-messages" id="logs_articles_health">
                                @php
                                    $colors = ['#8fd6d6', '#f57775', '#bda4ec', '#fed65a', '#81b56b'];
                                    shuffle($colors); // Randomizes the order
                                @endphp
                                @if (count($log_book_records) > 0)

                                @foreach ($log_book_records as $index => $key)
                                    @php
                                        $color = $colors[$index % count($colors)]; // Cycle through colors if more records than colors
                                    @endphp


                                    @if ($loop->iteration % 2 == 0)
                                        <div class="msg-time-chat">
                                            <div class="message-body msg-in rightmsg">
                                                <span class="arrow"></span>
                                                <div class="text">
                                                    <div class="first"> {{ date('d M Y', strtotime($key['form_date'])) }} -
                                                        {{ $key['form_time'] }}</div>
                                                    <div class="second" style="background-color: {{ $color }};">
                                                        <p>{{ $key['form_name'] }} - {{ $key['form_title'] }}</p>
                                                        <span class="input-group-addon cus-inpt-grp-addon clr-blue settings"
                                                            style="background-color: {{ $color }};">
                                                            <i class="fa fa-cog"></i>
                                                            <div class="pop-notifbox">
                                                                <ul class="pop-notification" type="none">
                                                                    <li> <a href="#" data-dismiss="modal"
                                                                            aria-hidden="true" class="dyn-form-view-data"
                                                                            id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}">
                                                                            <span> <i class="fa fa-eye"></i> </span>
                                                                            View/Edit
                                                                        </a> </li>
                                                                    <li> <a href="#" class="dyn_form_del_btn"
                                                                            id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}">
                                                                            <span class="color-red"> <i
                                                                                    class="fa fa-exclamation-circle"></i>
                                                                            </span> Remove </a> </li>
                                                                </ul>
                                                            </div>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="msg-time-chat">
                                            <div class="message-body msg-in leftmsg">
                                                <span class="arrow"></span>
                                                <div class="text">
                                                    <div class="second" style="background-color: {{ $color }};">
                                                        <span
                                                            class="input-group-addon cus-inpt-grp-addon clr-blue settings"
                                                            style="background-color: {{ $color }};">
                                                            <i class="fa fa-cog"></i>
                                                            <div class="pop-notifbox">
                                                                <ul class="pop-notification" type="none">
                                                                    <li> <a href="#" data-dismiss="modal"
                                                                            aria-hidden="true" class="dyn-form-view-data"
                                                                            id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}">
                                                                            <span> <i class="fa fa-eye"></i> </span>
                                                                            View/Edit
                                                                        </a> </li>
                                                                    <li> <a href="#" class="dyn_form_del_btn"
                                                                            id="{{ isset($key['dynamic_form_id']) ? $key['dynamic_form_id'] : null }}">
                                                                            <span class="color-red"> <i
                                                                                    class="fa fa-exclamation-circle"></i>
                                                                            </span> Remove </a> </li>
                                                                </ul>
                                                            </div>
                                                        </span>
                                                        <p>{{ $key['form_name'] }} - {{ $key['form_title'] }}</p>
                                                    </div>
                                                    <div class="first">{{ date('d M Y', strtotime($key['form_date'])) }}
                                                        - {{ $key['form_time'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @else
                                <div class="no-record text-center p-3">
                                    <p>No records found</p>
                                </div>
                            @endif
                            </div>

                        </div>
                    </div>
                </div>
                <!-- page end-->
            </section>
        </section>
        <!--main content end-->

    </section>

    <div class="modal fade" id="viewDaily_log" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Daily Logs </h4>
                </div>
                <div class="modal-body">
                    <div class="actionForm">
                        <div class="p-b-10">
                            <form class="form-horizontal" role="form">
                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Email</label>
                                    <div class="col-lg-10">
                                        <input type="email" class="form-control" id="" placeholder="Email">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col-lg-2 col-sm-2 control-label">Password</label>
                                    <div class="col-lg-10">
                                        <input type="password" class="form-control" id=""
                                            placeholder="Password">
                                    </div>
                                </div>

                                <div class="modal-footer m-t-0 m-b-15 p-r-0 modal-bttm">
                                    <button type="submit" class="btn btn-danger">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script>
        // const inputs = document.querySelectorAll('.dd-input');

        // inputs.forEach(input => {
        //     input.addEventListener('change', () => {
        //         if (input.checked) {
        //             inputs.forEach(otherInput => {
        //                 if (otherInput !== input) {
        //                     otherInput.checked = false;
        //                 }
        //             });
        //         }
        //     });
        // });

        // document.getElementById('open_form_view').addEventListener('click', function() {
        //     document.getElementById('viewDaily_log').style.display = 'block';

        //     $.ajax({
        //         type: 'get',
        //         url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
        //         data: data,
        //         success: function(resp) {
        //             console.log(resp);
        //             if (isAuthenticated(resp) == false) {
        //                 return false;
        //             }
        //             if (resp == 0) {
        //                 $('span.popup_error_txt').text('Error Occured');
        //                 $('.popup_error').show();
        //             } else {
        //                 const container = document.querySelector('#logs_articles_health');
        //                 removeAllChildNodes(container);
        //                 let previous_date = '';

        //                 return true;
        //             }
        //             return false;

        //         }
        //     });
        // });
    </script>
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
            // let categoy_id = $("#select_category").val();
            // return [newFormat, newFormat2, selected_category];
            return [start_date.format('YYYY-MM-DD'), end_date.format('YYYY-MM-DD')];
        }

        function pdf() {
            // get_dates();
            var start = get_dates()[0];
            var end = get_dates()[1];
            var category_id = parseInt(get_dates()[2]);
            var link = document.getElementById("pdf");
            let url =
                `{{ url('/service/logbook/download?end=${end}&start=${start}&format=pdf&service_user_id=' . $service_user_id) }}`;
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
    <script>
        function removeAllChildNodes(parent) {
            while (parent.firstChild) {
                parent.removeChild(parent.firstChild);
            }
        }
    </script>

    <script>
        function getHealthRecordData(data) {
            var service_user_id = "{{ $service_user_id }}";
            $.ajax({
                type: 'get',
                url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
                data: data,
                success: function(resp) {
                    console.log("respHealth", resp);

                    // return false;
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    if (resp == 0) {
                        $('span.popup_error_txt').text('Error Occured');
                        $('.popup_error').show();
                    } else {
                        const container = document.querySelector('#logs_articles_health');
                        removeAllChildNodes(container);

                        let previous_date = '';
                        // Define your color palette
                        let colors = ['#8fd6d6', '#f57775', '#bda4ec', '#fed65a', '#81b56b'];

                        // Shuffle colors randomly
                        colors = colors.sort(() => Math.random() - 0.5);
                        for (let i = 0; i < resp.log_book_records.length; i++) {


                            // alert(resp.log_book_records[i]);
                            let key = resp.log_book_records[i];
                            let color = colors[i % colors.length]; // cycle through colors

                            // Main container
                            let msgTimeChat = document.createElement("div");
                            msgTimeChat.classList.add("msg-time-chat");

                            // Message body
                            let msgBody = document.createElement("div");
                            msgBody.classList.add("message-body", "msg-in");

                            // Alternate left/right
                            if ((i + 1) % 2 === 0) {
                                msgBody.classList.add("rightmsg");
                            } else {
                                msgBody.classList.add("leftmsg");
                            }

                            let arrow = document.createElement("span");
                            arrow.classList.add("arrow");
                            msgBody.appendChild(arrow);

                            let textDiv = document.createElement("div");
                            textDiv.classList.add("text");

                            if ((i + 1) % 2 === 0) {
                                // Right side → first date/time, then content
                                let first = document.createElement("div");
                                first.classList.add("first");
                               first.textContent = moment(key['form_date']).format("DD MMM YYYY") + " - " + moment(key['form_time'], "HH:mm:ss").format("HH:mm");

                                let second = document.createElement("div");
                                second.classList.add("second");
                                second.style.backgroundColor = color;

                                let p = document.createElement("p");
                                p.textContent = `${key['form_name']} - ${key['form_title']}`;
                                second.appendChild(p);

                                let spanSettings = document.createElement("span");
                                spanSettings.classList.add("input-group-addon", "cus-inpt-grp-addon",
                                    "clr-blue", "settings");
                                spanSettings.style.backgroundColor = color;
                                spanSettings.innerHTML = `
                                        <i class="fa fa-cog"></i>
                                        <div class="pop-notifbox">
                                            <ul class="pop-notification" type="none">
                                                <li><a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="${key['dynamic_form_id'] ?? ''}">
                                                    <span><i class="fa fa-eye"></i></span> View/Edit
                                                </a></li>
                                                <li><a href="#" class="dyn_form_del_btn" id="${key['dynamic_form_id'] ?? ''}">
                                                    <span class="color-red"><i class="fa fa-exclamation-circle"></i></span> Remove
                                                </a></li>
                                            </ul>
                                        </div>
                                    `;
                                second.appendChild(spanSettings);

                                textDiv.appendChild(first);
                                textDiv.appendChild(second);

                            } else {
                                // Left side → first content, then date/time
                                let second = document.createElement("div");
                                second.classList.add("second");
                                second.style.backgroundColor = color;

                                let spanSettings = document.createElement("span");
                                spanSettings.classList.add("input-group-addon", "cus-inpt-grp-addon",
                                    "clr-blue", "settings");
                                spanSettings.style.backgroundColor = color;
                                spanSettings.innerHTML = `
                                                <i class="fa fa-cog"></i>
                                                <div class="pop-notifbox">
                                                    <ul class="pop-notification" type="none">
                                                        <li><a href="#" data-dismiss="modal" aria-hidden="true" class="dyn-form-view-data" id="${key['dynamic_form_id'] ?? ''}">
                                                            <span><i class="fa fa-eye"></i></span> View/Edit
                                                        </a></li>
                                                        <li><a href="#" class="dyn_form_del_btn" id="${key['dynamic_form_id'] ?? ''}">
                                                            <span class="color-red"><i class="fa fa-exclamation-circle"></i></span> Remove
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            `;

                                let p = document.createElement("p");
                                p.textContent = `${key['form_name']} - ${key['form_title']}`;

                                second.appendChild(spanSettings);
                                second.appendChild(p);

                                let first = document.createElement("div");
                                first.classList.add("first");
                                first.textContent = moment(key['form_date']).format("DD MMM YYYY") + " - " + moment(key['form_time'], "HH:mm:ss").format("HH:mm");

                                textDiv.appendChild(second);
                                textDiv.appendChild(first);
                            }

                            msgBody.appendChild(textDiv);
                            msgTimeChat.appendChild(msgBody);

                            // Append to container
                            document.getElementById("logs_articles_health").appendChild(msgTimeChat);
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
        }
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

            getHealthRecordData(data);
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
            getHealthRecordData(data);
            return false;

        }
    </script>

    @include('frontEnd.serviceUserManagement.elements.add_health_record')
    @include('frontEnd.serviceUserManagement.elements.comments')
@endsection
