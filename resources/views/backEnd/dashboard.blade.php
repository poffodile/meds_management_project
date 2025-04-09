@extends('backEnd.layouts.master')

@section('title',' Dashboard')

@section('content')

<script type="text/javascript" src="{{ url('public/backEnd/js/jquery.validate.js')}}"></script>
<!--main content start-->
<section id="main-content">
    <section class="wrapper">
        <style type="text/css">
            .mini-stat-icon.bg-darkgreen {
                background: #4ab661 none repeat scroll 0 0;
            }

            .mini-stat-icon.orange-bg {
                background: #ed6a22 none repeat scroll 0 0;
            }

            .mini-stat-icon.bg-red {
                background: #e13533 none repeat scroll 0 0 !important;
            }

            .cust_select.dataTables_length select {
                width: 100%;
            }

            .cust_select label {
                display: block;
                float: left;
                text-align: left;
            }

            .err {
                color: red;
            }

            .single_plan {
                background: #fff none repeat scroll 0 0;
                border: 1px solid #ddd;
                border-radius: 5px;
                color: #606060;
                margin-bottom: 20px;
                overflow: hidden;
            }

            .plan_type {
                background: #1fb5ad none repeat scroll 0 0;
                color: #fff;
                margin: 0;
                padding: 10px 0;
            }

            .button_buy {
                margin: 20px 0;
            }

            .price {
                color: #1fb5ad;
            }

            .leftside-navigation {
                height: 100%;
                overflow: auto;
                outline: none;
            }

            .leftside-navigation::-webkit-scrollbar {
                width: 3px;
            }

            /* Track */
            .leftside-navigation::-webkit-scrollbar-track {
                background: #1fb5ac;
            }

            /* Handle */
            .leftside-navigation::-webkit-scrollbar-thumb {
                background: #1fb5ac;
            }

            /* Handle on hover */
            .leftside-navigation::-webkit-scrollbar-thumb:hover {
                background: #1fb5ac;
            }
        </style>

        <!--mini statistics start-->
        <div class="col-md-12 col-sm-12 col-xs-12 pull-right">
            @include('backEnd.common.alert_messages')
        </div>
        <!-- <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="gauge-canvas">
                                <h4 class="widget-h">Monthly Expense</h4>
                                <canvas width=160 height=100 id="gauge"></canvas>
                            </div>
                            <ul class="gauge-meta clearfix">
                                <li id="gauge-textfield" class="pull-left gauge-value"></li>
                                <li class="pull-right gauge-title">Safe</li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="daily-visit">
                                <h4 class="widget-h">Daily Visitors</h4>
                                <div id="daily-visit-chart" style="width:100%; height: 100px; display: block">

                                </div>
                                <ul class="chart-meta clearfix">
                                    <li class="pull-left visit-chart-value">3233</li>
                                    <li class="pull-right visit-chart-title"><i class="fa fa-arrow-up"></i> 15%</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">Top Advertise</h4>
                            <div class="sm-pie">
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <section class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">Daily Sales</h4>
                            <div class="bar-stats">
                                <ul class="progress-stat-bar clearfix">
                                    <li data-percent="50%"><span class="progress-stat-percent pink"></span></li>
                                    <li data-percent="90%"><span class="progress-stat-percent"></span></li>
                                    <li data-percent="70%"><span class="progress-stat-percent yellow-b"></span></li>
                                </ul>
                                <ul class="bar-legend">
                                    <li><span class="bar-legend-pointer pink"></span> New York</li>
                                    <li><span class="bar-legend-pointer green"></span> Los Angels</li>
                                    <li><span class="bar-legend-pointer yellow-b"></span> Dallas</li>
                                </ul>
                                <div class="daily-sales-info">
                                    <span class="sales-count">1200 </span> <span class="sales-label">Products Sold</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div> -->
        <!--mini statistics end-->

        <!--earning graph start <div class="row">-->

        <!--  <div class="col-md-8">
       
        <section class="panel">
            <header class="panel-heading">
                Earning Graph <span class="tools pull-right">
            <a href="javascript:;" class="fa fa-chevron-down"></a>
            <a href="javascript:;" class="fa fa-cog"></a>
            <a href="javascript:;" class="fa fa-times"></a>
            </span>
            </header>
            <div class="panel-body">

                <div id="graph-area" class="main-chart">
                </div>
                <div class="region-stats">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="region-earning-stats">
                                This year total earning <span>$68,4545,454</span>
                            </div>
                            <ul class="clearfix location-earning-stats">
                                <li class="stat-divider">
                                    <span class="first-city">$734503</span>
                                    Rocky Mt,NC </li>
                                <li class="stat-divider">
                                    <span class="second-city">$734503</span>
                                    Dallas/FW,TX </li>
                                <li>
                                    <span class="third-city">$734503</span>
                                    Millville,NJ </li>
                            </ul>
                        </div>
                        <div class="col-md-5">
                            <div id="world-map" class="vector-stat">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div> -->
        <!--earning graph end-->
        <!--widget graph start  <div class="col-md-4">-->

        <!--  <section class="panel">
            <div class="panel-body">
                <div class="monthly-stats pink">
                    <div class="clearfix">
                        <h4 class="pull-left"> January 2013</h4>
                        <div class="btn-group pull-right stat-tab">
                            <a href="#line-chart" class="btn stat-btn active" data-toggle="tab"><i class="ico-stats"></i></a>
                            <a href="#bar-chart" class="btn stat-btn" data-toggle="tab"><i class="ico-bars"></i></a>
                        </div>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="line-chart">
                            <div class="sparkline" data-type="line" data-resize="true" data-height="75" data-width="90%" data-line-width="1" data-min-spot-color="false" data-max-spot-color="false" data-line-color="#ffffff" data-spot-color="#ffffff" data-fill-color="" data-highlight-line-color="#ffffff" data-highlight-spot-color="#e1b8ff" data-spot-radius="3" data-data="[100,200,459,234,600,800,345,987,675,457,765]">
                            </div>
                        </div>
                        <div class="tab-pane" id="bar-chart">
                            <div class="sparkline" data-type="bar" data-resize="true" data-height="75" data-width="90%" data-bar-color="#d4a7f5" data-bar-width="10" data-data="[300,200,500,700,654,987,457,300,876,454,788,300,200,500,700,654,987,457,300,876,454,788]"></div>
                        </div>
                    </div>
                </div>
                <div class="circle-sat">
                    <ul>
                        <li class="left-stat-label"><span class="sell-percent">60%</span><span>Direct Sell</span></li>
                        <li><span class="epie-chart" data-percent="45">
                        <span class="percent"></span>
                        </span></li>
                        <li class="right-stat-label"><span class="sell-percent">40%</span><span>Channel Sell</span></li>
                    </ul>
                </div>
            </div>
        </section> -->
        <!--widget graph end-->
        <!--widget graph start-->
        <!-- <section class="panel">
            <div class="panel-body">
                <ul class="clearfix prospective-spark-bar">
                    <li class="pull-left spark-bar-label">
                        <span class="bar-label-value"> $18887</span>
                        <span>Prospective Label</span>
                    </li>
                    <li class="pull-right">
                        <div class="sparkline" data-type="bar" data-resize="true" data-height="40" data-width="90%" data-bar-color="#f6b0ae" data-bar-width="5" data-data="[300,200,500,700,654,987,457,300,876,454,788,300,200,500,700,654,987,457,300,876,454,788]"></div>
                    </li>
                </ul>
            </div>
        </section> -->
        <!--widget graph end-->
        <!--widget weather start-->
        <!--  <section class="weather-widget clearfix">
            <div class="pull-left weather-icon">
                <canvas id="icon1" width="60" height="60"></canvas>
            </div>
            <div>
                <ul class="weather-info">
                    <li class="weather-city">New York <i class="ico-location"></i></li>
                    <li class="weather-cent"><span>18</span></li>
                    <li class="weather-status">Rainy Day</li>
                </ul>
            </div>
        </section> -->
        <!--widget weather end   </div>  </div>-->


        <!--mini statistics start-->

        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon tar"><i class="fa fa-users"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['staff'] }}</span>
                        Staff
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange"><i class="fa fa-user"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['yp'] }}</span>
                        Child
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon pink"><i class="fa fa-legal"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['access'] }}</span>
                        Access Level
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon green"><i class="fa fa-ticket"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['ticket'] }}</span>
                        Support Ticket
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div id="editable-sample_length" class="dataTables_length cust_select">
                    <form method="post" action="{{ url('admin/dashboard') }}" id="select_month_form">
                        <label>
                            Duration
                        </label>
                        <div class="row">
                            <select name="select_month" size="1" aria-controls="editable-sample" class="form-control xsmall" id="select_month">
                                <option value="">Select Duration</option>
                                <option value="30" {{ ($selected_month == '30') ? 'selected': '' }}>Last 1 Month</option>
                                <option value="90" {{ ($selected_month == '90') ? 'selected': '' }}>Last 3 Month</option>
                                <option value="180" {{ ($selected_month == '180') ? 'selected': '' }}>Last 6 Month</option>
                                <option value="270" {{ ($selected_month == '270') ? 'selected': '' }}>Last 9 Month</option>
                                <option value="360" {{ ($selected_month == '360') ? 'selected': '' }}>Last 1 Year</option>
                                <!-- <option value="all" >All</option> -->
                            </select>
                        </div>
                        <input name="_token" value="{{ csrf_token() }}" type="hidden">
                    </form>
                </div>
            </div>
        </div> 

        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon tar"><i class="fa fa-user-times"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['MFC'] }}</span>
                        Missing From Care
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange"><i class="fa fa-slideshare"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['staff_training'] }}</span>
                        Staff Training completed
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon pink"><i class="fa fa-calendar"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['calender'] }}</span>
                        Calender Events Added
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon green"><i class="fa fa-money"></i></span>
                    <div class="mini-stat-info">
                        <span> £{{ $count['petty_cash'] }}</span>
                        Total Petty Cash Spend
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-darkgreen"><i class="fa fa-scissors"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['no_risk'] }}</span>
                        No Risk
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange-bg"><i class="fa fa-scissors"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['historic_risk'] }}</span>
                        Historic Risk
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-red"><i class="fa fa-scissors"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['live_risk'] }}</span>
                        Live Risk
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon pink"><i class="fa fa-map-marker"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['completed_task'] }}</span>
                        Placement Plan Task Completed
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['danger'] }}</span>
                        In Danger
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange-bg"><i class="fa fa-exclamation"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['need_assistane'] }}</span>
                        Need assistance
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-darkgreen"><i class="fa fa-phone"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['request'] }}</span>
                        Request Callback
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-blue"><i class="fa fa-envelope-o"></i></span>
                    <div class="mini-stat-info">
                        <span>{{ $count['message'] }}</span>
                        Office Messages
                    </div>
                </div>
            </div>
        </div>
        <!--mini statistics end-->


        <!-- <div class="row">
    <div class="col-md-8">
        <div class="event-calendar clearfix">
            <div class="col-lg-7 col-md-7 col-sm-6 col-xs-12 calendar-block">
                <div class="cal1 ">
                </div>
            </div>
            <div class="col-lg-5 col-md-5 col-sm-6 col-lg-12 event-list-block">
                <div class="cal-day">
                    <span>Today</span>
                    Friday
                </div>
                <ul class="event-list">
                    <li>Lunch with jhon @ 3:30 <a href="#" class="event-close"><i class="ico-close2"></i></a></li>
                    <li>Coffee meeting with Lisa @ 4:30 <a href="#" class="event-close"><i class="ico-close2"></i></a></li>
                    <li>Skypee conf with patrick @ 5:45 <a href="#" class="event-close"><i class="ico-close2"></i></a></li>
                    <li>Gym @ 7:00 <a href="#" class="event-close"><i class="ico-close2"></i></a></li>
                    <li>Dinner with daniel @ 9:30 <a href="#" class="event-close"><i class="ico-close2"></i></a></li>

                </ul>
                <input type="text" class="form-control evnt-input" placeholder="NOTES">
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <section class="panel">
            <header class="panel-heading">
                Chat <span class="tools pull-right">
            <a href="javascript:;" class="fa fa-chevron-down"></a>
            <a href="javascript:;" class="fa fa-cog"></a>
            <a href="javascript:;" class="fa fa-times"></a>
            </span>
            </header>
            <div class="panel-body">
                <div class="chat-conversation">
                    <ul class="conversation-list">
                        <li class="clearfix">
                            <div class="chat-avatar">
                                <img src="images/chat-user-thumb.png" alt="male">
                                <i>10:00</i>
                            </div>
                            <div class="conversation-text">
                                <div class="ctext-wrap">
                                    <i>John Carry</i>
                                    <p>
                                        Hello!
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="clearfix odd">
                            <div class="chat-avatar">
                                <img src="images/chat-user-thumb-f.png" alt="female">
                                <i>10:00</i>
                            </div>
                            <div class="conversation-text">
                                <div class="ctext-wrap">
                                    <i>Lisa Peterson</i>
                                    <p>
                                        Hi, How are you? What about our next meeting?
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="clearfix">
                            <div class="chat-avatar">
                                <img src="images/chat-user-thumb.png" alt="male">
                                <i>10:00</i>
                            </div>
                            <div class="conversation-text">
                                <div class="ctext-wrap">
                                    <i>John Carry</i>
                                    <p>
                                        Yeah everything is fine
                                    </p>
                                </div>
                            </div>
                        </li>
                        <li class="clearfix odd">
                            <div class="chat-avatar">
                                <img src="images/chat-user-thumb-f.png" alt="female">
                                <i>10:00</i>
                            </div>
                            <div class="conversation-text">
                                <div class="ctext-wrap">
                                    <i>Lisa Peterson</i>
                                    <p>
                                        Wow that's great
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-xs-9">
                            <input type="text" class="form-control chat-input" placeholder="Enter your text">
                        </div>
                        <div class="col-xs-3 chat-send">
                            <button type="submit" class="btn btn-default">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div> -->
        <!-- <div class="row">
    <div class="col-md-8">
        <section class="panel">
            <div class="wdgt-row">
                <img src="images/weather_image.jpg" height="243" alt="">
                <div class="wdt-head">
                    weather forecast
                </div>
                <div class="country-select">
                    <select class="styled">
                        <option>New York </option>
                        <option>London </option>
                        <option>Australia </option>
                        <option>China </option>
                        <option>Canada </option>
                    </select>
                </div>
            </div>

            <div class="panel-body">
                <div class="row weather-full-info">
                    <div class="col-md-3 today-status">
                        <h1>Today</h1>
                        <i class=" ico-cloudy "></i>
                        <div class="degree">37</div>
                    </div>
                    <div class="col-md-9">
                        <ul>
                            <li>
                                <h2>Tomorrow</h2>
                                <i class=" ico-cloudy text-primary"></i>
                                <div class="statistics">32</div>
                            </li>
                            <li>
                                <h2>Mon</h2>
                                <i class=" ico-rainy2 text-danger"></i>
                                <div class="statistics">40</div>
                            </li>
                            <li>
                                <h2>Tue</h2>
                                <i class=" ico-lightning3 text-info"></i>
                                <div class="statistics">25</div>
                            </li>
                            <li>
                                <h2>Wed</h2>
                                <i class=" ico-sun3 text-success"></i>
                                <div class="statistics">37</div>
                            </li>
                            <li>
                                <h2>Thu</h2>
                                <i class=" ico-snowy3 text-warning"></i>
                                <div class="statistics">15</div>
                            </li>
                            <li>
                                <h2>Fri</h2>
                                <i class=" ico-cloudy "></i>
                                <div class="statistics">21</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </section>
    </div>

    <div class="col-md-4">
        <div class="profile-nav alt">
            <section class="panel">
                <div class="user-heading alt clock-row terques-bg">
                    <h1>December 14</h1>
                    <p class="text-left">2014, Friday</p>
                    <p class="text-left">7:53 PM</p>
                </div>
                <ul id="clock">
                    <li id="sec"></li>
                    <li id="hour"></li>
                    <li id="min"></li>
                </ul>

                <ul class="clock-category">
                    <li>
                        <a href="#" class="active">
                            <i class="ico-clock2"></i>
                            <span>Clock</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="ico-alarm2 "></i>
                            <span>Alarm</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="ico-stopwatch"></i>
                            <span>Stop watch</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class=" ico-clock2 "></i>
                            <span>Timer</span>
                        </a>
                    </li>
                </ul>

            </section>

        </div>
    </div>
