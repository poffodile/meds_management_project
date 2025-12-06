@extends('frontEnd.layouts.master')
@section('title','Manage Dashboard')
@section('content')




<section id="main-content">
    <div class="wrapper ps-0 pe-0 ">
        <div class="container-fluid">
            @include('frontEnd.roster.common.roster_header')
            <div class="row">
                <div class="col-md-9">
                    <div class="m-t-30">
                        <div class="panel">
                            <header class="panel-heading"> Manage Dashboard </header>
                            <div class="panel-body rosterBox">

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="{{ url('roster/dashboard') }}">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sys-mngmnticon">
                                                    <i class="fa fa-building-o"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>53 </h4>
                                                <p> Active Clients </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="#!" data-toggle="modal">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sys-mngmnticon">
                                                    <i class="fa fa-medkit"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>12 </h4>
                                                <p>Active Carers</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="#!">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sys-mngmnticon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>44 </h4>
                                                <p> Today's Shifts </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="#!">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sys-mngmnticon">
                                                    <i class="fa fa-calendar-o"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>22 </h4>
                                                <p>Unfilled Shifts</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        
                        <div class="col-md-6">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> <i class="fa fa-user"></i> Staff & Shifts</header>
                                <div class="panel-body">
                                   <div class="staffShifts">
                                      <div class="todayNumber">
                                        <p>Today's Shifts</p>
                                        <h3>3</h3>
                                      </div>
                                      <div class="fillPersent">
                                        <p>Fill Rate</p>
                                        <h3 class="text-green">33.3%</h3>
                                      </div>
                                   </div>
                                   <div class="text-center">
                                        <a href="#!" class="profileDrop openMoodModel d-block" data-action="add"><i class="fa fa-calendar-o"></i> Full view Schedule</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                          <div class="col-md-6">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> <i class="fa fa-warning (alias)"></i> Incidents & Safety</header>
                                <div class="panel-body">
                                   <div class="staffShifts">
                                      <div class="todayNumber">
                                        <p>This Month</p>
                                        <h3>3</h3>
                                      </div>
                                      <div class="fillPersent">
                                        <p>Unresolved</p>
                                        <h3 class="text-orange">1</h3>
                                      </div>
                                   </div>
                                   <div class="text-center">
                                        <a href="#!" class="profileDrop openMoodModel d-block" data-action="add"><i class="fa fa-info-circle"></i> View All Incidents</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> <i class="fa fa-home"></i> Occupancy & Capacity</header>
                                <div class="panel-body">

                                    <div class="occupancyBox">
                                        <div class="topRow">
                                            <span>Current Occupancy</span>
                                            <span class="value">8/50</span>
                                        </div>

                                        <div class="progressBar">
                                            <div class="progressFill" style="width:16%;"></div>
                                        </div>
                                    </div>
                                   <div class="staffShifts">
                                      <div class="todayNumber">
                                        <p>Occupancy Rate</p>
                                        <h3>66.0%</h3>
                                      </div>
                                      <div class="fillPersent">
                                        <p>Planned Admissions</p>
                                        <h3>3</h3>
                                      </div>
                                   </div>
                                   <div class="text-center">
                                        <a href="#!" class="profileDrop openMoodModel d-block" data-action="add"><i class="fa fa-info-circle"></i> Manage client information</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> <i class="fa fa-book"></i> Training Compliance</header>
                                <div class="panel-body">
                                    <div class="occupancyBox">
                                        <div class="topRow">
                                            <span>Completion Rate</span>
                                            <span class="value">0.0%</span>
                                        </div>

                                        <div class="progressBar">
                                            <div class="progressFill" style="width:0%;"></div>
                                        </div>
                                    </div>
                                   <div class="staffShifts">
                                      <div class="todayNumber">
                                        <p>Expiring Soon</p>
                                        <h3 class="text-orange">3</h3>
                                      </div>
                                      <div class="fillPersent">
                                        <p>Overdue</p>
                                        <h3 class="text-orange">33.3%</h3>
                                      </div>
                                   </div>
                                   <div class="text-center">
                                        <a href="#!" class="profileDrop openMoodModel d-block" data-action="add"><i class="fa fa-info-circle"></i> View Training</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                      

                    </div>
                </div>
                <div class="col-md-3">
                    <div class="rotawhitebgColor m-t-30">
                        <div class="panel">
                            <header class="panel-heading">Notifications</header>
                            <div class="panel-body">
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>
                                <div class="alert alert-placement clearfix">
                                    <span class="alert-icon"><i class="fa fa-map-marker"></i></span>
                                    <div class="notification-info">
                                        <ul class="clearfix notification-meta">
                                            <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Mick</b></a></span></li>
                                            <li class="pull-right notification-time">3 weeks ago</li>
                                        </ul>
                                        <p>A new Placement Plan 'task' is added</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection