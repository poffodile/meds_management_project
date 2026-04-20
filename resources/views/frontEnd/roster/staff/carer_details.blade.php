@extends('frontEnd.layouts.master')
@section('title', 'Carer')
@section('content')

@include('frontEnd.roster.common.roster_header')

<link rel="stylesheet" href="{{ asset('public/frontEnd/staff/css/working-hours.css') }}">

<style>
    .notContDetails {
        display: flex;
    }

    .notContDetails span {
        flex-grow: 3;
    }

    /*
                                                                                                                                                                                                                                                                                                                                                                                                                    .leavebanktabCont i {
                                                                                                                                                                                                                                                                                                                                                                                                                        font-size: 18px;
                                                                                                                                                                                                                                                                                                                                                                                                                        color: #f00;
                                                                                                                                                                                                                                                                                                                                                                                                                    } */

    .notContDetails .planCard {
        border: 1px solid #eee;
    }

    .uploadDocumentCont .certifiedList {
        background: #f6f9fd;
    }

    span.showDocumentname {
        display: flex;
        gap: 12px;
    }

    .showDocumentname span {
        background: #ffffff;
        color: #000;
        padding: 9px 20px;
        border-radius: 4px;
        text-align: center;
        font-size: 12px;
    }

    span.showDocumentname button {
        display: flex;
        gap: 8px;
        color: #fff;
        height: 34px;
    }

    span.showDocumentname i {
        color: #fff;
        line-height: 22px;
    }

    .items-row {
        display: flex;
        gap: 40px;
        /* space between items */
    }
