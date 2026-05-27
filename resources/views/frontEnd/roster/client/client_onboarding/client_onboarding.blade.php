<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title', 'Client Onboadrding')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="staffHeaderp">
                    <div>
                        <div class="d-flex gap-2 mb-3">
                            <div>
                                <i class="bx bx-user-circle blueText" style="font-size: 30px;"></i>
                            </div>

                            <h1 class="mainTitlep mb-0"> Client Onboarding Management</h1>
                        </div>
                        <p class="header-subtitle mb-0">Consent, assessment, and care plan tracking for all clients</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- card -->
        <div class="row mt20">
            <div class="col-md-12">
                <div class="card-row cardRow4">
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <div>
                                <i class="bx bx-user-circle fs30 blueText"></i>
                            </div>
                            <h2 class="cardBoldTitle mb-2 mt-3">32</h2>
                            <p class=" fs13 textGray">Total Client</p>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <div>
                                <i class="bx bx-check-circle fs30 greenText"></i>
                            </div>
                            <h2 class="cardBoldTitle mb-2 mt-3">0</h2>
                            <p class=" fs13 textGray">Active Clients</p>
                            <div>
                                <span class="careBadg darkGreenBadges">5%</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <div>
                                <i class="bx bx-alert-circle fs30 orangeText"></i>
                            </div>
                            <h2 class="cardBoldTitle mb-2 mt-3">6</h2>
                            <p class=" fs13 textGray">In Progress</p>

                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <div>
                                <i class="bx bx-clipboard-detail fs30 textGray500"></i>
                            </div>
                            <h2 class="cardBoldTitle mb-2 mt-3">29</h2>
                            <p class=" fs13 textGray">Not Started </p>

                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- search bar -->
        <div class="row mt20">
            <div class="col-md-12">
                <div class="dFlexGap">
                    <div class="input-group searchWithtabs flex1 shadowp">
                        <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                        <input type="text" class="form-control searchDailyLog" placeholder="Search clients by name...">
                    </div>
                    <div>
                        <div class="dFlexGap">
                            <div>
                                <select class="form-control">
                                    <option>All Status</option>
                                    <option>Active Only</option>
                                    <option>In Progress</option>
                                    <option>Not Started</option>
                                </select>
                            </div>
                            <div>
                                <select class="form-control">
                                    <option>All Care Types</option>
                                    <option>Residential</option>
                                    <option>Domiciliary</option>
                                    <option>Not Started</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  all staff stripe  -->
        <div class="row mt20">
            <div class="col-md-12">
                <div class="virtGap">
                    <!-- residential -->
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkBlueBadg ms-2">Residential</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div class="greenText">
                                            <i class="bx bx-heart  me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div class="flex1">
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModalClient">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">
                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkBlueBadg ms-2">Residential</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart textGray400  me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-check-circle fs23 greenText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkBlueBadg ms-2">Residential</span></h6>
                                    <div class="dFlexGap fs12 greenText mt-2">
                                        <div>
                                            <i class="bx bx-check-circle me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText"> 100%</p>
                                    <p class="fs13 mb-2 textGray500">3/3 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkGreenBadges">Active
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- domiciliary -->
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkGreenBadges ms-2">Domiciliary</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart textGray400  me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkGreenBadges ms-2">Domiciliary</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div class="greenText">
                                            <i class="bx bx-check-circle me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart textGray400  me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- supported living -->
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">
                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkVioletBadg ms-2">Supported living</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div class="greenText">
                                            <i class="bx bx-heart  me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkVioletBadg ms-2">supported living</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart textGray400 me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- day centre -->
                    <div class="emergencyMain p-3 AllStaffTabC ">
                        <div class="flexBw align-items-center flexWrap gap-3">
                            <div class="dFlexGap">

                                <i class="bx bx-alert-circle fs23 orangeText"> </i>
                                <div>
                                    <h6 class="h6Head mb-2">Jane Wakefield <span class="careBadg darkOrangeBadg ms-2">Day Care</span></h6>
                                    <div class="dFlexGap fs12 textGray500 mt-2">
                                        <div>
                                            <i class="bx bx-check-circle textGray400 me-1 fs13"></i>
                                            <span> Consent</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-clipboard-detail textGray400 me-1" fs13></i>
                                            <span> Assessment</span>
                                        </div>
                                        <div>
                                            <i class="bx bx-heart textGray400 me-1 fs13"></i>
                                            <span> Care Plan
                                            </span>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="dFlexGap">
                                <div>
                                    <div class="progressBar" style="width:150px; margin-left:auto;">
                                        <div class="progressFill" style="width:16%; background:#3376f2"></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="fs13 font700 mb-2 blackText">0%</p>
                                    <p class="fs13 mb-2 textGray500">0/5 complete</p>

                                </div>
                                <div>
                                    <span class="careBadg darkOrangeBadg">Onboarding
                                    </span>
                                </div>
                                <div>
                                    <button class="borderBtn" data-toggle="modal" data-target="#manageModal">
                                        <i class="bx bx-eye me-2 f18"></i>
                                        Manage
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="noData">
                        <div>
                            <i class="bx bx-user-circle"></i>
                            <p class="mb-0">No clients found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade leaveCommunStyle" id="manageModalClient" tabindex="1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg pModalScroll">
                <div class="modal-content">
                    <div class="modal-header p24">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"> <i class="bx bx-user-circle purpleTextp fs23"></i>Client Onboarding: Logan Jones
                        </h4>
                    </div>
                    <div class="modal-body heightScrollModal p24" style="height: unset;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="bg-blue-50 rounded8 shadowp p-4">
                                    <div class="occupancyBox" style="border: none;">
                                        <div class="topRow">
                                            <span class="fs16 font600">Onboarding Progress</span>
                                            <span class="value f20" style="color: #3376f2;">75%</span>
                                        </div>
                                        <div class="progressBar">
                                            <div class="progressFill" style="width:75%; background:#3376f2"></div>
                                        </div>
                                    </div>
                                    <p class="textGray500 fs13">
                                        3/4 required stages complete
                                    </p>
                                </div>
                                <div class="recordSec">
                                    <div class="recordCard">
                                        <div class="rounded8 shadowp p24 mt20 recordBtn cursorPointer" style="border: 1px solid #86efac;" type="button">
                                            <div class="flexBw">
                                                <div>
                                                    <div class="dFlexGap mb-3 align-items-start">
                                                        <div>
                                                            <i class="bx bx-check-circle fs23 greenText"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="h6Head">Consent & Mental Capacity <span class="careBadg redbadges ms-2">Required</span></h6>
                                                            <p class="fs13 textGray500 mb-0">Obtain consent and assess mental capacity</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="careBadg darkGreenBadges">Complete</span>
                                                </div>
                                            </div>
                                            <div class="recordContent">
                                                <h6 class="fs13 font600">Mental Capacity Assessment</h6>
                                                <form action="">
                                                    <div class="row mt-4">
                                                        <div class="col-md-12">
                                                            <div class="mentalCheckParent">
                                                                <div class="dFlexGap">
                                                                    <input type="checkbox" class="checkBoxHw mentalCheckIn">
                                                                    <p class="fs13 font600 mb-0">Mental capacity assessment completed</p>
                                                                </div>
                                                                <div class="mentalCapacitySelect" style="display: none;">
                                                                    <select name="mentalCapacity" class="form-control m-t-10">

                                                                        <option value="full">Has capacity</option>
                                                                        <option value="partial">Lacks capacity</option>
                                                                        <option value="none">Fluctuating Capacity</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-sm-6 m-t-10">
                                                            <label for="">Consent Given By *</label>
                                                            <input type="text" placeholder="Name" class="form-control">
                                                        </div>
                                                        <div class="col-md-6 col-sm-6 m-t-10">
                                                            <label for="">Relationship * </label>
                                                            <select name="relationship" id="relationship" class="form-control">
                                                                <option value="full">Self(Client)</option>
                                                                <option value="partial">LPA(Health and Welfare)</option>
                                                                <option value="none">Power of Attorney</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Consent Types *</label>
                                                            <div class="flexBw">
                                                                <div class="flex1">
                                                                    <div class="virtGap gap-2">
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Care provision</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Information sharing</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Medical treatment</p>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="flex1">
                                                                    <div class="virtGap gap-2">
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Data processing</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Photography</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Personal Care</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Signed Consent Form</label>
                                                            <div class="uploadBox mb-2 text-center muteHover">
                                                                <i class="bx bx-arrow-from-bottom"></i>
                                                                <p class="fs13 mb-0 textGray500">Upload Consent Form</p>
                                                                <input type="file">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <button class="bgBtn blueBtn w100"><i class="bx bx-check-circle f18 me-2"></i> Save Consent Record</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="recordCard">
                                        <div class="rounded8 shadowp p24 mt20 recordBtn cursorPointer" style="border: 1px solid #86efac;" type="button">
                                            <div class="flexBw">
                                                <div>
                                                    <div class="dFlexGap mb-3 align-items-start">
                                                        <div>
                                                            <i class="bx bx-check-circle fs23 greenText"></i>
                                                        </div>
                                                        <div>

                                                            <h6 class="h6Head">2. Care Assessment <span class="careBadg redbadges ms-2">Required</span></h6>
                                                            <p class="fs13 textGray500 mb-0">Complete comprehensive care needs assessment</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="careBadg darkGreenBadges">Complete</span>
                                                </div>
                                            </div>
                                            <div class="recordContent">
                                                <form action="">

                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-6">
                                                            <label for="">Assessment Date *</label>
                                                            <input type="date" class="form-control">
                                                        </div>
                                                        <div class="col-md-6 col-sm-6">
                                                            <label for="">Completed By *</label>
                                                            <input type="text" class="form-control" placeholder="Assessor name">
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Health Needs</label>
                                                            <textarea name="notes" class="form-control" rows="3" cols="15" placeholder="Physical and mental health needs..."></textarea>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Mobility & Transfer Needs</label>
                                                            <textarea name="notes" class="form-control" rows="3" cols="15" placeholder="Mobility assessment and support requirements..."></textarea>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Medication Requirements</label>
                                                            <textarea name="notes" class="form-control" rows="3" cols="15" placeholder="Current medications, administration support needed..."></textarea>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Safeguarding Risks Identified</label>
                                                            <textarea name="notes" class="form-control" rows="3" cols="15" placeholder="Any vulnerabilities or safeguarding concerns..."></textarea>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Personal Preferences & Routines</label>
                                                            <textarea name="notes" class="form-control" rows="3" cols="15" placeholder="Daily routines, likes/dislikes, preferences..."></textarea>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <button class="bgBtn pgreenBtn w100"><i class="bx bx-check-circle f18 me-2"></i> Complete Assessment</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded8 shadowp p24 mt20" style="border: 1px solid #86efac;">
                                        <div class="flexBw">
                                            <div>
                                                <div class="dFlexGap mb-3 align-items-start">
                                                    <div>
                                                        <i class="bx bx-check-circle fs23 greenText"></i>
                                                    </div>
                                                    <div>

                                                        <h6 class="h6Head">3. Care Plan<span class="careBadg redbadges ms-2">Required</span></h6>
                                                        <p class="fs13 textGray500 mb-0">Create and approve care plan</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="careBadg darkGreenBadges">Complete</span>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <p class="fs13 textGray500 mb-2">Create care plan from the client profile page using the Care Plan Manager</p>
                                            <div class="bg-greenp-50 p-3 rounded5" style="border: unset;">
                                                <p class="mb-0 fs13 greenText"> <i class="bx bx-heart f18"></i> Care plan approved on 17/02/2026 </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rounded8 shadowp p24 mt20 lightBorderp">
                                        <div class="flexBw">
                                            <div>
                                                <div class="dFlexGap mb-3 align-items-start">
                                                    <div class="muteCircle">
                                                        <i class="bx bx-circle f20 textGray400"></i>
                                                    </div>
                                                    <div>

                                                        <h6 class="h6Head">4. Risk Assessment<span class="borderBadg ms-2">Optional</span></h6>
                                                        <p class="fs13 textGray500 mb-0">Complete environmental and care risk assessments</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="careBadg darkMuteBadg">Pending</span>
                                            </div>
                                        </div>
                                        <p class="fs13 textGray500 mt-4 mb-0">Complete risk assessment from the client profile page</p>
                                    </div>
                                    <div class="recordCard">
                                        <div class="rounded8 shadowp p24 mt20 recordBtn cursorPointer lightBorderp" type="button">
                                            <div class="flexBw">
                                                <div>
                                                    <div class="dFlexGap mb-3 align-items-start">
                                                        <div class="muteCircle">
                                                            <i class="bx bx-circle f20 textGray400"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="h6Head">Test Stage <span class="careBadg redbadges ms-2">Required</span></h6>
                                                            <p class="fs13 textGray500 mb-0">No need to this.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="careBadg darkGreenBadges">Complete</span>
                                                </div>
                                            </div>
                                            <div class="recordContent">
                                                <h6 class="fs13 font600">Mental Capacity Assessment</h6>
                                                <form action="">
                                                    <div class="row mt-4">
                                                        <div class="col-md-12">
                                                            <div class="mentalCheckParent">
                                                                <div class="dFlexGap">
                                                                    <input type="checkbox" class="checkBoxHw mentalCheckIn">
                                                                    <p class="fs13 font600 mb-0">Mental capacity assessment completed</p>
                                                                </div>
                                                                <div class="mentalCapacitySelect" style="display: none;">
                                                                    <select name="mentalCapacity" class="form-control m-t-10">

                                                                        <option value="full">Has capacity</option>
                                                                        <option value="partial">Lacks capacity</option>
                                                                        <option value="none">Fluctuating Capacity</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-sm-6 m-t-10">
                                                            <label for="">Consent Given By *</label>
                                                            <input type="text" placeholder="Name" class="form-control">
                                                        </div>
                                                        <div class="col-md-6 col-sm-6 m-t-10">
                                                            <label for="">Relationship * </label>
                                                            <select name="relationship" id="relationship" class="form-control">
                                                                <option value="full">Self(Client)</option>
                                                                <option value="partial">LPA(Health and Welfare)</option>
                                                                <option value="none">Power of Attorney</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Consent Types *</label>
                                                            <div class="flexBw">
                                                                <div class="flex1">
                                                                    <div class="virtGap gap-2">
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Care provision</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Information sharing</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Medical treatment</p>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="flex1">
                                                                    <div class="virtGap gap-2">
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Data processing</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Photography</p>
                                                                        </div>
                                                                        <div class="dFlexGap align-items-center">
                                                                            <input type="checkbox" class="checkBoxHw">
                                                                            <p class="fs13 font600 mb-0">Personal Care</p>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <label for="">Signed Consent Form</label>
                                                            <div class="uploadBox mb-2 text-center muteHover">
                                                                <i class="bx bx-arrow-from-bottom"></i>
                                                                <p class="fs13 mb-0 textGray500">Upload Consent Form</p>
                                                                <input type="file">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12 m-t-10">
                                                            <button class="bgBtn blueBtn w100"><i class="bx bx-check-circle f18 me-2"></i> Save Consent Record</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!--  -->
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- manage document modal end -->
    </div>
    <!-- for section toggle -->
    <script>
        const recordSec = document.querySelector(".recordSec");

        recordSec.querySelectorAll(".recordBtn").forEach(button => {

            button.addEventListener("click", () => {

                const card = button.closest(".recordCard");
                console.log(card);

                const content = card.querySelector(".recordContent");

                if (!content) return;

                // close all
                recordSec.querySelectorAll(".recordContent").forEach(c => {
                    console.log(c);

                    c.style.display = "none";
                });

                // open current
                content.style.display = "block";

            });
        });
    </script>
    <!-- checkToggle-->
    <script>
        const mentalCheckin = document.querySelector(".mentalCheckIn");
        const mentalCapacitySelect = document.querySelector(".mentalCapacitySelect");
        mentalCheckin.addEventListener("change", () => {
            if (mentalCheckin.checked) {
                mentalCapacitySelect.style.display = "block"
            } else {
                mentalCapacitySelect.style.display = "none"
            }
        })
    </script>
</main>
@endsection