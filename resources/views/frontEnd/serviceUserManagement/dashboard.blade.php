@extends('frontEnd.layouts.master')
@section('title', 'View Report')
@section('content')

<style>
    .orange-bg {
        background: #ed6a22 none repeat scroll 0 0;
    }

    .bg-darkgreen {
        background: #4ab661 none repeat scroll 0 0;
    }
</style>

<section id="main-content">
    <div class="wrapper">

        <div class="col-md-12 col-sm-12 col-xs-12 metro-main">
            <form class="form-horizontal" method="get" action="">
                <div class="form-group col-md-6 col-sm-4 col-xs-12 p-0 metro-design">
                    <label class="col-md-3 col-sm-3 col-xs-12 control-label"> Report type: </label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <div class="select-style">
                            <select name="report_type" id="report_type_select">
                                <option value="ALL"> All </option>
                                <option value="INDIVIDUAL"> Individual </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-4 col-xs-12 p-0">
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 metro-design">
                        <label class="col-md-3 col-sm-3 col-xs-12 control-label"> Date Range: </label>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <input name="from_date" required="" class="form-control trans" type="text" autocomplete="off" maxlength="10" readonly="" id="datetime-picker2" value="">
                        </div>
                        <div class="col-md-1 col-sm-1 col-xs-12 p-0">
                            <label class="col-md-12 col-sm-12 col-xs-12 control-label"> To: </label>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <input name="to_date" required="" class="form-control trans" id="datetime-picker1" type="text" value="" autocomplete="off" maxlength="10" readonly="">
                        </div>
                    </div>
                </div>

                <div class="form-group col-md-6 col-sm-6 col-xs-12 p-0 metro-design">
                    <label class="col-md-3 col-sm-3 col-xs-12 control-label"> User type: </label>
                    <div class="col-md-9 col-sm-9 col-xs-12">
                        <div class="select-style">
                            <select id="user_type" name="user_type">
                                <option value="SERVICE_USER"> Child </option>
                                <option value="STAFF"> Staff </option>
                            </select>

                        </div>
                    </div>
                </div>

                <div class="form-group col-md-6 col-sm-4 col-xs-12 p-0 metro-design">
                    <label class="col-md-3 col-sm-3 col-xs-12 control-label cc-label"> User: </label>
                    <div class="ccol-md-4 col-sm-4 col-xs-12">
                        <div class="select-style">
                            <select name="select_user_id" id="select_user" disabled="disabled">
                                <option value=""> Select </option>
                                <option value="19">Mick</option>
                                <option value="21">Rock</option>
                                <option value="39">TG</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-4">
                        <input type="hidden" name="token" value="" autocomplete="">
                        <button type="submit" class="btn btn-warning">Confirm</button>
                    </div>

                </div>


            </form>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <div class="gauge-canvas">
                                <h4 class="widget-h">Money Spent</h4>
                                <canvas width="160" height="100" id="gauge"></canvas>
                            </div>
                            <ul class="gauge-meta clearfix">
                                <li id="gauge-textfield" class="pull-left gauge-value">0</li>
                                <li class="pull-right gauge-title">12800765</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">Star Given</h4>
                            <div class="star-big text-center">
                                <div class="star-biginner">
                                    <i class="fa fa-star-o"></i>
                                    <span class="star-bigno">4</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">Incident Report</h4>
                            <div class="star-big text-center">
                                <div class="star-biginner">
                                    <i class="fa fa-bolt"></i>
                                    <span class="star-bigno" style="margin-left:40px">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-body">
                        <div class="top-stats-panel">
                            <h4 class="widget-h">all areas</h4>
                            <div class="bar-stats">
                                <ul class="progress-stat-bar clearfix">
                                    <li data-percent="0%"><span class="progress-stat-percent bg-blue" style="height: 0%;"></span></li>
                                    <li data-percent="0%"><span class="progress-stat-percent" style="height: 0%;"></span></li>
                                    <li data-percent="0%"><span class="progress-stat-percent pink" style="height: 0%;"></span></li>
                                    <li data-percent="100%"><span class="progress-stat-percent yellow-b" style="height: 100%;"></span></li>
                                </ul>
                                <ul class="bar-legend">
                                    <li><span class="bar-legend-pointer bg-blue"></span>General Behaviour</li>
                                    <li><span class="bar-legend-pointer green"></span>Independent Living Skills</li>
                                    <li><span class="bar-legend-pointer pink"></span>Education / Training</li>
                                    <li><span class="bar-legend-pointer yellow-b"></span>Missing/Absent from Care</li>
                                </ul>
                                <div class="daily-sales-info">
                                    <span class="sales-count">25%</span> <span class="sales-label">scores earned so far 25</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-red"><i class="fa fa-exclamation-triangle"></i></span>
                    <div class="mini-stat-info">
                        <span>18</span>
                        In Danger
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange-bg"><i class="fa fa-exclamation"></i></span>
                    <div class="mini-stat-info">
                        <span>61</span>
                        Need Assistance
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-darkgreen"><i class="fa fa-phone"></i></span>
                    <div class="mini-stat-info">
                        <span>4</span>
                        Request Callback
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon bg-blue"><i class="fa fa-envelope-o"></i></span>
                    <div class="mini-stat-info">
                        <span>38</span>
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
                            <canvas id="pie-chart-js" height="250" width="440" style="width: 440px; height: 250px;"></canvas>
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
                        <div class="col-md-2 mood_chart_side">
                                                    best 
                                                    depressed 
                                                </div>
                        <div class="chartJS col-md-3">
                            <canvas id="line-chart-js" height="250" width="400" style="/* width: 400px; *//* height: 250px; */"></canvas>
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
                                <div id="daily-visit-chart2" style="width: 100%; height: 100px; display: block; min-height: 220px; padding: 0px; position: relative;">

                                <canvas class="flot-base" width="349" height="220" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 349px; height: 220px;"></canvas><div class="flot-text" style="position: absolute; inset: 0px; font-size: smaller; color: rgb(84, 84, 84);"><div class="flot-x-axis flot-x1-axis xAxis x1Axis" style="position: absolute; inset: 0px; display: block;"><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 14px; text-align: center;">06/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 65px; text-align: center;">07/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 115px; text-align: center;">08/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 166px; text-align: center;">09/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 216px; text-align: center;">10/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 267px; text-align: center;">11/11</div><div style="position: absolute; max-width: 49px; top: 206px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 317px; text-align: center;">12/11</div></div><div class="flot-y-axis flot-y1-axis yAxis y1Axis" style="position: absolute; inset: 0px; display: block;"><div style="position: absolute; top: 90px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 3px; text-align: right;">0</div><div style="position: absolute; top: 0px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 3px; text-align: right;">1</div><div style="position: absolute; top: 179px; font: 400 12px / 13.8px Roboto, sans-serif; color: rgb(102, 102, 102); left: 0px; text-align: right;">-1</div></div></div><canvas class="flot-overlay" width="349" height="220" style="direction: ltr; position: absolute; left: 0px; top: 0px; width: 349px; height: 220px;"></canvas></div>
                                <ul class="chart-meta clearfix">
                                                                        <li class="pull-left visit-chart-value">0</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

    </div>
</section>



<script src="{{ url('public/frontEnd/js/Chart.js') }}"></script>






@endsection