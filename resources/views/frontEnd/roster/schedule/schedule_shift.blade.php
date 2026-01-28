@extends('frontEnd.layouts.master')
@section('title', 'Schedule Shift')
@section('content')

    @include('frontEnd.roster.common.roster_header')
    <!-- FullCalendar Scheduler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.css">
    <link rel="stylesheet" href="{{ url('/public/frontEnd/staff/css/schedule-shift.css') }}">

    <script type="text/javascript" src="{{ url('/public/frontEnd/js/external-dragging-calendar.js') }}"></script>
    <main class="page-content">
        <div class="container-fluid">

            <div class="topHeaderCont">
                <div>
                    <h1>Shift Schedule</h1>
                    <p class="header-subtitle">Manage and assign shifts to carers</p>
                </div>
                <div class="topFilters">

                    <button class="filterBtn activeDot">
                        <span class="dot"></span> All Active
                    </button>
                    <button class="filterBtn">
                        📅 Today
                    </button>
                    <button class="filterBtn">
                        📆 This Week
                    </button>
                    <button class="filterBtn">
                        ⭐ Saved Views
                    </button>
                    <button class="filterBtn">
                        ⬇ Export
                    </button>
                    <button class="filterBtn highlight">
                        ✨ AI Generate
                    </button>
                    <button class="filterBtn">
                        🛠 Smart Allocate
                    </button>
                    <button class="filterBtn lightGreen">
                        🔁 Recurring
                    </button>
                    <button class="btn allBtnUseColor" data-toggle="modal" data-target="#addShiftModal">+ Add Shift</button>
                </div>

            </div>

            <!-- Alerts -->
            <div class="rota_alerts">
                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Mixed Shift - 09:30</div>
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Mixed Shift</div>
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Unfilled Shift in Next 24 Hours</div>
                        <div class="rota_alert-description">May 12, 2025: 16:30 at All or Care Home assigned care! Check Margaret Smith</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Assign a qualified carer to this shift urgently</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_view-all">View All (4 More) →</div>
            </div>

            <!-- Smart Suggestions -->
            <div class="suggestions">
                <div class="suggestion-card" style="border: 1px solid #fdbb76; border-left:4px solid #fdbb76">
                    <div class="suggestion-icon" style="background-color: #ea580c;">⚠️</div>
                    <div class="suggestionRightCont">
                        <div class="suggestion-title">Unfilled Shifts Detected</div>
                        <div class="suggestion-description">
                            You have 292 shifts without assigned carers. Would you like AI to help assign them?
                        </div>
                        <div class="suggestion-actions">
                            <button class="suggestion_btn-small suggestion_btn-orange">Auto-Assign →</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- advancedFiltersBox -->

            <div class="advancedFiltersBox m-b-15">
                <button class="advBtn">
                    🔍 Advanced Filters ▼
                </button>
                <div class="filterPanel" style="display:none;">
                    <h4>QUICK PRESETS</h4>
                    <div class="quickPresets">
                        <button class="preset activeDot">
                            <span class="dot"></span> Active Only
                        </button>
                        <button class="preset">
                            📅 This Week
                        </button>
                        <button class="preset yellow">
                            ⚠ High Priority
                        </button>
                    </div>


                    <div class="filter-row m-t-15">
                        <select class="filter-item">
                            <option>Status</option>
                            <option>Active</option>
                            <option>Draft</option>
                            <option>Closed</option>
                        </select>

                        <select class="filter-item small">
                            <option>is</option>
                            <option>is not</option>
                            <option>contains</option>
                        </select>

                        <input type="text" class="filter-input" placeholder="draft" />

                        <button class="close-btn">×</button>
                    </div>

                    <hr>
                    <h4>ADD FILTER FIELD</h4>
                    <div class="searchBox">
                        <span>🔍</span>
                        <input type="text" placeholder="Search all fields...">
                    </div>

                    <div class="filterFields">
                        <button class="fieldBtn">+ Status</button>
                        <button class="fieldBtn">+ Shift Type</button>
                        <button class="fieldBtn">+ Date</button>
                        <button class="fieldBtn">+ Duration</button>
                    </div>

                </div>
            </div>
            <!-- End of advancedFiltersBox -->

            <div class="calendarTabs">
                <div class="tabs">
                    <button class="tab active" data-tab="roster">
                        <span><i class='bx  bx-grid'></i> </span> Roster
                    </button>

                    <button class="tab" data-tab="day">
                        <span><i class='bx  bx-calendar-alt'></i> </span> Day
                    </button>

                    <button class="tab" data-tab="week">
                        <span><i class='bx  bx-calendar'></i> </span> Week
                    </button>

                    <button class="tab" data-tab="month">
                        <span><i class='bx  bx-calendar-detail'></i> </span> Month
                    </button>

                    <button class="tab" data-tab="days90">
                        <span><i class='bx  bx-calendar-detail'></i></span> 90 Days
                    </button>

                    <button class="tab" data-tab="list">
                        <span><i class='bx  bx-list-ul'></i> </span> List
                    </button>

                    <button class="tab" data-tab="bygroup">
                        <span><i class='bx  bx-arrow-cross'></i> </span> By Group
                    </button>

                    <button class="tab" data-tab="split">
                        <span><i class='bx  bx-table-cells'></i> </span> Split
                    </button>
                </div>

                <!-- ********************************** -->

                <div class="schedulingIssues">
                    <div class="accordion" id="accordionExample">
                        <div class="item">
                            <div class="item-header" id="headingFour">
                                <h2 class="mb-0 mt-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <div class="issuesDetectedHead">
                                            <div class="schedulingIcon" style="background-color: #ea580c;"> <i class="bx  bx-alert-triangle"></i> </div>
                                            <span>
                                                52 Scheduling Issues Detected
                                                <p class="priorityNumber"><span class="highProry"> 14 High Priority </span> <span> 38 Medium Priority </span></p>
                                            </span>
                                        </div>
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample">
                                <div class="t-p achedulingAccordionTabs">
                                    <div class="tabs">
                                        <button class="tab active" data-tab="allSchedulingIssues">
                                            All (52)
                                        </button>
                                        <button class="tab" data-tab="overallocationSchedulingIssues">
                                            Overallocation (2)
                                        </button>
                                        <button class="tab" data-tab="unfilledSchedulingIssues">
                                            Unfilled (12)
                                        </button>
                                        <button class="tab" data-tab="availabilitySchedulingIssues">
                                            Availability (36)
                                        </button>
                                        <button class="tab" data-tab="consecutiveSchedulingIssues">
                                            Consecutive (1)
                                        </button>
                                    </div>

                                    <div class="tab-content commonSchedulingIssues">
                                        <div class="content active" id="allSchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">effective</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i> 01 Jan</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Emma Wilson scheduled for 10.0 hours on Jan 1 (approaching limit) </strong></div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div> Total scheduled: 10.0 hours</div>
                                                    </div>
                                                </div>
                                                <div class="planCard redBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">HIGH</span>
                                                            <span class="commntagDesin">unfilled</span>
                                                        </div>
                                                        <div class="header-actions">
                                                            <div class="issuesDropdown">
                                                                <span class="dropdownToggle borderBtn" tabindex="0">
                                                                    Action <i class='bx  bx-chevron-down'></i>
                                                                </span>
                                                                <ul class="issuesDetectedDrop">
                                                                    <li><a href="#!"><i class='bx  bx-edit'></i> Edit Shift</a></li>
                                                                    <li><a href="#!"><i class='bx  bx-user-minus'></i> Unassign Carer</a></li>
                                                                    <li><a href="#!"><i class='bx  bx-paper-plane'></i> Send Coverage Request</a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unfilled shift for Unknown on Jan 8 at 09:00 </strong></div>
                                                    </div>
                                                </div>
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">availability</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i>03 Feb</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unknown is Not a working day on Feb 3 (09:00-17:00) </strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content" id="overallocationSchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">effective</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i> 01 Jan</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Emma Wilson scheduled for 10.0 hours on Jan 1 (approaching limit) </strong></div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div> Total scheduled: 10.0 hours</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content" id="unfilledSchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard redBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">HIGH</span>
                                                            <span class="commntagDesin">unfilled</span>
                                                        </div>
                                                        <div class="header-actions">
                                                            <select class="btn borderBtn">
                                                                <option>Actions</option>
                                                                <option>Actions</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unfilled shift for Unknown on Jan 8 at 09:00 </strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content" id="availabilitySchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">availability</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i>03 Feb</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unknown is Not a working day on Feb 3 (09:00-17:00) </strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content" id="consecutiveSchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i> </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">availability</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i>03 Feb</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unknown is Not a working day on Feb 3 (09:00-17:00) </strong></div>
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

                <!-- <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize clntalertheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain radIconClr"><i class="bx  bx-alert-triangle"></i> 52 Scheduling Issues Detected </div>
                                <p>14 High Priority <span>38 Medium Priority</span> </p>
                            </div>

                            <div class="actions mt-0">
                                <button class="btn addAssessmentBtn addalertClientDetailsBtn"> <i class="bx  bx-plus"></i> Add alert</button>
                            </div>
                        </header>

                        <div class="p-20">
                            <div class="clientFilterform addalertClientDetailsform" style="border: 2px solid #fdabab; background: #fef2f2;">
                                <div class="createNewAlert"><i class="bx  bx-alert-triangle"></i> Create New Alert </div>
                            </div>
                        </div>
                    </div> -->

                <!-- ********************************** -->

                <!-- TAB CONTENT -->
                <div class="tab-content">
                    <div class="content active" id="roster">
                        <!-- Top Blue Bar -->
                        <div class="roster-top">
                            <div class="title">
                                <h2>Care Home</h2> <span>Shift Roster</span>
                            </div>
                            <div class="stats">
                                <div class="stat"> <strong>12</strong> <small>Total Shifts</small> </div>
                                <div class="divider"></div>
                                <div class="stat filled"> <strong>0</strong> <small>Filled</small> </div>
                                <div class="divider"></div>
                                <div class="stat open"> <strong>12</strong> <small>Open</small> </div>
                                <div class="divider"></div>
                                <div class="stat hours"> <strong>96h</strong> <small>Hours</small> </div>
                            </div>
                        </div>
                        <!-- Date / Navigation Row -->
                        <div class="roster-nav">
                            <div class="left">
                                <button class="icon-btn" id="btnPrev">‹</button>

                                <button id="btnDay">Day</button>
                                <button id="btnWeek" class="active">Week</button>

                                <button class="date-btn" id="dateRange">
                                    📅 --
                                </button>

                                <button class="icon-btn" id="btnNext">›</button>
                                <button id="btnToday">Today</button>
                            </div>

                            <div class="right">
                                <button class="outline">Bulk Actions</button>
                                <button class="primary">👥 Staff</button>
                                <button>📍 Locations</button>
                                <button>👤 Clients</button>
                                <button>⇄ Split</button>
                            </div>
                        </div>


                        <div id="calendar"></div>
                        <div class="cell dropzone"
                            data-date="2026-01-21"
                            data-staff-id="12">

                            <div class="roster-footer">
                                <div class="shift-types">
                                    <span class="label">Shift Types:</span>

                                    <span class="shift"><i class="dot morning"></i> Morning</span>
                                    <span class="shift"><i class="dot afternoon"></i> Afternoon</span>
                                    <span class="shift"><i class="dot evening"></i> Evening</span>
                                    <span class="shift"><i class="dot night"></i> Night</span>
                                    <span class="shift"><i class="dot sleepin"></i> Sleep In</span>
                                </div>

                                <div class="unassigned">
                                    <i class="dashed-box"></i> Unassigned
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ********************************************************* -->

                    <div class="content" id="day">
                        <h3>Day View</h3>
                        <p>Day schedule appears here.</p>
                    </div>

                    <div class="content" id="week">
                        <h3>Week View</h3>
                        <p>Weekly details appear here.</p>
                    </div>

                    <div class="content" id="month">
                        <h3>Month View</h3>
                        <p>Monthly overview shown here.</p>
                    </div>

                    <div class="content" id="days90">
                        <div class="days90Content">
                            <div class="sectionWhiteBgAllUse">
                                <div class="dailyLogsdateSec">
                                    <div class="date-slider">
                                        <button class="nav-btn prev-btn"><i class='bx  bx-chevron-left'></i> Previous</button>

                                        <div class="changeDateSlide">
                                            <div class="date-display">
                                                <div class="date-inner">
                                                    <span class="day-text">Friday</span>,
                                                    <span class="full-date">January 16, 2026</span>
                                                </div>
                                                <p>90-Day Overview</p>
                                            </div>
                                        </div>
                                        <div class="datechangeBtnTodayOrNext">
                                            <button class="btn borderBtn">Today</button>
                                            <button class="nav-btn next-btn">Next <i class='bx  bx-chevron-right'></i> </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="rota_dashboard-cards simpleCard">
                                <div class="rota_dash-card blue">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Total Shifts</p>
                                        <h2 class="rota_count">37</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card orangeClr">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Filled</p>
                                        <h2 class="rota_count greenText">36</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card green">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Unfilled</p>
                                        <h2 class="rota_count orangeText">0</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card redClr">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Fill Rate</p>
                                        <h2 class="rota_count blueText">1</h2>
                                    </div>
                                </div>
                            </div>


                            <div class="carePlanWrapper proactiveSuggestionsWrap weeklyBreakdownAddCont">
                                <div class="psHeader">
                                    <span class="psIcon"><i class="bx  bx-trending-up"></i> </span>
                                    <span class="psTitle">Weekly Breakdown</span>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag greenShowbtn">100% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:100%;"></div>
                                    </div>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag radShowbtn">0% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:0%;"></div>
                                    </div>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag greenShowbtn">100% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:100%;"></div>
                                    </div>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag radShowbtn">0% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:0%;"></div>
                                    </div>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag greenShowbtn">100% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:100%;"></div>
                                    </div>
                                </div>

                                <div class="planCard">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            Week 1: Apr 12 - Apr 18, 2026
                                        </div>
                                        <div class="planActions">
                                            <span class="roundBtntag radShowbtn">0% Filled</span>
                                        </div>
                                    </div>

                                    <div class="planMeta totalShiftsCounter">
                                        <div class="rota_dash-left">
                                            Total Shifts
                                            <h2 class="rota_count">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Filled
                                            <h2 class="rota_count greenText">11</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Unfilled
                                            <h2 class="rota_count orangeText">0</h2>
                                        </div>
                                        <div class="rota_dash-left">
                                            Completed
                                            <h2 class="rota_count blueText">0</h2>
                                        </div>
                                    </div>
                                    <div class="progressBar">
                                        <div class="progressFill" style="width:0%;"></div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="content" id="list">
                        <div class="days90Content carertabcontent">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> • <span>09:00 - 17:00 (8h)</span>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">unfilled</span>
                                                <span class="inactive">Morning</span>
                                                <span class="inactive">residential care</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span> <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i> </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content" id="bygroup">
                        <div class="byGroupContent">
                            <div class="workHoursHeader">
                                <div class="title radIconClr"> Phil Holt <i class='bx bx-alert-circle'></i> </div>
                                <div class="actions">
                                    10 shifts
                                    <div class="roundBtntag radShowbtn">67.0h</div>
                                </div>
                            </div>
                            <div class="recent-activity sectionWhiteBgAllUse">
                                <!-- <div class="section-header">
                                                            <h2 class="section-title">Recent Activity</h2>
                                                        </div> -->

                                <div class="activity-item">
                                    <div class="activity-icon"><i class='bx  bx-apps'></i> </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Unknown</div>
                                        <div class="activity-description"><i class='bx  bx-clock-4'></i> 09:00 - 17:00</div>
                                        <div class="activity-time"><i class='bx  bx-calendar'></i> 2026-01-25</div>
                                        <div class="inactive roundTag">Morning</div>

                                        <div class="planActions">
                                            <button><i class="bx  bx-edit"></i> Edit </button>
                                            <button class="danger"><i class="bx  bx-trash"></i> Delete </button>
                                        </div>
                                    </div>
                                    <div class="roundBtntag greenShowbtn">unfilled</div>
                                </div>

                                <div class="activity-item">
                                    <div class="activity-icon"><i class='bx  bx-apps'></i> </div>
                                    <div class="activity-content">
                                        <div class="activity-title">Unknown</div>
                                        <div class="activity-description"><i class='bx  bx-clock-4'></i> 09:00 - 17:00</div>
                                        <div class="activity-time"><i class='bx  bx-calendar'></i> 2026-01-25</div>
                                        <div class="inactive roundTag">Morning</div>

                                        <div class="instructionBox">
                                            Time overlap with another shift (09:00-17:00)
                                        </div>

                                        <div class="planActions">
                                            <button><i class="bx  bx-edit"></i> Edit </button>
                                            <button class="danger"><i class="bx  bx-trash"></i> Delete </button>
                                        </div>
                                    </div>
                                    <div class="roundBtntag greenShowbtn">unfilled</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content" id="split">
                        <h3>Split View</h3>
                        <p>Split layout appears here.</p>
                    </div>
                </div>

            </div>

        </div>

        <!-- add Shift Schedule Modal -->
        <div class="modal fade leaveCommunStyle" id="addShiftModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"> Create New Shift</h4>
                    </div>
                    <div class="modal-body approveLeaveModal heightScrollModal">
                        <div class="carer-form createNewShiftTabBtn">
                            <form>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="">
                                            <label>care Type</label>
                                            <select class="form-control">
                                                <option>Active</option>
                                                <option>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="calendarTabs leaveRequesttabs m-t-10">
                                            <label>Assignment *</label>
                                            <div class="tabs p-2 m-b-10" style="background-color: #f5f5f5;">
                                                <button type="button" class="tab active" data-tab="scheduleLocation">
                                                    <i class='bx  bx-location'></i> Location
                                                </button>

                                                <button type="button" class="tab" data-tab="scheduleClient">
                                                    <i class='bx  bx-user'></i> Client
                                                </button>

                                                <button type="button" class="tab" data-tab="scheduleProperty">
                                                    <i class="fa fa-building-o"></i> Property
                                                </button>
                                            </div>

                                            <!-- TAB CONTENT -->
                                            <div class="tab-content carertabcontent">
                                                <div class="content active" id="scheduleLocation">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <select class="form-control">
                                                                <option>Select location</option>
                                                                <option>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <input type="text" name="" id="" class="form-control" placeholder="Enter custom location name">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <input type="text" name="" id="" class="form-control" placeholder="Address (optional)">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label>Date *</label>
                                                            <input type="date" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row m-t-10">
                                                        <div class="col-md-6">
                                                            <label>Start Time *</label>
                                                            <input type="time" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>End Time *</label>
                                                            <input type="time" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Shift Type</label>
                                                            <select class="form-control">
                                                                <option>Morning</option>
                                                                <option>Afternoon</option>
                                                                <option>Evening</option>
                                                                <option>Night</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Tasks (comma separated)</label>
                                                            <textarea class="form-control" rows="3" placeholder="e.g., Medication, Personal care, Meal preparation" name=""></textarea>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Notes</label>
                                                            <textarea class="form-control" rows="3" placeholder="Additional notes or instructions" name=""></textarea>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <div class="overtime  recurringShift">
                                                                <label>
                                                                    <input type="checkbox"> Make this a recurring shift
                                                                </label>

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label>Frequency</label>
                                                                        <select class="form-control">
                                                                            <option>Fortnightly</option>
                                                                            <option>Daily</option>
                                                                            <option>Weekly</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-12 m-t-10">
                                                                        <label>Frequency</label>
                                                                        <div class="weeklyDaysSelect">
                                                                            <span class="active">Sun</span>
                                                                            <span>Mon</span>
                                                                            <span>Tue</span>
                                                                            <span>Wed</span>
                                                                            <span>Thu</span>
                                                                            <span>Fri</span>
                                                                            <span>Sat</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 m-t-10">
                                                                        <label>End Date</label>
                                                                        <input type="date" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="attached-documents">

                                                                <div class="header">
                                                                    <div class="title">
                                                                        📎 <span>Attached Documents</span>
                                                                    </div>
                                                                    <div class="AttachAndCloseBtn">
                                                                        <button type="button" class="close-btn"><i class='bx  bx-plus'></i> Attach</button>
                                                                        <button type="button" class="close-btn"><i class='bx  bx-x'></i> </button>
                                                                    </div>
                                                                </div>

                                                                <div class="upload-box">


                                                                    <div class="" id="availabilityTab">
                                                                        <div class="availabilityTabs">
                                                                            <!-- TAB HEADER -->
                                                                            <div class="availabilityTabs__nav">
                                                                                <button type="button" class="availabilityTabs__tab active" data-target="selectfromSystem"> 📁 Select from System</button>
                                                                                <button type="button" class="availabilityTabs__tab" data-target="uploadFiles"> ⬆ Upload File</button>
                                                                            </div>

                                                                            <div class="availabilityTabs__content">

                                                                                <div class="availabilityTabs__panel active" id="selectfromSystem">
                                                                                    <div class="selectfromSystemTabCont">
                                                                                        <div class="input-group selectfromSearch">
                                                                                            <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                                                                            <input type="text" class="form-control" placeholder="Search entries...">
                                                                                        </div>

                                                                                        <div class="addSystemList">
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="availabilityTabs__panel" id="uploadFiles">

                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <label>Document Name</label>
                                                                                            <input type="text" class="form-control" placeholder="e.g. Supervision Note">
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <label>Document Type</label>
                                                                                            <select class="form-control">
                                                                                                <option>Other</option>
                                                                                                <option>Care Plan</option>
                                                                                                <option>Risk Assessment</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="checkbox">
                                                                                        <label>
                                                                                            <input type="checkbox">
                                                                                            Requires completion during shift
                                                                                        </label>
                                                                                    </div>

                                                                                    <button class="upload-btn">
                                                                                        ⬆ Upload & Attach
                                                                                    </button>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>

                                                                <div class="m-t-15">
                                                                    <div class="pendingCompletion">
                                                                        <div class="header">
                                                                            Pending Completion (2)
                                                                        </div>

                                                                        <div class="card">
                                                                            <div class="left">
                                                                                <div class="icon blueText"><i class='bx  bx-file'></i> </div>
                                                                                <div class="info">
                                                                                    <div class="title">Restrictive Physical Interventi...</div>
                                                                                    <div class="meta">
                                                                                        <div class="inactive roundTag">incident</div>
                                                                                        <span class="date">Jan 13, 2026</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="actions">
                                                                                <span class="approve"><i class='bx  bx-check-circle'></i> </span>
                                                                                <span class="delete"><i class='bx  bx-trash'></i> </span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="card">
                                                                            <div class="left">
                                                                                <div class="icon blueText"><i class='bx  bx-image'></i> </div>
                                                                                <div class="info">
                                                                                    <div class="title">dzad</div>
                                                                                    <div class="meta">
                                                                                        <div class="inactive roundTag">audit</div>
                                                                                        <span class="date">Jan 13, 2026</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="actions">
                                                                                <span class="view"><i class='bx  bx-eye'></i> </span>
                                                                                <span class="approve"><i class='bx  bx-check-circle'></i> </span>
                                                                                <span class="delete"><i class='bx  bx-trash'></i> </span>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>

                                                                <div class="empty-state">
                                                                    <div class="icon">📎</div>
                                                                    <p><strong>No documents attached</strong></p>
                                                                    <p class="hint">Click “Attach” to add documents</p>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="content" id="scheduleClient">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <select class="form-control">
                                                                <option>Select Client</option>
                                                                <option>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label>care Type</label>
                                                            <input type="date" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row m-t-10">
                                                        <div class="col-md-6">
                                                            <label>Start Time *</label>
                                                            <input type="time" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Start Time *</label>
                                                            <input type="time" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Shift Type</label>
                                                            <select class="form-control">
                                                                <option>Select Client</option>
                                                                <option>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Tasks (comma separated)</label>
                                                            <textarea class="form-control" rows="3" placeholder="Add any notes for the staff member..." name="approve_note"></textarea>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <label>Notes</label>
                                                            <textarea class="form-control" rows="3" placeholder="Add any notes for the staff member..." name="approve_note"></textarea>
                                                        </div>
                                                        <div class="col-md-12  m-t-10">
                                                            <div class="overtime  recurringShift">
                                                                <label>
                                                                    <input type="checkbox"> Make this a recurring shift
                                                                </label>

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label>Frequency</label>
                                                                        <select class="form-control">
                                                                            <option>Fortnightly</option>
                                                                            <option>Daily</option>
                                                                            <option>Weekly</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-12 m-t-10">
                                                                        <label>Frequency</label>
                                                                        <div class="weeklyDaysSelect">
                                                                            <span class="active">Sun</span>
                                                                            <span>Mon</span>
                                                                            <span>Tue</span>
                                                                            <span>Wed</span>
                                                                            <span>Thu</span>
                                                                            <span>Fri</span>
                                                                            <span>Sat</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 m-t-10">
                                                                        <label>End Date</label>
                                                                        <input type="date" class="form-control">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="attached-documents">

                                                                <div class="header">
                                                                    <div class="title">
                                                                        📎 <span>Attached Documents</span>
                                                                    </div>
                                                                    <div class="AttachAndCloseBtn">
                                                                        <button type="button" class="close-btn"><i class='bx  bx-plus'></i> Attach</button>
                                                                        <button type="button" class="close-btn"><i class='bx  bx-x'></i> </button>
                                                                    </div>
                                                                </div>

                                                                <div class="upload-box">


                                                                    <div class="" id="availabilityTab">
                                                                        <div class="availabilityTabs">
                                                                            <!-- TAB HEADER -->
                                                                            <div class="availabilityTabs__nav">
                                                                                <button type="button" class="availabilityTabs__tab active" data-target="selectfromSystem"> 📁 Select from System</button>
                                                                                <button type="button" class="availabilityTabs__tab" data-target="uploadFiles"> ⬆ Upload File</button>
                                                                            </div>

                                                                            <div class="availabilityTabs__content">

                                                                                <div class="availabilityTabs__panel active" id="selectfromSystem">
                                                                                    <div class="selectfromSystemTabCont">
                                                                                        <div class="input-group selectfromSearch">
                                                                                            <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                                                                            <input type="text" class="form-control" placeholder="Search entries...">
                                                                                        </div>

                                                                                        <div class="addSystemList">
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>
                                                                                            <div class="systemList">
                                                                                                <span class="blueText"><i class='bx  bx-file-detail'></i> </span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>Restrictive Physical Intervention Record</p>
                                                                                                    <div class="inactive roundTag">helthcare</div>
                                                                                                </div>
                                                                                                <span><i class='bx  bx-plus'></i> </span>
                                                                                            </div>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="availabilityTabs__panel" id="uploadFiles">

                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <label>Document Name</label>
                                                                                            <input type="text" class="form-control" placeholder="e.g. Supervision Note">
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <label>Document Type</label>
                                                                                            <select class="form-control">
                                                                                                <option>Other</option>
                                                                                                <option>Care Plan</option>
                                                                                                <option>Risk Assessment</option>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="checkbox">
                                                                                        <label>
                                                                                            <input type="checkbox">
                                                                                            Requires completion during shift
                                                                                        </label>
                                                                                    </div>

                                                                                    <button class="upload-btn">
                                                                                        ⬆ Upload & Attach
                                                                                    </button>
                                                                                </div>

                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>

                                                                <div class="m-t-15">
                                                                    <div class="pendingCompletion">
                                                                        <div class="header">
                                                                            Pending Completion (2)
                                                                        </div>

                                                                        <div class="card">
                                                                            <div class="left">
                                                                                <div class="icon blueText"><i class='bx  bx-file'></i> </div>
                                                                                <div class="info">
                                                                                    <div class="title">Restrictive Physical Interventi...</div>
                                                                                    <div class="meta">
                                                                                        <div class="inactive roundTag">incident</div>
                                                                                        <span class="date">Jan 13, 2026</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="actions">
                                                                                <span class="approve"><i class='bx  bx-check-circle'></i> </span>
                                                                                <span class="delete"><i class='bx  bx-trash'></i> </span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="card">
                                                                            <div class="left">
                                                                                <div class="icon blueText"><i class='bx  bx-image'></i> </div>
                                                                                <div class="info">
                                                                                    <div class="title">dzad</div>
                                                                                    <div class="meta">
                                                                                        <div class="inactive roundTag">audit</div>
                                                                                        <span class="date">Jan 13, 2026</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="actions">
                                                                                <span class="view"><i class='bx  bx-eye'></i> </span>
                                                                                <span class="approve"><i class='bx  bx-check-circle'></i> </span>
                                                                                <span class="delete"><i class='bx  bx-trash'></i> </span>
                                                                            </div>
                                                                        </div>

                                                                    </div>

                                                                </div>

                                                                <div class="empty-state">
                                                                    <div class="icon">📎</div>
                                                                    <p><strong>No documents attached</strong></p>
                                                                    <p class="hint">Click “Attach” to add documents</p>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>
                                                </div> <!--  -->

                                                <div class="content" id="scheduleProperty">
                                                    Property
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Tabs -->
                                    </div>
                                    <div class="col-md-6">
                                        <div class="createNewShiftRightSide">
                                            <div class="simpleCard">
                                                <div class="rota_dash-card bg-blue-50">
                                                    <div class="rota_dash-left">
                                                        <p class="rota_title">Assigned To:</p>
                                                        <h2 class="rota_count">Not assigned</h2>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="assignedCarer m-t-15">
                                                <label>Assigned Carer</label>
                                                <div class="">
                                                    <div class="dashedBorder">
                                                        <div class="leavebanktabCont">
                                                            <i class='bx  bx-home-alt'></i>
                                                            <p>Select assignment first to see carer suggestions</p>
                                                        </div>
                                                    </div>

                                                    <div class="">
                                                        <div class="suggestedCarers">

                                                            <h4 class="title">Suggested Carers (Ranked by compatibility):</h4>

                                                            <div class="carerCard">
                                                                <div class="avatar">G</div>
                                                                <div class="details">
                                                                    <div class="topRow">
                                                                        <span class="name">Georgia Ashmore</span>
                                                                        <span class="badge">WA9 3BT</span>
                                                                    </div>
                                                                    <span class="tag">Geographic Mismatch</span>
                                                                    <div class="warning">
                                                                        <i class='bx  bx-alert-circle'></i>
                                                                        <i class='bx  bx-alert-triangle'></i>
                                                                        Very far from client
                                                                    </div>
                                                                </div>
                                                                <button class="assignBtn">Assign</button>
                                                            </div>

                                                            <div class="carerCard">
                                                                <div class="avatar">K</div>
                                                                <div class="details">
                                                                    <div class="topRow">
                                                                        <span class="name">Kelly Moss</span>
                                                                        <span class="badge">WA9 3BT</span>
                                                                    </div>
                                                                    <span class="tag">Geographic Mismatch</span>
                                                                    <div class="warning">
                                                                        <i class='bx  bx-alert-circle'></i>
                                                                        <i class='bx  bx-alert-triangle'></i>
                                                                        Very far from client
                                                                    </div>
                                                                </div>
                                                                <button class="assignBtn">Assign</button>
                                                            </div>
                                                            <div class="carerCard">
                                                                <div class="avatar">G</div>
                                                                <div class="details">
                                                                    <div class="topRow">
                                                                        <span class="name">Georgia Ashmore</span>
                                                                        <span class="badge">WA9 3BT</span>
                                                                    </div>
                                                                    <span class="tag">Geographic Mismatch</span>
                                                                    <div class="warning">
                                                                        <i class='bx  bx-alert-circle'></i>
                                                                        <i class='bx  bx-alert-triangle'></i>
                                                                        Very far from client
                                                                    </div>
                                                                </div>
                                                                <button class="assignBtn">Assign</button>
                                                            </div>

                                                            <div class="carerCard">
                                                                <div class="avatar">K</div>
                                                                <div class="details">
                                                                    <div class="topRow">
                                                                        <span class="name">Kelly Moss</span>
                                                                        <span class="badge">WA9 3BT</span>
                                                                    </div>
                                                                    <span class="tag">Geographic Mismatch</span>
                                                                    <div class="warning">
                                                                        <i class='bx  bx-alert-circle'></i>
                                                                        <i class='bx  bx-alert-triangle'></i>
                                                                        Very far from client
                                                                    </div>
                                                                </div>
                                                                <button class="assignBtn">Assign</button>
                                                            </div>

                                                        </div>


                                                    </div>

                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- FullCalendar Scheduler JS (includes core + interaction + resource) -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.js"></script>
        <script src="{{ url('public/frontEnd/staff/js/schedule-shift.js') }}"></script>


        <!-- Advanced Filters  -->
        <script>
            const btn = document.querySelector('.advBtn');
            const panel = document.querySelector('.filterPanel');

            btn.addEventListener('click', () => {
                if (panel.style.display === "none") {
                    panel.style.display = "block";
                } else {
                    panel.style.display = "none";
                }
            });
        </script>


        <!-- calendarTabs -->

        <script>
            const tabs = document.querySelectorAll(".tab");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {
                    const tabName = tab.getAttribute("data-tab");
                    let scope = tab.parentElement;
                    while (scope && !scope.querySelector(`#${tabName}`)) {
                        scope = scope.parentElement;
                    }
                    if (!scope) return;
                    scope.querySelectorAll(".tab.active").forEach(t => {
                        t.classList.remove("active");
                    });
                    tab.classList.add("active");
                    scope.querySelectorAll(".content.active").forEach(c => {
                        c.classList.remove("active");
                    });
                    const target = scope.querySelector(`#${tabName}`);
                    if (target) {
                        target.classList.add("active");
                    }
                });
            });

            // Make external events draggable
            $('#external-events .fc-event').each(function() {

                $(this).data('event', {
                    title: $.trim($(this).text()),
                    stick: true
                });

                $(this).draggable({
                    zIndex: 999,
                    revert: true,
                    revertDuration: 0
                });
            });
            $(document).ready(function() {
                $('#calendar').fullCalendar({
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'agendaWeek,agendaDay'
                    },

                    editable: true,
                    droppable: true,

                    drop: function(date) {
                        console.log('Dropped on ' + date.format());
                    }
                });
            });

            // $(document).ready(function() {
            //     // Make external events draggable
            //     $('#external-events .fc-event').each(function() {
            //         var eventObject = {
            //             title: $.trim($(this).text())
            //         };
            //         // store event data
            //         $(this).data('eventObject', eventObject);

            //         // make draggable
            //         $(this).draggable({
            //             zIndex: 999,
            //             revert: true,
            //             revertDuration: 0
            //         });
            //     });

            //     // Initialize calendar
            //     $('#calendar').fullCalendar({
            //         header: {
            //             left: 'prev,next today',
            //             center: 'title',
            //             right: 'agendaWeek,agendaDay'
            //         },
            //         editable: true,
            //         droppable: true,
            //         drop: function(date, jsEvent, ui) {
            //             var originalEventObject = $(this).data('eventObject');
            //             var copiedEventObject = $.extend({}, originalEventObject);
            //             copiedEventObject.start = date;
            //             // 🔥 THIS LINE APPENDS EVENT TO CALENDAR
            //             $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
            //         }
            //     });

            // });
        </script>
        <script>
            document.querySelectorAll(".availabilityTabs").forEach(wrapper => {
                const tabs = wrapper.querySelectorAll(".availabilityTabs__tab");
                const panels = wrapper.querySelectorAll(".availabilityTabs__panel");

                tabs.forEach(tab => {
                    tab.addEventListener("click", () => {
                        tabs.forEach(t => t.classList.remove("active"));
                        panels.forEach(p => p.classList.remove("active"));
                        tab.classList.add("active");
                        wrapper
                            .querySelector("#" + tab.dataset.target)
                            .classList.add("active");
                    });
                });
            });
        </script>


        <!--Start JS 90-Day Overview -->
        {{-- <script>
            let currentDate = new Date();
            const dayText = document.querySelector(".day-text");
            const fullDate = document.querySelector(".full-date");
            const dateInner = document.querySelector(".date-inner");
            const datePicker = document.querySelector(".date-picker");

            function formatDate(date) {
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const parts = date.toLocaleDateString('en-US', options).split(',');
                return {
                    day: parts[0],
                    full: parts.slice(1).join(',').trim()
                };
            }

            function updateDate(direction = "next") {
                dateInner.style.transform = direction === "next" ?
                    "translateX(-100%)" :
                    "translateX(100%)";
                dateInner.style.opacity = "0";

                setTimeout(() => {
                    const formatted = formatDate(currentDate);
                    console.log(formatted)
                    dayText.textContent = formatted.day;
                    fullDate.textContent = formatted.full;

                    dateInner.style.transform = "translateX(0)";
                    dateInner.style.opacity = "1";
                }, 300);
            }

            document.querySelector(".next-btn").addEventListener("click", () => {
                currentDate.setDate(currentDate.getDate() + 1);
                updateDate("next");
            });

            document.querySelector(".prev-btn").addEventListener("click", () => {
                currentDate.setDate(currentDate.getDate() - 1);
                updateDate("prev");
            });

            datePicker.addEventListener("change", function() {
                currentDate = new Date(this.value);
                updateDate();
            });
        </script> --}}
        <!--End JS 90-Day Overview -->

    @endsection
</main>