</style>
<?php
$week_days = [
    [
        'slug' => 'monday',
        'value' => 'Monday',
        'is_weekend' => 0,
    ],
    [
        'slug' => 'tuesday',
        'value' => 'Tuesday',
        'is_weekend' => 0,
    ],
    [
        'slug' => 'wednesday',
        'value' => 'Wednesday',
        'is_weekend' => 0,
    ],
    [
        'slug' => 'thursday',
        'value' => 'Thursday',
        'is_weekend' => 0,
    ],
    [
        'slug' => 'friday',
        'value' => 'Friday',
        'is_weekend' => 0,
    ],
    [
        'slug' => 'saturday',
        'value' => 'Saturday',
        'is_weekend' => 1,
    ],
    [
        'slug' => 'sunday',
        'value' => 'Sunday',
        'is_weekend' => 1,
    ],
];
?>
<main class="page-content empoyeeHeader">
    <div class="topHeaderCont">
        <div>
            <h1>Employee: {{ $staffDetails->name }}</h1>
            <p class="header-subtitle">
                <span>
                    @if ($staffDetails->status == 1)
                    Active
                    @elseif ($staffDetails->status == 0)
                    Inactive
                    @elseif ($staffDetails->status == 2)
                    On Leave
                    @else
                    —
                    @endif
                </span>
                @if ($staffDetails->employment_type === 'full_time')
                <span>Full Time</span>
                @elseif($staffDetails->employment_type === 'part_time')
                <span>Part Time</span>
                @elseif($staffDetails->employment_type === 'contract')
                <span>Contract</span>
                @endif
                {{ $staffDetails->email }}
            </p>
        </div>

        <div class="header-actions">
            <a href="{{ url('/roster/schedule-shift') }}" class="btn"><i class='bx bx-calendar'></i> View Schedule</a>
            <a href="#" class="btn"><i class='bx bx-calendar'></i> Planning</a>
            <a href="#" class="btn"><i class='bx bx-user-x'></i> Terminate</a>
            <a href="#" class="btn"><i class='bx bx-history'></i> Audit Log</a>
        </div>
    </div>
    <div class="container-fluid">

        <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
            <div class="tabs p-1 ">
                <button class="tab active" data-tab="generalTab">General</button>
                <button class="tab" data-tab="availabilityTab">Availability</button>
                <button class="tab" data-tab="trainingQualificationsTab">Training & Qualifications <span
                        class="tabNumber">{{ count($staffDetails->qualifications) }}</span></button>
                <button class="tab" data-tab="supervisionsTab">Supervisions<span
                        class="tabNumber">{{ $superVisionCount ?? 0 }}</span></button>
                <button class="tab" data-tab="shiftsTab">Shifts</button>
                <button class="tab" data-tab="documentsTab">Documents</button>
                <button class="tab" data-tab="notesTab">Notes</button>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content carertabcontent">
                <div class="content active" id="generalTab">
                    <div class="sectionWhiteBgAllUse">
                        <div class="profile-details-card">
                            <div class="section two-col">
                                <div>
                                    <h3>Personal Information</h3>
                                    <div class="item">
                                        <span class="label">Full Name</span>
                                        <span class="value">{{ $staffDetails->name }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Email</span>
                                        <span class="value">{{ $staffDetails->email }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Phone</span>
                                        <span class="value">{{ $staffDetails->phone_no }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Address</span>
                                        <span class="value">
                                            {{ $staffDetails->current_location }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <h3>Employment Details</h3>
                                    <div class="item">
                                        <span class="label">Employment Type</span>
                                        @if ($staffDetails->employment_type === 'full_time')
                                        <span class="value">Full Time</span>
                                        @elseif($staffDetails->employment_type === 'part_time')
                                        <span class="value">Part Time</span>
                                        @elseif($staffDetails->employment_type === 'contract')
                                        <span class="value">Contract</span>
                                        @endif
                                    </div>
                                    <div class="item">
                                        <span class="label">Hourly Rate</span>
                                        <span class="value">
                                            £{{ number_format($staffDetails->hourly_rate ?? 0, 2) }}/hr</span>

                                    </div>
                                    <div class="item">
                                        <span class="label">Status</span>
                                        <span class="value">
                                            @if ($staffDetails->status == 1)
                                            Active
                                            @elseif ($staffDetails->status == 0)
                                            Inactive
                                            @elseif ($staffDetails->status == 2)
                                            On Leave
                                            @else
                                            —
                                            @endif
                                        </span>

                                    </div>
                                    <div class="item">
                                        <span class="label">Overtime Available</span>
                                        {{-- <span class="value">{{ $staffDetails->overtime_available ? 'Yes' : 'No' }}</span> --}}
                                        <span class="value">
                                            @if ($staffDetails->available_for_overtime == 1)
                                            Yes (max {{ $staffDetails->max_extra_hours }} hrs/week)
                                            @else
                                            No
                                            @endif
                                        </span>

                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="section">
                                <h3>Emergency Contact</h3>
                                <div class="three-col">
                                    <div class="item">
                                        <span class="label">Name</span>
                                        <span class="value">{{ $staffDetails->emergencyContact->name ?? '' }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Phone</span>
                                        <span
                                            class="value">{{ $staffDetails->emergencyContact->phone_no ?? '' }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Relationship</span>
                                        <span
                                            class="value">{{ $staffDetails->emergencyContact->relationship ?? '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="divider"></div>

                            <div class="section">
                                <h3>DBS Information</h3>
                                <div class="items-row">
                                    <div class="item">
                                        <span class="label">DBS Number</span>
                                        <span
                                            class="value">{{ $staffDetails->dbs_certificate_number ? $staffDetails->dbs_certificate_number : '' }}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">DBS Expiry</span>
                                        <span
                                            class="value">{{ $staffDetails->dbs_expiry_date ? \Carbon\Carbon::parse($staffDetails->dbs_expiry_date)->format('F jS, Y') : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="availabilityTab">
                    <div class="availabilityTabs">
                        <!-- TAB HEADER -->
                        <div class="availabilityTabs__nav">
                            <button class="availabilityTabs__tab active" data-target="workHoursPanel">Working
                                Hours</button>
                            <button class="availabilityTabs__tab"
                                data-target="unavailabilityPanel">Unavailability</button>
                            <button class="availabilityTabs__tab" data-target="summaryPanel">Summary</button>
                        </div>

                        <!-- TAB CONTENT -->
                        <div class="availabilityTabs__content">

                            <!-- WORKING HOURS -->
                            <div class="availabilityTabs__panel active" id="workHoursPanel">
                                <div class="workHoursCard">
                                    <div class="workHoursHeader">
                                        <div class="title"><i class='bx  bx-history'></i> Working Hours</div>
                                        <div class="actions">
                                            <input type="hidden" id="workHoursPerWeekValue">
                                            <span class="badge" id="workHoursPerWeekText">8.0 hrs/week</span>
                                            <button class="btn" type="button" id="applyMondayToWeek"> <i
                                                    class='bx  bx-copy'></i> Apply Mon to
                                                Weekdays</button>
                                            <button class="btn-outline" type="button" id="resetWorkingHrsBtn"><i
                                                    class='bx  bx-rotate-ccw'></i>
                                                Reset</button>
                                        </div>
                                    </div>
                                    <div class="schedulePattern">
                                        <div class="col-md-6 mb-3">
                                            <label for="schedule_pattern">Schedule Pattern</label>
                                            <select class="form-control" id="schedule_pattern">
                                                <option value="standard" <?php isset($staffDetails) && $staffDetails->working_hours_type == 'standard' ? 'selected' : ''; ?>>Standard Weekly Pattern
                                                </option>
                                                <option value="alternate" <?php isset($staffDetails) && $staffDetails->working_hours_type == 'alternate' ? 'selected' : ''; ?>>Alternate Weeks</option>
                                                <option value="specific" <?php isset($staffDetails) && $staffDetails->working_hours_type == 'specific' ? 'selected' : ''; ?>>Choose Specific Dates
                                                    (next 60 days)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 mb-3" id="editing_week" style="display:none;">
                                            <label for="schedule_pattern_2">Editing Week</label>
                                            <select class="form-control" id="schedule_pattern_2">
                                                <option value="1">Week 1</option>
                                                <option value="2">Week 2</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Standard Weekly -->
                                    <div id="tab-standard">
                                        @foreach ($week_days as $item)
                                        <div class="dayRow {{ $item['is_weekend'] == 1 ? 'weekend' : '' }}">
                                            <span class="day">{{ $item['value'] }}</span>

                                            <label class="switch">
                                                <input type="checkbox" data-daysname="{{ $item['slug'] }}"
                                                    class="dayToggle" data-workinghrsid="">
                                                <span class="slider"></span>
                                            </label>

                                            <div class="workingFields">
                                                <input type="time" value="09:00"
                                                    class="dayTime form-control startTime">
                                                <span>to</span>
                                                <input type="time" value="17:00"
                                                    class="dayTime form-control endTime">
                                                <span class="hours" data-hrsdiffval='8'>8.0 hrs</span>
                                            </div>

                                            <span class="notWorking" style="display:none;">Not working</span>
                                        </div>
                                        @endforeach
                                    </div>

                                    <!-- Alternate Weeks -->
                                    <div id="tab-alternate">
                                        <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                        </div>
                                        <div class="week_1">
                                            <input type="hidden" id="total_working_week_1">
                                            @foreach ($week_days as $item)
                                            <div class="dayRow {{ $item['is_weekend'] == 1 ? 'weekend' : '' }}">
                                                <span class="day">{{ $item['value'] }}</span>

                                                <label class="switch">
                                                    <input type="checkbox" data-daysname="{{ $item['slug'] }}"
                                                        class="dayToggle" data-workinghrsid="">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields">
                                                    <input type="time" value="09:00"
                                                        class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00"
                                                        class="dayTime form-control endTime">
                                                    <span class="hours" data-hrsdiffval='8'>8.0 hrs</span>
                                                </div>

                                                <span class="notWorking" style="display:none;">Not working</span>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="week_2 d-none">
                                            <input type="hidden" id="total_working_week_2">
                                            @foreach ($week_days as $item)
                                            <div class="dayRow {{ $item['is_weekend'] == 1 ? 'weekend' : '' }}">
                                                <span class="day">{{ $item['value'] }}</span>

                                                <label class="switch">
                                                    <input type="checkbox" data-daysname="{{ $item['slug'] }}"
                                                        class="dayToggle" data-workinghrsid="">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields">
                                                    <input type="time" value="09:00"
                                                        class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00"
                                                        class="dayTime form-control endTime">
                                                    <span class="hours" data-hrsdiffval='8'>8.0 hrs</span>
                                                </div>

                                                <span class="notWorking" style="display:none;">Not working</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Specific Dates -->
                                    <div id="tab-specific" class="availabilityScroller">

                                        <!-- Header -->
                                        <p class="helperText">
                                            Select the specific dates over the next 60 days when this carer is available
                                            to work:
                                        <div><span class="selectedCount" id="selectedCount">0 dates selected</span>
                                        </div>
                                        </p>

                                        <!-- DATE GRID -->
                                        <div class="calendarGrid" id="calendarGrid"></div>

                                        <hr class="divider">

                                        <!-- HOURS SECTION -->
                                        <div class="hoursSection">
                                            <div class="sectionTitle">
                                                <i class="bx bx-time"></i> Set Hours for Selected Dates
                                            </div>

                                            <div id="hoursList"></div>
                                        </div>

                                    </div>

                                    <div class="modal-footer m-t-0">
                                        <div class="alert alert-danger d-none" id="workingHoursFormError"></div>
                                        <button class="btn allBtnUseColor validation_staff" type="button"
                                            id="saveWorkingHrsBtn"> Save
                                            Working Hours </button>
                                    </div>

                                </div>
                            </div>

                            <!-- UNAVAILABILITY -->
                            <div class="availabilityTabs__panel" id="unavailabilityPanel">
                                <div class="shadowp bg-purple-50 rounded8 p24" style="margin-bottom:20px"
                                    id="leave_request_main_wrapper">
                                    <p class="fs13 font600 darkPurpleTextp"> <i
                                            class="bx bx-calendar-week f18 me-2"></i> Approved Leave Requests </p>
                                    <div id="leave_request_wrapper">
                                        <div class="bg-purple-50 bgWhite rounded5 p-3">
                                            <div class="flexBw">
                                                <div>
                                                    <h5 class="h5Head mb-2">Nov 7 - Nov 7, 2025</h5>
                                                    <p class="textGray500 fs13 mb-0">holiday</p>
                                                </div>
                                                <div>
                                                    <span class="careBadg purpleBadges">Via Leave System</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="leave-card">
                                    <div class="workHoursHeader">
                                        <div class="title"><i class='bx  bx-calendar-alt-2'></i> Unavailability
                                            Periods</div>
                                        <div class="actions">
                                            <button class="allbuttonDarkClr addalertClientDetailsBtn"> <i
                                                    class='bx  bx-plus'></i> Add Unavailability</button>
                                        </div>
                                    </div>
                                    <div class="p-20">
                                        <div class="alert alert-danger d-none" id="unavailabilityFormError"></div>
                                        <div class="clientFilterform addalertClientDetailsform">
                                            <form id="unavailabilityForm">
                                                <input type="hidden" id="unavailability_id"
                                                    name="unavailability_id">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label>Type</label>
                                                            <select class="form-control" name="unavailability_type"
                                                                id="unavailability_type">
                                                                <option value="single">Single</option>
                                                                <option value="range">Date Range</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <label id="start_date_label">Date</label>
                                                        <input type="date" value="{{ date('Y-m-d') }}"
                                                            min="{{ date('Y-m-d') }}" class="form-control"
                                                            id="start_date" name="start_date">
                                                    </div>
                                                    <div class="col-md-12 m-t-10 d-none"
                                                        id="unavailability_end_date_wrapper">
                                                        <label>End Date</label>
                                                        <input type="date" class="form-control" id="end_date"
                                                            name="end_date">
                                                    </div>

                                                    <div class="col-md-6 m-t-10"
                                                        id="unavailability_start_time_wrapper">
                                                        <label>From Time (optional)</label>
                                                        <input type="time" class="form-control"
                                                            id="unavailability_start_time"
                                                            name="unavailability_start_time" placeholder="">
                                                    </div>
                                                    <div class="col-md-6  m-t-10"
                                                        id="unavailability_end_time_wrapper">
                                                        <label>To Time (optional)</label>
                                                        <input type="time" class="form-control"
                                                            id="unavailability_end_time"
                                                            name="unavailability_end_time" placeholder="">
                                                    </div>

                                                    <div class="col-md-12  m-t-10">
                                                        <label>Reason (optional)</label>
                                                        <textarea name="unavailability_reason" id="unavailability_reason" class="form-control" rows="3"
                                                            cols="20" placeholder="e.g., Personal appointment, Training, etc."></textarea>
                                                    </div>
                                                    <div class="col-md-12 text-center m-t-10">
                                                        <button class="btn allBtnUseColor image_val" type="button"
                                                            id="addUnavailabilityBtn">
                                                            Add Unavailability </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="addUnavailabilityList">
                                        <div class="blankCarer" id="defaultBlankCarerWrapper">
                                            <div class="leave-card"
                                                style="height: 400px; margin-top:0px; display:flex; justify-content:center; align-items:center;">
                                                <div class="leavebanktabCont blankdesign">
                                                    <i class="bx bx-calendar-x"></i>
                                                    <h4 class="font600">No unavailability periods set</h4>
                                                    <p class="textGray500">Add periods when this carer is not
                                                        available
                                                        for shifts</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SUMMARY -->
                            <div class="availabilityTabs__panel" id="summaryPanel">
                                <div class="">
                                    <div class="workHoursHeader">
                                        <div class="actions" id="summaryCard">
                                            <button class="greenShowbtn"><i class='bx  bx-history'></i> 0 days •
                                                0h/week</button>
                                            <span class="badge">No working hours set</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="content" id="trainingQualificationsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Qualifications</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr" data-user-id="{{ $staffDetails->id }}"
                                    id="addQualificationBtn"> <i class='bx bx-education'></i> Add
                                    Qualification</button>
                            </div>
                        </div>

                        <div class="">
                            @forelse ($staffDetails->qualifications as $qualification)
                            <div class="certifiedList">
                                <span>{{ $qualification['name'] }}</span>

                                @if (!empty($qualification['image']))
                                <span class="roundBtntag greenShowbtn"><a
                                        href="{{ asset(userQualificationImgPath . '/' . $qualification['image']) }}"
                                        target="_blank">Certified</a></span>
                                @else
                                <span class="roundBtntag radShowbtn">Not Certified</span>
                                @endif
                            </div>
                            @empty
                            <div class="certifiedList">
                                <span>No qualification and certificate available</span>
                            </div>
                            @endforelse

                        </div>
                    </div>
                </div>

                <!-- supervision  -->
                <div class="content" id="supervisionsTab">
                    <div class="bgWhite lightBorderp rounded8 cursorPointer">
                        <div class="cardHeaderp p24">
                            <div class="flexBw">
                                <div class="dFlexGap gap-2">
                                    <i class="bx bx-clipboard-detail blueText f20"></i>
                                    <h6 class="h6Head mb-0"> Supervision History </h6>
                                </div>
                                <div id="superVisionHeaderBadge" class="d-none">
                                    <span class="careBadg redbadges"><i class="bx bx-alert-triangle"></i> Next:
                                        overdue </span>
                                </div>
                            </div>
                        </div>
                        <div class="p-4" id="supervision-list-wrapper">
                            <div class="leavebanktabCont">
                                <i class="bx  bx-clipboard-detail"></i>
                                <p>No supervision records</p>
                            </div>
                        </div>
                        <div id="supervision-pagination" class="mt-3 ms-4 text-center pagination"></div>
                        {{-- <div class="leavebanktabCont">
                                <i class="bx  bx-clipboard-detail"></i>
                                <p>No supervision records</p>
                            </div> --}}
                    </div>
                    <!-- supervision detail modal -->
                    <div class="modal fade leaveCommunStyle" id="superDetailModal" tabindex="1" role="dialog"
                        aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="modal-dialog pModalScroll">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"> Supervision Details </h4>
                                </div>
                                <div class="modal-body heightScrollModal">
                                    <div class="row" id="supervisionDetailsWrapper">
                                        <div class="col-md-6">
                                            <p class="muteText mb-2">Staff Member</p>
                                            <p class="h7Head mt-0">Michael Brown</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="muteText mb-2">Supervisor</p>
                                            <p class="h7Head mt-0">Phil Holt</p>
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <p class="muteText mb-2">Date</p>
                                            <p class="h7Head">27 November 2025</p>
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <p class="muteText mb-2">Type</p>
                                            <div> <span class="careBadg darkBlackBadg">formal one to one</span> </div>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <p class="muteText mb-2">Supervisor Notes</p>
                                            <div class="muteBg rounded8 p-3">
                                                <p class="mb-0 text-sm para">Supervisor Notes
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <p class="muteText mb-2">Staff Comments</p>
                                            <div class="lightBlueBg rounded8 p-3">
                                                <p class="mb-0 text-sm para">Staff Comments </p>
                                            </div>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <p class="muteText mb-2">Attached Documents</p>
                                            <div class="lightBorderp p-3 rounded8 muteBg">
                                                <div class="dFlexGap">
                                                    <div>
                                                        <i class="bx bx-file-detail fs23 blueText"></i>
                                                    </div>
                                                    <div>
                                                        <p class="fs13 font700 mb-2">Braden QD, Communication, and
                                                            Condition Specific Assessment</p>
                                                        <span class="orangeText fs12">Pending</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="emergencyMain p24 mt-3">
                                                <div class="dFlexGap mb-3"> <i class="bx bx-link fs23"></i>
                                                    <h5 class="mb-0 h5Head">
                                                        Attached Documents
                                                    </h5>
                                                    <div class="userMum">
                                                        <span>2</span>
                                                    </div>
                                                </div>
                                                <div class="lightBorderp p-3 rounded8 mt-3">
                                                    <div class="dFlexGap">
                                                        <div class="fileBoxSuper">
                                                            <i class="bx bx-file f20"></i>
                                                        </div>
                                                        <div>
                                                            <p class="fs13 mb-2 font700">
                                                                Braden QD, Communication, and Condition Specific
                                                                Assessment
                                                            </p>
                                                            <div class="dFlexGap">
                                                                <div class="userMum">
                                                                    <span
                                                                        class="title bgWhite50 mt-0 hoverBg">Healthcare
                                                                        Clinical Assessment
                                                                    </span>
                                                                </div>
                                                                <p class="mb-0 fs12 textGray">Feb 18, 2026
                                                                </p>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="lightBorderp p-3 rounded8 mt-3">
                                                    <div class="dFlexGap">
                                                        <div class="fileBoxSuper">
                                                            <i class="bx bx-file f20"></i>
                                                        </div>
                                                        <div>
                                                            <p class="fs13 mb-2 font700">
                                                                Braden QD, Communication, and Condition Specific
                                                                Assessment
                                                            </p>
                                                            <div class="dFlexGap">
                                                                <div class="userMum">
                                                                    <span
                                                                        class="title bgWhite50 mt-0 hoverBg">Healthcare
                                                                        Clinical Assessment
                                                                    </span>
                                                                </div>
                                                                <p class="mb-0 fs12 textGray">Feb 18, 2026
                                                                </p>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex justify-content-start"
                                    id="supervisionFooterDetailsWrapper">
                                    <div>
                                        <p class="muteText text-left mb-2">Next Supervision Due</p>
                                        <p class="h7Head text-left">
                                            27 December 2025
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- supervision detail modal end -->
                    <!-- supervision -->
                    {{-- <div class="leave-card">
                            <div class="workHoursHeader">
                                <div class="title"><i class="bx  bx-clipboard-detail"></i> Supervision History</div>
                            </div>
                            <div class="leavebanktabCont">
                                <i class="bx  bx-clipboard-detail"></i>
                                <p>No supervision records</p>
                            </div>
                        </div> --}}
                </div>
                <div class="content" id="shiftsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Recent Shifts</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr"> <i class='bx bx-calendar'></i> View All</button>
                            </div>
                        </div>
                        <!-- <div class="leavebanktabCont">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <p>No shifts recorded</p>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div> -->
                        <div class="">
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>
                                <span class="roundBtntag greenShowbtn"> scheduled </span>
                            </div>
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>
                                <span class="roundBtntag inactive"> published </span>
                            </div>
                            <div class="certifiedList">
                                <span class="">
                                    <div>Wed, Dec 10, 2025</div>
                                    <small>09:00 - 17:00 • 8hrs</small>
                                </span>
                                <span class="roundBtntag greenShowbtn"> Certified </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="documentsTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Documents</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr openUploadDocumentModal"
                                    data-id="{{ $staffDetails->id }}"> <i class='bx bx-file-detail'></i> Upload
                                    Document</button>
                            </div>
                        </div>
                        <div class="">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="uploadDocumentCont">
                                        @forelse ($user_documents as $documents)
                                        <div class="certifiedList remove-document">
                                            <span class="showDocumentname">
                                                <span>{{ $documents['title'] }}</span>
                                            </span>
                                            <div class="planActions">
                                                <a href="{{ asset('storage/app/public/' . $documents['file_path']) }}"
                                                    class="viewDocOpen"><i class='bx bx-eye'></i> </a>
                                                <button class="danger delete-document"
                                                    data-id="{{ $documents['id'] }}"><i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @empty
                                        <p>No documents uploaded</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="notesTab">
                    <div class="leave-card">
                        <div class="workHoursHeader">
                            <div class="title">Notes</div>
                            <div class="actions">
                                <button class="allbuttonDarkClr openNotesModal" data-id="{{ $staffDetails->id }}">
                                    <i class='bx bx-file-detail'></i> Add
                                    Note</button>
                            </div>
                        </div>
                        <div class="addNoteContentList">
                            @forelse ($user_notes as $note)
                            <div class="certifiedList planActions remove-note">
                                <span class="noteDateAndText">
                                    <div><strong>Date :</strong>
                                        {{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y') }}
                                    </div>
                                    <small>{{ $note['note'] }}</small>
                                </span>
                                <button class="danger delete-note" data-id="{{ $note['id'] }}"><i
                                        class="bx  bx-trash"></i> </button>
                            </div>
                            @empty
                            <p>No notes recorded</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TAB CONTENT -->
        </div>
    </div>

    {{-- Upload Document Model --}}
    <div class="modal fade leaveCommunStyle" id="uploadDocumentModal" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <!-- FORM START -->
                <form id="uploadDocumentForm" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- MODAL HEADER -->
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Upload Document</h4>
                    </div>
                    <!-- MODAL BODY -->
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="staff_id" value="">

                        <div class="form-group">
                            <label>Document Title</label>
                            <input type="text" name="document_title" id="document_title" class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Upload File</label>
                            <input type="file" name="document_file" id="document_file" class="form-control"
                                required>
                        </div>
                    </div>

                    <!-- MODAL FOOTER -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn allBtnUseColor" id="saveNote">
                            Save
                        </button>
                    </div>

                </form>
                <!-- FORM END -->

            </div>
        </div>
    </div>
    {{-- Upload Document Model --}}

    {{-- Upload Notes Model --}}
    <div class="modal fade leaveCommunStyle" id="noteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="noteForm">
                    @csrf
                    <input type="hidden" name="user_id" id="note_user_id">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add Note</h4>
                    </div>

                    <div class="modal-body">
                        <textarea name="note" class="form-control" required></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cancel
                        </button>
                        <button class="btn allBtnUseColor">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Upload Notes Model --}}

    {{-- Qualification Model --}}
    <div class="modal fade leaveCommunStyle" id="qualificationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Qualification</h4>
                </div>
                <div class="modal-body">
                    <div class="addQualificationForm">
                        <form id="qualificationForm" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $staffDetails->id }}"
                                id="qualification_userId">
                            @foreach ($courses as $course)
                            @php
                            $qualification = $staffDetails->qualifications
                            ->where('course_id', $course['course_id'])
                            ->first();

                            $isChecked = in_array($course['course_id'], $selectedCourseIds);
                            @endphp

                            <div class="form-group">
                                <label>
                                    <input type="checkbox"
                                        name="qualifications[{{ $course['course_id'] }}][course_id]"
                                        value="{{ $course['course_id'] }}" {{ $isChecked ? 'checked' : '' }}>
                                    {{ $course['title'] }}

                                    <input type="hidden" name="qualifications[{{ $course['course_id'] }}][name]"
                                        value="{{ $course['title'] }}">
                                </label>

                                {{-- Upload ALWAYS visible --}}

                                <div class="">
                                    <input type="file" name="qualifications[{{ $course['course_id'] }}][cert]"
                                        class="qual_upload" accept="application/pdf,.pdf">
                                </div>

                                {{-- View Certificate --}}
                                @if ($qualification && $qualification->image)
                                <div class="mt-1">
                                    <a href="{{ asset('public/images/userQualification/' . $qualification->image) }}"
                                        target="_blank">
                                        <i class='bx  bx-eye'></i>
                                    </a>
                                </div>
                                @endif

                            </div>
                            @endforeach

                        </form>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn allBtnUseColor" id="saveQualification">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- Qualification Model --}}
    <input type="hidden" id="carer_id" value="{{ $staffDetails->id }}">
    <input type="hidden" id="working_hours_type" value="{{ $staffDetails->working_hours_type }}">
    <script src="{{ asset('public/frontEnd/staff/js/working-hours1.js') }}"></script>

    <script>
        function alertMsg(selector = 'err', msg) {
            $('.ajax-alert-' + selector).find('.msg').text(
                msg);
            $('.ajax-alert-' + selector).show();

            setTimeout(function() {
                $(".ajax-alert-" + selector).fadeOut()
            }, 5000);
        }
        const tabs = document.querySelectorAll(".tab");
        const contents = document.querySelectorAll(".content");

        tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                document.querySelector(".tab.active")?.classList.remove("active");
                tab.classList.add("active");

                let tabName = tab.getAttribute("data-tab");
                //console.log(tabName);

                contents.forEach(content => {
                    content.classList.remove("active");
                });

                if (tabName === 'availabilityTab') {
                    setWorkingHoursData()
                }
                if (tabName === 'supervisionsTab') {
                    fetch_supervisions(1)
                }

                document.getElementById(tabName).classList.add("active");
            });
        });




        document.querySelectorAll(".availabilityTabs").forEach(wrapper => {

            const tabs = wrapper.querySelectorAll(".availabilityTabs__tab");
            const panels = wrapper.querySelectorAll(".availabilityTabs__panel");

            tabs.forEach(tab => {
                tab.addEventListener("click", () => {

                    tabs.forEach(t => t.classList.remove("active"));
                    panels.forEach(p => p.classList.remove("active"));

                    tab.classList.add("active");
                    // console.log(tab.dataset.target);
                    wrapper
                        .querySelector("#" + tab.dataset.target)
                        .classList.add("active");
                    if (tab.dataset.target === 'unavailabilityPanel') {
                        loadUnavailability()
                    }

                });
            });

        });
        document.addEventListener("DOMContentLoaded", function() {

            const toggleBtn = document.querySelector(".addalertClientDetailsBtn");
            const formBox = document.querySelector(".addalertClientDetailsform");

            if (toggleBtn && formBox) {
                toggleBtn.addEventListener("click", function() {
                    formBox.classList.toggle("active");
                });
            }

        });

        $(document).on('click', '.openUploadDocumentModal', function() {

            // Fill modal fields
            const staffId = $(this).data('id');
            $('#staff_id').val(staffId);

            // Open modal
            $('#uploadDocumentModal').modal('show');
        });

        $(document).on('click', '.openNotesModal', function() {
            const staffId = $(this).data('id');
            $('#note_user_id').val(staffId);

            // Open modal
            $('#noteModal').modal('show');
        });
        $('#uploadDocumentForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('carer.save.documents') }}", // update route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#uploadDocumentModal').modal('hide');
                    // alert('Document uploaded successfully');
                    location.reload();
                },
                error: function(xhr) {
                    alert('Something went wrong');
                }
            });
        });
        $(document).ready(function() {

            /* -------------------------
               OPEN MODAL
            -------------------------- */
            $(document).on('click', '.openNoteModal', function() {
                let userId = $(this).data('id');

                $('#note_user_id').val(userId);
                $('#note_text').val('');
                $('#noteModal').modal('show');
            });

            /* -------------------------
               SAVE NOTE (AJAX)
            -------------------------- */
            $('#noteForm').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $('#saveNoteBtn').prop('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ url('/roster/carer/save-notes') }}",
                    type: "POST",
                    data: formData,
                    success: function(res) {

                        if (res.status) {
                            $('#noteModal').modal('hide');

                            $('#noteForm')[0].reset();
                            location.reload();
                            // alert('Note saved successfully');
                        } else {
                            alert(res.message ?? 'Something went wrong');
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;

                        if (errors) {
                            alert(Object.values(errors)[0][0]);
                        } else {
                            alert('Server error');
                        }
                    },
                    complete: function() {
                        $('#saveNoteBtn').prop('disabled', false).text('Save');
                    }
                });
            });

        });
        $(document).on('click', '.delete-note', function() {
            let noteId = $(this).data('id');
            let button = $(this);
            if (!confirm('Are you sure you want to delete this note?')) {
                return;
            }

            $.ajax({
                url: "{{ url('/roster/carer/delete-notes') }}/" + noteId,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the note card from UI
                        button.closest('.remove-note').remove();
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });

        $(document).on('click', '.delete-document', function() {
            let documentId = $(this).data('id');
            let button = $(this);
            if (!confirm('Are you sure you want to delete this document?')) {
                return;
            }

            $.ajax({
                url: "{{ url('/roster/carer/delete-documents') }}/" + documentId,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        button.closest('.remove-document').remove();
                        // location.reload();
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again.');
                }
            });
        });

        $(document).on('click', '#addQualificationBtn', function() {
            let userId = $(this).data('user-id');
            // document.getElementById('qualification_userId')
            let id = $('#qualification_userId').val(userId);
            $('#qualificationModal').modal('show');
        });
        $(document).ready(function() {

            $('#saveQualification').on('click', function() {

                let form = document.getElementById('qualificationForm');
                let formData = new FormData(form);

                // Only include file if the checkbox is checked
                $('#qualificationForm input[type="checkbox"]').each(function() {
                    let courseId = $(this).val();
                    if (!$(this).is(':checked')) {
                        formData.delete(`qualifications[${courseId}][cert]`);
                    }
                });

                $.ajax({
                    url: "{{ route('staff.qualifications.store') }}", // create this route
                    type: "POST",
                    data: formData,
                    processData: false, // IMPORTANT
                    contentType: false, // IMPORTANT
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // alert(response.message);
                        $('#qualificationModal').modal('hide');
                        form.reset();
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Something went wrong');
                        // console.log(xhr.responseText);
                    }
                });

            });

        });
        // WORKING HOURS CODE START - ROHAN
        $("#schedule_pattern").change(function() {
            let val = $(this).val();
            let userId = $("#carer_id").val();
            // console.log("LOAD FIRST");

            $.ajax({
                url: "{{ route('roster.carer.availability.loadworkinghours') }}",
                type: 'POST',
                data: {
                    userId: userId,
                    type: val,
                    _token: "{{ @csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(res) {
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    // console.log(res.data);

                    if (res.status) {
                        $("#resetWorkingHrsBtn").trigger('click');
                        // loadOverviewData();
                        let array1 = [];
                        workingPreferences = {
                            max_per_day: res.data.work_preferences ? res.data
                                .work_preferences.max_per_day : null,
                            max_per_week: res.data.work_preferences ? res.data
                                .work_preferences.max_per_week : null,
                            postcode: res.data.work_preferences ? res.data
                                .work_preferences.postcode : ''
                        };
                        array1 = res.data.working_hours;
                        staffDetails = res.data.staff;
                        // console.log(staffDetails);
                        total_working_week_1 = staffDetails.week_1_sum != null ?
                            Number(staffDetails.week_1_sum).toFixed(2) :
                            "0.00";

                        total_working_week_2 = staffDetails.week_2_sum != null ?
                            Number(staffDetails.week_2_sum).toFixed(2) :
                            "0.00";
                        $("#total_working_week_1").val(total_working_week_1);
                        $("#total_working_week_2").val(total_working_week_2);
                        $("#summaryCard").html(
                            `<button class="greenShowbtn"><i class='bx  bx-history'></i>${staffDetails.working_hrs_per_week}</button>
                                                ${staffDetails.specific_total_working_hours_sum ? `<span class="careBadg muteBadges">No working hours set</span>`:''}`
                        );
                        setWorkingHours = array1;
                        loadWorkingHrsData(array1, val);
                        // track.appendChild(renderMonth(new Date(), res.data
                        //     .working_hours));
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    // Code to run if the request fails
                }
            });


            // if (val == 'standard') {
            //     loadWorkingHrsData(userWorkingHours, val);
            // }
        });

        $("#schedule_pattern_2").change(function() {
            let val = $(this).val();
            // let totalAlternate = "0.0";
            let weekLabel = "Week 1";
            if (val == '1') {
                $(".week_1").removeClass('d-none');
                $(".week_2").addClass('d-none');
                totalAlternate = $("#total_working_week_1").val() || "0.0";
                current_week_counts = $(".week_1 .dayRow.active").length;

                weekLabel = "Week 1";
            } else {
                $(".week_2").removeClass('d-none');
                $(".week_1").addClass('d-none');
                totalAlternate = $("#total_working_week_2").val() || "0.0";
                current_week_counts = $(".week_2 .dayRow.active").length;
                weekLabel = "Week 2";
            }
            week_1_counts = $(".week_1 .dayRow.active").length;

            week_2_counts = $(".week_2 .dayRow.active").length;

            $("#workHoursPerWeekText")
                .css("color", "#2563eb")
                .text(totalAlternate + " hrs/week");
            $('.workingHoursDifferentSchedules').html(
                `<p>You are editing <strong> ${weekLabel}</strong> of the alternating schedule. These
                                                hours will repeat every other week. Switch between Week 1 and Week 2 above
                                                to set different schedules.</p>
                                            <div class="debugWeek mt-2">Debug: Week1 enabled days: ${week_1_counts} | Week2 enabled days:
                                                 ${week_2_counts} | Current enabled days: ${current_week_counts}</div>`
            );
        });

        function setWorkingHoursData() {
            let userId = $("#carer_id").val();

            if ($("#working_hours_type").val()) {
                // console.log($("#working_hours_type").val());

                $("#schedule_pattern").val($("#working_hours_type").val().trim()).trigger('change');
            }
        }
        // Load & Set WOrking Hours
        function loadWorkingHrsData(workingHrs, selectTabs) {
            $("#unavailabilityFormError").addClass('d-none').html('');
            const tabs1 = {
                standard: document.getElementById("tab-standard"),
                alternate: document.getElementById("tab-alternate"),
                specific: document.getElementById("tab-specific"),
            };
            let selected_tab_standard = $("#tab-" + selectTabs);
            Object.values(tabs1).forEach((tab) => (tab.style.display = "none"));
            tabs1[selectTabs].style.display = "block";

            document.getElementById("editing_week").style.display =
                selectTabs === "alternate" ? "block" : "none";
            if (selectTabs === 'standard') {
                //console.log(workingHrs, selectTabs);
                workingHrs.forEach(function(item) {

                    let day = item.day.toLowerCase();
                    let start_time = item.start_time.substring(0, 5);
                    let end_time = item.end_time.substring(0, 5);
                    // let end_time = item.end_time.substring(0, 5);
                    // let end_time = item.end_time.substring(0, 5);

                    // checkbox se match karo
                    let dayRow = selected_tab_standard.find(`.dayToggle[data-daysname="${day}"]`).closest(
                        ".dayRow");

                    // if (item.is_working == 0) {
                    //     dayRow.removeClass("active");
                    //     dayRow.find(".dayToggle").prop("checked", false);
                    //     dayRow.find(".workingFields").hide();
                    //     dayRow.find(".notWorking").show();
                    // } else {
                    // }
                    let startDate = new Date(`1970-01-01T${start_time}`);
                    let endDate = new Date(`1970-01-01T${end_time}`);

                    dayRow.addClass("active");
                    dayRow.find(".dayToggle").attr("data-workinghrsid", item.id).prop("checked", true);
                    dayRow.find(".workingFields").show();
                    dayRow.find(".notWorking").hide();
                    dayRow.find(".startTime").val(start_time);
                    dayRow.find(".endTime").val(end_time);
                    let diff = (endDate - startDate) / 1000 / 60 / 60;
                    dayRow.find(".hours")
                        .attr("data-hrsdiffval", diff.toFixed(1))
                        .text(diff.toFixed(1) + " hrs");
                });
            } else if (selectTabs === 'alternate') {
                // console.log(workingHrs, selectTabs);
                workingHrs.forEach(function(item) {

                    let day = item.day.toLowerCase();
                    let week_number = item.week_number;
                    let start_time = item.start_time.substring(0, 5);
                    let end_time = item.end_time.substring(0, 5);

                    let week_number_text = '.week_' + week_number;
                    // checkbox se match karo
                    // let dayRow = selected_tab_standard.find(`${week_number_text} .dayToggle[data-daysname="${day}"]`).closest(
                    //     `.dayRow`);
                    let dayRow = selected_tab_standard
                        .find(`${week_number_text} .dayRow`)
                        .find(`.dayToggle[data-daysname="${day}"]`)
                        .closest('.dayRow');
                    let startDate = new Date(`1970-01-01T${start_time}`);
                    let endDate = new Date(`1970-01-01T${end_time}`);

                    dayRow.addClass("active");
                    dayRow.find(".dayToggle").attr("data-workinghrsid", item.id).prop("checked", true);
                    dayRow.find(".workingFields").show();
                    dayRow.find(".notWorking").hide();
                    dayRow.find(".startTime").val(start_time);
                    dayRow.find(".endTime").val(end_time);
                    let diff = (endDate - startDate) / 1000 / 60 / 60;
                    dayRow.find(".hours")
                        .attr("data-hrsdiffval", diff.toFixed(1))
                        .text(diff.toFixed(1) + " hrs");
                    let temp_current_week_counts = $(".week_1 .dayRow.active").length;
                    let temp_week_1_counts = $(".week_1 .dayRow.active").length;
                    let temp_week_2_counts = $(".week_2 .dayRow.active").length;
                    let temp_weekLabel = "Week 1";
                    $(".workingHoursDifferentSchedules").html(
                        `<p>You are editing <strong> ${temp_weekLabel}</strong> of the alternating schedule. These
                                                hours will repeat every other week. Switch between Week 1 and Week 2 above
                                                to set different schedules.</p>
                                            <div class="debugWeek mt-2">Debug: Week1 enabled days: ${temp_week_1_counts} | Week2 enabled days:
                                                 ${temp_week_2_counts} | Current enabled days: ${temp_current_week_counts}</div>`,
                    );
                });
            } else if (selectTabs === 'specific') {
                //  console.log(workingHrs, selectTabs);

                workingHrs.forEach(function(item) {
                    const hoursList1 = document.getElementById("hoursList");
                    let start_time = item.start_time;
                    let end_time = item.end_time;
                    let start_date = item.start_date;
                    const label1 = new Date(start_date).toLocaleDateString("en-US", {
                        weekday: "short",
                        month: "short",
                        day: "numeric",
                        year: "numeric",
                    });
                    // checkbox se match karo
                    dayCard = selected_tab_standard.find(`.dayCard[data-date="${start_date}"]`)
                        .addClass("active");
                    selectedDates[start_date] = label1;
                    renderHours();
                    let startDate = new Date(`1970-01-01T${start_time}`);
                    let endDate = new Date(`1970-01-01T${end_time}`);
                    let selectedHoursRow = selected_tab_standard.find(`.hourRow[data-date="${start_date}"]`);
                    // console.log(selectedHoursRow);

                    selectedHoursRow.attr("data-specificid", item.id);
                    selectedHoursRow.find(".startTime").val(start_time);
                    selectedHoursRow.find(".endTime").val(end_time);
                    let diff = (endDate - startDate) / 1000 / 60 / 60;
                    selectedHoursRow.find(".hours")
                        .attr("data-hrsdiffval", diff.toFixed(1))
                        .text(diff.toFixed(1) + " hrs");
                });
            }
            calculateTotalHours();
            // $("#workHoursPerWeekText").text("10.0 hrs/week");
        }
        // SAVE WORKING HRS
        $("#saveWorkingHrsBtn").click(function() {
            $("#unavailabilityFormError").addClass('d-none').html('');
            let schedule_pattern = $("#schedule_pattern").val();
            let schedule_pattern_2 = $("#schedule_pattern_2").val();
            let carer_id = $("#carer_id").val();
            // console.log(schedule_pattern);
            let selectedActiveTab = $("#tab-" + schedule_pattern);
            // console.log(selectedActiveTab);
            let activeRows = ".dayRow.active";
            if (schedule_pattern === "specific") {
                activeRows = ".hourRow";
            }
            let arr = [];
            let isValid = true;
            let totalHrs = 0;
            let maxPerDay = workingPreferences.max_per_week || null;
            let errMsg = '';
            if (schedule_pattern === "alternate") {
                selectedActiveTab.find('.week_1 .dayRow.active').each(function() {
                    activeDays1 = $(this).find(".dayToggle").attr(
                        "data-daysname");
                    workinghrsid1 = $(this).find(".dayToggle").attr(
                        "data-workinghrsid");
                    endTime1 = $(this).find(".endTime").val();
                    startTime1 = $(this).find(".startTime").val();

                    if (!startTime1 || !endTime1) {
                        $(this).find(".day-error").remove();
                        errMsg = 'Start time and end time are required';
                        isValid = false;
                        return;
                    }
                    arr.push({
                        id: workinghrsid1 ?? null,
                        activeDays: activeDays1,
                        startTime: startTime1,
                        endTime: endTime1,
                        week_number: 1,
                    });
                });
                selectedActiveTab.find('.week_2 .dayRow.active').each(function() {
                    activeDays2 = $(this).find(".dayToggle").attr(
                        "data-daysname");
                    workinghrsid2 = $(this).find(".dayToggle").attr(
                        "data-workinghrsid");
                    endTime2 = $(this).find(".endTime").val();
                    startTime2 = $(this).find(".startTime").val();
                    if (!startTime2 || !endTime2) {
                        $(this).find(".day-error").remove();
                        errMsg = 'Start time and end time are required';
                        isValid = false;
                        return;
                    }
                    arr.push({
                        id: workinghrsid2 ?? null,
                        activeDays: activeDays2,
                        startTime: startTime2,
                        endTime: endTime2,
                        week_number: 2,
                    });
                });

                if (arr.length == 0) {
                    $(this).find(".day-error").remove();
                    errMsg = 'Please select at least one working day and time.';
                    isValid = false;
                    alertMsg('err', errMsg);
                    return;
                }
                // remove error if valid
            } else {
                selectedActiveTab.find(activeRows).each(function() {
                    // let hrs = $(this).find(".hours").attr("data-hrsdiffval");
                    let activeDays = '';
                    let workinghrsid = null;
                    let endTime = '';
                    let startTime = '';
                    totalHrs += parseInt($(this).find(".hours").attr("data-hrsdiffval"));
                    if (schedule_pattern === "standard") {
                        activeDays = $(this).find(".dayToggle").attr("data-daysname");
                        workinghrsid = $(this).find(".dayToggle").attr("data-workinghrsid");
                        endTime = $(this).find(".endTime").val();
                        startTime = $(this).find(".startTime").val();
                        arr.push({
                            id: workinghrsid ?? null,
                            activeDays: activeDays,
                            startTime: startTime,
                            endTime: endTime
                        });
                    } else {
                        activeDays = $(this).attr("data-date");
                        workinghrsid = $(this).attr("data-specificid");
                        endTime = $(this).find(".endTime").val();
                        startTime = $(this).find(".startTime").val();
                        arr.push({
                            id: workinghrsid ?? null,
                            activeDays: activeDays,
                            startTime: startTime,
                            endTime: endTime
                        });
                        // console.log(activeDays + " - " + startTime + " - " + endTime);
                    }

                    // validation
                    if (!startTime || !endTime) {
                        $(this).find(".day-error").remove();
                        errMsg = 'Start time and end time are required';
                        isValid = false;
                        return;
                    }

                    let startDate = new Date(`1970-01-01T${startTime}`);
                    let endDate = new Date(`1970-01-01T${endTime}`);

                    if (endDate <= startDate) {
                        $(this).find(".day-error").remove();
                        $(this).append(
                            `<div class="day-error text-danger">End time must be greater than start time</div>`
                        );
                        isValid = false;
                        return;
                    }

                    // remove error if valid
                    $(this).find(".day-error").remove();

                });
            }
            if (maxPerDay && schedule_pattern !== "specific" && totalHrs > maxPerDay) {

                errMsg =
                    `Total hours (${totalHrs.toFixed(1)}) exceed the weekly limit (${maxPerDay.toFixed(1)} hrs).`

                isValid = false;
            }
            if (arr.length == 0) {
                errMsg = 'Please select at least one working day and time.';
                isValid = false;
            }
            if (!isValid) {
                alertMsg('err', errMsg);
                return;
            };
            // console.log(arr);
            // return;
            $("#workingHoursFormError").addClass('d-none alert-success');
            let formData = new FormData();
            //     console.log(arr);
            // return;

            formData.append('type', schedule_pattern);
            formData.append('carer_id', carer_id);
            formData.append('working_hours', JSON.stringify(arr));

            // console.log(formData);
            $.ajax({
                url: "{{ route('roster.carer.availability.save_working_hrs') }}", // URL to send the request to
                type: 'POST', // or 'POST'
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#saveWorkingHrsBtn").prop('disabled', true).html('Please wait...');
                },
                success: function(res) {
                    $("#saveWorkingHrsBtn").prop('disabled', false).html(
                        'Save Working Hours');
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    $("#schedule_pattern").val(schedule_pattern).change();
                    alertMsg('suc', res.message);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $("#saveWorkingHrsBtn").prop('disabled', false).html(
                        'Save Working Hours');
                    let errorMSg = xhr.responseJSON && xhr.responseJSON.message ? xhr
                        .responseJSON.message : 'An error occurred';
                    alertMsg('err', errorMSg);
                    // Code to run if the request fails
                }
            });

        });

        $("#unavailability_type").change(function() {
            let val = $(this).val();
            if (val == 'range') {
                $("#unavailability_end_date_wrapper").removeClass('d-none');
                $("#start_date_label").text('Start Date');
                $("#unavailability_start_time_wrapper").addClass('d-none');
                $("#unavailability_end_time_wrapper").addClass('d-none');
                $("#unavailability_start_time").val('');
                $("#unavailability_end_time").val('');
            } else {
                $("#start_date_label").text('Date');
                $("#unavailability_end_date_wrapper").addClass('d-none');
                $("#unavailability_end_time_wrapper").removeClass('d-none');
                $("#unavailability_start_time_wrapper").removeClass('d-none');
                $("#unavailability_end_date").val('');
            }
        });

        $("#addUnavailabilityBtn").click(function() {
            let carer_id = $("#carer_id").val();

            // return false;
            $.ajax({
                url: "{{ route('roster.carer.availability.save_unavailability') }}", // URL to send the request to
                type: 'POST', // or 'POST'
                data: $("#unavailabilityForm").serialize() + '&carer_id=' + carer_id,
                beforeSend: function() {},
                success: function(res) {
                    $("#unavailabilityForm").trigger('reset');
                    $("#unavailabilityFormError").addClass('d-none').html('');
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if (res.status) {
                        alertMsg('suc', res.message);
                        $('.addalertClientDetailsform').removeClass('active');
                        loadUnavailability();
                    } else {
                        // alert('Failed to save preferences. Please try again.');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    $("#unavailabilityFormError").addClass('d-none').html('');
                    let errorMSg = xhr.responseJSON && xhr.responseJSON.message ? xhr
                        .responseJSON.message : 'An error occurred';
                    if (xhr.status == 422) {
                        let htm = '';
                        $.each(errorMSg, function(key, value) {
                            htm += `<li>${value[0]}</li>`;
                        });
                        $("#unavailabilityFormError").removeClass(
                                'd-none alert-success')
                            .addClass('alert-danger').html(`<ul>${htm}</ul>`);

                    } else {
                        alertMsg('err', errorMSg);
                    }
                    setTimeout(() => {
                        $("#workPreferencesFormError").addClass('d-none').html('');
                    }, 3000);
                }
            });
        });
        $(document).on('click', '.deleteUnavailabilityBtn', function() {
            let confirmation = confirm("Are you sure you want to delete this unavailability?");
            if (!confirmation) {
                return false;
            }
            let id = $(this).attr('data-id');
            $.ajax({
                url: "{{ route('roster.carer.availability.delete_unavailability') }}", // URL to send the request to
                type: 'POST', // or 'POST'
                data: {
                    unavailability_id: id,
                    _token: "{{ @csrf_token() }}"
                },
                beforeSend: function() {},
                success: function(res) {
                    $("#unavailabilityFormError").addClass('d-none').html('');
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if (res.status) {
                        alertMsg('suc', res.message);
                        loadUnavailability();
                    } else {
                        // alert('Failed to save preferences. Please try again.');
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    let errorMSg = xhr.responseJSON && xhr.responseJSON.message ? xhr
                        .responseJSON.message : 'An error occurred';
                    alertMsg('err', errorMSg);
                }
            });

        });

        function loadUnavailability() {
            let carer_id = $("#carer_id").val();
            $.ajax({
                url: "{{ route('roster.carer.availability.load_unavailability_data') }}",
                type: 'POST',
                data: {
                    carer_id: carer_id,
                    _token: "{{ @csrf_token() }}"
                },
                beforeSend: function() {
                    $(".addUnavailabilityList").html(`<div class="blankCarer" id="defaultBlankCarerWrapper">
                                                <div class="leave-card"
                                                    style="height: 400px; margin-top:0px; display:flex; justify-content:center; align-items:center;">
                                                    <div class="leavebanktabCont blankdesign">
                                                        <i class="bx bx-calendar-x"></i>
                                                        <h4 class="font600">No unavailability periods set</h4>
                                                        <p class="textGray500">Add periods when this carer is not available for shifts</p>
                                                    </div>
                                                </div>
                                            </div>`);
                    $("#leave_request_main_wrapper").addClass('d-none');
                    $("#leave_request_wrapper").empty();
                },
                success: function(res) {
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if (res.status) {
                        unavailabilityHtml(res.data);
                        leaveDataHtml(res.leave_data);

                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    // Code to run if the request fails
                }
            });
        }

        function unavailabilityHtml(data) {
            if (data.length == 0) {
                $(".addUnavailabilityList").html(`<div class="blankCarer" id="defaultBlankCarerWrapper">
                                                <div class="leave-card"
                                                    style="height: 400px; margin-top:0px; display:flex; justify-content:center; align-items:center;">
                                                    <div class="leavebanktabCont blankdesign">
                                                        <i class="bx bx-calendar-x"></i>
                                                        <h4 class="font600">No unavailability periods set</h4>
                                                        <p class="textGray500">Add periods when this carer is not available for shifts</p>
                                                    </div>
                                                </div>
                                            </div>`);
                return;
            }
            let htm = '';
            $(".addUnavailabilityList").empty();
            $.each(data, function(key, value) {
                htm += `
                                        <div class="certifiedList">
                                            <span class="unavailabilityDateAndTime">
                                                <span class="caldrIcon">
                                                    <i class="bx  ${value.start_time?'bx-clock-4':'bx-calendar-x'}"></i>
                                                </span>
                                                <div class="">
                                                    <div><strong> ${value.formatted_date}</strong></div>`;
                if (value.formatted_time) {
                    htm += `<small>${value.formatted_time}</small>`;
                }
                htm += `</div>
                                            </span>
                                            <div class="planActions">
                                                <span class="cornorTags ${value.statusColor}"> ${value.status} </span>
                                                <button type="button" class="danger deleteUnavailabilityBtn" data-id="${value.id}"><i class="bx  bx-trash"></i> </button>
                                            </div>
                                        </div>
                                    `;
            });
            $(".addUnavailabilityList").html(htm);
        }

        function leaveDataHtml(data) {

            if (data.length == 0) {
                $("#leave_request_main_wrapper").addClass('d-none');
                $("#leave_request_wrapper").empty();
                return;
            }
            let htm = '';
            $("#leave_request_main_wrapper").removeClass('d-none');
            $.each(data, function(key, value) {
                htm += `<div class="bg-purple-50 bgWhite rounded5 p-3 mt-2">
                                                <div class="flexBw">
                                                    <div>
                                                        <h5 class="h5Head mb-2">${value.formatted_date}</h5>
                                                        <p class="textGray500 fs13 mb-0">${value.leave_name}</p>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg purpleBadges">Via Leave System</span>
                                                    </div>
                                                </div>
                                            </div>`;
            });
            $("#leave_request_wrapper").html(htm);
        }

        function fetch_supervisions(page = 1) {
            let statusVal = $("#statusChangeBtn option:selected").val();
            let carer_id = $("#carer_id").val();
            $("#superVisionHeaderBadge").addClass('d-none');
            $.ajax({
                url: "{{ route('roster.fetch_supervision.list') }}?page=" +
                    page + "&assinged_user_id=" +
                    carer_id, // URL to send the request to
                type: 'GET',
                beforeSend: function() {},
                success: function(res) {
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if (res.success) {
                        $("#supervision-pagination").empty();
                        $("#supervision-list-wrapper").empty();
                        let htm = '';
                        let superVisionHeaderBadgeHtml = '';
                        if (res.data.length == 0) {
                            $("#supervision-list-wrapper").html(`<div class="leavebanktabCont">
                                    <i class="bx  bx-clipboard-detail"></i>
                                    <p>No supervision records</p>
                                </div>`);
                            return;
                        }
                        $.each(res.data, function(index, item) {
                            if (index == 0) {
                                let colorText = item.status == 'On Track' ? "greenbadges" : (item
                                    .status == 'Due Soon' ? "yellowbadges" : "redbadges");
                                superVisionHeaderBadgeHtml = `<span class="careBadg ${colorText}"> Next:
                                        ${item.status} </span>`;
                            }
                            htm += `<div class="lightBorderp rounded8 p-4 hoverBg bottomSpace viewBtn" type="button" data-id="${item.id}">
                                    <div class="flexBw">
                                        <div>
                                            <h6 class="h6Head mb-3"> <i
                                                    class="bx bx-calendar-week fs18 textGray500 me-2"></i> ${item.date}
                                                <span class="borderBadg ms-2">${item.supervision_type}</span>
                                            </h6>
                                            <p class="fs13 textGray500 mb-0">Supervised by ${item.supervisor_name}</p>
                                        </div>
                                        <div>
                                            <i class="bx bx-eye fs16"></i>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        // console.log(htm);
                        $("#superVisionHeaderBadge").removeClass('d-none').html(superVisionHeaderBadgeHtml);
                        $("#supervision-list-wrapper").html(htm);
                        let p = res.pagination;
                        let paginationHtml = '';

                        $("#current_page").val(p.current_page < p.total_pages ? p.current_page : 1);

                        if (p.total_pages > 1) {
                            paginationHtml += `
                                    <button class="page-btn btn ${p.current_page == 1 ? 'disabled' : ''}"
                                        data-page="${p.current_page - 1}">
                                        Prev
                                    </button>`;
                            for (let i = 1; i <= p.total_pages; i++) {

                                paginationHtml += `
                                        <button class="page-btn btn ${i == p.current_page ? 'btn-primary disabled' : ''}"
                                            data-page="${i}">
                                            ${i}
                                        </button>`;
                            }

                            paginationHtml += `
                                    <button class="page-btn btn ${p.current_page == p.total_pages ? 'disabled' : ''}"
                                        data-page="${p.current_page + 1}">
                                        Next
                                    </button>
                                `;
                        }

                        $("#supervision-pagination").html(paginationHtml);

                    } else {
                        $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                                        <div class="
                                        leavebanktabCont p24">
                                            <i class="bx bx-file-detail"></i>
                                            <p class="mt-3">No supervision records</p>
                                        </div>
                                    </div>`);
                        return;
                    }

                },
                error: function(xhr, ajaxOptions, thrownError) {

                    if (xhr.status == 404) {
                        $("#supervision-pagination").empty();
                        $("#supervision-list-wrapper").html(`<div class="emergencyContent emergencyMain AllStaffTabC p-4 blueAllTabCard mt-4"">
                                    <div class="
                                    leavebanktabCont p24">
                                        <i class="bx bx-file-detail"></i>
                                        <p class="mt-3">No supervision records</p>
                                    </div>
                                </div>`);
                    }
                }
            });

        }
        $(document).on('click', '.viewBtn', function() {

            let id = $(this).data('id');

            $.ajax({
                url: "{{ route('roster.supervision.details') }}", // URL to send the request to
                type: 'POST', // or 'POST'
                data: {
                    id: id,
                    _token: "{{ @csrf_token() }}"
                }, // Data to send with the request
                beforeSend: function() {},
                success: function(res) {
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if (res.status) {
                        openSuperVisionModal(res.data);
                    } else if (res === '"unauthorize"') {
                        alert('You have not access rights for this page');
                        return;
                    } else {
                        alert(res.message ?? "Something went wrong !!");
                        return;
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    if (xhr.status === 500) {
                        alert('Internal Server Error');
                    } else if (xhr.status === 422) {
                        alert(xhr.responseJSON.message ?? "Data Not Found !!")
                    }
                }
            });



        });

        function openSuperVisionModal(el) {
            $("#supervisionDetailsWrapper").empty();
            $("#supervisionFooterDetailsWrapper").empty();
            let attachmentsHtml = '';
            let attachmentsCount = el.attachments.length || 0;
            $.each(el.attachments, function(key, item) {

                attachmentsHtml += `<div class="lightBorderp p-3 rounded8 mt-3">
                                    <div class="dFlexGap">
                                        <div class="fileBoxSuper">
                                            <i class="bx bx-file f20"></i>
                                        </div>
                                        <div>
                                            <p class="fs13 mb-2 font700">
                                                ${item.doc_name}
                                            </p>
                                            <div class="dFlexGap">
                                                <p class="mb-0 fs12 textGray">${item.created_at}
                                                </p>

                                            </div>
                                        </div>
                                    </div>
                                </div>`;
            });
            // console.log(attachmentsHtml);
            colorText = el.status == 'On Track' ? "greenbadges" : (el
                .status == 'Due Soon' ? "yellowbadges" : "redbadges");
            let html = `<div class="col-md-6">
                                    <p class="muteText mb-2">Staff Member</p>
                                    <p class="h7Head mb-0">${el.member_name}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="muteText mb-2">Supervisor</p>
                                    <p class="h7Head mb-0">${el.supervisor_name}</p>
                                </div>
                                <div class="col-md-6 m-t-10">
                                    <p class="muteText mb-2">Date</p>
                                    <p class="h7Head mb-0">${el.date}</p>
                                </div>
                                <div class="col-md-6 m-t-10">
                                    <p class="muteText">Type</p>
                                    <div> <span class="careBadg ${colorText}">${el.status}</span> </div>
                                </div>
                                <div class="col-md-12 m-t-10">
                                    <p class="muteText">Supervisor Note</p>
                                    <div class="muteBg rounded8 p-3">
                                        <p class="mb-0 text-sm para">${el.note}</p>
                                    </div>
                                </div>`;
            if (el.type === 'record') {
                html += `<div class="col-md-12 m-t-10"><p class="muteText">Supervisor Comments</p>
                                    <div class="lightBlueBg rounded8 p-3">
                                        <p class="mb-0 text-sm para">${el.comment}</p>
                                    </div>
                                </div>`;
            }
            if (attachmentsCount > 0) {
                html += `<div class="col-md-12 m-t-10">
                            <label for="">Attached Documents</label>
                            <div class="emergencyMain p24">
                                <div class="dFlexGap mb-3"> <i class="bx bx-link fs23"></i>
                                    <h5 class="mb-0 h5Head">
                                        Attached Documents
                                    </h5>
                                    <div class="userMum">
                                        <span>${attachmentsCount}</span>
                                    </div>
                                </div>
                                    ${attachmentsHtml}
                            </div>
                        </div>`;
            }
            let footerText = `<div>
                                <p class="muteText text-left mb-2">Next Supervision Due</p>
                                <p class="h7Head text-left">
                                   ${el.next_due}
                                </p>
                            </div>`;


            $("#supervisionFooterDetailsWrapper").html(footerText);
            $("#supervisionDetailsWrapper").html(html);
            $("#superDetailModal").modal('show');
        }
        $(document).on('click', '.page-btn', function() {
            let page = $(this).data('page');
            fetch_supervisions(page);
        });
    </script>
    @endsection
</main>