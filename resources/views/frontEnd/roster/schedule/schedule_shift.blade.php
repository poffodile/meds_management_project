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

    /* .pendingCompletionSection {
        display: none;
    } */

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
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        /* display: none; */
    }

    .assessment-header {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 20px;
    }

    .assessment-header .doc-icon {
        font-size: 24px;
        color: #7c3aed;
        padding: 10px;
        background: #f5f3ff;
        border-radius: 8px;
    }

    .assessment-header h4 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .assessment-header p {
        margin: 5px 0 0;
        font-size: 14px;
        color: #64748b;
    }

    .assessment-list {
        margin-bottom: 15px;
    }

    .assessment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fdfaff;
        border: 1px solid #f3ebff;
        border-radius: 10px;
        padding: 12px 15px;
        margin-bottom: 10px;
        transition: all 0.2s;
    }

    .assessment-item:hover {
        border-color: #d8b4fe;
        box-shadow: 0 2px 4px rgba(124, 58, 237, 0.05);
    }

    .assessment-item-left {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow: hidden;
        flex: 1;
    }

    .assessment-item-left i {
        color: #7c3aed;
        font-size: 18px;
    }

    .assessment-item-name {
        font-size: 14px;
        font-weight: 500;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .assessment-item-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .assessment-type-select {
        padding: 4px 8px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
        color: #475569;
        background: #fff;
        outline: none;
    }

    .assessment-item-delete {
        color: #ef4444;
        background: none;
        border: none;
        padding: 5px;
        font-size: 16px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .assessment-item-delete:hover {
        color: #b91c1c;
    }

    .assessment-upload-btn {
        width: 100%;
        padding: 15px;
        background: #fff;
        border: 2px dashed #d8b4fe;
        border-radius: 10px;
        color: #7c3aed;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .assessment-upload-btn:hover {
        background: #f5f3ff;
        border-color: #7c3aed;
    }

    /* Carer Selection Styles */
    .carerCard {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 15px;
        position: relative;
        transition: all 0.2s;
    }

    .carerCard:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .carerCard .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #475569;
        flex-shrink: 0;
    }

    .carerCard .details {
        flex: 1;
        min-width: 0;
    }

    .carerCard .topRow {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .carerCard .name {
        font-weight: 600;
        color: #1e293b;
        font-size: 15px;
        display: block;
    }

    .carerCard .badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 500;
    }

    .carerCard .assignBtn {
        padding: 6px 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .carerCard .assignBtn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .carerCard .assignBtn.assigned {
        background: #22c55e;
        color: #fff;
        border-color: #22c55e;
    }

    /* Green Card (< 20km) */
    .greenCarerCard {
        background: #f0fdf4;
        border-color: #bcf0da;
    }

    .greenCarerCard .avatar {
        background: #dcfce7;
        color: #166534;
    }

    .darkGreenBadges {
        background: #166534 !important;
        color: #fff !important;
    }

    /* Mute Card (== 20km) - White by default but could have subtle diff */
    .muteCarerCard {
        border-style: solid;
    }

    /* Red Card / Geographic Mismatch (> 50km) */
    .geographic-mismatch {
        background: #fff1f2;
        border-color: #fecaca;
    }

    .geographic-mismatch .avatar {
        background: #ffe4e6;
        color: #991b1b;
    }

    /* Best Match Badge */
    .best-match::after {
        content: 'Best Match';
        position: absolute;
        top: -10px;
        right: 15px;
        background: #7c3aed;
        color: #fff;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
        display: none;
        /* We use a inline badge now, but keeping for reference */
    }

    /* Selected Carer Card */
    .carer-selection-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        margin-bottom: 15px;
    }

    .carer-selection-card p {
        font-size: 13px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .carer-selection-card h3 {
        color: #1e293b;
        font-weight: 700;
    }

    /* Suggested Carer List Scroller */
    #suggested_carer {
        max-height: 480px;
        overflow-y: auto;
        padding-right: 8px;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f1f5f9;
    }

    #suggested_carer::-webkit-scrollbar {
        width: 6px;
    }

    #suggested_carer::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    #suggested_carer::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    #suggested_carer::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
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
                <!-- <button class="filterBtn activeDot"><span class="dot"></span> All Active </button>
                <button class="filterBtn"> 📅 Today </button>
                <button class="filterBtn"> 📆 This Week </button>
                <button class="filterBtn"> ⭐ Saved Views </button>
                <button class="filterBtn"> ⬇ Export </button>
                <button class="filterBtn highlight"> ✨ AI Generate </button>
                <button class="filterBtn"> 🛠 Smart Allocate </button> -->
                <button class="filterBtn lightGreen" data-toggle="modal" data-target="#recurringShiftModal"> 🔁 Recurring </button>
                <button class="btn allBtnUseColor" data-toggle="modal" data-target="#addShiftModal">+ Add Shift</button>
            </div>

        </div>

        <!-- Alerts -->
        <div class="rota_alerts">
            @forelse ($scheduled_shifts as $shift)
            <div class="rota_alert {{ $loop->index >= 3 ? 'extra-shift' : '' }}" style="{{ $loop->index >= 3 ? 'display: none;' : '' }}">
                <div class="rota_alert-icon"><i class="fa fa fa-calendar-o"></i></div>
                <div class="rota_alert-content">
                    <div class="rota_alert-title">{{ $shift->client_name ?? 'Unknown Client' }} - {{ date('H:i', strtotime($shift->start_time)) }}</div>
                    <div class="rota_alert-description">
                        Assigned to: {{ $shift->staff_name ?? 'Unassigned' }} |
                        Shift Type: {{ ucfirst($shift->shift_type) }} |
                        Date: {{ date('M d, Y', strtotime($shift->start_date)) }}
                    </div>
                    <div class="rota_alert-bottmDescription">
                        <i class="fa fa-clock-o"></i> {{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }}
                        @if($shift->notes)
                        <br><i class="fa fa-info-circle"></i> {{ $shift->notes }}
                        @endif
                    </div>
                </div>
                <div class="rota_alert-badge">high</div>
            </div>
            @empty
            <div class="rota_alert">
                <div class="rota_alert-content">
                    <div class="rota_alert-title">No shifts found</div>
                </div>
            </div>
            @endforelse

            <div class="rota_view-all" onclick="toggleShifts()" style="cursor: pointer;">View All ({{ count($scheduled_shifts) }}) →</div>

            <script>
                function toggleShifts() {
                    const hiddenShifts = document.querySelectorAll('.extra-shift');
                    const btn = document.querySelector('.rota_view-all');
                    let isHidden = false;

                    // Check status of first hidden element to decide action
                    if (hiddenShifts.length > 0) {
                        isHidden = hiddenShifts[0].style.display === 'none';
                    }

                    hiddenShifts.forEach(shift => {
                        shift.style.display = isHidden ? '' : 'none';
                    });

                    if (isHidden) {
                        btn.innerHTML = 'Show Less ↑';
                    } else {
                        btn.innerHTML = 'View All ({{ count($scheduled_shifts) }}) →';
                    }
                }
            </script>
        </div>

        <!-- Smart Suggestions -->
        <!-- <div class="suggestions">
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
        </div> -->
        <!-- Smart Suggestions -->

        <!-- advancedFiltersBox -->

        <!-- <div class="advancedFiltersBox m-b-15">
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
        </div> -->
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

            <!-- TAB CONTENT -->
            <div class="tab-content">
                <div class="content active" id="roster">
                    @php
                    $rosterTotalShifts = $scheduled_shifts->count();
                    $rosterOpenShifts = 0;
                    $rosterTotalHours = 0;

                    foreach($scheduled_shifts as $shift) {
                    // Check for unfilled condition logically exactly like we did in the API
                    $is_unfilled = ($shift->status == 'unfilled' || $shift->status == 'open' || empty($shift->staff_id));
                    if ($is_unfilled) {
                    $rosterOpenShifts++;
                    }

                    if($shift->start_time && $shift->end_time) {
                    $start_time = \Carbon\Carbon::parse($shift->start_time);
                    $end_time = \Carbon\Carbon::parse($shift->end_time);
                    $rosterTotalHours += $start_time->diffInHours($end_time);
                    }
                    }
                    $rosterFilledShifts = $rosterTotalShifts - $rosterOpenShifts;
                    @endphp
                    <!-- Top Blue Bar -->
                    <div class="roster-top">
                        <div class="title">
                            <h2 class="h2-color">Care Home</h2> <span>Shift Roster</span>
                        </div>
                        <div class="stats">
                            <div class="stat"> <strong>{{ $rosterTotalShifts }}</strong> <small>Total Shifts</small> </div>
                            <div class="divider"></div>
                            <div class="stat filled"> <strong>{{ $rosterFilledShifts }}</strong> <small>Filled</small> </div>
                            <div class="divider"></div>
                            <div class="stat open"> <strong>{{ $rosterOpenShifts }}</strong> <small>Open</small> </div>
                            <div class="divider"></div>
                            <div class="stat hours"> <strong>{{ round($rosterTotalHours) }}h</strong> <small>Hours</small> </div>
                        </div>
                    </div>

                    <!-- Filters Row -->
                    <!-- <div class="roster-filters">
                        <div class="left">
                            <select>
                                <option selected>Resources</option>
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
                    </div> -->
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

                        <!-- <div class="right">
                            <button class="outline">Bulk Actions</button>
                            <button class="primary">👥 Staff</button>
                            <button>📍 Locations</button>
                            <button>👤 Clients</button>
                            <button>⇄ Split</button>
                        </div> -->
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
                    <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <button id="dayPrevBtn" class="btn borderBtn" style="padding:6px 12px;font-size:18px;">‹</button>
                        <div style="text-align:center;">
                            <h2 id="dayDateDisplay" style="margin:0;font-size:20px;font-weight:700;color:#111827;">Loading...</h2>
                            <p id="dayShiftsCount" style="margin:0;color:#6b7280;font-size:13px;margin-top:2px;">0 shifts scheduled</p>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button id="dayTodayBtn" class="btn borderBtn" style="padding:6px 16px;">Today</button>
                            <button id="dayNextBtn" class="btn borderBtn" style="padding:6px 12px;font-size:18px;">›</button>
                        </div>
                    </div>

                    <div id="dayShiftsList" style="display:flex;flex-direction:column;gap:12px;">
                        <!-- JS populated shifts will inject here -->
                        <div style="text-align:center;padding:24px;color:#9ca3af;">Loading shifts...</div>
                    </div>
                </div>

                <div class="content" id="week">
                    <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:12px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <button id="weekPrevBtn" class="btn borderBtn" style="padding:6px 16px;">‹ Previous Week</button>
                        <div style="text-align:center;">
                            <h2 id="weekDateDisplay" style="margin:0;font-size:18px;font-weight:700;color:#111827;">Loading...</h2>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button id="weekTodayBtn" class="btn borderBtn" style="padding:6px 16px;">This Week</button>
                            <button id="weekNextBtn" class="btn borderBtn" style="padding:6px 16px;">Next Week ›</button>
                        </div>
                    </div>

                    <div id="weekShiftsList" style="display:grid;grid-template-columns:repeat(7, 1fr);gap:8px;overflow-x:auto;padding-bottom:12px;">
                        <!-- Columns will be injected here -->
                        <div style="grid-column: span 7;text-align:center;padding:24px;color:#9ca3af;">Loading week data...</div>
                    </div>
                </div>

                <div class="content" id="month">
                    <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:24px;">

                        <!-- Custom Header for Month View -->
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                            <button id="monthPrevBtn" class="btn borderBtn" style="padding:6px 16px;">‹</button>
                            <h2 id="monthDateDisplay" style="margin:0;font-size:24px;font-weight:700;color:#111827;">Loading...</h2>
                            <div style="display:flex;gap:8px;">
                                <button id="monthTodayBtn" class="btn borderBtn" style="padding:6px 16px;">Today</button>
                                <button id="monthNextBtn" class="btn borderBtn" style="padding:6px 16px;">›</button>
                            </div>
                        </div>

                        <!-- Calendar Grid -->
                        <div id="monthCalendar"></div>

                        <!-- Legend -->
                        <div style="margin-top: 24px; font-size: 13px;">
                            <strong style="color:#374151;display:block;margin-bottom:8px;">Legend:</strong>
                            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#22c55e;"></span> Completed</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#eab308;"></span> In Progress</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#a855f7;"></span> Scheduled</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#3b82f6;"></span> Published</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#f97316;"></span> Unfilled</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#ef4444;"></span> Cancelled</div>
                                <div style="display:flex;align-items:center;gap:4px;"><span style="width:12px;height:12px;border-radius:2px;background:#9ca3af;"></span> Draft</div>
                            </div>
                        </div>
                    </div>
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
                                    <h2 class="rota_count" id="totalShifts">37</h2>
                                </div>
                            </div>

                            <div class="rota_dash-card orangeClr">
                                <div class="rota_dash-left">
                                    <p class="rota_title">Filled</p>
                                    <h2 class="rota_count greenText" id="filledShifts">36</h2>
                                </div>
                            </div>

                            <div class="rota_dash-card green">
                                <div class="rota_dash-left">
                                    <p class="rota_title">Unfilled</p>
                                    <h2 class="rota_count orangeText" id="unfilledShifts">0</h2>
                                </div>
                            </div>

                            <div class="rota_dash-card redClr">
                                <div class="rota_dash-left">
                                    <p class="rota_title">Fill Rate</p>
                                    <h2 class="rota_count blueText" id="fillRate">1</h2>
                                </div>
                            </div>
                        </div>

                        <div class="carePlanWrapper proactiveSuggestionsWrap weeklyBreakdownAddCont" id="weeklyContainer">
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
                            @forelse($scheduled_shifts as $shift)
                            @php
                            $start = \Carbon\Carbon::parse($shift->start_time);
                            $end = \Carbon\Carbon::parse($shift->end_time);
                            $duration = $start->diffInHours($end);
                            @endphp
                            <div class="col-md-4 {{ $loop->index >= 3 ? 'm-t-25' : '' }}">
                                <div class="profile-card careTasksCard mb-0">
                                    <div class="details mt-0">
                                        <div class="item">
                                            <i class="bx  bx-clock"></i><span><strong> {{ date('D, M d', strtotime($shift->start_date)) }} </strong> </span> •
                                            <span>{{ date('H:i', strtotime($shift->start_time)) }} - {{ date('H:i', strtotime($shift->end_time)) }} ({{ $duration }}h)</span>
                                        </div>
                                    </div>
                                    <div class="sectionCarer">
                                        <div class="tags">
                                            <span class="yellow">{{ $shift->status ?? 'unfilled' }}</span>
                                            <span class="inactive">{{ ucfirst($shift->shift_type) }}</span>
                                            <!-- <span class="inactive">residential care</span> -->
                                        </div>
                                    </div>
                                    <div class="details">
                                        <div class="item">
                                            <span class="greenText"><i class='bx  bx-home-alt-2'></i> </span>
                                            <span><strong> {{ $shift->location_name ?? 'East Wing' }} </strong> </span>
                                        </div>
                                        <div class="item redalrttext">
                                            <i class='bx  bx-user'></i> <span>{{ $shift->staff_name ?? 'Unassigned' }}</span>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="actions">
                                        <button class="borderBtn edit day-shift-item"
                                            data-id="{{ $shift->id }}"
                                            data-client="{{ $shift->service_user_id ?? '' }}"
                                            data-property="{{ $shift->property_id ?? '' }}"
                                            data-location="{{ $shift->location_name ?? '' }}"
                                            data-address="{{ $shift->location_address ?? '' }}"
                                            data-date="{{ $shift->start_date ?? '' }}"
                                            data-start="{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}"
                                            data-end="{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}"
                                            data-staff="{{ $shift->staff_id ?? '' }}"
                                            data-type="{{ $shift->shift_type ?? '' }}"
                                            data-care="{{ $shift->care_type_id ?? '' }}"
                                            data-assignment="{{ $shift->assignment ?? '' }}"
                                            data-homearea="{{ $shift->home_area_id ?? '' }}"
                                            data-notes="{{ $shift->notes ?? '' }}"
                                            data-tasks="{{ $shift->tasks ?? '' }}">
                                            <i class='bx bx-edit'></i> Edit
                                        </button>
                                        <button class="borderBtn delete" data-id="{{ $shift->id }}"> <i class='bx  bx-trash'></i></button>
                                        <button class="borderBtn edit" data-id="{{ $shift->id }}"> <i class='bx  bx-paper-plane'></i> Request </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-md-12">
                                <p>No scheduled shifts found.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>


                <div class="content" id="bygroup">

                </div>

                <div class="content" id="split">
                    <!-- Split View: Navigation Bar -->
                    <div class="sv-nav-bar">
                        <button class="sv-nav-btn" id="sv-prev-week">
                            <i class="bx bx-chevron-left"></i> Previous Week
                        </button>
                        <div class="sv-week-title" id="sv-week-title">Loading...</div>
                        <div class="sv-nav-right">
                            <button class="sv-nav-btn sv-nav-today" id="sv-today">This Week</button>
                            <button class="sv-nav-btn" id="sv-next-week">
                                Next Week <i class="bx bx-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Split View: Main Body -->
                    <div class="sv-body">
                        <!-- Left Panel: Care Team -->
                        <div class="sv-left-panel">
                            <div class="sv-team-header">
                                <div class="sv-team-title">Care Team</div>
                                <div class="sv-team-subtitle" id="sv-team-count">0 active carers</div>
                            </div>
                            <div class="sv-team-list" id="sv-team-list">
                                <!-- Staff cards populated by JS -->
                            </div>
                        </div>

                        <!-- Right Panel: Timeline -->
                        <div class="sv-right-panel" id="sv-right-panel">
                            <!-- Day headers -->
                            <div class="sv-timeline-wrapper">
                                <div class="sv-day-header-row" id="sv-day-header-row">
                                    <!-- Day columns populated by JS -->
                                </div>
                                <!-- Timeline rows for each staff -->
                                <div class="sv-rows-container" id="sv-rows-container">
                                    <!-- Rows populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading overlay -->
                    <div class="sv-loading" id="sv-loading">
                        <div class="sv-spinner"></div>
                        <span>Loading shifts...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- add Shift Schedule Modal -->
    <div class="modal fade leaveCommunStyle" id="addShiftModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog newShiftModal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"> Create New Shift</h4>
                </div>
                <div class="modal-body approveLeaveModal heightScrollModal">
                    <div class="carer-form createNewShiftTabBtn">
                        <form id="createShiftForm" action="{{ route('roster.schedule.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="shift_id" id="edit_shift_id">
                            <input type="hidden" name="carer_id" id="selected_carer_id">
                            <input type="hidden" name="form_id" id="selected_form_id">
                            <input type="hidden" name="form_name" id="selected_form_name">
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
                                        <input type="hidden" name="assignment" id="selected_assignment" value="Client">
                                        <div class="tabs p-2 m-b-10" style="background-color: #f5f5f5;">
                                            <button type="button" class="tab" id="locationTab" data-tab="scheduleLocation">
                                                <i class='bx  bx-location'></i> Location
                                            </button>

                                            <button type="button" class="tab active" id="clientTab" data-tab="scheduleClient">
                                                <i class='bx  bx-user'></i> Client
                                            </button>
                                        </div>

                                        <!-- TAB CONTENT -->
                                        <div class="tab-content carertabcontent">
                                            {{-- Locaton Section --}}
                                            <div class="content" id="scheduleLocation">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <input type="text" name="location_address" value="{{ $home_title }}" id="" class="form-control" placeholder="Address">
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <select class="form-control" id="homeAreaSelect" name="home_area_id">
                                                            <option value="">Select Home Area</option>
                                                            @foreach ($home_areas as $area)
                                                            <option value="{{ $area->id }}"> {{ $area->name }} </option>
                                                            @endforeach
                                                        </select>
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

                                        </div>
                                        <div class="row m-t-10">
                                            <div class="col-md-12">
                                                <label>Date *</label>
                                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6 m-t-10">
                                                <label>Start Time *</label>
                                                <input type="time" name="start_time" class="form-control" value="09:00" required>
                                            </div>
                                            <div class="col-md-6 m-t-10">
                                                <label>End Time *</label>
                                                <input type="time" name="end_time" class="form-control" value="17:00" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12  m-t-10">
                                                <label>Shift Type</label>
                                                <select class="form-control" name="shift_type">
                                                    <option value="morning">Morning</option>
                                                    <option value="afternoon">Afternoon</option>
                                                    <option value="evening">Evening</option>
                                                    <option value="day_shift">Day Shift</option>
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
                                                                                    <div class="systemList addFormItem" data-form-id="{{ $form['id'] }}">
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

                                                    <!-- Container for uploaded assessment documents -->
                                                    <div id="assessmentList" class="assessment-list">
                                                        <!-- Dynamic items will be added here -->
                                                    </div>

                                                    <div class="upload-box">
                                                        <button type="button" class="assessment-upload-btn">
                                                            <i class="fa fa-upload"></i>
                                                            Upload Assessment Document
                                                        </button>
                                                        <input type="file" id="assessmentUpload" name="assessment_doc_files[]" accept=".pdf,.doc,.docx" style="display:none;" multiple>
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
                                            <div class="assigned-carer-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                                <label style="margin: 0;">Assigned Carer</label>
                                                <button type="button" id="toggleSuggestionsBtn" class="btn btn-xs btn-white" style="display: none;">Hide Suggestions</button>
                                            </div>

                                            <!-- Selected Carer Card -->
                                            <div id="selectedCarerCard" class="carer-selection-card" style="display: none;">
                                                <p>Currently Assigned:</p>
                                                <h3 id="selectedCarerName" class="m-t-5 m-b-10"></h3>
                                                <button type="button" id="changeCarerBtn" class="btn btn-sm btn-white">Change Carer</button>
                                            </div>

                                            <div id="carerSuggestionsWrapper">
                                                <div class="dashedBorder" id="assignedCarerBlankSection">
                                                    <div class="leavebanktabCont">
                                                        <i class='bx bx-home-alt'></i>
                                                        <p>Select assignment first to see carer suggestions</p>
                                                    </div>
                                                </div>

                                                <div id="suggested_carer_container" style="display: none;">
                                                    <p style="margin-bottom: 12px; font-size: 14px; color: #475569;">Suggested Carers (Ranked by compatibility):</p>
                                                    <div id="suggested_carer">
                                                        <!-- Carer cards injected here by JS -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="actions">
                                <button type="button" class="cancel" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="submit">Create Shift</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Create Recurring Shifts Modal -->
    <div class="modal fade recurring-modal" id="recurringShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="header-icon"><i class='bx bx-refresh'></i></div>
                    <h4 class="modal-title">Create Recurring Shifts <span class="step-badge">Step 1 of 3</span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 class="section-title">Shift Details</h5>
                    <form id="recurringShiftForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client *</label>
                                    <select class="form-control" name="client_id" required>
                                        <option value="">Select client</option>
                                        @foreach ($service_users as $service_user)
                                        <option value="{{ $service_user->id }}">{{ $service_user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Carer (Optional)</label>
                                    <select class="form-control" name="staff_id">
                                        <option value="">Unassigned</option>
                                        <!-- Carers will be loaded dynamically if needed -->
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Time *</label>
                                    <div class="input-with-icon">
                                        <input type="time" name="start_time" class="form-control" value="09:00" required>
                                        <i class='bx bx-time'></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>End Time *</label>
                                    <div class="input-with-icon">
                                        <input type="time" name="end_time" class="form-control" value="17:00" required>
                                        <i class='bx bx-time'></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Shift Type</label>
                                    <select class="form-control" name="shift_type">
                                        <option value="morning">Morning</option>
                                        <option value="afternoon">Afternoon</option>
                                        <option value="evening">Evening</option>
                                        <option value="night">Night</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Duration (hours)</label>
                                    <input type="text" class="form-control duration-readonly" value="8" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tasks</label>
                            <div class="task-input-group">
                                <input type="text" class="form-control" placeholder="Add a task...">
                                <button type="button" class="btn-add-task">Add</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions or notes..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-next active">Next: Recurrence Pattern</button>
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

                // Update hidden assignment field
                const assignmentInput = document.getElementById("selected_assignment");
                if (assignmentInput && tab.innerText) {
                    assignmentInput.value = tab.innerText.trim();
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
        // $(document).ready(function() {
        //     $('#calendar').fullCalendar({
        //         header: {
        //             left: 'prev,next today',
        //             center: 'title',
        //             right: 'agendaWeek,agendaDay'
        //         },

        //         editable: true,
        //         droppable: true,

        //         drop: function(date) {
        //             console.log('Dropped on ' + date.format());
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

    <!-- <script>
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
    </script> -->

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
                let formId = clickedItem.getAttribute('data-form-id');
                let title = clickedItem.querySelector('.helthcareText p').innerText;

                // Do not update the single hidden inputs anymore. We use arrays now inside the card.
                // document.getElementById('selected_form_id').value = formId;
                // document.getElementById('selected_form_name').value = title;

                let today = new Date().toISOString().split('T')[0];

                let newSection = `
                            <div class="card pendingCard">
                                <input type="hidden" name="form_ids[]" value="${formId}">
                                <input type="hidden" name="form_names[]" value="${title}">
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

            if (e.target.closest('.delete')) {
                let card = e.target.closest('.pendingCard');
                if (card) {
                    card.remove();
                    updatePendingCount();

                    // If no cards left, show the empty state again
                    if (document.querySelectorAll('#pendingCompletion .pendingCard').length === 0) {
                        document.getElementById('pendingCompletionSection').style.display = 'none';
                        document.getElementById('attachDocumentSection').style.display = 'block';
                        document.querySelector('.upload-box').style.display = 'block';
                        document.getElementById('attach_document').style.display = 'none';

                        // Clear hidden inputs for single selection case (legacy fallback)
                        document.getElementById('selected_form_id').value = '';
                        document.getElementById('selected_form_name').value = '';
                    }
                }
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

            const btn = document.querySelector('.upload-btn');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;

            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 1000);

            const file = this.files[0];
            const fileName = file.name;
            const today = new Date().toISOString().split('T')[0];

            let newSection = `<div class="card pendingCard">
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
            // this.value = '';
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const assessmentBtn = document.querySelector('.assessment-upload-btn');
            const assessmentInput = document.getElementById('assessmentUpload');
            const assessmentList = document.getElementById('assessmentList');

            if (assessmentBtn && assessmentInput) {
                assessmentBtn.addEventListener('click', function() {
                    assessmentInput.click();
                });

                assessmentInput.addEventListener('change', function() {
                    const files = Array.from(this.files);
                    if (files.length === 0) return;

                    const btn = document.querySelector('.assessment-upload-btn');
                    const originalHtml = btn.innerHTML;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
                    btn.disabled = true;

                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    }, 1000);

                    files.forEach((file, index) => {
                        const fileName = file.name;
                        const itemId = 'assessment-item-' + Date.now() + '-' + index;

                        const itemHtml = `
                                <div class="assessment-item" id="${itemId}">
                                    <div class="assessment-item-left">
                                        <i class="fa fa-file-text-o"></i>
                                        <span class="assessment-item-name" title="${fileName}">${fileName}</span>
                                    </div>
                                    <div class="assessment-item-right">
                                        <select class="assessment-type-select" name="assessment_types[]">
                                            <option value="other">Other</option>
                                            <option value="supervision">Supervision Form</option>
                                            <option value="care_plan">Care Plan</option>
                                            <option value="risk">Risk Assessment</option>
                                            <option value="medication">Medication Chart</option>
                                        </select>
                                        <button type="button" class="assessment-item-delete" title="Remove">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </div>
                                </div>
                            `;

                        assessmentList.insertAdjacentHTML('beforeend', itemHtml);

                        // Add delete listener
                        const newItem = document.getElementById(itemId);
                        newItem.querySelector('.assessment-item-delete').addEventListener('click', function() {
                            newItem.remove();
                        });
                    });
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const clientSelect = document.getElementById('clientSelect');
            const suggestedCarerContainer = document.getElementById('suggested_carer');
            const suggestionsWrapper = document.getElementById('carerSuggestionsWrapper');
            const suggestionsContainer = document.getElementById('suggested_carer_container');
            const blankSection = document.getElementById('assignedCarerBlankSection');
            const toggleBtn = document.getElementById('toggleSuggestionsBtn');
            const selectedCarerCard = document.getElementById('selectedCarerCard');
            const selectedCarerName = document.getElementById('selectedCarerName');
            const selectedCarerIdInput = document.getElementById('selected_carer_id');
            const assignedClientTo = document.getElementById('assignedClientTo');
            const changeCarerBtn = document.getElementById('changeCarerBtn');

            // Reset form when modal opens via data-toggle (new shift)
            $('#addShiftModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                if (button.length > 0) {
                    const form = $(this).find('#createShiftForm');
                    form.find('#edit_shift_id').val('');
                    $(this).find('.modal-title').text('Create New Shift');
                    form.find('button[type="submit"]').html('Create Shift');
                    form.attr('action', "{{ route('roster.schedule.store') }}");

                    // Reset other fields as needed
                    form.find('[name="client_id"]').val('').trigger('change');
                    $('#selected_carer_id').val('');
                    $('#selectedCarerCard').hide();
                    $('#carerSuggestionsWrapper').show();
                }
            });

            clientSelect.addEventListener('change', fetchSuggestedCarers);
            
            const locationTab = document.getElementById('locationTab');
            const propertyTab = document.getElementById('propertyTab');
            const homeAreaSelect = document.getElementById('homeAreaSelect');
            const propertySelect = document.querySelector('[name="property_id"]');

            if (locationTab) locationTab.addEventListener('click', fetchSuggestedCarers);
            if (propertyTab) propertyTab.addEventListener('click', fetchSuggestedCarers);
            if (homeAreaSelect) homeAreaSelect.addEventListener('change', fetchSuggestedCarers);
            if (propertySelect) propertySelect.addEventListener('change', fetchSuggestedCarers);

            // Fetch whenever date or time inputs change
            const form = clientSelect.closest('form');
            if (form) {
                const sDate = form.querySelector('input[name="start_date"]');
                const sTime = form.querySelector('input[name="start_time"]');
                const eTime = form.querySelector('input[name="end_time"]');

                if (sDate) sDate.addEventListener('change', fetchSuggestedCarers);
                if (sTime) sTime.addEventListener('change', fetchSuggestedCarers);
                if (eTime) eTime.addEventListener('change', fetchSuggestedCarers);
            }

            function fetchSuggestedCarers() {
                const assignment = document.getElementById("selected_assignment").value;
                let clientId = clientSelect.value;
                const form = clientSelect.closest('form');

                // Ensure the wrapper is visible so suggestions or blank section can be seen
                suggestionsWrapper.style.display = 'block';

                if (assignment === 'Client') {
                    if (clientId === "") {
                        assignedClientTo.textContent = "Not assigned";
                        blankSection.style.display = 'block';
                        suggestionsContainer.style.display = 'none';
                        toggleBtn.style.display = 'none';
                        if (selectedCarerCard) selectedCarerCard.style.display = 'none';
                        return;
                    }
                    let selectedText = clientSelect.options[clientSelect.selectedIndex].text;
                    assignedClientTo.textContent = selectedText;
                } else if (assignment === 'Location') {
                    clientId = '0';
                    const homeAreaVal = homeAreaSelect.value;
                    if (homeAreaVal) {
                        assignedClientTo.textContent = homeAreaSelect.options[homeAreaSelect.selectedIndex].text;
                    } else {
                        assignedClientTo.textContent = "Location (General)";
                    }
                } else if (assignment === 'Property') {
                    clientId = '0';
                    const propertyVal = propertySelect.value;
                    if (propertyVal) {
                        assignedClientTo.textContent = propertySelect.options[propertySelect.selectedIndex].text;
                    } else {
                        assignedClientTo.textContent = "Property (General)";
                    }
                }

                suggestedCarerContainer.innerHTML = "Loading carers...";
                blankSection.style.display = 'none';
                suggestionsContainer.style.display = 'block';
                // if (selectedCarerCard) selectedCarerCard.style.display = 'none';
                // selectedCarerIdInput.value = ""; // Don't reset if already editing/assigned

                let startDate = form ? form.querySelector('input[name="start_date"]').value : '';
                let startTime = form ? form.querySelector('input[name="start_time"]').value : '';
                let endTime = form ? form.querySelector('input[name="end_time"]').value : '';
                let shiftId = form ? form.querySelector('#edit_shift_id').value || form.querySelector('[name="shift_id"]')?.value : '';

                let fetchUrl = "{{ route('carer.shift.staff', ':id') }}".replace(':id', clientId);
                if (startDate && startTime && endTime) {
                    fetchUrl += `?start_date=${startDate}&start_time=${startTime}&end_time=${endTime}`;
                    if (shiftId) {
                        fetchUrl += `&shift_id=${shiftId}`;
                    }
                }

                fetch(fetchUrl)
                    .then(response => response.json())
                    .then(res => {
                        suggestedCarerContainer.innerHTML = "";

                        if (res.status && res.data.length > 0) {
                            toggleBtn.style.display = 'inline-block';
                            toggleBtn.innerText = 'Hide Suggestions';
                            document.getElementById('assessment_card').style.display = 'block';

                            res.data.forEach(carer => {
                                let firstLetter = carer.name.charAt(0).toUpperCase();
                                let dist = parseFloat(carer.distance);
                                let cardHtml = '';
                                
                                // Matching logic depends on whether we have distance (Client shift) or not (Location/Property shift)
                                let isClientShift = (assignment === 'Client');
                                let distanceHtml = (isClientShift && dist < 1000) ? `<p class="mb-0"><i class='bx bx-check-circle'></i> Within ${dist.toFixed(1)} km</p>` : '';
                                let matchLabel = '';
                                let cardClass = 'carerCard';

                                if (isClientShift) {
                                    if (carer.tag === 'Course Match') {
                                        cardClass = 'carerCard greenCarerCard best-match';
                                        matchLabel = 'Course Match';
                                    } else if (carer.tag === 'Best Match' || dist < 20) {
                                        cardClass = 'carerCard greenCarerCard best-match';
                                        matchLabel = 'Best Match';
                                    } else if (dist > 50) {
                                        cardClass = 'carerCard geographic-mismatch';
                                        matchLabel = 'Geographic Mismatch';
                                    }
                                } else {
                                    // Location/Property shifts
                                    cardClass = 'carerCard greenCarerCard';
                                    matchLabel = 'Available';
                                }

                                cardHtml = `
                                        <div class="${cardClass}">
                                            <div class="avatar">${firstLetter}</div>
                                            <div class="details">
                                                <div class="topRow">
                                                    <div><span class="name">${carer.name}</span> <span class="badge" style="background:#f1f5f9;color:#475569;margin-left:5px">${carer.postcode ?? ''}</span></div>
                                                    ${matchLabel ? `<div><span class="badge ${matchLabel.includes('Mismatch') ? 'redBadges' : 'darkGreenBadges'}">${matchLabel}</span></div>` : ''}
                                                </div>
                                                <div class="d-flex gap-2 mt-2" style="color:#166534; font-size:12px;">
                                                    ${distanceHtml}
                                                    <p class="mb-0 ${distanceHtml ? 'm-l-10' : ''}"><i class='bx bx-check-circle'></i> ${carer.qualifications_count ?? '0'} Qualifications</p>
                                                </div>
                                            </div>
                                            <button type="button" id="assignBtn_${carer.id}" class="assignBtn" data-id="${carer.id}" data-name="${carer.name}">Assign</button>
                                        </div>`;
                                suggestedCarerContainer.insertAdjacentHTML('beforeend', cardHtml);
                            });
                        } else {
                            suggestedCarerContainer.innerHTML = '<p>No Carer Found</p>';
                        }
                    })
                    .catch(() => {
                        suggestedCarerContainer.innerHTML = '<p>Failed to load carers</p>';
                    });
            }

            // Handle Assign Button Click
            suggestedCarerContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('assignBtn')) {
                    e.preventDefault();

                    const carerId = e.target.getAttribute('data-id');
                    const carerName = e.target.getAttribute('data-name');

                    // Set hidden input
                    selectedCarerIdInput.value = carerId;

                    // Display selected carer card
                    selectedCarerName.innerText = carerName;
                    selectedCarerCard.style.display = 'block';

                    // Hide suggestions list
                    suggestionsWrapper.style.display = 'none';
                    toggleBtn.innerText = 'Show Suggestions';
                }
            });

            // Toggle logic
            toggleBtn.addEventListener('click', function() {
                if (suggestionsWrapper.style.display === 'none') {
                    suggestionsWrapper.style.display = 'block';
                    this.innerText = 'Hide Suggestions';
                } else {
                    suggestionsWrapper.style.display = 'none';
                    this.innerText = 'Show Suggestions';
                }
            });

            // Change Carer btn
            if (changeCarerBtn) {
                changeCarerBtn.addEventListener('click', function() {
                    suggestionsWrapper.style.display = 'block';
                    toggleBtn.innerText = 'Hide Suggestions';
                    selectedCarerCard.style.display = 'none';
                    selectedCarerIdInput.value = "";
                    assignedClientTo.innerText = clientSelect.options[clientSelect.selectedIndex].text;

                    if (suggestedCarerContainer.innerHTML.trim() === "" || suggestedCarerContainer.innerHTML.includes("Loading")) {
                        clientSelect.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Form Validation
            document.getElementById('createShiftForm').addEventListener('submit', function(e) {
                const clientTab = document.getElementById('clientTab');
                if (clientTab && clientTab.classList.contains('active')) {
                    const clientId = document.getElementById('clientSelect').value;
                    if (!clientId) {
                        e.preventDefault();
                        alert('Please select a client before creating the shift.');
                        return;
                    }
                }

                // Time Validation
                const startTime = document.querySelector('input[name="start_time"]').value;
                const endTime = document.querySelector('input[name="end_time"]').value;

                if (startTime && endTime) {
                    const start = new Date(`2000-01-01T${startTime}`);
                    const end = new Date(`2000-01-01T${endTime}`);

                    if (end <= start) {
                        e.preventDefault();
                        alert('End time must be after the start time.');
                        return;
                    }
                }

                // Add loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating Shift...';
                submitBtn.style.pointerEvents = 'none'; // Use this instead of disabled to ensure form still submits in some browsers if it's the target

                // If the form takes time or if you want to prevent double clicks:
                // submitBtn.disabled = true; 
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeInput = document.querySelector('#recurringShiftModal [name="start_time"]');
            const endTimeInput = document.querySelector('#recurringShiftModal [name="end_time"]');
            const durationInput = document.querySelector('#recurringShiftModal .duration-readonly');

            function calculateDuration() {
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;

                if (startTime && endTime) {
                    let start = new Date(`2000-01-01T${startTime}`);
                    let end = new Date(`2000-01-01T${endTime}`);

                    if (end <= start) {
                        end.setDate(end.getDate() + 1); // Overnight shift
                    }

                    const diffMs = end - start;
                    const diffHours = diffMs / (1000 * 60 * 60);
                    durationInput.value = diffHours.toFixed(1);
                }
            }

            if (startTimeInput && endTimeInput) {
                startTimeInput.addEventListener('change', calculateDuration);
                endTimeInput.addEventListener('change', calculateDuration);
                calculateDuration();
            }
        });
    </script>

    {{-- ===== SPLIT VIEW GANTT SCHEDULER ===== --}}
    <script>
        (function() {
            'use strict';

            // ---------- Helpers ----------
            const DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            // Timezone-safe date-to-string (avoids UTC shift on +05:30 etc.)
            function localISODate(d) {
                const y = d.getFullYear();
                const mo = String(d.getMonth() + 1).padStart(2, '0');
                const dy = String(d.getDate()).padStart(2, '0');
                return `${y}-${mo}-${dy}`;
            }

            function formatDate(d) {
                return `${MONTHS[d.getMonth()]} ${d.getDate()}`;
            }

            function formatDateFull(d) {
                return `${MONTHS[d.getMonth()]} ${String(d.getDate()).padStart(2,'0')}, ${d.getFullYear()}`;
            }

            function isToday(d) {
                const now = new Date();
                return d.getFullYear() === now.getFullYear() &&
                    d.getMonth() === now.getMonth() &&
                    d.getDate() === now.getDate();
            }

            function getInitials(name) {
                if (!name) return '?';
                const parts = name.trim().split(/\s+/);
                return parts.length >= 2 ?
                    (parts[0][0] + parts[parts.length - 1][0]).toUpperCase() :
                    name[0].toUpperCase();
            }

            // Monday as start of week
            function startOfWeek(date) {
                const d = new Date(date);
                const day = d.getDay();
                d.setDate(d.getDate() - day + (day === 0 ? -6 : 1));
                d.setHours(0, 0, 0, 0);
                return d;
            }

            // ---------- State ----------
            let currentWeekStart = startOfWeek(new Date());

            // ---------- DOM refs ----------
            const splitTab = document.querySelector('.tab[data-tab="split"]');
            const weekTitle = document.getElementById('sv-week-title');
            const teamList = document.getElementById('sv-team-list');
            const teamCount = document.getElementById('sv-team-count');
            const dayHeaders = document.getElementById('sv-day-header-row');
            const rowsContainer = document.getElementById('sv-rows-container');
            const loadingEl = document.getElementById('sv-loading');
            const btnPrev = document.getElementById('sv-prev-week');
            const btnNext = document.getElementById('sv-next-week');
            const btnToday = document.getElementById('sv-today');

            if (!splitTab || !weekTitle) return;

            // ---------- Render header ----------
            function renderHeader(weekStart) {
                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekEnd.getDate() + 6);
                weekTitle.textContent = `${formatDate(weekStart)} - ${formatDateFull(weekEnd)}`;

                dayHeaders.innerHTML = '';
                for (let i = 0; i < 7; i++) {
                    const d = new Date(weekStart);
                    d.setDate(d.getDate() + i);
                    const col = document.createElement('div');
                    col.className = 'sv-day-col-header' + (isToday(d) ? ' sv-today-col' : '');
                    col.innerHTML = `<div class="sv-day-name">${DAYS[d.getDay()]}</div>
                                  <div class="sv-day-date">${formatDate(d)}</div>`;
                    dayHeaders.appendChild(col);
                }
            }

            // ---------- Render rows ----------
            function renderRows(staff, shifts, weekStart) {
                teamList.innerHTML = '';
                rowsContainer.innerHTML = '';

                if (!staff || staff.length === 0) {
                    teamList.innerHTML = '<div class="sv-empty-state"><i class="bx bx-user-x"></i><span>No staff found</span></div>';
                    return;
                }

                teamCount.textContent = `${staff.length} active carer${staff.length !== 1 ? 's' : ''}`;

                // Pre-build a lookup: staffId -> [shift, ...] keyed by date string
                const shiftMap = {};
                shifts.forEach(sh => {
                    const key = `${sh.staff_id}__${sh.start_date}`;
                    if (!shiftMap[key]) shiftMap[key] = [];
                    shiftMap[key].push(sh);
                });

                staff.forEach(member => {
                    const initials = getInitials(member.name);

                    // ---- Left panel card ----
                    const card = document.createElement('div');
                    card.className = 'sv-staff-card';
                    card.innerHTML = `
                    <div class="sv-avatar">${initials}</div>
                    <div class="sv-staff-info">
                        <div class="sv-staff-name">${member.name || 'Unknown'}</div>
                        <div class="sv-staff-type">${(member.employment_type || 'full time').replace('_', ' ')}</div>
                    </div>`;
                    teamList.appendChild(card);

                    // ---- Timeline row ----
                    const row = document.createElement('div');
                    row.className = 'sv-row-container';

                    // Small avatar column at row start
                    const avatarCol = document.createElement('div');
                    avatarCol.className = 'sv-row-avatar-col';
                    avatarCol.innerHTML = `<div class="sv-row-avatar">${initials}</div>`;
                    row.appendChild(avatarCol);

                    // 7 day cells
                    const daysDiv = document.createElement('div');
                    daysDiv.className = 'sv-row-days';

                    for (let i = 0; i < 7; i++) {
                        const day = new Date(weekStart);
                        day.setDate(day.getDate() + i);
                        const dayStr = localISODate(day);
                        const key = `${member.id}__${dayStr}`;

                        const cell = document.createElement('div');
                        cell.className = 'sv-day-cell' + (isToday(day) ? ' sv-today-cell' : '');

                        const cellShifts = shiftMap[key] || [];
                        cellShifts.forEach(sh => {
                            const isFilled = !!sh.staff_id;
                            const block = document.createElement('div');
                            block.className = 'sv-shift-block day-shift-item' + (!isFilled ? ' sv-unfilled' : '');
                            block.style.cursor = 'pointer';

                            // Edit Modal Bindings Payload
                            block.dataset.id = sh.id || '';
                            block.dataset.client = sh.client_id || '';
                            block.dataset.property = sh.property_id || '';
                            block.dataset.location = sh.location_name || '';
                            block.dataset.address = sh.location_address || '';
                            block.dataset.date = sh.start_date || '';
                            block.dataset.start = sh.start_time_raw || '';
                            block.dataset.end = sh.end_time_raw || '';
                            block.dataset.staff = sh.staff_id || '';
                            block.dataset.type = sh.shift_type_raw || '';
                            block.dataset.care = sh.care_type_id || '';
                            block.dataset.assignment = sh.assignment || '';
                            block.dataset.notes = sh.notes || '';
                            block.dataset.tasks = sh.tasks || '';
                            block.dataset.isRecurring = sh.is_recurring || '';
                            block.dataset.recurrence = JSON.stringify(sh.recurrence || null);
                            block.dataset.documents = JSON.stringify(sh.documents || null);
                            block.dataset.assessments = JSON.stringify(sh.assessments || null);
                            block.dataset.staffName = sh.staff_name || member.name || '';

                            const st = sh.start_time ? sh.start_time.substring(0, 5) : '?';
                            const et = sh.end_time ? sh.end_time.substring(0, 5) : '?';
                            const label = sh.location || sh.shift_type || 'Shift';
                            block.innerHTML = `<div class="sv-shift-time">${st} - ${et}</div>
                                           <div class="sv-shift-label">${label}</div>`;
                            cell.appendChild(block);
                        });

                        daysDiv.appendChild(cell);
                    }

                    row.appendChild(daysDiv);
                    rowsContainer.appendChild(row);
                });
            }

            // ---------- Fetch & load ----------
            function loadWeek(weekStart) {
                loadingEl.classList.add('sv-active');
                renderHeader(weekStart);

                const dateParam = localISODate(weekStart);
                fetch(`{{ url('/roster/schedule-shift/weekly-data') }}?week=${dateParam}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        loadingEl.classList.remove('sv-active');
                        renderRows(data.staff || [], data.shifts || [], weekStart);
                    })
                    .catch(err => {
                        loadingEl.classList.remove('sv-active');
                        console.error('Split view fetch error:', err);
                        rowsContainer.innerHTML = '<div class="sv-empty-state"><i class="bx bx-error-circle"></i><span>Failed to load data. Check console.</span></div>';
                    });
            }

            // ---------- Tab trigger ----------
            let loaded = false;
            splitTab.addEventListener('click', function() {
                if (!loaded) {
                    loaded = true;
                    loadWeek(currentWeekStart);
                }
            });

            // ---------- Navigation ----------
            if (btnPrev) btnPrev.addEventListener('click', function() {
                currentWeekStart.setDate(currentWeekStart.getDate() - 7);
                loaded = true; // mark loaded so navigation always works
                loadWeek(currentWeekStart);
            });

            if (btnNext) btnNext.addEventListener('click', function() {
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
                loaded = true;
                loadWeek(currentWeekStart);
            });

            if (btnToday) btnToday.addEventListener('click', function() {
                currentWeekStart = startOfWeek(new Date());
                loaded = true;
                loadWeek(currentWeekStart);
            });

        })();

        $(document).ready(function() {
            fetchData();
            load90DaysData();
        });

        function fetchData() {
            $.ajax({
                url: '{{ route("roster.scheduleShiftByGroup") }}',
                type: 'GET',
                success: function(response) {

                    let html = '';

                    response.forEach(function(user) {

                        // ✅ Skip user if no shifts
                        if (!user.shifts || user.shifts.length === 0) {
                            return; // continue to next user
                        }

                        let totalHours = 0;

                        user.shifts.forEach(function(shift) {
                            totalHours += parseFloat(shift.total_hours ?? 0);
                        });

                        html += `
                                <div class="byGroupContent">
                                    <div class="workHoursHeader">
                                        <div class="title radIconClr">
                                            ${user.name}
                                        </div>
                                        <div class="actions">
                                            ${user.shifts.length} shifts
                                            <div class="roundBtntag radShowbtn">
                                                ${totalHours}h
                                            </div>
                                        </div>
                                    </div>

                                    <div class="recent-activity sectionWhiteBgAllUse">
                            `;

                        user.shifts.forEach(function(shift) {

                            html += `
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class='bx bx-apps'></i>
                                            </div>

                                            <div class="activity-content">
                                                <div class="activity-title">
                                                    ${shift.client_name ?? 'Unknown'}
                                                </div>

                                                <div class="activity-description">
                                                    <i class='bx bx-clock-4'></i>
                                                    ${shift.start_time} - ${shift.end_time}
                                                </div>

                                                <div class="activity-time">
                                                    <i class='bx bx-calendar'></i>
                                                    ${shift.shift_date}
                                                </div>

                                                <div class="inactive roundTag">
                                                    ${shift.shift_type ?? ''}
                                                </div>

                                                <div class="planActions" style="display:flex; gap: 8px;">
                                                    <button class="day-shift-item" style="border: 1px solid #d1d5db; border-radius: 4px; padding: 4px 8px; background: transparent; cursor: pointer;"
                                                        data-id="${shift.id || ''}"
                                                        data-client="${shift.client_id || ''}"
                                                        data-property="${shift.property_id || ''}"
                                                        data-location="${shift.location_name || ''}"
                                                        data-address="${shift.location_address || ''}"
                                                        data-date="${shift.start_date || ''}"
                                                        data-start="${shift.start_time_raw || ''}"
                                                        data-end="${shift.end_time_raw || ''}"
                                                        data-staff="${shift.staff_id || ''}"
                                                        data-type="${shift.shift_type_raw || ''}"
                                                        data-care="${shift.care_type_id || ''}"
                                                        data-assignment="${shift.assignment || ''}"
                                                        data-notes="${shift.notes || ''}"
                                                        data-tasks="${shift.tasks || ''}"
                                                        data-is-recurring="${shift.is_recurring || ''}"
                                                        data-recurrence='${JSON.stringify(shift.recurrence || null)}'
                                                        data-documents='${JSON.stringify(shift.documents || null)}'
                                                        data-assessments='${JSON.stringify(shift.assessments || null)}'
                                                        data-staff-name="${shift.staff_name || ''}">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </button>

                                                    <button class="danger delete" style="border: 1px solid #ef4444; color: #ef4444; border-radius: 4px; padding: 4px 8px; background: transparent; cursor: pointer;" data-id="${shift.id}">
                                                        <i class="bx bx-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="roundBtntag greenShowbtn">
                                                ${shift.status ?? 'unfilled'}
                                            </div>
                                        </div>
                                    `;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    });

                    $('#bygroup').html(html);
                }
            });
        }

        function load90DaysData(date = null) {

            // fetch(`/roster/ninety-days-data?date=${date ?? ''}`)
            fetch(`{{ route('ninety.days.data') }}?date=${date ?? ''}`)
                .then(response => response.json())
                .then(data => {

                    // SUMMARY
                    document.getElementById('totalShifts').innerText = data.summary.total;
                    document.getElementById('filledShifts').innerText = data.summary.filled;
                    document.getElementById('unfilledShifts').innerText = data.summary.unfilled;
                    document.getElementById('fillRate').innerText = data.summary.fill_rate + '%';

                    // WEEKLY
                    let container = document.getElementById('weeklyContainer');
                    container.innerHTML = '';

                    data.weekly.forEach(week => {

                        let badgeClass = week.fill_rate === 100 ?
                            'greenShowbtn' :
                            'radShowbtn';

                        container.innerHTML += `
                <div class="planCard">
                    <div class="planTop">
                        <div class="planTitle">
                            Week: ${formatDate(week.week_start)} - ${formatDate(week.week_end)}
                        </div>
                        <div class="planActions">
                            <span class="roundBtntag ${badgeClass}">
                                ${week.fill_rate}% Filled
                            </span>
                        </div>
                    </div>

                    <div class="planMeta totalShiftsCounter">
                        <div class="rota_dash-left">
                            Total Shifts
                            <h2 class="rota_count">${week.total}</h2>
                        </div>
                        <div class="rota_dash-left">
                            Filled
                            <h2 class="rota_count greenText">${week.filled}</h2>
                        </div>
                        <div class="rota_dash-left">
                            Unfilled
                            <h2 class="rota_count orangeText">${week.unfilled}</h2>
                        </div>
                        <div class="rota_dash-left">
                            Completed
                            <h2 class="rota_count blueText">${week.completed}</h2>
                        </div>
                    </div>

                    <div class="progressBar">
                        <div class="progressFill" style="width:${week.fill_rate}%"></div>
                    </div>
                </div>
            `;
                    });

                });
        }

        function formatDate(dateStr) {
            let date = new Date(dateStr);
            return date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        // Load on page start
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('monthCalendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                initialView: 'dayGridMonth',
                headerToolbar: false, // We'll build our own controls
                dayMaxEvents: 3, // Shows "+X more" if more than 3

                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        url: "{{ route('get.monthly.shifts') }}",
                        type: "GET",
                        data: {
                            start: fetchInfo.startStr,
                            end: fetchInfo.endStr
                        },
                        success: function(response) {
                            successCallback(response);
                        },
                        error: function() {
                            failureCallback();
                        }
                    });
                },

                loading: function(isLoading) {
                    if (isLoading) {
                        document.getElementById('monthDateDisplay').innerText = 'Loading Events...';
                    }
                },

                datesSet: function(info) {
                    // Update our custom header string
                    let monthNameDate = info.view.currentStart;
                    let monthStr = monthNameDate.toLocaleString('default', {
                        month: 'long',
                        year: 'numeric'
                    });
                    document.getElementById('monthDateDisplay').innerText = monthStr;
                },

                eventContent: function(arg) {
                    let ev = arg.event;
                    let timeStr = ev.title; // We send the formatted time as the title from PHP
                    let status = ev.extendedProps.status; // from PHP
                    let isUnfilled = status === 'unfilled';

                    // Pick an icon badge string
                    let iconStr = isUnfilled ? '!' : '✓';

                    return {
                        html: `<div style="background:${ev.backgroundColor};color:#fff;border-radius:4px;padding:3px 6px;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:space-between;width:100%;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;cursor:pointer;">
                            <span>${timeStr}</span>
                            <span>${iconStr}</span>
                        </div>`
                    };
                },

                eventClick: function(arg) {
                    let props = arg.event.extendedProps;

                    const shiftId = props.shift_id;
                    const client = props.client_id;
                    const date = props.start_date;
                    const start = props.start_time_raw;
                    const end = props.end_time_raw;
                    const staff = props.staff_id;
                    const type = props.shift_type_raw;
                    const property = props.property_id;
                    const locationName = props.location_name;
                    const locationAddress = props.location_address;
                    const careType = props.care_type;
                    const assignment = props.assignment;
                    const notes = props.notes;
                    const tasks = props.tasks;
                    const staffName = props.staff_name || '';

                    const form = $('#createShiftForm');



                    // Change form action to update
                    let updateUrl = '{{ url("roster/schedule-shift/update") }}/' + shiftId;
                    form.attr('action', updateUrl);
                    form.find('#edit_shift_id').val(shiftId);
                    console.log('shiftId2', shiftId);
                    form.closest('.modal-content').find('.modal-title').text('Edit Shift');
                    form.find('button[type="submit"]').html('Update Shift');

                    // Populate fields

                    form.find('#edit_shift_id').val(shiftId);
                    console.log('shiftId', shiftId);

                    // Set Date/Time FIRST (to avoid incorrect initial suggestion API call)
                    form.find('[name="start_date"]').val(date);
                    form.find('[name="start_time"]').val(start);
                    form.find('[name="end_time"]').val(end);

                    if (client) {
                        form.find('[name="client_id"]').val(client).trigger('change');
                        $('#assignedClientTo').text(form.find('[name="client_id"] option:selected').text());
                    } else {
                        form.find('[name="client_id"]').val('').trigger('change');
                        $('#assignedClientTo').text('Not assigned');
                    }

                    if (staff) {
                        $('#selected_carer_id').val(staff);
                        $('#selectedCarerName').text(staffName);
                        $('#selectedCarerCard').show();
                        $('#carerSuggestionsWrapper').hide();
                        $('#toggleSuggestionsBtn').text('Show Suggestions');
                    } else {
                        $('#selected_carer_id').val('');
                        $('#selectedCarerCard').hide();
                        $('#carerSuggestionsWrapper').show();
                    }

                    form.find('[name="shift_type"]').val(type).trigger('change');
                    if (homeAreaId) form.find('[name="home_area_id"]').val(homeAreaId).trigger('change');
                    if (property) form.find('[name="property_id"]').val(property).trigger('change');
                    form.find('[name="location_name"]').val(locationName || '');
                    form.find('[name="location_address"]').val(locationAddress || '');

                    if (careType) {
                        form.find('[name="care_type"]').val(careType).trigger('change');
                    }

                    if (assignment) {
                        form.find('[name="assignment"]').val(assignment).trigger('change');
                        let assignLower = assignment.toLowerCase();
                        if (assignLower === 'location' || assignLower === 'home area') $('#locationTab').click();
                        else if (assignLower === 'client') $('#clientTab').click();
                    } else {
                        form.find('[name="assignment"]').val('Client').trigger('change');
                        $('#clientTab').click();
                    }

                    form.find('[name="notes"]').val(notes || '');

                    if (tasks) {
                        form.find('[name="tasks"]').val(tasks).trigger('change');
                    } else {
                        form.find('[name="tasks"]').val('').trigger('change');
                    }

                    // Populate Recurrence
                    if (props.is_recurring == "1" || props.is_recurring === true) {
                        form.find('#recurringClientToggle').prop('checked', true);
                        if (props.recurrence) {
                            form.find('[name="frequency"]').val(props.recurrence.frequency).trigger('change');
                            form.find('[name="end_date"]').val(props.recurrence.end_recurring_date || '');

                            const daysRow = form.find('.weeklyDaysSelect').closest('.col-md-12');
                            if (props.recurrence.frequency === 'weekly') {
                                daysRow.show();
                                if (props.recurrence.week_days) {
                                    try {
                                        let days = [];
                                        if (typeof props.recurrence.week_days === 'string' && (props.recurrence.week_days.startsWith('[') || props.recurrence.week_days.startsWith('{'))) {
                                            days = JSON.parse(props.recurrence.week_days);
                                        } else if (typeof props.recurrence.week_days === 'string') {
                                            days = props.recurrence.week_days.split(',').map(d => d.trim());
                                        } else {
                                            days = props.recurrence.week_days;
                                        }

                                        form.find('.weeklyDaysSelect span').removeClass('active');
                                        form.find('.weeklyDaysSelect span').each(function() {
                                            if (days.includes($(this).text().trim())) {
                                                $(this).addClass('active');
                                            }
                                        });
                                        form.find('#week_days').val(Array.isArray(days) ? days.join(',') : props.recurrence.week_days);
                                    } catch (e) {
                                        console.error('Failed to parse week_days', e);
                                    }
                                }
                            } else {
                                daysRow.hide();
                                form.find('.weeklyDaysSelect span').removeClass('active');
                                form.find('.weeklyDaysSelect span').first().addClass('active');
                                form.find('#week_days').val('');
                            }
                        }
                        $('#recurringClientDiv').slideDown();
                    } else {
                        form.find('#recurringClientToggle').prop('checked', false);
                        form.find('[name="frequency"]').val('daily').trigger('change');
                        form.find('[name="end_date"]').val('');
                        form.find('.weeklyDaysSelect span').removeClass('active');
                        form.find('.weeklyDaysSelect span').first().addClass('active');
                        form.find('#week_days').val('');
                        $('#recurringClientDiv').slideUp();
                    }

                    // Populate Documents
                    $('.pendingCard').remove();
                    let hasDocs = false;
                    let documentsData = props.documents;
                    if (documentsData && documentsData.length > 0) {
                        hasDocs = true;
                        documentsData.forEach(doc => {
                            let isForm = doc.form_id ? true : false;
                            let today = new Date().toISOString().split('T')[0];

                            // Improved title logic: use doc_name, fallback to filename or form_id
                            let title = doc.doc_name;
                            if (!title) {
                                if (isForm) {
                                    title = 'System Form #' + doc.form_id;
                                } else if (doc.doc_file) {
                                    title = doc.doc_file.split('/').pop();
                                } else {
                                    title = 'Unnamed Document';
                                }
                            }

                            if (!isForm && doc.doc_file) {
                                // Correct path: BASE_URL + '/' + doc_file
                                title = `<a href="{{ url('/') }}/${doc.doc_file}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${title}</a>`;
                            }

                            let newSection = `
                                <div class="card pendingCard" data-doc-id="${doc.id}">
                                    <input type="hidden" name="existing_document_ids[]" value="${doc.id}">
                                    <div class="left">
                                        <div class="icon blueText"><i class='bx bx-file'></i></div>
                                        <div class="info">
                                            <div class="title">${title}</div>
                                            <div class="meta">
                                                <div class="inactive roundTag">${isForm ? 'System Form' : 'Attachment'}</div>
                                                ${doc.doc_required == 1 ? '<div class="inactive roundTag" style="background:#fee2e2;color:#991b1b;">Required</div>' : ''}
                                                <span class="date">${today}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <span class="approve"><i class='bx bx-check-circle'></i></span>
                                        <span class="delete" onclick="$(this).closest('.pendingCard').remove(); $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');"><i class='bx bx-trash'></i></span>
                                    </div>
                                </div>
                            `;
                            $('#pendingCompletion').append(newSection);
                        });
                    }

                    if (hasDocs) {
                        $('#pendingCompletionSection').show();
                        $('#attachDocumentSection').hide();
                        $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');
                        $('#close_document').hide();
                        $('#attach_document').show();
                    } else {
                        $('#pendingCompletionSection').hide();
                        $('#attachDocumentSection').show();
                    }

                    // Populate Assessments
                    $('#assessmentList').empty();
                    let assessmentsData = props.assessments;
                    if (assessmentsData && assessmentsData.length > 0) {
                        assessmentsData.forEach((ass, index) => {
                            let itemId = 'assessment-item-edit-' + index;
                            let fileNameRaw = ass.assessment_doc ? ass.assessment_doc.split('/').pop() : 'Assessment ' + ass.id;
                            let fileNameHtml = fileNameRaw;
                            if (ass.assessment_doc) {
                                fileNameHtml = `<a href="{{ url('/') }}/${ass.assessment_doc}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${fileNameRaw}</a>`;
                            }

                            let itemHtml = `
                                <div class="assessment-item" id="${itemId}">
                                    <input type="hidden" name="existing_assessment_ids[]" value="${ass.id}">
                                    <div class="assessment-item-left">
                                        <i class="fa fa-file-text-o"></i>
                                        <span class="assessment-item-name" title="${fileNameRaw}">${fileNameHtml}</span>
                                    </div>
                                    <div class="assessment-item-right">
                                        <select class="assessment-type-select" name="existing_assessment_types[${ass.id}]">
                                            <option value="${ass.assessment_type || 'other'}" selected>${ass.assessment_type || 'Other'}</option>
                                        </select>
                                        <button type="button" class="assessment-item-delete" title="Remove" onclick="$(this).closest('.assessment-item').remove()">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            $('#assessmentList').append(itemHtml);
                        });
                    }

                    $('#assessment_card').show();
                    $('#addShiftModal').modal('show');
                }
            });

            calendar.render();

            // Setup a ResizeObserver so whenever the user clicks the "Month" tab and this div 
            // becomes visible (display changes from none to block), the calendar instantly resizes and renders.
            if (window.ResizeObserver) {
                new ResizeObserver(function() {
                    calendar.updateSize();
                }).observe(calendarEl);
            }

            // Bind our custom Header Controls
            $('#monthPrevBtn').click(function() {
                calendar.prev();
            });
            $('#monthNextBtn').click(function() {
                calendar.next();
            });
            $('#monthTodayBtn').click(function() {
                calendar.today();
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let dayCursor = new Date(); // tracks the day being viewed

            function loadDayShifts() {
                // Ensure correct local date string "YYYY-MM-DD"
                let targetDate = dayCursor.getFullYear() + "-" + String(dayCursor.getMonth() + 1).padStart(2, '0') + "-" + String(dayCursor.getDate()).padStart(2, '0');
                let baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

                $('#dayShiftsList').html('<div style="text-align:center;padding:24px;color:#9ca3af;">Loading shifts...</div>');

                $.ajax({
                    url: baseUrl + '/roster/carer/shifts/day?date=' + targetDate,
                    type: 'GET',
                    success: function(res) {
                        $('#dayDateDisplay').text(res.date);
                        $('#dayShiftsCount').text(res.total + (res.total === 1 ? ' shift scheduled' : ' shifts scheduled'));

                        let html = '';
                        if (res.shifts.length === 0) {
                            html = '<div style="background:#fff;border-radius:8px;border:1px solid #e5e7eb;padding:32px;text-align:center;color:#6b7280;font-size:14px;">No shifts scheduled for this day.</div>';
                        } else {
                            res.shifts.forEach(function(shift) {
                                html += `
                                <div class="day-shift-item" style="background:#fff;border-radius:8px;border:1px solid #e5e7eb;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.02);cursor:pointer;transition:all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)'"
                                    data-id="${shift.id}"
                                    data-client="${shift.client_id || ''}"
                                    data-property="${shift.property_id || ''}"
                                    data-location="${shift.location_name || ''}"
                                    data-address="${shift.location_address || ''}"
                                    data-date="${shift.start_date || ''}"
                                    data-start="${shift.start_time_raw || ''}"
                                    data-end="${shift.end_time_raw || ''}"
                                    data-staff="${shift.staff_id || ''}"
                                    data-type="${shift.shift_type_raw || ''}"
                                    data-care="${shift.care_type || ''}"
                                    data-homearea="${shift.home_area_id || ''}"
                                    data-assignment="${shift.assignment || ''}"
                                    data-notes="${shift.notes || ''}"
                                    data-tasks="${shift.tasks || ''}"
                                    data-is-recurring="${shift.is_recurring || ''}"
                                    data-recurrence='${JSON.stringify(shift.recurrence || null)}'
                                    data-documents='${JSON.stringify(shift.documents || null)}'
                                    data-assessments='${JSON.stringify(shift.assessments || null)}'
                                    data-staff-name="${shift.staff_name || ''}">
                                    <div style="display:flex;gap:8px;margin-bottom:12px;">
                                        <span style="background:#f3e8ff;color:#9333ea;padding:4px 8px;border-radius:6px;font-size:12px;font-weight:600;">scheduled</span>
                                        <span style="border:1px solid #e5e7eb;color:#374151;padding:4px 8px;border-radius:6px;font-size:12px;font-weight:600;">${shift.shift_type}</span>
                                    </div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                                        <div style="display:flex;align-items:center;gap:6px;font-weight:600;color:#111827;font-size:14px;min-width:180px;">
                                            <i class="bx bx-time" style="color:#6b7280;font-size:16px;"></i>
                                            ${shift.start_time} - ${shift.end_time} <span style="color:#9ca3af;font-weight:normal;">(${shift.duration})</span>
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;color:#374151;font-size:14px;min-width:150px;">
                                            <i class="bx bx-user" style="color:#9ca3af;font-size:16px;"></i>
                                            ${shift.staff_name}
                                        </div>
                                        <div style="display:flex;align-items:center;gap:6px;color:#374151;font-size:14px;min-width:150px;">
                                            <i class="bx bx-map" style="color:#9ca3af;font-size:16px;"></i>
                                            ${shift.client_name}
                                        </div>
                                    </div>
                                </div>`;
                            });
                        }
                        $('#dayShiftsList').html(html);
                    },
                    error: function() {
                        $('#dayShiftsList').html('<div style="text-align:center;padding:24px;color:#ef4444;">Failed to load shifts. Please try again.</div>');
                    }
                });
            }

            // Bind click events
            $('#dayPrevBtn').click(function() {
                dayCursor.setDate(dayCursor.getDate() - 1);
                loadDayShifts();
            });

            $('#dayNextBtn').click(function() {
                dayCursor.setDate(dayCursor.getDate() + 1);
                loadDayShifts();
            });

            $('#dayTodayBtn').click(function() {
                dayCursor = new Date();
                loadDayShifts();
            });

            // If the user clicks "Day" view tab from header, fetch data on demand or immediately
            $('#btnDay').click(function() {
                loadDayShifts();
            });

            // Initiate first load
            loadDayShifts();

            // Handle edit modal opening
            $(document).on('click', '.day-shift-item', function() {
                const shiftId = $(this).data('id');
                const client = $(this).data('client');
                const date = $(this).data('date');
                const start = $(this).data('start');
                const end = $(this).data('end');
                const staff = $(this).data('staff');
                const type = $(this).data('type');
                const property = $(this).data('property');
                const locationName = $(this).data('location');
                const locationAddress = $(this).data('address');
                const careType = $(this).data('care');
                const homeAreaId = $(this).data('homearea');
                const assignment = $(this).data('assignment');
                const notes = $(this).data('notes');
                const tasks = $(this).data('tasks');
                const staffName = $(this).data('staff-name') || '';
                const isRecurring = $(this).data('is-recurring');
                const recurrenceData = $(this).data('recurrence');
                const documentsData = $(this).data('documents');
                const assessmentsData = $(this).data('assessments');

                const form = $('#createShiftForm');

                // Change form action to update
                form.attr('action', '{{ url("roster/schedule-shift/update") }}/' + shiftId);
                form.closest('.modal-content').find('.modal-title').text('Edit Shift');
                form.find('button[type="submit"]').html('Update Shift');

                // Populate fields
                if (client) {
                    form.find('[name="client_id"]').val(client).trigger('change');
                    $('#assignedClientTo').text(form.find('[name="client_id"] option:selected').text());
                } else {
                    form.find('[name="client_id"]').val('').trigger('change');
                    $('#assignedClientTo').text('Not assigned');
                }
                form.find('[name="start_date"]').val(date);
                form.find('[name="start_time"]').val(start);
                form.find('[name="end_time"]').val(end);

                // Handle optional/empty selects
                if (staff) {
                    $('#selected_carer_id').val(staff);
                    $('#selectedCarerName').text(staffName);
                    $('#selectedCarerCard').show();
                    $('#carerSuggestionsWrapper').hide();
                    $('#toggleSuggestionsBtn').text('Show Suggestions');
                } else {
                    $('#selected_carer_id').val('');
                    $('#selectedCarerCard').hide();
                    $('#carerSuggestionsWrapper').show();
                }

                form.find('[name="shift_type"]').val(type).trigger('change');
                form.find('[name="home_area_id"]').val(homeAreaId).trigger('change');
                form.find('[name="property_id"]').val(property).trigger('change');
                form.find('[name="location_name"]').val(locationName);
                form.find('[name="location_address"]').val(locationAddress);

                if (careType) {
                    form.find('[name="care_type"]').val(careType).trigger('change');
                }
                if (assignment) {
                    form.find('[name="assignment"]').val(assignment).trigger('change');
                    let assignLower = assignment.toLowerCase();
                    if (assignLower === 'location' || assignLower === 'home area') $('#locationTab').click();
                    else if (assignLower === 'client') $('#clientTab').click();
                } else {
                    form.find('[name="assignment"]').val('Client').trigger('change');
                    $('#clientTab').click();
                }
                if (homeAreaId) {
                    form.find('#homeAreaSelect').val(homeAreaId).trigger('change');
                }
                form.find('[name="notes"]').val(notes);

                if (tasks) {
                    form.find('[name="tasks"]').val(tasks).trigger('change');
                } else {
                    form.find('[name="tasks"]').val('').trigger('change');
                }

                // Populate Recurrence
                if (isRecurring == "1" || isRecurring === true) {
                    form.find('#recurringClientToggle').prop('checked', true);
                    if (recurrenceData) {
                        form.find('[name="frequency"]').val(recurrenceData.frequency).trigger('change');
                        form.find('[name="end_date"]').val(recurrenceData.end_recurring_date || '');

                        const daysRow = form.find('.weeklyDaysSelect').closest('.col-md-12');
                        if (recurrenceData.frequency === 'weekly') {
                            daysRow.show();
                            if (recurrenceData.week_days) {
                                try {
                                    let days = [];
                                    if (typeof recurrenceData.week_days === 'string' && (recurrenceData.week_days.startsWith('[') || recurrenceData.week_days.startsWith('{'))) {
                                        days = JSON.parse(recurrenceData.week_days);
                                    } else if (typeof recurrenceData.week_days === 'string') {
                                        days = recurrenceData.week_days.split(',').map(d => d.trim());
                                    } else {
                                        days = recurrenceData.week_days;
                                    }

                                    form.find('.weeklyDaysSelect span').removeClass('active');
                                    form.find('.weeklyDaysSelect span').each(function() {
                                        if (days.includes($(this).text().trim())) {
                                            $(this).addClass('active');
                                        }
                                    });
                                    form.find('#week_days').val(Array.isArray(days) ? days.join(',') : recurrenceData.week_days);
                                } catch (e) {
                                    console.error('Failed to parse week_days', e);
                                }
                            }
                        } else {
                            daysRow.hide();
                            form.find('.weeklyDaysSelect span').removeClass('active');
                            form.find('.weeklyDaysSelect span').first().addClass('active');
                            form.find('#week_days').val('');
                        }
                    }
                    $('#recurringClientDiv').slideDown();
                } else {
                    form.find('#recurringClientToggle').prop('checked', false);
                    form.find('[name="frequency"]').val('daily').trigger('change');
                    form.find('[name="end_date"]').val('');
                    form.find('.weeklyDaysSelect span').removeClass('active');
                    form.find('.weeklyDaysSelect span').first().addClass('active');
                    form.find('#week_days').val('');
                    $('#recurringClientDiv').slideUp();
                }

                // Populate Documents
                $('.pendingCard').remove();
                let hasDocs = false;
                if (documentsData && documentsData.length > 0) {
                    hasDocs = true;
                    documentsData.forEach(doc => {
                        let title = doc.doc_name || 'System Form ' + (doc.form_id || '');
                        let today = new Date().toISOString().split('T')[0];
                        let isForm = doc.form_id ? true : false;

                        if (!isForm && doc.doc_file) {
                            title = `<a href="{{ url('uploads/documents/') }}/${doc.doc_file}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${title}</a>`;
                        }

                        let newSection = `
                            <div class="card pendingCard" data-doc-id="${doc.id}">
                                <input type="hidden" name="existing_document_ids[]" value="${doc.id}">
                                <div class="left">
                                    <div class="icon blueText"><i class='bx bx-file'></i></div>
                                    <div class="info">
                                        <div class="title">${title}</div>
                                        <div class="meta">
                                            <div class="inactive roundTag">${isForm ? 'System Form' : 'Attachment'}</div>
                                            <span class="date">${today}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="actions">
                                    <span class="approve"><i class='bx bx-check-circle'></i></span>
                                    <span class="delete" onclick="$(this).closest('.pendingCard').remove(); $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');"><i class='bx bx-trash'></i></span>
                                </div>
                            </div>
                        `;
                        $('#pendingCompletion').append(newSection);
                    });
                }

                if (hasDocs) {
                    $('#pendingCompletionSection').show();
                    $('#attachDocumentSection').hide();
                    $('#pendingHeader').text('Pending Completion (' + $('.pendingCard').length + ')');
                    $('#close_document').hide();
                    $('#attach_document').show();
                } else {
                    $('#pendingCompletionSection').hide();
                    $('#attachDocumentSection').show();
                }

                // Populate Assessments
                $('#assessmentList').empty();
                if (assessmentsData && assessmentsData.length > 0) {
                    assessmentsData.forEach((ass, index) => {
                        let itemId = 'assessment-item-edit-' + index;
                        let fileNameRaw = ass.assessment_doc ? ass.assessment_doc.split('/').pop() : 'Assessment ' + ass.id;
                        let fileNameHtml = fileNameRaw;
                        if (ass.assessment_doc) {
                            fileNameHtml = `<a href="{{ url('/') }}/${ass.assessment_doc}" target="_blank" style="color:var(--primary-color); text-decoration:underline;">${fileNameRaw}</a>`;
                        }

                        let itemHtml = `
                            <div class="assessment-item" id="${itemId}">
                                <input type="hidden" name="existing_assessment_ids[]" value="${ass.id}">
                                <div class="assessment-item-left">
                                    <i class="fa fa-file-text-o"></i>
                                    <span class="assessment-item-name" title="${fileNameRaw}">${fileNameHtml}</span>
                                </div>
                                <div class="assessment-item-right">
                                <select class="assessment-type-select" name="existing_assessment_types[${ass.id}]">
                                    <option value="other" ${ass.assessment_type === 'other' || !ass.assessment_type ? 'selected' : ''}>Other</option>
                                    <option value="supervision" ${ass.assessment_type === 'supervision' ? 'selected' : ''}>Supervision Form</option>
                                    <option value="care_plan" ${ass.assessment_type === 'care_plan' ? 'selected' : ''}>Care Plan</option>
                                    <option value="risk" ${ass.assessment_type === 'risk' ? 'selected' : ''}>Risk Assessment</option>
                                    <option value="medication" ${ass.assessment_type === 'medication' ? 'selected' : ''}>Medication Chart</option>
                                </select>
                                    <button type="button" class="assessment-item-delete" title="Remove" onclick="$(this).closest('.assessment-item').remove()">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#assessmentList').append(itemHtml);
                    });
                }

                $('#assessment_card').show();
                $('#addShiftModal').modal('show');
            });

            // Handle delete shift
            $(document).on('click', '.delete', function(e) {
                e.preventDefault();
                const shiftId = $(this).data('id');
                const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

                if (confirm('Are you sure you want to delete this shift?')) {
                    $.ajax({
                        url: baseUrl + '/roster/schedule-shift/delete/' + shiftId,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert(response.message || 'Error deleting shift.');
                            }
                        },
                        error: function() {
                            alert('An error occurred while deleting the shift.');
                        }
                    });
                }
            });

            // Reset modal attributes when closed so "Add Shift" works correctly
            $('#addShiftModal').on('hidden.bs.modal', function() {
                const form = $('#createShiftForm');

                // Ensure action defaults to store
                form.attr('action', '{{ route("roster.schedule.store") }}');
                form.closest('.modal-content').find('.modal-title').text('Create New Shift');
                form.find('button[type="submit"]').html('Create Shift');

                // Reset form fields
                form[0].reset();
                form.find('select').val('').trigger('change');

                // Reset Documents & Assessments
                $('.pendingCard').remove();
                $('#assessmentList').empty();
                $('#assessment_card').hide();
                $('#pendingCompletionSection').hide();
                $('#attachDocumentSection').show();
            });

            // --- WEEK VIEW LOGIC ---
            let weekCursor = new Date();

            function loadWeekShifts() {
                let targetDate = weekCursor.getFullYear() + "-" + String(weekCursor.getMonth() + 1).padStart(2, '0') + "-" + String(weekCursor.getDate()).padStart(2, '0');
                let baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

                $('#weekShiftsList').html('<div style="grid-column: span 7;text-align:center;padding:24px;color:#9ca3af;">Loading week data...</div>');

                $.ajax({
                    url: baseUrl + '/roster/carer/shifts/week?date=' + targetDate,
                    type: 'GET',
                    success: function(res) {
                        $('#weekDateDisplay').text(res.week_label);

                        let html = '';
                        res.days.forEach(function(day) {
                            let colStyle = 'background:#fafafa;border-radius:12px;border:1px solid #e5e7eb;min-width:160px;display:flex;flex-direction:column;';
                            if (day.is_today) {
                                colStyle = 'background:#f0f9ff;border-radius:12px;border:2px solid #3b82f6;min-width:160px;display:flex;flex-direction:column;box-shadow:0 0 0 1px rgba(59,130,246,0.2) inset;';
                            }

                            let todayBadge = day.is_today ? '<div style="background:#3b82f6;color:#fff;font-size:11px;padding:2px 8px;border-radius:12px;display:inline-block;margin-top:4px;">Today</div>' : '';
                            let dayTitleColor = day.is_today ? '#3b82f6' : '#111827';

                            html += `<div style="${colStyle}">
                                <!-- Column Header -->
                                <div style="padding:16px 16px 12px;border-bottom:1px solid #e5e7eb;background:transparent;">
                                    <div style="font-size:14px;color:#6b7280;font-weight:600;">${day.day_name}</div>
                                    <div style="font-size:24px;font-weight:700;color:${dayTitleColor};line-height:1.2;">${day.day_number}</div>
                                    ${todayBadge}
                                </div>
                                <!-- Shifts Container -->
                                <div style="padding:10px;flex:1;display:flex;flex-direction:column;gap:8px;">`;

                            if (day.shifts.length === 0) {
                                html += `<div style="text-align:center;color:#d1d5db;font-size:12px;padding:12px 0;"></div>`;
                            } else {
                                day.shifts.forEach(function(shift) {
                                    html += `
                                    <div class="day-shift-item" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,0.02);cursor:pointer;transition:all 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='0 1px 2px rgba(0,0,0,0.02)'"
                                        data-id="${shift.id}"
                                        data-client="${shift.client_id || ''}"
                                        data-property="${shift.property_id || ''}"
                                        data-location="${shift.location_name || ''}"
                                        data-address="${shift.location_address || ''}"
                                        data-date="${shift.start_date || ''}"
                                        data-start="${shift.start_time_raw || ''}"
                                        data-end="${shift.end_time_raw || ''}"
                                        data-staff="${shift.staff_id || ''}"
                                        data-type="${shift.shift_type_raw || ''}"
                                        data-care="${shift.care_type || ''}"
                                        data-homearea="${shift.home_area_id || ''}"
                                        data-assignment="${shift.assignment || ''}"
                                        data-notes="${shift.notes || ''}"
                                        data-tasks="${shift.tasks || ''}"
                                        data-is-recurring="${shift.is_recurring || ''}"
                                        data-recurrence='${JSON.stringify(shift.recurrence || null)}'
                                        data-documents='${JSON.stringify(shift.documents || null)}'
                                        data-assessments='${JSON.stringify(shift.assessments || null)}'
                                        data-staff-name="${shift.staff_name || ''}">
                                        <div style="display:flex;align-items:center;gap:6px;font-weight:600;color:#1f2937;font-size:13px;margin-bottom:6px;">
                                            <span style="width:6px;height:6px;background:#3b82f6;border-radius:50%;"></span>
                                            ${shift.start_time}
                                        </div>
                                        <div style="display:flex;align-items:center;gap:4px;color:#6b7280;font-size:12px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <i class="bx bx-user" style="color:#9ca3af;"></i> ${shift.staff_name}
                                        </div>
                                        <div style="display:flex;align-items:center;gap:4px;color:#6b7280;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <i class="bx bx-right-arrow-alt" style="color:#9ca3af;"></i> ${shift.client_name}
                                        </div>
                                    </div>`;
                                });
                            }

                            html += `</div></div>`;
                        });
                        $('#weekShiftsList').html(html);
                    },
                    error: function() {
                        $('#weekShiftsList').html('<div style="grid-column: span 7;text-align:center;padding:24px;color:#ef4444;">Failed to load week data.</div>');
                    }
                });
            }

            // Bind click events for Week View
            $('#weekPrevBtn').click(function() {
                weekCursor.setDate(weekCursor.getDate() - 7);
                loadWeekShifts();
            });

            $('#weekNextBtn').click(function() {
                weekCursor.setDate(weekCursor.getDate() + 7);
                loadWeekShifts();
            });

            $('#weekTodayBtn').click(function() {
                weekCursor = new Date();
                loadWeekShifts();
            });

            // If the user clicks "Week" view tab from header
            $('#btnWeek').click(function() {
                loadWeekShifts();
            });

            // Initiate first load for week
            loadWeekShifts();

            // --- 90 DAYS OVERVIEW LOGIC ---
            let days90Cursor = new Date();

            function load90DaysShifts() {
                let targetDate = days90Cursor.getFullYear() + "-" + String(days90Cursor.getMonth() + 1).padStart(2, '0') + "-" + String(days90Cursor.getDate()).padStart(2, '0');
                let baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

                $('#weeklyContainer').html('<div style="text-align:center;padding:24px;color:#9ca3af;">Loading 90-day overview...</div>');

                $.ajax({
                    url: baseUrl + '/roster/carer/shifts/ninety-days?date=' + targetDate,
                    type: 'GET',
                    success: function(res) {


                        $('#days90 .full-date').text(res.overview_date);
                        $('#days90 .day-text').text(res.overview_date.split(',')[0]);
                        $('#totalShifts').text(res.summary.total);
                        $('#filledShifts').text(res.summary.filled);
                        $('#unfilledShifts').text(res.summary.unfilled);
                        $('#fillRate').text(res.summary.fill_rate + '%');

                        let html = '';
                        html += `
                            <div class="psHeader">
                                <span class="psIcon"><i class="bx bx-trending-up"></i> </span>
                                <span class="psTitle">Weekly Breakdown</span>
                            </div>`;

                        res.weeks.forEach(function(week) {
                            let badgeClass = week.fill_rate === 100 ? 'greenShowbtn' : (week.fill_rate === 0 ? 'radShowbtn' : 'yllowShowbtn');
                            let barColor = week.fill_rate === 100 ? '#22c55e' : (week.fill_rate === 0 ? '#ef4444' : '#eab308');

                            html += `
                            <div class="planCard" style="border-bottom: 3px solid ${barColor};">
                                <div class="planTop">
                                    <div class="planTitle" style="font-weight: 600; color: #1f2937;">
                                        ${week.label}
                                    </div>
                                    <div class="planActions">
                                        <span class="roundBtntag ${badgeClass}">${week.fill_rate}% Filled</span>
                                    </div>
                                </div>

                                <div class="planMeta totalShiftsCounter" style="display:flex; justify-content:space-between; text-align:left; border-top: 1px solid #e5e7eb; padding-top: 12px; margin-top: 12px;">
                                    <div class="rota_dash-left">
                                        <p style="margin:0 0 4px 0; font-size:12px; color:#6b7280; font-weight:500;">Total Shifts</p>
                                        <h2 class="rota_count" style="margin:0; font-size:18px; font-weight:700; color:#111827;">${week.total}</h2>
                                    </div>
                                    <div class="rota_dash-left">
                                        <p style="margin:0 0 4px 0; font-size:12px; color:#6b7280; font-weight:500;">Filled</p>
                                        <h2 class="rota_count" style="margin:0; font-size:18px; font-weight:700; color:#22c55e;">${week.filled}</h2>
                                    </div>
                                    <div class="rota_dash-left">
                                        <p style="margin:0 0 4px 0; font-size:12px; color:#6b7280; font-weight:500;">Unfilled</p>
                                        <h2 class="rota_count" style="margin:0; font-size:18px; font-weight:700; color:#ef4444;">${week.unfilled}</h2>
                                    </div>
                                    <div class="rota_dash-left">
                                        <p style="margin:0 0 4px 0; font-size:12px; color:#6b7280; font-weight:500;">Completed</p>
                                        <h2 class="rota_count" style="margin:0; font-size:18px; font-weight:700; color:#3b82f6;">${week.completed}</h2>
                                    </div>
                                </div>
                            </div>`;
                        });

                        $('#weeklyContainer').html(html);
                    },
                    error: function() {
                        $('#weeklyContainer').html('<div style="text-align:center;padding:24px;color:#ef4444;">Failed to load 90-day overview data.</div>');
                    }
                });
            }

            $('#days90 .prev-btn').click(function() {
                days90Cursor.setDate(days90Cursor.getDate() - 90);
                load90DaysShifts();
            });

            $('#days90 .next-btn').click(function() {
                days90Cursor.setDate(days90Cursor.getDate() + 90);
                load90DaysShifts();
            });

            $('#days90 .datechangeBtnTodayOrNext .btn.borderBtn').click(function() {
                days90Cursor = new Date();
                load90DaysShifts();
            });

            $('[data-tab="days90"]').click(function() {
                load90DaysShifts();
            });

            load90DaysShifts();
        });

        // Form Reset for New Shift
        $('#addShiftModal').on('show.bs.modal', function(e) {
            // e.relatedTarget is the button that triggered the modal (i.e. "+ Add Shift")
            // If it's undefined, the modal was triggered via JS (e.g. "Edit Shift")
            if (e.relatedTarget) {
                const form = $('#createShiftForm');
                form[0].reset();

                // Reset custom inputs & layouts
                form.find('[name="carer_id"]').val('');
                form.attr('action', "{{ route('roster.schedule.store') }}"); // Assuming store
                $(this).find('.modal-title').text('Create New Shift');
                form.find('button[type="submit"]').html('Create Shift');

                $('#selectedCarerName').text('');
                $('#selectedCarerCard').hide();
                $('#carerSuggestionsWrapper').show();
                $('#toggleSuggestionsBtn').hide();
                $('#assignedCarerBlankSection').show();
                $('#suggested_carer_container').hide();
                $('#assignedClientTo').text('Not assigned');

                // Reset Documents & Assessments
                $('.pendingCard').remove();
                $('#pendingCompletionSection').hide();
                $('#attachDocumentSection').show();
                $('#close_document').hide();
                $('#attach_document').show();

                $('#assessment_card').hide();
                $('#assessmentList').empty();

                // Trigger select events
                $('#clientSelect').val('').trigger('change');

                // Reset Recurrence
                form.find('#recurringClientToggle').prop('checked', false);
                form.find('[name="frequency"]').val('daily').trigger('change');
                form.find('[name="end_date"]').val('');
                form.find('.weeklyDaysSelect span').removeClass('active');
                form.find('.weeklyDaysSelect span').first().addClass('active');
                form.find('#week_days').val('');
                $('#recurringClientDiv').slideUp();
            }
        });
    </script>

    @endsection
</main>