</div> -->
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
                    <div class="panel-body">
                        <div class="notify-panels">
                            <div class="notifiScroller"><!-- to-do-list -->
                                {!! $notifications !!}
                            </div>
                        </div>
                        <!-- <div class="alert alert-info clearfix">
                <span class="alert-icon"><i class="fa fa-envelope-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="#">Jonathan Smith</a></span> send you a mail </li>
                        <li class="pull-right notification-time">1 min ago</li>
                    </ul>
                    <p>
                        Urgent meeting for next proposal
                    </p>
                </div>
            </div>
            <div class="alert alert-danger">
                <span class="alert-icon"><i class="fa fa-facebook"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender"><span><a href="#">Jonathan Smith</a></span> mentioned you in a post </li>
                        <li class="pull-right notification-time">7 Hours Ago</li>
                    </ul>
                    <p>
                        Very cool photo jack
                    </p>
                </div>
            </div>
            <div class="alert alert-success ">
                <span class="alert-icon"><i class="fa fa-comments-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender">You have 5 message unread</li>
                        <li class="pull-right notification-time">1 min ago</li>
                    </ul>
                    <p>
                        <a href="#">Anjelina Mewlo, Jack Flip</a> and <a href="#">3 others</a>
                    </p>
                </div>
            </div>
            <div class="alert alert-warning ">
                <span class="alert-icon"><i class="fa fa-bell-o"></i></span>
                <div class="notification-info">
                    <ul class="clearfix notification-meta">
                        <li class="pull-left notification-sender">Domain Renew Deadline 7 days ahead</li>
                        <li class="pull-right notification-time">5 Days Ago</li>
                    </ul>
                    <p>
                        Next 5 July Thursday is the last day
                    </p>
                </div>
            </div> -->
                    </div>
                </section>
                <!-- <div class="col-md-12 col-12 col-xs-12 view-more-noti-su-mng text-right">
        <a href="javascript:void(0)">View More</a>
    </div> -->
                <!--notification end-->
            </div>
            <div class="col-md-6">
                <!--todolist start-->
                <section class="panel">
                    <header class="panel-heading">
                        Modification Request <!-- <span class="tools pull-right">
            <a href="javascript:;" class="fa fa-chevron-down"></a>
            <a href="javascript:;" class="fa fa-cog"></a>
            <a href="javascript:;" class="fa fa-times"></a>
            </span> -->
                    </header>
                    <div class="panel-body">
                        <ul class="to-do-list" id="sortable-todo">

                            @if(empty($request))
                            <p class="text-center">No Requests Found.</p>
                            @else
                            @foreach($request as $req)
                            <li class="clearfix">
                                <p class="todo-title">
                                    {{ ucfirst($req['admin_name']) }}, Action = {{ ucfirst($req['action']) }}, Detail = {{ ucfirst($req['content']) }}
                                </p>
                                <!--   <div class="todo-actionlist pull-right clearfix">
                                <a href="#" class="todo-done"><i class="fa fa-check"></i></a>
                                <a href="#" class="todo-edit"><i class="ico-pencil"></i></a>
                                <a href="#" class="todo-remove"><i class="ico-close"></i></a>
                            </div> -->
                            </li>
                            @endforeach
                            @endif
                            <!--  <li class="clearfix">
                    <span class="drag-marker">
                    <i></i>
                    </span>
                    <div class="todo-check pull-left">
                        <input type="checkbox" value="None" id="todo-check1"/>
                        <label for="todo-check1"></label>
                    </div>
                    <p class="todo-title">
                        Donec quam libero, rutrum non gravida
                    </p>
                    <div class="todo-actionlist pull-right clearfix">
                        <a href="#" class="todo-done"><i class="fa fa-check"></i></a>
                        <a href="#" class="todo-edit"><i class="ico-pencil"></i></a>
                        <a href="#" class="todo-remove"><i class="ico-close"></i></a>
                    </div>
                </li>
                <li class="clearfix">
                    <span class="drag-marker">
                    <i></i>
                    </span>
                    <div class="todo-check pull-left">
                        <input type="checkbox" value="None" id="todo-check2"/>
                        <label for="todo-check2"></label>
                    </div>
                    <p class="todo-title">
                        Donec quam libero, rutrum non gravida ut
                    </p>
                    <div class="todo-actionlist pull-right clearfix">
                        <a href="#" class="todo-done"><i class="fa fa-check"></i></a>
                        <a href="#" class="todo-edit"><i class="ico-pencil"></i></a>
                        <a href="#" class="todo-remove"><i class="ico-close"></i></a>
                    </div>
                </li> -->
                        </ul>
                        <!-- <div class="todo-action-bar">
                <div class="row">
                    <div class="col-xs-4 btn-todo-select">
                        <button type="submit" class="btn btn-default"><i class="fa fa-check"></i> Select All</button>
                    </div>
                    <div class="col-xs-4 todo-search-wrap">
                        <input type="text" class="form-control search todo-search pull-right" placeholder=" Search">
                    </div>
                    <div class="col-xs-4 btn-add-task">
                        <button type="submit" class="btn btn-default btn-primary"><i class="fa fa-plus"></i> Add Task</button>
                    </div>
                </div>
            </div> -->
                    </div>
                </section>
                <!--todolist end-->
            </div>
        </div>
    </section>
