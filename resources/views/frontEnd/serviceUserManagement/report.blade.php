@extends('frontEnd.layouts.master')
@section('title', 'Report')
@section('content')
    <style>
        .white-box {
            border-radius: 25px;
        }

        .box_white {
            border-radius: 25px;
            box-shadow: 0 5px 10px rgb(30 32 37 / 12%) !important;
            transition: .5s;
            color: #4691ce;
        }

        .box_white:hover {
            transform: translateY(calc(-1.5rem / 5));
        }

        .white-box {
            background: #fff;
            padding: 16px;
            margin-bottom: 30px;
        }

        .white-box .box-title {
            font-weight: 700;
            line-height: 30px;
            font-size: 18px;
        }

        .list-inline {
            padding-left: 0;
            margin-left: -5px;
            list-style: none;
        }

        .box_white {
            color: #1F88B5;
        }

        .d-flex {
            display: flex;
        }

        .ms-auto {
            margin-left: auto !important;
        }

        .list-inline .counter {
            font-size: 24px;
            font-weight: 100;
            font-weight: 600;
        }

        .text-success {
            color: #7ace4c !important;
        }

        .bg-light {
            background-color: #f7fafc !important;
        }

        .icon {
            margin-left: auto;
        }

        .icon-1 .icon_name {
            font-size: 30px;
            background-color: #7ace4c40;
            padding: 10px 16px;
            border-radius: 5px;
            color: #7ace4c;
        }

        .icon-2 .icon_name {
            font-size: 30px;
            background-color: #1f88bc2e;
            padding: 10px 16px;
            border-radius: 5px;
            color: #1f88bc;
        }

        .icon-3 .icon_name {
            font-size: 30px;
            background-color: #8175c733;
            padding: 10px 16px;
            border-radius: 5px;
            color: #8175c7;
        }

        .icon-4 .icon_name {
            font-size: 30px;
            background-color: #1f88b526;
            padding: 10px 16px;
            border-radius: 5px;
            color: #1f88b5;
        }

        /* chart css  */
        .charts {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 20px;
            width: 100%;
            padding-top: 0px;
        }

        .chart {
            background: #fff;
            padding: 15px;
            box-shadow: 0 5px 10px rgb(30 32 37 / 12%);
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .chart-row2 {
            display: grid;
            width: 100%;
            padding-top: 0px;
        }
    </style>
    <section id="container">

        <!--main content start-->
        <section id="main-content">
            <section class="wrapper">
                <div class="row">
                    <div class="row justify-content-center owl-carousel owl-theme">
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Calender Events Added</h3>
                                    </div>
                                    <div class="icon-1 icon">
                                        <span class="icon_name"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    <!-- <li>
                                                                                        <div id="sparklinedash">
                                                                                            <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0">
                                                                                                <i class="fa fa-arrow-up"></i> 16.24 %
                                                                                            </span> vs. previous month</p>
                                                                                        </div>
                                                                                    </li> -->
                                    <li class="ms-auto"><span
                                            class="counter text-success counter-value"><?= $totaleventsadded ?></span></li>
                                </ul>
                            </div>
                        </a>
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Missing Child</h3>
                                    </div>
                                    <div class="icon-2 icon">
                                        <span class="icon_name"><i class="fa fa-user-circle-o"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    {{-- <li>
                                    <div id="sparklinedash2">
                                        <p class="mb-0 text-muted"><span class="badge bg-light text-danger mb-0">
                                            <i class="fa fa-arrow-down"></i> 3.96 %
                                        </span> vs. previous month</p>
                                    </div>
                                </li> --}}
                                    <li class="ms-auto"><span
                                            class="counter text-purple counter-value"><?= $missingserviceuser ?></span></li>
                                </ul>
                            </div>
                        </a>
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Police called</h3>
                                    </div>
                                    <div class="icon-3 icon">
                                        <span class="icon_name"><i class="fa fa-gavel" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    <!-- <li>
                                                                                        <div id="sparklinedash3">
                                                                                            <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0">
                                                                                                <i class="fa fa-arrow-up"></i> 16.24 %
                                                                                            </span> vs. previous month</p>
                                                                                        </div>
                                                                                    </li> -->
                                    <li class="ms-auto"><span
                                            class="counter text-info counter-value"><?= $totalpolicecall ?></span> </li>
                                </ul>
                            </div>
                        </a>
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Appointments</h3>
                                    </div>
                                    <div class="icon-4 icon">
                                        <span class="icon_name"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    <!-- <li>
                                                                                        <div id="sparklinedash4">
                                                                                            <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0">
                                                                                                <i class="fa fa-arrow-up"></i> 16.24 %
                                                                                            </span> vs. previous month</p>
                                                                                        </div>
                                                                                    </li> -->
                                    <li class="ms-auto"><span
                                            class="counter text-purple counter-value"><?= $totalappointments ?></span></li>
                                </ul>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="row justify-content-center owl-carousel owl-theme">
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Child Mood</h3>
                                    </div>
                                    <div class="icon-1 icon">
                                        <span class="icon_name"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    <!-- <li>
                                                                                        <div id="sparklinedash">
                                                                                            <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0">
                                                                                                <i class="fa fa-arrow-up"></i> 16.24 %
                                                                                            </span> vs. previous month</p>
                                                                                        </div>
                                                                                    </li> -->
                                    <li class="ms-auto"><span
                                            class="counter text-success counter-value"><?= $totaleventsadded ?></span></li>
                                </ul>
                            </div>
                        </a>
                        <a class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Child Behavior</h3>
                                    </div>
                                    <div class="icon-2 icon">
                                        <span class="icon_name"><i class="fa fa-user-circle-o"
                                                aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    {{-- <li>
                                    <div id="sparklinedash2">
                                        <p class="mb-0 text-muted"><span class="badge bg-light text-danger mb-0">
                                            <i class="fa fa-arrow-down"></i> 3.96 %
                                        </span> vs. previous month</p>
                                    </div>
                                </li> --}}
                                    <li class="ms-auto"><span
                                            class="counter text-purple counter-value"><?= $missingserviceuser ?></span></li>
                                </ul>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                                Police Called
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    <!-- 08-25-2023 -->
                                    <!-- <a href="javascript:;" class="fa fa-cog"></a> -->
                                    <a href="javascript:;" class="fa fa-times"></a>
                                </span>
                            </header>
                            <div class="panel-body">
                                <div class="chartJS">
                                    <canvas id="bar-chart-js" height="250" width="800"></canvas>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                                Appointments
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    <!-- 08-25-2023 -->
                                    <!-- <a href="javascript:;" class="fa fa-cog"></a> -->
                                    <a href="javascript:;" class="fa fa-times"></a>
                                </span>
                            </header>
                            <div class="panel-body">
                                <div class="chartJS">
                                    <canvas id="bar-charta-js" height="250" width="800"></canvas>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="chart-row2">
                            <div class="chart">
                                <h2>Child Behavior</h2>
                                <canvas id="childRatingChart" height="250" width="800"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="chart-row2">
                            <div class="chart">
                                <h2>Child Mood</h2>
                                <canvas id="moodChart" height="250" width="800"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="charts">
                    <div class="chart">
                        <h2>last 12 month</h2>
                        <canvas id="lineChart"></canvas>
                    </div>
                    <div class="chart" id="doughnut-chart">
                            <h2>Case's</h2>
                            <canvas id="doughnut"></canvas>
                    </div>
                </div> --}}
                <!-- page end-->
            </section>
        </section>
        <!--main content end-->

    </section>
    <script src="{{ url('public/frontEnd/js/Chart2.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.counter-value').each(function() {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 3500,
                    easing: 'swing',
                    step: function(now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });
        });
    </script>

    {{-- Mood Graph start --}}
    <script>
        var mood_date = [
            @foreach ($mood_graph as $value)
                "{{ $value['date'] }}",
            @endforeach
        ];

        var mood_list = [
            @foreach ($mood_graph as $value)
                "{{ $value['name'] }}",
            @endforeach
        ];

        var moodCtx = document.getElementById("moodChart").getContext("2d");

        var moodChart = new Chart(moodCtx, {
            type: 'line',
            data: {
                labels: mood_date,
                datasets: [{
                    label: "Mood Level",
                    data: mood_list,
                    borderColor: "#2196F3",
                    backgroundColor: "rgba(33, 150, 243, 0.2)",
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: "#2196F3",
                    lineTension: 0
                }]
            },
            options: {
                responsive: true,
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 1,
                            max: 5,
                            stepSize: 1,
                            callback: function(value) {
                                return value;
                            }
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Mood (1–5)"
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "Date"
                        }
                    }]
                }
            }
        });
    </script>
    {{-- Mood Graph End --}}


    {{-- Behaviour Chart Start --}}
    <script>
        var rating_date = [
            @foreach ($rating_graph as $value)
                "{{ $value['date'] }}",
            @endforeach
        ];

        var rating_list = [
            @foreach ($rating_graph as $value)
                {{ $value['rating'] }},
            @endforeach
        ];

        var ratingCtx = document.getElementById("childRatingChart").getContext("2d");

        var childRatingChart = new Chart(ratingCtx, {
            type: 'line',
            data: {
                labels: rating_date,
                datasets: [{
                    label: "Child Rating",
                    data: rating_list,
                    borderColor: "#4CAF50",
                    backgroundColor: "rgba(76, 175, 80, 0.2)",
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: "#4CAF50",
                    lineTension: 0
                }]
            },
            options: {
                responsive: true,
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 1,
                            max: 5,
                            stepSize: 1
                        },
                        scaleLabel: {
                            display: true,
                            labelString: "Rating (1–5)"
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "Date"
                        }
                    }]
                }
            }
        });
    </script>
    {{-- Behaviour Chart End --}}

    <script>
        (function() {
            var t;

            function size(animate) {
                if (animate == undefined) {
                    animate = false;
                }
                clearTimeout(t);
                t = setTimeout(function() {
                    $("canvas").each(function(i, el) {
                        $(el).attr({
                            "width": $(el).parent().width(),
                            "height": $(el).parent().outerHeight()
                        });
                    });
                    redraw(animate);
                    var m = 0;
                    $(".chartJS").height("");
                    $(".chartJS").each(function(i, el) {
                        m = Math.max(m, $(el).height());
                    });
                    $(".chartJS").height(m);
                }, 30);
            }
            $(window).on('resize', function() {
                size(false);
            });


            function redraw(animation) {
                var options = {};
                if (!animation) {
                    options.animation = false;
                } else {
                    options.animation = true;
                }


                var barChartData = {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September",
                        "October", "November", "December"
                    ],
                    datasets: [{
                        label: 'POLICE CALLED',
                        fillColor: "#E67A77",
                        strokeColor: "#E67A77",
                        data: [<?= $policecallpermonths ?>]
                    }]

                }

                var barChartOptions = {
                    scales: {
                        y: {
                            min: 0, // Set the minimum value for the y-axis
                            max: 10, // Set the maximum value for the y-axis
                            stepSize: 1 // Set the interval between ticks on the y-axis
                        }
                    }
                };

                var myLine = new Chart(document.getElementById("bar-chart-js").getContext("2d"), {
                    type: 'bar',
                    data: barChartData,
                    options: barChartOptions
                });

                // var myLine = new Chart(document.getElementById("bar-chart-js").getContext("2d")).Bar(barChartData);


                var barChartDataa = {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September",
                        "October", "November", "December"
                    ],
                    datasets: [{
                        label: 'APPOINTMENTS ',
                        fillColor: "#E67A77",
                        strokeColor: "#E67A77",
                        data: [<?= $appointmentpermonths ?>]
                    }]

                }

                var barChartOptionss = {
                    scales: {
                        y: {
                            min: 0, // Set the minimum value for the y-axis
                            max: 10, // Set the maximum value for the y-axis
                            stepSize: 1 // Set the interval between ticks on the y-axis
                        }
                    }
                };

                var myLinea = new Chart(document.getElementById("bar-charta-js").getContext("2d"), {
                    type: 'bar',
                    data: barChartDataa,
                    options: barChartOptionss
                });


                // var myLinea = new Chart(document.getElementById("bar-charta-js").getContext("2d")).Bar(barChartDataa);


                var Linedata = {
                    labels: ["January", "February", "March", "April", "May", "June", "July"],
                    datasets: [{
                            fillColor: "#E67A77",
                            strokeColor: "#E67A77",
                            pointColor: "#E67A77",
                            pointStrokeColor: "#fff",
                            data: [100, 159, 190, 281, 156, 155, 140]
                        },
                        {
                            fillColor: "#79D1CF",
                            strokeColor: "#79D1CF",
                            pointColor: "#79D1CF",
                            pointStrokeColor: "#fff",
                            data: [65, 59, 90, 181, 56, 55, 40]
                        },
                        {
                            fillColor: "#D9DD81",
                            strokeColor: "#D9DD81",
                            pointColor: "#D9DD81",
                            pointStrokeColor: "#fff",
                            data: [28, 48, 40, 19, 96, 27, 100]
                        }

                    ]
                }
                var myLineChart = new Chart(document.getElementById("line-chart-js").getContext("2d")).Line(Linedata);


                


                var pieData = [{
                        value: 30,
                        color: "#E67A77"
                    },
                    {
                        value: 50,
                        color: "#D9DD81"
                    },
                    {
                        value: 100,
                        color: "#79D1CF"
                    }

                ];

                var myPie = new Chart(document.getElementById("pie-chart-js").getContext("2d")).Pie(pieData);



                var donutData = [{
                        value: 30,
                        color: "#E67A77"
                    },
                    {
                        value: 50,
                        color: "#D9DD81"
                    },
                    {
                        value: 100,
                        color: "#79D1CF"
                    },
                    {
                        value: 40,
                        color: "#95D7BB"
                    },
                    {
                        value: 120,
                        color: "#4D5360"
                    }

                ]
                var myDonut = new Chart(document.getElementById("donut-chart-js").getContext("2d")).Doughnut(donutData);
            }




            size(true);

        }());
    </script>


    <!-- Date Range Initialization -->






@endsection
