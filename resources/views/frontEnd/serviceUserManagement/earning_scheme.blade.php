
@extends('frontEnd.layouts.master')
@section('title',$labels['earning_scheme']['label'])
@section('content')

<link href="{{ url('public/frontEnd/css/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet">
<link href="{{ url('public/frontEnd/css/clndr.css') }}" rel="stylesheet">
<!--Morris Chart CSS -->
<link href="{{ url('public/frontEnd/css/morris.css') }}" rel="stylesheet"> 
<link href="{{ url('public/frontEnd/css/bucket-ico-fonts.css') }}" rel="stylesheet"> 
<?php //echo "string"; die; ?>
<section id="main-content">
    <section class="wrapper">
        <!-- page start-->
        <!--mini statistics start-->
        <div class="row">
            <div class="col-md-3">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="gauge-canvas">
                                <h4 class="widget-h"><!-- Points untill next star -->Today's progress</h4>
                                <canvas width=160 height=100 id="gauge"></canvas>
                            </div> 
                            <ul class="gauge-meta clearfix">
                                <li class="pull-left gauge-value"><span id="gauge-textfield" >0</span>%</li>
                                <li class="pull-right gauge-title gauge-remain-textfield">{{ round($record_score['pending_overall']) }}%</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-3">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="daily-visit">
                                <h4 class="widget-h">Points this week</h4>
                                <div id="daily-visit-chart" style="width:100%; height: 100px; display: block">

                                </div>
                                <ul class="chart-meta clearfix">
                                    <?php 
                                        $total_week_points = 0;
                                        
                                        foreach($week_graph as $key => $value){     
                                            $total_week_points += $value['point'];
                                        }
                                    ?>

                                    <li class="pull-left visit-chart-value">{{ $total_week_points }}</li>
                                    <!-- <li class="pull-right visit-chart-title"><i class="fa fa-arrow-up"></i> 15%</li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-3">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel view-star-history">
                            <h4 class="widget-h">Total Stars</h4>
                            <div class="star-big text-center">
                                <div class="star-biginner">
                                    <i class="fa fa-star-o"></i>
                                    <span class="star-bigno">{{ $total_stars }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-3">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">all areas</h4>
                            <div class="bar-stats">
                                <ul class="progress-stat-bar clearfix">
                                    <li data-percent="{{ ($record_score['daily_record']/25)*100 }}%"><span class="progress-stat-percent bg-blue"></span></li>
                                    <li data-percent="{{ ($record_score['living_skill']/25)*100 }}%"><span class="progress-stat-percent"></span></li>
                                    <li data-percent="{{ ($record_score['education_record']/25)*100 }}%"><span class="progress-stat-percent pink"></span></li>
                                    <li data-percent="{{ ($record_score['mfc']/25)*100 }}%"><span class="progress-stat-percent yellow-b"></span></li>
                                </ul>

                                <ul class="bar-legend">
                                    <li><span class="bar-legend-pointer bg-blue"></span>{{ $labels['daily_record']['label'] }} <!-- General Behaviour pink --></li>
                                    <li><span class="bar-legend-pointer green"></span>{{ $labels['living_skill']['label'] }}</li>
                                    <li><span class="bar-legend-pointer pink"></span>{{ $labels['education_record']['label'] }}</li>
                                    <li><span class="bar-legend-pointer yellow-b"></span>{{ $labels['mfc']['label'] }}</li>
                                </ul>

                                <div class="daily-sales-info">
                                    <!-- <span class="sales-count"> 1200 </span> <span class="sales-label">Extra star earned so far</span> --> <!-- floor shows nearest possible integer value e.g. floor(5.9) => 5 -->
                                    <span class="sales-count">{{ floor($record_score['obtained']) }}%</span> <span class="sales-label">scores earned so far {{ $record_score['obtained'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        
        <div class="row">
            <div class="form-group col-md-8">
                <div class="event-calendar clearfix">
                    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 calendar-block">
                        <div class="cal1 ">
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 event-list-block">
                        <div class="cal-day">
                            <span>Today</span>
                           {{ date('l') }}
                        </div>
                        
                        <div class="earnAreaScroller">
                            <ul class="event-list">
                                <?php                 
                                    if($record_score['daily_record'] >= $earn_area_percent['daily_record']){
                                        $daily_icon = 'fa fa-check';
                                        $daily_clas = 'color-green';
                                    }else{
                                        $daily_icon = 'fa fa-close';
                                        $daily_clas = 'color-red';                                    
                                    }
                                    if($record_score['living_skill'] >= $earn_area_percent['living_skill']){
                                        $liv_icon = 'fa fa-check';
                                        $liv_clas = 'color-green';
                                    }else{
                                        $liv_icon = 'fa fa-close';
                                        $liv_clas = 'color-red';                                    
                                    }
                                    if($record_score['mfc'] >= $earn_area_percent['mfc']){
                                        $mfc_icon = 'fa fa-check';
                                        $mfc_clas = 'color-green';
                                    }else{
                                        $mfc_icon = 'fa fa-close';
                                        $mfc_clas = 'color-red';                                    
                                    }
                                    if($record_score['education_record'] >= $earn_area_percent['education_record']){
                                        $edu_icon = 'fa fa-check';
                                        $edu_clas = 'color-green';
                                    }else{
                                        $edu_icon = 'fa fa-close';
                                        $edu_clas = 'color-red';                                    
                                    }
                                ?>    
                                @if(!empty($earning_scheme_labels))
                                    @foreach($earning_scheme_labels as $label)
                                        @if($label['label_type'] == 'M')
                                            <li class="mfc">{{ $label['name'] }}
                                        @elseif($label['label_type'] == 'G')
                                            <li class="daily-record-list">{{ $label['name'] }}
                                        @elseif($label['label_type'] == 'I')
                                            <li class="living-skill-list">{{ $label['name'] }}
                                        @elseif($label['label_type'] == 'E')
                                            <li class="education-record-list">{{ $label['name'] }}
                                        @endif
                                                @if($label['label_type'] == 'E')
                                                    <span class="close-event {{ $daily_clas }}">
                                                            <i class="{{ $daily_icon }}"></i>
                                                    </span>
                                                @elseif($label['label_type'] == 'M')
                                                    <span class="close-event {{ $mfc_clas }}">
                                                            <i class="{{ $mfc_icon }}"></i>
                                                    </span>
                                                @elseif($label['label_type'] == 'I')
                                                    <span class="close-event {{ $liv_clas }}">
                                                            <i class="{{ $liv_icon }}"></i>
                                                    </span>
                                                @elseif($label['label_type'] == 'G')
                                                    <span class="close-event {{ $daily_clas }}">
                                                            <i class="{{ $daily_icon }}"></i>
                                                    </span>
                                                @endif
                                            </li><!-- today_daily_record -->
                                    @endforeach
                                @endif
                                <!-- today_mfc_record -->
                                
                                <?php /*
                                foreach ($su_daily_record as $key => $value)
                                {
                                    if($value->scored == 0) { //if score = 0 means it does not have any score
                                        $icon = 'fa fa-close';
                                        $class = 'color-red';
                                    }else if($value->scored > 3) {
                                        $icon = 'fa fa-close';
                                        $class = 'color-red';
                                    } elseif ($value->scored <= 3) {
                                         $icon = 'fa fa-check';
                                         $class = 'color-green';
                                    }   else{ 
                                        $class = ''; $icon = '';
                                    } 
                                    ?>
                                    
                                    <li>{{ $value->description }} -Score {{ $value->scored }} <span class="close-event {{ $class }}"><i class="{{ $icon }}"></i></span></li>
                                <?php } */?>
                            </ul>
                            <p class="color-white"> Score atleast {{ $earning_target }}% to get a star. </p>
                            <a href="#" class="trgt-chn-btn">Change Target</a>
                            <!-- Stick to all daily rules to collect maximum points, 1 star for every 5 points gained. -->
                            <!-- <input type="text" class="form-control evnt-input" placeholder="NOTES" /> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-4">

                <div class="profile-nav alt">
                    <section class="panel col-md-12 col-sm-12 col-xs-12 p-0">
                        <div class="user-heading alt clock-row bg-themecolor">
                            <h1>{{ date('F d') }}   <a href="{{ url('/service/calendar/'.$service_user_id) }}"><i class="fa fa-calendar pull-right"></i></a></h1>
                            <p class="text-left">{{ date('Y, l') }}</p>
                            <p class="text-left"><span id="hh">{{ date('h') }}</span>:<span id="mm">{{ date('i') }}</span> <span id="aa">{{ date('A') }}</span></p>
                        </div>
                        <ul id="clock">
                            <li id="sec"></li>
                            <li id="hour"></li>
                            <li id="min"></li>
                        </ul>

                        <div class="below-clock dshbrd-clock">
                            <div class="incentiveScroller">
                                <ul class="below-list" type="none">
                                
                                <?php 
                                //for color class of categories
                                $i = 1; 
                                foreach ($earning_scheme as $key => $value) {
                                    
                                    $bg_color[$value['id']] = 'bg-'.$i;
                                    
                                    if($i > 5){
                                        $i = 1;   
                                    } else{
                                        $i++;
                                    }
                                } 
                                
                                foreach($booked_incentives as $key => $value) { 
                                    //if(!empty($value['calendar_id'])) {
                                    if(empty($value['calendar_id'])) {
                                        $event_date = '';
                                    } else{
                                        $event_date =  date('d',strtotime($value['event_date']));
                                    }
                                    if(empty($value['suspended_id'])) {
                                        $text_line = '';
                                        // $i_class   = 'fa fa-check';
                                       // $s_status  = 'color-green';
                                    } else {
                                        $text_line = 'suspend-line';
                                        // $i_class   = 'fa fa-close';
                                        //$s_status  = 'color-red';
                                    }
                                    ?>
                                        <li class="{{ $text_line }}"> 
                                            <span class="text-center {{ $bg_color[$value['earning_category_id']] }}"> {{ $event_date }} </span>  {{ $value['name'] }} {{ (!empty($value['time'])) ? '-':''  }} {{ $value['time'] }} 
                                            <a href="#" class="incentive_suspended" calndr_id="{{ $value['calendar_id'] }}" su_ern_inc_id="{{ $value['su_ern_inc_id'] }}" su_incentive_suspended_id="{{ $value['suspended_id'] }}"> <span class="clock-cross"><i class="fa fa-close"></i></span> </a>
                                         </li> 
                                <?php }  ?>
                                </ul>
                                
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">

                <!--notification start-->
                <section class="panel">
                    <header class="panel-heading">
                        Notification <!-- <span class="tools pull-right">
                        <a href="javascript:;" class="fa fa-chevron-down"></a>
                        <a href="javascript:;" class="fa fa-cog"></a>
                        <a href="javascript:;" class="fa fa-times"></a>
                        </span> -->
                    </header>
                    <div class="panel-body"> <!-- cus-alert  -->
                        @include('frontEnd.serviceUserManagement.elements.su_profile_notification')

                    </div>
                </section>
                    <!--notification end-->
            </div>

                <div class="col-md-6 col-sm-6 col-xs-12 p-0 dashboard-box">
                    <?php  foreach ($earning_scheme as $key => $value) { ?>
                    <div class="col-md-4">
                        <div class="profile-nav alt incentive_details" earning_category_id="{{ $value['id'] }}">
                            <section class="panel text-center">
                                <div class="user-heading alt wdgt-row {{ $bg_color[$value['id']] }}"> <i class="{{ $value['icon'] }}"></i> </div>
                                <div class="panel-body">
                                    <div class="wdgt-value">
                                        <h1 class="count">{{ $value['incentives_count'] }}</h1>
                                        <p> {{ $value['title'] }} </p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <!-- page end-->
    </section>
</section>

@include('frontEnd.serviceUserManagement.elements.earning_incentives')
@include('frontEnd.serviceUserManagement.elements.earning_history')
@include('frontEnd.serviceUserManagement.elements.earning_target')
@include('frontEnd.serviceUserManagement.elements.earning_incentive_suspend')
<!-- include('frontEnd.serviceUserManagement.elements.earning_today_record') -->

<script src="{{ url('public/frontEnd/js/skycons.js') }}"></script>
<script src="{{ url('public/frontEnd/js/gauge.js') }}"></script>
<script src="{{ url('public/frontEnd/js/calendar/clndr.js') }}"></script>
<script src="{{ url('public/frontEnd/js/calendar/moment-2.2.1.js') }}"></script>
<!-- <script src="{{ url('public/frontEnd/js/evnt.calendar.init.js') }}"></script> -->
<script src="{{ url('public/frontEnd/js/morris-chart/morris.js') }}"></script>
<script src="{{ url('public/frontEnd/js/morris-chart/raphael-min.js') }}"></script>
<script src="{{ url('public/frontEnd/js/dashboard.js') }}"></script>
<script src="{{ url('public/frontEnd/js/jquery.customSelect.min.js') }}" ></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>

<!--jQuery Flot Chart - line chart-->
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.tooltip.min.js') }}"></script>

<script>
    $(document).ready(function(){
        autosize($("textarea"));
        
        //update_time of watch in r.h.s
        function update_time(){
                
            var hours   = moment().tz("Europe/London").format("HH");
            var mins    = moment().tz("Europe/London").format("mm");
            var seconds = moment().tz("Europe/London").format("ss");
            var year    = moment().tz("Europe/London").format("YYYY");
            var month   = moment().tz("Europe/London").format("MM");
            var date    = moment().tz("Europe/London").format("DD");

            // var hours = new Date().getUTCHours();
            // var mins =  new Date().getUTCMinutes();
           var mid='am';
            
            if(hours==0){ //At 00 hours we need to show 12 am
                hours=12;
            }
            else if(hours>=12)
            {
                if (hours==12) {
                    hours = hours;
                } else {
                    hours = hours-12;
                    if (hours < 10) {
                    hours = '0'+hours;
                }
                    mid   = 'pm'; 
                }
                // hours=hours-12;
                // if (hours < 10) {
                //     hours = '0'+hours;
                // }
                mid='pm';
            }

            // if(hours < 10){
            //     hours = '0'+hours;
            // }
            // if(mins < 10){
            //     mins = '0'+mins;
            // }
            $("#hh").text(hours);
            $("#mm").text(mins);
            $("#aa").text(mid);
        }
        window.setInterval(update_time,1000);
    });
    /*function calcTime(){
        var city = "London";
        // var offset = "-10";  //by karan working perfectly in our system but not in client's sys.
        var offset = "-13"; // -13 use for time becoming less then 3hours to original time
        var d = new Date();
        // subtract local time zone offset
        // get UTC time in msec
        var utc = d.getTime() - (d.getTimezoneOffset() * 60000);
        //alert(utc);

        // create new Date object for different city
        // using supplied offset
        var nd = new Date(utc + (3600000*offset));

        //alert(nd);
        // return time as a string
        //return  nd.toLocaleString();
        var hours = nd.getHours();
        var mins  =  (nd.getMinutes()<10?'0':'') + nd.getMinutes();
        var mid   ='AM';
        
        if(hours == 0){ //At 00 hours we need to show 12 am
            hours = 12;
        
        } else if(hours >= 12) {
            hours = hours - 12;
            mid = 'PM';
        }

        if(hours < 10){
            hours = '0'+hours;
        }
      

        $("#hh").text(hours);
        $("#mm").text(mins);
               
    }
    window.setInterval(calcTime,1000);*/
</script>

<script>
    //javascript of gauge (meter - points untill nexr star)
    if (Gauge) {
        /*Knob*/
        var opts = {
            lines: 12, // The number of lines to draw
            angle: 0, // The length of each line
            lineWidth: 0.48, // The line thickness
            pointer: {
                length: 0.6, // The radius of the inner circle
                strokeWidth: 0.03, // The rotation offset
                color: '#464646' // Fill color
            },
            limitMax: false,
            limitMin: false, 
            colorStart: '#fa8564', // Colors
            colorStop: '#fa8564', // just experiment with them
            strokeColor: '#F1F1F1', // to see which ones work best for you
            generateGradient: true
        };

        var max_val = 100;
        var set_val = "{{ floor($record_score['obtained']) }}";
        set_val     = parseInt(set_val);
       
        if(set_val==0){
            set_val = 0.00001;
        }
        var target = document.getElementById('gauge'); // your canvas element
        var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
        gauge.maxValue = max_val; // set max gauge value
        gauge.animationSpeed = 32; // set animation speed (32 is default value)
        gauge.set(set_val); // set actual value
        gauge.setTextField(document.getElementById("gauge-textfield"));
    }
</script>
<script >
    //javascript of graph - points this week (line chart)
   if ($.fn.plot) {

    var d1 = [
        <?php foreach($week_graph as $key => $value){ ?>
    
        [{{ $key }}, {{ $value['point'] }}],
    
        <?php } ?>
    ];

    var ticks = [
        <?php foreach($week_graph as $key => $value){ ?>
    
        [{{ $key }}, "{{ $value['date'] }}"],
    
        <?php } ?>
    ];

    var data = ([{
        label: "Point",
        data: d1,
        lines: {
            show: true,
            fill: true,
            lineWidth: 2,
            fillColor: {
                colors: ["rgba(255,255,255,.1)", "rgba(160,220,220,.8)"]
            }
        }
    }]);
    
    var options = {
        grid: {
            backgroundColor: {
                colors: ["#fff", "#fff"]
            },
            borderWidth: 0,
            borderColor: "#f0f0f0",
            margin: 0,
            minBorderMargin: 0,
            labelMargin: 20,
            hoverable: true,
            clickable: true
        },
        // Tooltip
        tooltip: false,
        tooltipOpts: {
            content: "%s X: %x Y: %y",
            shifts: {
                x: -60,
                y: 25
            },
            defaultTheme: false
        },

        legend: {
            labelBoxBorderColor: "#ccc",
            show: false,
            noColumns: 0
        },
        series: {
            stack: true,
            shadowSize: 0,
            highlightColor: 'rgba(30,120,120,.5)'

        },
        xaxis: {
            tickLength: 0,
            tickDecimals: 0,
            show: true,
            min: 0,
            font: {

                style: "normal",
                color: "#666666"
            },
            ticks: ticks
        },
        yaxis: {
            ticks: 4,
            tickDecimals: 0,
            show: true,
            tickColor: "#f0f0f0",
            max:2, //graph y
            font: {

                style: "normal",


                color: "#666666"
            }
        },
        //        lines: {
        //            show: true,
        //            fill: true
        //
        //        },
        points: {
            show: true,
            radius: 2,
            symbol: "circle"
        },
        colors: ["#87cfcb", "#48a9a7"]
    };
    var plot = $.plot($("#daily-visit-chart"), data, options);

}
</script>

<script>
    //for showing bar graph chart in right hand side
    $('.progress-stat-bar li').each(function () {
        $(this).find('.progress-stat-percent').animate({
            height: $(this).attr('data-percent')
        }, 1000);
    });
</script>

<script>
     $(document).ready(function(){
        $('.incentive_details').on('click',function(){

            var earning_category_id = $(this).attr('earning_category_id');
            var earning_category_name = $(this).find('p').text();

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type: 'get',
                url : "{{ url('/service/earning-scheme/view_incentive') }}"+'/'+earning_category_id,
                success: function(resp) {
                    
                    if(isAuthenticated(resp) == false){
                        return false;
                    }

                    $('.view_incentive_details').html(resp);
                    $('#incentivesModal .modal-title').text(earning_category_name);
                    $('#incentivesModal').modal('show');

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');   
                }
            });
            return false; 
        });

        $(document).on('click','.confirm_to_clndr', function() {

            var star = $(this).attr('stars_need');
            
            if(star > 1){
                star= star+' stars';
            } else{
                star= star+' star';
            }

            if(confirm("This incentive will cost "+star+", Are you sure you want to continue?")) {

                var link = $(this).attr('href');
                var new_link = link+'&service_user_id={{ $service_user_id }}';
                $(this).attr('href',new_link);
                return true;
        
            } else {
                return false;
            }
        });
    });
</script>
<script>
    //earn areas scroller
    $(".earnAreaScroller").slimScroll();
</script>
<script>
    //calendar events list scroller
    $(".incentiveScroller").slimScroll({height:'180px'});
</script>

<script>
    //show selected dates in calendar
    var lotsOfMixedEvents = [
        <?php
        foreach($booked_incentives as $key => $value) { 
            if(!empty($value['calendar_id'])) { ?>
            {
                date: '{{ $value["event_date"] }}',
                title: 'Monday to Friday Event',
                color: '#ffffff'
            },
        <?php } } ?>
    ];
    
    $('.cal1').clndr({
        events: lotsOfMixedEvents
        // multiDayEvents: {
        //     endDate: 'end',
        //     singleDay: 'date',
        //     startDate: 'start'
        // }
    });
</script>

<script>
    colorful_events();
    $(document).on('click','.clndr-next-button', function(){
  
        setTimeout(function(){
            colorful_events();            
        }, 10);
    });
    $(document).on('click','.clndr-previous-button', function(){

        setTimeout(function(){
            colorful_events();            
        }, 10);
    });
    function colorful_events(){
        <?php
        foreach($booked_incentives as $key => $value) { 
            if(!empty($value['calendar_id'])) { ?>    
                $('.cal1 .clndr .clndr-table tr .calendar-day-{{ $value["event_date"] }}').children().addClass("{{ $bg_color[$value['earning_category_id']] }}"); 
        <?php } } ?>
    }
</script>


<script>
    /*--------- Script used for cog icon click specially in EarningScheme webpage -----------*/
    //----- click on cog(setting) icon to view options of a record
    $(document).ready(function(){
        $(document).on('click','.settings',function(){ //alert(1);
            $(this).find('.pop-notifbox').toggleClass('active');
            $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
        });
        $(window).on('click',function(e){
            e.stopPropagation();
            var $trigger = $(".settings");
            if($trigger !== e.target && !$trigger.has(e.target).length){
                $('.pop-notifbox').removeClass('active');
            }
        });
    });
</script>

@endsection