@extends('frontEnd.layouts.master')
@section('title','Staff Task')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container-fluid">
        <div class="staffMainp">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp">
                        <div>
                            <h1>Staff Tasks</h1>

                            <p class="header-subtitle mb-0"> Supervisions, assessments, and other tasks </p>
                        </div>
                        <div>
                            <button class="bgBtn" data-toggle="modal" data-target="#newRecord"><i class='bx  bx-plus me-2'></i> Create Task</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-4 staffCardCol">
                    <div class="staffCardp p-4">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="staffCicon">
                                <i class='bx  bx-clock'></i>
                            </div>
                            <div>
                                <h3 class="mt-0">2</h3>
                                <p>pending</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 staffCardCol">
                    <div class="staffCardp p-4">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="staffCicon">
                                <i class='bx  bx-caret-big-right'></i>
                            </div>
                            <div>
                                <h3 class="mt-0">2</h3>
                                <p>In Progress</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 staffCardCol">
                    <div class="staffCardp p-4">
                        <div class="d-flex gap-3 align-items-center">
                            <div class="staffCicon">
                                <i class='bx  bx-alert-circle'></i>
                            </div>
                            <div>
                                <h3 class="mt-0">2</h3>
                                <p>Over Due</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row  mt20">
                <div class="col-lg-12">
                    <div class="calendarTabs staffTaskTab">
                        <div class="tabs p-1 ">
                            <button class="tab active" data-tab="generalTab">
                                Pending

                            </button>
                            <button class="tab " data-tab="availabilityTab">
                                In Progress

                            </button>

                            <button class="tab" data-tab="supervisionsTab">
                                Completed

                            </button>
                            <button class="tab" data-tab="shiftsTab">
                                All
                            </button>

                        </div>

                        <!-- TAB CONTENT -->
                        <div class="tab-content carertabcontent">
                            <div class="content active" id="generalTab">
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 blueAllTabCard">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg buleBadges">Training</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg redbadges">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>

                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 purpleAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Supervision - Michael Chen</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg purpleBadges">Audit</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>

                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 yellowAllTabCard allterStafftask mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm allertStaffTaskDate"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg yellowBadges">Spot Check</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg muteBadges">Low</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg redDarkBadges">Overdue</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>

                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 yellowAllTabCard  allterStafftask  mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> About: <span>Sarah Johnsan</span></p>
                                                    <p class="para text-sm allertStaffTaskDate"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg yellowBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg highBadges">High</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg redDarkBadges">Overdue</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 purpleAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg purpleBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg orangeBages">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="content" id="availabilityTab">
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 orangeAllTabCard">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg orangeBages">Training</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg orangeBages">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 blueAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-group'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Supervision - Michael Chen</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg buleBadges">Supervision</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 yellowAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg yellowBadges">Spot Check</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 muteAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg muteBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 purpleAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg purpleBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg orangeBages">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="content" id="supervisionsTab">
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 blueAllTabCard completedTaskStaff mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-group'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Supervision - Michael Chen</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg buleBadges">Supervision</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="careBadg greenbadges"><i class='bx  bx-check-circle greenText me-1'></i> Completed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content" id="shiftsTab">
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 orangeAllTabCard">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg orangeBages">Training</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg orangeBages">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 blueAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-group'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Supervision - Michael Chen</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg buleBadges">Supervision</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="bgBtn pgreenBtn"><i class='bxr  bx-caret-big-right me-3'></i> Start Task</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 yellowAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i>About: <span>Sarah Mitchell</span></p>

                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg yellowBadges">Spot Check</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 muteAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bx  bx-check-circle'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg muteBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg buleBadges">Medium</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="emergencyMain emergencyContent AllStaffTabC p-4 purpleAllTabCard mt-4">
                                    <div class="d-flex justify-content-between  align-items-center">
                                        <div class="d-flex gap-4 align-items-center">
                                            <div class="bgIconStaffT">
                                                <i class='bxr  bx-file-detail'></i>
                                            </div>
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="m-0">Task Start</h5>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            <i class='bxr  bx-file-detail me-1'></i> Form Required
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <p class="para text-sm"><i class='bxr  bx-user'></i> Assigned to: <span>Emma Johnson</span></p>
                                                    <p class="para text-sm"><i class='bxr  bx-calendar-week'></i> Due: <span>27 Jan 2026</span></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">

                                                    <div>
                                                        <span class="careBadg purpleBadges">General</span>
                                                    </div>
                                                    <div>
                                                        <span class="careBadg orangeBages">High</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn"><i class='bxr  bx-caret-big-right me-3'></i> Continue</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- END TAB CONTENT -->
                    </div>
                </div>
            </div>
        </div>
        <!-- pratima modal start -->
        <div class="modal fade leaveCommunStyle" id="newRecord" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog pModalScroll customModalWidthp">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"> Create Staff Task
                        </h4>
                    </div>
                    <div class="modal-body heightScrollModal" style="height: unset;">
                        <div class="carer-form">

                            <div class="row mb-4">
                                <div class="col-lg-12">
                                    <label>Task Type *</label>
                                    <select class="form-control">
                                        <option>Assessment</option>
                                        <option>Spot Check</option>
                                    </select>
                                </div>

                                <div class="col-md-12  m-t-10">
                                    <label>Title *</label>
                                    <input type="text" class="form-control" placeholder="e.g. Monthly Supervision - John Smith">
                                </div>
                                <div class="col-lg-12 m-t-10">
                                    <label> Assigned To (who will perform this task) *</label>
                                    <select class="form-control">
                                        <option>Assessment</option>
                                        <option>Spot Check</option>
                                    </select>
                                </div>
                                <div class="col-lg-12 m-t-10">
                                    <label>About (staff member being supervised/assessed)</label>
                                    <select class="form-control">
                                        <option>Assessment</option>
                                        <option>Spot Check</option>
                                    </select>
                                </div>
                                <div class="col-lg-12 m-t-10">
                                    <label>Form Template (optional)</label>
                                    <select class="form-control">
                                        <option>Assessment</option>
                                        <option>Spot Check</option>
                                    </select>
                                </div>
                                <div class="col-md-6  m-t-10">
                                    <label>Due Date</label>
                                    <input type="date" class="form-control">
                                </div>
                                <div class="col-lg-6 m-t-10">
                                    <label>Priority</label>
                                    <select class="form-control">
                                        <option>Medium</option>
                                        <option>High</option>
                                    </select>
                                </div>
                                <div class="col-md-6  m-t-10">
                                    <label>Scheduled Date</label>
                                    <input type="date" class="form-control">
                                </div>
                                <div class="col-md-6  m-t-10">
                                    <label>Time</label>
                                    <input type="time" class="form-control">
                                </div>
                                <div class="col-md-12 m-t-10">
                                    <label>Description</label>
                                    <textarea name="morning" required="" class="form-control" rows="3" cols="20" placeholder="Additional details..."></textarea>
                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-3 justify-content-end">
                        <button type="button" data-dismiss="modal" aria-hidden="true" class="borderBtn">Cancel</button>
                        <button type="submit" class="bgBtn darkBg submit ">Create Task</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- pratima modal end -->
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
    @endsection
</main>