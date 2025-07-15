@extends('frontEnd.layouts.master')
@section('title','Health Record')
@section('content')
<style type="text/css">
    /*09 Aug 2018*/
    .back_opt {
        background: #1f88b5;
        border-radius: 100%;
        color: #fff;
        font-size: 20px;
        padding: 8px 18px;
        z-index: 999;
        cursor: pointer;
        height: 45px;
        width: 45px;
        display: inline-block;
    }

    .back_opt:hover i {
        color: #fff;
    }

    i.fa.fa.fa-h-square {
        color: #1f88b5;
        font-size: 25px;
    }

    .space_bottom {
        margin-bottom: 7px !important;
    }

    .timeline,
    #logs_articles {
        border-collapse: collapse;
        border-spacing: 0;
        display: table;
        position: relative;
        table-layout: fixed;
        width: 100%;
        min-height: 50vh;
    }

    .timeline .time-show {
        margin-right: 0px;
        text-align: center;
    }
</style>

<link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

@php
function time_diff_string($from, $to, $full = false) {
$from = new DateTime($from);
$to = new DateTime($to);
$diff = $to->diff($from);

$diff->w = floor($diff->d / 7);
$diff->d -= $diff->w * 7;

$string = array(
'y' => 'year',
'm' => 'month',
'w' => 'week',
'd' => 'day',
'h' => 'hour',
'i' => 'minute',
's' => 'second',
);
foreach ($string as $k => &$v) {
if ($diff->$k) {
$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
} else {
unset($string[$k]);
}
}

if (!$full) $string = array_slice($string, 0, 1);
return $string ? implode(', ', $string) . ' ago' : 'just now';
}
@endphp

<section id="container">
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="pull-right">
                    <div class="filter_buttons" style="text-align:right;padding-right:150px;display:inline-block; padding-bottom: 10px;">
                        <a data-toggle="modal" href="#dynmicFormModalhealthrecord" class="btn btn-primary  col-6" id='add_new_log'>Add New</a>
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
                                                                                            echo "disabled";
                                                                                        } ?>>
                        <option value="">Select Child</option>
                        @foreach($service_users as $val)
                        <option <?php if (isset($service_user_id)) {
                                    if ($service_user_id == $val->id) {
                                        echo "Selected";
                                    }
                                } ?> value="{{$val->id}}">{{$val->name}}</option>
                        @endforeach
                    </select>
                </div>

                <!-- sourabh -->
                <div class="col-md-3 col-lg-3" style="margin-left: -10px;">
                    <div class="form-group datepicker-sttng date-sttng">
                        <label class="col-md-2 col-sm-1 col-xs-12 p-t-7" style="display: none;"> Date: </label>
                        <div class="col-md-10 col-sm-10 col-xs-12">
                            <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                <input id="date_range_input" style="cursor: pointer;" name="daterange" value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text" value="" readonly="" size="16" class="form-control log-book-datetime">
                                <span class="input-group-btn add-on datetime-picker2">
                                    <button onclick="showDate()" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
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
                    <input type="text" class="form-control" id="keywordhr" onKeyPress="hrmyFunctionkey()" name="keywordhr" placeholder="Keyword">
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

                        <div class="timeline-messages" id="logs_articles">
                            @php
                            $colors = ['#8fd6d6', '#E13533', '#bda4ec', '#fed65a', '#81b56b'];
                            shuffle($colors); // Randomizes the order
                            @endphp

                            @foreach ($log_book_records as $index => $key )

                            @php
                            $color = $colors[$index % count($colors)]; // Cycle through colors if more records than colors
                            @endphp


                            @if($loop->iteration % 2 == 0)
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first"> {{ date('d M Y', strtotime($key['created_at'])) }} </div>

                                        <div class="second" style="background-color: {{ $color }};">
                                            <p>{{ $key['title'] }}</p>
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" id="open_form_view" data-toggle="modal"> <i class="fa fa-eye" data-id="{{ $key['id'] }}"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>
                                                </label>
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
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>
                                                </label>
                                            </span>
                                            <p>{{ $key['title'] }}</p>
                                        </div>
                                        <div class="first">{{ date('d M Y', strtotime($key['created_at'])) }}</div>
                                    </div>
                                </div>
                            </div>

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

