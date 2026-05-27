@extends('frontEnd.layouts.master')
@section('title', 'Leave Request')
@section('content')

    @include('frontEnd.roster.common.roster_header')

    <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Leave Requests</h1>
                    <p class="header-subtitle">Operational overview and key metrics</p>
                </div>
                <!-- <div class="header-actions">
                                        <button class="btn" data-toggle="modal" data-target="#addLeaveModal"><i class="fa fa-plus"></i> Add Leaves</button>
                                    </div>  -->
            </div>

            <div class="rota_dashboard-cards simpleCard">
                <div class="rota_dash-card blue">
                    <div class="rota_dash-left">
                        <p class="rota_title">Total Requests</p>
                        <h2 class="rota_count">{{ $totalLeaveCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-calendar-o"></i>
                    </div>
                </div>

                <div class="rota_dash-card orangeClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Pending</p>
                        <h2 class="rota_count">{{ $pendingLeaveCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                </div>

                <div class="rota_dash-card green">
                    <div class="rota_dash-left">
                        <p class="rota_title">Approved</p>
                        <h2 class="rota_count">{{ $approvedLeaveCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa fa-check-circle-o"></i>
                    </div>
                </div>

                <div class="rota_dash-card redClr">
                    <div class="rota_dash-left">
                        <p class="rota_title">Rejected</p>
                        <h2 class="rota_count">{{ $rejectedLeaveCount }}</h2>
                    </div>
                    <div class="rota_dash-icon">
                        <i class="fa  fa-times-circle-o"></i>
                    </div>
                </div>

            </div>

            <!-- Alerts -->
            <div class="rota_alerts">
                <div class="rota_alert p-10">
                    <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Action Required</div>
                        <div class="rota_alert-description">{{ $pendingLeaveCount }} leave request pending your review</div>
                    </div>
                </div>
            </div>

            <div class="calendarTabs leaveRequesttabs m-t-20">
                <div class="tabs">
                    <button class="tab active" data-tab="allLeaves">
                        All <span>({{ $totalLeaveCount }})</span>
                    </button>

                    <button class="tab" data-tab="panddingLeaves">
                        Pending <span>({{ $pendingLeaveCount }})</span>
                    </button>

                    <button class="tab" data-tab="approvedLeaves">
                        Approved <span>({{ $approvedLeaveCount }})</span>
                    </button>

                    <button class="tab" data-tab="rejectedLeaves">
                        Rejected <span>({{ $rejectedLeaveCount }})</span>
                    </button>
                </div>

                <!-- TAB CONTENT -->
                <div class="tab-content">
                    <div class="content active" id="allLeaves">
                        @if ($totalLeaveCount === 0)
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No leave requests</h4>
                                    <p>No leave requests found</p>
                                </div>
                            </div>
                        @endif
                        @foreach ($leaves as $leave)
                            <div class="leave-card">
                                <div class="unknownCarer">
                                    <div class="leave-left">
                                        <div class="user-icon">
                                            {{ strtoupper(substr($leave->staff_name, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <h3>{{ $leave->staff_name }}</h3>
                                            <div class="tags">
                                                <span class="tag blue">{{ $leave->leave_type_name }}</span>
                                                @if ($leave->leave_status == 0)
                                                    <span class="tag yellow"> Pending </span>
                                                @elseif ($leave->leave_status == 1)
                                                    <span class="tag greenShowbtn"> Approved </span>
                                                @elseif ($leave->leave_status == 2)
                                                    <span class="tag radShowbtn"> Rejected </span>
                                                @endif
                                            </div>
                                            <div class="date-row">
                                                <p>
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                                                    @if (!empty($leave->end_date))
                                                        - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <p>
                                                    <i class="fa fa-clock-o"></i>
                                                    @if (!empty($leave->end_date))
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                                    @else
                                                        1 day
                                                    @endif
                                                </p>
                                            </div>
                                            <small class="requested">Requested {{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                    @if ($leave->leave_status === 0)
                                        <div class="leave-actions">
                                            <button class="approve-btn" data-toggle="modal" data-target="#approveLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}" data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✔
                                                Approve</button>
                                            <button class="reject-btn" data-toggle="modal" data-target="#rejectLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}" data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✖
                                                Reject</button>
                                        </div>
                                    @endif
                                </div>
                                <div class="reason-box">
                                    <p class="reason-title">Reason:</p>
                                    <p class="reasonPera">{{ $leave->notes }}</p>
                                </div>
                                @if ($leave->leave_status !== 0)
                                    <div class="rota_alerts">
                                        <div class="rota_alert p-10">
                                            <div class="rota_alert-icon"><i class="fa  fa-comment"></i></div>
                                            <div class="rota_alert-content">
                                                <div class="rota_alert-title">Manager Response:</div>
                                                <div class="rota_alert-description">{{ $leave->description }}</div>
                                                <div class="rota_alert-bottmDescription"> By {{ $leave->actioned_by_name }} (Manager)</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($leave->leave_status === 1)
                                    <div class="rota_alerts ">
                                        <div class="rota_alert leaveGreenbg p-10">
                                            <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                                            <div class="rota_alert-content">
                                                <div class="rota_alert-description">This leave has been approved. The carer's status will be updated to "On Leave" during this period, and they won't be available for scheduling.</div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach


                    </div> <!--End off All Leaves -->

                    <div class="content" id="panddingLeaves">
                        {{-- <h3>Day View</h3>
                        <p>Day schedule appears here.</p> --}}
                        @if ($pendingLeaveCount === 0)
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No leave requests</h4>
                                    <p>No leave requests found</p>
                                </div>
                            </div>
                        @endif
                        @foreach ($pending_leave as $leave)
                            <div class="leave-card">
                                <div class="unknownCarer">
                                    <div class="leave-left">
                                        <div class="user-icon">
                                            {{ strtoupper(substr($leave->staff_name, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <h3>{{ $leave->staff_name }}</h3>
                                            <div class="tags">
                                                <span class="tag blue">{{ $leave->leave_type_name }}</span>
                                                @if ($leave->leave_status == 0)
                                                    <span class="tag yellow"> Pending </span>
                                                @elseif ($leave->leave_status == 1)
                                                    <span class="tag greenShowbtn"> Approved </span>
                                                @elseif ($leave->leave_status == 2)
                                                    <span class="tag radShowbtn"> Rejected </span>
                                                @endif
                                            </div>
                                            <div class="date-row">
                                                <p>
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                                                    @if (!empty($leave->end_date))
                                                        - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <p>
                                                    <i class="fa fa-clock-o"></i>
                                                    @if (!empty($leave->end_date))
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                                    @else
                                                        1 day
                                                    @endif
                                                </p>
                                            </div>
                                            <small class="requested">Requested {{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                    @if ($leave->leave_status === 0)
                                        <div class="leave-actions">
                                            <button class="approve-btn" data-toggle="modal" data-target="#approveLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}"
                                                data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✔ Approve</button>
                                            <button class="reject-btn" data-toggle="modal" data-target="#rejectLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}" data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✖
                                                Reject</button>
                                        </div>
                                    @endif
                                </div>
                                <div class="reason-box">
                                    <p class="reason-title">Reason:</p>
                                    <p class="reasonPera">{{ $leave->notes }}</p>
                                </div>
                                {{-- <div class="rota_alerts">
                                    <div class="rota_alert p-10">
                                        <div class="rota_alert-icon"><i class="fa  fa-comment"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-title">Manager Response:</div>
                                            <div class="rota_alert-description">Approved. Adequate cover arranged. Enjoy your holiday!</div>
                                            <div class="rota_alert-bottmDescription"> By Val Dyer (Team Manager)</div>
                                        </div>
                                    </div>
                                </div> --}}
                                {{-- <div class="rota_alerts ">
                                    <div class="rota_alert leaveGreenbg p-10">
                                        <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-description">This leave has been approved. The carer's status will be updated to "On Leave" during this period, and they won't be available for scheduling.</div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        @endforeach
                    </div>

                    <div class="content" id="approvedLeaves">
                        {{-- <h3>Week View</h3>
                        <p>Weekly details appear here.</p> --}}
                        @if ($approvedLeaveCount === 0)
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No leave requests</h4>
                                    <p>No leave requests found</p>
                                </div>
                            </div>
                        @endif
                        @foreach ($approved_leave as $leave)
                            <div class="leave-card">
                                <div class="unknownCarer">
                                    <div class="leave-left">
                                        <div class="user-icon">
                                            {{ strtoupper(substr($leave->staff_name, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <h3>{{ $leave->staff_name }}</h3>
                                            <div class="tags">
                                                <span class="tag blue">{{ $leave->leave_type_name }}</span>
                                                @if ($leave->leave_status == 0)
                                                    <span class="tag yellow"> Pending </span>
                                                @elseif ($leave->leave_status == 1)
                                                    <span class="tag greenShowbtn"> Approved </span>
                                                @elseif ($leave->leave_status == 2)
                                                    <span class="tag radShowbtn"> Rejected </span>
                                                @endif
                                            </div>
                                            <div class="date-row">
                                                <p>
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                                                    @if (!empty($leave->end_date))
                                                        - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <p>
                                                    <i class="fa fa-clock-o"></i>
                                                    @if (!empty($leave->end_date))
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                                    @else
                                                        1 day
                                                    @endif
                                                </p>
                                            </div>
                                            <small class="requested">Requested {{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                    @if ($leave->leave_status === 0)
                                        <div class="leave-actions">
                                            <button class="approve-btn" data-toggle="modal" data-target="#approveLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}"
                                                data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✔ Approve</button>
                                            <button class="reject-btn" data-toggle="modal" data-target="#rejectLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}" data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✖
                                                Reject</button>
                                        </div>
                                    @endif
                                </div>
                                <div class="reason-box">
                                    <p class="reason-title">Reason:</p>
                                    <p class="reasonPera">{{ $leave->notes }}</p>
                                </div>
                                <div class="rota_alerts">
                                    <div class="rota_alert p-10">
                                        <div class="rota_alert-icon"><i class="fa  fa-comment"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-title">Manager Response:</div>
                                            <div class="rota_alert-description">{{ $leave->description }}</div>
                                            <div class="rota_alert-bottmDescription"> By {{ $leave->actioned_by_name }} (Manager)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="rota_alerts ">
                                    <div class="rota_alert leaveGreenbg p-10">
                                        <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-description">This leave has been approved. The carer's status will be updated to "On Leave" during this period, and they won't be available for scheduling.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="content" id="rejectedLeaves">
                        @if ($rejectedLeaveCount === 0)
                            <div class="leave-card">
                                <div class="leavebanktabCont">
                                    <i class="fa fa-calendar-o"></i>
                                    <h4>No leave requests</h4>
                                    <p>No leave requests found</p>
                                </div>
                            </div>
                        @endif
                        @foreach ($rejected_leave as $leave)
                            <div class="leave-card">
                                <div class="unknownCarer">
                                    <div class="leave-left">
                                        <div class="user-icon">
                                            {{ strtoupper(substr($leave->staff_name, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <h3>{{ $leave->staff_name }}</h3>
                                            <div class="tags">
                                                <span class="tag blue">{{ $leave->leave_type_name }}</span>
                                                @if ($leave->leave_status == 0)
                                                    <span class="tag yellow"> Pending </span>
                                                @elseif ($leave->leave_status == 1)
                                                    <span class="tag greenShowbtn"> Approved </span>
                                                @elseif ($leave->leave_status == 2)
                                                    <span class="tag radShowbtn"> Rejected </span>
                                                @endif
                                            </div>
                                            <div class="date-row">
                                                <p>
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}
                                                    @if (!empty($leave->end_date))
                                                        - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                                    @endif
                                                </p>
                                                <p>
                                                    <i class="fa fa-clock-o"></i>
                                                    @if (!empty($leave->end_date))
                                                        {{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }} days
                                                    @else
                                                        1 day
                                                    @endif
                                                </p>
                                            </div>
                                            <small class="requested">Requested {{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                    @if ($leave->leave_status === 0)
                                        <div class="leave-actions">
                                            <button class="approve-btn" data-toggle="modal" data-target="#approveLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}"
                                                data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✔ Approve</button>
                                            <button class="reject-btn" data-toggle="modal" data-target="#rejectLeaveModal" data-id="{{ $leave->id }}" data-carer-name="{{ $leave->staff_name }}" data-leave-start-date="{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}" data-leave-end-date="{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}" data-days="{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}">✖
                                                Reject</button>
                                        </div>
                                    @endif
                                </div>
                                <div class="reason-box">
                                    <p class="reason-title">Reason:</p>
                                    <p class="reasonPera">{{ $leave->notes }}</p>
                                </div>
                                <div class="rota_alerts">
                                    <div class="rota_alert p-10">
                                        <div class="rota_alert-icon"><i class="fa  fa-comment"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-title">Manager Response:</div>
                                            <div class="rota_alert-description">{{ $leave->description }}</div>
                                            <div class="rota_alert-bottmDescription"> By {{ $leave->actioned_by_name }} (Manager)</div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="rota_alerts ">
                                    <div class="rota_alert leaveGreenbg p-10">
                                        <div class="rota_alert-icon"><i class="fa fa-exclamation-circle"></i></div>
                                        <div class="rota_alert-content">
                                            <div class="rota_alert-description">This leave has been approved. The carer's status will be updated to "On Leave" during this period, and they won't be available for scheduling.</div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- <div class="modal fade" id="addLeaveModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Actions </h4>
                    </div>
                    <div class="modal-body">
                        <div class="actionForm">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="inner_cards">
                                                <a href="{{ url('/absence/type=1') }}">
                                                    <div>
                                                        <!-- icon9 -->
                                                        <div class="icon bg-yellow">
                                                            <svg width="50" height="50" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill="white" d="M16 28C15.448 28 15 27.552 15 27V25C15 24.448 15.448 24 16 24C16.552 24 17 24.448 17 25V27C17 27.552 16.552 28 16 28Z"></path>
                                                                <path fill="white" d="M25.707 24.293C25.888 24.474 26 24.724 26 25C26 25.552 25.552 26 25 26C24.724 26 24.474 25.888 24.293 25.707L22.293 23.707C22.112 23.526 22 23.276 22 23C22 22.448 22.448 22 23 22C23.276 22 23.526 22.112 23.707 22.293L25.707 24.293Z"></path>
                                                                <path fill="white" d="M7.707 25.707C7.526 25.888 7.276 26 7 26C6.448 26 6 25.552 6 25C6 24.724 6.112 24.474 6.293 24.293L8.293 22.293C8.474 22.112 8.724 22 9 22C9.552 22 10 22.448 10 23C10 23.276 9.888 23.526 9.707 23.707L7.707 25.707V25.707Z"></path>
                                                                <path fill="white" d="M27 17H25C24.448 17 24 16.552 24 16C24 15.448 24.448 15 25 15H27C27.552 15 28 15.448 28 16C28 16.552 27.552 17 27 17Z"></path>
                                                                <path fill="white" d="M7 17H5C4.448 17 4 16.552 4 16C4 15.448 4.448 15 5 15H7C7.552 15 8 15.448 8 16C8 16.552 7.552 17 7 17Z"></path>
                                                                <path fill="white" d="M16 22C12.691 22 10 19.308 10 16C10 12.692 12.691 10 16 10C19.309 10 22 12.692 22 16C22 19.308 19.308 22 16 22ZM16 12C13.794 12 12 13.794 12 16C12 18.206 13.794 20 16 20C18.206 20 20 18.206 20 16C20 13.794 18.206 12 16 12Z"></path>
                                                                <path fill="white" d="M24.293 6.293C24.474 6.112 24.724 6 25 6C25.552 6 26 6.448 26 7C26 7.276 25.888 7.526 25.707 7.707L23.707 9.707C23.526 9.888 23.276 10 23 10C22.448 10 22 9.552 22 9C22 8.724 22.112 8.474 22.293 8.293L24.293 6.293V6.293Z"></path>
                                                                <path fill="white" d="M6.293 7.707C6.112 7.526 6 7.276 6 7C6 6.448 6.448 6 7 6C7.276 6 7.526 6.112 7.707 6.293L9.707 8.293C9.888 8.474 10 8.724 10 9C10 9.552 9.552 10 9 10C8.724 10 8.474 9.888 8.293 9.707L6.293 7.707V7.707Z"></path>
                                                                <path fill="white" d="M16 8C15.448 8 15 7.552 15 7V4.875C15 4.323 15.448 3.875 16 3.875C16.552 3.875 17 4.323 17 4.875V7C17 7.552 16.552 8 16 8Z"></path>
                                                            </svg>

                                                        </div>
                                                    </div>
                                                    <div class="card_name">
                                                        <h4>Add Annual Leave</h4>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="inner_cards">
                                                <a href="{{ url('/absence/type=2') }}">
                                                    <div>
                                                        <!-- icon4 -->
                                                        <div class="icon label-daily">
                                                            <svg width="50" class="svgColor" height="50" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill="white"
                                                                    d="M24.488 16L26.25 14.238C27.375 13.113 28 11.601 28 10C28 8.399 27.375 6.888 26.238 5.763C25.113 4.625 23.601 4 22 4C20.399 4 18.887 4.625 17.762 5.763L16 7.513L14.238 5.75C13.113 4.625 11.601 4 10 4C8.399 4 6.888 4.625 5.763 5.763C4.625 6.888 4 8.401 4 10C4 11.599 4.625 13.113 5.763 14.238L7.513 16L5.75 17.762C4.625 18.887 4 20.399 4 22C4 23.601 4.625 25.113 5.763 26.238C6.888 27.375 8.401 28 10 28C11.599 28 13.113 27.375 14.238 26.238L16 24.476L17.762 26.238C18.899 27.375 20.399 28 22 28C23.601 28 25.113 27.375 26.238 26.238C27.375 25.101 28 23.601 28 22C28 20.399 27.375 18.887 26.238 17.762L24.488 16ZM7.175 12.825C5.612 11.262 5.612 8.725 7.175 7.163C7.925 6.413 8.938 5.988 10 5.988C11.062 5.988 12.075 6.4 12.825 7.163L14.587 8.925L8.925 14.587L7.175 12.825V12.825ZM12.825 24.825C11.262 26.388 8.725 26.388 7.163 24.825C6.413 24.075 5.988 23.063 5.988 22C5.988 20.937 6.4 19.925 7.163 19.175L19.163 7.175C19.938 6.4 20.963 6 21.988 6C23.013 6 24.038 6.388 24.813 7.175C25.563 7.925 25.988 8.937 25.988 10C25.988 11.063 25.575 12.075 24.813 12.825L12.825 24.825ZM24.825 24.825C24.075 25.575 23.063 26 22 26C20.937 26 19.925 25.587 19.175 24.825L17.413 23.063L23.076 17.4L24.838 19.162C26.388 20.725 26.388 23.275 24.826 24.824L24.825 24.825Z">
                                                                </path>
                                                                <path fill="white" d="M12 21C12 21.552 11.552 22 11 22C10.448 22 10 21.552 10 21C10 20.448 10.448 20 11 20C11.552 20 12 20.448 12 21Z"></path>
                                                                <path fill="white" d="M17 16C17 16.552 16.552 17 16 17C15.448 17 15 16.552 15 16C15 15.448 15.448 15 16 15C16.552 15 17 15.448 17 16Z"></path>
                                                                <path fill="white" d="M22 11C22 11.552 21.552 12 21 12C20.448 12 20 11.552 20 11C20 10.448 20.448 10 21 10C21.552 10 22 10.448 22 11Z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="card_name">
                                                        <h4>Add Sickness</h4>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="inner_cards">
                                                <a href="{{ url('/absence/type=3') }}">
                                                    <div>
                                                        <!-- icon7 -->
                                                        <div class="icon terques-bg">
                                                            <svg width="50" height="50" class="svgColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill="white"
                                                                    d="M26 16C26 12.738 24.425 9.838 22 8V5C22 3.35 20.65 2 19 2H13C11.35 2 10 3.35 10 5V8C7.575 9.825 6 12.725 6 16C6 19.275 7.575 22.163 10 24V27C10 28.65 11.35 30 13 30H19C20.65 30 22 28.65 22 27V24C24.425 22.163 26 19.262 26 16ZM12 5C12 4.45 12.45 4 13 4H19C19.55 4 20 4.45 20 5V6.838C18.775 6.301 17.425 6 16 6C14.575 6 13.225 6.3 12 6.838V5ZM8 16C8 11.588 11.588 8 16 8C20.412 8 24 11.588 24 16C24 20.412 20.413 24 16 24C11.587 24 8 20.413 8 16ZM20 27C20 27.55 19.55 28 19 28H13C12.45 28 12 27.55 12 27V25.163C13.225 25.701 14.575 26 16 26C17.425 26 18.775 25.7 20 25.163V27Z">
                                                                </path>
                                                                <path fill="white" d="M19.55 17.163L17 15.463V13C17 12.45 16.55 12 16 12C15.45 12 15 12.45 15 13V16C15 16.35 15.175 16.65 15.45 16.837L18.45 18.837C18.613 18.937 18.8 19 19 19C19.55 19 20 18.55 20 18C20 17.65 19.825 17.35 19.55 17.163Z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="card_name">
                                                        <h4>Add Lateness</h4>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="inner_cards">
                                                <a href="{{ url('/absence/type=4') }}">
                                                    <div>
                                                        <!-- icon6 -->
                                                        <div class="icon lightRed">
                                                            <svg width="50" height="50" class="svgColor" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill="white"
                                                                    d="M25 10C24.448 10 24 10.448 24 11C24 11.552 24.448 12 25 12C25.551 12 26 12.449 26 13V15C26 15.551 25.551 16 25 16H23.828C23.526 15.149 22.851 14.474 22 14.172V10C22 6.692 19.308 4 16 4C12.692 4 10 6.692 10 10V14.172C9.149 14.474 8.474 15.149 8.172 16H7C6.449 16 6 15.551 6 15V13C6 12.449 6.449 12 7 12C7.551 12 8 11.552 8 11C8 10.448 7.552 10 7 10C5.346 10 4 11.346 4 13V15C4 16.654 5.346 18 7 18H8.172C8.585 19.164 9.696 20 11 20H15V22C12.243 22 10 24.243 10 27C10 27.552 10.448 28 11 28C11.552 28 12 27.552 12 27C12 25.346 13.346 24 15 24V25C15 25.552 15.448 26 16 26C16.552 26 17 25.552 17 25V24C18.654 24 20 25.346 20 27C20 27.552 20.448 28 21 28C21.552 28 22 27.552 22 27C22 24.243 19.757 22 17 22V20H21C22.304 20 23.415 19.164 23.828 18H25C26.654 18 28 16.654 28 15V13C28 11.346 26.654 10 25 10ZM12 10C12 7.794 13.794 6 16 6C18.206 6 20 7.794 20 10V14H12V10ZM21 18H11C10.449 18 10 17.551 10 17C10 16.449 10.449 16 11 16H21C21.551 16 22 16.449 22 17C22 17.551 21.551 18 21 18Z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="card_name">
                                                        <h4>Add Other Absences</h4>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="modal fade leaveCommunStyle" id="approveLeaveModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">✔ Approve Leave Request </h4>
                    </div>
                    <div class="modal-body approveLeaveModal">
                        <div class="leave-body">
                            <input type="hidden" name="leave_id" id="approve_leave_id">
                            <div class="leave-info">
                                <p><strong>Carer: <span id="carer_name"></span></strong></p>
                                <p><strong>Dates:</strong> <span id="leave_dates"></span></p>
                                <p><strong>Duration:</strong> <span id="leave_duration"></span></p>
                            </div>

                            <div class="form-group">
                                <label>Notes (Optional)</label>
                                <textarea placeholder="Add any notes for the staff member..." name="approve_note" id="approve_note"></textarea>
                            </div>

                            <div class="info-alert">
                                The carer's status will be automatically updated to
                                <strong>"On Leave"</strong> and they won't be available for scheduling
                                during this period.
                            </div>

                            <div class="footer-actions">
                                <button class="btn btn-cancel">Cancel</button>
                                <button class="btn btn-approve leave-approve-btn">Approve Leave</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade leaveCommunStyle" id="rejectLeaveModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">✖ Reject Leave Request</h4>
                    </div>
                    <div class="modal-body approveLeaveModal rejectLeaveModal">
                        <div class="leave-body">
                            <input type="hidden" name="leave_id" id="reject_leave_id">
                            <div class="leave-info">
                                <p><strong>Carer:</strong> <span id="r_carer_name"></span></p>
                                <p><strong>Dates:</strong> <span id="r_leave_dates"></span></p>
                                <p><strong>Duration:</strong> <span id="r_leave_duration"></span></p>
                            </div>

                            <div class="form-group">
                                <label>Reason for Rejection *</label>
                                <textarea placeholder="Please provide a reason for rejecting this request..." name="reject_note" id="reject_note"></textarea>
                            </div>

                            <div class="info-alert">
                                The staff member will be notified of this decision via notification.
                            </div>

                            <div class="footer-actions">
                                <button class="btn btn-cancel">Cancel</button>
                                <button class="btn btn-Request btn-danger leave-reject-btn">Reject Request</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            const tabs = document.querySelectorAll(".tab");
            const contents = document.querySelectorAll(".content");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    document.querySelector(".tab.active")?.classList.remove("active");
                    tab.classList.add("active");

                    let tabName = tab.getAttribute("data-tab");

                    contents.forEach(content => {
                        content.classList.remove("active");
                    });

                    document.getElementById(tabName).classList.add("active");
                });
            });
        </script>

        <script>
            $('#approveLeaveModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var start_date = button.data('leave-start-date');
                var end_date = button.data('leave-end-date');
                var days = button.data('days');

                $('#approve_leave_id').val(button.data('id'));
                $('#carer_name').text(button.data('carer-name'));
                $('#leave_dates').text(start_date + ' - ' + end_date);
                $('#leave_duration').text(days + ' day(s)');

            });

            $('#rejectLeaveModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var start_date = button.data('leave-start-date');
                var end_date = button.data('leave-end-date');
                var days = button.data('days');

                $('#reject_leave_id').val(button.data('id'));
                $('#r_carer_name').text(button.data('carer-name'));
                $('#r_leave_dates').text(start_date + ' - ' + end_date);
                $('#r_leave_duration').text(days + ' day(s)');
            });

            $(document).on('click', '.leave-approve-btn', function() {
                let approve_leave_id = $('#approve_leave_id').val();
                let approve_note = $('#approve_note').val();

                $.ajax({
                    url: "{{ url('/roster/leave/update') }}",
                    type: "POST",
                    data: {
                        id: approve_leave_id,
                        description: approve_note,
                        status: 1,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response);
                        alert(response.message); // ✅ FROM CONTROLLER
                        location.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        alert(xhr.responseJSON?.message || 'Something went wrong.');
                        $('#approveLeaveModal').modal('hide');
                    }
                });
            });


            $(document).on('click', '.leave-reject-btn', function() {
                let reject_leave_id = $('#reject_leave_id').val();
                let reject_note = $('#reject_note').val();

                $.ajax({
                    url: "{{ url('/roster/leave/update') }}",
                    type: "POST",
                    data: {
                        id: reject_leave_id,
                        description: reject_note,
                        status: 2,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response);
                        alert(response.message); // ✅ FROM CONTROLLER
                        location.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        alert(xhr.responseJSON?.message || 'Something went wrong.');
                        $('#rejectLeaveModal').modal('hide');
                    }
                });
            });
        </script>
    @endsection
</main>
