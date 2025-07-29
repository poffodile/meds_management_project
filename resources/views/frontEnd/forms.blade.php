@extends('frontEnd.layouts.master')
@section('title','Forms')
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
$service_user_name = (isset($service_user_name )) ? $service_user_name : 0;
@endphp

<section id="container">
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <div class="row">
                <div class="pull-right">
                    <div class="filter_buttons" style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                        <a data-toggle="modal" href="#dynmicFormModal" class="btn btn-primary  col-6" id='add_new_log'>Add New</a>
                        <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6" style="background-color:#d9534f;color:white;">PDF Export</a>
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
                        <option value="{{$val->id}}" <?php if (isset(Auth::user()->id)) {
                                                            if (Auth::user()->id == $val->id) {
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
                                <input id="date_range_input" style="cursor: pointer;" name="daterange" value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" type="text" readonly="" size="16" class="form-control log-book-datetime">
                                <span class="input-group-btn add-on datetime-picker2">
                                    <button onclick="showDate()" class="btn btn-primary" type="button"><span class="glyphicon glyphicon-calendar"></span></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- sourabh -->
                <div class="Select_staff_inner">
                    <input type="text" class="form-control" id="keyword" onKeyPress="myFunctionkey()" onKeyUp="myFunctionkey()" name="keyword" placeholder="Keyword">
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
                        <div class="timeline-messages view-dyn-record">
                        </div>
                    </div>
                </div>
            </div>
            <!-- page end-->
        </section>
    </section>
    <!--main content end-->

    <script>

        //This Ajax list the record of the form in this pae
        $(document).ready(function() {
            $('.loader').show();
            $('body').addClass('body-overflow');
            $.ajax({
                type: 'get',
                url: "{{ url('/service/dynamic-forms') }}",
                success: function(resp) {
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    console.log("resp from the ", resp);
                    if (resp == '') {
                        $('.view-dyn-record').html('<div class="text-center p-b-20" style="width:100%">No Records found.</div>');
                    } else {
                        $('.view-dyn-record').html("");
                        $('.view-dyn-record').html(resp);
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            // return false;
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
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            });
        });

        // function applyFormFilter() {
        //     var staffId = $('#staff_member').val();
        //     var childId = $('#service_user').val();
        //     let start_date = $('input[name="daterange"]').data('daterangepicker').startDate;
        //     let end_date = $('input[name="daterange"]').data('daterangepicker').endDate;
        //     var title = $('#keyword').val();

            
        //     let formdata = {
        //         'staff_id' : staffId,
        //         'child_id' : childId, 


        //         'start_date' : start_date,
        //         'end_date' : end_date,
        //         'title' : title
        //     };
        //     console.log(formdata);

        //     $.ajax({
        //         type: 'post',
        //         url: "{{ url('/service/dynamic-forms') }}" + '?search=' + search,
        //         data: formdata,
        //         success: function(resp) {
        //             if (isAuthenticated(resp) == false) {
        //                 return false;
        //             }
        //             if (resp == '') {
        //                 $('#searched-dyn-records-form .searched-record').html('No Records found.');
        //             } else {
        //                 $('#searched-dyn-records-form .searched-record').html(resp);
        //             }
        //             $('.loader').hide();
        //             $('body').removeClass('body-overflow');
        //         }
        //     });
        //     return false;
        // }

        // Apply filter automatically on change
        $('#staff_member, #service_user, #date_range_input').on('change', applyFormFilter);

        // For title field, use keyup so it filters as user types
        $('#keyword').on('keyup', function() {
            clearTimeout($(this).data('timer')); // clear previous timer
            var timer = setTimeout(applyFormFilter, 500); // delay to prevent too many requests
            $(this).data('timer', timer);
        });
    </script>

</section>


@include('frontEnd.serviceUserManagement.elements.comments')
@endsection