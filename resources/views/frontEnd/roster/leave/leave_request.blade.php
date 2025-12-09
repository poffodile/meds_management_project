@extends('frontEnd.layouts.master')
@section('title', 'Leave Request')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />

    <section id="main-content">
        <div class="wrapper ps-0 pe-0 ">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @include('frontEnd.roster.common.roster_header')
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="m-t-30">
                            <div class="panel">
                                <header class="panel-heading"> Leave Requests </header>
                                <div class="panel-body rosterBox">

                                    <div class="col-md-3 col-sm-3 col-xs-6">
                                        <a href="{{ url('roster/dashboard') }}">
                                            <div class="sys-mngmnt-box">
                                                <div>
                                                    <div class="sys-mngmnticon">
                                                        <i class="fa fa-calendar-o"></i>
                                                    </div>
                                                </div>
                                                <div class="rotsBoxRightCont">
                                                    <h4>{{ $total_leave }} </h4>
                                                    <p> Total Requests</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-md-3 col-sm-3 col-xs-6">
                                        <a href="#!" data-toggle="modal">
                                            <div class="sys-mngmnt-box">
                                                <div>
                                                    <div class="sys-mngmnticon">
                                                        <i class="fa fa-clock-o"></i>
                                                    </div>
                                                </div>
                                                <div class="rotsBoxRightCont">
                                                    <h4>{{ $pending }} </h4>
                                                    <p>Pending</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-md-3 col-sm-3 col-xs-6">
                                        <a href="#!">
                                            <div class="sys-mngmnt-box">
                                                <div>
                                                    <div class="sys-mngmnticon">
                                                        <i class="fa  fa-check-circle-o"></i>
                                                    </div>
                                                </div>
                                                <div class="rotsBoxRightCont">
                                                    <h4>{{ $approved }} </h4>
                                                    <p> Approved </p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-md-3 col-sm-3 col-xs-6">
                                        <a href="#!">
                                            <div class="sys-mngmnt-box">
                                                <div>
                                                    <div class="sys-mngmnticon">
                                                        <i class="fa fa-times-circle-o"></i>
                                                    </div>
                                                </div>
                                                <div class="rotsBoxRightCont">
                                                    <h4>{{ $rejected }} </h4>
                                                    <p>Rejected</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="m-t-30">
                            <div class="panel">
                                <div class="panel-body">
                                    <div class="calendarTabs">
                                        <div class="tabs">
                                            <button class="tab active" data-tab="roster">
                                                <span>📆</span> Months
                                            </button>

                                            <button class="tab" data-tab="day">
                                                <span>📋</span> Leave Request
                                            </button>

                                        </div>

                                        <!-- TAB CONTENT -->
                                        <div class="tab-content">

                                            <div class="content active" id="roster">
                                                {{-- <h3>Calendar Here</h3>
                                                <p>Day schedule appears here.</p> --}}
                                                <div id="calendar" class="has-toolbar"></div>

                                            </div>

                                            <div class="content" id="day">
                                                <div class="calendarTabsLev">
                                                    <div class="tabGroup myTabs">
                                                        <div class="tabs">
                                                            <button class="leaveTab active" data-tab="allBox">All (1)</button>
                                                            <button class="leaveTab" data-tab="pendingBox">Pending (1)</button>
                                                            <button class="leaveTab" data-tab="approvedBox">Approved (5)</button>
                                                            <button class="leaveTab" data-tab="rejectedBox">Rejected (0)</button>
                                                        </div>

                                                        <div class="leaveBox active" id="allBox">
                                                            @foreach ($leaves as $leave)
                                                                <div class="leave-card">
                                                                    <div class="unknownCarer">
                                                                        <div class="leave-left">
                                                                            <div class="user-icon">?</div>

                                                                            <div class="user-info">
                                                                                <h3>{{ $leave->staff_name }}</h3>

                                                                                <div class="tags">
                                                                                    <span class="tag blue">{{ $leave->leave_type_name }}</span>
                                                                                    <span class="tag yellow">
                                                                                        @if ($leave->leave_status == 0)
                                                                                            Pending
                                                                                        @elseif ($leave->leave_status == 1)
                                                                                            Approved
                                                                                        @elseif ($leave->leave_status == 2)
                                                                                            Rejected
                                                                                        @endif
                                                                                    </span>
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

                                                                        <div class="leave-actions">
                                                                            <button class="approve-btn">✔ Approve</button>
                                                                            <button class="reject-btn">✖ Reject</button>
                                                                        </div>
                                                                    </div>

                                                                    <div class="reason-box">
                                                                        <p class="reason-title">Reason:</p>
                                                                        <p>{{ $leave->notes }}</p>
                                                                    </div>

                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <div class="leaveBox" id="pendingBox">
                                                            <div class="leave-card">
                                                                <div class="unknownCarer">
                                                                    <div class="leave-left">
                                                                        <div class="user-icon">?</div>

                                                                        <div class="user-info">
                                                                            <h3>Unknown Carer</h3>

                                                                            <div class="tags">
                                                                                <span class="tag blue">holiday</span>
                                                                                <span class="tag yellow">pending</span>
                                                                            </div>

                                                                            <div class="date-row">
                                                                                <p><i class="fa fa-calendar-o"></i> Dec 20, 2025 - Dec 27, 2025</p>
                                                                                <p><i class="fa fa-clock-o"></i> 8 days</p>
                                                                            </div>

                                                                            <small class="requested">Requested Nov 4, 2025 at 10:40 AM</small>
                                                                        </div>
                                                                    </div>

                                                                    <div class="leave-actions">
                                                                        <button class="approve-btn">✔ Approve</button>
                                                                        <button class="reject-btn">✖ Reject</button>
                                                                    </div>
                                                                </div>

                                                                <div class="reason-box">
                                                                    <p class="reason-title">Reason:</p>
                                                                    <p>Christmas holiday with family</p>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="leaveBox" id="approvedBox">
                                                            <h3>Approved Content</h3>
                                                            <p>Approved items will be shown here...</p>
                                                        </div>

                                                        <div class="leaveBox" id="rejectedBox">
                                                            <h3>Rejected Content</h3>
                                                            <p>Rejected items will be shown here...</p>
                                                        </div>

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
                                <header class="panel-heading">Alerts</header>
                                <div class="panel-body">
                                    <div class="alert alert-placement clearfix">
                                        <span class="alert-icon"><i class="fa fa-exclamation-circle"></i></span>
                                        <div class="notification-info">
                                            <ul class="clearfix notification-meta">
                                                <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Action Required</b> - 09:00</a></span></li>
                                                <li class="pull-right notification-time">High</li>
                                            </ul>
                                            <p>1 leave request pending your review</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-placement clearfix">
                                        <span class="alert-icon"><i class="fa fa-calendar-o"></i></span>
                                        <div class="notification-info">
                                            <ul class="clearfix notification-meta">
                                                <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Missed Shift</b> - 09:00</a></span></li>
                                                <li class="pull-right notification-time">3 weeks ago</li>
                                            </ul>
                                            <p>A new Placement Plan 'task' is added</p>
                                        </div>
                                    </div>
                                    <div class="alert alert-placement clearfix">
                                        <span class="alert-icon"><i class="fa fa-calendar-o"></i></span>
                                        <div class="notification-info">
                                            <ul class="clearfix notification-meta">
                                                <li class="pull-left notification-sender"><span><a href="http://localhost/socialcareitsolution/service/placement-plans/19"><b>Missed Shift</b> - 09:00</a></span></li>
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
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    <!-- Tow Tab JS  -->
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

    <!-- Innre tabs Js -->
    <script>
        document.querySelectorAll(".tabGroup").forEach(group => {

            const tabs = group.querySelectorAll(".leaveTab");
            const boxes = group.querySelectorAll(".leaveBox");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const target = tab.dataset.tab;

                    // remove active from tabs of this group only
                    tabs.forEach(t => t.classList.remove("active"));
                    tab.classList.add("active");

                    // hide all boxes of this group only
                    boxes.forEach(b => b.classList.remove("active"));

                    // show target box
                    group.querySelector("#" + target).classList.add("active");
                });
            });

        });

        document.addEventListener('DOMContentLoaded', function() {
            var initialLocaleCode = 'en';
            var localeSelectorEl = document.getElementById('locale-selector');
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },

                initialDate: moment().format('YYYY-MM-DD'),
                locale: initialLocaleCode,
                buttonIcons: false, // show the prev/next text
                weekNumbers: true,
                navLinks: true, // can click day/week names to navigate views
                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                events: <?=$calender?>
            });

            calendar.render();
            // build the locale selector's options

            calendar.getAvailableLocaleCodes().forEach(function(localeCode) {
                var optionEl = document.createElement('option');
                optionEl.value = localeCode;
                optionEl.selected = localeCode == initialLocaleCode;
                optionEl.innerText = localeCode;
                localeSelectorEl.appendChild(optionEl);
            });


            // when the selected option changes, dynamically change the calendar option
            localeSelectorEl.addEventListener('change', function() {
                if (this.value) {
                    calendar.setOption('locale', this.value);
                }
            });
        });
    </script>
@endsection
