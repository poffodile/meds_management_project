@extends('frontEnd.layouts.master')
@section('title','Dashboard')
@section('content')


@include('frontEnd.roster.common.roster_header')
<section id="main-content">
    <div class="wrapper ps-0 pe-0 ">
        <div class="container-fluid">
            {{-- <div class="row">
                <div class="col-md-12">
                    <div class="wrappermenu">
                        <nav>
                            <input type="checkbox" id="show-search">
                            <input type="checkbox" id="show-menu">
                            <label for="show-menu" class="menu-icon"><i class="fas fa-bars"></i></label>
                            <div class="content">
                                <ul class="links">
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Dashboard</a></li>
                                    <li><a href="{{ url('/roster/manage-dashboard') }}"> <i class="fa fa-tachometer"></i> Manager Dashboard</a></li>
                                    <li><a href="{{ url('/roster/schedule-shift') }}"> <i class="fa fa-tachometer"></i> Schedule</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Carer Availability</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Messaging Center</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Staff Tasks</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Carers</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Clients</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Care Documents</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Reports</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Leave Requests</a></li>
                                    <li><a href="#"> <i class="fa fa-tachometer"></i> Daily Log</a></li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div> --}}
            <div class="row">
                <div class="col-md-9">
                    <div class="m-t-30">
                        <div class="panel">
                            <header class="panel-heading"> Dashboard</header>
                            <div class="panel-body rosterBox">

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="{{ url('roster/dashboard') }}">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sysMngmnticon">
                                                    <i class="fa fa-building-o"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>{{ $serviceUserCount }} </h4>
                                                <p> Active Clients </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="#!" data-toggle="modal">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sysMngmnticon">
                                                    <i class="fa fa-medkit"></i>
                                                </div>
                                            </div>
                                            <div class="rotsBoxRightCont">
                                                <h4>{{ $userCount }} </h4>
                                                <p>Active Carers</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-3 col-sm-3 col-xs-6">
                                    <a href="#!">
                                        <div class="sys-mngmnt-box">
                                            <div>
                                                <div class="sysMngmnticon">
                                                    <i class="fa fa-life-ring"></i>
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
                                                <div class="sysMngmnticon">
                                                    <i class="fa fa-sun-o"></i>
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
                        
                        <div class="col-md-12">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> Today's Shifts</header>
                                <div class="panel-body">
                                    <div class="todayShiftsList">
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-clock-o"></i>
                                                <span><strong>09:00 - 17:00</strong></span>
                                            </div>
                                            <div class="unfilledbtn">Unfilled</div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-user-o"></i>
                                                <span>Carer: <strong> Unassigned</strong></span>
                                            </div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa  fa-map-marker"></i>
                                                <span>Client: <strong> Unknown Client</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="todayShiftsList m-t-15">
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-clock-o"></i>
                                                <span><strong>09:00 - 17:00</strong></span>
                                            </div>
                                            <div class="unfilledbtn">Unfilled</div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-user-o"></i>
                                                <span>Carer: <strong> Unassigned</strong></span>
                                            </div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa  fa-map-marker"></i>
                                                <span>Client: <strong> Unknown Client</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="todayShiftsList m-t-15">
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-clock-o"></i>
                                                <span><strong>09:00 - 17:00</strong></span>
                                            </div>
                                            <div class="unfilledbtn">Unfilled</div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-user-o"></i>
                                                <span>Carer: <strong> Unassigned</strong></span>
                                            </div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa  fa-map-marker"></i>
                                                <span>Client: <strong> Unknown Client</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="todayShiftsList m-t-15">
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-clock-o"></i>
                                                <span><strong>09:00 - 17:00</strong></span>
                                            </div>
                                            <div class="unfilledbtn">Unfilled</div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-user-o"></i>
                                                <span>Carer: <strong> Unassigned</strong></span>
                                            </div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa  fa-map-marker"></i>
                                                <span>Client: <strong> Unknown Client</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> Quick Actions</header>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#!">
                                                <div class="quickActions">   
                                                    <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                                    <div class="rotsBoxRightCont">
                                                        <h4>Create Shift </h4>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#!">
                                                <div class="quickActions">   
                                                    <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                                    <div class="rotsBoxRightCont">
                                                        <h4>Add Carer </h4>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#!">
                                                <div class="quickActions  m-t-15">   
                                                    <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                                    <div class="rotsBoxRightCont">
                                                        <h4>Add Client </h4>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#!">
                                                <div class="quickActions m-t-15">   
                                                    <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                                    <div class="rotsBoxRightCont">
                                                        <h4>Leave Requests</h4>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>                            
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="panel">
                                <header class="panel-heading headingCapitilize"> Recent Activity</header>
                                <div class="panel-body">
                                    <div class="todayShiftsList recentActivity">
                                        <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>Shift unfilled</strong></span>
                                                </div>
                                                <div class="unfilledbtn">Unfilled</div>
                                            </div>
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <p class="m-b-5"> Unknown → Unknown</p>
                                                    <span>Nov 27, 2025 at 11:13 AM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="todayShiftsList recentActivity m-t-15">
                                        <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>Shift unfilled</strong></span>
                                                </div>
                                                <div class="unfilledbtn">Unfilled</div>
                                            </div>
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <p class="m-b-5"> Unknown → Unknown</p>
                                                    <span>Nov 27, 2025 at 11:13 AM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="todayShiftsList recentActivity m-t-15">
                                        <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>Shift unfilled</strong></span>
                                                </div>
                                                <div class="unfilledbtn">Unfilled</div>
                                            </div>
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <p class="m-b-5"> Unknown → Unknown</p>
                                                    <span>Nov 27, 2025 at 11:13 AM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="todayShiftsList recentActivity m-t-15">
                                        <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>Shift unfilled</strong></span>
                                                </div>
                                                <div class="unfilledbtn">Unfilled</div>
                                            </div>
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <p class="m-b-5"> Unknown → Unknown</p>
                                                    <span>Nov 27, 2025 at 11:13 AM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                   
                
                <div class="col-md-3">

                    <div class="rotawhitebgColor m-t-30">
                        <div class="panel">
                            @include('frontEnd.common.notification_bar')
                            {{-- <header class="panel-heading">Notifications</header> --}}
                            {{-- <div class="panel-body">
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

                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection
