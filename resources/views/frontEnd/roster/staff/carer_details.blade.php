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
    </style>
    <main class="page-content empoyeeHeader">
        <div class="topHeaderCont">
            <div>
                <h1>Employee: {{ $staffDetails->name }}</h1>
                <p class="header-subtitle">
                    <span>Inactive</span>
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
                    <button class="tab" data-tab="trainingQualificationsTab">Training & Qualifications <span class="tabNumber">{{ count($staffDetails->qualifications) }}</span></button>
                    <button class="tab" data-tab="supervisionsTab">Supervisions</button>
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
                                            <span class="value">£{{ $staffDetails->pay_rate ?? '0.00' }}</span>
                                        </div>
                                        <div class="item">
                                            <span class="label">Status</span>
                                            <span class="value">{{ $staffDetails->status ? 'Active' : 'Inactive' }}</span>
                                        </div>
                                        <div class="item">
                                            <span class="label">Overtime Available</span>
                                            <span class="value">{{ $staffDetails->overtime_available ? 'Yes' : 'No' }}</span>
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
                                            <span class="value">{{ $staffDetails->emergencyContact->phone_no ?? '' }}</span>
                                        </div>
                                        <div class="item">
                                            <span class="label">Relationship</span>
                                            <span class="value">{{ $staffDetails->emergencyContact->relationship ?? '' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="divider"></div>

                                <div class="section">
                                    <h3>DBS Information</h3>
                                    <div class="item">
                                        <span class="label">DBS Expiry</span>
                                        <span class="value">{{ $staffDetails->dbs_expiry_date ? \Carbon\Carbon::parse($staffDetails->dbs_expiry_date)->format('F jS, Y') : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content" id="availabilityTab">
                        <div class="availabilityTabs">
                            <!-- TAB HEADER -->
                            <div class="availabilityTabs__nav">
                                <button class="availabilityTabs__tab active" data-target="workHoursPanel">Working Hours</button>
                                <button class="availabilityTabs__tab" data-target="unavailabilityPanel">Unavailability</button>
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
                                                <span class="badge">8.0 hrs/week</span>
                                                <button class="btn"> <i class='bx  bx-copy'></i> Apply Mon to Weekdays</button>
                                                <button class="btn-outline"><i class='bx  bx-rotate-ccw'></i> Reset</button>
                                            </div>
                                        </div>
                                        <div class="schedulePattern">
                                            <div class="col-md-6 mb-3">
                                                <label for="schedule_pattern">Schedule Pattern</label>
                                                <select class="form-control" id="schedule_pattern">
                                                    <option value="standard">Standard Weekly Pattern</option>
                                                    <option value="alternate">Alternate Weeks</option>
                                                    <option value="specific">Choose Specific Dates (next 60 days)</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3" id="editing_week" style="display:none;">
                                                <label for="schedule_pattern_2">Editing Week</label>
                                                <select class="form-control" id="schedule_pattern_2">
                                                    <option value="week_1">Week 1</option>
                                                    <option value="week_2">Week 2</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Standard Weekly -->
                                        <div id="tab-standard">
                                            <div class="dayRow active">
                                                <span class="day">Monday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle" checked>
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking" style="display:none;">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Tuesday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Wednesday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Thursday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Friday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow weekend">
                                                <span class="day">Saturday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow weekend">
                                                <span class="day">Sunday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                        </div>

                                        <!-- Alternate Weeks -->
                                        <div id="tab-alternate">
                                            <div class="alternateInfoBox">
                                                <div class="alternateInfoText">
                                                    <strong>You are editing <span class="highlight">Week 1</span> of the alternating schedule.</strong>
                                                    These hours will repeat every other week.
                                                    Switch between <strong>Week 1</strong> and <strong>Week 2</strong> above to set different schedules.
                                                </div>
                                                <div class="alternateDebug">
                                                    Debug: Week1 enabled days: <strong>7</strong> |
                                                    Week2 enabled days: <strong>7</strong> |
                                                    Current enabled days: <strong>7</strong>
                                                </div>
                                            </div>

                                            <div class="dayRow active">
                                                <span class="day">Monday</span>
                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle" checked>
                                                    <span class="slider"></span>
                                                </label>
                                                <div class="workingFields">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>
                                                <span class="notWorking" style="display:none;">Not working</span>
                                            </div>

                                            <div class="dayRow">
                                                <span class="day">Tuesday</span>
                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Wednesday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Thursday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow">
                                                <span class="day">Friday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow weekend">
                                                <span class="day">Saturday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                            <div class="dayRow weekend">
                                                <span class="day">Sunday</span>

                                                <label class="switch">
                                                    <input type="checkbox" class="dayToggle">
                                                    <span class="slider"></span>
                                                </label>

                                                <div class="workingFields" style="display:none;">
                                                    <input type="time" value="09:00" class="dayTime form-control startTime">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control endTime">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <span class="notWorking">Not working</span>
                                            </div>
                                        </div>

                                        <!-- Specific Dates -->
                                        <div id="tab-specific" class="availabilityScroller">

                                            <!-- Header -->
                                            <p class="helperText">
                                                Select the specific dates over the next 60 days when this carer is available to work:
                                            <div><span class="selectedCount" id="selectedCount">0 dates selected</span></div>
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
                                            <button class="btn allBtnUseColor validation_staff" type="submit"> Save Working Hours </button>
                                        </div>

                                    </div>
                                </div>

                                <!-- UNAVAILABILITY -->
                                <div class="availabilityTabs__panel" id="unavailabilityPanel">
                                    <div class="leave-card">
                                        <div class="workHoursHeader">
                                            <div class="title"><i class='bx  bx-calendar-alt-2'></i> Unavailability Periods</div>
                                            <div class="actions">
                                                <button class="allbuttonDarkClr addalertClientDetailsBtn"> <i class='bx  bx-plus'></i> Add Unavailability</button>
                                            </div>
                                        </div>
                                        <div class="p-20">
                                            <div class="clientFilterform addalertClientDetailsform">
                                                <form action="">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Type</label>
                                                                <select class="form-control">
                                                                    <option>Single Day</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Date</label>
                                                                <input type="Date" class="form-control" name="" placeholder="dd/mm/yyyy">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>From Time (optional)</label>
                                                                <input type="time" class="form-control" name="" placeholder="dd/mm/yyyy">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>To Time (optional)</label>
                                                                <input type="time" class="form-control" name="" placeholder="dd/mm/yyyy">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>Reason (optional)</label>
                                                                <textarea name="short_description" required="" class="form-control" rows="3" cols="20" placeholder="e.g., Personal appointment, Training, etc."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 text-center">
                                                            <button class="btn allBtnUseColor image_val" type="submit"> Add Unavailability </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="leavebanktabCont">
                                            <i class="fa fa-calendar-o"></i>
                                            <h4>No clients found</h4>
                                            <p>Add your first client to get started</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- SUMMARY -->
                                <div class="availabilityTabs__panel" id="summaryPanel">
                                    <div class="">
                                        <div class="workHoursHeader">
                                            <div class="actions">
                                                <button class="greenShowbtn"><i class='bx  bx-history'></i> 0 days • 0h/week</button>
                                                <span class="badge">No working hours set</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
                                                <div class="tabs p-1 ">
                                                    <button class="tab active" data-tab="workingHoursTab">
                                                        Working Hours
                                                    </button>
                                                    <button class="tab" data-tab="unavailabilityTab">
                                                        Unavailability
                                                    </button>
                                                    <button class="tab" data-tab="summaryTab">
                                                        Summary
                                                    </button>
                                                </div>

                                                <div class="tab-content carertabcontent">
                                                    <div class="content active" id="workingHoursTab">
                                                        <div class="sectionWhiteBgAllUse">
                                                                <h1> Working Hours</h1>
                                                        </div>
                                                    </div>
                                                    <div class="content" id="unavailabilityTab">
                                                        <h1>Unavailability</h1>
                                                    </div>
                                                    <div class="content" id="summaryTab">
                                                        <h1>Summary</h1>
                                                    </div>
                                                </div>
                                            </div> -->
                    </div>

                    <div class="content" id="trainingQualificationsTab">
                        <div class="leave-card">
                            <div class="workHoursHeader">
                                <div class="title">Qualifications</div>
                                <div class="actions">
                                    <button class="allbuttonDarkClr" data-user-id="{{ $staffDetails->id }}" id="addQualificationBtn"> <i class='bx bx-education'></i> Add Qualification</button>
                                </div>
                            </div>

                            <div class="">
                                @forelse ($staffDetails->qualifications as $qualification)
                                    <div class="certifiedList">
                                        <span>{{ $qualification['name'] }}</span>

                                        @if (!empty($qualification['image']))
                                            <span class="roundBtntag greenShowbtn">Certified</span>
                                        @else
                                            <span class="roundBtntag greenShowbtn">Not Certified</span>
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

                    <div class="content" id="supervisionsTab">
                        <div class="leave-card">
                            <div class="workHoursHeader">
                                <div class="title"><i class="bx  bx-clipboard-detail"></i> Supervision History</div>
                            </div>
                            <div class="leavebanktabCont">
                                <i class="bx  bx-clipboard-detail"></i>
                                <p>No supervision records</p>
                            </div>
                        </div>
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
                                    <button class="allbuttonDarkClr openUploadDocumentModal" data-id="{{ $staffDetails->id }}"> <i class='bx bx-file-detail'></i> Upload Document</button>
                                </div>
                            </div>
                            <div class="">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="uploadDocumentCont">
                                            @forelse ($user_documents as $documents)
                                                <div class="certifiedList">
                                                    <span class="showDocumentname">
                                                        <span>{{ $documents['title'] }}</span>
                                                    </span>
                                                    <div class="planActions">
                                                        <a href="{{ asset('storage/app/public/' . $documents['file_path']) }}" class="viewDocOpen"><i class='bx bx-eye'></i> </a>
                                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
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
                                    <button class="allbuttonDarkClr openNotesModal" data-id="{{ $staffDetails->id }}"> <i class='bx bx-file-detail'></i> Add Note</button>
                                </div>
                            </div>
                            <div class="addNoteContentList">
                                    @forelse ($user_notes as $note)
                                    <div class="certifiedList planActions">
                                        <span class="noteDateAndText">
                                            <div><strong>Date :</strong>  {{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y') }}</div>
                                            <small>{{ $note['note'] }}</small>
                                        </span>
                                        <button class="danger delete-note" data-id="{{ $note['id'] }}"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                    @empty
                                    <p>No notes recorded</p>
                                 @endforelse
                            </div>
                            <!-- <div class="leavebanktabCont">
                                <div class="row">
                                    @forelse ($user_notes as $note)
                                        <div class="col-md-12">
                                            <div class="notContDetails carePlanWrapper">
                                                <div class="planCard">
                                                    <div class="planTop">
                                                        <div class="planMeta">
                                                            <div>
                                                                <strong>Date:</strong>
                                                                {{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y') }}
                                                            </div>
                                                        </div>
                                                        <div class="planActions">
                                                            <button class="danger delete-note" data-id="{{ $note['id'] }}">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <div class="planFooter">
                                                        <span>{{ $note['note'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <p>No notes recorded</p>
                                    @endforelse
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <!-- END TAB CONTENT -->
            </div>
        </div>

        {{-- Upload Document Model --}}
        <div class="modal fade leaveCommunStyle" id="uploadDocumentModal" tabindex="-1" role="dialog" aria-hidden="true">
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
                                <input type="text" name="document_title" id="document_title" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Upload File</label>
                                <input type="file" name="document_file" id="document_file" class="form-control" required>
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
                                <input type="hidden" name="user_id" value="{{ $staffDetails->id }}" id="qualification_userId">
                                @foreach ($courses as $course)
                                    @php
                                        $qualification = $staffDetails->qualifications->where('course_id', $course['course_id'])->first();

                                        $isChecked = in_array($course['course_id'], $selectedCourseIds);
                                    @endphp

                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox"
                                                name="qualifications[{{ $course['course_id'] }}][course_id]"
                                                value="{{ $course['course_id'] }}"
                                                {{ $isChecked ? 'checked' : '' }}>
                                            {{ $course['title'] }}

                                            <input type="hidden"
                                                name="qualifications[{{ $course['course_id'] }}][name]"
                                                value="{{ $course['title'] }}">
                                        </label>

                                        {{-- Upload ALWAYS visible --}}
                                       
                                            <div class="">
                                                <input type="file"
                                                    name="qualifications[{{ $course['course_id'] }}][cert]"
                                                    class="qual_upload"
                                                    accept="application/pdf,.pdf">
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

        <script src="{{ asset('public/frontEnd/staff/js/working-hours.js') }}"></script>

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
        </script>

        <script>
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
        </script>

        <script>
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
        </script>

        <script>
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
                            button.closest('.col-md-6').remove();
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
                            location.reload();
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
        </script>

        <script>
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
                            console.log(xhr.responseText);
                        }
                    });

                });

            });
        </script>


    @endsection
</main>