<div class="modal fade" id="viewDaily_log" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                    <input type="password" class="form-control" id="" placeholder="Password">
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
    const inputs = document.querySelectorAll('.dd-input');

    inputs.forEach(input => {
        input.addEventListener('change', () => {
            if (input.checked) {
                inputs.forEach(otherInput => {
                    if (otherInput !== input) {
                        otherInput.checked = false;
                    }
                });
            }
        });
    });

    document.getElementById('open_form_view').addEventListener('click', function() {
        document.getElementById('viewDaily_log').style.display = 'block';

        $.ajax({
            type: 'get',
            url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
            data: data,
            success: function(resp) {
                console.log(resp);
                return false;
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

                    return true;
                }
            }
        });
    });
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
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    $(function() {
        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            locale: {
                format: 'DD/MM/YYYY'
            }
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
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
        let url = `{{ url('/service/logbook/download?end=${end}&start=${start}&category_id=${category_id}&format=pdf&service_user_id='.$service_user_id) }}`;
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

<!-- Daterange Filter -->
<!-- <script>
$('input[name="daterange"]').on('apply.daterangepicker', function(ev, picker) {
    let staff_member = $('#staff_member').val();
    let start_date = picker.startDate.format('DD-MM-YYYY');
    let end_date = picker.endDate.format('DD-MM-YYYY');
    let service_user = $('#service_user').val();
    let keyword = $('#keyword').val();
    $(this).val(start_date + ' - ' + end_date);

    let today  = new Date;
    let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0"+(today.getMonth()+1)).slice(-2) + "-" + today.getFullYear();

    if (start_date == todayFormat && end_date == todayFormat) {
        $('#today').text('Today');
    }
    else{
        $('#today').text(start_date + ' - ' + end_date);
    }

    let category_id = $("#select_category").val();

    if(category_id && category_id!='all')
        data = { 'staff_member':staff_member,'service_user':service_user,'start_date': picker.startDate.format('YYYY-MM-DD'), 'end_date': picker.endDate.format('YYYY-MM-DD'), 'category_id': category_id,'filter':1,'keyword':keyword};
    else
        data = { 'staff_member':staff_member,'service_user':service_user,'start_date': picker.startDate.format('YYYY-MM-DD'), 'end_date': picker.endDate.format('YYYY-MM-DD'),'filter':1,'keyword':keyword};


    $.ajax({
        type  : 'get',
        url   : "{{ url('/service/daily-logs') }}",
        data : data,
        success:function(resp)  {
            if (isAuthenticated(resp) == false){
                return false;
            }
            if (resp == 0){
                $('span.popup_error_txt').text('Error Occured');
                $('.popup_error').show();
            } else {
                const container = document.querySelector('#logs_articles');
                removeAllChildNodes(container);
                let previous_date = '';
                
                for(var i=0; i<resp.log_book_records.length; i++)
                {
                    if (i%2 !=0)
                    {
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

                        if(resp.log_book_records[i]['is_late'] == '1')
                        {
                            $(pannel_body).append($(`
                            <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                    `));
                        }

                        

                        var timeline_icon = document.createElement("span");
                        timeline_icon.setAttribute("class", "timeline-icon");
                        timeline_icon.setAttribute("data-toggle", "tooltip");
                        timeline_icon.setAttribute("data-placement", "left");
                        timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                        timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                        var fa_check = document.createElement("i");
                        fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                        timeline_icon.append(fa_check);

                        pannel_body.append(timeline_icon);

                        var created_at = document.createElement("span");
                        created_at.setAttribute("class", "time_abbre");
                        created_at.setAttribute("data-toggle", "tooltip");
                        created_at.setAttribute("data-placement", "top");
                        created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                        var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                        created_at.append(created_at_text);
                        $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));
                        

                        pannel_body.append(created_at);

                        if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                        {
                            $(log_atricles).append($(`
                                            <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                    ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                </span>
                                            </div>`
                            ));
                            previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                        }

                        if(resp.log_book_records[i]['category_name'])
                        $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                        else
                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${escapeHtml(resp.log_book_records[i]['title'])}</span></h1>`));

                        var details = document.createElement("p");
                        var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                        details.append(details_text)

                        pannel_body.append(details);

                        var date_field = document.createElement("p");
                        date_field.setAttribute("class", "daily_log_time");
                        var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                        if(resp.log_book_records[i]['is_late']) {
                            if(resp.log_book_records[i]['late_time_text']) {
                                var span_date_field = document.createElement("span");
                                span_date_field.setAttribute("style", "color:red");
                                var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                span_date_field.append(span_date_field_text);
                                date_field.append(' | ');
                                // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                date_field.append(span_date_field);
                            } else {
                                var span_date_field = document.createElement("span");
                                span_date_field.setAttribute("style", "color:red");
                                var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                span_date_field.append(span_date_field_text);
                                date_field.append(' | ');
                                date_field.append(span_date_field);
                            }
                        }                            
                        date_field.prepend(date_text);        
                        
                        $(pannel_body).append($(`
                        <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                    Comments
                                    <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                    </a>
                                    `));
                        pannel_body.append(date_field);


                        pannel.append(pannel_body);

                        timeline_desk.append(pannel);

                        article_left.append(timeline_desk);
   
                        log_atricles.append(article_left);
                    }
                    else{
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

                        if(resp.log_book_records[i]['is_late'] == '1')
                        {
                            $(pannel_body).append($(`
                            <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                    `));
                        }

                        var timeline_icon = document.createElement("span");
                        timeline_icon.setAttribute("class", "timeline-icon");
                        timeline_icon.setAttribute("data-toggle", "tooltip");
                        timeline_icon.setAttribute("data-placement", "right");
                        timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                        timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                        var fa_check = document.createElement("i");
                        fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                        timeline_icon.append(fa_check);

                        pannel_body.append(timeline_icon);

                        var created_at = document.createElement("span");
                        created_at.setAttribute("class", "time_abbre");
                        created_at.setAttribute("data-toggle", "tooltip");
                        created_at.setAttribute("data-placement", "top");
                        created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                        var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                        created_at.append(created_at_text);
                        $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                        pannel_body.append(created_at);

                        if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                        {
                            $(log_atricles).append($(`
                                            <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                    ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                </span>
                                            </div>`
                            ));
                            previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                        }

                        if(resp.log_book_records[i]['category_name'])
                        $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                        else
                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${escapeHtml(resp.log_book_records[i]['title'])}</span></h1>`));

                        var details = document.createElement("p");
                        var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                        details.append(details_text)

                        pannel_body.append(details);

                        var date_field = document.createElement("p");
                        date_field.setAttribute("class", "daily_log_time");
                        var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                        if(resp.log_book_records[i]['is_late']) {
                            if(resp.log_book_records[i]['late_time_text']) {
                                var span_date_field = document.createElement("span");
                                span_date_field.setAttribute("style", "color:red");
                                var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                span_date_field.append(span_date_field_text);
                                // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                date_field.append(' | ');
                                date_field.append(span_date_field);
                            } else {
                                var span_date_field = document.createElement("span");
                                span_date_field.setAttribute("style", "color:red");
                                var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                span_date_field.append(span_date_field_text);
                                date_field.append(' | ');
                                date_field.append(span_date_field);
                            }
                        }                            
                        date_field.prepend(date_text);        

                        $(pannel_body).append($(`
                        <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                    Comments
                                    <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                    </a>
                                    `));
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
            }
        }
    });

  });

