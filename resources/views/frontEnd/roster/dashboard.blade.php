@extends('frontEnd.layouts.master')
@section('title','Dashboard')
@section('content')


@include('frontEnd.roster.common.roster_header')
<section id="main-content">
    <div class="wrapper ps-0 pe-0 ">
        <div class="container-fluid">
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
                                                <p> Active {{ getTerm('Service User') }}s </p>
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
                                                <p>Active {{ getTerm('Staff') }}s</p>
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
                                                <h4>{{ $today_shifts_count }} </h4>
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
                                                <h4>{{ $unfilled_shifts_count }} </h4>
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
                                    @forelse ($today_shifts->slice(0, 3) as $shift)
                                    <div class="todayShiftsList {{ !$loop->first ? 'm-t-15' : '' }}">
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-clock-o"></i>
                                                <span><strong>{{ date('H:i', strtotime($shift->start_time)) }} - {{ date('H:i', strtotime($shift->end_time)) }}</strong></span>
                                            </div>
                                            <div class="unfilledbtn">{{ $shift->staff_id ? 'scheduled' : 'unfilled' }}</div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa fa-user-o"></i>
                                                <span>{{ getTerm('Staff') }}: <strong> {{ $shift->staff_name ?? 'Unassigned' }}</strong></span>
                                            </div>
                                        </div>
                                        <div class="siftTime">
                                            <div class="siftTimeCont">
                                                <i class="fa  fa-map-marker"></i>
                                                <span>{{ getTerm('Service User') }}: <strong> {{ $shift->client_name ?? 'Unknown ' . getTerm('Service User') }}</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="todayShiftsList">
                                        <p>No shifts scheduled for today.</p>
                                    </div>
                                    @endforelse

                                    @if(count($today_shifts) > 3)
                                    <div class="text-center p-t-10">
                                        <a href="#!" style="color: #4299e1; font-weight: 600;">View All Today's Shifts ({{ count($today_shifts) }}) →</a>
                                    </div>
                                    @endif
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
                                                        <h4>Add {{ getTerm('Staff') }} </h4>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#!">
                                                <div class="quickActions  m-t-15">
                                                    <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                                    <div class="rotsBoxRightCont">
                                                        <h4>Add {{ getTerm('Service User') }} </h4>
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
                                    @forelse ($scheduled_shifts->slice(0, 3) as $shift)
                                    <div class="todayShiftsList recentActivity {{ !$loop->first ? 'm-t-15' : '' }}">
                                        <div class="activityCalendar"> <i class="fa fa-calendar-o"></i></div>
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>New Shift Created</strong></span>
                                                </div>
                                                <div class="unfilledbtn">{{ $shift->staff_id ? 'scheduled' : 'unfilled' }}</div>
                                            </div>
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <p class="m-b-5"> {{ $shift->client_name ?? 'Unknown' }} → {{ $shift->staff_name ?? 'Unassigned' }}</p>
                                                    <span>{{ date('M d, Y at h:i A', strtotime($shift->created_at)) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="todayShiftsList recentActivity">
                                        <div class="recentCant">
                                            <div class="siftTime">
                                                <div class="siftTimeCont">
                                                    <span><strong>No recent activity</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforelse

                                    @if(count($scheduled_shifts) > 3)
                                    <div class="text-center p-t-10">
                                        <a href="#!" style="color: #4299e1; font-weight: 600;">View All Activity ({{ count($scheduled_shifts) }}) →</a>
                                    </div>
                                    @endif
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