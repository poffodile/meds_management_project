@extends('frontEnd.layouts.master')
@section('title', 'Schedule Shift')
@section('content')
    <meta name="base-url" content="{{ url('') }}">
    @include('frontEnd.roster.common.roster_header')
    <!-- FullCalendar Scheduler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@6.1.11/index.global.min.css">
    <link rel="stylesheet" href="{{ url('/public/frontEnd/staff/css/schedule-shift.css') }}">

    <script type="text/javascript" src="{{ url('/public/frontEnd/js/external-dragging-calendar.js') }}"></script>

    <style>
        .documentContent {
            display: none;
        }

        .pendingCompletionSection {
            display: none;
        }

        @media print {
            #pendingCount {
                display: inline;
                margin: 0;
                padding: 0;
                white-space: nowrap;
            }
        }

        #close_document {
            display: none;
        }

        .assessment-card {
            display: none;
        }
    </style>
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
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned
                        </div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and
                            verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Mixed Shift</div>
                        <div class="rota_alert-description">Ron contacted for PDA one can I have contact No one assigned
                        </div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Contact care immediately and
                            verify shift status</div>
                    </div>
                    <div class="rota_alert-badge">New</div>
                </div>

                <div class="rota_alert">
                    <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                    <div class="rota_alert-content">
                        <div class="rota_alert-title">Unfilled Shift in Next 24 Hours</div>
                        <div class="rota_alert-description">May 12, 2025: 16:30 at All or Care Home assigned care! Check
                            Margaret Smith</div>
                        <div class="rota_alert-bottmDescription"> <i class="fa fa-bolt"></i> Assign a qualified carer to
                            this shift urgently</div>
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
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                        data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        <div class="issuesDetectedHead">
                                            <div class="schedulingIcon" style="background-color: #ea580c;"> <i
                                                    class="bx  bx-alert-triangle"></i> </div>
                                            <span>
                                                52 Scheduling Issues Detected
                                                <p class="priorityNumber"><span class="highProry"> 14 High Priority </span>
                                                    <span> 38 Medium Priority </span>
                                                </p>
                                            </span>
                                        </div>
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour"
                                data-parent="#accordionExample">
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
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i>
                                                            </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">effective</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i> 01 Jan</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI
                                                                Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Emma Wilson scheduled for 10.0 hours on Jan 1
                                                                (approaching limit) </strong></div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div> Total scheduled: 10.0 hours</div>
                                                    </div>
                                                </div>
                                                <div class="planCard redBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i></span>
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
                                                        <div><strong>Unfilled shift for Unknown on Jan 8 at 09:00 </strong>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i></span>
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
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i>
                                                            </span>
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
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i></span>
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
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i>
                                                            </span>
                                                            <span class="commntagDesin careBadg">MEDIUM</span>
                                                            <span class="commntagDesin effecTive">availability</span>
                                                            <span class="commntagDesin dateBadg"><i class="bx bx-calendar"></i>03 Feb</span>
                                                        </div>

                                                        <div class="header-actions">
                                                            <button class="btn purpleBgBtn"><i class="bx bx-brain"></i> AI
                                                                Resolve</button>
                                                        </div>
                                                    </div>
                                                    <div class="planMeta">
                                                        <div><strong>Unknown is Not a working day on Feb 3 (09:00-17:00)
                                                            </strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="content" id="consecutiveSchedulingIssues">
                                            <div class="carePlanWrapper">
                                                <div class="planCard yllowBgAndBorder">
                                                    <div class="planTop">
                                                        <div class="planTitle">
                                                            <span class="heartIcon"><i class="bx  bx-alert-triangle"></i></span>
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
                                <h2 class="h2-color">Care Home</h2> <span>Shift Roster</span>
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

                        <!-- Filters Row -->
                        <div class="roster-filters">
                            <div class="left">
                                <select>
                                    <option>Runs</option>
                                </select>
                                <select>
                                    <option>By Visit</option>
                                </select>
                                <select>
                                    <option>View: Planned</option>
                                </select>
                                <select>
                                    <option>Duration: 1 Day</option>
                                </select>
                                <button class="lock-btn">
                                    ✔ Locked Visits
                                </button>
                            </div>

                            <input type="text" class="search" placeholder="Search..." />
                        </div>
                        <!-- Date / Navigation Row -->
                        <div class="roster-nav">
                            <div class="left">
                                <button class="icon-btn" id="btnPrev">‹</button>
                                <button id="btnDay">Day</button>
                                <button id="btnWeek" class="active">Week</button>
                                <button class="" id="dateRange"> 📅 -- </button>
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
                        <div class="cell dropzone" data-date="2026-01-21" data-staff-id="12">

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
                                            <button class="nav-btn next-btn">Next <i class='bx  bx-chevron-right'></i>
                                            </button>
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
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i></button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i> Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
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
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span>Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 m-t-25">
                                    <div class="profile-card careTasksCard mb-0">
                                        <div class="details mt-0">
                                            <div class="item">
                                                <i class="bx  bx-clock"></i><span><strong> Fri, Oct 16 </strong> </span> •
                                                <span>09:00 - 17:00 (8h)</span>
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
                                                <span class="greenText"> <i class='bx  bx-home-alt-2'></i></span>
                                                <span><strong> East Wing </strong> </span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-user'></i> <span> Unassigned</span>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="actions">
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-edit'></i> Edit
                                            </button>
                                            <button class="borderBtn delete" data-id="120"> <i class='bx  bx-trash'></i>
                                            </button>
                                            <button class="borderBtn edit" data-id="120"> <i class='bx  bx-paper-plane'></i>
                                                Request </button>
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
        <div class="modal fade leaveCommunStyle" id="addShiftModal" tabindex="1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog newShiftModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"> Create New Shift</h4>
                    </div>
                    <div class="modal-body approveLeaveModal heightScrollModal">
                        <div class="carer-form createNewShiftTabBtn">
                            <form id="createShiftForm" action="{{ route('roster.schedule.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="carer_id" id="selected_carer_id">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="">
                                            <label>Care Type</label>
                                            <select class="form-control" id="careType" name="care_type">
                                                @foreach ($company_department as $department)
                                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="calendarTabs leaveRequesttabs m-t-10">
                                            <label>Assignment *</label>
                                            <div class="tabs p-2 m-b-10" style="background-color: #f5f5f5;">
                                                <button type="button" class="tab" id="locationTab" data-tab="scheduleLocation">
                                                    <i class='bx  bx-location'></i> Location
                                                </button>

                                                <button type="button" class="tab active" id="clientTab" data-tab="scheduleClient">
                                                    <i class='bx  bx-user'></i> Client
                                                </button>

                                                <button type="button" class="tab" id="propertyTab" data-tab="scheduleProperty">
                                                    <i class="fa fa-building-o"></i> Property
                                                </button>
                                            </div>

                                            <!-- TAB CONTENT -->
                                            <div class="tab-content carertabcontent">
                                                {{-- Locaton Section --}}
                                                <div class="content" id="scheduleLocation">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <select class="form-control" name="location_id">
                                                                <option value="">Select location</option>
                                                                <option>Inactive</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <input type="text" name="location_name" id="" class="form-control"
                                                                placeholder="Enter custom location name">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <input type="text" name="location_address" id="" class="form-control"
                                                                placeholder="Address (optional)">
                                                        </div>

                                                    </div>
                                                </div>
                                                {{-- Location Section --}}

                                                {{-- Client Section --}}
                                                <div class="content active" id="scheduleClient">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <select class="form-control" id="clientSelect" name="client_id">
                                                                <option value="">Select Client</option>
                                                                @foreach ($service_users as $service_user)
                                                                    <option value="{{ $service_user->id }}"> {{ $service_user->name }} </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                                {{-- Client Section --}}

                                                {{-- Property Section --}}
                                                <div class="content" id="scheduleProperty">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <select class="form-control" name="property_id">
                                                                <option value="">Select Property</option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                                {{-- Property Section --}}
                                            </div>
                                            <div class="row m-t-10">
                                                <div class="col-md-12">
                                                    <label>Date *</label>
                                                    <input type="date" name="start_date" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Start Time *</label>
                                                    <input type="time" name="start_time" class="form-control" required>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>End Time *</label>
                                                    <input type="time" name="end_time" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12  m-t-10">
                                                    <label>Shift Type</label>
                                                    <select class="form-control" name="shift_type">
                                                        <option value="morning">Morning</option>
                                                        <option value="afternoon">Afternoon</option>
                                                        <option value="evening">Evening</option>
                                                        <option value="night">Night</option>
                                                        <option value="supervision">Supervision</option>
                                                        <option value="shadowing">Shadowing</option>
                                                        <option value="sleep_in">Sleep In</option>
                                                        <option value="waking_night">Waking Night</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Tasks (comma separated)</label>
                                                    <textarea class="form-control" rows="3"
                                                        placeholder="e.g., Medication, Personal care, Meal preparation"
                                                        name="tasks"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea class="form-control" rows="3"
                                                        placeholder="Additional notes or instructions" name="notes"></textarea>
                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <div class="overtime  recurringShift">
                                                        <label>
                                                            <input type="checkbox" id="recurringClientToggle" name="is_recurring" value="1"> Make this a recurring shift
                                                        </label>

                                                        <div class="row recurringOptions" id="recurringClientDiv">
                                                            <div class="col-md-12">
                                                                <label>Frequency</label>
                                                                <select class="form-control" name="frequency">
                                                                    <option value="daily">Daily</option>
                                                                    <option value="weekly">Weekly</option>
                                                                    <option value="fortnightly">Fortnightly</option>
                                                                    <option value="monthly">Monthly</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-12 m-t-10">
                                                                <label>Days of Week</label>
                                                                <input type="hidden" name="week_days" id="week_days">
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
                                                                <input type="date" name="end_date" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="attached-documents">
                                                        <div class="header">
                                                            <div class="title">
                                                                <i class="fa fa-paperclip"></i> <span>Attached Documents</span>
                                                            </div>
                                                            <div class="AttachAndCloseBtn">
                                                                <button type="button" id="attach_document" class="close-btn"><i class='bx bx-plus'></i> Attach</button>
                                                                <button type="button" id="close_document" class="close-btn"><i class='bx bx-x'></i> </button>
                                                            </div>
                                                        </div>
                                                        <div class="documentContent" id="documentContent">
                                                            <div class="upload-box">
                                                                <div class="" id="availabilityTab">
                                                                    <div class="availabilityTabs">
                                                                        <!-- TAB HEADER -->
                                                                        <div class="availabilityTabs__nav">
                                                                            <button type="button" class="availabilityTabs__tab active" data-target="selectfromSystem"> 📁
                                                                                Select from System</button>
                                                                            <button type="button" class="availabilityTabs__tab" data-target="uploadFiles"> <i class="fa fa-upload"></i> Upload File</button>
                                                                        </div>

                                                                        <div class="availabilityTabs__content">
                                                                            <div class="availabilityTabs__panel active" id="selectfromSystem">
                                                                                <div class="selectfromSystemTabCont">
                                                                                    <div class="input-group selectfromSearch">
                                                                                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                                                                        <input type="text" id="systemSearch" class="form-control" placeholder="Search entries...">
                                                                                    </div>

                                                                                    <div class="addSystemList">
                                                                                        <p id="noResults" style="display:none;">No results found</p>

                                                                                        @foreach ($dynamic_form_builder as $form)
                                                                                            <div class="systemList addFormItem">
                                                                                                <span class="blueText"><i class='bx bx-file-detail'></i></span>
                                                                                                <div class="helthcareText">
                                                                                                    <p>{{ $form['title'] }}</p>
                                                                                                    <div class="inactive roundTag"> {{ $form['title'] }} </div>
                                                                                                </div>
                                                                                                <span><i class='bx bx-plus'></i></span>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="availabilityTabs__panel" id="uploadFiles">
                                                                                <div class="row">
                                                                                    <div class="col-md-6">
                                                                                        <label>Document Name</label>
                                                                                        <input type="text" name="doc_name" class="form-control" placeholder="e.g. Supervision Note">
                                                                                    </div>
                                                                                    <div class="col-md-6">
                                                                                        <label>Document Type</label>
                                                                                        <select class="form-control" name="doc_type">
                                                                                            <option>Other</option>
                                                                                            <option>Supervision Form</option>
                                                                                            <option>Care Plan</option>
                                                                                            <option>Risk Assessment</option>
                                                                                            <option>Medication Chart</option>
                                                                                            <option>Daily Notes Template</option>
                                                                                            <option>Incident Form</option>
                                                                                            <option>Consent Form</option>
                                                                                            <option>Assessment</option>
                                                                                            <option>Training Record</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>

                                                                                <div class="checkbox">
                                                                                    <label>
                                                                                        <input type="checkbox" name="doc_required"> Requires completion during shift
                                                                                    </label>
                                                                                </div>
                                                                                <button class="upload-btn" type="button"> <i class="fa fa-upload"></i> Upload & Attach </button>
                                                                                <input type="file" id="imageUpload" name="doc_file" accept="image/*" style="display:none;">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="m-t-15 pendingCompletionSection" id="pendingCompletionSection">
                                                                <div class="pendingCompletion" id="pendingCompletion">
                                                                    <div class="header" id="pendingHeader">Pending Completion</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="empty-state attachDocumentSection" id="attachDocumentSection">
                                                            <div class="icon"> <i class="fa fa-paperclip"></i> </div>
                                                            <p><strong>No documents attached</strong></p>
                                                            <p class="hint">Click “Attach” to add documents</p>
                                                        </div>

                                                    </div>

                                                </div>

                                                <div class="col-md-12 mt-3">
                                                    <div class="assessment-card" id="assessment_card"> 
                                                        <div class="assessment-header">
                                                            <i class="fa fa-file-text-o doc-icon"></i>
                                                            <div>
                                                                <h4>Assessment Documents</h4>
                                                                <p>Attach pre-admission or care assessment documents to AI-generate a care plan</p>
                                                            </div>
                                                        </div>

                                                        <div class="upload-box">
                                                            <button type="button" class="assessment-upload-btn">
                                                                <i class="fa fa-upload"></i>
                                                                Upload Assessment Document
                                                            </button>
                                                            <input type="file" id="assessmentUpload" name="assessment_doc" accept=".pdf,.doc,.docx" style="display:none;">
                                                        </div>
                                                    </div>
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
                                                        <h2 class="rota_count" id="assignedClientTo">Not assigned</h2>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="assignedCarer m-t-15">
                                                <label>Assigned Carer</label>
                                                <div class="">
                                                    <div class="dashedBorder" id="assignedCarerBlankSection">
                                                        <div class="leavebanktabCont">
                                                            <i class='bx bx-home-alt'></i>
                                                            <p>Select assignment first to see carer suggestions</p>
                                                        </div>
                                                    </div>

                                                    <div class="">
                                                        <div class="suggestedCarers">
                                                          <div id="suggested_carer"></div>

                                                            <div id="carerSelect">
                                                                
                                                                <!-- Carer cards injected here by JS -->
                                                          
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="actions">
                                    <button type="button" class="cancel" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="submit">Create Carer</button>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const recurringClientToggle = document.getElementById('recurringClientToggle');
                const recurringClientDiv = document.getElementById('recurringClientDiv');

                if (!recurringClientToggle || !recurringClientDiv) return;

                // Set initial state (important on page load)
                recurringClientDiv.style.display = recurringClientToggle.checked ? 'block' : 'none';

                recurringClientToggle.addEventListener('change', function() {
                    recurringClientDiv.style.display = this.checked ? 'block' : 'none';
                });

            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const careType = document.getElementById('careType');
                const locationTab = document.getElementById('locationTab');
                const propertyTab = document.getElementById('propertyTab');

                function toggleTabs() {
                    if (careType.value == '1') {

                        // Disable Location & Property
                        locationTab.classList.add('disabled');
                        propertyTab.classList.add('disabled');

                        locationTab.disabled = true;
                        propertyTab.disabled = true;

                    } else {

                        // Enable all
                        locationTab.classList.remove('disabled');
                        propertyTab.classList.remove('disabled');

                        locationTab.disabled = false;
                        propertyTab.disabled = false;
                    }
                }

                // Run on page load
                toggleTabs();

                // Run on change
                careType.addEventListener('change', toggleTabs);
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const frequencySelect = document.querySelector('select[name="frequency"]');
                const daysWrapper = document.querySelector('.weeklyDaysSelect').closest('.col-md-12');

                function toggleWeeklyDays() {
                    if (frequencySelect.value === 'weekly') {
                        daysWrapper.style.display = 'block';
                    } else {
                        daysWrapper.style.display = 'none';
                    }
                }

                // Run on page load
                toggleWeeklyDays();

                // Run on change
                frequencySelect.addEventListener('change', toggleWeeklyDays);
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const days = document.querySelectorAll('.weeklyDaysSelect span');
                const hiddenInput = document.getElementById('week_days');

                function updateSelectedDays() {
                    const selectedDays = [];
                    document.querySelectorAll('.weeklyDaysSelect span.active')
                        .forEach(day => selectedDays.push(day.innerText));

                    hiddenInput.value = selectedDays.join(',');
                }

                days.forEach(day => {
                    day.addEventListener('click', function() {
                        this.classList.toggle('active');
                        updateSelectedDays();
                    });
                });

                // Set initial value (in case Sun is preselected)
                updateSelectedDays();
            });
        </script>

        <script>
            const attach_document = document.getElementById('attach_document');
            const close_document = document.getElementById('close_document');
            const documentContent = document.getElementById('documentContent');

            attach_document.addEventListener('click', function() {
                documentContent.style.display = 'block';
                attach_document.style.display = 'none';
                close_document.style.display = 'inline-block';
                document.querySelector('.upload-box').style.display = 'block';
            });

            close_document.addEventListener('click', function() {
                documentContent.style.display = 'none';
                close_document.style.display = 'none';
                attach_document.style.display = 'inline-block';
            });
        </script>
        <script>
            document.getElementById('systemSearch').addEventListener('keyup', function() {
                let searchValue = this.value.toLowerCase();
                let systemLists = document.querySelectorAll('.addSystemList .systemList');
                let noResults = document.getElementById('noResults');
                let hasVisible = false;

                systemLists.forEach(function(item) {
                    let titleText = item.querySelector('.helthcareText p').innerText.toLowerCase();

                    if (titleText.includes(searchValue)) {
                        item.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        item.style.display = 'none';
                    }
                });

                noResults.style.display = hasVisible ? 'none' : 'block';
            });
        </script>



        <script>
            document.addEventListener('click', function(e) {
                if (e.target.closest('.addFormItem')) {
                    let clickedItem = e.target.closest('.addFormItem');

                    let title = clickedItem.querySelector('.helthcareText p').innerText;
                    let today = new Date().toISOString().split('T')[0];

                    let newSection = `
                            <div class="card pendingCard">
                                <div class="left">
                                    <div class="icon blueText"><i class='bx bx-file'></i></div>
                                    <div class="info">
                                        <div class="title">${title}</div>
                                        <div class="meta">
                                            <div class="inactive roundTag">${title}</div>
                                            <span class="date">${today}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="actions">
                                    <span class="approve"><i class='bx bx-check-circle'></i></span>
                                    <span class="delete"><i class='bx bx-trash'></i></span>
                                </div>
                            </div>
                        `;

                    document.getElementById('pendingCompletionSection').style.display = 'block';
                    document.getElementById('attachDocumentSection').style.display = 'none';
                    document.getElementById('pendingCompletion').insertAdjacentHTML('beforeend', newSection);

                    updatePendingCount();
                    document.getElementById('close_document').style.display = 'none';
                    document.querySelector('.upload-box').style.display = 'none';
                    document.getElementById('attach_document').style.display = 'inline-block';

                }
            });

            function updatePendingCount() {
                let count = document.querySelectorAll('#pendingCompletion .pendingCard').length;
                document.getElementById('pendingHeader').textContent = 'Pending Completion (' + count + ')';
            }
        </script>

        <script>
            document.querySelector('.upload-btn').addEventListener('click', function() {
                document.getElementById('imageUpload').click();
            });

            document.getElementById('imageUpload').addEventListener('change', function() {
                if (this.files.length === 0) return;

                const file = this.files[0];
                const fileName = file.name;
                const today = new Date().toISOString().split('T')[0];

                let newSection = `
                                <div class="card pendingCard">
                                    <div class="left">
                                        <div class="icon blueText">
                                            <i class='bx bx-file'></i>
                                        </div>
                                        <div class="info">
                                            <div class="title">${fileName}</div>
                                            <div class="meta">
                                                <div class="inactive roundTag">Attachment</div>
                                                <span class="date">${today}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="actions">
                                        <span class="approve"><i class='bx bx-check-circle'></i></span>
                                        <span class="delete"><i class='bx bx-trash'></i></span>
                                    </div>
                                </div>
                            `;

                document.getElementById('pendingCompletionSection').style.display = 'block';
                document.getElementById('attachDocumentSection').style.display = 'none';
                document.getElementById('pendingCompletion').insertAdjacentHTML('beforeend', newSection);

                updatePendingCount();

                document.getElementById('close_document').style.display = 'none';
                document.querySelector('.upload-box').style.display = 'none';
                document.getElementById('attach_document').style.display = 'inline-block';

                // reset input so same file can be selected again
                this.value = '';
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const assessmentBtn = document.querySelector('.assessment-upload-btn');
                const assessmentInput = document.getElementById('assessmentUpload');

                if (assessmentBtn && assessmentInput) {

                    // Open file picker
                    assessmentBtn.addEventListener('click', function() {
                        assessmentInput.click();
                    });

                    // Handle selected file
                    assessmentInput.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            console.log('Selected file:', this.files[0]);

                            // future use:
                            // - preview
                            // - AJAX upload
                            // - append to pending section
                        }
                    });
                }

            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const clientSelect = document.getElementById('clientSelect');
                const carerContainer = document.getElementById('carerSelect'); // it's now a DIV

                clientSelect.addEventListener('change', function() {
                    const clientId = this.value;
                    const heading = document.getElementById('assignedClientTo');
                    
                    if (this.value === "") {
                        heading.textContent = "Not assigned";
                        return;
                    }
                    
                    let selectedText = this.options[this.selectedIndex].text;
                    heading.textContent = selectedText;
                    
                    console.log("clientId", clientId);
                    carerContainer.innerHTML = "Loading carers...";

                    if (!clientId) {
                        carerContainer.innerHTML = "";
                        return;
                    }

                    fetch("{{ route('carer.shift.staff', ':id') }}".replace(':id', clientId))
                        .then(response => response.json())
                        .then(res => {
                            document.getElementById('assignedCarerBlankSection').style.display = 'none';
                            carerContainer.innerHTML = ''; // clear old cards

                            if (res.status && res.data.length > 0) {
                                const suggested_carer = document.getElementById('suggested_carer');
                                suggested_carer.innerHTML = '<h4 class="title">Suggested Carers (Ranked by compatibility):</h4>';
                                console.log("res.data", res.data);
                                document.getElementById('assessment_card').style.display = 'block';
                                res.data.forEach(carer => {

                                    let firstLetter = carer.name.charAt(0).toUpperCase();
                                    
                                    // Ensure distance is treated as a number
                                    let dist = parseFloat(carer.distance);
                                    let cardHtml = '';

                                    if (dist < 20) {
                                        // GREEN CARD (< 20km)
                                        cardHtml = `
                                            <div class="carerCard greenCarerCard">
                                                <div class="avatar">${firstLetter}</div>
                                                <div class="details">
                                                    <div class="topRow">
                                                        <div><span class="name">${carer.name}</span></div>
                                                        <div><span class="badge">${carer.postcode ?? ''}</span></div>
                                                        <div><span class="careBadg darkGreenBadges">Best Match</span></div>
                                                    </div>
                                                    <span class="badge">${carer.tag ?? 'Score'}</span>
                                                    <div class="d-flex gap-2 mt-2">
                                                        <p class="fs13 darkGreenTextp mb-0"><i class='bx bx-check-circle me-1'></i> Within ${dist.toFixed(1)} km</p>
                                                        <p class="fs13 darkGreenTextp mb-0"><i class='bx bx-check-circle me-1'></i> ${carer.qualifications_count ?? '0'} Qualifications</p>
                                                    </div>
                                                </div>
                                                <button class="bgBtn assignBtn" data-id="${carer.id}">Assign</button>
                                            </div>`;
                                            
                                    } else if (dist === 20) {
                                        // WHITE / MUTE CARD (== 20km)
                                        cardHtml = `
                                            <div class="carerCard muteCarerCard">
                                                <div class="avatar">${firstLetter}</div>
                                                <div class="details">
                                                    <div class="topRow">
                                                        <div><span class="name">${carer.name}</span></div>
                                                        <div><span class="badge">${carer.postcode ?? ''}</span></div>
                                                    </div>
                                                    <span class="badge">${carer.tag ?? 'Standard Match'}</span>
                                                    <div class="d-flex gap-2 mt-2">
                                                        <p class="fs13 darkGreenTextp mb-0"><i class='bx bx-check-circle me-1'></i> 20 km away</p>
                                                        <p class="fs13 darkGreenTextp mb-0"><i class='bx bx-check-circle me-1'></i> ${carer.qualifications_count ?? '0'} Qualifications</p>
                                                    </div>
                                                </div>
                                                <button class="bgBtn assignBtn" data-id="${carer.id}">Assign</button>
                                            </div>`;

                                    } else {
                                        // RED CARD (> 20km or Default)
                                        cardHtml = `
                                            <div class="carerCard">
                                                <div class="avatar">${firstLetter}</div>
                                                <div class="details">
                                                    <div class="topRow">
                                                        <span class="name">${carer.name}</span>
                                                        <span class="badge">${carer.postcode ?? ''}</span>
                                                    </div>
                                                    <span class="tag">${carer.tag ?? 'Geographic Mismatch'}</span>
                                                    <div class="warning">
                                                        <i class='bx bx-alert-circle'></i>
                                                        <i class='bx bx-alert-triangle'></i>
                                                        ${carer.warning ?? 'Very far from client'}
                                                    </div>
                                                </div>
                                                <button class="assignBtn" data-id="${carer.id}">Assign</button>
                                            </div>`;
                                    }

                                    carerContainer.insertAdjacentHTML('beforeend', cardHtml);
                                });

                            } else {
                                carerContainer.innerHTML = '<p>No Carer Found</p>';
                            }
                        })
                        .catch(() => {
                            carerContainer.innerHTML = '<p>Failed to load carers</p>';
                        });
                });

                // Handle Assignment
                document.getElementById('carerSelect').addEventListener('click', function(e) {
                    if (e.target.classList.contains('assignBtn')) {
                        e.preventDefault();
                        
                        // Get Carer ID
                        let carerId = e.target.getAttribute('data-id');
                        document.getElementById('selected_carer_id').value = carerId;
                        
                        // 1. Reset all cards
                        document.querySelectorAll('.carerCard').forEach(card => {
                            card.style.border = "1px solid #e5e7eb";
                            let btn = card.querySelector('.assignBtn');
                            if(btn) {
                                btn.innerText = "Assign"; 
                                btn.classList.remove('btn-success');
                            }
                        });

                        // 2. Highlight selected card
                        let selectedCard = e.target.closest('.carerCard');
                        selectedCard.style.border = "2px solid #22c55e"; // Green border
                        
                        let btn = e.target;
                        btn.innerText = "Assigned";
                        btn.classList.add('btn-success');
                        
                        // 3. Update "Assigned To" text
                        let carerName = selectedCard.querySelector('.name').innerText;
                        let assignedTitle = document.getElementById('assignedClientTo');
                        if(assignedTitle) assignedTitle.innerText = carerName;

                        // 4. Update the dashed placeholder
                        let placeholder = document.getElementById('assignedCarerBlankSection');
                        if(placeholder) {
                            placeholder.innerHTML = `
                                <div style="text-align:center; padding: 20px;">
                                    <div class="avatar" style="width:50px;height:50px;border-radius:50%;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;margin:0 auto 10px;">
                                        ${carerName.charAt(0)}
                                    </div>
                                    <h4 style="margin:0;font-weight:600">${carerName}</h4>
                                    <span class="badge" style="background:#dcfce7;color:#166534;margin-top:5px">Assigned</span>
                                </div>
                            `;
                            placeholder.style.border = "1px solid #d1fae5";
                            placeholder.style.background = "#fff";
                        }
                    }
                });

                // Form Validation
                document.getElementById('createShiftForm').addEventListener('submit', function(e) {
                    let carerId = document.getElementById('selected_carer_id').value;
                    if(!carerId) {
                        e.preventDefault();
                        alert('Please assign a carer before creating the shift.');
                    }
                });
            });
        </script>



    @endsection
</main>