</script> -->

<script>
    function removeAllChildNodes(parent) {
        while (parent.firstChild) {
            parent.removeChild(parent.firstChild);
        }
    }
</script>
<!-- sourabh -->
<!-- <script type="text/javascript">
    $('#service_user').change(function(){
        let staff_member = $('#staff_member').val();
        let service_user = $('#service_user').val();
        let category_id = $('#select_category').val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        if(category_id && category_id!='all')
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'), 'category_id': category_id,'filter':1,'keyword':keyword};
        else
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'),'filter':1,'keyword':keyword};

        $.ajax({
            type:'post',
            url:"{{ url('/service/daily-logs') }}",
            data:data,
            success:function(resp)  {
                console.log(resp)
                if (isAuthenticated(resp) == false){
                    return false;
                }
                if (resp == 0){
                    $('span.popup_error_txt').text('Error Occured');
                    $('.popup_error').show();
                } else {
                    const container = document.querySelector('#logs_articles');
                    removeAllChildNodes(container);
                    let previous_date = '';
                    for(var i=0; i<resp.log_book_records.length; i++)
                    {
                        if (i%2 !=0)
                        {
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);                            

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);
                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);
                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));                            

                            pannel_body.append(date_field);
                            


                            pannel.append(pannel_body);

                            timeline_desk.append(pannel);

                            article_left.append(timeline_desk);  
                            log_atricles.append(article_left);
                        }
                        else{
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);

                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                            $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);

                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));
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
    
