@extends('frontEnd.layouts.master')
@section('title','Daily Logs')
@section('content')

<link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

@php
if (!function_exists('time_diff_string')) {
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
}
$service_user_id = (isset($service_user_id)) ? $service_user_id : 0;
$service_user_name  = (isset($service_user_name )) ? $service_user_name  : 0;
@endphp


<section id="container">
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="pull-right">
                    <div class="filter_buttons" style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                        <a data-toggle="modal" href="#addLogModal" class="btn btn-primary  col-6" id='add_new_log'>Add New</a>
                        <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6" id='add_new_log' style="background-color:#d9534f;color:white;">PDF Export</a>
                    </div>
                </div>
            </div>
            <!-- page start-->
            <div class="Select_staff">
                <div class="Select_staff_inner">
                    <a class="back_opt col-3" onclick="history.back()">
                        <i class="fa fa-angle-left"></i>
                    </a>
                </div>
                <!-- sourabh -->
                <div class="Select_staff_inner">
                    <select class="form-control" name="staff_member" id="staff_member">
                        <option value="">Select Staff Member</option>
                        @foreach($staff_members as $val)
                        <option value="{{$val->id}}" <?php  if(isset(Auth::user()->id)) {
                            if(Auth::user()->id == $val->id ) {
                                echo "Selected";
                            }
                        } ?>>{{$val->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="Select_staff_inner">
                    <select class="form-control" name="service_user" id="service_user" <?php if (isset($_GET['key'])) {
                                                                                            echo "disabled";
                                                                                        } ?>>
                        <option value="">Select Child</option>
                        @foreach($service_users as $val)
                        <option <?php if (isset($_GET['key'])) {
                                    if ($_GET['key'] == $val->id) {
                                        echo "Selected";
                                    }
                                } ?> value="{{$val->id}}">{{$val->name}}</option>
                        @endforeach
                    </select>
                </div>
                <!-- sourabh -->
                <div class="Select_staff_inner">
                    <div class="datepicker-sttng date-sttng">
                        <label style="display: none;"> Date: </label>
                        <div>
                            <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                <input id="date_range_input" style="cursor: pointer;" name="daterange" value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text" value="" readonly="" size="16" class="form-control log-book-datetime">
                                <span class="input-group-btn add-on datetime-picker2">
                                    <button onclick="showDate()" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
             
                <!-- sourabh -->
                <div class="Select_staff_inner">
                    <!-- <input type="text" class="form-control" id="keyword" onKeyPress="myFunctionkey()" onKeyUp="myFunctionkey()" name="keyword" placeholder="Keyword"> -->
                </div>
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

                        <div class="timeline-messages">
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            13 Jan 2013
                                        </div>
                                        <div class="second bg-terques ">
                                            <p>Join as Product Asst. Manager</p>
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
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
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-red">
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Completed Provition period and Appointed as a permanent Employee</p>
                                        </div>
                                        <div class="first">
                                            10 Feb 2012
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            2 January 2011
                                        </div>
                                        <div class="second bg-purple">
                                            <p>Selected Employee of the Month</p>
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
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
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Got Promotion and become area manager of California</p>
                                        </div>
                                        <div class="first">
                                            4 March 2010
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            3 April 2009
                                        </div>
                                        <div class="second bg-yellow">
                                            <p>Selected the Best Employee of the Year 2013 and was awarded</p>
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
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
                            <!-- /comment -->

                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-terques">
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p> Got Promotion and become Product Manager and was transper from Branch to Head Office. Lorem ipsum dolor sit amet</p>
                                        </div>
                                        <div class="first">
                                            23 May 2008
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->
                            
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in rightmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="first">
                                            14 June 2007
                                        </div>
                                        <div class="second bg-blue">
                                            <p>Height Sales scored and break all of the previous sales record ever in the company. Awarded</p>
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
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
                            <!-- /comment -->
                            
                            <!-- Comment -->
                            <div class="msg-time-chat">
                                <div class="message-body msg-in leftmsg">
                                    <span class="arrow"></span>
                                    <div class="text">
                                        <div class="second bg-green">
                                            <span class="timelineIcons">
                                                <label class="timelinedropdown">
                                                    <div class="dd-button"><i class="fa fa-cog"></i> </div>
                                                    <input type="checkbox" class="dd-input">
                                                    <ul class="dd-menu">
                                                        <li><a href="#viewDaily_log" data-toggle="modal"> <i class="fa  fa-eye"></i> View</a></li>
                                                        <li><a href="#!"> <i class="fa  fa-pencil"></i> Edit </a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Daily</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Weekly</a></li>
                                                        <li><a href="#!"> <i class="fa fa-calendar-o"></i> Monthly</a></li>
                                                    </ul>                                                    
                                                </label> 
                                            </span>
                                            <p>Take 15 days leave for his wedding and Honeymoon & Christmas</p>
                                        </div>
                                        <div class="first">
                                            1 January 2006
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /comment -->

                        </div>
                        <article class="timeline-item alt">
                            <div class="text-right">
                                <div class="time-show first">
                                    <a href="#" class="btn btn-primary" id="today">08/07/2025</a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <!-- page end-->
        </section>
    </section>
    <!--main content end-->

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


</section>
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
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g,
            "&#039;");
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
        let url = `{{ url('/service/logbook/download?end=${end}&start=${start}&category_id=${category_id}&format=pdf&service_user_id='.$service_user_id) }}`;
        url = url.replaceAll('&amp;', '&')
        link.setAttribute("href", url);
        return false;
    }
