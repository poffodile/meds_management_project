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
                        <a href="{{ url('service/incident-report/' . base64_decode($_GET['key'])) }}"
                            class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Incident Reports</h3>
                                    </div>
                                    <div class="icon-1 icon">
                                        <span class="icon_name"><i class="fa fa-shield" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">

                                    <li class="ms-auto"><span
                                            class="counter text-success counter-value"><?= $incidentCount ?></span></li>
                                </ul>
                            </div>
                        </a>
                        <a href="{{ url('service/behavior/' . base64_decode($_GET['key'])) }}"
                            class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Child Behavior</h3>
                                    </div>
                                    <div class="icon-2 icon">
                                        <span class="icon_name"><i class="fa fa-star" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">

                                    <li class="ms-auto"> @php
                                        $displayAvg = isset($avg_rating) ? (float) $avg_rating : 0;
                                        $fullStars = floor($displayAvg);
                                        $hasHalf = $displayAvg - $fullStars >= 0.5 ? true : false;
                                    @endphp
                                        <span class="profile-rating" title="{{ $rating_count ?? 0 }} rating(s)"
                                            style="vertical-align:middle; font-size: 22px;">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $fullStars)
                                                    <i class="fa fa-star" style="color:#f90"></i>
                                                @elseif ($i == $fullStars + 1 && $hasHalf)
                                                    <i class="fa fa-star-half-o" style="color:#f90"></i>
                                                @else
                                                    <i class="fa fa-star-o" style="color:#ccc"></i>
                                                @endif
                                            @endfor
                                            <small style="margin-left:6px; color:#666;">({{ $displayAvg }})</small>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </a>
                        <a href="{{ url('service/mood/' . base64_decode($_GET['key'])) }}"
                            class="col-lg-3 round_box col-md-4 col-sm-1 item">
                            <div class="white-box analytics-info box_white counter">
                                <div class="d-flex">
                                    <div class="heading">
                                        <h3 class="box-title">Child Mood</h3>
                                    </div>
                                    <div class="icon-2 icon">
                                        <span class="icon_name" style="font-size:30px;">😊</span>
                                    </div>
                                </div>
                                <ul class="list-inline two-part d-flex align-items-center mb-0">
                                    <li class="ms-auto"><span class="counter text-purple" style="font-size: 16px"> Today's Mood @if (isset($current_moods))
                                                            <img src="{{ url(MoodImgPath . '/' . $current_moods->image) }}" style="width: 30px;">
                                                        @endif</span></li>
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
                        <section class="panel">
                            <header class="panel-heading">
                                Child Behavior
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    <a href="javascript:;" class="fa fa-times"></a>
                                </span>
                            </header>
                            <div class="panel-body">
                                <div class="chartJS">
                                    <canvas id="behaviorChart" height="150" width="800"></canvas>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-sm-12">
                        <section class="panel">
                            <header class="panel-heading">
                                Child Mood
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    <a href="javascript:;" class="fa fa-times"></a>
                                </span>
                            </header>
                            <div class="panel-body">
                                <div class="chartJS">
                                    <canvas id="moodChart" height="150" width="800"></canvas>
                                    <div id="moodLegend"
                                        style=" display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                                        @foreach ($mood as $moodItem)
                                            <div style="display: flex; align-items: center;">
                                                <span>{{ $moodItem->id }}</span> - <span>{{ $moodItem->name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <section class="panel">
                            <header class="panel-heading">
                                Task
                                <span class="tools pull-right">
                                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                                    <a href="javascript:;" class="fa fa-times"></a>
                                </span>
                            </header>
                            <div class="panel-body pie-rel">
                                <div class="chartJS">
                                    <canvas id="pie-chart-js" height="330" width="530"></canvas>
                                </div>
                            </div>
                        </section>
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

    <!--Easy Pie Chart-->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script> --}}

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

    <?php //print_r($task_count); ?>

    {{-- Pie Chart Start --}}
    <script>
        var ctx = document.getElementById("pie-chart-js").getContext("2d");

        var myPie = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ["Completed Targets", "Active Targets", "Pending Targets"],
                datasets: [{
                    data: [
                        {{ $task_count['completed_targets'] }},
                        {{ $task_count['active_targets'] }},
                        {{ $task_count['pending_targets'] }}
                    ],
                    backgroundColor: [
                        "#4ab661", // Completed - Green
                        "#e13533", // Active - Red
                        "#ed6a22"  // Pending - Orange
                    ]
                }]
            },
            options: {
                responsive: false,
                animation: {
                    animateScale: true
                }
            }
        });
    </script>
    {{-- Pie Chart End --}}


    {{-- Mood Graph start --}}
    <script>
        var moodDates = [
            @foreach ($mood_graph as $item)
                "{{ $item['date'] }}",
            @endforeach
        ];

        var moodValues = [
            @foreach ($mood_graph as $item)
                {{ $item['mood_id'] }},
            @endforeach
        ];

        var moodNames = {
            @foreach ($mood as $m)
                {{ $m->id }}: "{{ $m->name }}",
            @endforeach
        };



        // Manual formatter (NO UTC issue)
        function formatDate(d) {
            let year = d.getFullYear();
            let month = ('0' + (d.getMonth() + 1)).slice(-2);
            let day = ('0' + d.getDate()).slice(-2);
            return `${year}-${month}-${day}`;
        }

        var fullMoodDates = [];
        var finalMoodValues = [];

        var startDate = new Date();
        startDate.setDate(startDate.getDate() - 29);

        for (let i = 0; i < 30; i++) {

            let d = new Date(startDate);
            d.setDate(startDate.getDate() + i);

            let formatted = formatDate(d);

            fullMoodDates.push(formatted);

            let index = moodDates.indexOf(formatted);

            finalMoodValues.push(
                index !== -1 ? Number(moodValues[index]) : 0 // MAKE SURE VALUE IS NUMBER
            );
        }


        var ctx = document.getElementById('moodChart').getContext('2d');

        var moodChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: fullMoodDates, // X-axis (30 days)
                datasets: [{
                    label: "Child Mood (Last 30 Days)",
                    data: finalMoodValues, // FIXED — processed value
                    // borderWidth: 2,
                    fillColor: "#79D1CF",
                    strokeColor: "#79D1CF",
                    // borderColor: "#3e95cd",
                    // backgroundColor: "#3e95cd55",
                    fill: true,
                    pointRadius: 5
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            min: 1,
                            max: {{ count($mood) }},
                            stepSize: 1,
                            callback: function(value) {
                                return moodNames[value]; // mood name
                            }
                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }]
                }
            }
        });
    </script>
    {{-- Mood Graph End --}}


    {{-- Behaviour Chart Start --}}
    <script>
        var behaviorData = @json($behavior_graph);

        var behaviorDates = [];
        var behaviorValues = [];

        behaviorData.forEach(item => {
            behaviorDates.push(item.date);
            behaviorValues.push(item.rate); // rating column (1–5)
        });


        // Generate last 30 days
        var fullDates = [];
        var finalValues = [];

        var startDate = new Date();
        startDate.setDate(startDate.getDate() - 29);

        for (let i = 0; i < 30; i++) {
            let d = new Date(startDate);
            d.setDate(startDate.getDate() + i);

            let formatted = d.toISOString().slice(0, 10);

            fullDates.push(formatted);

            let index = behaviorDates.indexOf(formatted);
            finalValues.push(index !== -1 ? behaviorValues[index] : 0);
        }

        var ctx = document.getElementById('behaviorChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: fullDates,
                datasets: [{
                    label: "Behavior Rating",
                    data: finalValues,
                    // borderColor: "#3b82f6",
                    fillColor: "#79D1CF",
                    strokeColor: "#79D1CF",
                    // borderWidth: 2,
                    fill: false,
                    lineTension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6
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

                        }
                    }],
                    xAxes: [{
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 10
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