</script> -->
<!-- <script type="text/javascript">
    $('#staff_member').change(function(){
        let staff_member = $('#staff_member').val();
        let service_user = $('#service_user').val();
        let category_id = $('#select_category').val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
        if(category_id && category_id!='all')
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'), 'category_id': category_id,'filter':1,'keyword':keyword};
        else
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'),'filter':1,'keyword':keyword};

        $.ajax({
            type:'post',
            url:"{{ url('/service/daily-logs') }}",
            data:data,
            success:function(resp)  {
                console.log(resp)
                if (isAuthenticated(resp) == false){
                    return false;
                }
                if (resp == 0){
                    $('span.popup_error_txt').text('Error Occured');
                    $('.popup_error').show();
                } else {
                    const container = document.querySelector('#logs_articles');
                    removeAllChildNodes(container);
                    let previous_date = '';
                    for(var i=0; i<resp.log_book_records.length; i++)
                    {
                        if (i%2 !=0)
                        {
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);                            

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);
                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);
                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));                            

                            pannel_body.append(date_field);
                            


                            pannel.append(pannel_body);

                            timeline_desk.append(pannel);

                            article_left.append(timeline_desk);  
                            log_atricles.append(article_left);
                        }
                        else{
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);

                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                            $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);

                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));
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
<!-- sourabh --
<script>
    //$('#keyword').keypress(function(){
        function myFunctionkey(){
        let staff_member = $('#staff_member').val();
        let service_user = $('#service_user').val();
        let category_id = $('#select_category').val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let keyword = $('#keyword').val();
       // alert(keyword)
        if(category_id && category_id!='all')
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'), 'category_id': category_id,'filter':1,'keyword':keyword};
        else
            data = { 'staff_member':staff_member,'service_user':service_user,'start_date': start_date.format('YYYY-MM-DD'), 'end_date': end_date.format('YYYY-MM-DD'),'filter':1,'keyword':keyword};

        $.ajax({
            type:'post',
            url:"{{ url('/service/daily-logs') }}",
            data:data,
            success:function(resp)  {
                console.log(resp)
                if (isAuthenticated(resp) == false){
                    return false;
                }
                if (resp == 0){
                    $('span.popup_error_txt').text('Error Occured');
                    $('.popup_error').show();
                } else {
                    const container = document.querySelector('#logs_articles');
                    removeAllChildNodes(container);
                    let previous_date = '';
                    for(var i=0; i<resp.log_book_records.length; i++)
                    {
                        if (i%2 !=0)
                        {
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);                            

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);
                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);
                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));                            

                            pannel_body.append(date_field);
                            


                            pannel.append(pannel_body);

                            timeline_desk.append(pannel);

                            article_left.append(timeline_desk);  
                            log_atricles.append(article_left);
                        }
                        else{
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

                            if(resp.log_book_records[i]['is_late'] == '1')
                            {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:"+resp.log_book_records[i]['category_color']);
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);

                            if(previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY'))
                            {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`
                                ));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if(resp.log_book_records[i]['category_name'])
                            $(pannel_body).append($(`<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title']}</span></h1>`));
                            else
                                $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text= document.createTextNode(resp.log_book_records[i]['date']);
                            if(resp.log_book_records[i]['is_late']) {
                                if(resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(resp.log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text= document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }                            
                            date_field.prepend(date_text);

                            $(pannel_body).append($(`
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments']}</span>  
                                        </a>
                                        `));
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
</script> -->
<!-- sourabh -->
<script>
    $('#date_range_input').on('apply.daterangepicker', function(ev, picker) {
        let start_date = picker.startDate.format('YYYY-MM-DD');
        let end_date = picker.endDate.format('YYYY-MM-DD');
        let keyword = $('#keywordhr').val();
        var service_user_id = "{{ $service_user_id }}";
        $(this).val(start_date + ' - ' + end_date);

        let today = new Date;
        let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" + today.getFullYear();

        if (start_date == todayFormat && end_date == todayFormat) {
            $('#today').text('Today');
        } else {
            $('#today').text(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
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
            url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
            data: data,
            success: function(resp) {
                console.log(resp);
                return false;
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
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:null");
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title", "");

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", "fa fa-h-square");

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);
                            if (previous_date != moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }


                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

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
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:null");
                            timeline_icon.setAttribute("title", "");

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", "fa fa fa-h-square");

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);

                            if (previous_date != moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }


                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

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
        let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" + today.getFullYear();

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
            url: "{{ url('/service/health-records') }}" + '/' + service_user_id,
            data: data,
            success: function(resp) {
                console.log(resp)
                //return false;
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
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:null");
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title", "");

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", "fa fa-h-square");

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);
                            if (previous_date != moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }


                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title']}</span></h1>`));

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
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:null");
                            timeline_icon.setAttribute("title", "");

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", "fa fa fa-h-square");

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i]['created_at']).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(`<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`));


                            pannel_body.append(created_at);

                            if (previous_date != moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['created_at'], 'YYYY-MM-DD H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['created_at'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }


                            $(pannel_body).append($(`<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`));

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



@include('frontEnd.serviceUserManagement.elements.add_health_record')
@include('frontEnd.serviceUserManagement.elements.comments')
@endsection