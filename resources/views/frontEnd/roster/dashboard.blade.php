@extends('frontEnd.layouts.master')
@section('title', 'Roster Dashboard')
@section('content')

    <section id="main-content">
        <div class="wrapper ps-0 pe-0 ">
            <div class="container-fluid">
                @include('frontEnd.roster.common.roster_header')
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
                                                    <div class="sys-mngmnticon">
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
                                                    <div class="sys-mngmnticon">
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
                                                    <div class="sys-mngmnticon">
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
                                                    <div class="sys-mngmnticon">
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
                                                <a href="{{ url('roster/schedule-shift') }}">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