</script>

<!-- Daily Log Comments -->
<script>
    function daily_log_comment(logId, service_user_id) {
        // alert(service_user_id)
        localStorage.setItem('log_book_id', logId);
        $('.loader').show();
        $('body').addClass('body-overflow');
        $('#commentsModal').modal('hide');
        $("#daily_log_comments_list").empty();
        $.ajax({
            type: 'get',
            url: "{{ url('/service/logbook/comments?log_book_id=') }}" + logId,

            dataType: 'json',

            success: function(resp) {
                if (isAuthenticated(resp) == false) {
                    return false;
                }
                let ul_list_items = '';
                resp.data.forEach((comment) => {
                    // ul_list_items += '<li>'+ comment + '</li>';
                    var d_format = moment.utc(comment.created_at).format('DD-MM-YYYY HH:mm');
                    // var d = new Date(comment.created_at);
                    // var d_format = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" +
                    // d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                    $("#daily_log_comments_list").append(
                        `
                        <div class="d-flex justify-content-center py-2" style="margin-top:10px;">
                                <div class="second py-2 px-2"> <span class="text1">${comment.comment}</span>
                                    <div class="d-flex justify-content-between py-1 pt-2" style="text-align:right;">
                                        <div><span class="text3">${d_format}</span></div>
                                    </div>
                                </div>
                            </div>
                        `
                    );

                    // $("#daily_log_comments_list").append($("<li style='font-size:18px;' class='list-group-item'>").text(comment.comment + " " + d_format));
                })
                // $('daily_log_comments_list').text(ul_list_items);
                $('#commentsModal').modal('show');
                $('.loader').hide();
                $('body').removeClass('body-overflow');
                return false;
            }
        });
    }
    $('#commentsModal').modal('hide');
    $('#addLogModal').modal('hide');
    $('#add_new_log').click(function() {
        $('#addLogModal').modal('show');
    });
</script>

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
    $('#select_category').on('change', function() {
        let staff_member = $('#staff_member').val();
        let category_id = $(this).val();
        let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        let service_user = $('#service_user').val();
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

        $.ajax({
            type: 'get',
            url: "{{ url('/service/daily-logs') }}",
            data: data,
            success: function(resp) {
                console.log(resp)
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
                    console.log()
                    // if (!service_user && !resp.log_book_records.date) {

                    // } else {

                    // }
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(` <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>`));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]['category_color']);
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
                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            // details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image
                            $(pannel_body).append($(` <div class="comment-number-bnt-info">
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                            Comments <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span></a> </div>
                                        `));

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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                    <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                        <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                            ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                        </span>
                                    </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(
                                    `
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>`
                                ));
                            }
                            // image

                            $(pannel_body).append($(` <div class="comment-number-bnt-info">
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
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
        let todayFormat = ("0" + today.getDate()).slice(-2) + "-" + ("0" + (today.getMonth() + 1)).slice(-2) + "-" +
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


        $.ajax({
            type: 'get',
            url: "{{ url('/service/daily-logs') }}",
            data: data,
            success: function(resp) {
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                            <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                    `));
                            }



                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                            <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                    ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                </span>
                                            </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${escapeHtml(resp.log_book_records[i]['title'] || '')}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image

                            $(pannel_body).append($(` <div class="comment-number-bnt-info">
                        <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                    Comments
                                    <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                    </a> </div>
                                    `));
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                            <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                    `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                            <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                    ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                </span>
                                            </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${escapeHtml(resp.log_book_records[i]['title'] || '')}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image

                            $(pannel_body).append($(`<div class="comment-number-bnt-info">
                        <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                    Comments
                                    <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                    </a></div>
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
</script>

<script>
    function removeAllChildNodes(parent) {
        while (parent.firstChild) {
            parent.removeChild(parent.firstChild);
        }
    }
</script>
<!-- sourabh -->
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

        $.ajax({
            type: 'post',
            url: "{{ url('/service/daily-logs') }}",
            data: data,
            success: function(resp) {
                console.log(resp)
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }



                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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
                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image
                            $(pannel_body).append($(`
                            <div class="comment-number-bnt-info"> <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a> </div>
                                        `));

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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image

                            $(pannel_body).append($(`
                            <div class="comment-number-bnt-info">  <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a> </div>
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
<script type="text/javascript">
    $('#staff_member').change(function() {
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

        $.ajax({
            type: 'post',
            url: "{{ url('/service/daily-logs') }}",
            data: data,
            success: function(resp) {
                console.log(resp)
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }



                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("data-placement", "left");
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon'] || 'fa fa-question');

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
                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image
                            $(pannel_body).append($(`
                            <div class="comment-number-bnt-info">  <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a></div>
                                        `));

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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i]
                                ['category_color']);
                            timeline_icon.setAttribute("title",
                                `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

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

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'],
                                    'DD-MM-YYYY H:i').format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i][
                                'details'
                            ]);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp
                                        .log_book_records[i]['late_time_text']);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format(
                                        'DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image

                            $(pannel_body).append($(`
                            <div class="comment-number-bnt-info"> <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a> </div>
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
<!-- sourabh -->
<script>
    //$('#keyword').keypress(function(){
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

        $.ajax({
            type: 'post',
            url: "{{ url('/service/daily-logs') }}",
            data: data,
            success: function(resp) {
                console.log(resp)
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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }



                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i][
                                'category_color'
                            ]);
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
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at'])
                                .format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i][
                                'created_at'
                            ]).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(
                                `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                            ));


                            pannel_body.append(created_at);
                            if (previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i')
                                .format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i')
                                    .format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp.log_book_records[i][
                                        'late_time_text'
                                    ]);
                                    span_date_field.append(span_date_field_text);
                                    // date_field.append(' | '+resp.log_book_records[i]['late_date_text']+' ');
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image
                            $(pannel_body).append($(`
                            <div class="comment-number-bnt-info">  <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a> </div>
                                        `));

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

                            if (resp.log_book_records[i]['is_late'] == '1') {
                                $(pannel_body).append($(`
                                <span class="badge badge-pill red-bg" style="position:absolute;right:30px;">Late</span>
                                        `));
                            }

                            var timeline_icon = document.createElement("span");
                            timeline_icon.setAttribute("class", "timeline-icon");
                            timeline_icon.setAttribute("data-toggle", "tooltip");
                            timeline_icon.setAttribute("data-placement", "right");
                            timeline_icon.setAttribute("style", "background:" + resp.log_book_records[i][
                                'category_color'
                            ]);
                            timeline_icon.setAttribute("title", `${resp.log_book_records[i]['category_name']}`);

                            var fa_check = document.createElement("i");
                            fa_check.setAttribute("class", resp.log_book_records[i]['category_icon']);

                            timeline_icon.append(fa_check);

                            pannel_body.append(timeline_icon);

                            var created_at = document.createElement("span");
                            created_at.setAttribute("class", "time_abbre");
                            created_at.setAttribute("data-toggle", "tooltip");
                            created_at.setAttribute("data-placement", "top");
                            created_at.setAttribute("title", moment.utc(resp.log_book_records[i]['created_at'])
                                .format('DD-MM-YYYY HH:mm'));

                            var created_at_text = document.createTextNode(moment.utc(resp.log_book_records[i][
                                'created_at'
                            ]).fromNow());
                            created_at.append(created_at_text);
                            $(created_at).append($(
                                `<span style="color:black;font-weight:400;font-size:14px;"> by ${resp.log_book_records[i]['staff_name']}</span>`
                            ));


                            pannel_body.append(created_at);

                            if (previous_date != moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i')
                                .format('DD-MM-YYYY')) {
                                $(log_atricles).append($(`
                                                <div class="header_section" style="display: table-row;text-align: center;padding: 20px 0;">
                                                    <span style="width: 120px;text-align: center;font-size: 13px;background: #1f88b5;float: right;color: white;border-radius: 4px;margin-right: -60px;margin-top:10px;margin-bottom:30px;">
                                                        ${moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i').format('DD-MM-YYYY')}
                                                    </span>
                                                </div>`));
                                previous_date = moment.utc(resp.log_book_records[i]['date'], 'DD-MM-YYYY H:i')
                                    .format('DD-MM-YYYY');
                            }

                            if (resp.log_book_records[i]['category_name'])
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span style="color: ${resp.log_book_records[i]['category_color']}">${resp.log_book_records[i]['category_name']}</span> | <span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));
                            else
                                $(pannel_body).append($(
                                    `<h1 class="title_time_log"><span>${resp.log_book_records[i]['title'] || ''}</span></h1>`
                                ));

                            var details = document.createElement("p");
                            details.className = "comment-detail-info-area";
                            var details_text = document.createTextNode(resp.log_book_records[i]['details']);
                            details.append(details_text)

                            pannel_body.append(details);

                            var date_field = document.createElement("p");
                            date_field.setAttribute("class", "daily_log_time");
                            var date_text = document.createTextNode(resp.log_book_records[i]['date']);
                            if (resp.log_book_records[i]['is_late']) {
                                if (resp.log_book_records[i]['late_time_text']) {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(resp.log_book_records[i][
                                        'late_time_text'
                                    ]);
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                } else {
                                    var span_date_field = document.createElement("span");
                                    span_date_field.setAttribute("style", "color:red");
                                    var span_date_field_text = document.createTextNode(moment.utc(resp
                                        .log_book_records[i]['created_at']).format('DD-MM-YYYY HH:mm'));
                                    span_date_field.append(span_date_field_text);
                                    date_field.append(' | ');
                                    date_field.append(span_date_field);
                                }
                            }
                            date_field.prepend(date_text);
                            // image sourabh
                            if (resp.log_book_records[i]['image_name'] != '') {
                                $(pannel_body).append($(`
                                <div class="logimg"><a href="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}"><img class="" src="{{url('upload/events/')}}/${resp.log_book_records[i]['image_name']}" style="width:100px" /></a></div>
                                            `));
                            }
                            // image

                            $(pannel_body).append($(` <div class="comment-number-bnt-info">
                            <a data-toggle="modal" onclick="daily_log_comment(${resp.log_book_records[i]['id']})" data-id="${resp.log_book_records[i]['date']}" href="#commentsModal" id="commentModal2" class="btn daily_log_comments_btn" style="background-color:#1f88b5;color:white;float:right;font-size: 10px;padding: 4px;margin-bottom:15px;">
                                        Comments
                                        <span id="_${resp.log_book_records[i]['id']}" class="badge badge-primary badge-pill comment_badge">${resp.log_book_records[i]['comments'] || 0}</span>
                                        </a> </div>
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
</script>
<!-- sourabh -->


@include('frontEnd.serviceUserManagement.elements.add_log_form')
@include('frontEnd.serviceUserManagement.elements.comments')
@endsection