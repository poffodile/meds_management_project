@extends('frontEnd.layouts.master')
@section('title','View Report')
@section('content')

<style type="text/css">
    .orange-bg {
        background: #ed6a22 none repeat scroll 0 0;
    }
    .bg-darkgreen {
        background: #4ab661 none repeat scroll 0 0;
    }
</style>

<!--main content start-->
<section id="main-content">
    <section class="wrapper">

        @include('frontEnd.viewReports.elements.report_filter') 
    
        <div class="row">
            <div class="col-md-4">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="gauge-canvas">
                                <h4 class="widget-h">Money Spent</h4>
                                <canvas width="160" height="100" id="gauge"></canvas>
                            </div>
                            <ul class="gauge-meta clearfix">
                                <li id="gauge-textfield" class="pull-left gauge-value">{{ $count['spent'] ? $count['spent'] : '0'}}</li>
                                <li class="pull-right gauge-title">{{ $count['weekly_allowance'] ? $count['weekly_allowance'] : '0'}}</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-2">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">Star Given</h4>
                            <div class="star-big text-center">
                                <div class="star-biginner">
                                    <i class="fa fa-star-o"></i>
                                    <span class="star-bigno">{{ $count['star'] ? $count['star'] : '0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-2">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">{{ $labels['incident_report']['label'] }}</h4>
                            <div class="star-big text-center">
                                <div class="star-biginner">
                                    <i class="{{ $labels['incident_report']['icon'] }}"></i>
                                    <span class="star-bigno" style="margin-left:40px">{{ $count['incident'] ? $count['incident'] : '0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-md-4">
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
                                    <li><span class="bar-legend-pointer bg-blue"></span>{{ $labels['daily_record']['label'] }}</li>
                                    <li><span class="bar-legend-pointer green"></span>{{ $labels['living_skill']['label'] }}</li>
                                    <li><span class="bar-legend-pointer pink"></span>{{ $labels['education_record']['label'] }}</li>
                                    <li><span class="bar-legend-pointer yellow-b"></span>{{ $labels['mfc']['label'] }}</li>
                                </ul>
                                <!-- <div class="daily-sales-info">
                                    <span class="sales-count">{{ floor($record_score['obtained']) }}%</span> <span class="sales-label">scores earned so far {{ $record_score['obtained'] }}</span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['in_danger'] }}</span>
                        In Danger
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange-bg"><i class="fa fa-exclamation"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['assistance'] }}</span>
                        Need Assistance
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-darkgreen"><i class="fa fa-phone"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['req_cb'] }}</span>
                        Request Callback
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-blue" ><i class="fa fa-envelope-o"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['off_mesg'] }}</span>
                        Message Office
                    </div>
                </div>
            </div>  
        </div>
        <div class="row">
            <div class="col-sm-4">
                <section class="panel">
                   <!--  <header class="panel-heading">
                        Risk
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header> -->
                    <div class="panel-body pie-rel">
                        <h4 class="widget-h">Risk</h4>
                        <div class="chartJS">
                            <canvas id="pie-chart-js" height="250" width="440" ></canvas>
                        </div>
                        <ul class="pie-legend">
                            <li><span class="bar-legend-pointer bg-darkgreen"></span> No Risk </li>
                            <li><span class="bar-legend-pointer bg-red"></span> Live Risk </li>
                            <li><span class="bar-legend-pointer orange-bg"></span> Historic Risk </li>
                        </ul>
                    </div>
                </section>
            </div>
            
            <div class="col-md-5">
                <section class="panel">
                    <!-- <header class="panel-heading">
                        Mood Chart
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header> -->
                    <div class="panel-body">
                        <h4 class="widget-h">Mood Chart</h4>
                        <div class='col-md-2 mood_chart_side'>
                        <?php foreach ($moods as $key => $mood) { ?>
                            {{ $mood['name'] }} 
                        <?php } ?>
                        </div>
                        <div class="chartJS col-md-3">
                            <canvas id="line-chart-js" height="250" width="400" ></canvas>
                        </div>
                    </div>
                </section>  
            </div>

            <div class="col-md-3">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="daily-visit">
                                <h4 class="widget-h">MFC</h4>
                                <div id="daily-visit-chart2" style="width:100%; height: 100px; display: block; min-height: 220px">

                                </div>
                                <ul class="chart-meta clearfix">
                                    <?php 
                                        $total_week_points = 0;

                                        foreach ($week_graph as $key => $value) {
                                           $total_week_points += $value['point'];
                                        }
                                    ?>
                                    <li class="pull-left visit-chart-value">{{ $total_week_points }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</section>
<!--main content end-->

@include('frontEnd.common.system_guide')

<script>
    $(document).ready(function(){
        var report_type = $('#report_type_select').val();
        if(report_type == 'ALL') {
            $('select[name=\'user_type\']').html("<option value='SERVICE_USER'> Child </option>"+"<option value='STAFF' > Staff </option>");
            // $('select[name=\'select_user_id\']').attr('disabled', true);
            // $('#confirm_btn').hide();
        }

        $('#report_type_select').change(function(){
            var report_type = $('#report_type_select').val();
            if(report_type == 'ALL') { 
                $('select[name=\'user_type\']').html("<option value='SERVICE_USER'> Child </option>"+"<option value='STAFF' > Staff </option>");
                $('select[name=\'select_user_id\']').html("<option value=''> Select </option>");
                // $('select[name=\'select_user_id\']').attr('disabled', true);
                // $('#confirm_btn').hide();
            } else {
                $('select[name=\'user_type\']').html("<option value=''>Select</option>"+"<option value='SERVICE_USER'> Child </option>"+"<option value='STAFF'> Staff </option>");
                $('select[name=\'select_user_id\']').attr('disabled', false);
                // $('#confirm_btn').show();
            }
        });

        $('#user_type').change(function(){
            var user_type_id = $(this).val();
            console.log("user_type_id", user_type_id);
            if(user_type_id == '') {
                $('select[name=\'select_user_id\']').html("<option value=''>Select</option>");
                return false;
            } else if(user_type_id == 'SERVICE_USER') {
                $('select[name=\'select_user_id\']').html("<option value=''>Select Child</option>");
            } else {
                $('select[name=\'select_user_id\']').html("<option value=''>Select Staff</option>");
            }

            $('.loader').show();
            $('body').addClass('body-overflow');

            $.ajax({
                type : 'get',
                 url  : "{{ url('/users') }}"+'/'+ user_type_id,
                // url  : "{{ url('/users') }}"+'?user_type_id='+user_type_id,
                success : function(resp) {
                    console.log("child List", resp);
                    if(isAuthenticated(resp) == false){
                        return false;
                    }
                    if(resp != '') {
                        if(user_type_id == 'SERVICE_USER') {
                            $('#select_user').html('<option value="0">Select Child</option>'+resp);
                        } else {
                            $('#select_user').html('<option value="0">Select Staff</option>'+resp);
                        }
                    } else {
                        $('#select_user').html('<option value="0">Select</option>');
                    }

                    $('.loader').hide();
                    $('body').removeClass('body-overflow');
                }
            });
            return false;
        });
    });
</script>

<!-- Placed js at the end of the document so the pages load faster -->

<!--Core js-->
<script src="{{ url('public/frontEnd/js/gauge.js') }}"></script>
<script src="{{ url('public/frontEnd/js/skycons.js') }}"></script>
<script class="include" type="text/javascript" src="{{ url('public/frontEnd/js/jquery.dcjqaccordion.2.7.js') }}"></script>
<!-- <script src="{{ url('public/frontEnd/js/jquery.scrollTo.min.js') }}"></script> -->
<!-- <script src="{{ url('public/frontEnd/js/jQuery-slimScroll-1.3.0/jquery.slimscroll.js') }}"></script> -->
<!-- <script src="{{ url('public/frontEnd/js/jquery.nicescroll.js') }}"></script> -->
<!-- morris chart -->
<!-- <script src="{{ url('public/frontEnd/js/morris-chart/morris.js') }}"></script>
<script src="{{ url('public/frontEnd/js/morris-chart/raphael-min.js') }}"></script>
<script src="{{ url('public/frontEnd/js/morris.init.js') }}"></script> -->
<!--Easy Pie Chart-->
<script src="{{ url('public/frontEnd/js/dashboard.js') }}"></script>
<script src="{{ url('public/frontEnd/js/easypiechart/jquery.easypiechart.js') }}"></script>
<!--Sparkline Chart-->
<script src="{{ url('public/frontEnd/js/sparkline/jquery.sparkline.js') }}"></script>
<!--jQuery Flot Chart-->
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/flot.chart.init.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.resize.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.pie.resize.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.selection.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.stack.js') }}"></script>
<script src="{{ url('public/frontEnd/js/flot-chart/jquery.flot.time.js') }}"></script>


<!--Chart JS-->

<script src="{{ url('public/frontEnd/js/Chart.js') }}"></script>
<!-- <script src="{{ url('public/frontEnd/js/chartjs.init.js') }}"></script> -->

<!--common script init for all pages-->


<!-- <script src="{{ url('public/frontEnd/js/pieChart/jquery.easy-pie-chart.js') }}"></script>
<script src="{{ url('public/frontEnd/js/pieChart/easy-pie-chart.js') }}"></script>
<script src="{{ url('public/frontEnd/js/pieChart/sparkline-chart.js') }}"></script> -->

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

        var max_val = "{{ floor($count['weekly_allowance']) }}"; //remaining petty cash balance
        // var max_val = 100;
        // set_val = 30;
     /*   var set_val     = "{{ floor($count['cash_withdraw']) }}"; 
        set_val     = parseInt(set_val);
        if(set_val==0){
            set_val = 0.00001;
        }*/
        var expenditure = "{{ floor($count['spent']) }}"; //spent cash
        expenditure = parseInt(expenditure);
        if(expenditure==0){
            expenditure = 0.00001;
        }

        var target = document.getElementById('gauge'); // your canvas element
        var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
        gauge.maxValue = max_val; // set max gauge value
        gauge.animationSpeed = 32; // set animation speed (32 is default value)
        gauge.set(expenditure); // set actual value
        // gauge.setTextField(document.getElementById("gauge-textfield"));
    }
</script>

<script>
    //(line chart)
    if ($.fn.plot) {

        var d1 = [
            [0, 10],
            [1, 20],
            [2, 33],
            [3, 24],
            [4, 45],
            [5, 96],
            [6, 47],
            [7, 18],
            [8, 11],
            [9, 13],
            [10, 21]

        ];
        var data = ([{
            label: "Too",
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
            tooltip: true,
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
                min: 2,

                font: {

                    style: "normal",


                    color: "#666666"
                }
            },
            yaxis: {
                ticks: 3,
                tickDecimals: 0,
                show: true,
                tickColor: "#f0f0f0",
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
    //(line chart2)
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

       /* var min_y = "{{ $key }}, {{ $value['point'] }}",
        if(min_y == '') {
            min_y = 2;
        }*/


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
            tooltip: true,
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
                ticks: ticks  //for week dates
            },
            yaxis: {
                ticks: 4,
                tickDecimals: 0,
                show: true,
                tickColor: "#f0f0f0",
                // max: "{{ $key }}, {{ $value['point'] }}",
                // min: 2,
                // max:2, //graph y
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
        var plot = $.plot($("#daily-visit-chart2"), data, options);
    }
</script> 

<script>
    var pieData = [
        {
            value: {{ $risk_count['historic'] }},
            color:"#ed6a22"
        },
        {
            value : {{ $risk_count['live'] }},
            color : "#e13533"
        },
        {
            value : {{ $risk_count['no_risk'] }},
            color : "#4ab661"
        }
    ];
    var myPie = new Chart(document.getElementById("pie-chart-js").getContext("2d")).Pie(pieData);
</script>

<?php //echo "<pre>"; print_r($moods); die; ?>

<script>
    var week_date = [
        <?php foreach($mood_graph as $key => $value){ ?>
            //["{{ $value['date'] }}"],
            ["{{ $value['date'] }}"],
        <?php } ?>
    ];
   
    var mood_list = [ 
        <?php foreach ($mood_graph as $key => $mdd) { ?>
            //["{{ $mdd['mood_value'] }}"],
            ["{{ $mdd['mood_value'] }}"],
        <?php } ?>
    ];
    
    var Linedata = {
        labels : week_date,
        datasets : [
            {   
                fillColor : "#E67A77",
                strokeColor : "#E67A77",
                pointColor : "#E67A77",
                pointStrokeColor : "#fff",
                label: "My Duration",
                data: mood_list,
            }

        ]
    }

    var myLineChart = new Chart(document.getElementById("line-chart-js").getContext("2d")).Line(Linedata);
</script>

@endsection
