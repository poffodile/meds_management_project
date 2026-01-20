@extends('frontEnd.layouts.master')
@section('title','Carer Availability')
@section('content')

@include('frontEnd.roster.common.roster_header')

<style>
.sideTabWrapper {
    border-radius: 8px;
}
.sideTabWrapper .sectionWhiteBgAllUse.rightsideBtnTab{
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
.searchCareAvailability .input-group span{
    background: #fff;
    color: #d5d5d5;
    border: 1px solid #e2e2e4;
    border-right: 0;
}

.searchCareAvailability .input-group input {
    border-left: 0;
    background: #ffffff;
}
.searchCareAvailability label{
    font-size: 18px;
    color: #000;
    display: flex;
    gap: 10px;
}
.searchCareAvailability label i{
    font-size: 22px;
}
.sideTabs.suggestedCarers{
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
.sideTabs.suggestedCarers .avatar{
   background: #3b82f6;
}
.sideTabs.suggestedCarers .tag.partial{
    background: #fef9c3;
    color: #ab7b0f;
    font-weight: 600;
}
.sideTabs.suggestedCarers .tag.available{
    background-color: #afeccb;
    color: #06af51;
    font-weight: 600;
}
.userNameAndDetails .status{
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
                                    <input type="text" class="form-control" placeholder="Search carers...">
                                </div>
                            </div>
                            <div class="sideTabs suggestedCarers  p-20 p-t-0">

                                <button class="tab active" data-tab="overviewTab">
                                    <div class="carerCard">
                                        <div class="avatar">A</div>
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

                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="sectionWhiteBgAllUse" >
                            <div class="sideTabContent">
                                <div class="content active" id="overviewTab">
                                    <div class="carertabcontent">
                                        <div class="userNameAndDetails">
                                            <div class="card-header">
                                                <div class="user">
                                                    <div class="avatar">A</div>
                                                    <div class="info">
                                                        <div class="name"><a href="#!">Aick Carter</a></div>
                                                        <div class="role"> flipholt72@yahoo.co.uk</div>
                                                    </div>
                                                </div>
                                                <span class="status greenShowbtn">
                                                   <i class='bx  bx-clock-4'></i>  5 days • 50h/week
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="content" id="scheduleTab">
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
                                                   <i class='bx  bx-clock-4'></i>  5 days • 50h/week
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
                                                   <i class='bx  bx-clock-4'></i>  5 days • 50h/week
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
                                <div class="tabs p-1 m-b-0">
                                    <button class="tab active" data-tab="overviewTab"><i class="bx bx-calendar"></i> Overview</button>
                                    <button class="tab" data-tab="workingHoursTab"><i class='bx  bx-clock-4'></i>  Working Hours</button>
                                    <button class="tab" data-tab="unavailabilityTab"><i class='bx  bx-calendar-x'></i>  Unavailability </button>
                                    <button class="tab" data-tab="preferencesTab"><i class='bx  bx-cog'></i>  Preferences</button>
                                </div>

                                <!-- TAB CONTENT -->
                                <div class="tab-content carertabcontent">
                                    <div class="content active" id="overviewTab">
                                        <div class="careInsightsWrap">
                                            <div class="patternsCard bg-red-50">
                                                <h3 class="cardTitle rota_count greenText"> <i class='bxf  bx-alert-triangle'></i>  5 Conflicts Detected</h3>
                                               <div class="">

                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i>  Date: 19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift: 08:00</div>
                                                </div>
                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i>  Date: 19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift: 08:00</div>
                                                </div>
                                                <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                    <p><span class="commntagDesin careBadg">LOW</span> <strong> Shift outside preferred working hours</strong></p>
                                                    <div class="debugWeek mt-2"><i class='bx  bx-calendar-week'></i>  Date: 19/01/2026</div>
                                                    <div class="debugWeek mt-2">Preferred hours: 09:00 - 17:00, Shift: 08:00</div>
                                                </div>

                                               </div>
                                            </div>

                                            <div class="availabilityCalendar">
                                                <div class="calendarHeader">
                                                    <div class="workHoursHeader">
                                                        <div class="title"><i class='bx  bx-calendar'></i>Availability Overview</div>                                                    
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
                                                        <div>Sun</div><div>Mon</div><div>Tue</div>
                                                        <div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                                                    </div>
                                                </div>

                                                <div class="calendarViewport">
                                                    <div class="calendarTrack">
                                                    <!-- Month 1 -->
                                                    <div class="month active"> 
                                                        <div class="dates">
                                                            <div class="cell">1<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">2<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">3<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell workingDateCle">4 <div class="pill working">Working</div><div class="pill">09:00 - 17:00</div></div>
                                                            <div class="cell unavailableDateCle">5<div class="pill unavailable">Unavailable</div></div>
                                                            <div class="cell">6<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">7<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">8<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">9<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">10<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">11<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">12<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">13<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">14<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">15<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">16<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">17<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">18<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell active">19<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">20<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">21<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">22<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">23<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">24<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">25<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">26<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">27<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">28<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">29<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">30<div class="pill dayoff">Day Off</div></div>
                                                            <div class="cell">31<div class="pill dayoff">Day Off</div></div>
                                                        </div>
                                                    </div>

                                                    <!-- Month 2 (example empty for demo) -->
                                                    <div class="month">                                                        
                                                        <div class="dates"></div>
                                                    </div>
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
                                                    <span class="badge">8.0 hrs/week</span>
                                                    <button class="btn"> <i class='bx  bx-copy'></i> Apply Mon to Weekdays</button>
                                                    <button class="btn-outline"><i class='bx  bx-rotate-ccw'></i> Reset</button>
                                                </div>
                                            </div>
                                            <div class="schedulePattern"> 
                                                <div class="row">
                                                    <div class="col-md-6">                                                    
                                                        <label>Schedule Pattern</label>
                                                        <select class="form-control" id="schedule_pattern">
                                                            <option value="standard">Standard Weekly Pattern</option>
                                                            <option value="alternate">Alternate Weeks</option>
                                                            <option value="specific">Choose Specific Dates (next 60 days)</option>
                                                        </select>
                                                    </div>
                                                     <div class="col-md-6">                                                    
                                                        <label>Editing Week</label>
                                                        <select class="form-control" id="schedule_pattern">
                                                            <option value="standard">Week 1</option>
                                                            <option value="alternate">Week 2</option>
                                                            <option value="specific">Week 3</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="workingHoursDifferentSchedules careItem  bg-orange-50">
                                                <p>You are editing <strong> Week 1</strong> of the alternating schedule. These hours will repeat every other week. Switch between Week 1 and Week 2 above to set different schedules.</p>
                                                 <div class="debugWeek mt-2">Debug: Week1 enabled days: 2 | Week2 enabled days: 2 | Current enabled days: 2</div>
                                            </div>

                                            <!-- Standard Weekly -->
                                            <div id="tab-standard">
                                                <div class="dayRow active">
                                                    <span class="day">Monday</span>
                                                    <label class="switch">
                                                        <input type="checkbox" checked>
                                                        <span class="slider"></span>
                                                    </label>
                                                    <input type="time" value="09:00" class="dayTime form-control">
                                                    <span>to</span>
                                                    <input type="time" value="17:00" class="dayTime form-control">
                                                    <span class="hours">8.0 hrs</span>
                                                </div>

                                                <div class="dayRow">
                                                    <span class="day">Tuesday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                                <div class="dayRow">
                                                    <span class="day">Wednesday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                                <div class="dayRow">
                                                    <span class="day">Thursday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                                <div class="dayRow">
                                                    <span class="day">Friday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                                <div class="dayRow weekend">
                                                    <span class="day">Saturday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                                <div class="dayRow weekend">
                                                    <span class="day">Sunday</span>
                                                    <label class="switch">
                                                        <input type="checkbox">
                                                        <span class="slider"></span>
                                                    </label>
                                                    <span class="notWorking">Not working</span>
                                                </div>

                                            </div>

                                            <div class="modal-footer m-t-0">
                                                <button class="btn allBtnUseColor validation_staff" type="submit"> Save Working Hours </button>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="content" id="unavailabilityTab">
                                          <div class="workHoursCard">
                                                <div class="workHoursHeader">
                                                    <div class="title"><i class="bx  bx-calendar-x"></i> Unavailability Periods</div>
                                                    <div class="actions">
                                                        <button class="allbuttonDarkClr  addUnavailabilityBtnFormShow"> <i class='bx  bx-plus'></i>  Add Unavailability</button>
                                                    </div>
                                                </div>
                                                <div class="carer-form addUnavailabilityForm">
                                                    <form>
                                                        <div class="clientFilterform">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <label>Type</label>
                                                                    <select class="form-control">
                                                                        <option>Single</option>
                                                                        <option>Date Range</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-12 m-t-10">
                                                                    <label>Date</label>
                                                                    <input type="date" class="form-control" id="" name="date">
                                                                </div>
                                                                
                                                                <div class="col-md-6  m-t-10">
                                                                    <label>From Time (optional)</label>
                                                                    <input type="time" class="form-control" placeholder="">
                                                                </div>
                                                                <div class="col-md-6  m-t-10">
                                                                    <label>To Time (optional)</label>
                                                                    <input type="time" class="form-control" placeholder="">
                                                                </div>
                                                                
                                                                <div class="col-md-12  m-t-10">
                                                                    <label>Reason (optional)</label>
                                                                    <textarea name="Reason" required="" class="form-control" rows="3" cols="20" placeholder="e.g., Personal appointment, Training, etc."></textarea>
                                                                </div>
                                                                <div class="col-md-12 m-t-10">
                                                                   <div class="actions addUnavailabilityBtn">
                                                                        <button type="submit" class="submit">Add Unavailability</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>

                                                <div class="addUnavailabilityList">
                                                    <div class="certifiedList">                                                       
                                                        <span class="unavailabilityDateAndTime">   
                                                             <span class="caldrIcon">
                                                                <i class="bx  bx-calendar-x"></i>
                                                            </span>                                                         
                                                            <div class="">
                                                                <div><strong> Wed, Dec 10, 2025</strong></div>
                                                                <small>09:00 - 17:00 • 8hrs</small>
                                                            </div>
                                                        </span>    
                                                        <div class="planActions">
                                                            <span class="cornorTags yellow"> Active </span>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                        </div>
                                                    </div>
                                                    <div class="certifiedList">                                                       
                                                        <span class="unavailabilityDateAndTime">   
                                                             <span class="caldrIcon">
                                                                <i class="bx  bx-calendar-x"></i>
                                                            </span>                                                         
                                                            <div class="">
                                                                <div><strong> Wed, Dec 10, 2025</strong></div>
                                                                <small>09:00 - 17:00 • 8hrs</small>
                                                            </div>
                                                        </span>    
                                                        <div class="planActions">
                                                            <span class="cornorTags inactive"> Inactive </span>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                        </div>
                                                    </div>
                                                    <div class="certifiedList">                                                       
                                                        <span class="unavailabilityDateAndTime">   
                                                             <span class="caldrIcon">
                                                                <i class="bx  bx-calendar-x"></i>
                                                            </span>                                                         
                                                            <div class="">
                                                                <div><strong> Wed, Dec 10, 2025</strong></div>
                                                                <small>09:00 - 17:00 • 8hrs</small>
                                                            </div>
                                                        </span>    
                                                        <div class="planActions">
                                                            <span class="cornorTags yellow"> Active </span>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
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
                                                <form>
                                                    <div class="row">
                                                        
                                                        <div class="col-md-6 ">
                                                            <label>Max Hours Per Day</label>
                                                            <input type="number" class="form-control" value="8" maxlength="40">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Max Hours Per Week</label>
                                                            <input type="number" class="form-control" value="40" maxlength="40">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label>Preferred Areas (Postcodes)</label>
                                                            <input type="text" class="form-control" placeholder="e.g., SW1, W1, NW3">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <div class="actions">
                                                                <button type="submit" class="submit allBtnUseColor">Save Preferences</button>
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
        </script>


<!-- ***************Start js availabilityCalendar***************** -->
<script>
(() => {
  const calendar = document.querySelector(".availabilityCalendar");
  const track = calendar.querySelector(".calendarTrack");
  const label = calendar.querySelector(".monthLabel");

  let current = new Date();

  function renderMonth(date) {
    const year = date.getFullYear();
    const month = date.getMonth();

    label.textContent = date.toLocaleString("default", {
      month: "long",
      year: "numeric"
    });

    const firstDay = new Date(year, month, 1).getDay();
    const totalDays = new Date(year, month + 1, 0).getDate();

    const monthEl = document.createElement("div");
    monthEl.className = "month";

    monthEl.innerHTML = `
     
      <div class="dates"></div>
    `;

    const datesEl = monthEl.querySelector(".dates");

    for (let i = 0; i < firstDay; i++) {
      datesEl.appendChild(document.createElement("div"));
    }

    for (let d = 1; d <= totalDays; d++) {
      const cell = document.createElement("div");
      cell.className = "cell";
      cell.innerHTML = `${d}<div class="pill dayoff">Day Off</div>`;
      datesEl.appendChild(cell);
    }

    return monthEl;
  }

  function slide(direction) {
    const newDate = new Date(current);
    newDate.setMonth(current.getMonth() + direction);

    const newMonth = renderMonth(newDate);
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

  track.appendChild(renderMonth(current));

  calendar.querySelector(".next").onclick = () => slide(1);
  calendar.querySelector(".prev").onclick = () => slide(-1);



})();


</script>


 <script>
    document.addEventListener("DOMContentLoaded", function() {

        const toggleBtn = document.querySelector(".addUnavailabilityBtnFormShow");
        const formBox = document.querySelector(".addUnavailabilityForm");

        if (toggleBtn && formBox) {
            toggleBtn.addEventListener("click", function() {
                formBox.classList.toggle("active");
            });
        }

    });
</script>

<!-- **************End availabilityCalendar****************** -->

        

        
@endsection




</main>























