@extends('frontEnd.layouts.master')
@section('title', 'Forms')
@section('content')

    <link rel="stylesheet" href="{{ url('public\frontEnd\css\time-line.css') }}">

    @php
        if (!function_exists('time_diff_string')) {
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
        }
        $service_user_id = isset($service_user_id) ? $service_user_id : 0;
        $service_user_name = isset($service_user_name) ? $service_user_name : 0;
    @endphp

    <section id="container">
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="pull-right">
                        <div class="filter_buttons"
                            style="text-align:right;padding-right:16px;display:inline-block; padding-bottom: 10px;">
                            <a data-toggle="modal" href="#dynmicFormModal" class="btn btn-primary  col-6"
                                id='add_new_log'>Add New</a>
                            <a onclick="pdf()" id="pdf" target="_blank" class="btn col-6"
                                style="background-color:#d9534f;color:white;">PDF Export</a>
                        </div>
                    </div>
                </div>
                <!-- page start-->
                <div class="Select_staff">
                    <div class="">
                        <a class="back_opt col-3" onclick="history.back()">
                            <i class="fa fa-angle-left"></i>
                        </a>
                    </div>
                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <select class="form-control" name="staff_member" id="staff_member">
                            <option value="">Select Staff Member</option>
                            @foreach ($staff_members as $val)
                                <option value="{{ $val->id }}" <?php if (isset(Auth::user()->id)) {
                                    if (Auth::user()->id == $val->id) {
                                        echo 'Selected';
                                    }
                                } ?>>{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="Select_staff_inner">
                        <select class="form-control" name="service_user" id="service_user" <?php if (isset($_GET['key'])) {
                            echo 'disabled';
                        } ?>>
                            <option value="">Select Child</option>
                            @foreach ($service_users as $val)
                                <option <?php if (isset($_GET['key'])) {
                                    if ($_GET['key'] == $val->id) {
                                        echo 'Selected';
                                    }
                                } ?> value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <div class="datepicker-sttng date-sttng">
                            <label style="display: none;"> Date: </label>
                            <div>
                                @php
                                    $today = \Carbon\Carbon::now()->format('d-m-Y');
                                    $oneMonthAgo = \Carbon\Carbon::now()->subMonth()->format('d-m-Y');
                                @endphp
                                <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date=""
                                    class="input-group date">
                                    <input id="date_range_input" style="cursor: pointer;" name="daterange"
                                        value="{{ date('d-m-Y') }} - {{ date('d-m-Y') }}" 
                                        {{-- value="{{ $oneMonthAgo }} - {{ $today }}"  --}}
                                        type="text" readonly=""
                                        size="16" class="form-control log-book-datetime">
                                    <span class="input-group-btn add-on datetime-picker2">
                                        <button onclick="showDate()" class="btn btn-primary" type="button"><span
                                                class="glyphicon glyphicon-calendar"></span></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- sourabh -->
                    <div class="Select_staff_inner">
                        <input type="text" class="form-control" id="keyword" onKeyPress="myFunctionkey()"
                            onKeyUp="myFunctionkey()" name="keyword" placeholder="Keyword">
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
                            <div class="timeline-messages view-dyn-record"> </div>
                        </div>
                    </div>
                </div>
                <!-- page end-->
            </section>
        </section>
        <!--main content end-->
    </section>


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
                        $('.view-dyn-record').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
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
                console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end
                    .format('YYYY-MM-DD'));
            });
        });

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

        function getFormData(data) {
            $.ajax({
                type: 'post',
                url: "{{ url('/service/dynamic-forms') }}",
                data: data,
                success: function(resp) {
                    console.log(resp)
                    if (isAuthenticated(resp) == false) {
                        return false;
                    }
                    console.log("resp from the ", resp);
                    if (resp == '') {
                        $('.view-dyn-record').html(
                            '<div class="text-center p-b-20" style="width:100%">No Records found.</div>'
                        );
                    } else {
                        $('.view-dyn-record').html("");
                        $('.view-dyn-record').html(resp);
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

    <!-- {{-- Filter for staff --}} -->
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

    @include('frontEnd.serviceUserManagement.elements.comments')
@endsection
