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
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-red"><i class="fa fa-users"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $counts['absence'] }}</span>
                        Absence Meeting
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange-bg"><i class="fa fa-files-o"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $counts['anual_leave'] }}</span>
                        Annual Leaves
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-darkgreen"><i class="fa fa-slideshare"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $counts['target'] }}</span>
                        Missed Targets
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-blue" ><i class="fa fa-envelope-o"></i></span>
                    <div class="mini-stat-info">
                        <span>05</span>
                        Message Office
                    </div>
                </div>
            </div>   -->
        </div>
        <div class="row">
            <!-- <div class="col-sm-4">
                <section class="panel">
                    <header class="panel-heading">
                        Staff Turnover
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header>
                    <div class="panel-body pie-rel">
                        <div class="chartJS">
                            <canvas id="pie-chart-js" height="220" width="440" ></canvas>
                        </div>
                        <ul class="pie-legend">
                            <li><span class="bar-legend-pointer bg-darkgreen"></span> No Risk </li>
                            <li><span class="bar-legend-pointer bg-red"></span> Joining </li>
                            <li><span class="bar-legend-pointer orange-bg"></span> Leaving </li>
                        </ul>
                    </div>
                </section>
            </div> -->
            <div class="col-sm-4">
                <section class="panel">
                    <!-- <header class="panel-heading">
                        Staff Turnover
                        <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-chevron-down"></a>
                            <a href="javascript:;" class="fa fa-cog"></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span>
                    </header> -->
                    <div class="panel-body">
                        <h4 class="widget-h">Staff Turnover</h4>
                        <div class="stf-trn">
                            <ul class="bar-legend p-t-30 p-r-40">
                              <li><span class="bar-legend-pointer" style="background:#E67A77"></span>Leaving</li>
                              <li><span class="bar-legend-pointer" style="background:#79D1CF"></span>Joining</li>
                            </ul>
                            <div class="chartJS">
                                <canvas id="bar-chart-js" height="220" width="400" style="bottom: 0px;" ></canvas>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <div class="col-md-4">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="gauge-canvas">
                                <h4 class="widget-h">Petty Cash</h4>
                                <canvas width="310" height="222" id="gauge"></canvas>
                            </div>
                            <ul class="gauge-meta clearfix">
                                <li id="gauge-textfield" class="pull-left gauge-value">{{ $counts['expenditure'] ? $counts['expenditure'] : '0'}}</li>
                                <li class="pull-right gauge-title">{{ $counts['cash_withdraw'] ? $counts['cash_withdraw'] : '0'}}</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-md-4">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="daily-visit">
                                <h4 class="widget-h">Sickness Record</h4>
                                <div id="daily-visit-chart" style="width:100%; height: 256px; display: block; min-height: 220px">

                                </div>
                                <ul class="chart-meta clearfix">

                                    <!-- <li class="pull-left visit-chart-value">20</li> -->
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
            $('select[name=\'user_type\']').html("<option value='STAFF'> Staff </option>"+"<option value='SERVICE_USER' > Child </option>");
            // $('select[name=\'select_user_id\']').attr('disabled', true);
            // $('#confirm_btn').hide();
        }

        $('#report_type_select').change(function(){
            var report_type = $('#report_type_select').val();
            if(report_type == 'ALL') { 
                $('select[name=\'user_type\']').html("<option value='STAFF'> Staff </option>"+"<option value='SERVICE_USER' > Child </option>");
                $('select[name=\'select_user_id\']').html("<option value=''> Select </option>");
                // $('select[name=\'select_user_id\']').attr('disabled', true);
                // $('#confirm_btn').hide();
            } else {
                $('select[name=\'user_type\']').html("<option value=''>Select</option>"+"<option value='SERVICE_USER'> Child </option>"+"<option value='STAFF'> Staff </option>");
                // $('select[name=\'select_user_id\']').attr('disabled', false);
                // $('#confirm_btn').show();
            }
        });

        $('#user_type').change(function(){
            var user_type_id = $(this).val();

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
<script src="{{ url('public/frontEnd/js/gauge.js') }}"></script>
<script src="{{ url('public/frontEnd/js/skycons.js') }}"></script>
<script class="include" type="text/javascript" src="{{ url('public/frontEnd/js/jquery.dcjqaccordion.2.7.js') }}"></script>
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

        var max_val = "{{ floor($counts['cash_withdraw']) }}"; //remaining petty cash balance
        // var max_val = 100;
        // set_val = 30;
     /*   var set_val     = "{{ floor($counts['cash_withdraw']) }}"; 
        set_val     = parseInt(set_val);
        if(set_val==0){
            set_val = 0.00001;
        }*/
        var expenditure = "{{ floor($counts['expenditure']) }}"; //spent cash
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
            <?php foreach($sick_graph as $key => $value){ ?>
        
            [{{ $key }}, {{ $value['point'] }}],
        
            <?php } ?>

        ];

        var ticks = [
            <?php
                foreach ($sick_graph as $key => $value) {  ?>
                    [ {{ $key }}, "{{ $value['date'] }}" ],
            <?php  } ?>

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
                min: 0,
                font: {
                    style: "normal",
                    color: "#666666"
                },
                ticks: ticks
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

    var barChartData = {

        labels : [
            @foreach ($turnover_graph as $key => $value)
            "{{ $value['year'] }}",
            @endforeach
        ],
        //labels : ["2016","2017",'2018'],

        datasets : [
            {   
                fillColor : "#E67A77",
                strokeColor : "#E67A77",
                //data : [65,59,0]
                data : [ 
                    @foreach ($turnover_graph as $key => $value)
                        {{ $value['leave'] }},
                    @endforeach
                ]
            },
            {
                fillColor : "#79D1CF",
                strokeColor : "#79D1CF",
                //data : [28,48,0]
                data : [ 
                    @foreach ($turnover_graph as $key => $value)
                        {{ $value['join'] }},
                    @endforeach
                ]
            }
        ]
    }
    var myLine = new Chart(document.getElementById("bar-chart-js").getContext("2d")).Bar(barChartData);
</script>

@endsection