</section>

<?php
// $admin_dtl = Session::get('scitsAdminSession'); echo "<pre>"; print_r($admin_dtl); die;
$selected_home_id       = Session::get('scitsAdminSession')->home_id;
$selected_company_id    = App\Home::where('id', $selected_home_id)->value('admin_id');
$check_package_dtl      = App\CompanyPayment::where('admin_id', $selected_company_id)
    ->where('company_charges_id', '1')
    ->first();
// echo "<pre>"; print_r($check_package_dtl); //die;
$check_card_detail = App\AdminCardDetail::where('admin_id', $selected_company_id)
    ->first();
if (!empty($check_package_dtl)) {

    $current_date           = date('Y-m-d');
    $expiry_date            = $check_package_dtl['expiry_date'];
    $expiry_date_next_day   = date('Y-m-d', strtotime('+1 day', strtotime($expiry_date)));
} else {
    $current_date = '';
    $expiry_date_next_day = '';
}

?>


<!-- add admin card detail -->
<div class="modal fade" id="card_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog cus-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                <h4 class="modal-title" id="myModalLabel"> Add Admin Card Detail</h4>
            </div>

            <div class="modal-body">
                <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="card_detail_form">
                    <!-- <form method="post" action="3" id="add_classroom_form">   -->
                    <div class="row">
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label>Card Holder Name: </label>
                            <input type="text" class="form-control" name="card_holder_name" placeholder="Enter Card Holder Name" />
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label> Card Number: </label>
                            <input type="text" class="form-control" name="card_number" placeholder="Enter Card Number" />
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label> MM/YY: </label>
                            <input type="text" class="form-control" name="card_expiry_date" placeholder="Enter MM/YY" />
                            <span class="err"></span>
                        </div>
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">
                            <label> CVV: </label>
                            <input type="text" class="form-control" name="cvv" placeholder="Enter CVV Number" />
                        </div>
                        <!--  <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>First Name: </label>
                                <input type="text" class="form-control" name="f_name" placeholder="Enter First Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Last Name: </label>
                                <input type="text" class="form-control" name="l_name" placeholder="Enter Last Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Street: </label>
                                <input type="text" class="form-control" name="street" placeholder="Enter Street"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> State: </label>
                                <select class="form-control sel_state" name="state_code">
                                    <option value="">Select State</option>
                                    
                                    <option value=""></option>
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> City: </label>
                                <select class="form-control cty_lst" name="city_name">
                                    <option value="">Select City</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Zip Code: </label>
                                <input type="text" class="form-control" name="zip_code" placeholder="Enter Zip Code"/> 
                            </div> -->
                        <div class="form-group col-md-12 col-sm-12 col-xs-12">

                            <input type="hidden" name="system_admin_id" value="{{$selected_company_id}}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="submit" class="btn btn-primary" value="Submit" />
                            <!-- <button class="btn btn-primary" type="submit" name="submit">Submit</button> -->
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!--/.modal-dialog -->
</div>

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="package_modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="float:left; width:100%; background-color: #fff;">
            <div class="modal-header ">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
                <h4 class="modal-title">Choose Plan ?</h4>
            </div>

            <div class="form-group col-sm-12 col-md-8 col-md-offset-2 col-xs-12 m-t-20 form-horizontal">
                <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label"> Plan Type: </label>
                <div class="col-md-10 col-sm-9 col-xs-12">
                    <div class="" style="width: 100%">
                        <select class="form-control pln_type">
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12">
                <div class="plans_wrap">
                    <?php
                    $package_type = '';
                    $free_trial_done = '';
                    if (!empty($company_package)) {
                        // echo "<pre>"; print_r($company_package); die;
                        // $current_date           = date('Y-m-d');
                        // $expiry_date_next_day   = date('Y-m-d',strtotime('+1 day',strtotime($company_package->expiry_date)));
                        $package_type           = $company_package->package_type;
                        $free_trial_done        = $company_package->free_trial_done;
                    }


                    $free_days              = $company_charges['0']['days'];
                    $silver_price_monthly   = $company_charges['1']['price_monthly'];
                    $gold_price_monthly     = $company_charges['2']['price_monthly'];
                    $platinum_price_monthly = $company_charges['3']['price_monthly'];
                    $silver_price_yearly    = $company_charges['1']['price_yearly'];
                    $gold_price_yearly      = $company_charges['2']['price_yearly'];
                    $platinum_price_yearly  = $company_charges['3']['price_yearly'];

                    foreach ($company_charges as $company_charge) {

                        $range          = $company_charge['home_range'];
                        $range          = explode('-', $range);
                        $range_end      = 'Add upto ' . $range['1'] . ' homes';
                        $price_monthly  = '$' . $company_charge['price_monthly'] . '/Month';
                        $price_yearly   = '$' . $company_charge['price_yearly'] . '/Year';

                        if ($package_type == $company_charge['package_type']) {
                            $disabled = 'disabled';
                        } else {
                            $disabled = '';
                        }
                        if ($free_trial_done == '1' && $package_type == 'F') {
                            $disabled = 'disabled';
                        } else {
                            $disabled = '';
                        }

                        if ($company_charge['package_type'] == 'F') {
                            $buy_plan           = 'Get Free';
                            $package_type       = 'Free Trial';
                            $pkg_cls            = 'free';
                            $price_monthly      = 'Free for ' . $company_charges['0']['days'] . ' days';
                            $company_charges_id = '1';

                            // echo "<pre>"; print_r($disable_bt); die; 
                            // if(!empty($disable_btn)){
                            //     foreach ($disable_btn as $key => $value) {
                            //         if($value == 'F'){
                            //             $disabled = 'disabled';
                            //         }else{
                            //             $disabled = '';
                            //         }
                            //     }
                            // }

                            // if(!empty($disabled)){
                            //     if($free_trail == 'disabled' || $disabled == 'disabled'){
                            //         $disabled = 'disabled';
                            //     }else{
                            //         $disabled = '';
                            //     }
                            // }
                        } elseif ($company_charge['package_type'] == 'S') {
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Silver';
                            $pkg_cls            = 'slvr';
                            $company_charges_id = '2';

                            // if(!empty($disable_btn)){
                            //     foreach ($disable_btn as $key => $value) {
                            //         if($value == 'S'){
                            //             $disabled = 'disabled';
                            //         }else{
                            //             $disabled = '';
                            //         }
                            //     }
                            // }

                        } elseif ($company_charge['package_type'] == 'G') {
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Gold';
                            $pkg_cls            = 'gld';
                            $company_charges_id = '3';

                            // if(!empty($disable_btn)){
                            //     foreach ($disable_btn as $key => $value) {
                            //         if($value == 'G'){
                            //             $disabled = 'disabled';
                            //         }else{
                            //             $disabled = '';
                            //         }
                            //     }
                            // }
                        } elseif ($company_charge['package_type'] == 'P') {
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Platinum';
                            $pkg_cls            = 'pltnm';
                            $company_charges_id = '4';

                            // if(!empty($disable_btn)){
                            //     foreach ($disable_btn as $key => $value) {
                            //         if($value == 'P'){
                            //             $disabled = 'disabled';
                            //         }else{
                            //             $disabled = '';
                            //         }
                            //     }
                            // }
                        }
                    ?>

                        <div class="col-sm-6">
                            <div class="single_plan text-center">
                                <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="choose_package">
                                    <h2 class="plan_type">{{ $package_type }}</h2>
                                    <div class="wrap_rng_pr">
                                        <h2 class="{{ $pkg_cls }} slvr_prce">{{ $price_monthly }}</h2>
                                        <h4 class="range">{{ $range_end }}</h4>
                                        <div class="button_buy">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="company_charges_id" value="{{ $company_charges_id }}">
                                            <input type="hidden" name="system_admin_id" value="{{ $selected_company_id}}">
                                            <input type="hidden" name="package_duration" value="M">
                                            <button class="btn btn-primary" type="submit" {{ @(isset($disabled)) ? $disabled : '' }}>{{$buy_plan}}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!--main content end-->

<script type="text/javascript">
    $(document).ready(function() {
        var current_date = "{{ $current_date}}";
        var correct_expiry_date = "{{ $expiry_date_next_day}}";
        var check_card_detail = "{{ $check_card_detail }}";
        // console.log(current_date);
        // console.log(correct_expiry_date);
        if (check_card_detail == '') {
            if ((current_date != '') && (correct_expiry_date != '')) {
                if (current_date == correct_expiry_date) {
                    $('#card_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#card_modal').modal('show');
                }
            }
        } else {
            if ((current_date != '') && (correct_expiry_date != '')) {
                if (current_date == correct_expiry_date) {
                    $('#package_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    $('#package_modal').modal('show');
                }
            }
        }


    });
</script>

<script>
    //notification scroller
    $(".notifiScroller").slimScroll({
        height: '301px'
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {

        var joining_date = $('#date-range').datepicker({
            format: 'dd-mm-yyyy',
            onRender: function(date) {
                //to compare "joining_date" and "leaving_date"
                return date.valueOf();
            }
        })
    });
</script>


<script>
    $(document).on('change', '#select_month', function() {
        // alert(1);
        $('#select_month_form').submit();
    });
</script>

<script type="text/javascript">
    $("input[name='card_expiry_date']").each(function() {
        $(this).on("change keyup paste", function(e) {

            var output,
                $this = $(this),
                input = $this.val();

            if (e.keyCode != 8) {
                input = input.replace(/[^0-9]/g, '');
                var area = input.substr(0, 2);
                var pre = input.substr(2, 2);
                // var tel  = input.substr(5, 4);
                if (area.length < 2) {
                    output = area;
                } else if (area.length == 2 && pre.length < 3) {

                    var ar_val = input.substr(0, 2);
                    var pre_val = input.substr(2, 2);
                    var current_year = new Date().getFullYear().toString().substr(-2);
                    if (ar_val > 12) {
                        $('.err').text('Please enter a valid month');
                    }

                    if (pre.length == 2 && pre_val <= current_year) {
                        $('.err').text('Please enter a valid year');
                    }

                    if (ar_val <= 12 && pre.length == 2 && pre_val >= current_year) {
                        $('.err').text('');
                    }
                    output = area + "/" + pre;
                }

                $this.val(output);
            }
        });
    });
</script>

<script type="text/javascript">
    $('#card_detail_form').validate({
        rules: {
            card_holder_name: {
                required: true,
                // minlength:2,
                // maxlength:100,
                regex: /^[a-zA-Z ]+$/
            },
            card_number: {
                required: true,
                minlength: 10,
                maxlength: 16,
                regex: /^[0-9]+$/
            },
            card_expiry_date: {
                required: true,
                // minlength:5,
                // maxlength:5,
                // regex:/^[0-9]+$/
            },
            cvv: {
                required: true,
                minlength: 3,
                maxlength: 3,
                number: true
            },
            f_name: {
                required: true,
                regex: /^[a-zA-Z ]+$/
            },
            l_name: {
                required: true,
                regex: /^[a-zA-Z ]+$/
            },
            street: {
                required: true,
            },
            city: {
                required: true,
            },
            state_code: {
                required: true,
            },
            zip_code: {
                required: true,
                minlength: 5,
                maxlength: 5,
                regex: /^[0-9]+$/
            },
        },
        messages: {
            card_holder_name: {
                regex: 'This field can only consist of alphabets'
            },
            card_number: {
                regex: 'This field can only consist of digits',
            },
            card_expiry_date: {
                // regex: 'This field can only consist of digits',
            },
            cvv: {
                regex: 'This field can only consist of digits',
            },
            f_name: {
                regex: 'This field can only consist of alphabets'
            },
            l_name: {
                regex: 'This field can only consist of alphabets'
            },
            zip_code: {
                regex: 'This field can only consist of digits',
            },

        },

        submitHandler: function(form) {

            var err_txt = $('.err').text();
            if (err_txt != '') {
                return false;
            } else {
                var form_data = $('#card_detail_form').serialize();

                $('.loader').show();
                $.ajax({
                    type: 'post',
                    url: "{{ url('admin/system-admin/home/card-detail')}}",
                    data: form_data,
                    success: function(resp) {
                        // console.log(resp);
                        if (resp == '1') {
                            $('#card_modal').modal('hide');
                            $('#package_modal').modal('show');
                        }
                        $('.loader').hide();
                    }

                });
                // form.submit();
            }

        }
    });
</script>
<script type="text/javascript">
    var free_days = 'Free for ' + '{{ $free_days }}' + ' days';
    var silver_price_monthly = '$' + '{{ $silver_price_monthly }}' + '/' + 'Month';
    var gold_price_monthly = '$' + '{{ $gold_price_monthly }}' + '/' + 'Month';
    var platinum_price_monthly = '$' + '{{ $platinum_price_monthly }}' + '/' + 'Month';
    var silver_price_yearly = '$' + '{{ $silver_price_yearly }}' + '/' + 'Year';
    var gold_price_yearly = '$' + '{{ $gold_price_yearly }}' + '/' + 'Year';
    var platinum_price_yearly = '$' + '{{ $platinum_price_yearly }}' + '/' + 'Year';

    $(document).on('change', '.pln_type', function() {
        var plan_type = $(this).val();

        if (plan_type == 'Yearly') {
            $('.free').text(free_days);
            $('.slvr').text(silver_price_yearly);
            $('.gld').text(gold_price_yearly);
            $('.pltnm').text(platinum_price_yearly);
            $('input[name=package_duration]').val('Y');
        } else {
            $('.free').text(free_days);
            $('.slvr').text(silver_price_monthly);
            $('.gld').text(gold_price_monthly);
            $('.pltnm').text(platinum_price_monthly);
            $('input[name=package_duration]').val('M');
        }
    });
</script>

<script type="text/javascript">
    // $(document).on('change','.sel_state',function(){
    //     var state_code = $(this).val();
    //     $('.loader').show();
    //     $.ajax({
    //         type:'get',
    //         url:"{{ url('admin/city-list')}}"+'/'+state_code ,
    //         success:function(resp){
    //             // console.log(resp);
    //             $('.cty_lst').html(resp);
    //             $('.loader').hide();
    //         }
    //     });

    // });
</script>
@endsection