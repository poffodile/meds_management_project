@extends('frontEnd.layouts.master')
@section('title', 'Carer Availability')
@section('content')
    <link rel="stylesheet" href="{{ asset('public/frontEnd/staff/css/working-hours.css') }}">
    @include('frontEnd.roster.common.roster_header')

    <style>
        .sideTabWrapper {
            border-radius: 8px;
        }

        .sideTabWrapper .sectionWhiteBgAllUse.rightsideBtnTab {
            padding: 0px 2px 9px 0;
        }

        /* .sideTabs {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            width: 100%;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        } */
        .sideTabs .tab {
            width: 100%;
            text-align: left;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 14px;
            color: #333;
        }

        .sideTabContent {
            flex: 1;
            /* padding: 20px; */
        }

        .sideTabContent .content {
            display: none;
        }

        .sideTabContent .content.active {
            display: block;
        }

        .searchCareAvailability .input-group span {
            background: #fff;
            color: #d5d5d5;
            border: 1px solid #e2e2e4;
            border-right: 0;
        }

        .searchCareAvailability .input-group input {
            border-left: 0;
            background: #ffffff;
        }

        .searchCareAvailability label {
            font-size: 18px;
            color: #000;
            display: flex;
            gap: 10px;
        }

        .searchCareAvailability label i {
            font-size: 22px;
        }

        .sideTabs.suggestedCarers {
            padding-right: 14px;
            margin-top: 12px;
            max-height: 580px;
        }

        .sideTabs.suggestedCarers .tab .carerCard {
            background: #f9fafb;
            border: 2px solid #f0f0f000;
        }

        .sideTabs.suggestedCarers .tab.active .carerCard {
            background: #dbeafe;
            border: 2px solid #3b82f6;
        }

        .sideTabs.suggestedCarers .avatar {
            background: #3b82f6;
        }

        .sideTabs.suggestedCarers .tag.partial {
            background: #fef9c3;
            color: #ab7b0f;
            font-weight: 600;
        }

        .sideTabs.suggestedCarers .tag.available {
            background-color: #afeccb;
            color: #06af51;
            font-weight: 600;
        }

        .userNameAndDetails .status {
            padding: 12px;
            border-radius: 6px;
            display: flex;
            line-height: 12px;
            font-size: 13px;
            gap: 5px;
        }

        .sideTabWrapper .calendarTabs.employeeDetailsTabs .tabs {
            display: inline-flex;
            flex-grow: 4;
            width: 100%;
        }

        .purpleOnLeave {
            background: #a855f7;
            color: #fff
        }
    </style>

    <main class="page-content">
        <div class="container-fluid">
            <div class="topHeaderCont">
                <div>
                    <h1>Carer Availability</h1>
                    <p class="header-subtitle">Manage working hours, days off, and unavailability periods</p>
                </div>
            </div>

            <div class="sideTabWrapper">
                <div class="row">
                    <div class="col-md-3">
                        <div class="sectionWhiteBgAllUse rightsideBtnTab">
                            <div class="searchCareAvailability p-20 p-b-0">
                                <label><i class='bx  bx-user'></i> Carers</label>
                                <div class="input-group">
                                    <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" id="searchText"
                                        placeholder="Search carers...">
                                </div>
                            </div>
                            <div class="sideTabs suggestedCarers  p-20 p-t-0" id="usersListWrapper">
                                {{-- <button class="tab active" data-tab="overviewTab">
                                    <div class="carerCard">
                                        <div class="avatar">A</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag partial">Partial</span>
                                        </div>
                                    </div>
                                </button> --}}
                                {{-- <button class="tab" data-tab="scheduleTab">
                                    <div class="carerCard">
                                        <div class="avatar">B</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag available">Available</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="reportsTab">
                                    <div class="carerCard">
                                        <div class="avatar">C</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag partial">Partial</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="scheduleTab">
                                    <div class="carerCard">
                                        <div class="avatar">B</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag available">Available</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="reportsTab">
                                    <div class="carerCard">
                                        <div class="avatar">C</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag partial">Partial</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="scheduleTab">
                                    <div class="carerCard">
                                        <div class="avatar">B</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag available">Available</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="reportsTab">
                                    <div class="carerCard">
                                        <div class="avatar">C</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag partial">Partial</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="scheduleTab">
                                    <div class="carerCard">
                                        <div class="avatar">B</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag available">Available</span>
                                        </div>
                                    </div>
                                </button>
                                <button class="tab" data-tab="reportsTab">
                                    <div class="carerCard">
                                        <div class="avatar">C</div>
                                        <div class="details">
                                            <div class="topRow">
                                                <span class="name">Georgia Ashmore</span>
                                            </div>
                                            <span class="tag partial">Partial</span>
                                        </div>
                                    </div>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        {{-- FOR blank career --}}
                        <div class="blankCarer" id="defaultBlankCarerWrapper">
                            <div class="leave-card"
                                style="height: 400px; margin-top:0px; display:flex; justify-content:center; align-items:center;">
                                <div class="leavebanktabCont blankdesign">
                                    <i class="bx bx-user"></i>
                                    <h4 class="font600">Select a carer</h4>
                                    <p class="textGray500">Choose a carer from the list to manage their
                                        availability</p>
                                </div>
                            </div>
                        </div>
                        <div class="sectionWhiteBgAllUse d-none" id="carerUserProfileWrapper">
                            <div class="sideTabContent">
                                <div class="content active" id="overviewTab">
                                    <div class="carertabcontent">
                                        <div class="userNameAndDetails">

                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="content" id="scheduleTab">
                                    <div class="carertabcontent">
                                        <div class="userNameAndDetails">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="avatar">B</div>
                                                    <div class="info">
                                                        <div class="name"><a href="#!">Bikanes Carter</a></div>
                                                        <div class="role"> flipholt72@yahoo.co.uk</div>
                                                    </div>
                                                </div>
                                                <span class="status greenShowbtn">
                                                    <i class='bx  bx-clock-4'></i> 5 days • 50h/week
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content" id="reportsTab">
                                    <div class="carertabcontent">
                                        <div class="userNameAndDetails">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="avatar">C</div>
                                                    <div class="info">
                                                        <div class="name"><a href="#!">Crosti Carter</a></div>
                                                        <div class="role"> flipholt72@yahoo.co.uk</div>
                                                    </div>
                                                </div>
                                                <span class="status greenShowbtn">
                                                    <i class='bx  bx-clock-4'></i> 5 days • 50h/week
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>

                        <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20 d-none"
                            id="carerUserDataWrapper">

                            <div class="tabs p-1 m-b-0">
                                <button class="tab active" data-tab="overviewTab"><i class="bx bx-calendar"></i>
                                    Overview</button>
                                <button class="tab" data-tab="workingHoursTab"><i class='bx  bx-clock-4'></i> Working
                                    Hours</button>
                                <button class="tab" data-tab="unavailabilityTab"><i class='bx  bx-calendar-x'></i>
                                    Unavailability </button>
                                <button class="tab" data-tab="preferencesTab"><i class='bx  bx-cog'></i>
                                    Preferences</button>
                            </div>

                            <!-- TAB CONTENT -->
                            <div class="tab-content carertabcontent">
                                <div class="content active" id="overviewTab">
                                    {{-- no conflict --}}
                                    <div class="careInsightsWrap">
                                        <div class="bg-greenp-50 p-4 rounded12 shadowp">
                                            <div class="dFlexGap">
                                                <div class="greenBox dFlexGap justify-content-center">
                                                    <i class="bx bx-check f20" style="color:#ffffff;"></i>
                                                </div>
                                                <h6 class="h6Head mb-0 greenTextp font600">No conflicts detected</h6>
                                            </div>
                                        </div>
                                        {{-- no conflict end --}}


                                        {{-- FOR blank career end --}}

                                        <div class="patternsCard bg-red-50 d-none">
                                            <h3 class="cardTitle rota_count greenText"> <i
                                                    class='bxf  bx-alert-triangle'></i> 3 Conflicts Detected</h3>
                                            <div class="" id="shift-conflict-wrapper">

                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift
                                                            outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i> Date:
                                                        19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift:
                                                        08:00</div>
                                                </div>
                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift
                                                            outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i> Date:
                                                        19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift:
                                                        08:00</div>
                                                </div>
                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift
                                                            outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i> Date:
                                                        19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift:
                                                        08:00</div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="availabilityCalendar">
                                            <div class="calendarHeader">
                                                <div class="workHoursHeader">
                                                    <div class="title"><i class='bx  bx-calendar'></i>Availability
                                                        Overview</div>
                                                </div>

                                                <div class="nav">
                                                    <button class="prev"><i class='bxf  bx-chevron-left'></i> </button>
                                                    <span class="monthLabel">January 2026</span>
                                                    <button class="next"><i class='bxf  bx-chevron-right'></i> </button>
                                                </div>
                                            </div>

                                            <div class="legend">
                                                <span class="working">Working</span>
                                                <span class="dayoff">Day Off</span>
                                                <span class="unavailable">Unavailable</span>
                                                <span class="leave">On Leave</span>
                                            </div>

                                            <div class="month active">
                                                <div class="week days">
                                                    <div>Sun</div>
                                                    <div>Mon</div>
                                                    <div>Tue</div>
                                                    <div>Wed</div>
                                                    <div>Thu</div>
                                                    <div>Fri</div>
                                                    <div>Sat</div>
                                                </div>
                                            </div>

                                            <div class="calendarViewport">
                                                <div class="calendarTrack">
                                                    <!-- Month 1 -->
                                                    {{-- <div class="cell workingDateCle">4 <div class="pill working">
                                                                    Working</div>
                                                                <div class="pill">09:00 - 17:00</div>
                                                            </div>
                                                            <div class="cell unavailableDateCle">5<div
                                                                    class="pill unavailable">Unavailable</div>
                                                            </div> --}}
                                                    {{-- <div class="month active"> --}}
                                                    {{-- <div class="dates">
                                                            <div class="cell">1<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">2<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">3<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">4 <div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">5<div class="pill dayoff">Day Offs</div>
                                                            </div>
                                                            <div class="cell">6<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">7<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">8<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">9<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">10<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">11<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">12<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">13<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">14<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">15<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">16<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">17<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">18<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell active">19<div class="pill dayoff">Day Off
                                                                </div>
                                                            </div>
                                                            <div class="cell">20<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">21<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">22<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">23<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">24<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">25<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">26<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">27<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">28<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">29<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">30<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                            <div class="cell">31<div class="pill dayoff">Day Off</div>
                                                            </div>
                                                        </div> --}}
                                                    {{-- </div> --}}

                                                    <!-- Month 2 (example empty for demo) -->
                                                    {{-- <div class="month">
                                                        <div class="dates"></div>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="content" id="workingHoursTab">

                                    <div class="workHoursCard">
                                        <div class="workHoursHeader">
                                            <div class="title"><i class='bx  bx-history'></i> Working Hours</div>
                                            <div class="actions">
                                                <input type="hidden" id="workHoursPerWeekValue">
                                                <span class="badge" id="workHoursPerWeekText">0.0 hrs/week</span>
                                                <button class="btn" type="button" id="applyMondayToWeek"> <i
                                                        class='bx  bx-copy'></i> Apply Mon to
                                                    Weekdays</button>
                                                <button class="btn-outline" type="button" id="resetWorkingHrsBtn"><i
                                                        class='bx  bx-rotate-ccw'></i>
                                                    Reset</button>
                                            </div>
                                        </div>
                                        <div class="schedulePattern">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="schedule_pattern">Schedule Pattern</label>
                                                    <select class="form-control" id="schedule_pattern">
                                                        <option value="standard">Standard Weekly Pattern</option>
                                                        <option value="alternate">Alternate Weeks</option>
                                                        <option value="specific">Choose Specific Dates (next 60 days)
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 mb-3" id="editing_week" style="display:none;">
                                                    <label for="schedule_pattern_2">Editing Week</label>
                                                    <select class="form-control" id="schedule_pattern_2">
                                                        <option value="1" selected>Week 1</option>
                                                        <option value="2">Week 2</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

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
                                                <input type="hidden"id="total_working_week_1">
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
                                                <input type="hidden"id="total_working_week_2">
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
                                            <div class="alert alert-danger d-none" id="workingHoursFormError">

                                            </div>
                                            <button class="btn allBtnUseColor validation_staff" type="button"
                                                id="saveWorkingHrsBtn"> Save
                                                Working Hours </button>
                                        </div>

                                    </div>
                                </div>

                                <div class="content" id="unavailabilityTab">
                                    <div class="shadowp bg-purple-50 rounded8 p24" style="margin-bottom:20px"
                                        id="leave_request_main_wrapper">
                                        <p class="fs13 font600 darkPurpleTextp"> <i
                                                class="bx bx-calendar-week f18 me-2"></i> Approved Leave Requests </p>
                                        <div id="leave_request_wrapper">
                                            {{-- <div class="bg-purple-50 bgWhite rounded5 p-3">
                                                <div class="flexBw">
                                                    <div>
                                                        <h5 class="h5Head mb-2">Nov 7 - Nov 7, 2025</h5>
                                                        <p class="textGray500 fs13 mb-0">holiday</p>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg purpleBadges">Via Leave System</span>
                                                    </div>
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                    <div class="workHoursCard">
                                        <div class="workHoursHeader">
                                            <div class="title"><i class="bx  bx-calendar-x"></i> Unavailability Periods
                                            </div>
                                            <div class="actions">
                                                <button class="allbuttonDarkClr  addUnavailabilityBtnFormShow"> <i
                                                        class='bx  bx-plus'></i> Add Unavailability</button>
                                            </div>
                                        </div>
                                        <div class="alert alert-danger d-none" id="unavailabilityFormError"></div>
                                        <div class="carer-form addUnavailabilityForm">
                                            <form id="unavailabilityForm">
                                                <input type="hidden" id="unavailability_id" name="unavailability_id">
                                                <div class="clientFilterform">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <label>Type</label>
                                                            <select class="form-control" name="unavailability_type"
                                                                id="unavailability_type">
                                                                <option value="single">Single</option>
                                                                <option value="range">Date Range</option>
                                                            </select>
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
                                                        <div class="col-md-12 m-t-10">
                                                            <div class="actions addUnavailabilityBtn">
                                                                <button type="button" id="addUnavailabilityBtn"
                                                                    class="submit">Add
                                                                    Unavailability</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="addUnavailabilityList">
                                            <div class="blankCarer" id="defaultBlankCarerWrapper">
                                                <div class="leave-card"
                                                    style="height: 400px; margin-top:0px; display:flex; justify-content:center; align-items:center;">
                                                    <div class="leavebanktabCont blankdesign">
                                                        <i class="bx bx-calendar-x"></i>
                                                        <h4 class="font600">No unavailability periods set</h4>
                                                        <p class="textGray500">Add periods when this carer is not available
                                                            for shifts</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="content" id="preferencesTab">
                                    <div class="leave-card">

                                        <div class="workHoursHeader">
                                            <div class="title"> Work Preferences</div>
                                        </div>

                                        <div class="workPreferences">
                                            <form id="workPreferencesForm">
                                                <input type="hidden" name="workPreferencesId" id="workPreferencesId">
                                                <div class="row">
                                                    <div class="col-md-12 alert alert-danger d-none"
                                                        id="workPreferencesFormError">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Max Hours Per Day</label>
                                                        <input type="number" name="max_per_day" id="max_per_day"
                                                            class="form-control" placeholder="8"
                                                            onkeypress="return value.length < 2">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Max Hours Per Week</label>
                                                        <input type="number"name="max_per_week" id="max_per_week"
                                                            class="form-control" placeholder="40"
                                                            onkeypress="return value.length < 2">
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <label>Preferred Areas (Postcodes)</label>
                                                        <input type="text" name="postcode" id="postcode"
                                                            class="form-control" placeholder="e.g., SW1, W1, NW3">
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <div class="actions">
                                                            <button type="button" id="preferencesSubmitBtn"
                                                                class="submit allBtnUseColor">
                                                                Save Preferences</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- END TAB CONTENT -->
                        </div>
                    </div> <!-- and off col-9 -->
                </div>
            </div>
        </div>
        <form id="form-data">
            <input type="hidden" id="carer_id">
        </form>
        <script src="{{ asset('public/frontEnd/staff/js/working-hours1.js') }}"></script>
        <script>
            let userWorkingHours = null;
            let setWorkingHours = null;

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
            const tabs1 = document.querySelectorAll(".tab");

            tabs1.forEach(tab => {
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
                    if (tabName == 'unavailabilityTab') {
                        loadUnavailability();
                    }
                    if (tabName == 'overviewTab') {
                        loadOverviewData();
                    }
                });
            });


            // <!-- ***************Start js availabilityCalendar***************** -->

            // (() => {

            const calendar = document.querySelector(".availabilityCalendar");
            const track = calendar.querySelector(".calendarTrack");
            const label = calendar.querySelector(".monthLabel");

            let current = new Date();

            function renderMonth(date, workingHrs = null) {
                current = new Date();
                track.innerHTML = '';
                let availabilityHrs = workingHrs.availability;
                let unavailabilityHrs = workingHrs.unavailability;
                let leave_arr = workingHrs.leave_arr;
                const unavailableSet = new Set(unavailabilityHrs);
                const leaveSet = new Set(leave_arr);
                const year = date.getFullYear();
                const month = date.getMonth();

                label.textContent = date.toLocaleString("default", {
                    month: "long",
                    year: "numeric"
                });

                const firstDay = new Date(year, month, 1).getDay();
                const totalDays = new Date(year, month + 1, 0).getDate();
                const weekNames = [
                    "sunday",
                    "monday",
                    "tuesday",
                    "wednesday",
                    "thursday",
                    "friday",
                    "saturday"
                ];
                const monthEl = document.createElement("div");
                monthEl.className = "month";

                monthEl.innerHTML = `
                            <div class="dates"></div>
                            `;

                const datesEl = monthEl.querySelector(".dates");

                for (let i = 0; i < firstDay; i++) {
                    datesEl.appendChild(document.createElement("div"));
                }
                let selectTabs = 'standard';
                let availableWorkingHrsDay = availabilityHrs ? availabilityHrs.reduce((acc, curr) => {
                    if (curr.day && curr.week_number == null) {
                        selectTabs = curr.type;
                        acc[curr.day.toLowerCase()] = {
                            startTime: curr.start_time,
                            endTime: curr.end_time,
                            week_number: null,
                        };
                    } else if (curr.day && curr.week_number != null) {
                        selectTabs = curr.type;

                        const dayKey = curr.day.toLowerCase();

                        if (!acc[dayKey]) {
                            acc[dayKey] = [];
                        }

                        acc[dayKey].push({
                            startTime: curr.start_time,
                            endTime: curr.end_time,
                            week_number: curr.week_number,
                            weekDaysName: dayKey,
                        });
                    } else {
                        selectTabs = 'specific';
                        acc[curr.start_date] = {
                            startTime: curr.start_time,
                            endTime: curr.end_time
                        };
                    }
                    return acc;
                }, {}) : {};

                const today = new Date();
                const todayDate = today.getDate();
                const todayMonth = today.getMonth();
                const todayYear = today.getFullYear();
                for (let d = 1; d <= totalDays; d++) {
                    const dateObj = new Date(year, month, d);
                    // const weekNumber = getWeekNumber(dateObj);


                    const formattedDate =
                        year + '-' +
                        String(month + 1).padStart(2, '0') + '-' +
                        String(d).padStart(2, '0');
                    const weekDayName = weekNames[dateObj.getDay()];
                    const currentWeekDayName = weekNames[today.getDay()];
                    const cell = document.createElement("div");
                    cell.className = "cell";
                    if (
                        d === todayDate &&
                        month === todayMonth &&
                        year === todayYear
                    ) {
                        cell.classList.add("active");
                    }
                    if (leave_arr.length > 0 && leaveSet.has(formattedDate)) {

                        cell.classList.remove("workingDateCle");
                        cell.classList.add("unavailableDateCle");

                        cell.innerHTML = `${d}<div class="pill purpleOnLeave">On Leave</div>`;

                    } else if (unavailabilityHrs.length > 0 && unavailableSet.has(formattedDate)) {

                        cell.classList.remove("workingDateCle");
                        cell.classList.add("unavailableDateCle");

                        cell.innerHTML = `${d}<div class="pill unavailable">Unavailable</div>`;

                    } else if (availableWorkingHrsDay[weekDayName] && selectTabs == 'standard') {
                        let startTime = availableWorkingHrsDay[weekDayName].startTime;
                        let endTime = availableWorkingHrsDay[weekDayName].endTime;
                        cell.classList.add("workingDateCle");
                        cell.innerHTML =
                            `${d}<div class="pill working">Working</div><div class="pill">${startTime} - ${endTime}</div>`;

                    } else if (availableWorkingHrsDay[formattedDate] && selectTabs == 'specific') {
                        let startTime = availableWorkingHrsDay[formattedDate].startTime;
                        let endTime = availableWorkingHrsDay[formattedDate].endTime;
                        cell.classList.add("workingDateCle");
                        cell.innerHTML =
                            `${d}<div class="pill working">Working</div><div class="pill">${startTime} - ${endTime}</div>`;


                    } else if (availableWorkingHrsDay[weekDayName] && selectTabs == 'alternate') {
                        let weekDaysNameList = availableWorkingHrsDay[weekDayName];
                        let isWorkingFound = false;
                        let firstDays = new Date(year, month, 1);
                        let weekNumber = Math.ceil((d + firstDays.getDay()) / 7);
                        $.each(weekDaysNameList, function(key1, val1) {
                            let weekDaysName = val1.weekDaysName;
                            if (weekDaysName == weekDayName) {
                                let startTime = val1.startTime;
                                let endTime = val1.endTime;
                                let weekNum = val1.week_number;



                                let isValidWeek =
                                    (weekNum == 1 && weekNumber % 2 != 0) || // odd week
                                    (weekNum == 2 && weekNumber % 2 == 0); // even week

                                if (isValidWeek) {
                                    isWorkingFound = true;
                                    cell.classList.add("workingDateCle");

                                    cell.innerHTML =
                                        `${d}<div class="pill working">Working</div><div class="pill">${startTime} - ${endTime}</div>`;
                                }

                            }
                        });
                        if (!isWorkingFound) {
                            cell.innerHTML =
                                `${d}<div class="pill dayoff">Day Off</div>`;
                        }
                    } else {
                        cell.innerHTML = `${d}<div class="pill dayoff ">Day Off</div>`;

                    }
                    datesEl.appendChild(cell);
                }


                return monthEl;
            }

            function getWeekNumber(date) {
                const tempDate = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));

                const dayNum = tempDate.getUTCDay() || 7;
                tempDate.setUTCDate(tempDate.getUTCDate() + 4 - dayNum);

                const yearStart = new Date(Date.UTC(tempDate.getUTCFullYear(), 0, 1));

                const weekNo = Math.ceil((((tempDate - yearStart) / 86400000) + 1) / 7);

                return weekNo;
            }

            function slide(direction) {
                const newDate = new Date(current);
                newDate.setMonth(current.getMonth() + direction);

                const newMonth = renderMonth(newDate, userWorkingHours);
                track.appendChild(newMonth);

                requestAnimationFrame(() => {
                    track.style.transform = `translateX(${direction === 1 ? "-100%" : "100%"})`;
                });

                setTimeout(() => {
                    track.innerHTML = "";
                    track.appendChild(newMonth);
                    track.style.transition = "none";
                    track.style.transform = "translateX(0)";
                    track.offsetHeight;
                    track.style.transition = "transform 0.4s ease";
                    current = newDate;
                }, 400);
            }


            calendar.querySelector(".next").onclick = () => slide(1);
            calendar.querySelector(".prev").onclick = () => slide(-1);



            // })();
            document.addEventListener("DOMContentLoaded", function() {

                const toggleBtn = document.querySelector(".addUnavailabilityBtnFormShow");
                const formBox = document.querySelector(".addUnavailabilityForm");

                if (toggleBtn && formBox) {
                    toggleBtn.addEventListener("click", function() {
                        formBox.classList.toggle("active");
                    });
                }

            });

            function loadOverviewData() {
                let carer_id = $("#carer_id").val();
                $.ajax({
                    url: "{{ route('roster.carer.availability.load_overview_data') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        carer_id: carer_id,
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            userWorkingHours = res.data;
                            track.appendChild(renderMonth(new Date(), res.data));
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        // Code to run if the request fails
                    }

                });
            }

            function loadWorkingHrsData(workingHrs, selectTabs) {
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
                    });
                } else if (selectTabs === 'specific') {
                    // console.log(selected_tab_standard);

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
            let nextPageUrl = null;
            let loading = false;
            let search = '';

            function loadUsers(url = "{{ route('roster.carer.availability.loadUserData') }}", reset = true) {

                if (loading) return;

                loading = true;

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        search: search
                    },
                    success: function(res) {
                        $(".addUnavailabilityList").empty();
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (reset) {
                            $("#usersListWrapper").empty();
                        }
                        if (res.data.length > 0) {
                            setUserHtml(res); // ✅ full response bhejo
                        } else {

                            $("#usersListWrapper").html('No Carer Found !!');
                        }

                    },
                    complete: function() {
                        loading = false;
                    }
                });
            }
            $("#searchText").on('input', function() {
                search = $(this).val();
                loadUsers(undefined, true);
            });

            function setUserHtml(res) {
                // $("#usersListWrapper").empty();
                let html = '';


                $.each(res.data, function(index, user) {
                    let isPartial = user.work_unavailability_count > 0 ? true : false;
                    let partialColor = isPartial ? 'partial' : 'available';
                    let partialText = isPartial ? 'Partial' : 'Available';
                    let username = user.name
                        .toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');

                    html += `<button class="tab users-tab" data-tab="overviewTab" data-userid="${user.id}">
                            <div class="carerCard">
                                <div class="avatar">${username[0]}</div>
                                <div class="details">
                                    <div class="topRow">
                                        <span class="name">${username}</span>
                                    </div>
                                    <span class="tag ${partialColor}">${partialText}</span>
                                </div>
                            </div>
                        </button>`;
                });

                $("#usersListWrapper").append(html);
                // ✅ save next page
                nextPageUrl = res.next_page;
            }
            $(document).ready(function() {
                // first load
                loadUsers();
                // 🔥 infinite scroll
                $("#usersListWrapper").on('scroll', function() {
                    let div = this;

                    if (div.scrollTop + div.clientHeight >= div.scrollHeight - 5) {

                        if (nextPageUrl) {
                            loadUsers(nextPageUrl, false);
                        }
                    }
                });
                $(document).on('click', '.users-tab', function() {

                    $('.users-tab').removeClass('active');

                    $(this).addClass('active');

                    let userId = $(this).attr('data-userid');
                    $.ajax({
                        url: "{{ route('roster.carer.availability.details') }}",
                        type: 'POST',
                        data: {
                            userId: userId,
                            _token: "{{ @csrf_token() }}"
                        },
                        beforeSend: function() {},
                        success: function(res) {
                            if (typeof isAuthenticated === "function") {
                                if (isAuthenticated(res) == false) {
                                    return false;
                                }
                            }
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
                            setAvailabilityData(res.data);
                            $("#carerUserDataWrapper").addClass('d-none');
                            $("#carerUserProfileWrapper").addClass('d-none');
                            $("#defaultBlankCarerWrapper").removeClass('d-none');
                            if (res.status) {

                                $("#defaultBlankCarerWrapper").addClass('d-none');
                                $("#carerUserDataWrapper").removeClass('d-none');
                                let wrapper = $("#carerUserDataWrapper");

                                wrapper.find('.tab, .content').removeClass('active');

                                wrapper.find(".tab[data-tab='overviewTab']").addClass('active');
                                wrapper.find("#overviewTab").addClass('active');
                                $("#carerUserProfileWrapper").removeClass('d-none');
                                $("#resetWorkingHrsBtn").trigger('click');
                                loadOverviewData();
                                let array1 = [];
                                let selectTabs = 'standard';
                                $('.workingHoursDifferentSchedules').html(
                                    `<p>You are editing <strong> Week 1</strong> of the alternating schedule. These
                                                hours will repeat every other week. Switch between Week 1 and Week 2 above
                                                to set different schedules.</p>
                                            <div class="debugWeek mt-2">Debug: Week1 enabled days: ${res.data.week_1_counts} | Week2 enabled days:
                                                 ${res.data.week_2_counts} | Current enabled days: ${res.data.week_1_counts}</div>`
                                );

                                $("#total_working_week_2").val(parseFloat(res.data.week_2_sum)
                                    .toFixed(2))
                                $("#total_working_week_1").val(parseFloat(res.data.week_1_sum)
                                    .toFixed(2))
                                workingPreferences = {
                                    max_per_day: res.data.work_preferences ? res.data
                                        .work_preferences.max_per_day : 8,
                                    max_per_week: res.data.work_preferences ? res.data
                                        .work_preferences.max_per_week : 40,
                                    postcode: res.data.work_preferences ? res.data
                                        .work_preferences.postcode : ''
                                };
                                if (res.data.working_hours.length > 0) {
                                    array1 = res.data.working_hours;
                                    selectTabs1 = '#tab-' + array1[0].type;
                                    selectTabs = array1[0].type;

                                } else if (res.data.specific_working_hours.length > 0) {
                                    array1 = res.data.specific_working_hours;
                                    selectTabs1 = '#tab-specific';
                                    selectTabs = 'specific';
                                }
                                //  else if (res.data.alternate_working_hours.length > 0) {
                                //     array1 = res.data.alternate_working_hours;
                                //     selectTabs1 = '#tab-alternate';
                                //     selectTabs = 'alternate';
                                // }
                                $("#schedule_pattern").val(selectTabs).change();
                                setWorkingHours = array1;
                                loadWorkingHrsData(array1, selectTabs);
                                // track.appendChild(renderMonth(new Date(), res.data
                                //     .working_hours));
                            }
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            // Code to run if the request fails
                        }
                    });
                });

                function setAvailabilityData(res) {
                    $("#carer_id").val(res.id);
                    let username = res.name
                        .toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                    let email = res.email;
                    let working_hrs_per_week = res.working_hrs_per_week;

                    $(".userNameAndDetails").html(`<div class="card-header">
                                        <div class="user">
                                            <div class="avatar">${username[0]}</div>
                                            <div class="info">
                                                <div class="name"><a href="#!">${username}</a></div>
                                                <div class="role">${email}</div>
                                            </div>
                                        </div>
                                        <div class="dFlexGap">
                                        <span class="status greenShowbtn">
                                            <i class='bx  bx-clock-4'></i> ${working_hrs_per_week}
                                        </span>
                                        ${res.specific_total_working_hours_sum ? `<span class="careBadg muteBadges">No working hours set</span>`:''}
                                    </div></div>`);

                    $("#max_per_day").val(res.work_preferences ? res.work_preferences.max_per_day : '');
                    $("#max_per_week").val(res.work_preferences ? res.work_preferences.max_per_week : '');
                    $("#postcode").val(res.work_preferences ? res.work_preferences.postcode : '');
                    $("#workPreferencesId").val(res.work_preferences ? res.work_preferences.id : '');
                }
                // SAVE WORKING HRS
                $("#saveWorkingHrsBtn").click(function() {
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
                    let maxPerDay = workingPreferences.max_per_week;
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
                            $("#workingHoursFormError").removeClass('d-none alert-success')
                                .css('text-align', 'left')
                                .html(errMsg);
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
                    if (schedule_pattern !== "specific" && totalHrs > maxPerDay) {

                        errMsg =
                            `Total hours (${totalHrs.toFixed(1)}) exceed the weekly limit (${maxPerDay.toFixed(1)} hrs).`

                        isValid = false;
                    }
                    if (arr.length == 0) {
                        errMsg = 'Please select at least one working day and time.';
                        isValid = false;
                    }
                    if (!isValid) {
                        $("#workingHoursFormError").removeClass('d-none alert-success')
                            .css('text-align', 'left')
                            .html(errMsg);
                        return;
                    };
                    // console.log(arr);
                    // return;
                    $("#workingHoursFormError").addClass('d-none alert-success');
                    let formData = new FormData();

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
                            alert(res.message);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            $("#saveWorkingHrsBtn").prop('disabled', false).html(
                                'Save Working Hours');
                            let errorMSg = xhr.responseJSON && xhr.responseJSON.message ? xhr
                                .responseJSON.message : 'An error occurred';
                            alert(errorMSg);
                            // Code to run if the request fails
                        }
                    });

                });

                $("#preferencesSubmitBtn").click(function() {
                    let carer_id = $("#carer_id").val();
                    $.ajax({
                        url: "{{ route('roster.carer.availability.save_work_preferences') }}", // URL to send the request to
                        type: 'POST', // or 'POST'
                        data: $("#workPreferencesForm").serialize() + '&carer_id=' + carer_id,
                        beforeSend: function() {},
                        success: function(res) {
                            $("#workPreferencesFormError").addClass('d-none').html('');
                            if (typeof isAuthenticated === "function") {
                                if (isAuthenticated(res) == false) {
                                    return false;
                                }
                            }
                            if (res.status) {
                                $("#workPreferencesFormError").removeClass(
                                        'd-none alert-danger')
                                    .addClass('alert-success').html(res.message);
                                $("#workPreferencesId").val(res.data.id);
                            } else {
                                alert('Failed to save preferences. Please try again.');
                            }

                            setTimeout(() => {
                                $("#workPreferencesFormError").addClass('d-none').html(
                                    '');
                            }, 3000);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            $("#workPreferencesFormError").addClass('d-none').html('');
                            let errorMSg = xhr.responseJSON && xhr.responseJSON.message ? xhr
                                .responseJSON.message : 'An error occurred';
                            if (xhr.status == 422) {
                                let htm = '';
                                $.each(errorMSg, function(key, value) {
                                    htm += `<li>${value[0]}</li>`;
                                });
                                $("#workPreferencesFormError").removeClass(
                                        'd-none alert-success')
                                    .addClass('alert-danger').html(`<ul>${htm}</ul>`);

                            } else {
                                $("#workPreferencesFormError").removeClass(
                                        'd-none alert-success')
                                    .addClass('alert-danger').html(errorMSg);
                            }
                            // setTimeout(() => {
                            //     $("#workPreferencesFormError").addClass('d-none').html('');
                            // }, 3000);
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
                                $("#unavailabilityFormError").removeClass('d-none alert-danger')
                                    .addClass('alert-success').html(res.message);
                                $('.addUnavailabilityForm').removeClass('active');
                                loadUnavailability();
                            } else {
                                // alert('Failed to save preferences. Please try again.');
                            }

                            setTimeout(() => {
                                $("#unavailabilityFormError").addClass('d-none').html(
                                    '');
                            }, 3000);
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
                                $("#unavailabilityFormError").removeClass(
                                        'd-none alert-success')
                                    .addClass('alert-danger').html(errorMSg);
                            }
                            // setTimeout(() => {
                            //     $("#workPreferencesFormError").addClass('d-none').html('');
                            // }, 3000);
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
                                $("#unavailabilityFormError").removeClass('d-none alert-danger')
                                    .addClass('alert-success').html(res.message);
                                loadUnavailability();
                            } else {
                                // alert('Failed to save preferences. Please try again.');
                            }

                            setTimeout(() => {
                                $("#unavailabilityFormError").addClass('d-none').html(
                                    '');
                            }, 3000);
                        },
                        error: function(xhr, ajaxOptions, thrownError) {}
                    });

                });

                $("#schedule_pattern").change(function() {
                    let val = $(this).val();
                    let userId = $("#carer_id").val();

                    // $.ajax({
                    //     url: "{{ route('roster.carer.availability.details') }}",
                    //     type: 'POST',
                    //     data: {
                    //         userId: userId,
                    //         _token: "{{ @csrf_token() }}"
                    //     },
                    //     beforeSend: function() {},
                    //     success: function(res) {
                    //         if (typeof isAuthenticated === "function") {
                    //             if (isAuthenticated(res) == false) {
                    //                 return false;
                    //             }
                    //         }
                    //         setAvailabilityData(res.data);
                    //         $("#carerUserDataWrapper").addClass('d-none');
                    //         $("#carerUserProfileWrapper").addClass('d-none');
                    //         $("#defaultBlankCarerWrapper").removeClass('d-none');
                    //         if (res.status) {
                    //             $("#defaultBlankCarerWrapper").addClass('d-none');
                    //             $("#carerUserDataWrapper").removeClass('d-none');
                    //             $("#carerUserProfileWrapper").removeClass('d-none');
                    //             $("#resetWorkingHrsBtn").trigger('click');
                    //             // loadOverviewData();
                    //             let array1 = [];
                    //             let selectTabs = val;
                    //             workingPreferences = {
                    //                 max_per_day: res.data.work_preferences ? res.data
                    //                     .work_preferences.max_per_day : 8,
                    //                 max_per_week: res.data.work_preferences ? res.data
                    //                     .work_preferences.max_per_week : 40,
                    //                 postcode: res.data.work_preferences ? res.data
                    //                     .work_preferences.postcode : ''
                    //             };
                    //             if (res.data.working_hours.length > 0 && (val == 'standard' ||
                    //                     val == 'alternate')) {
                    //                 array1 = res.data.working_hours;
                    //                 selectTabs1 = '#tab-' + array1[0].type;
                    //                 selectTabs = val;


                    //             } else if (res.data.specific_working_hours.length > 0 && val ==
                    //                 'specific') {
                    //                 array1 = res.data.specific_working_hours;
                    //                 selectTabs1 = '#tab-specific';
                    //                 selectTabs = 'specific';
                    //             }

                    //             setWorkingHours = array1;
                    //             // console.log(selectTabs, array1);
                    //             loadWorkingHrsData(array1, selectTabs);
                    //             // track.appendChild(renderMonth(new Date(), res.data
                    //             //     .working_hours));
                    //         }
                    //     },
                    //     error: function(xhr, ajaxOptions, thrownError) {
                    //         // Code to run if the request fails
                    //     }
                    // });
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
                                        .work_preferences.max_per_day : 8,
                                    max_per_week: res.data.work_preferences ? res.data
                                        .work_preferences.max_per_week : 40,
                                    postcode: res.data.work_preferences ? res.data
                                        .work_preferences.postcode : ''
                                };
                                array1 = res.data.working_hours;
                                staffDetails = res.data.staff;
                                console.log(staffDetails);
                                total_working_week_1 = staffDetails.week_1_sum != null ?
                                    Number(staffDetails.week_1_sum).toFixed(2) :
                                    "0.00";

                                total_working_week_2 = staffDetails.week_2_sum != null ?
                                    Number(staffDetails.week_2_sum).toFixed(2) :
                                    "0.00";
                                $("#total_working_week_1").val(total_working_week_1);
                                $("#total_working_week_2").val(total_working_week_2);
                                // $("#summaryCard").html(
                                //     `<button class="greenShowbtn"><i class='bx  bx-history'></i>${staffDetails.working_hrs_per_week}</button>
                                //                 ${staffDetails.specific_total_working_hours_sum ? `<span class="careBadg muteBadges">No working hours set</span>`:''}`
                                //     );
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
            });
        </script>
        <!-- **************End availabilityCalendar****************** -->
    @endsection
</main>
