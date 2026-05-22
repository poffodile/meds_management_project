<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title','Client')
@section('content')
<style>
    /* Global overrides for clipping issues in modals and page content */
    .modal-body select.form-control,
    .modal-body input.form-control,
    .page-content select.form-control,
    .page-content input.form-control {
        height: 45px !important;
        padding: 8px 15px !important;
        line-height: 25px !important;
        font-size: 14px !important;
        vertical-align: middle !important;
        overflow: visible !important;
        box-sizing: border-box !important;
    }

    .modal-body textarea.form-control,
    .page-content textarea.form-control {
        height: auto !important;
        min-height: 80px !important;
        padding: 10px 15px !important;
        line-height: 1.5 !important;
        overflow: auto !important;
    }

    /* Ensure labels don't overlap */
    .modal-body label,
    .page-content label {
        margin-bottom: 8px !important;
        display: inline-block !important;
    }

    /* MAR Monthly Grid Styles */
    .mar-grid-scroll {
        overflow-x: auto;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .mar-grid-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
    }

    .mar-grid-table th,
    .mar-grid-table td {
        border: 1px solid #ccc;
        padding: 2px 3px;
        text-align: center;
        white-space: nowrap;
    }

    .mar-grid-table thead th {
        background: #1e3a5f;
        color: #fff;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .mar-grid-med-col {
        text-align: left !important;
        min-width: 160px;
        max-width: 200px;
        white-space: normal !important;
        word-break: break-word;
        font-size: 11px;
    }

    .mar-grid-time-col {
        min-width: 45px;
        font-weight: 600;
        background: #f0f4ff;
    }

    .mar-grid-day-col {
        min-width: 24px;
        font-size: 10px;
    }

    .mar-grid-bal-col {
        min-width: 35px;
        font-weight: 600;
        background: #f9f9f9;
    }

    .mar-grid-sunday {
        color: #f87171;
    }

    .mar-grid-week-sep {
        border-left: 2px solid #1e3a5f !important;
    }

    .mar-grid-med-first>td {
        border-top: 2px solid #555 !important;
    }

    .mar-grid-cell {
        width: 24px;
        height: 24px;
        cursor: pointer;
        transition: background 0.15s;
        font-weight: 600;
    }

    .mar-grid-cell:hover {
        background: #dbeafe !important;
    }

    .mar-code-a,
    .mar-code-s {
        background: #dcfce7;
        color: #166534;
    }

    .mar-code-r {
        background: #fee2e2;
        color: #991b1b;
    }

    .mar-code-w {
        background: #fef3c7;
        color: #92400e;
    }

    .mar-code-n {
        background: #f1f5f9;
        color: #475569;
    }

    .mar-code-o {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .mar-grid-dosage {
        color: #888;
        font-weight: normal;
    }

    .mar-grid-route {
        color: #888;
        font-weight: normal;
        font-size: 10px;
    }

    .mar-grid-freq {
        color: #666;
        font-size: 10px;
    }

    .mar-grid-prn {
        display: inline-block;
        background: #f0ad4e;
        color: #fff;
        font-size: 9px;
        padding: 1px 4px;
        border-radius: 3px;
        font-weight: 600;
    }

    .mar-grid-stock-row td {
        background: #f5f5f5;
        border-top: 1px solid #999;
    }

    .mar-grid-stock-cell {
        text-align: left !important;
    }

    .mar-grid-stock-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        padding: 3px 0;
    }

    .mar-grid-stock-field {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 11px;
    }

    .mar-grid-stock-field label {
        margin: 0;
        font-weight: 600;
        color: #555;
        font-size: 10px;
    }

    .mar-stock-input {
        width: 55px;
        padding: 2px 4px;
        border: 1px solid #ccc;
        border-radius: 3px;
        font-size: 11px;
        text-align: center;
    }

    .mar-grid-legend {
        margin-top: 10px;
        font-size: 12px;
        color: #555;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .mar-grid-legend-item {
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 11px;
    }
</style>

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container-fluid">
        <div class="topHeaderCont">
            <div>
                <h1>{{$clientDetails['name']}}</h1>
                <p class="header-subtitle"><span class="careBadg greenBadges me-2">{{ $status }}</span>
                    <span>{{$status}}</span> local authority
                </p>
            </div>
            <div class="header-actions addnewicons">
                <button class="btn borderBtn editClient" data-toggle="modal" data-target="#addServiceUserModal" data-child_id="{{$clientDetails['id']}}"><i class='bx  bx-edit'></i> Edit Client</button>
                <button class="btn borderBtn openBodyMapProfile" data-service-user-id="{{ $client_id }}"><i class='bx  bx-body'></i> Body Map</button>
                <!-- <button class="btn borderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Import Documents</button> -->
                <button class="btn borderBtn" onclick="openImportModal({{ $client_id }})"><i class='bx  bx-arrow-in-up-square-half'></i> Import Documents</button>
                <button class="btn allBtnUseColor" data-toggle="modal" data-target="#generateCarePlanModal"><i class='bx  bx-sparkles'></i> Generate Care Plan</button>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <!-- yellow  medium  risk-->
                <div id="headerAlertHtml"></div>

                <div class="moreAllertsSec mt-3">
                </div>
                <div class="text-center mt-3">
                    <button class="borderBtn showMoreBtn w100" style="display:none">
                        <i class="bx bx-chevron-down me-2 fs23"></i>
                        <span></span>
                    </button>
                    <div class="mt-3 headerAlertCounting" style="display:none">
                        <span class="fs12 redtext font600 me-3" id="headerCriticalAlertsCount">0 Critical</span>
                        <span class="fs12 orangeText font600 me-3" id="headerHighAlertsCount">.0 High</span>
                        <span class="fs12 muteText font600" id="headerOtherAlertsCount">. 0 Other</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- above part of tab -->
        <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
            <div class="clientOverTabs">
                <div class="tabs p-1 ">
                    <button class="tab active" data-tab="clientDetailsTab"> Details </button>
                    <button class="tab" data-tab="clientOnboardingTab"> Onboarding </button>
                    <button class="tab" data-tab="clientCareTasksTab" id="clientCareTasksTabBtn" onclick="getCareTask()"> Care Tasks </button>
                    <!-- onclick="getAlerts()" -->
                    <button class="tab" data-tab="clientAlertsTab"> Alerts </button>
                    <button class="tab" data-tab="clientAIInsightsTab"> AI Insights </button>
                    <button class="tab" data-tab="clientCarePlanTab" onclick="loadCarePlans()"> Care Plan </button>
                    <button class="tab" data-tab="clientRiskAssessmentsTab"> Risk Assessments </button>
                    <button class="tab" data-tab="clientMedicationTab" onclick="getMedication()"> Medication </button>
                    <button class="tab" data-tab="clientPEEPTab"> PEEP </button>
                    <button class="tab" data-tab="clientRepositioningTab"> Repositioning </button>
                    <button class="tab" data-tab="clientBehaviorTab"> Behavior </button>
                    <button class="tab" data-tab="clientEducationTab"> Education </button>
                    <button class="tab" data-tab="clientMentalCapacityTab"> Mental Capacity </button>
                    <button class="tab" data-tab="clientDoLSTab" onclick="showDolsList()"> DoLS </button>
                    <button class="tab" data-tab="clientDNACPRTab" onclick="showDncprList()"> DNACPR </button>
                    <button class="tab" data-tab="clientSafeguardingTab"> Safeguarding </button>
                    <button class="tab" data-tab="clientConsentTab" onclick="showConsentList()"> Consent </button>
                    <button class="tab" data-tab="clientEmergencyTab"> Emergency </button>
                    <button class="tab" data-tab="clientDocumentsTab"> Documents </button>
                    <button class="tab" data-tab="clientProgressReportTab"> Progress Report </button>
                    <button class="tab" data-tab="clientExpensesTab" onclick="getExpenses()"> Expenses </button>
                </div>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content carertabcontent">
                <div class="content active" id="clientDetailsTab">
                    <div class="sectionWhiteBgAllUse">
                        <div class="profile-details-card">
                            <div class="section two-col">
                                <div>
                                    <h3>Client Information</h3>
                                    <div class="item">
                                        <span class="label">Full Name</span>
                                        <span class="value">{{$clientDetails['name']}}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Date Of Birth</span>
                                        @if(!empty($clientDetails['date_of_birth']))
                                        <span class="value">{{date('d.m.Y',strtotime($clientDetails['date_of_birth']))}}</span>
                                        @endif
                                    </div>
                                    <div class="item">
                                        <span class="label">Address</span>
                                        <span class="value">
                                            {{$clientDetails['street']}}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <h3>Care Details</h3>
                                    <div class="item">
                                        <span class="label">Funding Type</span>
                                        <span class="value">{{$clientDetails['suFundingType']}}</span>
                                    </div>
                                    <div class="item">
                                        <span class="label">Mobility</span>
                                        <span class="value">{{$clientDetails['suMobility']}}</span>
                                    </div>
                                    <div class="item carertabcontent">
                                        <span class="label">Care Needs</span>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <?php
                                                if (!empty($clientDetails['care_needs'])) {
                                                    $exp = explode(',', $clientDetails['care_needs']);
                                                    foreach ($exp as $val) { ?>
                                                        <span>{{$val}}</span>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="item">
                                        <span class="label">Medical Notes</span>
                                        <span class="value">{{$clientDetails['medical_notes']}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content" id="clientOnboardingTab">
                    <!-- onboardinf start -->
                    <div class="onboardingMain">
                        <div class="leave-card">
                            <div class="">
                                <div class="medicationManagement" id="availabilityTab">
                                    <div class="availabilityTabs">
                                        <div class="availabilityTabs__nav">
                                            <button class="availabilityTabs__tab borderBtn active"
                                                data-target="OnboardingTabPanel1" id="marSheetBtn">Client Progress
                                            </button>
                                            <button class="availabilityTabs__tab borderBtn"
                                                data-target="OnboardingTabPanel2" id="onboardingForm">Onboarding
                                                Form</button>
                                        </div>
                                        <div class="availabilityTabs__content">
                                            <div class="availabilityTabs__panel active" id="OnboardingTabPanel1">

                                                {{-- <div class="d-flex justify-content-between">
                                                <h5>Client Onboarding Progress </h5>
                                                <div>
                                                    <span class="careBadg">Complete</span>
                                                </div>
                                            </div>
                                            <div class="occupancyBox">
                                                <div class="topRow">
                                                    <span>Overall Progress</span>
                                                    <span class="value" style="color:#272727">3/3 Complete</span>
                                                </div>

                                                <div class="progressBar">
                                                    <div class="progressFill" style="width:100%;background:#2563eb"></div>
                                                </div>
                                            </div> --}}
                                                <div class="bg-blue-50 rounded8 shadowp p-4">
                                                    <div class="occupancyBox" style="border: none;">
                                                        <div class="topRow">
                                                            <span class="fs16 font600">Onboarding Progress</span>
                                                            <span class="value f20 onboardingformprogresspercentage"
                                                                style="color: #3376f2;">0%</span>
                                                        </div>
                                                        <div class="progressBar">
                                                            <div class="progressFill onboardingformprogressfill"
                                                                style="width:0%; background:#3376f2"></div>
                                                        </div>
                                                    </div>
                                                    <p class="textGray500 fs13 onboardingformprogresstext">
                                                    </p>
                                                </div>
                                                <div class="rounded8 shadowp p24 mt20 bg-greenp-50 d-none"
                                                    id="activate_client_wrapper">
                                                    <div class="flexBw">
                                                        <div class="dFlexGap align-items-start">
                                                            <i class="bx bx-check-circle greenText fs23"></i>
                                                            <div class="greenText">
                                                                <h6 class="h6Head font700 darkGreenTextp">Client
                                                                    onboarding complete!
                                                                    All stages approved.</h6>
                                                                <p class="fs13 mb-0">Client can now be activated</p>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <button class="bgBtn pgreenBtn activateClientBtn">Activate
                                                                Client</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="recordSec" id="loadStagesData">

                                                </div>
                                                {{-- <div class="onboardingBox boardingToggle p-4 mt-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="d-flex gap-3">
                                                            <div>
                                                                <i class="bx  bx-check-circle greenText"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="m-0">Consent & Capacity</h6>
                                                                <p class="header-subtitle mb-0">Completed: 09/01/2026</p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-4 align-items-center">
                                                        <div>
                                                            <span class="careBadg">Complete</span>
                                                        </div>
                                                        <div class="eyeOnboard">
                                                            <i class='bx  bx-eye'></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- boarding click  Consent & Capacity -->
                                            <div class="onboardContent d-none p-3">
                                                <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                                                    <header class="panel-heading headingCapitilize greanHeaderbgClr">
                                                        <div class="clientHeadung">
                                                            <div class="onlyheadingmain"><i class='bx  bx-file greenText'></i> Consent Management </div>
                                                            <p>Track client agreements and permissions</p>
                                                        </div>

                                                        <div class="actions mt-0">
                                                            <button class="btn aiBtnThrd addConsentBtn" data-formType="add"> <i class='bx  bx-plus'></i> Add Consent</button>
                                                        </div>
                                                    </header>

                                                    <div class="p-20">
                                                        <div class="clientFilterform greanHeaderbgClr consentManagementSec consentRecordSectionFirst" style="display:none">

                                                            <div class="createNewAlert"><i class='bx  bx-file'></i> Add New Consent Record </div>

                                                            <form action="" class="addAlertForm">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Consent Type *</label>
                                                                            <select class="form-control">
                                                                                <option>Single Day</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Consent Title *</label>
                                                                            <input type="text" class="form-control" name="" placeholder="e.g., Consent to Administer Medication">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label> Description *</label>
                                                                            <textarea name="short_description" class="form-control" rows="3" cols="20" placeholder="Detailed description of what is being consented to"></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Status *</label>
                                                                            <select class="form-control">
                                                                                <option>Single Day</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Date Granted</label>
                                                                            <input type="date" class="form-control" name="" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Expiry Date (Optional)</label>
                                                                            <input type="date" class="form-control" name="" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Granted By *</label>
                                                                            <input type="text" class="form-control" name="" placeholder="Logan Jones">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Relationship to Client</label>
                                                                            <input type="text" class="form-control" name="" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Witness Name (if applicable)</label>
                                                                            <input type="text" class="form-control" name="" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>Witness Role</label>
                                                                            <input type="text" class="form-control" name="" placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label>Additional Notes</label>
                                                                            <textarea name="short_description" required="" class="form-control" rows="2" cols="20" placeholder="Specific actions staff should take..."></textarea>
                                                                        </div>
                                                                    </div>


                                                                    <div class="col-md-12">
                                                                        <div class="header-actions">
                                                                            <button class="btn allbuttonDarkClr " type="submit"> Save Consent </button>
                                                                            <button class="btn borderBtn closeConsentRecordBtn" type="button"> Cancel </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>

                                                        <div class="carePlanWrapper consentRecordSectionSecond">
                                                            <div class="planCard greenLeftBorder m-t-20">
                                                                <div class="planTop">
                                                                    <div class="planTitle">
                                                                        Management
                                                                        <span class="roundTag greenShowbtn">granted</span>
                                                                        <span class="inactive roundTag">medication</span>
                                                                    </div>
                                                                    <div class="planActions IconFontSize">
                                                                        <span><i class='bx  bx-check-circle greenText'></i></span>
                                                                    </div>
                                                                </div>
                                                                <div class="planMeta">
                                                                    <div>Taken for one week during</div>
                                                                </div>
                                                                <div class="row medicationSheet">
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Granted by:</strong> Logan Jonesdvv (selfdvdsv)
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Date:</strong> Jan 7, 2026
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Expires:</strong> Jan 14, 2026
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Witnessed by:</strong> Taken for onen holiday.
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="medicationSheet">
                                                                    <div class="reasonBox">
                                                                        <strong>Notes:</strong> Taken for one week during August to delay period whilst on holiday.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="planCard greenLeftBorder m-t-20">
                                                                <div class="planTop">
                                                                    <div class="planTitle">
                                                                        Management
                                                                        <span class="roundTag radShowbtn">refused</span>
                                                                        <span class="inactive roundTag">other</span>
                                                                    </div>
                                                                    <div class="radIconClr IconFontSize ">
                                                                        <span><i class='bx  bx-x-circle'></i> </span>
                                                                    </div>
                                                                </div>
                                                                <div class="planMeta">
                                                                    <div>Taken for one week during</div>
                                                                </div>
                                                                <div class="row medicationSheet">
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Granted by:</strong> Logan Jonesdvv (selfdvdsv)
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Date:</strong> Jan 7, 2026
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Expires:</strong> Jan 14, 2026
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="reasonBox">
                                                                            <strong>Witnessed by:</strong> Taken for onen holiday.
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="medicationSheet">
                                                                    <div class="reasonBox">
                                                                        <strong>Notes:</strong> Taken for one week during August to delay period whilst on holiday.
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- boarding click  Consent & Capacity end -->
                                            <div class="onboardingBox boardingToggle p-4 mt-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="d-flex gap-3">
                                                            <div>
                                                                <i class="bx  bx-check-circle greenText"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="m-0">Care Assessment</h6>
                                                                <p class="header-subtitle mb-0">Completed: 09/01/2026</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-4 align-items-center">
                                                        <div>
                                                            <span class="careBadg">Complete</span>
                                                        </div>
                                                        <div class="eyeOnboard">
                                                            <i class='bx  bx-eye'></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- boarding click risk Assessment  -->
                                            <div class="onboardContent d-none p-3">

                                                <div class="carePlanTabCont riskAssessmentSectionFirst">
                                                    <div class="workHoursHeader">
                                                        <div class="title"> Risk Assessments</div>
                                                        <div class="actions">
                                                            <button class="addAssessmentBtn"> <i class='bx  bx-plus'></i>Add Assessment</button>
                                                        </div>
                                                    </div>

                                                    <div class="carePlanWrapper">
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag radShowbtn">high</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Substance misuse: Concerns around purchasing and taking various substances, an incident regarding substance misuse in July. Vaping (e-cigarette use) with declining cessation support. ADHD medication is withheld due to substance concerns.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag yellow">medium</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Dental health: Overdue for dental check-ups and has refused recent reviews due to a fear of the dentist. History of multiple dental procedures.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag radShowbtn">high</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Substance misuse: Concerns around purchasing and taking various substances, an incident regarding substance misuse in July. Vaping (e-cigarette use) with declining cessation support. ADHD medication is withheld due to substance concerns.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag yellow">medium</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Dental health: Overdue for dental check-ups and has refused recent reviews due to a fear of the dentist. History of multiple dental procedures.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag radShowbtn">high</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Substance misuse: Concerns around purchasing and taking various substances, an incident regarding substance misuse in July. Vaping (e-cigarette use) with declining cessation support. ADHD medication is withheld due to substance concerns.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                        <div class="planCard borderleftOrange">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <!-- <span class="heartIcon"><i class="bx  bx-heart"></i></span> -->
                                                                    <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                                                    general
                                                                    <span class="roundTag yellow">medium</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                                                    <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Dental health: Overdue for dental check-ups and has refused recent reviews due to a fear of the dentist. History of multiple dental procedures.</span>
                                                            </div>
                                                            <div class="planMeta">
                                                                <div><strong>Assessed: </strong> Dec 16, 2025</div>
                                                                <div><strong>Review: </strong> Mar 16, 2026</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="riskAssessmentSectionSecond" style="display:none">

                                                    <div class="topHeaderCont">
                                                        <div>
                                                            <button class="btn borderBtn backBtn" id="riskAssesmentBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back </button>
                                                        </div>
                                                    </div>

                                                    <div class="generalRiskAssessment">
                                                        <!-- Header -->
                                                        <div class="riskHeader">
                                                            <div class="titleWrap">
                                                                <span class="warnIcon">⚠</span>
                                                                <h2>General Risk Assessment</h2>
                                                            </div>
                                                            <span class="riskLevel">high risk</span>
                                                        </div>
                                                        <div class="riskMeta">
                                                            <div>
                                                                <p><strong>Assessed:</strong> December 16th, 2025</p>
                                                                <p><strong>Review Date:</strong> March 16th, 2026</p>
                                                            </div>
                                                            <div>
                                                                <p><strong>By:</strong> AI Import</p>
                                                                <p><strong>Status:</strong> active</p>
                                                            </div>
                                                        </div>
                                                        <div class="riskSection">
                                                            <h4>Risk Identified</h4>
                                                            <div class="infoBox">
                                                                <p> Substance misuse: Concerns around purchasing and taking various substances, an incident regarding substance misuse in July.
                                                                    Vaping (e-cigarette use) with declining cessation support. ADHD medication is withheld due to substance concerns. </p>
                                                            </div>
                                                        </div>
                                                        <div class="riskSection">
                                                            <h4>Existing Controls</h4>
                                                            <div class="controlItem">
                                                                <p>Ongoing YPDAAT support and keywork sessions</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                            <div class="controlItem">
                                                                <p>Education on risks of substance misuse</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                            <div class="controlItem">
                                                                <p>Support attending health appointments related to substance misuse</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                            <div class="controlItem">
                                                                <p>Liaison with Alex Fanning from YPDAAT</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                            <div class="controlItem">
                                                                <p>Withholding ADHD medication</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                            <div class="controlItem">
                                                                <p>Not supporting time with certain friends (Liv, Sophie, Lilly, Maggie, Stevie, Mia, Ellie)</p>
                                                                <span class="statusTag">effective</span>
                                                            </div>
                                                        </div>
                                                        <div class="riskSection">
                                                            <h4>Additional Controls Required</h4>
                                                        </div>
                                                    </div>

                                                </div>



                                            </div>
                                            <!-- boarding click risk Assessment end  -->

                                            <div class="onboardingBox boardingToggle p-4 mt-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="d-flex gap-3">
                                                            <div>
                                                                <i class="bx  bx-check-circle greenText"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="m-0">Care Plan</h6>
                                                                <p class="header-subtitle mb-0">Completed: 09/01/2026</p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-4 align-items-center">
                                                        <div>
                                                            <span class="careBadg">Complete</span>
                                                        </div>
                                                        <div class="eyeOnboard">
                                                            <i class='bx  bx-eye'></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- boarding click Care plan  -->
                                            <div class="onboardContent d-none p-3">



                                                <div class="carePlanTabCont carePlanBtnSectionFirst">
                                                    <div class="workHoursHeader">
                                                        <div class="title"><i class='bx  bx-heart'></i> Care Plans</div>
                                                        <div class="actions">
                                                            <button class="allBtnUseColor" data-toggle="modal" data-target="#addcreateCarePlanModal"> <i class='bx  bx-plus'></i> Create Care Plan</button>
                                                        </div>
                                                    </div>

                                                    <div class="carePlanWrapper">

                                                        <!-- Active Plan Summary -->
                                                        <div class="activePlanCard">
                                                            <div class="activePlanHeader">
                                                                <div class="leftInfo">
                                                                    <span class="activeBadge">Active Plan</span>
                                                                    <span class="assessedDate">Assessed Dec 19, 2025</span>
                                                                </div>
                                                                <button class="viewPlanBtn">
                                                                    View Full Plan <span>›</span>
                                                                </button>
                                                            </div>

                                                            <div class="activePlanStats">
                                                                <div class="statItem">
                                                                    <span class="statIcon iconblue"><i class='bx  bx-radio-circle-marked'></i> </span>
                                                                    <div>
                                                                        <div class="statLabel">Objectives</div>
                                                                        <div class="statValue">5</div>
                                                                    </div>
                                                                </div>
                                                                <div class="statItem">
                                                                    <span class="statIcon iconpurple"><i class='bx  bx-checklist'></i> </span>
                                                                    <div>
                                                                        <div class="statLabel">Tasks</div>
                                                                        <div class="statValue">5</div>
                                                                    </div>
                                                                </div>
                                                                <div class="statItem">
                                                                    <span class="statIcon iconpink"><i class='bx  bx-pill'></i> </span>
                                                                    <div>
                                                                        <div class="statLabel">Medications</div>
                                                                        <div class="statValue">6</div>
                                                                    </div>
                                                                </div>
                                                                <div class="statItem">
                                                                    <span class="statIcon iconorange"><i class='bx  bx-alert-triangle'></i> </span>
                                                                    <div>
                                                                        <div class="statLabel">Risk Factors</div>
                                                                        <div class="statValue">4</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Care Plan Card -->
                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="viewPlanBtn"><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>

                                                                <div class="planActions">
                                                                    <button class="viewPlanBtn"><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>

                                                                <div class="planActions">
                                                                    <button class="viewPlanBtn"><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>

                                                                <div class="planActions">
                                                                    <button><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>

                                                                <div class="planActions">
                                                                    <button><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                        <div class="planCard">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    <span class="heartIcon"><i class='bx  bx-heart'></i></span>
                                                                    Initial Care Plan
                                                                    <span class="draftBadge">draft</span>
                                                                </div>

                                                                <div class="planActions">
                                                                    <button><i class='bx  bx-eye'></i> </button>
                                                                    <button><i class='bx  bx-pencil'></i> </button>
                                                                    <button class="danger"><i class='bx  bx-trash'></i> </button>
                                                                </div>
                                                            </div>

                                                            <div class="planMeta">
                                                                <div><strong>Setting:</strong> residential</div>
                                                                <div><strong>Assessed:</strong> Jan 3, 2026</div>
                                                                <div><strong>By:</strong> Pratima Pathak</div>
                                                                <div><strong>Review:</strong> Apr 3, 2026</div>
                                                            </div>

                                                            <div class="planFooter">
                                                                <span><i class='bx  bx-radio-circle-marked'></i> 5 objectives</span>
                                                                <span><i class='bx  bx-list'></i> 0 tasks</span>
                                                                <span><i class='bx  bx-pill'></i> 6 medications</span>
                                                            </div>
                                                        </div>

                                                    </div>

                                                </div>


                                                <div class="carePlanBtnSectionSecond" style="display: none;">
                                                    <div class="topHeaderCont">
                                                        <div>
                                                            <button class="btn borderBtn backBtn" id="planBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back to Care Plans</button>
                                                        </div>
                                                        <div class="header-actions addnewicons">
                                                            <button class="btn allbuttonDarkClr"> Standard View</button>
                                                            <button class="btn borderBtn purpleBorderBtn"> CQC Print Format</button>
                                                            <button class="btn borderBtn blueBorderBtn"><i class='bx  bx-printer'></i> Print </button>
                                                            <button class="btn borderBtn greenBorderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Export PDF </button>
                                                            <button class="btn allBtnUseColor"><i class='bx  bx-edit'></i> Edit Plan</button>
                                                        </div>
                                                    </div>
                                                    <div class="CarePlanAllObjective" style="display: ;">
                                                        <div class="assessmentDetails leave-card p-0">
                                                            <header class="panel-heading headingCapitilize careTaskheader">
                                                                <div class="clientHeadung">
                                                                    <div class="onlyheadingmain blueIconClr"><i class='bx  bx-heart'></i> Care Plan - Logan Jones </div>
                                                                    <p>initial Assessment • residential care</p>
                                                                </div>
                                                                <div class="actions mt-0">
                                                                    <span class="roundBtntag greenShowbtn"> Active </span>
                                                                </div>
                                                            </header>
                                                            <div class="assessmentDateAndVersion carePlanWrapper">
                                                                <div class="activePlanStats">
                                                                    <div class="statItem">
                                                                        <div>
                                                                            <div class="statLabel">Assessment Date</div>
                                                                            <div class="statValue">December 19th, 2025</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="statItem">
                                                                        <div>
                                                                            <div class="statLabel">Assessed By</div>
                                                                            <div class="statValue">m.carter</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="statItem">
                                                                        <div>
                                                                            <div class="statLabel">Next Review</div>
                                                                            <div class="statValue">March 19th, 2026</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="statItem">
                                                                        <div>
                                                                            <div class="statLabel">Version</div>
                                                                            <div class="statValue">v1</div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> <!-- ****************************************************** -->

                                                        <div class="careDetailsWrapper">
                                                            <!-- Care Objectives -->
                                                            <div class="careSection">
                                                                <div class="sectionHeader">
                                                                    <span class="icon blue">◎</span>
                                                                    <h3>Care Objectives</h3>
                                                                </div>

                                                                <div class="objectiveCard">
                                                                    <div class="objectiveTop">
                                                                        <strong>Objective 1</strong>
                                                                        <span class="statusBadge gray">not started</span>
                                                                    </div>
                                                                    <p class="objectiveText">
                                                                        Increase school attendance to 80% by attending at least 4 out of 5 school days weekly.
                                                                    </p>
                                                                    <p class="metaLine">
                                                                        <strong>Success measures:</strong> School attendance records, feedback from school.
                                                                    </p>
                                                                    <p class="metaLine">
                                                                        <strong>Target:</strong> Jan 31, 2024
                                                                    </p>
                                                                </div>
                                                                <div class="objectiveCard">
                                                                    <div class="objectiveTop">
                                                                        <strong>Objective 2</strong>
                                                                        <span class="statusBadge gray">not started</span>
                                                                    </div>
                                                                    <p class="objectiveText">
                                                                        Increase school attendance to 80% by attending at least 4 out of 5 school days weekly.
                                                                    </p>
                                                                    <p class="metaLine">
                                                                        <strong>Success measures:</strong> School attendance records, feedback from school.
                                                                    </p>
                                                                    <p class="metaLine">
                                                                        <strong>Target:</strong> Jan 31, 2024
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <!-- Care Tasks & Interventions -->
                                                            <div class="careSection">
                                                                <div class="sectionHeader">
                                                                    <span class="icon purple">≡</span>
                                                                    <h3>Care Tasks & Interventions</h3>
                                                                </div>
                                                                <div class="taskCard">
                                                                    <div class="taskHeader">
                                                                        <span class="pill blue">Emotional Support</span>
                                                                        <span class="taskTime">🕒 weekly · 60 mins</span>
                                                                    </div>
                                                                    <h4>Emotional support session with counselor</h4>
                                                                    <div class="instructionBox">
                                                                        <strong>Special Instructions:</strong>
                                                                        Ensure Logan feels comfortable and safe to express feelings.
                                                                    </div>
                                                                    <p class="preferredTime"> Preferred time: Monday 3 PM </p>
                                                                </div>
                                                                <div class="taskCard">
                                                                    <div class="taskHeader">
                                                                        <span class="pill blue">Emotional Support</span>
                                                                        <span class="taskTime">🕒 weekly · 60 mins</span>
                                                                    </div>
                                                                    <h4>Emotional support session with counselor</h4>
                                                                    <div class="instructionBox">
                                                                        <strong>Special Instructions:</strong>
                                                                        Ensure Logan feels comfortable and safe to express feelings.
                                                                    </div>
                                                                    <p class="preferredTime"> Preferred time: Monday 3 PM </p>
                                                                </div>
                                                            </div>

                                                            <!-- Risk Factors -->
                                                            <div class="careSection">
                                                                <div class="sectionHeader">
                                                                    <span class="icon orange">⚠</span>
                                                                    <h3>Risk Factors</h3>
                                                                </div>

                                                                <div class="riskCard">
                                                                    <div class="riskTop">
                                                                        <strong>Increased anxiety about dental visits</strong>

                                                                        <div class="riskBadges">
                                                                            <span class="riskBadge danger">Likelihood: high</span>
                                                                            <span class="riskBadge danger">Impact: high</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="controlBox">
                                                                        <strong>Control Measures:</strong>
                                                                        Prepare Logan ahead of appointments, use relaxation techniques prior to visits.
                                                                    </div>
                                                                </div>
                                                                <div class="riskCard">
                                                                    <div class="riskTop">
                                                                        <strong>Increased anxiety about dental visits</strong>

                                                                        <div class="riskBadges">
                                                                            <span class="riskBadge danger">Likelihood: high</span>
                                                                            <span class="riskBadge danger">Impact: high</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="controlBox">
                                                                        <strong>Control Measures:</strong>
                                                                        Prepare Logan ahead of appointments, use relaxation techniques prior to visits.
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="CQCCompliantDocumentationPDF" style="background: #fff; padding: 30px 0; margin-top:30px; display:none">
                                                        <div>
                                                            <div class="bg-white text-black" style="font-family: Arial, sans-serif;">
                                                                <div style="border-bottom: 4px solid rgb(30, 64, 175); padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
                                                                    <h1 style="font-size: 32px; font-weight: bold; color: rgb(30, 64, 175); margin: 0px 0px 10px; text-transform: uppercase; letter-spacing: 2px;">RESIDENTIAL CARE PLAN</h1>
                                                                    <p style="font-size: 14px; color: rgb(107, 114, 128); margin: 0px;">CQC Compliant Documentation</p>
                                                                </div>
                                                                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; padding: 20px; background-color: rgb(248, 250, 252); border: 1px solid rgb(226, 232, 240); border-radius: 8px;">
                                                                    <div>
                                                                        <h2 style="font-size: 24px; font-weight: bold; color: rgb(30, 64, 175); margin-top: 0px; margin-bottom: 15px;">Client Name: Logan Jones</h2>
                                                                        <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                                                                            <tbody>
                                                                                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                                                    <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139); width: 180px;">Date of Birth:</td>
                                                                                    <td style="padding: 8px 0px;">29.10.2009</td>
                                                                                </tr>
                                                                                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                                                    <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">NHS Number:</td>
                                                                                    <td style="padding: 8px 0px;">Not recorded</td>
                                                                                </tr>
                                                                                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                                                    <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Room Number:</td>
                                                                                    <td style="padding: 8px 0px;">Not assigned</td>
                                                                                </tr>
                                                                                <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                                                    <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Care Plan Start Date:</td>
                                                                                    <td style="padding: 8px 0px;">19/12/2025</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Care Manager:</td>
                                                                                    <td style="padding: 8px 0px;">m.carter</td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <div style="border: 2px dashed rgb(203, 213, 225); border-radius: 8px; display: flex; align-items: center; justify-content: center; min-height: 200px; background-color: rgb(241, 245, 249); padding: 20px; text-align: center;">
                                                                        <div>
                                                                            <p style="font-size: 12px; color: rgb(100, 116, 139); margin: 0px;">CLIENT PHOTOGRAPH<br>(To be inserted with consent)</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">1. Personal Details &amp; Contact Information</h3>
                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Preferred Name:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Logan Jones</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Gender:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Legal Status:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Informal</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">GP Practice:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Language:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">English</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Religion:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                                                        </div>
                                                                    </div>
                                                                    <div style="margin-top: 15px;">
                                                                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 10px; color: rgb(71, 85, 105);">Next of Kin / Emergency Contact</h4>
                                                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                            <div style="font-size: 13px;">
                                                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Name:</p>
                                                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Carolanne Jones</p>
                                                                            </div>
                                                                            <div style="font-size: 13px;">
                                                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Relationship:</p>
                                                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Mum</p>
                                                                            </div>
                                                                            <div style="font-size: 13px;">
                                                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Contact Number:</p>
                                                                                <p style="margin: 0px; color: rgb(31, 41, 55);"></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">2. Capacity, Consent &amp; Legal Framework</h3>
                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Mental Capacity Assessment:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">To be assessed</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Capacity to Consent to Care:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">✗ No</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">LPA/Deputyship:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">None in place</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">DNACPR:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Not in place</p>
                                                                        </div>
                                                                    </div>
                                                                    <p style="font-size: 13px; margin-top: 10px; font-style: italic; color: rgb(100, 116, 139);">Client has been involved in the development of this care plan and has given informed consent.</p>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">6. Personal Care</h3>
                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Washing/Bathing:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Requires prompts only</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Dressing:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Independent with choices</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Continence:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Continent</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Skin Integrity:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Intact</p>
                                                                        </div>
                                                                    </div>
                                                                    <div style="margin-top: 15px; padding: 12px; background-color: rgb(239, 246, 255); border-left: 4px solid rgb(59, 130, 246); border-radius: 4px;">
                                                                        <p style="font-size: 13px; margin: 0px; color: rgb(30, 64, 175);"><strong>Care Approach:</strong> Respect privacy and dignity. Offer choice and promote independence.</p>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">11. Risk Assessments (Summary)</h3>
                                                                    <div style="margin-bottom: 10px;">
                                                                        <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Increased anxiety about dental visits</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 242, 242); color: rgb(220, 38, 38);">high risk</span></p>
                                                                    </div>
                                                                    <div style="margin-bottom: 10px;">
                                                                        <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Medication nonadherence due to side effects or refusal</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                                                    </div>
                                                                    <div style="margin-bottom: 10px;">
                                                                        <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Substance misuse (vaping) impacting health</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                                                    </div>
                                                                    <div style="margin-bottom: 10px;">
                                                                        <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Skin reactions due to new products or environmental factors</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">12. Safeguarding</h3>
                                                                    <p style="font-size: 13px; margin: 0px; color: rgb(31, 41, 55);">No current safeguarding concerns identified.</p>
                                                                    <p style="font-size: 13px; margin-top: 10px; color: rgb(100, 116, 139);">Staff to follow safeguarding policy and whistleblowing procedures. All concerns must be reported immediately.</p>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">13. Emergency Information</h3>
                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Emergency Contact:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Carolanne Jones (Mum)</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Hospital Preference:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Local NHS Trust</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">DNACPR Status:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Not in place</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">14. Review &amp; Monitoring</h3>
                                                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Care Plan Review Date:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">19/03/2026</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Reviewed By:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">m.carter</p>
                                                                        </div>
                                                                        <div style="font-size: 13px;">
                                                                            <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Client Involvement:</p>
                                                                            <p style="margin: 0px; color: rgb(31, 41, 55);">Yes</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                                                    <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">15. Signatures</h3>
                                                                    <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;">
                                                                        <thead>
                                                                            <tr style="background-color: rgb(241, 245, 249);">
                                                                                <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Role</th>
                                                                                <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Name</th>
                                                                                <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Signature</th>
                                                                                <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Date</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Client</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Logan Jones</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                                            </tr>
                                                                            <tr style="background-color: rgb(248, 250, 252);">
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Key Worker</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);"></td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Manager</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">m.carter</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                                                <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                                <div style="margin-top: 40px; padding: 20px; background-color: rgb(241, 245, 249); border-radius: 8px; text-align: center; break-inside: avoid;">
                                                                    <h4 style="font-size: 14px; font-weight: 600; margin-top: 0px; margin-bottom: 10px; color: rgb(30, 64, 175);">CQC Key Lines of Enquiry (KLOEs) Addressed</h4>
                                                                    <div style="display: flex; justify-content: center; gap: 15px; font-size: 13px; flex-wrap: wrap;">
                                                                        <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Safe</span>
                                                                        <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Effective</span>
                                                                        <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Caring</span>
                                                                        <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Responsive</span>
                                                                        <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Well-led</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div> <!-- CQCCompliantDocumentationPDF -->


                                                </div>
                                            </div>
                                            <!-- boarding click Care plan end  -->
                                            <div class="onboardingBox p-4 mt-4">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <i class="bx  bx-check-circle greenText"></i>
                                                            </div>
                                                            <div>
                                                                <p class="boardingStatus">Client onboarding complete! All stages approved.</p>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div> --}}

                                            </div>
                                            <div class="availabilityTabs__panel" id="OnboardingTabPanel2">

                                                <div class="p-20">
                                                    <div class="carer-form dolsSectionFirst" style="">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="flexBw">
                                                                    <label>Billing Details</label>
                                                                    <button type="button"
                                                                        class="bgBtn onboardingDetailsBtn"
                                                                        data-type="manage"><i class="bx bx-edit"></i>
                                                                        Manage Funding </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="basicTable mt20">
                                                            <table class="table" id="">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Name</th>
                                                                        <th>Funding Type</th>
                                                                        <th>Percentage</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="onboardingDetailsListHtml">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    <!-- <div class="carePlanWrapper dolsSectionSecond" id="dolsRenderList"
                                                            style="display: none;">

                                                        </div>
                                                        <div id="dolsPagination"></div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- onboarding end -->
                </div>
                <div class="content" id="clientCareTasksTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize careTaskheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain"><i class='bx bx-checklist'></i> Care Tasks </div>
                            </div>
                            <div class="actions mt-0">
                                <button class="btn borderBtn"> <i class="bx  bx-sparkles"></i> AI Generate from Care Needs</button>
                                <button class="allBtnUseColor" type="button" onclick="window.location.href='{{url('roster/care-task-add?client_id=')}}{{$client_id}}'"> <i class='bx  bx-plus'></i> Add Task</button>
                            </div>
                        </header>
                        <div class="p-20 p-b-0">
                            <div class="rota_dashboard-cards simpleCard">
                                <div class="rota_dash-card bg-blue-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Total Tasks</p>
                                        <h2 class="rota_count" id="clientCareTaskTotalCount">0</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-red-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Critical Priority</p>
                                        <h2 class="rota_count" id="clientCareTaskCriticalCount">0</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-orange-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">High Priority</p>
                                        <h2 class="rota_count orangeText" id="clientCareTaskHighCount">0</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-purple-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Two Staff Required</p>
                                        <h2 class="rota_count" id="clientCareTaskTwoStaffCount">0</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-20 p-t-0" id="renderHtmlClientCareTask">

                        </div>
                        <!-- <div class="p-20 p-t-0">
                            <div class="caretasknameandnumber m-b-10">
                                Personal Care <span>2</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard redborderleft mb-0">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Static Data</a></div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="radShowbtn">critical</span>
                                                <span class="inactive">Weekly</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class='bx  bx-clock'></i> <span>30 minutes</span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                            </div>
                                        </div>
                                        <div class="dFlexGap">
                                            <button class="borderBtn flex1" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="borderBtn deletewithBorder" data-id="120"> <i class="fa-regular fa-trash-can redText"></i> </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard blueborderleft mb-0">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Daily emotional support check-in</a></div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">critical</span>
                                                <span class="inactive">Weekly</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class='bx  bx-clock'></i> <span>30 minutes</span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button class="edit" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="delete" data-id="120"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div id="clientCareTaskPagination"></div>
                        <!-- <div class="p-20 p-t-0">
                            <div class="caretasknameandnumber m-b-10">
                                Nutrition
                                <span>3</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard redborderleft m-b-15">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Meal planning with nutrients focusing on balanced diet</a></div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">critical</span>
                                                <span class="inactive">Weekly</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class='bx  bx-clock'></i> <span>30 minutes</span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button class="edit" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="delete" data-id="120"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard blueborderleft m-b-15">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Follow healthy diet plan</a></div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">critical</span>
                                                <span class="inactive">Weekly</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class='bx  bx-clock'></i> <span>30 minutes</span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button class="edit" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="delete" data-id="120"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard blueborderleft m-b-15">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Follow healthy diet plan</a></div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sectionCarer">
                                            <div class="tags">
                                                <span class="yellow">critical</span>
                                                <span class="inactive">Weekly</span>
                                            </div>
                                        </div>
                                        <div class="details">
                                            <div class="item">
                                                <i class='bx  bx-clock'></i> <span>30 minutes</span>
                                            </div>
                                            <div class="item redalrttext">
                                                <i class='bx  bx-alert-circle'></i> <span>Alerts: Missed</span>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button class="edit" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="delete" data-id="120"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                    </div>
                </div>
                <div class="content" id="clientAlertsTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize clntalertheader">
                            <div class="clientHeadung">
                                <div class="dFlexNoAlign mb-2">
                                    <div class="onlyheadingmain radIconClr"><i class='bx  bx-alert-triangle'></i></i> Client Alerts </div>
                                    <div>
                                        <span class="careBadg redDarkBadges" id="activeAlertsCount">0 Active</span>
                                    </div>
                                    <div>
                                        <span class="careBadg redDarkBadgesAni" id="criticalAlertsCount">0 Critical</span>
                                    </div>
                                </div>
                                <p>Manage important alerts and warnings for this client</p>
                            </div>

                            <div class="actions mt-0">
                                <button class="btn addAssessmentBtn addalertClientDetailsBtn"> <i class='bx  bx-plus'></i> Add alert</button>
                            </div>
                        </header>

                        <div class="p-20">
                            <div class="clientFilterform addalertClientDetailsform" style="border: 2px solid #fdabab; background: #fef2f2;">

                                <div class="createNewAlert"><i class='bx  bx-alert-triangle'></i></i> Create New Alert </div>

                                <form id="clientAlertForm" class="addAlertForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Alert Type *</label>
                                                <select class="form-control checkClientAlert" id="alert_type_id" name="alert_type_id">
                                                    @foreach($alert_type as $alertVal)
                                                    <option value="{{$alertVal->id}}">{{$alertVal->title}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Severity *</label>
                                                <select class="form-control checkClientAlert" id="severity" name="severity">
                                                    <option value="Low">Low</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="High">High</option>
                                                    <option value="Critical">Critical</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Alert Title *</label>
                                                <input type="text" class="form-control checkClientAlert" name="alert_title" id="alert_title" placeholder="e.g., High Fall Risk - Use Walking Frame">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Detailed Description *</label>
                                                <textarea name="description" id="alert_description" class="form-control checkClientAlert" rows="3" cols="20" placeholder="Provide detailed information about this alert..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Action Required (Optional)</label>
                                                <textarea name="action_required" id="action_required" class="form-control" rows="2" cols="20" placeholder="Specific actions staff should take..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Expiry Date (Optional)</label>
                                                <input type="date" class="form-control" name="expiry_date" id="expiry_date">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="requiresStaff checkbox">
                                                <label>
                                                    <input type="checkbox" id="requires_staff_acknowledgment" name="requires_staff_acknowledgment" value="0"> Requires Staff Acknowledgment
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Display Alert On (select sections):</label>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked style="pointer-events: none;" id="all" name="all" value="1"> All </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled value="1" id="medication" name="medication"> medication </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled value="1" id="dashboard" name="dashboard"> dashboard </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled value="1" id="visits" name="visits"> visits </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled value="1" id="care_plan" name="care_plan"> care plan </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled value="1" id="schedule" name="schedule"> schedule </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button class="btn addAssessmentBtn saveClientAlert" type="button"> Create Alert </button>
                                            <button class="btn whiteBgBtncolor" type="submit"> Cancel </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="clientFilterform p-3 mt-0">
                                <div class="filtersSorting">
                                    <div class="flexBw w100">
                                        <div> <i class='bx bx-filter'></i> Filters & Sorting</div>
                                        <div class="addDailyCheck">
                                            <label for="selectAllAllert" class="lightBorderp fs13 py-2">
                                                <input type="checkbox" id="selectAllAllert">
                                                Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <!-- <form action=""> -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Severity</label>
                                            <select class="form-control severity_AlertFilter">
                                                <option value="1">All Severities</option>
                                                <option value="Critical">Critical</option>
                                                <option value="High">High</option>
                                                <option value="Medium">Medium</option>
                                                <option value="Low">Low</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control status_alertFilter">
                                                <option value="0">All Status</option>
                                                <option value="1">Active</option>
                                                <option value="2">Resolved</option>
                                                <option value="3">Archived</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Type</label>
                                            <select class="form-control type_alertFilter">
                                                <option value="0">All Type</option>
                                                <option value="1">Fall Risk</option>
                                                <option value="2">Dietary</option>
                                                <option value="3">Behavioral</option>
                                                <option value="4">Medical</option>
                                                <option value="5">Medication</option>
                                                <option value="8">Safeguarding</option>
                                                <option value="9">Allergy</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Sort By</label>
                                            <select class="form-control sortby_alertFilter">
                                                <option value="1">Severity</option>
                                                <option value="2">Date Created</option>
                                                <option value="3">Alert Type</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- </form> -->
                            </div>
                        </div>
                        <!-- pr alert box -->
                        <div class="p-20 pt-0 ">
                            <!-- blue suggestation -->
                            <div class="bg-blue-50 p-3 mb-3  rounded8" id="actionBox" style="display:none">
                                <div class="d-flex justify-content-between flexWrap ">
                                    <div class="fs13">
                                        <p class="mb-2 darkBlueTextp  font600" id="selectedCheckCount"> 9 selected </p>
                                        <p class="mb-0 blueText ">Critical & safeguarding/medication/allergy alerts require individual review</p>
                                    </div>
                                    <div>
                                        <div class="d-flex flexWrap gap-2 align-items-center">
                                            <div class="userMum all_acknowledgement">
                                                <span class="title mt-0 bgWhite50"><i class="bx bx-check-circle f18 me-2"></i> Acknowledge</span>
                                            </div>
                                            <div>
                                                <span class="careBadg darkGreenBadges" id="rosolveAlertCount">Resolve (9)</span>
                                                <input type="hidden" id="actualRosolveAlertCount">
                                            </div>
                                            <div>
                                                <i class='bx bx-x-circle f18 ms-2' id="closeActionBox"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- red box for critical -->
                            <div id="renderHtmlClientAlert">
                                <!-- <div class="redBorder borderLeftThick  rounded8 urReqSec p-3 manageDSysAlrt mt-2 mb-2">
                                    <div class="dFlexNoAlign">
                                        <div>
                                            <input class="checkBoxHW trans alertCheck" type="checkbox">
                                        </div>
                                        <div class="flex1">
                                            <div class="dFlexNoAlign flexWrap">
                                                <div>
                                                    <i class="bx bx-alert-circle redtext f18"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 h6Head font600 blackText">Missed Medication - Dextromethorphan
                                                    </h6>
                                                </div>
                                                <div>
                                                    <span class="carebadg redBorderBadg">
                                                        Critical
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="careBadg greenbadges">
                                                        active
                                                    </span>
                                                </div>
                                                <div class="userMum ">
                                                    <span class="title bgWhite50 mt-0 hoverBg">medication</span>
                                                </div>
                                            </div>
                                            <p class="fs12 textGray">Dextromethorphan (500) was due at 10:24 and has not been administered.</p>
                                            <div>
                                                <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                    Requires Individual Review
                                                </span>
                                            </div>
                                            <div class="bg-blue-50 fs12 p-2 rounded8 mt-3 mb-2">
                                                <p class=" font700 darkBlueTextp mb-1">Required Action: </p>
                                                <p class=" darkBlueTextp mb-0">
                                                    Administer medication immediately if still within safe window, otherwise contact prescriber
                                                </p>
                                            </div>
                                            <div class="dFlexNoAlign fs12 textGray">

                                                <p class="mb-2">Created: <span class="font600 blackText me-3">Feb 17</span>by Unknown Staff</p>


                                            </div>
                                            <div>
                                                <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on: <span class="font600 blackText ms-1"> dashboard, medication, all</span></p>
                                            </div>
                                            <div class="bg-yellow-50 P-2 rounded8">
                                                <div class="flexBw">
                                                    <div>
                                                        <p class="fs12 mb-0 darkOrangeTextp"><i class="bx bx-bell me-2"></i>Requires Acknowledgment
                                                        </p>
                                                    </div>
                                                    <div class="userMum">
                                                        <span class="title bgWhite50 hoverBg mt-0 "><i class="bx bx-check-circle f18 me-2"></i>Acknowledge</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dFlexNoAlign mt-2 allertMsgBtn">
                                                <div class="userMum">
                                                    <span class="title pgreenBtn hoverBg mt-0" style="color: #fff;"><i class="bx bx-check-circle f18 me-2"></i>Resolve</span>
                                                </div>

                                                <div class="userMum ">
                                                    <span class="title bgWhite50 hoverBg mt-0 "><i class="bx bx-archive-alt f18 me-2"></i>Archive</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                            <div id="clientAlertPagination"></div>
                            <!-- red for risk -->
                            <!-- <div class="redBorder borderLeftThick  rounded8 urReqSec p-3 manageDSysAlrt">
                                <div class="dFlexNoAlign">
                                    <div>
                                        <input class="checkBoxHW trans alertCheck" type="checkbox">
                                    </div>
                                    <div class="flex1">
                                        <div class="dFlexNoAlign flexWrap">
                                            <div>
                                                <i class="bx bx-alert-triangle redtext f18"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 h6Head font600 blackText">Missed Medication - Dextromethorphan
                                                </h6>
                                            </div>
                                            <div>
                                                <span class="carebadg redBorderBadg">
                                                    Critical
                                                </span>
                                            </div>
                                            <div>
                                                <span class="careBadg greenbadges">
                                                    active
                                                </span>
                                            </div>
                                            <div class="userMum ">
                                                <span class="title bgWhite50 mt-0 hoverBg">medication</span>
                                            </div>
                                        </div>
                                        <p class="fs12 textGray">Dextromethorphan (500) was due at 10:24 and has not been administered.</p>
                                        <div class="mb-2">
                                            <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                Requires Individual Review
                                            </span>
                                        </div>

                                        <div class="dFlexNoAlign fs12 textGray">
                                            <p class="mb-2">Created: <span class="font600 blackText me-3">Feb 17</span>by Unknown Staff</p>
                                        </div>
                                        <div>
                                            <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on: <span class="font600 blackText ms-1"> dashboard, medication, all</span></p>
                                        </div>

                                        <div class="dFlexNoAlign mt-2 allertMsgBtn">
                                            <div class="userMum">
                                                <span class="title pgreenBtn hoverBg mt-0" style="color: #fff;"><i class="bx bx-check-circle f18 me-2"></i>Resolve</span>
                                            </div>

                                            <div class="userMum ">
                                                <span class="title bgWhite50 hoverBg mt-0 "><i class="bx bx-archive-alt f18 me-2"></i>Archive</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!-- blue box for low -->
                            <!-- <div class="blueBorder borderLeftThick rounded8 lightBlueBg p-3 manageDSysAlrt">
                                <div class="dFlexNoAlign">
                                    <div>
                                        <input class="checkBoxHW trans alertCheck" type="checkbox">
                                    </div>
                                    <div class="flex1">
                                        <div class="dFlexNoAlign flexWrap">
                                            <div>
                                                <i class="bx bx-alert-triangle blueText f18"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 h6Head font600 blackText">Missed Medication - Dextromethorphan
                                                </h6>
                                            </div>
                                            <div>
                                                <span class="carebadg blueBorderBadg">
                                                    Low
                                                </span>
                                            </div>
                                            <div>
                                                <span class="careBadg greenBorderBadg">
                                                    active
                                                </span>
                                            </div>
                                            <div class="userMum ">
                                                <span class="title bgWhite50 mt-0 hoverBg">medication</span>
                                            </div>
                                        </div>
                                        <p class="fs12 textGray">It's testing data</p>
                                        <div class="mb-2">
                                            <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                Requires Individual Review
                                            </span>
                                        </div>
                                        <div class="dFlexNoAlign fs12 textGray">

                                            <p class="mb-2">Created: <span class="font600 blackText me-3">Feb 17</span>by Unknown Staff</p>


                                        </div>
                                        <div>
                                            <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on: <span class="font600 blackText ms-1"> dashboard, medication, all</span></p>
                                        </div>
                                        
                                        <div class="dFlexNoAlign mt-2 allertMsgBtn">
                                            <div class="userMum ">
                                                <span class="title pgreenBtn hoverBg mt-0" style="color: #fff;"><i class="bx bx-check-circle f18 me-2"></i>Resolve</span>
                                            </div>

                                            <div class="userMum ">
                                                <span class="title bgWhite50 hoverBg mt-0 "><i class="bx bx-archive-alt f18 me-2"></i>Archive</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!--orange high -->
                            <!-- <div class="orangeBorder borderLeftThick rounded8 bg-orange-50 p-3 manageDSysAlrt">
                                <div class="dFlexNoAlign">
                                    <div>
                                        <input class="checkBoxHW trans alertCheck" type="checkbox">
                                    </div>
                                    <div class="flex1">
                                        <div class="dFlexNoAlign flexWrap">
                                            <div>
                                                <i class="bx bx-bell orangeText f18"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 h6Head font600 blackText">Missed Medication - Dextromethorphan
                                                </h6>
                                            </div>
                                            <div>
                                                <span class="carebadg orangeBorderBadg">
                                                    High
                                                </span>
                                            </div>
                                            <div>
                                                <span class="careBadg muteBadges">
                                                    Resolve
                                                </span>
                                            </div>
                                            <div class="userMum ">
                                                <span class="title bgWhite50 mt-0 hoverBg">other</span>
                                            </div>
                                        </div>
                                        <p class="fs12 textGray">Dextromethorphan (500) was due at 10:24 and has not been administered.</p>
                                        <div>
                                            <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                Requires Individual Review
                                            </span>
                                        </div>
                                        <div class="bg-blue-50 fs12 p-2 rounded8 mt-3 mb-2">
                                            <p class=" font700 darkBlueTextp mb-1">Required Action: </p>
                                            <p class=" darkBlueTextp mb-0">
                                                Administer medication immediately if still within safe window, otherwise contact prescriber
                                            </p>
                                        </div>
                                        <div class="dFlexNoAlign fs12 textGray">

                                            <p class="mb-2">Created: <span class="font600 blackText me-3">Feb 17</span>by Unknown Staff</p>


                                        </div>
                                        <div>
                                            <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on:<span class="font600 blackText ms-1">dashboard, medication, all</span></p>
                                        </div>
                                        <div class="bg-yellow-50 P-2 rounded8">
                                            <div class="flexBw">
                                                <div>
                                                    <p class="fs12 mb-0 darkOrangeTextp font600"><i class="bx bx-bell me-2"></i>Requires Acknowledgment
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="bg-greenp-50 P-2 rounded8 mt-2">

                                            <div>
                                                <p class="fs12 mb-0 darkGreenTextp"><span class="font700 me-1">Resolved:</span> Jan 27, 17:33 by Unknown Staff
                                                </p>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <!--yellow medium -->
                            <!-- <div class="yellowBorder borderLeftThick rounded8 bg-yellow-50 p-3 manageDSysAlrt">
                                <div class="dFlexNoAlign">
                                    <div>
                                        <input class="checkBoxHW trans alertCheck" type="checkbox">
                                    </div>
                                    <div class="flex1">
                                        <div class="dFlexNoAlign flexWrap">
                                            <div>
                                                <i class="bx bx-alert-triangle yellowText f18"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 h6Head font600 blackText">Missed Medication - Dextromethorphan
                                                </h6>
                                            </div>
                                            <div>
                                                <span class="carebadg yellowBorderBadg">
                                                    Medium
                                                </span>
                                            </div>
                                            <div>
                                                <span class="careBadg muteBadges">
                                                    Resolve
                                                </span>
                                            </div>
                                            <div class="userMum ">
                                                <span class="title bgWhite50 mt-0 hoverBg">Medical</span>
                                            </div>
                                        </div>
                                        <p class="fs12 textGray">Immediate and ongoing actions are required to reduce the risk of falls and ensure the individual’s safety. Staff must closely monitor the individual at all times, particularly during mobility, transfers, and personal care activities. Assistance should be provided when standing, walking, or using stairs, and the individual should be encouraged to use prescribed mobility aids correctly at all times.</p>
                                        <div>
                                            <span class="careBadg yellowBorderLight yellowHoverUnset">
                                                Requires Individual Review
                                            </span>
                                        </div>
                                        <div class="bg-blue-50 fs12 p-2 rounded8 mt-3 mb-2">
                                            <p class=" font700 darkBlueTextp mb-1">Required Action: </p>
                                            <p class=" darkBlueTextp mb-0">
                                                Administer medication immediately if still within safe window, otherwise contact prescriber
                                            </p>
                                        </div>
                                        <div class="dFlexNoAlign fs12 textGray">

                                            <p class="mb-2 w50">Created: <span class="font600 blackText me-3">Feb 17</span>by Unknown Staff</p>
                                            <p class="mb-2">Expires: <span class="font600 blackText me-3">Jan 15</span></p>
                                        </div>
                                        <div>
                                            <p class="mb-2 fs12 textGray verticalCenter"> <i class="bx bx-eye  me-1"></i>Shown on:<span class="font600 blackText ms-1">dashboard, medication, all</span></p>
                                        </div>

                                        <div class="bg-greenp-50 P-2 rounded8 mt-2">
                                            <div class="darkGreenTextp">
                                                <p class="fs12 mb-0"><span class="font700 me-1">Resolved:</span> Jan 27, 17:33 by Unknown Staff
                                                </p>
                                                <p class="fs12 mb-0">Resolved by manager</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                        <!-- pr alert box end -->



                        <!-- <div class="leavebanktabCont">
                            <i class='bx  bx-alert-triangle'></i>
                            <p>No alerts match the selected filters</p>
                        </div> -->
                    </div>
                </div>
                <div class="content" id="clientAIInsightsTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize aIInsightsheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain purpleiconclr"><i class='bx  bx-brain'></i> AI Care Insights </div>
                            </div>
                        </header>
                        <div class="p-20 p-b-0">
                            <div class="aiCareSection">
                                <label class="useAItoanalyzelabel">Use AI to analyze Logan Jones's data and generate insights</label>
                                <div class="useAIBtns">
                                    <button class="btn aiBtnFst aiInsightsBtn" data-tab_id="1"><i class="bx  bx-sparkles"></i> Proactive Analysis </button>
                                    <button class="btn aiBtnSec aiInsightsBtn" data-tab_id="2"><i class="bx bx-file-detail"></i> Handover Summary </button>
                                    <button class="btn aiBtnThrd aiInsightsBtn" data-tab_id="3"><i class='bx  bx-trending-up'></i> Care Plan Review </button>
                                </div>
                            </div>
                            <div class="p-b-20 productAnalysisHideDefault" style="display: none;">
                                <div class="topHeaderCont">
                                    <div class="proactiveAnalysis">
                                        <span class="badge">Proactive Analysis</span>
                                    </div>
                                    <div class="header-actions addnewicons">
                                        <button class="btn borderBtn"><i class='bx  bx-copy'></i> Copy</button>
                                        <button class="btn borderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Export</button>
                                        <button class="btn borderBtn"><i class='bx  bx-edit'></i> New Analysis</button>
                                    </div>
                                </div>
                                <div class="riskAssessmentWrap">
                                    <div class="riskHeader">
                                        <span class="riskIcon">⚠</span>
                                        <span class="riskTitle">Risk Assessment: <strong>MEDIUM</strong></span>
                                    </div>

                                    <!-- Risk Item -->
                                    <div class="riskItem">
                                        <p class="riskText">
                                            Lack of defined mobility and cognitive function status could lead to unnoticed complications.
                                        </p>

                                        <span class="riskBadge medium">medium</span>

                                        <div class="riskSection">
                                            <span class="riskSectionTitle">Indicators:</span>
                                            <ul>
                                                <li>No specified mobility level</li>
                                                <li>Cognitive function unspecified</li>
                                            </ul>
                                        </div>

                                        <div class="riskSection">
                                            <span class="riskSectionTitle">Actions:</span>
                                            <ul class="actionList">
                                                <li>Conduct a comprehensive mobility and cognitive assessment to establish baseline functionality.</li>
                                                <li>Regular check-ins to monitor any changes in mobility or cognitive ability.</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Risk Item -->
                                    <div class="riskItem">
                                        <p class="riskText">
                                            Potential for poor dietary compliance affecting health outcomes.
                                        </p>

                                        <span class="riskBadge medium">medium</span>

                                        <div class="riskSection">
                                            <span class="riskSectionTitle">Indicators:</span>
                                            <ul>
                                                <li>Goal to achieve 5 servings of fruits/vegetables daily assessed weekly.</li>
                                                <li>No specific dietary tracking or compliance guidelines noted.</li>
                                            </ul>
                                        </div>

                                        <div class="riskSection">
                                            <span class="riskSectionTitle">Actions:</span>
                                            <ul class="actionList">
                                                <li>Introduce a food diary to track daily servings of fruits and vegetables.</li>
                                                <li>Schedule regular dietary evaluations with a nutritionist.</li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>

                                <div class="proactiveSuggestionsWrap">
                                    <div class="psHeader">
                                        <span class="psIcon"><i class='bx  bx-trending-up'></i> </span>
                                        <span class="psTitle">Proactive Suggestions</span>
                                    </div>

                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Implement weekly meal planning sessions with Logan to encourage balanced nutrition.
                                            </p>
                                            <span class="psRisk high">high</span>
                                        </div>

                                        <p class="psSubText">
                                            Active involvement can enhance Logan's understanding and compliance with dietary goals.
                                        </p>

                                        <span class="psTag dietary">Dietary</span>
                                    </div>

                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Introduce a nightly wind-down routine to promote relaxation before bed.
                                            </p>
                                            <span class="psRisk high">high</span>
                                        </div>

                                        <p class="psSubText">
                                            Establishing habits can significantly improve Logan's likelihood of achieving the sleep goal.
                                        </p>

                                        <span class="psTag sleep">Sleep Management</span>
                                    </div>

                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Engage Logan in mindfulness activities or relaxation techniques during counseling sessions.
                                            </p>
                                            <span class="psRisk medium">medium</span>
                                        </div>

                                        <p class="psSubText">
                                            This can complement emotional skill-building and enhance counseling efficacy.
                                        </p>

                                        <span class="psTag emotional">Emotional Management</span>
                                    </div>

                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Explore additional resources or tutoring to assist with academic performance and attendance.
                                            </p>
                                            <span class="psRisk medium">medium</span>
                                        </div>

                                        <p class="psSubText">
                                            Supportive measures may counteract potential academic stress and promote increased attendance.
                                        </p>

                                        <span class="psTag education">Educational Support</span>
                                    </div>

                                </div>

                                <div class="careInsightsWrap">

                                    <!-- Patterns Identified -->
                                    <div class="patternsCard">
                                        <h3 class="cardTitle">Patterns Identified</h3>

                                        <div class="patternItem">
                                            <span class="patternBadge">Frequency: Weekly</span>
                                            <span class="patternBadge">Client may struggle to meet attendance and dietary goals, impacting emotional stability.</span>
                                        </div>

                                        <div class="patternItem">
                                            <span class="patternBadge">Frequency: Monthly</span>
                                            <span class="patternBadge">Inconsistencies in sleep patterns may correlate with daytime tiredness and emotional management issues.</span>
                                        </div>

                                        <div class="patternItem">
                                            <span class="patternBadge">Frequency: Bi-annual</span>
                                            <span class="patternBadge">Inconsistencies in sleep patterns may corrs and emotional management issues.</span>
                                        </div>
                                    </div>

                                    <!-- Care Plan Recommendations -->
                                    <div class="carePlanCard">
                                        <h3 class="cardTitle">Care Plan Recommendations</h3>

                                        <div class="careItem">
                                            <span class="careTag green">Mobility and Cognitive Function</span>
                                            <span class="careRisk high">high</span>
                                            <p class="careText">
                                                Add assessments for mobility and cognitive function to the care plan.
                                            </p>
                                        </div>

                                        <div class="careItem">
                                            <span class="careTag green">Dietary Compliance</span>
                                            <span class="careRisk medium">medium</span>
                                            <p class="careText">
                                                Implement a structured dietary assessment program with reminders for fruit/vegetable intake.
                                            </p>
                                        </div>

                                        <div class="careItem">
                                            <span class="careTag green">Sleep Hygiene</span>
                                            <span class="careRisk medium">medium</span>
                                            <p class="careText">
                                                Incorporate sleep tracking tools and behavioral interventions to establish better sleep patterns.
                                            </p>
                                        </div>

                                        <div class="careItem">
                                            <span class="careTag green">Emotional Support</span>
                                            <span class="careRisk medium">medium</span>
                                            <p class="careText">
                                                Ensure that counseling sessions include specific goals for emotional management progress tracking.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-b-20 handoverSummaryHideDefault" style="display: none;">
                                <div class="topHeaderCont">
                                    <div class="proactiveAnalysis">
                                        <span class="badge">Handover Summary</span>
                                    </div>
                                    <div class="header-actions addnewicons">
                                        <button class="btn borderBtn"><i class='bx  bx-copy'></i> Copy</button>
                                        <button class="btn borderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Export</button>
                                        <button class="btn borderBtn"><i class='bx  bx-edit'></i> New Analysis</button>
                                    </div>
                                </div>
                                <div class="proactiveSuggestionsWrap handoverSummary">
                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Overall Status
                                            </p>
                                        </div>
                                        <p class="psSubText">
                                            Logan Jones is currently stable with no active alerts or recent incidents. Medication compliance records indicate no recent administrations.
                                        </p>
                                    </div>
                                </div>

                                <div class="careInsightsWrap">
                                    <!-- Patterns Identified -->
                                    <div class="patternsCard bg-red-50">
                                        <h3 class="cardTitle rota_count greenText"> <i class="bx  bx-alert-triangle"></i> Immediate Attention Needed</h3>

                                        <ul class="space-y-1">
                                            <li class="textSm"><span class="radIconClr"><i class='bx  bx-x-circle'></i></span> Address active alert regarding ADASDASD</li>
                                            <li class="textSm"><span class="radIconClr"><i class='bx  bx-x-circle'></i></span> Ensure medication compliance with Logan's prescribed regimen.</li>
                                            <li class="textSm"><span class="radIconClr"><i class='bx  bx-x-circle'></i></span> No information found on mobility, key conditions, or mental health.</li>
                                        </ul>
                                    </div>
                                    <div class="careItem m-t-15 bg-orange-50">
                                        <h3 class="cardTitle">Ongoing Concerns</h3>
                                        <ul class="space-y-1">
                                            <li class="textSm">• Lack of defined mobility assessment for Logan.</li>
                                            <li class="textSm">• Undefined key conditions that need to be addressed.</li>
                                            <li class="textSm">• No recent mental health evaluation available.</li>
                                        </ul>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="patternsCard">
                                                <h3 class="cardTitle">Medication Notes</h3>

                                                <ul class="space-y-1">
                                                    <li class="textSm">No recent medications administered; monitor for upcoming schedules.</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="patternsCard">
                                                <h3 class="cardTitle">Behavioral Observations</h3>

                                                <ul class="space-y-1">
                                                    <li class="textSm">No significant behavioral observations documented in the past week.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="patternsCard recommendationsSift m-t-15">
                                        <h3 class="cardTitle">Recommendations for Shift</h3>
                                        <ul class="space-y-1">
                                            <li class="textSm"><i class='bx  bx-check-circle'></i> Clarify and assess mobility and key conditions during this shift.</li>
                                            <li class="textSm"><i class='bx  bx-check-circle'></i> Schedule mental health evaluation as soon as possible.</li>
                                            <li class="textSm"><i class='bx  bx-check-circle'></i> Monitor for any changes in behavior or needs throughout the shift.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="p-b-20 carePlanReviewHideDefault" style="display:none">
                                <div class="topHeaderCont">
                                    <div class="proactiveAnalysis">
                                        <span class="badge">Care Plan Review</span>
                                    </div>
                                    <div class="header-actions addnewicons">
                                        <button class="btn borderBtn"><i class='bx  bx-copy'></i> Copy</button>
                                        <button class="btn borderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Export</button>
                                        <button class="btn borderBtn"><i class='bx  bx-edit'></i> New Analysis</button>
                                    </div>
                                </div>
                                <div class="proactiveSuggestionsWrap handoverSummary">
                                    <!-- Item -->
                                    <div class="psItem">
                                        <div class="psTop">
                                            <p class="psMainText">
                                                Overall Assessment
                                            </p>
                                        </div>
                                        <p class="psSubText">
                                            The current care plan requires adjustments due to limited activity engagement and challenges in meeting initial objectives. Targets should be modified to become more achievable, thereby ensuring Logan feels supported rather than overwhelmed.
                                        </p>
                                    </div>
                                </div>

                                <div class="careInsightsWrap">

                                    <!-- Care Plan Recommendations -->
                                    <div class="carePlanCard">
                                        <h3 class="cardTitle">Recommended Objectives</h3>

                                        <div class="careItem">
                                            <span class="recommendedTitle">Increase school attendance to 60% by attending at least 3 out of 5 school days weekly due to current attendance challenges.</span>
                                            <span class="careRisk high">high</span>
                                            <p class="careText">
                                                Add assessments for mobility and cognitive function to the care plan.
                                            </p>
                                            <p class="careText">
                                                Target: 2024-01-31
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Increase school attendance to 60% by attending at least 3 out of 5 school days weekly due to current attendance challenges.</span>
                                            <span class="careRisk high">medium</span>
                                            <p class="careText">
                                                Add assessments for mobility and cognitive function to the care plan.
                                            </p>
                                            <p class="careText">
                                                Target: 2024-01-31
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Increase school attendance to 60% by attending at least 3 out of 5 school days weekly due to current attendance challenges.</span>
                                            <span class="careRisk high">high</span>
                                            <p class="careText">
                                                Add assessments for mobility and cognitive function to the care plan.
                                            </p>
                                            <p class="careText">
                                                Target: 2024-01-31
                                            </p>
                                        </div>
                                    </div>

                                    <div class="carePlanCard recommendedTasks">
                                        <h3 class="cardTitle">Recommended Tasks</h3>

                                        <div class="careItem">
                                            <span class="recommendedTitle">Prepare relaxing activities before dental visits, such as deep breathing or visualization techniques.</span>
                                            <span class="careRisk high">emotional</span>
                                            <p class="careText">
                                                To address increased anxiety about dental visits and ensure Logan feels comfortable, proactive measures can help reduce stress.
                                            </p>
                                            <p class="careText frequency">
                                                Frequency: as needed
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Bi-weekly food diary review focusing on achieving 3 servings of fruits and vegetables.</span>
                                            <span class="careRisk high">nutrition</span>
                                            <p class="careText">
                                                This adjustment will provide opportunities to reflect, modify, and improve meal intake without overwhelming Logan.
                                            </p>
                                            <p class="careText Frequency">
                                                Frequency: bi-weekly
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Establish a check-in discussion post each counseling session to gather feedback and address any concerns Logan might have.</span>
                                            <span class="careRisk high">emotional</span>
                                            <p class="careText">
                                                This additional task will help in tracking Logan's feelings about his counseling sessions and address any issues as they arise.
                                            </p>
                                            <p class="careText Frequency">
                                                Frequency: after each session
                                            </p>
                                        </div>
                                    </div>

                                    <div class="carePlanCard riskAssessmentUpdates">
                                        <h3 class="cardTitle">Risk Assessment Updates</h3>

                                        <div class="careItem">
                                            <span class="recommendedTitle">Increased anxiety about dental visits</span>
                                            <span class="careRisk high">Likelihood: medium</span>
                                            <span class="careRisk high">Impact: high</span>
                                            <p class="careText">
                                                <strong>Control Measures: </strong> Continue using relaxation techniques and discuss any ongoing concerns about dental visits with the counselor.
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Inadequate medication adherence due to non-compliance</span>
                                            <span class="careRisk high">Likelihood: medium</span>
                                            <span class="careRisk high">Impact: high</span>
                                            <p class="careText">
                                                <strong> Control Measures: </strong> Expand education efforts and involve family members in motivation and accountability.
                                            </p>
                                        </div>
                                        <div class="careItem">
                                            <span class="recommendedTitle">Potential for social isolation due to lack of after-school activities</span>
                                            <span class="careRisk high">Likelihood: medium</span>
                                            <span class="careRisk high">Impact: high</span>
                                            <p class="careText">
                                                <strong> Control Measures:</strong> Encourage participation in group activities to enhance social skills and reduce anxiety.
                                            </p>
                                        </div>
                                    </div>



                                </div>


                            </div>

                        </div>
                    </div>
                </div>
                <div class="content" id="clientCarePlanTab">
                    <div class="carePlanTabCont carePlanBtnSectionFirst" style="display: ;">
                        <div class="workHoursHeader">
                            <div class="title"><i class='bx  bx-heart'></i> Care Plans</div>
                            <div class="actions">
                                <button class="btn allBtnUseColor" data-toggle="modal" data-target="#generateCarePlanModal" style="margin-right:10px;"><i class='bx bx-sparkles'></i> Generate AI Plan</button>
                            </div>
                        </div>

                        <div class="carePlanWrapper" id="carePlanTabListContainer">
                            <div class="text-center p-4"><i class="bx bx-loader-alt bx-spin" style="font-size:24px"></i> Loading care plans...</div>
                        </div>
                    </div>


                    <div class="carePlanBtnSectionSecond" style="display: none;">
                        <div class="topHeaderCont">
                            <div>
                                <button class="btn borderBtn backBtn" id="planBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back to Care Plans</button>
                            </div>
                            <div class="header-actions addnewicons">
                                <button class="btn allbuttonDarkClr"> Standard View</button>
                                <button class="btn borderBtn purpleBorderBtn"> CQC Print Format</button>
                                <button class="btn borderBtn blueBorderBtn"><i class='bx  bx-printer'></i> Print </button>
                                <button class="btn borderBtn greenBorderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Export PDF </button>
                                <button class="btn allBtnUseColor"><i class='bx  bx-edit'></i> Edit Plan</button>
                            </div>
                        </div>
                        <div class="CarePlanAllObjective" style="display: ;">
                            <div class="assessmentDetails leave-card p-0">
                                <header class="panel-heading headingCapitilize careTaskheader">
                                    <div class="clientHeadung">
                                        <div class="onlyheadingmain blueIconClr"><i class='bx  bx-heart'></i> Care Plan - {{$clientDetails['name']}} </div>
                                        <p>initial Assessment • residential care</p>
                                    </div>
                                    <div class="actions mt-0">
                                        <span class="careBadg badgeCarePlanDetail"> </span>
                                    </div>
                                </header>
                                <div class="assessmentDateAndVersion carePlanWrapper">
                                    <div class="activePlanStats">
                                        <div class="statItem">
                                            <div>
                                                <div class="statLabel">Assessment Date</div>
                                                <div class="statValue carePlanAssessmentDate"></div>
                                            </div>
                                        </div>
                                        <div class="statItem">
                                            <div>
                                                <div class="statLabel">Assessed By</div>
                                                <div class="statValue carePlanAssessedBy"></div>
                                            </div>
                                        </div>
                                        <div class="statItem carePlanReviewDateSection">
                                            <div>
                                                <div class="statLabel">Next Review</div>
                                                <div class="statValue carePlanReviewDate"></div>
                                            </div>
                                        </div>
                                        <div class="statItem">
                                            <div>
                                                <div class="statLabel">Version</div>
                                                <div class="statValue">v1</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- personal details p -->
                                    <div class="mt20">
                                        <h5 class="h5Head mb-0"> Personal Details </h5>
                                        <div class="activePlanStats mt-4">
                                            <div class="statItem carePlanPreferedNameSection">
                                                <div>
                                                    <div class="statLabel">Preferred Name</div>
                                                    <div class="statValue carePlanPreferedName">

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="statItem carePlanLanguageSection">
                                                <div>
                                                    <div class="statLabel">Language</div>
                                                    <div class="statValue carePlanLanguage"></div>
                                                </div>
                                            </div>
                                            <div class="statItem carePlanReligionSection">
                                                <div>
                                                    <div class="statLabel">Religion</div>
                                                    <div class="statValue carePlanReligion"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="mt20">
                                        <div class="statItem carePlanCulturalNeedsSection">
                                            <div>
                                                <div class="statLabel">Cultural Needs</div>
                                                <div class="statValue carePlanCulturalNeeds">Cultural Needs</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end personal details p-->
                                </div>
                            </div> <!-- ****************************************************** -->
                            <!-- physical health p -->

                            <div class="emergencyMain carePlanWrapper careDetailsWrapper p24">
                                <div class="sectionHeader">
                                    <span class="icon blue"><i class="bx bx-pulse fs23 redtext"></i></span>
                                    <h3>Physical Health</h3>
                                </div>
                                <div class="activePlanStats row" style="gap: 0px;">
                                    <div class="col-md-4 col-sm-6">
                                        <div class="statItem" style="flex-grow: unset;">
                                            <div>
                                                <div class="statLabel">Mobility</div>
                                                <div class="statValue ">independent</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <div class="statItem">
                                            <div>
                                                <div class="statLabel">Continence</div>
                                                <div class="statValue ">continent</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- end physical health p -->
                            <!-- Mental Health & Communication p  -->
                            <div class="emergencyMain carePlanWrapper careDetailsWrapper p24">
                                <div class="sectionHeader">
                                    <span class="icon blue"><i class="bx bx-brain fs23 purpleTextp"></i></span>
                                    <h3>Mental Health & Communication</h3>
                                </div>

                            </div>

                            <!--end Mental Health & Communication p  -->



                            <div class="careDetailsWrapper">
                                <!-- Care Objectives -->
                                <div class="careSection">
                                    <div class="sectionHeader">
                                        <span class="icon blue">◎</span>
                                        <h3>Care Objectives</h3>
                                    </div>

                                    <div class="carePlanObjectiveHtmlRender">
                                    </div>
                                </div>

                                <!-- Care Tasks & Interventions -->
                                <div class="careSection">
                                    <div class="sectionHeader">
                                        <span class="icon purple">≡</span>
                                        <h3>Care Tasks & Interventions</h3>
                                    </div>

                                    <div class="carePlanTaskHtmlRender">

                                    </div>
                                </div>
                                <!-- Medication Management p -->
                                <div class="emergencyMain carePlanWrapper careDetailsWrapper p24">
                                    <div class="sectionHeader">
                                        <span class="icon blue"><i class="bx bx-pill fs23 pinkText"></i></span>
                                        <h3>Medication Management
                                        </h3>
                                    </div>
                                    <div class="muteBg rounded8 p-4">
                                        <div class="row activePlanStats" style="gap:0;">

                                            <div class="col-md-4 col-sm-6">
                                                <div class="statItem">
                                                    <div>
                                                        <p class="fs13 textGray500 mb-2">Self Administers</p>
                                                        <h6 class="h6Head mb-0 carePlanSelfAdministers"></h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 col-sm-6">
                                                <div class="statItem">
                                                    <div>
                                                        <p class="fs13 textGray500 mb-2">Support Level</p>
                                                        <h6 class="h6Head mb-0 carePlanSupportLevel"></h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 col-sm-6 carePlanPharmacyDetailsSection">
                                                <div class="statItem">
                                                    <div>
                                                        <p class="fs13 textGray500 mb-2">Pharmacy</p>
                                                        <h6 class="h6Head mb-0 carePlanPharmacyDetails"></h6>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 col-sm-6 carePlanGPDetailsSection">
                                                <div class="statItem">
                                                    <div>
                                                        <p class="fs13 textGray500 mb-2">GP</p>
                                                        <h6 class="h6Head mb-0 carePlanGPDetails"></h6>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="bg-red-50 rounded8 p-4 carePlanAllergiesSection">
                                            <div class="dFlexGap mb-2">
                                                <p class="mb-0 fs13 font600 darkRedText">
                                                    ⚠️ Allergies & Sensitivities
                                                </p>
                                            </div>
                                            <p class="fs14 redText mb-0 carePlanAllergies"></p>
                                        </div>
                                        <div class="mt-4">
                                            <h5 class="h5Head">Current Medications</h5>
                                        </div>
                                        <div class="carePlanMedicalHtmlRender">

                                        </div>
                                    </div>
                                </div>
                                <!--end Medication Management p -->

                                <!-- Daily Routine p -->
                                <div class="emergencyMain carePlanWrapper careDetailsWrapper p24">
                                    <div class="sectionHeader">
                                        <span class="icon blue"><i class="bx bx-clock fs23 cyanText"></i></span>
                                        <h3>Daily Routine </h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 carePlan_m_RoutineSection">
                                            <div class="bg-yellow-50 p-4 rounded8" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-sun f20 yellowText"></i>
                                                        <span>Morning</span>
                                                    </h5>

                                                </div>
                                                <p class="mb-0 fs14 textGray600 carePlan_m_Routine">

                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-orange-50 p-4 rounded8 carePlan_a_RoutineSection" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-sun-rise f20 orangeText"></i>
                                                        <span>Afternoon</span>
                                                    </h5>

                                                </div>
                                                <p class="mb-0 fs14 textGray600 carePlan_a_Routine">

                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6 m-t-10 carePlan_e_RoutineSection">
                                            <div class="bg-purple-50 p-4 rounded8" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-moon f20 purpleTextp"></i>
                                                        <span>Evening</span>
                                                    </h5>
                                                </div>
                                                <p class="mb-0 fs14 textGray600 carePlan_e_Routine">

                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-md-6 m-t-10 carePlan_n_RoutineSection">
                                            <div class="muteBg p-4 rounded8" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-cloud-moon f20 textGray"></i>
                                                        <span>Night</span>
                                                    </h5>
                                                </div>
                                                <p class="mb-0 fs14 textGray600 carePlan_n_Routine">

                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end daily rountine p  -->
                                <!--Preferences & Special Requirements p  -->
                                <div class="emergencyMain carePlanWrapper careDetailsWrapper p24">
                                    <div class="sectionHeader">
                                        <span class="icon blue"><i class="bx bx-star fs23 yellowText"></i></span>
                                        <h3>Preferences & Special Requirements </h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 carePlanLikeSection">
                                            <div class="bg-greenp-50 p-4 rounded8" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-thumb-up f20 greenText"></i>
                                                        <span>Likes</span>
                                                    </h5>

                                                </div>
                                                <div class="mb-0 fs14 textGray600 dFlexGap">
                                                    <span class="dotC thickDotC"></span>
                                                    <span class="carePlanLike"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 carePlanDislikeSection">
                                            <div class="bg-red-50 p-4 rounded8" style="border: unset;">
                                                <div class="dFlexGap">
                                                    <h5 class="h6Head"><i class="bx bx-thumb-down f20 redtext"></i>
                                                        <span>Dislikes</span>
                                                    </h5>

                                                </div>
                                                <div class="mb-0 fs14 dFlexGap textGray600">
                                                    <span class="dotC thickDotC"></span>
                                                    <span class="carePlanDislike"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <h5 class="h5Head carePlanHobbySection">Hobbies & Interests</h5>
                                        <span class="borderBadg carePlanHobby"></span>
                                        <div class="row mt-4">
                                            <div class="col-md-6 carePlanfoodSection">
                                                <h6 class="h6Head mb-2">Food Preferences</h6>
                                                <p class="textGray500 fs14 mb-0 carePlanFood"></p>
                                            </div>
                                            <div class="col-md-6 carePlanPersonalSection">
                                                <h6 class="h6Head mb-2">Personal Care Preferences</h6>
                                                <p class="textGray500 fs14 mb-0 carePlanPersonal"></p>
                                            </div>
                                            <div class="col-md-6 m-t-10 carePlanCommunicationSection">
                                                <h6 class="h6Head mb-2">Communication Preferences</h6>
                                                <p class="textGray500 fs14 carePlanCommunication"></p>
                                            </div>
                                            <div class="col-md-6 m-t-10 carePlanSocialSection">
                                                <h6 class="h6Head mb-2">Social Preferences</h6>
                                                <p class="textGray500 fs14 carePlanSocial"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Preferences & Special Requirements p -->


                                <!-- Risk Factors -->
                                <div class="careSection">
                                    <div class="sectionHeader">
                                        <span class="icon orange">⚠</span>
                                        <h3>Risk Factors</h3>
                                    </div>
                                    <div class="carePlanRiskHtmlRender">

                                    </div>
                                </div>
                                <!-- Emergency Information p -->
                                <div class="emergencyMain carePlanWrapper careDetailsWrapper">
                                    <div class="emergencyHeader">
                                        <div class="dFlexGap darkRedText">
                                            <i class="bx bx-alert-triangle fs23"></i>
                                            <h5 class="h5Head darkRedText mb-0">Emergency Information </h5>
                                        </div>
                                    </div>
                                    <div class="p24">
                                        <div class="carePlanPreferedHospSection">
                                            <p class="fs13 textGray500 mb-2">Preferred Hospital</p>
                                            <h6 class="h6Head mb-0 carePlanPreferedHosp"></h6>
                                        </div>
                                        <div class="mt-4 carePlanPreferedDnacprSection">
                                            <span class="careBadg redBadges">DNACPR in Place - See DNACPR Section Above
                                            </span>
                                        </div>
                                        <div class="mt-4 carePlanPreferedProtocolSection">
                                            <p class="fs13 textGray500 mb-2">Emergency Protocol</p>
                                            <p class="fs14 textGay500 mb-0 carePlanPreferedProtocol"></p>
                                        </div>
                                    </div>
                                </div>

                                <!--end Emergency Information p -->
                            </div>
                        </div>

                        <div class="CQCCompliantDocumentationPDF" style="background: #fff; padding: 30px 0; margin-top:30px; display:none">
                            <div>
                                <div class="bg-white text-black" style="font-family: Arial, sans-serif;">
                                    <div style="border-bottom: 4px solid rgb(30, 64, 175); padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
                                        <h1 style="font-size: 32px; font-weight: bold; color: rgb(30, 64, 175); margin: 0px 0px 10px; text-transform: uppercase; letter-spacing: 2px;">RESIDENTIAL CARE PLAN</h1>
                                        <p style="font-size: 14px; color: rgb(107, 114, 128); margin: 0px;">CQC Compliant Documentation</p>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 30px; padding: 20px; background-color: rgb(248, 250, 252); border: 1px solid rgb(226, 232, 240); border-radius: 8px;">
                                        <div>
                                            <h2 style="font-size: 24px; font-weight: bold; color: rgb(30, 64, 175); margin-top: 0px; margin-bottom: 15px;">Client Name: Logan Jones</h2>
                                            <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                                                <tbody>
                                                    <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                        <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139); width: 180px;">Date of Birth:</td>
                                                        <td style="padding: 8px 0px;">29.10.2009</td>
                                                    </tr>
                                                    <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                        <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">NHS Number:</td>
                                                        <td style="padding: 8px 0px;">Not recorded</td>
                                                    </tr>
                                                    <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                        <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Room Number:</td>
                                                        <td style="padding: 8px 0px;">Not assigned</td>
                                                    </tr>
                                                    <tr style="border-bottom: 1px solid rgb(226, 232, 240);">
                                                        <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Care Plan Start Date:</td>
                                                        <td style="padding: 8px 0px;">19/12/2025</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 8px 0px; font-weight: 600; color: rgb(100, 116, 139);">Care Manager:</td>
                                                        <td style="padding: 8px 0px;">m.carter</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div style="border: 2px dashed rgb(203, 213, 225); border-radius: 8px; display: flex; align-items: center; justify-content: center; min-height: 200px; background-color: rgb(241, 245, 249); padding: 20px; text-align: center;">
                                            <div>
                                                <p style="font-size: 12px; color: rgb(100, 116, 139); margin: 0px;">CLIENT PHOTOGRAPH<br>(To be inserted with consent)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">1. Personal Details &amp; Contact Information</h3>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Preferred Name:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Logan Jones</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Gender:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Legal Status:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Informal</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">GP Practice:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Language:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">English</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Religion:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Not recorded</p>
                                            </div>
                                        </div>
                                        <div style="margin-top: 15px;">
                                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 10px; color: rgb(71, 85, 105);">Next of Kin / Emergency Contact</h4>
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                                <div style="font-size: 13px;">
                                                    <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Name:</p>
                                                    <p style="margin: 0px; color: rgb(31, 41, 55);">Carolanne Jones</p>
                                                </div>
                                                <div style="font-size: 13px;">
                                                    <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Relationship:</p>
                                                    <p style="margin: 0px; color: rgb(31, 41, 55);">Mum</p>
                                                </div>
                                                <div style="font-size: 13px;">
                                                    <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Contact Number:</p>
                                                    <p style="margin: 0px; color: rgb(31, 41, 55);"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">2. Capacity, Consent &amp; Legal Framework</h3>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Mental Capacity Assessment:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">To be assessed</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Capacity to Consent to Care:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">✗ No</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">LPA/Deputyship:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">None in place</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">DNACPR:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Not in place</p>
                                            </div>
                                        </div>
                                        <p style="font-size: 13px; margin-top: 10px; font-style: italic; color: rgb(100, 116, 139);">Client has been involved in the development of this care plan and has given informed consent.</p>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">6. Personal Care</h3>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Washing/Bathing:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Requires prompts only</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Dressing:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Independent with choices</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Continence:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Continent</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Skin Integrity:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Intact</p>
                                            </div>
                                        </div>
                                        <div style="margin-top: 15px; padding: 12px; background-color: rgb(239, 246, 255); border-left: 4px solid rgb(59, 130, 246); border-radius: 4px;">
                                            <p style="font-size: 13px; margin: 0px; color: rgb(30, 64, 175);"><strong>Care Approach:</strong> Respect privacy and dignity. Offer choice and promote independence.</p>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">11. Risk Assessments (Summary)</h3>
                                        <div style="margin-bottom: 10px;">
                                            <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Increased anxiety about dental visits</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 242, 242); color: rgb(220, 38, 38);">high risk</span></p>
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Medication nonadherence due to side effects or refusal</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Substance misuse (vaping) impacting health</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                        </div>
                                        <div style="margin-bottom: 10px;">
                                            <p style="font-size: 13px; margin: 0px 0px 4px;"><strong>Skin reactions due to new products or environmental factors</strong> –<span style="margin-left: 8px; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: rgb(254, 252, 232); color: rgb(202, 138, 4);">medium risk</span></p>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">12. Safeguarding</h3>
                                        <p style="font-size: 13px; margin: 0px; color: rgb(31, 41, 55);">No current safeguarding concerns identified.</p>
                                        <p style="font-size: 13px; margin-top: 10px; color: rgb(100, 116, 139);">Staff to follow safeguarding policy and whistleblowing procedures. All concerns must be reported immediately.</p>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">13. Emergency Information</h3>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Emergency Contact:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Carolanne Jones (Mum)</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Hospital Preference:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Local NHS Trust</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">DNACPR Status:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Not in place</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">14. Review &amp; Monitoring</h3>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-bottom: 12px;">
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Care Plan Review Date:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">19/03/2026</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Reviewed By:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">m.carter</p>
                                            </div>
                                            <div style="font-size: 13px;">
                                                <p style="margin: 0px 0px 4px; font-weight: 600; color: rgb(100, 116, 139);">Client Involvement:</p>
                                                <p style="margin: 0px; color: rgb(31, 41, 55);">Yes</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 25px; break-inside: avoid; border-left: 3px solid rgb(59, 130, 246); padding-left: 15px;">
                                        <h3 style="font-size: 16px; font-weight: 700; margin-top: 0px; margin-bottom: 12px; color: rgb(30, 41, 59);">15. Signatures</h3>
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px;">
                                            <thead>
                                                <tr style="background-color: rgb(241, 245, 249);">
                                                    <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Role</th>
                                                    <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Name</th>
                                                    <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Signature</th>
                                                    <th style="padding: 10px; text-align: left; border: 1px solid rgb(203, 213, 225); font-weight: 600;">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Client</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Logan Jones</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                </tr>
                                                <tr style="background-color: rgb(248, 250, 252);">
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Key Worker</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);"></td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">Manager</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">m.carter</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">__________</td>
                                                    <td style="padding: 15px; border: 1px solid rgb(203, 213, 225);">______</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div style="margin-top: 40px; padding: 20px; background-color: rgb(241, 245, 249); border-radius: 8px; text-align: center; break-inside: avoid;">
                                        <h4 style="font-size: 14px; font-weight: 600; margin-top: 0px; margin-bottom: 10px; color: rgb(30, 64, 175);">CQC Key Lines of Enquiry (KLOEs) Addressed</h4>
                                        <div style="display: flex; justify-content: center; gap: 15px; font-size: 13px; flex-wrap: wrap;">
                                            <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Safe</span>
                                            <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Effective</span>
                                            <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Caring</span>
                                            <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Responsive</span>
                                            <span style="padding: 6px 12px; background-color: rgb(30, 64, 175); color: white; border-radius: 4px; font-weight: 600;">✓ Well-led</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- CQCCompliantDocumentationPDF -->


                    </div>









                </div>
                <div class="content" id="clientRiskAssessmentsTab">
                    <div class="carePlanTabCont riskAssessmentSectionFirst" style="">
                        <div class="workHoursHeader">
                            <div class="title"> Risk Assessments</div>
                            <div class="actions">
                                <button class="addAssessmentBtn"> <i class='bx  bx-plus'></i>Add Assessment</button>
                            </div>
                        </div>

                        <div class="carePlanWrapper">
                            @forelse($risks ?? [] as $risk)
                            @php
                            $statusMap = [
                            '1' => ['label' => 'historic', 'cls' => 'roundTag yellow'],
                            '2' => ['label' => 'live', 'cls' => 'roundTag radShowbtn'],
                            '3' => ['label' => 'no risk', 'cls' => 'roundTag greenTag'],
                            ];
                            $riskStatus = $statusMap[(string) $risk->status] ?? ['label' => 'unknown', 'cls' => 'roundTag'];
                            $assessedDate = $risk->created_at ? date('M j, Y', strtotime($risk->created_at)) : '—';
                            $reviewDate = (isset($risk->review_date) && $risk->review_date) ? date('M j, Y', strtotime($risk->review_date)) : '—';
                            @endphp
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class="bx  bx-alert-triangle"></i> </span>
                                        {{ $risk->description }}
                                        <span class="{{ $riskStatus['cls'] }}">{{ $riskStatus['label'] }}</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="realRiskBodyMapBtn" data-su-risk-id="{{ $risk->id }}" title="Open Body Map"><i class="bx  bx-body"></i> </button>
                                        <button class="riskAssessmentDeatils"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong>Assessed: </strong> {{ $assessedDate }}</div>
                                    <div><strong>Review: </strong> {{ $reviewDate }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="planCard">
                                <div class="planFooter">
                                    <span>No risk assessments recorded for this client yet.</span>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="riskAssessmentSectionSecond" style="display:none">

                        <div class="topHeaderCont">
                            <div>
                                <button class="btn borderBtn backBtn" id="riskAssesmentBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back </button>
                            </div>
                        </div>

                        <div class="generalRiskAssessment">
                            <!-- Header -->
                            <div class="riskHeader">
                                <div class="titleWrap">
                                    <span class="warnIcon">⚠</span>
                                    <h2>General Risk Assessment</h2>
                                </div>
                                <span class="riskLevel">high risk</span>
                            </div>
                            <div class="riskMeta">
                                <div>
                                    <p><strong>Assessed:</strong> December 16th, 2025</p>
                                    <p><strong>Review Date:</strong> March 16th, 2026</p>
                                </div>
                                <div>
                                    <p><strong>By:</strong> AI Import</p>
                                    <p><strong>Status:</strong> active</p>
                                </div>
                            </div>
                            <div class="riskSection">
                                <h4>Risk Identified</h4>
                                <div class="infoBox">
                                    <p> Substance misuse: Concerns around purchasing and taking various substances, an incident regarding substance misuse in July.
                                        Vaping (e-cigarette use) with declining cessation support. ADHD medication is withheld due to substance concerns. </p>
                                </div>
                            </div>
                            <div class="riskSection">
                                <h4>Existing Controls</h4>
                                <div class="controlItem">
                                    <p>Ongoing YPDAAT support and keywork sessions</p>
                                    <span class="statusTag">effective</span>
                                </div>
                                <div class="controlItem">
                                    <p>Education on risks of substance misuse</p>
                                    <span class="statusTag">effective</span>
                                </div>
                                <div class="controlItem">
                                    <p>Support attending health appointments related to substance misuse</p>
                                    <span class="statusTag">effective</span>
                                </div>
                                <div class="controlItem">
                                    <p>Liaison with Alex Fanning from YPDAAT</p>
                                    <span class="statusTag">effective</span>
                                </div>
                                <div class="controlItem">
                                    <p>Withholding ADHD medication</p>
                                    <span class="statusTag">effective</span>
                                </div>
                                <div class="controlItem">
                                    <p>Not supporting time with certain friends (Liv, Sophie, Lilly, Maggie, Stevie, Mia, Ellie)</p>
                                    <span class="statusTag">effective</span>
                                </div>
                            </div>
                            <div class="riskSection">
                                <h4>Additional Controls Required</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientMedicationTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0 medicationSectionFirst" style="display:;">
                        <header class="panel-heading headingCapitilize medicationManagementHeader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain purpleiconclr"><i class='fa fa-medkit'></i> Medication Management </div>
                                <p>Track medication administration and MAR sheets</p>
                            </div>
                        </header>

                        <div class="p-20">
                            <div class="medicationManagement" id="availabilityTab">
                                <div class="availabilityTabs">
                                    <div class="availabilityTabs__nav">
                                        <button class="availabilityTabs__tab borderBtn active" data-target="MARSheetsPanel" id="marSheetBtn">MAR Sheets <span id="countMarSheet">(0)</span> </button>
                                        <button class="availabilityTabs__tab borderBtn" data-target="medicationLogsPanel" id="medicationLogsBtn">Medication Logs <span id="countMedicationLogs">(0)</span></button>
                                    </div>
                                    <div class="availabilityTabs__content">
                                        <div class="availabilityTabs__panel active" id="MARSheetsPanel">
                                            <div class="carePlanTabCont" style="">
                                                <div class="workHoursHeader">
                                                    <div class="title"> MAR Sheets</div>
                                                    <div class="actions">
                                                        <button class="btn borderBtn" id="viewMonthlyGridBtn" style="margin-right:5px;"><i class="fa fa-calendar"></i> Monthly MAR Grid</button>
                                                        <button class="purpleBgBtn" id="addPrescriptionBtn"><i class="fa fa-plus"></i> Add Prescription</button>
                                                    </div>
                                                </div>

                                                <div class="m-b-10" style="display:flex;gap:5px;">
                                                    <button class="btn btn-sm btn-default mar-status-filter active" data-status="all">All</button>
                                                    <button class="btn btn-sm btn-default mar-status-filter" data-status="active">Active</button>
                                                    <button class="btn btn-sm btn-default mar-status-filter" data-status="discontinued">Discontinued</button>
                                                </div>

                                                <div class="marPrescriptionFormWrapper" style="display:none;">
                                                    <div class="clientFilterform greanHeaderbgClr">
                                                        <div class="createNewAlert"><i class="fa fa-medkit"></i> Prescription Details</div>
                                                        <form id="marPrescriptionForm">
                                                            @csrf
                                                            <input type="hidden" id="mar_sheet_id" value="">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Medication Name *</label>
                                                                        <input type="text" class="form-control" id="mar_medication_name" placeholder="e.g., Metformin">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Dosage</label>
                                                                        <input type="text" class="form-control" id="mar_dosage" placeholder="e.g., 500mg">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Dose</label>
                                                                        <input type="text" class="form-control" id="mar_dose" placeholder="e.g., 2 tablets">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Route</label>
                                                                        <select class="form-control" id="mar_route">
                                                                            <option value="">Select route</option>
                                                                            <option value="Oral">Oral</option>
                                                                            <option value="Topical">Topical</option>
                                                                            <option value="Inhaled">Inhaled</option>
                                                                            <option value="Injection">Injection</option>
                                                                            <option value="Sublingual">Sublingual</option>
                                                                            <option value="Rectal">Rectal</option>
                                                                            <option value="Other">Other</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Frequency</label>
                                                                        <input type="text" class="form-control" id="mar_frequency" placeholder="e.g., Twice daily">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Time Slots</label>
                                                                        <div id="mar-time-slots-container">
                                                                            <div class="input-group m-b-5 time-slot-row">
                                                                                <input type="time" class="form-control mar-time-slot-input" name="time_slots[]" value="08:00">
                                                                                <span class="input-group-btn"><button type="button" class="btn btn-danger remove-time-slot"><i class="fa fa-times"></i></button></span>
                                                                            </div>
                                                                        </div>
                                                                        <button type="button" class="btn btn-xs btn-default" id="addTimeSlotBtn"><i class="fa fa-plus"></i> Add Time</button>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label><input type="checkbox" id="mar_as_required"> PRN (as required)</label>
                                                                    </div>
                                                                </div>
                                                                <div id="mar-prn-fields" style="display:none;">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label>PRN Details</label>
                                                                            <textarea class="form-control" id="mar_prn_details" rows="2" placeholder="When and why to give this PRN medication"></textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label>Reason for Medication</label>
                                                                        <textarea class="form-control" id="mar_reason_for_medication" rows="2" placeholder="Why this medication is prescribed"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Prescribed By</label>
                                                                        <input type="text" class="form-control" id="mar_prescribed_by" placeholder="e.g., Dr. Smith">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Prescriber / Specialist</label>
                                                                        <input type="text" class="form-control" id="mar_prescriber" placeholder="GP / specialist name">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Pharmacy</label>
                                                                        <input type="text" class="form-control" id="mar_pharmacy" placeholder="Dispensing pharmacy">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Start Date</label>
                                                                        <input type="date" class="form-control" id="mar_start_date">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>End Date</label>
                                                                        <input type="date" class="form-control" id="mar_end_date">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Stock Level</label>
                                                                        <input type="number" class="form-control" id="mar_stock_level" min="0" placeholder="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Reorder Level</label>
                                                                        <input type="number" class="form-control" id="mar_reorder_level" min="0" placeholder="0">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Storage Requirements</label>
                                                                        <input type="text" class="form-control" id="mar_storage_requirements" placeholder="e.g., Room temperature, away from light">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Allergies / Warnings</label>
                                                                        <input type="text" class="form-control" id="mar_allergies_warnings" placeholder="e.g., Penicillin allergy">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="header-actions">
                                                                        <button class="btn allbuttonDarkClr" type="button" id="saveMarPrescriptionBtn">Save Prescription</button>
                                                                        <button class="btn borderBtn" type="button" id="cancelMarPrescriptionBtn">Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <div class="carePlanWrapper m-t-15" id="mar-sheet-list"></div>
                                                <div id="mar-sheet-pagination"></div>
                                            </div>
                                        </div>

                                        <div class="availabilityTabs__panel" id="medicationLogsPanel">
                                            <div class="carePlanTabCont" style="">
                                                <div class="workHoursHeader">
                                                    <div class="title"> Medication Logs</div>
                                                    <div class="actions">
                                                        <button class="purpleBgBtn" id="logMedicationBtn"><i class='fa fa-plus'></i> Log Medication</button>
                                                    </div>
                                                </div>

                                                <div class="">
                                                    <div class="clientFilterform greanHeaderbgClr medicationLogsForm " style="display:none">

                                                        <div class="createNewAlert"><i class='fa fa-medkit'></i> Add Medication Administration Log </div>

                                                        <form id="medication_logsForm" class="addAlertForm">
                                                            @csrf
                                                            <input type="hidden" name="id" id="medication_log_id" value="">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Medication Name *</label>
                                                                        <input type="text" class="form-control checkMediLog" name="medication_name" id="medication_name" placeholder="e.g., Paracetamol">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Dosage *</label>
                                                                        <input type="text" class="form-control checkMediLog" name="dosage" id="dosage" placeholder="e.g., 500mg">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Frequency</label>
                                                                        <input type="text" class="form-control" name="frequesncy" id="frequesncy" placeholder="e.g., Twice daily">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Administration Time *</label>
                                                                        <input type="datetime-local" class="form-control checkMediLog" name="administrator_date" id="administrator_date" placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Status *</label>
                                                                        <select class="form-control checkMediLog" id="status" name="status">
                                                                            <option value="1" selected>Administered</option>
                                                                            <option value="2">Refused</option>
                                                                            <option value="3">Missed</option>
                                                                            <option value="4">Not Required</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Witnessed By (if required)</label>
                                                                        <input type="text" class="form-control" name="witnessed_by" id="witnessed_by" placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label> Notes</label>
                                                                        <textarea name="notes" id="medication_log_notes" class="form-control" rows="3" cols="20" placeholder="Any additional notes about administration"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label> Side Effects Observed</label>
                                                                        <textarea name="side_effect" id="side_effect" class="form-control" rows="3" cols="20" placeholder="Any observed side effects or reactions"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="header-actions">
                                                                        <button class="btn allbuttonDarkClr saveMedicationLogBtn" type="button">Save Log </button>
                                                                        <button class="btn borderBtn cancelMedicationLogBtn" type="button"> Cancel </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="carePlanWrapper m-t-15" id="renderHtmlMedicalLogs">

                                                    </div>
                                                    <div id="medicationLogsPagination"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0 medicationSectionSecond" style="display:none">
                        <div class="medicationManagement">
                            <header class="panel-heading headingCapitilize medicationManagementHeader">
                                <div class="clientHeadung" style="display: flex; align-items: center;">
                                    <button class="btn borderBtn backBtn" id="medicationBackBtn" style="display: inline-flex !important; align-items: center; margin: 0;"><i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back to MAR Sheets</button>
                                </div>
                            </header>

                            <div class="p-20">
                                <div id="mar-detail-header"></div>

                                <div style="margin-bottom:15px;">
                                    <label style="font-weight:600;margin-right:10px;">Date:</label>
                                    <input type="date" id="mar-grid-date" class="form-control" style="display:inline-block;width:200px;">
                                </div>

                                <div id="mar-grid-container"></div>

                                <div id="mar-detail-info" style="margin-top:20px;"></div>

                                <div style="margin-top:20px;">
                                    <h4 style="margin-bottom:10px;"><i class="fa fa-history"></i> Administration History</h4>
                                    <div id="mar-admin-history"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0 medicationSectionGrid" style="display:none">
                        <div class="medicationManagement">
                            <header class="panel-heading headingCapitilize medicationManagementHeader">
                                <div class="clientHeadung" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                    <button class="btn borderBtn backBtn" id="gridBackBtn" style="display: inline-flex !important; align-items: center; margin: 0;"><i class="fa fa-arrow-left" style="margin-right: 8px;"></i> Back to MAR Sheets</button>
                                    <div class="onlyheadingmain purpleiconclr" style="display: inline-flex !important; align-items: center; margin: 0;"><i class="fa fa-calendar" style="margin-right: 8px;"></i> Monthly MAR Grid</div>
                                </div>
                            </header>

                            <div class="p-20">
                                <div style="display:flex;align-items:center;gap:10px;margin-bottom:15px;flex-wrap:wrap;">
                                    <button class="btn btn-sm btn-default" id="mar-grid-prev-month"><i class="fa fa-chevron-left"></i></button>
                                    <select class="form-control" id="mar-grid-month" style="width:130px;display:inline-block;">
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <select class="form-control" id="mar-grid-year" style="width:90px;display:inline-block;">
                                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                    </select>
                                    <button class="btn btn-sm btn-default" id="mar-grid-next-month"><i class="fa fa-chevron-right"></i></button>
                                    <button class="btn btn-sm btn-default" id="mar-grid-print-btn" style="margin-left:auto;"><i class="fa fa-print"></i> Print MAR</button>
                                </div>

                                <div id="mar-monthly-grid-container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- MAR Administer Modal -->
                    <div class="modal fade" id="marAdministerModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                    <h4 class="modal-title"><i class="fa fa-medkit"></i> Record Administration</h4>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Medication:</strong> <span id="admin-modal-med-name"></span></p>
                                    <p><strong>Time Slot:</strong> <span id="admin-modal-slot"></span> | <strong>Date:</strong> <span id="admin-modal-date"></span></p>
                                    <hr>
                                    <input type="hidden" id="admin_mar_sheet_id">
                                    <input type="hidden" id="admin_time_slot">
                                    <input type="hidden" id="admin_date">
                                    <div class="form-group">
                                        <label>Status *</label>
                                        <select class="form-control" id="admin_code">
                                            <option value="A">A - Administered</option>
                                            <option value="S">S - Self-administered</option>
                                            <option value="R">R - Refused</option>
                                            <option value="W">W - Withheld</option>
                                            <option value="N">N - Not Available</option>
                                            <option value="O">O - Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Dose Given</label>
                                        <input type="text" class="form-control" id="admin_dose_given" placeholder="e.g., 500mg">
                                    </div>
                                    <div class="form-group">
                                        <label>Witnessed By</label>
                                        <input type="text" class="form-control" id="admin_witnessed_by" placeholder="Witness name">
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" id="admin_notes" rows="2" placeholder="Additional notes"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn borderBtn" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn allbuttonDarkClr" id="saveAdministrationBtn">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientPEEPTab">
                    <div class="carePlanTabCont peepSectionFirst" style="">
                        <div class="workHoursHeader">
                            <div class="title"> Personal Emergency Evacuation Plans</div>
                            <div class="actions">
                                <button class="addAssessmentBtn"> <i class='bx  bx-plus'></i>Add PEEP</button>
                            </div>
                        </div>

                        <div class="carePlanWrapper">
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class='bx  bx-bolt'></i> </span>
                                        Emergency Evacuation Plan
                                        <span class="roundTag greenShowbtn">Active</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="peepDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong> Method: </strong> with physical_assistance</div>
                                    <div><strong>Assessed: </strong> 1 staff</div>
                                    <div><strong>Review:</strong> Mar 16</div>
                                </div>
                            </div>
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class='bx  bx-bolt'></i> </span>
                                        Emergency Evacuation Plan
                                        <span class="roundTag greenShowbtn">Active</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="peepDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong> Method: </strong> with physical_assistance</div>
                                    <div><strong>Assessed: </strong> 1 staff</div>
                                    <div><strong>Review:</strong> Mar 16</div>
                                </div>
                            </div>
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class='bx  bx-bolt'></i> </span>
                                        Emergency Evacuation Plan
                                        <span class="roundTag greenShowbtn">Active</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="peepDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong> Method: </strong> with physical_assistance</div>
                                    <div><strong>Assessed: </strong> 1 staff</div>
                                    <div><strong>Review:</strong> Mar 16</div>
                                </div>
                            </div>
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class='bx  bx-bolt'></i> </span>
                                        Emergency Evacuation Plan
                                        <span class="roundTag greenShowbtn">Active</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="peepDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong> Method: </strong> with physical_assistance</div>
                                    <div><strong>Assessed: </strong> 1 staff</div>
                                    <div><strong>Review:</strong> Mar 16</div>
                                </div>
                            </div>
                            <div class="planCard borderleftOrange">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon iconorange"><i class='bx  bx-bolt'></i> </span>
                                        Emergency Evacuation Plan
                                        <span class="roundTag greenShowbtn">Active</span>
                                    </div>
                                    <div class="planActions">
                                        <button class="peepDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planMeta">
                                    <div><strong> Method: </strong> with physical_assistance</div>
                                    <div><strong>Assessed: </strong> 1 staff</div>
                                    <div><strong>Review:</strong> Mar 16</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="peepSectionSecond" style="display:none">

                        <div class="topHeaderCont">
                            <div>
                                <button class="btn borderBtn backBtn" id="peepBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back </button>
                            </div>
                        </div>

                        <div class="generalRiskAssessment">
                            <!-- Header -->
                            <div class="riskHeader">
                                <div class="titleWrap">
                                    <span class="warnIcon iconorange"><i class='bx  bx-bolt'></i></span>
                                    <h2>Personal Emergency Evacuation Plan</h2>
                                </div>
                                <span class="roundTag greenShowbtn">Active</span>
                            </div>

                            <div class="personalEmergencyDateTime">
                                <div class="assessedReview">
                                    <div class="assRevCont">
                                        <span><strong> Assessed:</strong> December 16th, 2025</span>
                                    </div>
                                    <div class="assRevCont">
                                        <span><strong> Review:</strong> March 16th, 2026</span>
                                    </div>
                                </div>
                                <div class="assessedReview">
                                    <div class="assRevCont">
                                        <span><strong> Mobility:</strong> fully_mobile</span>
                                    </div>
                                    <div class="assRevCont">
                                        <span><strong> Staff Required:</strong> 1</span>
                                    </div>
                                </div>
                            </div>
                            <div class="evacuationMethod">
                                <label class="redtext">Evacuation Method</label>
                                <div class="methodsec">
                                    <h4>with physical_assistance</h4>
                                </div>
                            </div>
                            <div class="evacuationMethod">
                                <label color="redtext">Equipment Required</label>
                                <div class="row careDetailsWrapper">
                                    <div class="col-md-6">
                                        <div class="objectiveCard mt-0">
                                            <div class="objectiveTop">
                                                <strong>Location</strong>
                                            </div>

                                            <p class="metaLine"> <strong>In Building:</strong> School attendance records, feedback from school. </p>
                                            <p class="metaLine"> <strong>Nearest Exit:</strong> School attendance records, feedback from school. </p>
                                            <p class="metaLine"> <strong>Alternative:</strong> School attendance records, feedback from school. </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="objectiveCard mt-0">
                                            <div class="objectiveTop">
                                                <strong>Assembly Point</strong>
                                            </div>
                                            <p class="metaLine"> <strong>In Building:</strong> School attendance records, feedback from school. </p>
                                            <p class="metaLine"> <strong>Nearest Exit:</strong> School attendance records, feedback from school. </p>
                                            <p class="metaLine"> <strong>Alternative:</strong> School attendance records, feedback from school. </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="content" id="clientRepositioningTab">

                    <div class="carePlanTabCont">
                        <div class="workHoursHeader">
                            <div class="title"> Repositioning Charts</div>
                            <div class="actions">
                                <button class="btn aiBtnThrd"> <i class="bx  bx-plus"></i>Add Chart</button>
                            </div>
                        </div>

                        <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                            <div class="leavebanktabCont">
                                <i class='bx  bx-square-root'></i>
                                <h4>No repositioning charts</h4>
                                <p>Create a chart to track repositioning</p>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="content" id="clientBehaviorTab">

                    <div class="carePlanTabCont behaviorChartSectionFirst" style="">
                        <div class="workHoursHeader">
                            <div class="title"> Behavior Charts</div>
                            <div class="actions">
                                <button class="btn purpleBlueBtn"> <i class='bx  bx-plus'></i> Add Chart</button>
                            </div>
                        </div>

                        <div class="carePlanWrapper">
                            <div class="planCard borderleftpurpleBlue">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon  purpleiconclr"><i class="bx bx-brain"></i> </span>
                                        Self-harm (historically, now few incidents), Struggling to get up for school, Refusing to attend education, Vaping in school, Abusive language towards teachers, Multiple school exclusions, Struggling to maintain personal hygiene, Struggling to take medication consistently, Excessive snacking on crisps and chocolate, Struggling to engage in physical activities, Selectivity with food, Making unkind remarks towards the team, Substance misuse, Refusing dental and optical appointments
                                        <span class="roundTag radShowbtn">high</span>
                                    </div>
                                    <div class="planActions d-flex">
                                        <button class="behaviorChartDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planFooter">
                                    <span>Dec 16, 2025</span>
                                </div>
                                <div class="planFooter">
                                    <span>Incidents: 0</span>
                                </div>
                            </div>
                            <div class="planCard borderleftpurpleBlue">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon  purpleiconclr"><i class="bx bx-brain"></i> </span>
                                        Self-harm (historically, now few incidents), Struggling to get up for school, Refusing to attend education, Vaping in school, Abusive language towards teachers, Multiple school exclusions, Struggling to maintain personal hygiene, Struggling to take medication consistently, Excessive snacking on crisps and chocolate, Struggling to engage in physical activities, Selectivity with food, Making unkind remarks towards the team, Substance misuse, Refusing dental and optical appointments
                                    </div>
                                    <div class="planActions d-flex">
                                        <button class="behaviorChartDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planFooter">
                                    <span>Dec 16, 2025</span>
                                </div>
                                <div class="planFooter">
                                    <span>Incidents: 0</span>
                                </div>
                            </div>
                            <div class="planCard borderleftpurpleBlue">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon  purpleiconclr"><i class="bx bx-brain"></i> </span>
                                        Self-harm (historically, now few incidents), Struggling to get up for school, Refusing to attend education, Vaping in school, Abusive language towards teachers, Multiple school exclusions, Struggling to maintain personal hygiene, Struggling to take medication consistently, Excessive snacking on crisps and chocolate, Struggling to engage in physical activities, Selectivity with food, Making unkind remarks towards the team, Substance misuse, Refusing dental and optical appointments
                                    </div>
                                    <div class="planActions d-flex">
                                        <button class="behaviorChartDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planFooter">
                                    <span>Dec 16, 2025</span>
                                </div>
                                <div class="planFooter">
                                    <span>Incidents: 0</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="behaviorChartSectionSecond" style="display:none">
                        <div class="topHeaderCont">
                            <div>
                                <button class="btn borderBtn backBtn" id="behaviorBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back </button>
                            </div>
                        </div>
                        <div class="generalRiskAssessment behaviorViewChart">
                            <!-- Header -->
                            <div class="riskHeader">
                                <div class="titleWrap">
                                    <!-- <span class="">⚠</span> -->
                                    <span class="statIcon warnIcon  purpleiconclr"><i class="bx bx-brain"></i> </span>
                                    <h2>Behavior Chart - December 16th, 2025</h2>
                                </div>
                                <!-- <span class="riskLevel">high risk</span> -->
                            </div>

                            <div class="riskSection">
                                <div class="controlItem">
                                    <div class="">
                                        <p>Self-harm (historically, now few incidents), Struggling to get up for school, Refusing to attend education, Vaping in school, Abusive language towards teachers, Multiple school exclusions, Struggling to maintain personal hygiene, Struggling to take medication consistently, Excessive snacking on crisps and chocolate, Struggling to engage in physical activities, Selectivity with food, Making unkind remarks towards the team, Substance misuse, Refusing dental and optical appointments</p>
                                    </div>
                                    <!-- <span class="statusTag">effective</span> -->
                                    <div class="reasonAndTarget">
                                        <p>Reason: Imported from client documentation</p>
                                        <p> Target: Building positive and trusting relationships, Opening up about past experiences, Discussing the importance of attending school and routines, Sharing tasks to help with morning routine, Offering incentives for appointments and pocket money, Team role-modelling healthy lifestyle, Tracking steps/progress with a watch, Involving Logan in weekly menu planning, Educating on the importance of a balanced diet, Offering healthier food choices and alternatives, Providing a walking pad, Discussing risks of not performing health checks (e.g., breast checks), Offering stop smoking services and continued discussions, Supporting the reduction of nicotine in vapes, Encouraging consistent morning routine for education, Providing breakfast options, Encouraging a tidy room (with incentives), Reminding Logan of school attendance incentives, Team liaising with school and transport driver, Obtaining school work if not attending, Limiting internet access for non-educational use, Encouraging social activities with friends, Celebrating events/parties at Omega, Regular keywork sessions, Providing a 1:1 support tutor in school, Celebrating positive achievements, Promoting an active lifestyle and good diet for endorphin release </p>
                                    </div>
                                </div>
                            </div>
                            <div class="riskSection bgYellow50">
                                <h4>Incidents (0)</h4>
                                <div class="controlItem">
                                    <div class="">
                                        <p>Daily Summary</p>
                                    </div>
                                    <div class="reasonAndTarget">
                                        <p>Engage in de-briefs, reflections, and chain analysis after incidents.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="content" id="clientEducationTab">
                    @include('frontEnd.roster.client.elements.education')
                </div>
                <div class="content" id="clientMentalCapacityTab">

                    <div class="carePlanTabCont mentalCapAsessmentSectonFirst" style="">
                        <div class="workHoursHeader">
                            <div class="title"> Mental Capacity Assessments</div>
                            <div class="actions">
                                <button class="btn pinkBtn"> <i class='bx  bx-plus'></i> Add Assessment</button>
                            </div>
                        </div>

                        <div class="carePlanWrapper">
                            <div class="planCard borderleftpinkClr">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon  pinkiconclr"><i class="bx bx-brain"></i> </span>
                                        School attendance and engagement, Engagement in care planning, Medication adherence, Family time arrangements, Substance misuse decisions, Health appointments (dental, optical, EKG, blood tests), Lifestyle choices (diet, exercise), Emotional wellbeing, Overnight stays at mum's home, Declining advocate services, Declining smoking cessation support
                                        <span class="roundTag radShowbtn" style="white-space: nowrap;">lacks capacity</span>
                                    </div>
                                    <div class="planActions d-flex">
                                        <button class="mentalCapAsessmentDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planFooter">
                                    <span>Dec 16, 2025</span>
                                </div>
                                <div class="planFooter">
                                    <span>Assessor: AI Import</span>
                                </div>
                            </div>
                        </div>
                        <div class="carePlanWrapper">
                            <div class="planCard borderleftpinkClr">
                                <div class="planTop">
                                    <div class="planTitle">
                                        <span class="statIcon heartIcon  pinkiconclr"><i class="bx bx-brain"></i> </span>
                                        School attendance and engagement, Engagement in care planning, Medication adherence, Family time arrangements, Substance misuse decisions, Health appointments (dental, optical, EKG, blood tests), Lifestyle choices (diet, exercise), Emotional wellbeing, Overnight stays at mum's home, Declining advocate services, Declining smoking cessation support
                                        <span class="roundTag radShowbtn" style="white-space: nowrap;">lacks capacity</span>
                                    </div>
                                    <div class="planActions d-flex">
                                        <button class="mentalCapAsessmentDetailsBtn"><i class="bx  bx-eye"></i> </button>
                                        <button class="danger"><i class="bx  bx-trash"></i> </button>
                                    </div>
                                </div>
                                <div class="planFooter">
                                    <span>Dec 16, 2025</span>
                                </div>
                                <div class="planFooter">
                                    <span>Assessor: AI Import</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mentalCapAsessmentSectonSecond" style="display:none">
                        <div class="topHeaderCont">
                            <div>
                                <button class="btn borderBtn backBtn" id="mentalCapAsessmentBackBtn"><i class='bx  bx-arrow-left-stroke'></i> Back </button>
                            </div>
                        </div>
                        <div class="generalRiskAssessment behaviorViewChart">
                            <!-- Header -->
                            <div class="riskHeader">
                                <div class="titleWrap">
                                    <span class="statIcon warnIcon  pinkiconclr"><i class="bx bx-brain"></i> </span>
                                    <h2>Mental Capacity Assessment</h2>
                                </div>
                                <span class="riskLevel radShowbtn">lacks capacity</span>
                            </div>

                            <div class="riskSection lightRedBg">
                                <div class="controlItem">
                                    <div class="">
                                        <p>Specific Decision</p>
                                    </div>
                                    <div class="reasonAndTarget">
                                        <p> School attendance and engagement, Engagement in care planning, Medication adherence, Family time arrangements, Substance misuse decisions, Health appointments (dental, optical, EKG, blood tests), Lifestyle choices (diet, exercise), Emotional wellbeing, Overnight stays at mum's home, Declining advocate services, Declining smoking cessation support </p>
                                    </div>
                                </div>
                            </div>
                            <div class="riskMeta">
                                <div>
                                    <p><strong>Assessed:</strong> December 16th, 2025</p>
                                </div>
                                <div>
                                    <p><strong>Assessed:</strong> All Import</p>
                                </div>
                            </div>
                            <div class="riskSection">
                                <div class="controlItem">
                                    <div class="">
                                        <p>Reasons for Conclusion</p>
                                    </div>
                                    <div class="reasonAndTarget">
                                        <p>Logan is 15 years old and under a Section 20 care order, meaning her Mum retains full parental responsibility. The care team actively supports Logan's participation in decision-making and encourages her to make informed choices, particularly regarding education. However, final decisions on certain matters are made in Logan's best interests, reflecting her age and legal status. Specific decisions are assessed individually for her capacity to understand and retain information, and any associated risks.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="content" id="clientDoLSTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize aIInsightsheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain purpleiconclr"><i class='bx  bx-shield'></i> Deprivation of Liberty Safeguards (DoLS) </div>
                                <p>Manage DoLS authorisations and reviews</p>
                            </div>
                            <div class="actions mt-0">
                                <button class="btn purpleBgBtn addDolsRecordBtn" data-formType="add"> <i class="bx  bx-plus"></i> New DoLS Record</button>
                            </div>
                        </header>
                        <div class="p-20">
                            <div class="carer-form dolsSectionFirst" style="display:none">
                                <form id="clientDolsForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>DoLS Status <span class="radStar">*</span></label>
                                            <select class="form-control" name="dols_status" id="dols_status">
                                                <option value="Not Applicable">Not Applicable</option>
                                                <option value="Screening Required">Screening Required</option>
                                                <option value="Application Submitted">Application Submitted</option>
                                                <option value="Standard Authorisation Granted">Standard Authorisation Granted</option>
                                                <option value="Urgent Authorisation Granted">Urgent Authorisation Granted</option>
                                                <option value="Not Authorised">Not Authorised</option>
                                                <option value="Expired">Expired</option>
                                                <option value="Under Review">Under Review</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Authorisation Type</label>
                                            <select class="form-control" name="authorisation_type" id="authorisation_type">
                                                <option value="Standard">Standard</option>
                                                <option value="Urgent">Urgent</option>
                                                <option value="Not Applicable">Not Applicable</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Referral Date </label>
                                            <input type="date" name="referral_date" id="referral_date" class="form-control">
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Authorisation Start Date</label>
                                            <input type="date" name="authorisation_start_date" id="authorisation_start_date" class="form-control">
                                        </div>

                                        <div class="col-md-6  m-t-10">
                                            <label>Authorisation End Date</label>
                                            <input type="date" name="authorisation_end_date" id="authorisation_end_date" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Review Date</label>
                                            <input type="date" name="review_date" id="review_date" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Supervisory Body</label>
                                            <input type="text" name="supervisory_body" id="supervisory_body" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Case Reference</label>
                                            <input type="text" name="case_reference" id="case_reference" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Best Interests Assessor</label>
                                            <input type="text" name="best_interests_assessor" id="best_interests_assessor" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Mental Health Assessor</label>
                                            <input type="text" name="mental_health_assessor" id="mental_health_assessor" class="form-control">
                                        </div>
                                        <div class="col-md-12  m-t-10">
                                            <label>Reason for DoLS</label>
                                            <textarea name="reason_for_dols" id="reason_for_dols" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div class="DoLSCheckList">
                                                <label><input type="checkbox" value="0" name="imca_appointed" id="imca_appointed" class="dolsCheckbox"> IMCA Appointed</label>
                                                <label><input type="checkbox" value="0" name="mental_capacity_assessment" id="mental_capacity_assessment" class="dolsCheckbox"> Mental Capacity Assessment Completed</label>
                                                <label><input type="checkbox" value="0" name="appeal_rights" id="appeal_rights" class="dolsCheckbox"> Appeal Rights Explained</label>
                                                <label><input type="checkbox" value="0" name="care_plan_updated" id="care_plan_updated" class="dolsCheckbox"> Care Plan Updated</label>
                                                <label><input type="checkbox" value="0" name="family_notified" id="family_notified" class="dolsCheckbox"> Family/Next of Kin Notified</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12  m-t-10">
                                            <label>Additional Notes</label>
                                            <textarea name="additional_notes" id="additional_notes" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                        </div>
                                        <div class="col-md-12  m-t-10">
                                            <div class="header-actions addnewicons">
                                                <input type="hidden" id="dols_id" name="dols_id">
                                                <button class="btn allbuttonDarkClr" id="saveClientDols" type="button"> Save DoLS Record</button>
                                                <button class="btn borderBtn" id="closeDolsformBtn" type="button"> Cancel</button>
                                            </div>
                                            <!-- <div class="actions mt-0">
                                                <button class="btn allbuttonDarkClr "> Save DoLS Record </button>
                                                <button class="btn borderBtn"> Cancel</button>
                                            </div> -->
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="carePlanWrapper dolsSectionSecond" id="dolsRenderList">

                            </div>
                            <div id="dolsPagination"></div>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientDNACPRTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize clntalertheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain radIconClr"><i class='bx  bx-heart'></i> DNACPR (Do Not Attempt CPR) </div>
                                <p>Manage resuscitation decisions and treatment ceilings</p>
                            </div>
                            <div class="actions mt-0">
                                <button class="btn addAssessmentBtn addDnaCprBtn" data-formType="add"> <i class="bx  bx-plus"></i> New DNACPR</button>
                            </div>
                        </header>
                        <div class="p-20">
                            <div class="carer-form DnaCprSectionFirst" style="display:none">
                                 <form id="dnaCprForm_id">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Status <span class="radStar">*</span></label>
                                                <select class="form-control dnaCprRequiredField" id="dnaCprStatus" name="status">
                                                    <option value="Active">Active</option>
                                                    <option value="Expired">Expired</option>
                                                    <option value="Withdrawn">Withdrawn</option>
                                                    <option value="Under Review">Under Review</option>
                                                    <option value="Not In Place">Not In Place</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Decision Date <span class="radStar">*</span></label>
                                                <input type="date" name="decision_date" id="dnaCprDecisionDate" class="form-control dnaCprRequiredField">
                                            </div>
                                            <div class="col-md-6 m-t-10">
                                                <label>Review Date</label>
                                                <input type="date" name="review_date" id="dnaCprReviewDate" class="form-control">
                                            </div>

                                            <div class="col-md-6  m-t-10">
                                                <label>Expiry Date</label>
                                                <input type="date" name="expiry_date" id="dnaCprExpiryDate" class="form-control">
                                            </div>
                                            <div class="col-md-6  m-t-10">
                                                <label>Decision Made By <span class="radStar">*</span></label>
                                                <input type="text" name="decision_made_by" id="dnaCprDecisionMadeBy" class="form-control dnaCprRequiredField">
                                            </div>
                                            <div class="col-md-6  m-t-10">
                                                <label>Decision Maker Role</label>
                                                <input type="text" name="decision_maker_role" id="dnaCprDecisionMakerRole" class="form-control">
                                            </div>
                                            <div class="col-md-6  m-t-10">
                                                <label>GMC Number</label>
                                                <input type="text" name="gmc_number" id="dnaCprGMCNumber" class="form-control">
                                            </div>
                                            <div class="col-md-6 m-t-10">
                                                <label>Mental Capacity</label>
                                                <select class="form-control" id="dnaCprMentalCapacity" name="mental_capacity">
                                                    <option selected disabled>Select</option>
                                                    <option value="Has Capacity">Has Capacity</option>
                                                    <option value="Lacks Capacity for This Decision">Lacks Capacity for This Decision</option>
                                                    <option value="Fluctuating Capacity">Fluctuating Capacity</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <label>Patient Involvement</label>
                                                <select class="form-control" id="dnaCprPatientInvolvement" name="patient_involvement">
                                                    <option selected disabled>Select</option>
                                                    <option value="Patient Has Capacity and Agrees">Patient Has Capacity and Agrees</option>
                                                    <option value="Patient Has Capacity and Disagrees">Patient Has Capacity and Disagrees</option>
                                                    <option value="Patient Lacks Capacity">Patient Lacks Capacity</option>
                                                    <option value="Patient Not Informed - Clinical Reasons">Patient Not Informed - Clinical Reasons</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <label>Clinical Reasons <span class="radStar">*</span></label>
                                                <textarea name="clinical_reasons" id="dnaCprClinicalReasons" class="form-control dnaCprRequiredField" rows="4" cols="20" placeholder="Clinical reasons for DNACPR decision..."></textarea>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <label>Anticipated Circumstances</label>
                                                <textarea name="anticipated_circumstances" id="dnaCprAnticipatedCircumstances" class="form-control" rows="3" cols="20" placeholder="Circumstances in which DNACPR applies..."></textarea>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <label>Other Emergency Treatments</label>
                                                <textarea name="other_emergency_treatments" id="dnaCprOtherEmergencyTreatments" class="form-control" rows="3" cols="20" placeholder="Other treatments that should/shouldn't be given..."></textarea>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <label>Patient Wishes</label>
                                                <textarea name="patient_wishes" id="dnaCprPatientWishes" class="form-control" rows="3" cols="20" placeholder=""></textarea>
                                            </div>
                                            <div class="col-md-6 m-t-10">
                                                <label>Form Location</label>
                                                <input type="text" name="form_location" id="dnaCprFormLocation" class="form-control" placeholder="Physical location of signed form">
                                            </div>
                                            <div class="col-md-12 mt-3">
                                                <div class="DoLSCheckList">
                                                    <label><input type="checkbox" value="0" name="discussion_held_check" id="dnaCprDiscussionHeld" class="dnaCprCheckbox"> Discussion Held with Patient</label>
                                                    <label><input type="checkbox" value="0" name="involved_check" id="dnaCprFamilyInvolved" class="dnaCprCheckbox"> Family/LPA/IMCA Involved</label>
                                                    <label><input type="checkbox" value="0" name="emergency_services_check" id="dnaCprEmergencyServicesAware" class="dnaCprCheckbox"> Emergency Services Made Aware</label>
                                                    <label><input type="checkbox" value="0" name="care_plan_updated_check" id="dnaCprCarePlanUpdated" class="dnaCprCheckbox"> Care Plan Updated</label>
                                                    <label><input type="checkbox" value="0" name="all_staff_briefed_check" id="dnaCprAllStaffBriefed" class="dnaCprCheckbox"> All Staff Briefed</label>
                                                    <label><input type="checkbox" value="0" name="gp_notified_check" id="dnaCprGPNotified" class="dnaCprCheckbox"> GP Notified</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 m-t-10">
                                                <label>Additional Notes</label>
                                                <textarea name="additional_notes" id="dnaCprAdditionalNotes" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                            </div>
                                            <div class="col-md-12 m-t-10">
                                                <div class="header-actions addnewicons">
                                                    <input type="hidden" id="dncprId" name="id">
                                                    <button class="btn addAssessmentBtn saveDnaCprBtn" type="button"> Save DNACPR</button>
                                                    <button class="btn borderBtn closeDnaCprBtn" type="button"> Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                            </div>

                            <div class="carePlanWrapper DnaCprSectionSecond" id="dnaCprRenderList">
                                
                            </div>
                            <div id="dnaCprPagination"></div>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientSafeguardingTab">
                    <div class="carePlanTabCont">
                        <div class="workHoursHeader">
                            <div class="title">Safeguarding Referrals</div>
                            <div class="actions">
                                <button class="btn addAssessmentBtn"> <i class="bx  bx-plus"></i> Add Referral</button>
                            </div>
                        </div>

                        <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                            <div class="leavebanktabCont">
                                <i class="bx  bx-shield"></i>
                                <h4>No safeguarding referrals</h4>
                                <p>No safeguarding concerns have been reported</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientConsentTab">
                        <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                            <header class="panel-heading headingCapitilize greanHeaderbgClr">
                                <div class="clientHeadung">
                                    <div class="onlyheadingmain"><i class='bx  bx-file greenText'></i> Consent
                                        Management </div>
                                    <p>Track client agreements and permissions</p>
                                </div>

                                <div class="actions mt-0">
                                    <button class="btn aiBtnThrd addConsentBtn" data-formType="add"> <i
                                            class='bx  bx-plus'></i> Add Consent</button>
                                </div>
                            </header>

                            <div class="p-20">
                                <div class="clientFilterform greanHeaderbgClr consentManagementSec consentRecordSectionFirst"
                                    style="display:none">

                                    <div class="createNewAlert"><i class='bx  bx-file'></i> Add New Consent Record
                                    </div>

                                    <form class="addAlertForm" id="consentForm_id">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Consent Type <span class="radStar">*</span></label>
                                                    <select class="form-control consentRequiredField" id="consent_type" name="consent_type">
                                                        <option value="Care Plan">Care Plan</option>
                                                        <option value="Medication">Medication</option>
                                                        <option value="Data Sharing">Data Sharing</option>
                                                        <option value="Photography">Photography</option>
                                                        <option value="Medical Treatment">Medical Treatment</option>
                                                        <option value="Emergency Contact">Emergency Contact</option>
                                                        <option value="Personal Care">Personal Care</option>
                                                        <option value="Financial">Financial</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Consent Title <span class="radStar">*</span></label>
                                                    <input type="text" class="form-control consentRequiredField" name="consent_title" id="consent_title" placeholder="e.g., Consent to Administer Medication">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label> Description <span class="radStar">*</span></label>
                                                    <textarea name="description" id="consentDescription" class="form-control consentRequiredField" rows="3" cols="20" placeholder="Detailed description of what is being consented to"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status <span class="radStar">*</span></label>
                                                    <select class="form-control consentRequiredField" name="status" id="consentStatus">
                                                        <option value="Pending">Pending</option>
                                                        <option value="Granted">Granted</option>
                                                        <option value="Refused">Refused</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date Granted</label>
                                                    <input type="date" class="form-control" id="consentDate_granted" name="date_granted" value="{{ now()->format('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Expiry Date (Optional)</label>
                                                    <input type="date" class="form-control" name="expiry_date" id="consentExpiry_date">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Granted By <span class="radStar">*</span></label>
                                                    <input type="text" class="form-control consentRequiredField" name="granted_by" id="consentGranted_by" placeholder="Name of person granting consent" value="{{ $clientDetails['name'] }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Relationship to Client</label>
                                                    <input type="text" class="form-control" name="relationship_client" id="consentRelationship_client" placeholder="e.g., self, daughter, guardian">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Witness Name (if applicable)</label>
                                                    <input type="text" class="form-control" name="witness_name" id="consentWitness_name" placeholder="Name of witness">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Witness Role</label>
                                                    <input type="text" class="form-control" name="witness_role" id="consentWitness_role" placeholder="e.g., Care Manager, Family Member">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Additional Notes</label>
                                                    <textarea name="additional_notes" id="consentAdditional_notes" class="form-control" rows="2" cols="20" placeholder="Any conditions or additional information"></textarea>
                                                </div>
                                            </div>


                                            <div class="col-md-12">
                                                <div class="header-actions">
                                                    <button class="btn allbuttonDarkClr saveConsentBtn" type="button"> Save Consent
                                                    </button>
                                                    <button class="btn borderBtn closeConsentRecordBtn" type="button">
                                                        Cancel </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="carePlanWrapper consentRecordSectionSecond" id="consentRenderList">

                                </div>
                                <div id="consentPagination"></div>
                            </div>

                            <!-- <div class="leavebanktabCont">
                                            <i class='bx  bx-alert-triangle'></i>
                                            <p>No alerts match the selected filters</p>
                                        </div> -->
                        </div>
                    </div>
                 <div class="content" id="clientEmergencyTab">
                    <style>
                        #clientEmergencyTab .emergencyMain {
                            border: 1px solid #e2e8f0 !important;
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
                            border-radius: 12px !important;
                            background: #ffffff !important;
                            overflow: hidden !important;
                        }

                        #clientEmergencyTab .emergencyHeader {
                            background: #fff5f3 !important; /* Light peach/coral background */
                            border-bottom: 1px solid #fee2e2 !important; /* Very light pinkish border */
                            padding: 24px !important;
                        }

                        #clientEmergencyTab .radIconClr i {
                            color: #c20a30 !important; /* Crimson red icon */
                        }

                        #clientEmergencyTab .emergencyContent h3 {
                            color: #1e293b !important;
                            font-weight: 700 !important;
                            font-size: 16px !important;
                            margin: 0 !important;
                        }

                        #clientEmergencyTab .emergencyContent p small {
                            color: #64748b !important;
                            font-size: 13px !important;
                        }

                        #clientEmergencyTab .editBtn {
                            background-color: #ffffff !important;
                            border: 1px solid #cbd5e1 !important;
                            color: #1e293b !important;
                            font-weight: 600 !important;
                            border-radius: 6px !important;
                            padding: 8px 16px !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
                            transition: all 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab .editBtn:hover {
                            background-color: #f8fafc !important;
                            border-color: #cbd5e1 !important;
                            color: #0f172a !important;
                        }

                        /* Edit Mode Containers */
                        #clientEmergencyTab #editableContactsContainer {
                            padding: 24px 24px 0 24px !important;
                        }

                        #clientEmergencyTab .formFooter {
                            padding: 0 24px 24px 24px !important;
                            border-top: none !important; /* Ensure no line separating the footer buttons */
                        }

                        #clientEmergencyTab .contact-row {
                            background-color: #f8fafc !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 12px !important;
                            padding: 24px !important;
                            margin-bottom: 20px !important;
                            position: relative !important;
                        }

                        #clientEmergencyTab .contact-row .contact-number-label {
                            font-size: 15px !important;
                            font-weight: 700 !important;
                            color: #1e293b !important;
                            margin: 0 !important;
                        }

                        #clientEmergencyTab .contact-row .remove-contact-row {
                            color: #dc2626 !important;
                            cursor: pointer !important;
                            transition: color 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab .contact-row .remove-contact-row:hover {
                            color: #991b1b !important;
                        }

                        #clientEmergencyTab .contact-row .form-label {
                            font-size: 13px !important;
                            font-weight: 600 !important;
                            color: #334155 !important;
                            margin-bottom: 6px !important;
                            display: block !important;
                        }

                        #clientEmergencyTab .contact-row .form-control {
                            height: 40px !important;
                            border-radius: 6px !important;
                            border: 1px solid #cbd5e1 !important;
                            box-shadow: none !important;
                            font-size: 14px !important;
                            background-color: #ffffff !important;
                            color: #0f172a !important;
                            transition: border-color 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab .contact-row .form-control:focus {
                            border-color: #c20a30 !important;
                            outline: none !important;
                        }

                        #clientEmergencyTab #addContactBtn {
                            width: 100% !important;
                            border: 1px dashed #cbd5e1 !important;
                            background-color: #ffffff !important;
                            color: #475569 !important;
                            padding: 12px !important;
                            font-weight: 600 !important;
                            border-radius: 6px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            gap: 8px !important;
                            transition: all 0.15s ease-in-out !important;
                            margin-bottom: 24px !important;
                        }

                        #clientEmergencyTab #addContactBtn:hover {
                            background-color: #f8fafc !important;
                            border-color: #94a3b8 !important;
                            color: #0f172a !important;
                        }

                        #clientEmergencyTab #saveContactsBtn {
                            background-color: #c20a30 !important;
                            color: #ffffff !important;
                            border: 1px solid #c20a30 !important;
                            padding: 10px 20px !important;
                            font-weight: 600 !important;
                            border-radius: 6px !important;
                            transition: all 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab #saveContactsBtn:hover {
                            background-color: #a80825 !important;
                            border-color: #a80825 !important;
                        }

                        #clientEmergencyTab #cancelContactsBtn {
                            background-color: #ffffff !important;
                            color: #475569 !important;
                            border: 1px solid #cbd5e1 !important;
                            padding: 10px 20px !important;
                            font-weight: 600 !important;
                            border-radius: 6px !important;
                            transition: all 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab #cancelContactsBtn:hover {
                            background-color: #f8fafc !important;
                            color: #0f172a !important;
                            border-color: #94a3b8 !important;
                        }

                        /* Styling read-only cards to match mockup 1 */
                        #clientEmergencyTab .legacy-contact-card,
                        #clientEmergencyTab .contact-card {
                            margin: 24px !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 12px !important;
                            background-color: #ffffff !important;
                            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
                            padding: 24px !important;
                        }

                        #clientEmergencyTab .icon__pp {
                            background: #ff7a59 !important; /* solid color avatar background */
                            border-radius: 50% !important;
                            width: 40px !important;
                            height: 40px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            color: #ffffff !important;
                            font-size: 16px !important;
                        }

                        #clientEmergencyTab .legacy-contact-card h2,
                        #clientEmergencyTab .contact-card h2 {
                            font-weight: 700 !important;
                            font-size: 16px !important;
                            color: #1e293b !important;
                            margin: 0 0 6px 0 !important;
                        }

                        #clientEmergencyTab .title {
                            display: inline-block !important;
                            background-color: #f8fafc !important;
                            border: 1px solid #cbd5e1 !important;
                            border-radius: 6px !important;
                            padding: 2px 8px !important;
                            font-size: 12px !important;
                            font-weight: 600 !important;
                            color: #475569 !important;
                            margin-top: 4px !important;
                            margin-bottom: 6px !important;
                        }

                        #clientEmergencyTab .contact-phone-row {
                            font-size: 14px !important;
                            color: #475569 !important;
                            font-weight: 500 !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                            margin-top: 8px !important;
                            margin-bottom: 0 !important;
                        }

                        #clientEmergencyTab .contact-phone-row i {
                            color: #64748b !important;
                            font-size: 14px !important;
                        }

                        #clientEmergencyTab .call-now-btn {
                            background-color: #22c55e !important;
                            border-color: #22c55e !important;
                            font-weight: 600 !important;
                            border-radius: 6px !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                            padding: 8px 16px !important;
                            color: #ffffff !important;
                            text-decoration: none !important;
                            transition: all 0.15s ease-in-out !important;
                        }

                        #clientEmergencyTab .call-now-btn:hover {
                            background-color: #16a34a !important;
                            border-color: #16a34a !important;
                        }

                        #clientEmergencyTab .emergency-priority-banner {
                            margin: 16px 0 0 0 !important;
                            font-size: 13px !important;
                            color: #991b1b !important;
                            background-color: #fdf1f1 !important;
                            border: 1px solid #fecaca !important;
                            border-radius: 6px !important;
                            padding: 10px 16px !important;
                            display: block !important;
                            width: 100% !important;
                            font-weight: 400 !important;
                        }

                        #clientEmergencyTab .emergency-priority-banner strong {
                            font-weight: 700 !important;
                        }
                    </style>
                    <div class="emergencyMain">
                        <div id="emergencyAller">
                            <div class="emergencyHeader">
                                <div class="emeregencyParent">
                                    <div class="emergencyContent">
                                        <div class="gap-3 d-flex align-items-center radIconClr">
                                            <i class="fas fa-phone-volume"></i>
                                            <h3>Emergency Contacts</h3>
                                        </div>
                                        <p class="mt-1"><small>Manage client emergency contact information</small></p>
                                    </div>
                                    <div class="emergencyBtn d-flex gap-2">
                                        <button class="borderBtn editBtn" id="editContactsBtn">
                                            <i class="fas fa-pencil-alt"></i>
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="emergencyListContainer">
                                @php
                                    $hasLegacy = !empty($clientDetails['em_name']) || !empty($clientDetails['em_phone']) || !empty($clientDetails['relationship']);
                                    $hasNew = !empty($clientDetails['emergency_contacts']);
                                    $hasAny = $hasLegacy || $hasNew;
                                    $priorityCounter = 1;
                                @endphp

                                @if(!$hasAny)
                                    <div class="p-5 text-center no-contacts-msg" style="padding: 40px; color: #64748b;">
                                        <i class="fas fa-phone-slash" style="font-size: 48px; margin-bottom: 15px; color: #cbd5e1;"></i>
                                        <p style="font-size: 16px; font-weight: 500;">No emergency contact information exists for this client.</p>
                                        <button class="btn btn-primary btn-sm mt-3 add-first-contact-btn" style="background-color: #c20a30; border-color: #c20a30;">
                                            <i class="fa fa-plus"></i> Add Emergency Contact
                                        </button>
                                    </div>
                                @else
                                    @if($hasLegacy)
                                        <div class="emergencybottom p-4 legacy-contact-card">
                                            <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                                <div class="userMum">
                                                    <div class="d-flex gap-3" style="display: flex; gap: 15px; align-items: center;">
                                                        <div class="icon__pp"><i class="fa-regular fa-user" aria-hidden="true"></i></div>
                                                        <div>
                                                            <h2>{{ $clientDetails['em_name'] ?? 'N/A' }}</h2>
                                                            <span class="title">{{ $clientDetails['relationship'] ?? 'N/A' }}</span>
                                                            @if(!empty($clientDetails['em_phone']))
                                                                <p class="contact-phone-row"><i class="fa fa-phone"></i> {{ $clientDetails['em_phone'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(!empty($clientDetails['em_phone']))
                                                    <div class="callBtn">
                                                        <a href="tel:{{ $clientDetails['em_phone'] }}" class="call-now-btn">
                                                            <i class="fa fa-phone"></i> Call Now
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="emergencyError">
                                                <p class="emergency-priority-banner">
                                                    <strong>Priority Contact {{ $priorityCounter++ }}</strong> - This contact will be reached in case of emergencies
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($hasNew)
                                        @foreach($clientDetails['emergency_contacts'] as $index => $contact)
                                            <div class="emergencybottom p-4 contact-card" id="contact-card-{{ $contact['id'] }}">
                                                <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                                    <div class="userMum">
                                                        <div class="d-flex gap-3" style="display: flex; gap: 15px; align-items: center;">
                                                            <div class="icon__pp"><i class="fa-regular fa-user" aria-hidden="true"></i></div>
                                                            <div>
                                                                <h2>{{ $contact['name'] ?? 'N/A' }}</h2>
                                                                <span class="title">{{ $contact['relationship'] ?? 'N/A' }}</span>
                                                                @if(!empty($contact['phone_no']))
                                                                    <p class="contact-phone-row"><i class="fa fa-phone"></i> {{ $contact['phone_no'] }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(!empty($contact['phone_no']))
                                                        <div class="callBtn">
                                                            <a href="tel:{{ $contact['phone_no'] }}" class="call-now-btn">
                                                                <i class="fa fa-phone"></i> Call Now
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="emergencyError">
                                                    <p class="emergency-priority-banner">
                                                        <strong>Priority Contact {{ $priorityCounter++ }}</strong> - This contact will be reached in case of emergencies
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Template row for dynamically cloning contacts during edit -->
                        <div id="contactRowTemplate" style="display: none;">
                            <div class="contactMain p-4 contact-row">
                                <div class="d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3 class="contact-number-label">Contact #</h3>
                                    <div class="deleteIcon remove-contact-row">
                                        <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <input type="hidden" class="contact-id" name="contacts[INDEX][id]" value="">
                                <div class="emergencyForm">
                                    <div class="row" style="margin-top: 15px;">
                                        <div class="col-md-4 col-sm-4 col-xs-12">
                                            <label class="form-label">Full Name</label>
                                            <input class="form-control contact-name" name="contacts[INDEX][name]" type="text" placeholder="Enter name" required>
                                        </div>

                                        <div class="col-md-4 col-sm-4 col-xs-12">
                                            <label class="form-label">Phone Number</label>
                                            <input class="form-control contact-phone" name="contacts[INDEX][phone_no]" type="text" placeholder="Enter phone">
                                        </div>

                                        <div class="col-md-4 col-sm-4 col-xs-12">
                                            <label class="form-label">Relationship</label>
                                            <input class="form-control contact-relationship" name="contacts[INDEX][relationship]" type="text" placeholder="e.g., Daughter, Son" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="contactWrapper" style="display: none;">
                            <form id="emergencyContactsForm">
                                @csrf
                                <input type="hidden" name="service_user_id" value="{{ $client_id }}">
                                
                                <div id="editableContactsContainer">
                                    <!-- Dynamic contact forms will be appended here -->
                                </div>

                                <div class="formFooter">
                                    <div class="contactBtn" style="margin-top: 10px;">
                                        <button type="button" id="addContactBtn" class="btn btn-default">
                                            <i class="fa fa-plus" style="margin-right: 8px;"></i>
                                            <span>Add Another Contact</span>
                                        </button>
                                    </div>
                                    <div style="margin-top: 20px;">
                                        <div class="d-flex gap-3" style="display: flex; gap: 12px;">
                                            <button type="submit" class="redBtn" id="saveContactsBtn">Save Contacts</button>
                                            <button type="button" class="borderBtn cancelBtn" id="cancelContactsBtn">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="content" id="clientDocumentsTab">
                    <div class="emergencyMain">
                        <div class="emergencyHeader">
                            <div class="emeregencyParent">
                                <div class="emergencyContent">
                                    <div class="gap-3 d-flex align-items-center iconConsent">
                                        <i class="far fa-file-alt" style="color:#2563eb"></i>
                                        <h3>Document Management</h3>
                                    </div>
                                    <p class="mt-1"><small>Store and manage client-related documents</small></p>
                                </div>
                                <div class="emergencyBtn d-flex gap-3">
                                    <div>
                                        <button class="borderBtn" onclick="openImportModal({{ $client_id }})">
                                            <i class="bx bx-import me-1"></i>
                                            <span>Import Documents</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="margin: 24px;">
                            <!-- Category Filters -->
                            <div class="d-flex align-items-center gap-2 mb-4 pb-3" style="border-bottom: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; gap: 8px;">
                                <span class="text-muted me-2" style="font-size: 13px; font-weight: 600; margin-right: 8px;">Filter by Category:</span>
                                <button class="btn btn-sm btn-primary filter-btn active" data-category="All" style="border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 500; border: none; margin-right: 4px;">All</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="Care Plan" style="border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 500; background: transparent; color: #475569; border: 1px solid #cbd5e1; margin-right: 4px;">Care Plan</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="Risk Assessment" style="border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 500; background: transparent; color: #475569; border: 1px solid #cbd5e1; margin-right: 4px;">Risk Assessment</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="Medical" style="border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 500; background: transparent; color: #475569; border: 1px solid #cbd5e1; margin-right: 4px;">Medical</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-category="Other" style="border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 500; background: transparent; color: #475569; border: 1px solid #cbd5e1; margin-right: 4px;">Other</button>
                            </div>

                            <div id="documentListContainer">
                                <div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Loading documents...</div>
                            </div>
                            <div id="importHistoryContainer" class="mt-4"></div>
                        </div>
                    </div>
                </div>
                <!-- progress tab section -->
                <div class="content" id="clientProgressReportTab">

                    <div class="topHeaderCont">
                        <div>
                            <h1 style="font-size:18px;">Progress Report </h1>
                            <p class="header-subtitle">Track improvements and areas needing attention</p>
                        </div>
                        <div class="d-flex gap-3">
                            <div>
                                <select name="" id="" class="form-control">
                                    <option value="">1 Month</option>
                                    <option value="">3 Month</option>
                                    <option value="">6 Month</option>
                                </select>
                            </div>

                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-arrow-to-bottom me-3'></i>
                                    <span> Export</span>
                                </button>
                            </div>
                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-sparkles me-3'></i>
                                    <span>AI Generate</span>
                                </button>
                            </div>
                            <div>
                                <button class="bgBtn" data-toggle="modal" data-target="#newRecord">
                                    <i class='bx  bx-plus me-3'></i>
                                    <span> New Record</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 mb-4">
                            <div class="emergencyMain emergencyContent  p24">
                                <h3>Overall Progress Over Time</h3>
                                <div id="chart-container" style="width: 100%; height: 300px; max-width:1200px">
                                    <svg id="chart1"></svg>
                                </div>
                                <div id="tooltip1" class="tooltip"></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="emergencyMain emergencyContent  p24">
                                <h3>All Areas Trend</h3>
                                <div id="chart-container2" style="width: 100%; height: 300px; overflow: hidden;">
                                    <svg id="chart2"></svg>
                                </div>
                                <div id="tooltip2" class="tooltip"></div>

                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="emergencyMain emergencyContent  p24">
                                <h3> Current Assessment Snapshot</h3>
                                <div style="height: 300px;">
                                    <div id="chartRadar"></div>
                                </div>


                            </div>
                        </div>
                    </div>

                    <!-- individual area progress -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="emergencyMain emergencyContent p24">
                                <h3>Detailed Breakdown - December 19, 2025</h3>

                                <div class="docIndMain mt-4 ">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="progress_history p-4" style="background-color: #fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brain w-4 h-4" style="color: rgb(139, 92, 246);">
                                                            <path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"></path>
                                                            <path d="M12 5a3 3 0 1 1 5.997.125 4 4 0 0 1 2.526 5.77 4 4 0 0 1-.556 6.588A4 4 0 1 1 12 18Z"></path>
                                                            <path d="M15 13a4.5 4.5 0 0 1-3-4 4.5 4.5 0 0 1-3 4"></path>
                                                            <path d="M17.599 6.5a3 3 0 0 0 .399-1.375"></path>
                                                            <path d="M6.003 5.125A3 3 0 0 0 6.401 6.5"></path>
                                                            <path d="M3.477 10.896a4 4 0 0 1 .585-.396"></path>
                                                            <path d="M19.938 10.5a4 4 0 0 1 .585.396"></path>
                                                            <path d="M6 18a4 4 0 0 1-1.967-.516"></path>
                                                            <path d="M19.967 17.484A4 4 0 0 1 18 18"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Behaviour</h5>

                                                </div>
                                                <div id="chart-container3" style="width: 100%; height: 160px; overflow: hidden;">
                                                    <svg id="chart3"></svg>
                                                </div>
                                                <div id="tooltip3" class="tooltip"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 ">
                                            <div class="progress_history p-4" style="background-color: #fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(59, 130, 246);">
                                                            <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path>
                                                            <path d="M22 10v6"></path>
                                                            <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Education/Schooling</h5>

                                                </div>
                                                <div id="chart-container4" style="width: 100%; height: 160px; overflow: hidden;">
                                                    <svg id="chart4"></svg>
                                                </div>
                                                <div id="tooltip4" class="tooltip"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="progress_history p-4" style="background-color:#fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(236, 72, 153);">
                                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Social & Emotional
                                                    </h5>
                                                </div>

                                                <div id="chart-container5" style="width:100%; height:160px;">
                                                    <svg id="chart5"></svg>
                                                </div>
                                                <div id="tooltip5" class="tooltip"></div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="progress_history p-4" style="background-color:#fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(16, 185, 129);">
                                                            <path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Health & Wellbeing</h5>
                                                </div>

                                                <div id="chart-container6" style="width:100%; height:160px;">
                                                    <svg id="chart6"></svg>
                                                </div>
                                                <div id="tooltip6" class="tooltip"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="progress_history p-4" style="background-color:#fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(245, 158, 11);">
                                                            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                                            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Independence Skills
                                                    </h5>
                                                </div>

                                                <div id="chart-container7" style="width:100%; height:160px;">
                                                    <svg id="chart7"></svg>
                                                </div>
                                                <div id="tooltip7" class="tooltip"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="progress_history p-4" style="background-color:#fff;">
                                                <div class="d-flex gap-3 align-items-center">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(99, 102, 241);">
                                                            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                                        </svg>
                                                    </div>
                                                    <h5 class="m-0">Activities & Engagement
                                                    </h5>
                                                </div>

                                                <div id="chart-container8" style="width:100%; height:160px;">
                                                    <svg id="chart8"></svg>
                                                </div>
                                                <div id="tooltip8" class="tooltip"></div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- detail breakdown start -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="emergencyMain emergencyContent p24 mt-4">
                                <h3>Detailed Breakdown - December 19, 2025</h3>
                                <div class="mt-4">
                                    <div class="rowDoc_card ">
                                        <div>
                                            <div class="emergencyMain p-4">
                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: #EDE5FF; padding: 5px; border-radius: 6px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-brain w-4 h-4" style="color: rgb(139, 92, 246);">
                                                                        <path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"></path>
                                                                        <path d="M12 5a3 3 0 1 1 5.997.125 4 4 0 0 1 2.526 5.77 4 4 0 0 1-.556 6.588A4 4 0 1 1 12 18Z"></path>
                                                                        <path d="M15 13a4.5 4.5 0 0 1-3-4 4.5 4.5 0 0 1-3 4"></path>
                                                                        <path d="M17.599 6.5a3 3 0 0 0 .399-1.375"></path>
                                                                        <path d="M6.003 5.125A3 3 0 0 0 6.401 6.5"></path>
                                                                        <path d="M3.477 10.896a4 4 0 0 1 .585-.396"></path>
                                                                        <path d="M19.938 10.5a4 4 0 0 1 .585.396"></path>
                                                                        <path d="M6 18a4 4 0 0 1-1.967-.516"></path>
                                                                        <path d="M19.967 17.484A4 4 0 0 1 18 18"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5>Behaviour</h5>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>

                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="emergencyMain p-4">
                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: #e0e3fdff; padding: 5px; border-radius: 6px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(59, 130, 246);">
                                                                        <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"></path>
                                                                        <path d="M22 10v6"></path>
                                                                        <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5>Education/Schooling
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>

                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="emergencyMain p-4">
                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: #FEE2E9; padding: 5px; border-radius: 6px; text-align: center; line-height: 18px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(236, 72, 153);">
                                                                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <h5>Social & Emotional
                                                            </h5>
                                                        </div>

                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>

                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="emergencyMain p-4">

                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: rgba(16, 185, 129, 0.125); padding: 5px; border-radius: 6px; text-align: center; line-height: 18px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(16, 185, 129);">
                                                                        <path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5>Health & Wellbeing
                                                                </h5>
                                                            </div>

                                                        </div>
                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>

                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="emergencyMain p-4">
                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: rgba(245, 158, 11, 0.125); padding: 5px; border-radius: 6px; text-align: center; line-height: 18px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(245, 158, 11);">
                                                                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"></path>
                                                                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5>Independence Skills
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>
                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="emergencyMain p-4">
                                                <div class="detail_chart_doc">
                                                    <div class=" d-flex justify-content-between align-items-center mb-4">
                                                        <div class="d-flex gap-3 align-items-center">
                                                            <div>
                                                                <div style="display: inline-block; background-color: rgba(99, 102, 241, 0.125); padding: 5px; border-radius: 6px; text-align: center; line-height: 18px;">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: rgb(99, 102, 241);">
                                                                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5>Activities & Engagement</h5>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <i style="color:#9ca3af" class='bx  bx-minus'></i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center gap-4  mb-3">
                                                        <div class="progressBar">
                                                            <div class="progressFill" style="width:30%;background:#2563eb"></div>
                                                        </div>

                                                        <div>
                                                            <span class="careBadg">3/10</span>
                                                        </div>
                                                    </div>
                                                    <p class="para" style="font-size:12px">Engagement in de-briefs and reflections after incidents is noted, signaling some awareness of behaviours.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-lg-6">
                                        <div class="purpleBox allertbox" style="background-color: #fffbeb;">
                                            <div class="d-flex gap-3">
                                                <i style="color: 92400e;" class='bx  bx-alert-triangle'></i>
                                                <div>
                                                    <p class="mb-2"> <strong style="font-size: 15px;">Concerns</strong></p>
                                                    <ul>
                                                        <li class="text-sm">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability.</li>
                                                        <li class="text-sm"> There is a lack of consistent engagement in healthcare appointments, both dental and optical.</li>
                                                        <li class="text-sm">Substance misuse and the refusal to attend school are critical areas of concern.</li>

                                                    </ul>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress History start -->
                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="emergencyMain emergencyContent  p24">
                                <h3>Progress History </h3>
                                <div class="progress_history detail_chart_doc mt-4">
                                    <div class="d-flex gap-3 align-items-center">
                                        <div class="d-flex gap-3 align-items-center">
                                            <div>
                                                <button style="border: unset;" data-toggle="modal" data-target="#newRecordEdit" class="border:none">
                                                    <i style="color:#9ca3af" class='bx  bx-calendar-event'></i>

                                                </button>
                                            </div>
                                            <div>
                                                <h5>
                                                    December 19, 2025
                                                </h5>
                                                <p class="para text-sm mt-3">
                                                    weekly review • By m.carter@omegalife.uk
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="careBadg">3/10</span>
                                        </div>
                                        <div>
                                            <span class="careBadg">Logan is currently facing significant challenges across several areas, with minimal progress on active care plan goals. Immediate interventions and support are necessary to address these issues.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expenses Tab -->
                <div class="content" id="clientExpensesTab">
                    <div class="topHeaderCont">
                        <div>
                            <h1 style="font-size:18px;">Client Expenses</h1>
                            <p class="header-subtitle">Manage and track expenses for {{$clientDetails['name']}}</p>
                        </div>
                        <div class="header-actions">
                            <button class="btn bgBtn" data-toggle="modal" data-target="#addExpenseModal">
                                <i class='bx bx-plus me-2'></i> Add Expense
                            </button>
                        </div>
                    </div>
                    <div class="sectionWhiteBgAllUse p24">
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Amount</th>
                                        <th>Notes</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="clientExpensesListHtml">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TAB CONTENT -->
        </div>

    </div>

    <!-- Add Expense Modal -->
    <div class="modal fade leaveCommunStyle" id="addExpenseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="bx bx-plus"></i> Add Expense</h4>
                </div>
                <form id="clientExpenseForm">
                    @csrf
                    <input type="hidden" name="id" id="expense_id">
                    <input type="hidden" name="service_user_id" value="{{$clientDetails['id']}}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="expense_date" id="expense_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="expense_title" class="form-control" placeholder="Expense Title" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="amount" id="expense_amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" id="expense_notes" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn borderBtn" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn allbuttonDarkClr">Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- add Carer Modal -->
    <div class="modal fade leaveCommunStyle" id="addcreateCarePlanModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="clientCarePlanForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"> <i class="bx  bx-heart"></i> Create Care Plan - {{$clientDetails['name']}}</h4>
                    </div>
                    <div class="modal-body heightScrollModal">
                        <div class="carer-form">

                            <div class="availabilityTabs createCarePlanTabs">
                                <!-- TAB HEADER -->
                                <div class="availabilityTabs__nav">
                                    <button type="button" class="availabilityTabs__tab active" data-target="carePlanOverview"><i class="bx  bx-file-report"></i> Overview</button>
                                    <button type="button" class="availabilityTabs__tab" data-target="carePlanObjectives"><i class='bx  bx-radio-circle-marked'></i> Objectives</button>
                                    <button type="button" class="availabilityTabs__tab" data-target="carePlanCareTasks"><i class='bx  bx-checklist'></i> Care Tasks</button>
                                    <button type="button" class="availabilityTabs__tab" data-target="carePlanMedication"><i class='bx  bx-pill'></i> Medication</button>
                                    <button type="button" class="availabilityTabs__tab" data-target="carePlanPreferences"><i class='bx  bx-user'></i> Preferences</button>
                                    <button type="button" class="availabilityTabs__tab" data-target="carePlanRisk"><i class='bx  bx-alert-triangle'></i> Risk</button>
                                </div>

                                <!-- TAB CONTENT -->

                                <div class="availabilityTabs__content">

                                    <div class="availabilityTabs__panel active" id="carePlanOverview">
                                        <div class="">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Care Setting <span class="radStar">*</span></label>
                                                    <select class="form-control checkClientCarePlan" name="care_setting" id="care_setting">
                                                        <option value="Domiciliary Care">Domiciliary Care</option>
                                                        <option value="Residential Care">Residential Care</option>
                                                        <option value="Supported Living">Supported Living</option>
                                                        <option value="Day Centre">Day Centre</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Plan Type </label>
                                                    <select class="form-control" name="plan_type" id="plan_type">
                                                        <option value="Initial Assessment">Initial Assessment</option>
                                                        <option value="Review">Review</option>
                                                        <option value="Interim">Interim</option>
                                                        <option value="Discharge">Discharge</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4  m-t-10">
                                                    <label>Assessment Date <span class="radStar">*</span></label>
                                                    <input type="date" name="assessment_date" id="assessment_date" class="form-control checkClientCarePlan">
                                                </div>
                                                <div class="col-md-4  m-t-10">
                                                    <label>Review Date</label>
                                                    <input type="date" name="review_date" id="carePlanreview_date" class="form-control">
                                                </div>

                                                <div class="col-md-4  m-t-10">
                                                    <label>Assessed By <span class="radStar">*</span></label>
                                                    <input type="text" name="assessed_by" id="assessed_by" class="form-control checkClientCarePlan" placeholder="Staff member name">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Status</label>
                                                    <select class="form-control" name="status" id="carePlanStatus">
                                                        <option value="0">Draft</option>
                                                        <option value="1">Active</option>
                                                        <option value="2">Under Review</option>
                                                        <option value="3">Archived</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="address">
                                                <label>Personal Details</label>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Preferred Name</label>
                                                    <input type="text" name="preferred_name" id="preferred_name" class="form-control" value="{{$clientDetails['name']}}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Language </label>
                                                    <input type="text" name="language" id="language" class="form-control" value="English">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Religion</label>
                                                    <input type="text" name="religion" id="religion" class="form-control" placeholder="Staff member name">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Cultural Needs </label>
                                                    <input type="text" name="cultural_needs" id="cultural_needs" class="form-control" placeholder="Staff member name">
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="address">
                                                <label>Daily Routine</label>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Morning</label>
                                                    <textarea name="morning" id="morning" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Afternoon </label>
                                                    <textarea name="afternoon" id="afternoon" class="form-control" rows="3" cols="20" placeholder="Describe afternoon routine..."></textarea>
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Evening</label>
                                                    <textarea name="evening" id="evening" class="form-control" rows="3" cols="20" placeholder="Describe evening routine..."></textarea>
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Night </label>
                                                    <textarea name="night" id="night" class="form-control" rows="3" cols="20" placeholder="Describe night routine..."></textarea>
                                                </div>
                                                <input type="hidden" id="overview_id" name="id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="availabilityTabs__panel" id="carePlanObjectives">
                                        <div class="">
                                            <div class="workHoursHeader">
                                                <div class="title"> Care Objectives</div>
                                                <div class="actions mt-0">
                                                    <button type="button" class="borderBtn addMoreObjective"> <i class="bx bx-plus"></i> Add Objective</button>
                                                </div>
                                            </div>
                                            <div id="renderLeaveCard">
                                                <div class="no-data-card">
                                                    <div class="noData" style="text-align:center">
                                                        <div>
                                                            <i class="bx bx-bullseye"></i>
                                                            <p>No Objective defined yet</p>
                                                            <button type="button" class="borderBtn addMoreObjective" style="display:unset !important">
                                                                Add First Objective
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="availabilityTabs__panel" id="carePlanCareTasks">
                                        <div class="">
                                            <div class="workHoursHeader">
                                                <div class="title"> Care Tasks & Interventions</div>
                                                <div class="actions mt-0">
                                                    <button class="borderBtn addMoreTask" type="button"> <i class="bx  bx-plus"></i> Add Task</button>
                                                </div>
                                            </div>

                                            <div id="renderClientCarePlanTask">
                                                <div class="no-data-card-task">
                                                    <div class="noData" style="text-align:center">
                                                        <div>
                                                            <i class="bx bx-checklist"></i>
                                                            <p>No tasks defined yet</p>
                                                            <button type="button" class="borderBtn addMoreTask" style="display:unset !important">
                                                                Add First Task
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>



                                        </div>
                                    </div>
                                    <div class="availabilityTabs__panel" id="carePlanMedication">
                                        <div class="">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="requiresLable">
                                                        <input type="checkbox" class="self_administers" id="self_administers" name="self_administers" value="0">
                                                        <label for="self_administers">Client self-administers medication</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Administration Support Level</label>
                                                    <select class="form-control" name="administration_support_level" id="administration_support_level">
                                                        <option value="None Required">None Required</option>
                                                        <option value="Prompting Only">Prompting Only</option>
                                                        <option value="Assistance Required">Assistance Required</option>
                                                        <option value="Full Administration">Full Administration</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Pharmacy Details</label>
                                                    <input type="text" class="form-control" placeholder="Pharmacy name & contact" id="pharmacy_details" name="pharmacy_details">
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>GP Details</label>
                                                    <input type="text" class="form-control" placeholder="GP surgery & contact" id="gp_details" name="gp_details">
                                                </div>

                                                <div class="col-md-12 m-t-10">
                                                    <label>Allergies & Sensitivities</label>
                                                    <textarea name="allergies" id="allergies" class="form-control sensitivitiesTextarea" rows="3" cols="20" placeholder="List any known allergies or sensitivities..."></textarea>
                                                </div>
                                                <input type="hidden" id="pharmacy_id" name="pharmacy_id">
                                            </div>



                                            <div class="workHoursHeader m-t-15">
                                                <div class="title"> Medications</div>
                                                <div class="actions mt-0">
                                                    <button class="borderBtn addMoreMedication" type="button"> <i class="bx  bx-plus"></i> Add Medication</button>
                                                </div>
                                            </div>
                                            <div id="renderClientCarePlanMedical">
                                                <div class="no-data-card-medication">
                                                    <div class="noData" style="text-align:center">
                                                        <div>
                                                            <i class="bx bx-pill"></i>
                                                            <p>No medications recorded</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <div class="availabilityTabs__panel" id="carePlanPreferences">
                                        <div class="">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Likes</label>
                                                    <textarea name="preferences[likes]" id="likes" class="form-control" rows="3" cols="20" placeholder="Enter likes (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Dislikes</label>
                                                    <textarea name="preferences[dislikes]" id="dislikes" class="form-control" rows="3" cols="20" placeholder="Enter dislikes (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Hobbies & Interests</label>
                                                    <textarea name="preferences[hobbies_interests]" id="hobbies_interests" class="form-control" rows="3" cols="20" placeholder="Enter hobbies (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Food Preferences</label>
                                                    <textarea name="preferences[food_preferences]" id="food_preferences" class="form-control" rows="3" cols="20" placeholder="Dietary requirements, favourite foods, etc."></textarea>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Personal Care Preferences</label>
                                                    <textarea name="preferences[personal_care_preferences]" id="personal_care_preferences" class="form-control" rows="3" cols="20" placeholder="How they like to be supported with personal care..."></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Communication Preferences</label>
                                                    <textarea name="preferences[communication_preferences]" id="communication_preferences" class="form-control" rows="2" cols="20" placeholder="How they prefer to communicate, any aids needed..."></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Social Preferences</label>
                                                    <textarea name="preferences[social_preferences]" id="social_preferences" class="form-control" rows="2" cols="20" placeholder="Social activities, visitors, alone time preferences..."></textarea>
                                                    <input type="hidden" name="preferences[preferences_id]" id="preferences_id">
                                                </div>
                                            </div>
                                            <!-- <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div> -->

                                        </div>
                                    </div>
                                    <div class="availabilityTabs__panel" id="carePlanRisk">
                                        <div class="">
                                            <div class="workHoursHeader">
                                                <div class="title"> Risk Factors</div>
                                                <div class="actions mt-0">
                                                    <button type="button" class="borderBtn addMoreClientPlanRisk"> <i class="bx  bx-plus"></i> Add Risk</button>
                                                </div>
                                            </div>

                                            <div id="renderClientCarePlanRisk">
                                                <div class="no-data-card-risk">
                                                    <div class="noData" style="text-align:center">
                                                        <div>
                                                            <i class="bx  bx-alert-triangle"></i>
                                                            <p>No risk factors identified</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="workHoursHeader">
                                                        <div class="title"> Emergency Information</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 ">
                                                    <label>Hospital Preference</label>
                                                    <input type="text" class="form-control" placeholder="Preferred hospital" name="emergency_information" id="emergency_information">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="requiresLable">
                                                        <input type="checkbox" id="dnacpr" class="dnacprCheckbox" name="dnacpr" value="0">
                                                        <label for="dnacpr">DNACPR in place</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Emergency Protocol</label>
                                                    <textarea name="emergency_protocol" id="emergency_protocol" class="form-control" rows="3" cols="20" placeholder="What to do in an emergency..."></textarea>
                                                    <input type="hidden" name="emergency_id" id="emergency_id">
                                                </div>
                                            </div>




                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="actions dFlexGap justify-content-end">
                            <button type="button" class="cancel borderBtn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <button type="button" class="submit bgBtn" id="saveClientCarePlanBtn">Create Care Plan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- pratima modal start-->
    <!-- for add record -->
    <div class="modal fade leaveCommunStyle" id="newRecord" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg pModalScroll">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"> New Progress Record
                    </h4>
                </div>
                <div class="modal-body heightScrollModal" style="height: unset;">
                    <div class="carer-form">

                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <label>Record Date</label>
                                <input type="date" class="form-control">
                            </div>

                            <div class="col-md-6">

                                <div class="box">
                                    <label>Record Type</label>
                                    <div class="trendClass-select small" tabindex="0">
                                        <span class="current">Select</span>

                                        <ul class="trendClass-list">
                                            <li class="trendClass-option selected" data-value="Nothing"> Weekly</li>
                                            <li class="trendClass-option" data-value="1"> Monthly</li>
                                            <li class="trendClass-option" data-value="2"> Quarterly</li>
                                            <li class="trendClass-option" data-value="3"> Ad Hoc</li>

                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="availabilityTabs createCarePlanTabs pActiveBehave">
                            <!-- TAB HEADER -->
                            <div class="availabilityTabs__nav">
                                <button
                                    id="tab-behaviour"
                                    class="availabilityTabs__tab active"
                                    role="tab"
                                    aria-selected="true"
                                    aria-controls="panel-behaviour"
                                    data-target="panel-behaviour">
                                    <i class="bxr bx-brain"></i> Behaviour
                                </button>

                                <button
                                    id="tab-education"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-education"
                                    data-target="panel-education">
                                    <i class="bxr bx-education"></i> Education/Schooling
                                </button>

                                <button
                                    id="tab-social"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-social"
                                    data-target="panel-social">
                                    <i class="bxr bx-heart"></i> Social
                                </button>

                                <button
                                    id="tab-health"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-health"
                                    data-target="panel-health">
                                    <i class="bxr bx-pulse"></i> Health
                                </button>

                                <button
                                    id="tab-independence"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-independence"
                                    data-target="panel-independence">
                                    <i class="bxr bx-home-alt"></i> Independence
                                </button>

                                <button
                                    id="tab-activities"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-activities"
                                    data-target="panel-activities">
                                    <i class="bxr bx-star"></i> Activities
                                </button>
                            </div>

                            <!-- TAB CONTENT -->
                            <div class="availabilityTabs__content">

                                <div id="panel-behaviour" class="availabilityTabs__panel active" role="tabpanel">
                                    <form action="">
                                        <div class="">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about behaviour..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer  recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                        </div>
                                    </form>

                                </div>

                                <div id="panel-education" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Attendance %</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Academic Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Above Expected</li>
                                                                <li class="trendClass-option" data-value="1">At Expected</li>
                                                                <li class="trendClass-option" data-value="2"> Below Expected</li>
                                                                <li class="trendClass-option" data-value="2">Significantly Below </li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about education/schooling..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer  recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div id="panel-social" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Peer Relationships</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Excellent</li>
                                                                <li class="trendClass-option" data-value="1">Good</li>
                                                                <li class="trendClass-option" data-value="2"> Fair</li>
                                                                <li class="trendClass-option" data-value="2">Poor</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Emotional Regulation</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Excellent</li>
                                                                <li class="trendClass-option" data-value="1">Good</li>
                                                                <li class="trendClass-option" data-value="2"> Developing</li>
                                                                <li class="trendClass-option" data-value="2">Need Support</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about social & emotional..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div id="panel-health" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about health & wellbeing..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div id="panel-independence" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about independence skills..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div id="panel-activities" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">

                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about activities & engagement..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>

                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
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
                <div class="modal-footer d-flex gap-3 justify-content-end">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="borderBtn">Cancel</button>
                    <button type="submit" class="bgBtn darkBg submit ">Create Care Plan</button>
                </div>
            </div>
        </div>
    </div>
    <!-- for edit record  -->
    <div class="modal fade leaveCommunStyle" id="newRecordEdit" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg pModalScroll">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"> Edit Progress Record </h4>
                </div>
                <div class="modal-body heightScrollModal" style="height: unset;">
                    <div class="carer-form">
                        <div class="row mb-4">
                            <div class="col-lg-6">
                                <label>Record Date</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-6">

                                <div class="box">
                                    <label>Record Type</label>
                                    <div class="trendClass-select small" tabindex="0">
                                        <span class="current">Select</span>

                                        <ul class="trendClass-list">
                                            <li class="trendClass-option selected" data-value="Nothing"> Weekly</li>
                                            <li class="trendClass-option" data-value="1"> Monthly</li>
                                            <li class="trendClass-option" data-value="2"> Quarterly</li>
                                            <li class="trendClass-option" data-value="3"> Ad Hoc</li>

                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="availabilityTabs createCarePlanTabs pActiveBehave">
                            <!-- TAB HEADER -->
                            <div class="availabilityTabs__nav">
                                <button
                                    id="tab-behaviour-edit"
                                    class="availabilityTabs__tab active"
                                    role="tab"
                                    aria-selected="true"
                                    aria-controls="panel-behaviour-edit"
                                    data-target="panel-behaviour-edit">
                                    <i class="bxr bx-brain"></i> Behaviour
                                </button>

                                <button
                                    id="tab-education-edit"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-education-edit"
                                    data-target="panel-education-edit">
                                    <i class="bxr bx-education"></i> Education/Schooling
                                </button>

                                <button
                                    id="tab-social-edit"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-social-edit"
                                    data-target="panel-social-edit">
                                    <i class="bxr bx-heart"></i> Social
                                </button>

                                <button
                                    id="tab-health-edit"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-health-edit"
                                    data-target="panel-health-edit">
                                    <i class="bxr bx-pulse"></i> Health
                                </button>

                                <button
                                    id="tab-independence-edit"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-independence-edit"
                                    data-target="panel-independence-edit">
                                    <i class="bxr bx-home-alt"></i> Independence
                                </button>

                                <button
                                    id="tab-activities-edit"
                                    class="availabilityTabs__tab"
                                    role="tab"
                                    aria-selected="false"
                                    aria-controls="panel-activities-edit"
                                    data-target="panel-activities-edit">
                                    <i class="bxr bx-star"></i> Activities
                                </button>

                            </div>

                            <!-- TAB CONTENT -->
                            <div class="availabilityTabs__content">

                                <div id="panel-behaviour-edit" class="availabilityTabs__panel active" role="tabpanel">
                                    <form action="">
                                        <div class="">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about behaviour..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>


                                        </div>
                                    </form>

                                </div>

                                <div id="panel-education-edit" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Attendance %</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Academic Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Above Expected</li>
                                                                <li class="trendClass-option" data-value="1">At Expected</li>
                                                                <li class="trendClass-option" data-value="2"> Below Expected</li>
                                                                <li class="trendClass-option" data-value="2">Significantly Below </li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about education/schooling..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div id="panel-social-edit" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Peer Relationships</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Excellent</li>
                                                                <li class="trendClass-option" data-value="1">Good</li>
                                                                <li class="trendClass-option" data-value="2"> Fair</li>
                                                                <li class="trendClass-option" data-value="2">Poor</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Emotional Regulation</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> </i> Excellent</li>
                                                                <li class="trendClass-option" data-value="1">Good</li>
                                                                <li class="trendClass-option" data-value="2"> Developing</li>
                                                                <li class="trendClass-option" data-value="2">Need Support</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about social & emotional..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                                <div id="panel-health-edit" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about health & wellbeing..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div id="panel-independence-edit" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about independence skills..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div id="panel-activities-edit" class="availabilityTabs__panel" role="tabpanel">
                                    <form action="">

                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="box">
                                                        <label>Trend</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"><i class=' trendGreenI bxr  bx-trending-up'></i> Improving</li>
                                                                <li class="trendClass-option" data-value="1"> <i class='   bxr  bx-minus'></i> Stable</li>
                                                                <li class="trendClass-option" data-value="2"> <i class='trendRedI bxr  bx-trending-down'></i> Declining</li>

                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="col-md-12  m-t-10">
                                                    <label>Notes</label>
                                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Notes about activities & engagement..." class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Key Achievements
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add achievement...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Concerns
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add concern...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer concernBe"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer concernBe"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-12  m-t-10">
                                                    <label>Recommendations & Care Plan Adjustments
                                                    </label>
                                                    <div class="addBadgMain">
                                                        <div class="d-flex align-items-center gap-3 userMum">
                                                            <div>
                                                                <input type="text" class="form-control badge-input" placeholder="Add recommendation...">
                                                            </div>
                                                            <div class="bgBtn add-badge-btn">
                                                                <i class='bx bx-plus'></i>
                                                            </div>
                                                        </div>
                                                        <div class="editBadgeContainer recomBlueBadg"><span class="achieveTitle">Engagement in de-briefs <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Engagement <i class="bx bx-x remove-badge"></i></span>
                                                            <span class="achieveTitle">Logan exhibits multiple challenging behaviours that impede educational attendance and emotional stability. <i class="bx bx-x remove-badge"></i></span>
                                                        </div>
                                                        <div class="badgeContainer recomBlueBadg"></div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Overall Rating (1-10)</label>
                                                    <input type="number" class="form-control">
                                                </div>
                                                <div class="col-md-6  m-t-10">

                                                    <div class="box">
                                                        <label>Overall Progress</label>
                                                        <div class="trendClass-select small" tabindex="0">
                                                            <span class="current">Select</span>

                                                            <ul class="trendClass-list">
                                                                <li class="trendClass-option selected" data-value="Nothing"> Significant Improvement</li>
                                                                <li class="trendClass-option" data-value="1"> Improvement</li>
                                                                <li class="trendClass-option" data-value="2"> Stable</li>
                                                                <li class="trendClass-option" data-value="2">Slight Decline</li>
                                                                <li class="trendClass-option" data-value="2">Significant Decline</li>
                                                            </ul>
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
                <div class="modal-footer d-flex gap-3 justify-content-end">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="borderBtn">Cancel</button>
                    <button type="submit" class="bgBtn darkBg submit ">Create Care Plan</button>
                </div>
            </div>
        </div>
    </div>
    <!-- AI Document Import Modal (outside all tab divs) -->
    <div class="modal fade" id="aiDocumentImportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="bx bx-import"></i> AI Document Import</h4>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" style="max-height:70vh;overflow-y:auto;">
                    <input type="hidden" id="docImportClientId" value="">
                    <input type="hidden" id="docImportId" value="">

                    <!-- Step 1: Upload -->
                    <div id="importStep1">
                        <h5 class="mb-3">Step 1: Upload Document</h5>
                        <div id="dropZone" style="border:2px dashed #ccc;border-radius:8px;padding:40px;text-align:center;cursor:pointer;background:#f9f9f9;transition:background 0.2s;">
                            <i class="far fa-file-alt" style="font-size:48px;color:#2563eb;"></i>
                            <p class="mt-2 mb-1"><strong>Drag &amp; drop document here</strong></p>
                            <p class="text-muted mb-0">or click to browse</p>
                            <p class="text-muted"><small>PDF or Word (.docx) &bull; Max 10MB</small></p>
                        </div>
                        <input type="file" id="pdfFileInput" accept=".pdf,.docx,.doc,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword" style="display:none;">
                        <div id="selectedFileInfo" style="display:none;" class="mt-3 p-3" style="background:#f0f7ff;border-radius:6px;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="far fa-file-pdf" style="color:#e74c3c;"></i>
                                    <strong id="selectedFileName" class="ml-2"></strong>
                                    <span id="selectedFileSize" class="text-muted ml-2"></span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearFileSelection()">Remove</button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn allBtnUseColor" id="uploadBtn" disabled onclick="uploadAndExtractText()">
                                <i class="bx bx-upload"></i> Upload &amp; Analyse
                            </button>
                        </div>
                        <div id="uploadProgress" style="display:none;" class="mt-3">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:100%;background:#2563eb;"></div>
                            </div>
                            <p class="text-muted mt-2" id="uploadStatusText">Uploading document...</p>
                        </div>
                    </div>

                    <!-- Step 2: Review -->
                    <div id="importStep2" style="display:none;">
                        <h5 class="mb-3">Step 2: Review Extracted Data</h5>
                        <p class="text-muted mb-3">Select which categories to import into the client's record.</p>
                        <div id="extractedDataContainer"></div>
                        <div id="noDataMessage" style="display:none;" class="text-center p-4 text-muted">
                            <i class="bx bx-info-circle" style="font-size:24px;"></i>
                            <p class="mt-2">No structured data could be extracted from this document.</p>
                        </div>
                        <div class="mt-3 d-flex gap-3">
                            <button class="btn allBtnUseColor" id="confirmImportBtn" onclick="confirmImport()">
                                <i class="bx bx-check"></i> Confirm Import (<span id="selectedCount">0</span> categories)
                            </button>
                            <button class="btn borderBtn" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>

                    <!-- Step 3: Summary -->
                    <div id="importStep3" style="display:none;">
                        <h5 class="mb-3">Step 3: Import Summary</h5>
                        <div id="importSummaryContainer"></div>
                        <div class="mt-3">
                            <button class="btn allBtnUseColor" data-dismiss="modal">Close</button>
                        </div>
                    </div>

                    <!-- Error display -->
                    <div id="importError" style="display:none;" class="alert alert-danger mt-3">
                        <i class="bx bx-error"></i> <span id="importErrorText"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- pratima modal end -->
    <style>
        .monthlyWeekly input[type=radio] {
            margin: -6px 0 0;
        }

        .basicTable .table>thead>tr>th {
            font-size: 14px;
        }

        .basicTable .table>tbody>tr>td {
            font-size: 14px;
            color: #3b3b3b;
        }
    </style>
    <!-- Ram Modal start -->
    <div class="modal fade leaveCommunStyle" id="onboardingDetails" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog pModalScroll modal-lg">
            <div class="modal-content">
                <div class="modal-header p24">
                    <div class="flexBw">
                        <div class="dFlexGap">
                            <div>
                                <h4 class="modal-title" id="onboardingDetailModalTitle">Billing Details</h4>
                            </div>
                        </div>
                        <button class="close" type="button" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                </div>
                <form id="onboardingDetailForm">
                    <div class="modal-body heightScrollModal" style="height: unset;">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="mb-4 onboardingDetailsRow">
                                    <div class="row align-items-center mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label mb-0">Billing Type <span class="radStar">*</span></label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="monthlyWeekly form-check form-check-inline d-flex align-items-center gap-2">
                                                    <input type="radio" class="form-check-input" id="weeklyfrequency" name="frequency" value="1" {{ ($clientDetails['billing_frequency'] == 1 || empty($clientDetails['billing_frequency'])) ? 'checked' : '' }}>
                                                    <label for="weeklyfrequency" class="form-check-label ms-1">Weekly</label>
                                                </div>
                                                <div class="monthlyWeekly form-check form-check-inline d-flex align-items-center gap-2">
                                                    <input type="radio" class="form-check-input" id="monthlyfrequency" name="frequency" value="2" {{ ($clientDetails['billing_frequency'] == 2) ? 'checked' : '' }}>
                                                    <label for="monthlyfrequency" class="form-check-label ms-1">Monthly</label>
                                                </div>
                                                <input type="text" name="frequency_rate" id="onboardingDetailFrequencyRate" class="form-control checkOnboardingDetail ms-auto" placeholder="Frequency Rate" value="{{ $clientDetails['billing_rate'] ?? ($home_details->weekly_allowance_service_users ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-md-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label mb-0">Funding Type <span class="radStar">*</span></label>
                                                <button type="button" class="btn btn-outline-primary btn-sm addFundingRow"><i class="bx bx-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div id="fundingTypeContainer">
                                                <!-- Dynamic rows will be loaded here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end modal-footer mt-4">
                        <div class="dFlexGap">
                            <input type="hidden" id="onboardingDetail_id" name="id">
                            <button class="borderBtn" type="button" data-dismiss="modal" aria-hidden="true">Cancel</button>
                            <button type="button" class="bgBtn" id="onboardingDetailSaveBtn"> <i class="bx bx-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <!-- Ram modal end -->
    <!-- script for URL's variables -->
    <script>
        var saveMedicationLogUrl = "{{url('roster/client/medication-log-save')}}";
        var listMedicationLogUrl = "{{url('roster/client/medication-log-list')}}";
        var token = "{{csrf_token()}}";
        var listClientCareTaskUrl = "{{url('roster/client/care-task-list')}}";
        var clientCareTaskEditUrl = "{{url('/roster/care-task-edit')}}";
        var clientCareTaskDeleteUrl = "{{url('/roster/care-task-delete')}}";
        var client_id = "{{$client_id}}";
        var clientCareTaskAddUrl = "{{url('roster/care-task-add?client_id=')}}" + client_id;
        var saveClientAlertUrl = "{{url('roster/client-alert-save')}}";
        var editClientAlertUrl = "{{url('roster/client-alert-edit')}}";
        var listAlertTypeUrl = "{{url('roster/client/alert-type')}}";
        var increaseAcknowledgeUrl = "{{url('roster/client/alert-increase-acknowledge')}}";
        var alert_resolveUrl = "{{url('roster/client/alert-resolve')}}";
        var alert_archivedUrl = "{{url('roster/client/alert-archived')}}";
        var increaseAllAcknowledgeUrl = "{{url('roster/client/alert-increase-all-acknowledge')}}";
        var saveDolsUrl = "{{url('roster/client/save-dols')}}";
        var dolsListUrl = "{{url('roster/client/dols-list')}}";
        var clientCarePlanSaveUrl = "{{url('roster/client/care-plan-save')}}";
        var clientCarePlanListUrl = "{{url('roster/client/care-plan-get-list')}}";
        var clientCarePlanDeleteUrl = "{{url('roster/client/care-plan-delete')}}";
        var clientCarePlanDetailsUrl = "{{url('roster/client/care-plan-details')}}";

        var clientCarePlanObjectiveDeleteUrl = "{{url('roster/client/care-plan-objective-delete')}}";
        var clientCarePlanTaskDeleteUrl = "{{url('roster/client/care-plan-task-delete')}}";
        var clientCarePlanMedicalDeleteUrl = "{{url('roster/client/care-plan-medical-delete')}}";
        var clientCarePlanRiskDeleteUrl = "{{url('roster/client/care-plan-risk-delete')}}";
        var get_document_ai_response = "{{url('roster/get-document-ai-response')}}";
        var docImportBaseUrl = '{{ url("roster/ai-document-import") }}';

        var saveConsentUrl = "{{ url('roster/client/consent-save') }}";
        var consentListUrl = "{{ url('roster/client/consent-list') }}";
        var consentDeleteUrl = "{{ url('roster/client/consent-delete') }}";
        var consentStatusChangeUrl = "{{ url('roster/client/consent-status-change') }}";

        var saveDnaCprUrl = "{{ url('roster/client/dna-cpr-save') }}";
        var dnaCprListUrl = "{{ url('roster/client/dna-cpr-list') }}";
        var dnaCprDeleteUrl = "{{ url('roster/client/dna-cpr-delete') }}";
        var dnaCprDetailsUrl = "{{ url('roster/client/dna-cpr-details') }}";
    </script>
    <!-- end here -->
    <script src="{{ url('public/js/roster/client/client_details.js')}}" defer></script>
    <script src="{{ url('public/js/roster/client/client_alert.js')}}" defer></script>
    <script src="{{ url('public/js/roster/client/client_dols.js')}}" defer></script>
    <script src="{{ url('public/js/roster/client/mar_sheets.js')}}" defer></script>
    <script src="{{ url('public/js/roster/client/mar_grid.js')}}" defer></script>
    <script src="{{ url('public/js/roster/client/care_plan.js')}}" defer></script>
    <script src="{{ url('public/js/roster/ai-document-import.js') }}"></script>
    <script src="{{ url('public/js/roster/client/consent.js') }}" defer></script>
    <script src="{{ url('public/js/roster/client/dnaCpr.js') }}" defer></script>
    <script>
        const tabs = document.querySelectorAll(".tab");
        const contents = document.querySelectorAll(".content");

        tabs.forEach(tab => {
            tab.addEventListener("click", () => {
                document.querySelector(".tab.active")?.classList.remove("active");
                tab.classList.add("active");

                let tabName = tab.getAttribute("data-tab");
                if (tabName == 'clientOnboardingTab') {
                    loadOnboardingData()
                }

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
    </script>

    <!-- pratima js start -->
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://jqueryniceselect.hernansartorio.com/js/jquery.nice-select.min.js"></script>
    <script>
        function reindexContacts() {
            $('#editableContactsContainer .contact-row').each(function(index) {
                // Update the contact label title
                $(this).find('.contact-number-label').text('Contact #' + (index + 1));
                // Update name attributes for inputs
                $(this).find('input').each(function() {
                    var nameAttr = $(this).attr('name');
                    if (nameAttr) {
                        var newName = nameAttr.replace(/contacts\[(INDEX|\d+)\]/g, 'contacts[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }

        function addBlankContactRow() {
            var templateHtml = $('#contactRowTemplate').html();
            var $newRow = $(templateHtml);
            $newRow.find('input').val('');
            $('#editableContactsContainer').append($newRow);
            reindexContacts();
        }

        function populateEditForm(focusId) {
            $('#editableContactsContainer').empty();
            
            var contacts = [];
            var details = typeof clientDetails !== 'undefined' ? clientDetails : (window.clientDetails || null);
            if (details && details.emergency_contacts) {
                contacts = details.emergency_contacts;
            }
            
            if (contacts.length > 0) {
                contacts.forEach(function(contact) {
                    var templateHtml = $('#contactRowTemplate').html();
                    var $newRow = $(templateHtml);
                    
                    $newRow.find('.contact-id').val(contact.id);
                    $newRow.find('.contact-name').val(contact.name);
                    $newRow.find('.contact-phone').val(contact.phone_no);
                    $newRow.find('.contact-relationship').val(contact.relationship);
                    
                    $('#editableContactsContainer').append($newRow);
                });
                reindexContacts();
            } else {
                addBlankContactRow();
            }

            if (focusId) {
                // Find the row containing this ID and focus on its name input
                var $row = $('#editableContactsContainer').find('.contact-id[value="' + focusId + '"]').closest('.contact-row');
                if ($row.length > 0) {
                    $row.find('.contact-name').focus();
                    $('html, body').animate({
                        scrollTop: $row.offset().top - 100
                    }, 500);
                }
            }
        }

        $(document).on('click', '#editContactsBtn', function() {
            $('#emergencyAller .emergencyBtn').hide();
            $('#emergencyAller .emergencyListContainer').hide();
            $('#contactWrapper').show();
            populateEditForm();
        });

        $(document).on('click', '.add-first-contact-btn', function() {
            $('#emergencyAller .emergencyBtn').hide();
            $('#emergencyAller .emergencyListContainer').hide();
            $('#contactWrapper').show();
            populateEditForm();
        });

        $(document).on('click', '#addContactBtn', function() {
            addBlankContactRow();
        });

        $(document).on('click', '.remove-contact-row', function() {
            $(this).closest('.contact-row').remove();
            reindexContacts();
        });

        $(document).on('click', '#cancelContactsBtn', function() {
            $('#contactWrapper').hide();
            $('#emergencyAller .emergencyBtn').show();
            $('#emergencyAller .emergencyListContainer').show();
        });

        $('#emergencyContactsForm').on('submit', function(e) {
            e.preventDefault();
            
            var $saveBtn = $('#saveContactsBtn');
            $saveBtn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                type: "POST",
                url: "{{ route('roster.client.emergency_contact.save') }}",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                        $saveBtn.prop('disabled', false).text('Save Contacts');
                    }
                },
                error: function(xhr) {
                    var msg = 'Something went wrong. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    toastr.error(msg);
                    $saveBtn.prop('disabled', false).text('Save Contacts');
                }
            });
        });

        $(document).on('click', '.edit-single-contact', function() {
            var contactId = $(this).data('id');
            $('#emergencyAller .emergencyBtn').hide();
            $('#emergencyAller .emergencyListContainer').hide();
            $('#contactWrapper').show();
            populateEditForm(contactId);
        });

        $(document).on('click', '.delete-single-contact', function() {
            var contactId = $(this).data('id');
            if (confirm('Are you sure you want to delete this emergency contact?')) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('roster.client.emergency_contact.delete') }}",
                    data: {
                        id: contactId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#contact-card-' + contactId).fadeOut(function() {
                                $(this).remove();
                                if ($('.contact-card').length === 0 && !$('.legacy-contact-card').length) {
                                    location.reload();
                                }
                            });
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        var msg = 'Something went wrong. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            }
        });
    </script>


    <script>
        const chartOne = () => {
            const svg = d3.select("#chart1");
            const tooltip = d3.select("#tooltip1");

            // Sample data (unchanged)
            const data = [{
                value: 0.5,
                label: "A",
                color: "red",
                details: {
                    date: "Dec 16",
                    behaviour: 3,
                    education: 1,
                    social: 2
                }
            }];

            const colorMap = {
                behaviour: "purple",
                education: "blue",
                social: "red",
                date: "black"
            };

            let margin = {
                top: 30,
                right: 30,
                bottom: 60,
                left: 50
            };
            let width, height;
            const container = document.getElementById("chart-container");
            const resizeObserver = new ResizeObserver(() => {
                updateChart();
            })
            resizeObserver.observe(container);

            const updateChart = () => {

                const rect = container.getBoundingClientRect();
                const width = rect.width || container.offsetWidth || window.innerWidth;
                const height = 300;
                if (width === 0) return;
                // Update margins for small screens
                if (width < 768) {
                    margin.right = 10;
                    margin.left = 40;
                }

                svg.attr("width", width)
                    .attr("height", height)
                    .attr("viewBox", `0 0 ${width} ${height}`)
                    .attr("preserveAspectRatio", "xMidYMid meet");
                // Scales
                const xScale = d3.scaleLinear().domain([0, 100]).range([margin.left, width - margin.right]);
                const yScale = d3.scaleLinear().domain([0, 10]).range([height - margin.bottom, margin.top]);

                // Clear and redraw all elements (simplified; in production, update existing)
                svg.selectAll("*").remove();

                // Y grid
                svg.append("g")
                    .attr("class", "grid")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]).tickSize(-(width - margin.left - margin.right)).tickFormat(""));

                // X label
                svg.append("g")
                    .attr("transform", `translate(0,${height - margin.bottom})`)
                    .append("text")
                    .attr("x", xScale(50))
                    .attr("y", 20)
                    .attr("text-anchor", "middle")
                    .attr("fill", "#a8a8a8")
                    .text("16 Dec");

                // Horizontal line
                svg.append("line")
                    .attr("x1", margin.left).attr("x2", width - margin.right)
                    .attr("y1", height - margin.bottom).attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8").attr("stroke-width", 2);

                // Y axis
                const yAxis = svg.append("g")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]));
                yAxis.selectAll("text").attr("fill", "#a8a8a8").style("font-size", "14px");
                yAxis.selectAll(".tick line").attr("stroke", "#a8a8a8").attr("stroke-width", 2);
                yAxis.select(".domain").attr("stroke", "#a8a8a8").attr("stroke-width", 2);

                // Vertical line
                const middleX = xScale(50);
                const verticalLine = svg.append("line")
                    .attr("x1", middleX).attr("x2", middleX)
                    .attr("y1", margin.top).attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8").attr("stroke-width", 1)
                    .attr("stroke-dasharray", "4,4");

                // Circles
                const circles = svg.selectAll("circle")
                    .data(data)
                    .enter()
                    .append("circle")
                    .attr("cx", middleX)
                    .attr("cy", (d) => yScale(d.value))
                    .attr("r", 3)
                    .attr("fill", "none")
                    .attr("stroke", (d) => d.color)
                    .attr("stroke-width", 2);

                // Hover overlay
                svg.append("rect")
                    .attr("x", margin.left).attr("y", margin.top)
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .attr("fill", "transparent")
                    .on("mouseover", () => {
                        circles.attr("fill", (d) => d.color);
                        verticalLine.attr("stroke-dasharray", "");
                        const d = data[0];
                        tooltip.style("opacity", 1)
                            .html(`
                  <div><span style="color:${colorMap.date}"><strong>${d.details.date}</strong></span></div>
                  <div><span style="color:${colorMap.behaviour}">Over All Rating: 2</span></div>
                `)
                            .style("left", (middleX + 15) + "px")
                            .style("top", (margin.top + 20) + "px");
                    })
                    .on("mouseout", () => {
                        circles.attr("fill", "none");
                        verticalLine.attr("stroke-dasharray", "4,4");
                        tooltip.style("opacity", 0).html("");
                    });
            };

            // Initial render
            updateChart();

            // Resize listener
            window.addEventListener("resize", updateChart);
        };
        chartOne()


        // second chart
        const chartTwo = () => {
            const svg = d3.select("#chart2");
            const tooltip = d3.select("#tooltip2");
            // Sample data (unchanged)
            const data = [{
                    value: 0.5,
                    label: "A",
                    color: "red",
                    details: {
                        date: "Dec 16",
                        behaviour: 3,
                        education: 1,
                        social: 2
                    },
                },
                {
                    value: 1.5,
                    label: "B",
                    color: "green",
                    details: {
                        date: "Dec 16",
                        behaviour: 2,
                        education: 0,
                        social: 1
                    },
                },
                {
                    value: 2,
                    label: "C",
                    color: "orange",
                    details: {
                        date: "Dec 16",
                        behaviour: 4,
                        education: 2,
                        social: 3
                    },
                },
                {
                    value: 3,
                    label: "D",
                    color: "purple",
                    details: {
                        date: "Dec 16",
                        behaviour: 1,
                        education: 1,
                        social: 0
                    },
                },
            ];

            const colorMap = {
                behaviour: "purple",
                education: "blue",
                social: "red",
                date: "black"
            };

            let margin = {
                top: 30,
                right: 30,
                bottom: 20,
                left: 30
            };
            let width, height;
            const container = document.getElementById("chart-container2");
            const resizeObserver = new ResizeObserver(() => {
                updateChart();
            })
            resizeObserver.observe(container);
            const updateChart = () => {

                const rect = container.getBoundingClientRect();
                const width = rect.width || container.offsetWidth || window.innerWidth;
                const height = 300;
                if (width === 0) return;

                // Update margins for small screens
                if (width < 768) {
                    margin.right = 10;
                    margin.left = 40;
                }

                svg.attr("width", width)
                    .attr("height", height)
                    .attr("viewBox", `0 0 ${width} ${height}`)
                    .attr("preserveAspectRatio", "xMidYMid meet");
                // Scales
                const xScale = d3.scaleLinear().domain([0, 100]).range([margin.left, width - margin.right]);
                const yScale = d3.scaleLinear().domain([0, 10]).range([height - margin.bottom, margin.top]);

                // Clear and redraw all elements (simplified; in production, update existing)
                svg.selectAll("*").remove();

                // Y grid
                svg.append("g")
                    .attr("class", "grid")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]).tickSize(-(width - margin.left - margin.right)).tickFormat(""));

                // X label
                svg.append("g")
                    .attr("transform", `translate(0,${height - margin.bottom})`)
                    .append("text")
                    .attr("x", xScale(50))
                    .attr("y", 20)
                    .attr("text-anchor", "middle")
                    .attr("fill", "#a8a8a8")
                    .text("16 Dec");

                // Horizontal line
                svg.append("line")
                    .attr("x1", margin.left).attr("x2", width - margin.right)
                    .attr("y1", height - margin.bottom).attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8").attr("stroke-width", 2);

                // Y axis
                const yAxis = svg.append("g")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]));
                yAxis.selectAll("text").attr("fill", "#a8a8a8").style("font-size", "14px");
                yAxis.selectAll(".tick line").attr("stroke", "#a8a8a8").attr("stroke-width", 2);
                yAxis.select(".domain").attr("stroke", "#a8a8a8").attr("stroke-width", 2);

                // Vertical line
                const middleX = xScale(50);
                const verticalLine = svg.append("line")
                    .attr("x1", middleX).attr("x2", middleX)
                    .attr("y1", margin.top).attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8").attr("stroke-width", 1)
                    .attr("stroke-dasharray", "4,4");

                // Circles
                const circles = svg.selectAll("circle")
                    .data(data)
                    .enter()
                    .append("circle")
                    .attr("cx", middleX)
                    .attr("cy", (d) => yScale(d.value))
                    .attr("r", 3)
                    .attr("fill", "none")
                    .attr("stroke", (d) => d.color)
                    .attr("stroke-width", 2);

                // Hover overlay
                svg.append("rect")
                    .attr("x", margin.left).attr("y", margin.top)
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .attr("fill", "transparent")
                    .on("mouseover", () => {
                        circles.attr("fill", (d) => d.color);
                        verticalLine.attr("stroke-dasharray", "");
                        const d = data[0];
                        tooltip.style("opacity", 1)
                            .html(`
                  <div><span style="color:${colorMap.date}"><strong>${d.details.date}</strong></span></div>
        <div><span style="color:${colorMap.behaviour}">Behaviour: ${d.details.behaviour}</span></div>
        <div><span style="color:${colorMap.education}">Education: ${d.details.education}</span></div>
        <div><span style="color:${colorMap.social}">Social: ${d.details.social}</span></div>
                `)
                            .style("left", (middleX + 15) + "px")
                            .style("top", (margin.top + 20) + "px");
                    })
                    .on("mouseout", () => {
                        circles.attr("fill", "none");
                        verticalLine.attr("stroke-dasharray", "4,4");
                        tooltip.style("opacity", 0).html("");
                    });
            };

            // Initial render
            updateChart();

            // Resize listener
            window.addEventListener("resize", updateChart);
        };
        chartTwo()

        // radar chart

        var options = {
            series: [{
                name: 'Series 1',
                data: [80, 50, 30, 40, 100, 20],
            }],
            chart: {
                type: 'radar',
                height: 300,
                width: '100%' // 👈 THIS
            },

            yaxis: {
                stepSize: 20
            },
            xaxis: {
                categories: ['Behaviour', 'Education/Schooling', 'Social & Emotional', 'Health & Wellbeing', 'Independence Skills', 'Activities & Engagement']
            }
        };
        var chart = new ApexCharts(document.querySelector("#chartRadar"), options);
        chart.render();

        function renderMiniChart({
            containerId,
            svgId,
            tooltipId,
            data,
            mainColor,
            ratingText
        }) {
            const svg = d3.select(`#${svgId}`);
            const tooltip = d3.select(`#${tooltipId}`);
            const container = document.getElementById(containerId);

            let margin = {
                top: 30,
                right: 30,
                bottom: 15,
                left: 20
            };

            const resizeObserver = new ResizeObserver(updateChart);
            resizeObserver.observe(container);

            function updateChart() {
                const rect = container.getBoundingClientRect();
                const width = rect.width;
                const height = 160;

                if (!width) return;

                if (width < 768) {
                    margin.right = 10;
                    margin.left = 40;
                }

                svg
                    .attr("width", width)
                    .attr("height", height)
                    .attr("viewBox", `0 0 ${width} ${height}`)
                    .attr("preserveAspectRatio", "xMidYMid meet");

                svg.selectAll("*").remove();

                const xScale = d3.scaleLinear().domain([0, 100]).range([margin.left, width - margin.right]);
                const yScale = d3.scaleLinear().domain([0, 10]).range([height - margin.bottom, margin.top]);

                // Grid
                svg.append("g")
                    .attr("class", "grid")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]).tickSize(-(width - margin.left - margin.right)).tickFormat(""));


                // X label
                svg.append("text")
                    .attr("x", xScale(50))
                    .attr("y", height - 1)
                    .attr("text-anchor", "middle")
                    .attr("fill", "#a8a8a8")
                    .text(data[0].details.date);

                // Horizontal base line
                svg.append("line")
                    .attr("x1", margin.left).attr("x2", width - margin.right)
                    .attr("y1", height - margin.bottom).attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8").attr("stroke-width", 2);

                // Y axis
                const yAxis = svg.append("g")
                    .attr("transform", `translate(${margin.left},0)`)
                    .call(d3.axisLeft(yScale).tickValues([0, 3, 6, 10]));
                yAxis.selectAll("text").attr("fill", "#a8a8a8").style("font-size", "14px");
                yAxis.selectAll(".tick line").attr("stroke", "#a8a8a8").attr("stroke-width", 2);
                yAxis.select(".domain").attr("stroke", "#a8a8a8").attr("stroke-width", 2);


                // Vertical marker
                const middleX = xScale(50);
                const verticalLine = svg.append("line")
                    .attr("x1", middleX)
                    .attr("x2", middleX)
                    .attr("y1", margin.top)
                    .attr("y2", height - margin.bottom)
                    .attr("stroke", "#a8a8a8")
                    .attr("stroke-width", 0)
                    .attr("stroke-dasharray", "4,4");

                // Data circle
                const circles = svg.selectAll("circle")
                    .data(data)
                    .enter()
                    .append("circle")
                    .attr("cx", middleX)
                    .attr("cy", d => yScale(d.value))
                    .attr("r", 3)
                    .attr("fill", "none")
                    .attr("stroke", mainColor)
                    .attr("stroke-width", 2);

                // Hover
                svg.append("rect")
                    .attr("x", margin.left)
                    .attr("y", margin.top)
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .attr("fill", "transparent")
                    .on("mouseover", () => {
                        circles.attr("fill", mainColor);
                        verticalLine.attr("stroke-width", 1).attr("stroke-dasharray", "");

                        tooltip
                            .style("opacity", 1)
                            .html(`
           <strong>${data[0].details.date}</strong><br/>
           <span style="color:${mainColor}; font-weight:500;">
    ${ratingText}: ${data[0].value}
  </span>
          `)
                            .style("left", middleX + 15 + "px")
                            .style("top", margin.top + 20 + "px");
                    })
                    .on("mouseout", () => {
                        circles.attr("fill", "none");
                        verticalLine.attr("stroke-width", 0).attr("stroke-dasharray", "4,4");
                        tooltip.style("opacity", 0);
                    });
            }

            updateChart();
            window.addEventListener("resize", updateChart);
        }
        renderMiniChart({
            containerId: "chart-container3",
            svgId: "chart3",
            tooltipId: "tooltip3",
            mainColor: "#8f61f6",
            ratingText: "Overall Rating",
            data: [{
                value: 3,
                details: {
                    date: "Dec 16"
                }
            }]
        });
        renderMiniChart({
            containerId: "chart-container4",
            svgId: "chart4",
            tooltipId: "tooltip4",
            mainColor: "#3b82f6",
            ratingText: "Overall Rating",
            data: [{
                value: 3,
                details: {
                    date: "Dec 16"
                }
            }]
        });
        renderMiniChart({
            containerId: "chart-container5",
            svgId: "chart5",
            tooltipId: "tooltip5",
            mainColor: "#ec4899",
            ratingText: "Health Score",
            data: [{
                value: 6,
                details: {
                    date: "Dec 16"
                }
            }]
        });
        renderMiniChart({
            containerId: "chart-container6",
            svgId: "chart6",
            tooltipId: "tooltip6",
            mainColor: "#10b981",
            ratingText: "Activity Level",
            data: [{
                value: 8,
                details: {
                    date: "Dec 16"
                }
            }]
        });
        renderMiniChart({
            containerId: "chart-container7",
            svgId: "chart7",
            tooltipId: "tooltip7",
            mainColor: "#f59e0b",
            ratingText: "Activity Level",
            data: [{
                value: 8,
                details: {
                    date: "Dec 16"
                }
            }]
        });
        renderMiniChart({
            containerId: "chart-container8",
            svgId: "chart8",
            tooltipId: "tooltip8",
            mainColor: "#6366f1",
            ratingText: "Activity Level",
            data: [{
                value: 8,
                details: {
                    date: "Dec 16"
                }
            }]
        });
    </script>
    <!-- new js -->

    <script>
        // for document toggle
        document.querySelectorAll(".bgBtn[data-target-form]").forEach(button => {
            button.addEventListener("click", () => {
                const formId = button.dataset.targetForm;
                const form = document.querySelector(`.docForm[data-form-id="${formId}"]`);

                // toggle display
                if (form.style.display === "block") {
                    form.style.display = "none";
                } else {
                    form.style.display = "block";
                }
            });
        });

        // Close button inside form
        document.querySelectorAll(".docForm .cancelBtn").forEach(btn => {
            btn.addEventListener("click", () => {
                const form = btn.closest(".docForm");
                form.style.display = "none";
            });
        });
    </script>
    <!-- select js -->
    <script>
        document.querySelectorAll(".trendClass-select").forEach(select => {
            const current = select.querySelector(".current");
            const options = select.querySelectorAll(".trendClass-option");

            // 🔹 initial state (muted if Select)
            if (current.textContent.trim().toLowerCase() === "select") {
                select.classList.remove("has-value");
            }

            // Toggle dropdown
            select.addEventListener("click", e => {
                select.classList.toggle("open");
            });

            // Option click
            options.forEach(option => {
                option.addEventListener("click", e => {
                    e.stopPropagation();
                    if (option.classList.contains("disabled")) return;

                    options.forEach(o => o.classList.remove("selected"));
                    option.classList.add("selected");

                    current.innerHTML = option.innerHTML;

                    // ✅ change color logic
                    if (current.textContent.trim().toLowerCase() === "select") {
                        select.classList.remove("has-value"); // muted
                    } else {
                        select.classList.add("has-value"); // black
                    }

                    select.classList.remove("open");
                });
            });
        });

        // Close on outside click
        document.addEventListener("click", e => {
            document.querySelectorAll(".trendClass-select.open").forEach(openSelect => {
                if (!openSelect.contains(e.target)) {
                    openSelect.classList.remove("open");
                }
            });
        });
    </script>

    <!-- select end -->

    <script>
        // edit badg
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-badge")) {
                const badge = e.target.closest(".achieveTitle");
                if (badge) {
                    badge.remove();
                }
            }
        });
        // Add badge function
        function addBadge(wrapper) {
            const input = wrapper.querySelector(".badge-input");
            const badgeContainer = wrapper.querySelector(".badgeContainer");

            const value = input.value.trim();
            if (!value) return;

            const badge = document.createElement("span");
            badge.className = "achieveTitle";
            badge.innerHTML = `
      ${value}
      <i class='bx bx-x remove-badge'></i>
    `;

            badgeContainer.appendChild(badge);
            input.value = "";
        }

        // Click + button
        document.addEventListener("click", e => {
            if (e.target.closest(".add-badge-btn")) {
                const wrapper = e.target.closest(".addBadgMain");
                addBadge(wrapper);
            }
        });

        // Press Enter
        document.addEventListener("keydown", e => {
            if (e.key === "Enter" && e.target.classList.contains("badge-input")) {
                e.preventDefault();
                const wrapper = e.target.closest(".addBadgMain");
                addBadge(wrapper);
            }
        });

        // Remove badge
        document.addEventListener("click", e => {
            if (e.target.classList.contains("remove-badge")) {
                e.target.parentElement.remove();
            }
        });
    </script>

    <!-- onboarding js -->
    <script>
        const togglesBoard = document.querySelectorAll(".boardingToggle");
        const contentsBoard = document.querySelectorAll(".onboardContent");

        togglesBoard.forEach((box, index) => {
            box.addEventListener("click", function() {
                contentsBoard[index].classList.toggle("d-none");
            });
        });
    </script>


    <!-- onboarding js -->

    <!-- pratima js end -->
    <script>
        var clientDetails = @json($clientDetails);
        var task_category = @json($task_category);
        // console.log(task_category);
    </script>
    <script>
        $(document).on('click', '.editClient', function() {
            if (clientDetails.image) {

                var $fileupload = $('.fileupload');
                var $preview = $fileupload.find('.fileupload-preview');
                var imgUrl = "{{url('public/images/serviceUserProfileImages')}}";
                $preview.html(
                    '<img src="' + imgUrl + '/' + clientDetails.image + '" ' +
                    'style="max-height:150px; max-width:200px;" />'
                );
                $fileupload.removeClass('fileupload-new').addClass('fileupload-exists');
            }
            $("#suClientId").val(clientDetails.id);
            $("#su_name").val(clientDetails.name);
            $("#su_user_name").val(clientDetails.user_name);
            $("#date_of_birth").val(clientDetails.date_of_birth);
            $("#phone_no").val(clientDetails.phone_no);
            $("#hair_and_eyes").val(clientDetails.hair_and_eyes);
            $("#markings").val(clientDetails.markings);
            $("#start_date").val(clientDetails.start_date);
            $("#end_date").val(clientDetails.end_date);
            $("#suEmail").val(clientDetails.email);
            $("#department").val(clientDetails.department);
            $("#admission_number").val(clientDetails.admission_number);
            $("#suStatus").val(clientDetails.status);
            $("#suMobility").val(clientDetails.suMobility);
            $("#suFundingType").val(clientDetails.suFundingType);
            $("#section").val(clientDetails.section);
            $("#ethnicity_id").val(clientDetails.ethnicity_id);
            $("#short_description").val(clientDetails.short_description);
            $("#street").val(clientDetails.street);
            $("#city").val(clientDetails.city);
            $("#postcode").val(clientDetails.postcode);
            $("#care_needs").val(clientDetails.care_needs);
            $("#medical_notes").val(clientDetails.medical_notes);
            $("#em_name").val(clientDetails.em_name);
            $("#em_phone").val(clientDetails.em_phone);
            $("#relationship").val(clientDetails.relationship);
            $("#height_unit").val(clientDetails.height_unit);
            $("#height_dropdown").val(clientDetails.height_ft);
            $("#height_in_dropdown").val(clientDetails.height_in);
            $("#weight_unit").val(clientDetails.weight_unit);
            $("#weight_dropdown").val(clientDetails.weight);
            let selectedCourses = clientDetails.courses.map(c => c.coursenumber);
            childCourseData(null, function() {
                autoCheckCourses(selectedCourses);
            });
        });

        function autoCheckCourses(selectedCourses) {

            $('.course_qualifications').each(function() {

                let checkbox = $(this);
                let courseNumber = checkbox.data('coursenumber');

                if (selectedCourses.includes(courseNumber)) {

                    checkbox.prop('checked', true);

                    let box = checkbox.closest('.course-box');

                    // name add
                    box.find('[data-name]').each(function() {
                        $(this).attr('name', $(this).data('name'));
                    });

                    // file enable
                    // box.find('.qual_upload')
                    //    .prop('disabled', false);
                }
            });
        }
    </script>

    <script>
        $(document).on('click', '.aiInsightsBtn', function() {
            $('.aiInsightsBtn').hide();
            if ($(this).data('tab_id') == 1) {
                $('.productAnalysisHideDefault').show();
                $('.handoverSummaryHideDefault').hide();
                $('.carePlanReviewHideDefault').hide();
            } else if ($(this).data('tab_id') == 2) {
                $('.productAnalysisHideDefault').hide();
                $('.handoverSummaryHideDefault').show();
                $('.carePlanReviewHideDefault').hide();
            } else if ($(this).data('tab_id') == 3) {
                $('.productAnalysisHideDefault').hide();
                $('.handoverSummaryHideDefault').hide();
                $('.carePlanReviewHideDefault').show();
            }
        });
        $(document).on('click', '.tab', function() {
            $('.aiInsightsBtn').hide();
            if ($(this).data('tab') === 'clientAIInsightsTab') {
                $('.aiInsightsBtn').show();
                $('.productAnalysisHideDefault').hide();
                $('.handoverSummaryHideDefault').hide();
                $('.carePlanReviewHideDefault').hide();
            }
        });
        $(document).on('click', '.viewPlanBtn', function() {
            $('.carePlanBtnSectionFirst').hide();
            $('.carePlanBtnSectionSecond').show();
        });
        $(document).on('click', '#planBackBtn', function() {
            $('.carePlanBtnSectionFirst').show();
            $('.carePlanBtnSectionSecond').hide();
        });
        $(document).on('click', '.riskAssessmentDeatils', function() {
            $('.riskAssessmentSectionSecond').show();
            $('.riskAssessmentSectionFirst').hide();
        });
        $(document).on('click', '#riskAssesmentBackBtn', function() {
            $('.riskAssessmentSectionSecond').hide();
            $('.riskAssessmentSectionFirst').show();
        });
        $(document).on('click', '#logMedicationBtn', function() {
            setDateTimeFormat();
            $(".medicationLogsForm").toggle();
        });
        $(document).on('click', '.marSheetDetails', function() {
            $(".medicationSectionFirst").hide();
            $(".medicationSectionSecond").show();
        });
        $(document).on('click', '#medicationBackBtn', function() {
            $(".medicationSectionFirst").show();
            $(".medicationSectionSecond").hide();
        });
        $(document).on('click', '.peepDetailsBtn', function() {
            $(".peepSectionFirst").hide();
            $(".peepSectionSecond").show();
        });
        $(document).on('click', '#peepBackBtn', function() {
            $(".peepSectionFirst").show();
            $(".peepSectionSecond").hide();
        });
        $(document).on('click', '.behaviorChartDetailsBtn', function() {
            $(".behaviorChartSectionFirst").hide();
            $(".behaviorChartSectionSecond").show();
        });
        $(document).on('click', '#behaviorBackBtn', function() {
            $(".behaviorChartSectionFirst").show();
            $(".behaviorChartSectionSecond").hide();
        });
        $(document).on('click', '.mentalCapAsessmentDetailsBtn', function() {
            $(".mentalCapAsessmentSectonFirst").hide();
            $(".mentalCapAsessmentSectonSecond").show();
        });
        $(document).on('click', '#mentalCapAsessmentBackBtn', function() {
            $(".mentalCapAsessmentSectonFirst").show();
            $(".mentalCapAsessmentSectonSecond").hide();
        });
        $(document).on('click', '.addDolsRecordBtn', function() {
            $(".dolsSectionFirst").show();
            $(".dolsSectionSecond").hide();
            if ($(this).data('formtype') == 'add') {
                $("#clientDolsForm")[0].reset();
            } else {
                $("#dols_status").val($(this).data('dols_status'));
                $("#authorisation_type").val($(this).data('authorisation_type'));
                $("#referral_date").val($(this).data('referral_date'));
                $("#authorisation_start_date").val($(this).data('authorisation_start_date'));
                $("#authorisation_end_date").val($(this).data('authorisation_end_date'));
                $("#review_date").val($(this).data('review_date'));
                $("#supervisory_body").val($(this).data('supervisory_body'));
                $("#case_reference").val($(this).data('case_reference'));
                $("#best_interests_assessor").val($(this).data('best_interests_assessor'));
                $("#mental_health_assessor").val($(this).data('mental_health_assessor'));
                $("#reason_for_dols").text($(this).data('reason_for_dols') || '');
                $("#imca_appointed").val($(this).data('imca_appointed')).prop('checked', $(this).data('imca_appointed') == 1);
                $("#mental_capacity_assessment").val($(this).data('mental_capacity_assessment')).prop('checked', $(this).data('mental_capacity_assessment') == 1);
                $("#appeal_rights").val($(this).data('appeal_rights')).prop('checked', $(this).data('appeal_rights') == 1);
                $("#care_plan_updated").val($(this).data('care_plan_updated')).prop('checked', $(this).data('care_plan_updated') == 1);
                $("#family_notified").val($(this).data('family_notified')).prop('checked', $(this).data('family_notified') == 1);
                $("#additional_notes").text($(this).data('additional_notes') || '');
                $("#dols_id").val($(this).data('id'));
            }
        });
        $(document).on('click', '#closeDolsformBtn', function() {
            $(".dolsSectionFirst").hide();
            $(".dolsSectionSecond").show();
        });
        $(document).on('click', '.addDnaCprBtn', function() {
            $(".DnaCprSectionFirst").show();
            $(".DnaCprSectionSecond").hide();
        });
        $(document).on('click', '.closeDnaCprBtn', function() {
            $(".DnaCprSectionFirst").hide();
            $(".DnaCprSectionSecond").show();
        });
        $(document).on('click', '.addConsentBtn', function() {
            $(".consentRecordSectionFirst").show();
            $(".consentRecordSectionSecond").hide();
        });
        $(document).on('click', '.closeConsentRecordBtn', function() {
            $(".consentRecordSectionFirst").hide();
            $(".consentRecordSectionSecond").show();
        });
    </script>

    <!-- for checkbox css -->
    <!-- <script>
        $(document).on('click','#selectAllAllert',function(){
            const selectAll = document.getElementById('selectAllAllert');
            const actionBox = document.getElementById('actionBox');
            const checks = document.querySelectorAll('.alertCheck');
            const closeBtn = document.getElementById('closeActionBox');

            function updateSytemAlert() {
                const count = document.querySelectorAll('.alertCheck:checked').length;
                actionBox.style.display = count > 0 ? 'block' : 'none';
                document.getElementById('selectedCheckCount').textContent = count + " selected";
            }

            selectAll.addEventListener('change', function() {
                checks.forEach(cb => cb.checked = this.checked);
                updateSytemAlert();
            });

            checks.forEach(cb => {
                cb.addEventListener('change', function() {
                    const total = checks.length;
                    const checked = document.querySelectorAll('.alertCheck:checked').length;
                    selectAll.checked = total === checked;
                    updateSytemAlert();
                });
            });
            closeBtn.addEventListener('click', function() {
                actionBox.style.display = 'none';

                checks.forEach(cb => cb.checked = false);
                selectAll.checked = false;
            });
        });
    </script> -->
    <script>
        var globalBillingInfo = {
            frequency: "{{ $clientDetails['billing_frequency'] ?? '' }}",
            rate: "{{ $clientDetails['billing_rate'] ?? '' }}"
        };
        var currentOnboardingDetails = [];

        $(document).on('click', '#onboardingForm', function() {
            onboardingDetailsList();
        });

        $(document).on('click', '.addFundingRow', function() {
            var newRow = `
                <div class="row funding-row mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control checkOnboardingDetail" name="name[]" placeholder="Funding Name">
                    </div>
                    <div class="col-md-3">
                        <select name="type[]" class="form-control checkOnboardingDetail">
                            <option value="1">Percentage</option>
                            <option value="2" selected>Amount</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control checkOnboardingDetail" name="vat[]" placeholder="Value">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm removeFundingRow"><i class="bx bx-minus"></i></button>
                    </div>
                </div>`;
            $('#fundingTypeContainer').append(newRow);
        });

        $(document).on('click', '.removeFundingRow', function() {
            // Check if it's the last row, maybe keep it but empty? OR just remove if there's at least one left.
            // For now, simple remove.
            $(this).closest('.funding-row').remove();
        });

        $(document).on('click', '.removeFundingRow', function() {
            $(this).closest('.funding-row').remove();
        });

        $(document).on('click', '.onboardingDetailsBtn', function() {
            var type = $(this).data('type');
            $("#onboardingDetails").modal('show');
            $("#onboardingDetailForm")[0].reset();
            $("#onboardingDetailModalTitle").text("Manage Billing Details");

            // Clear existing rows
            $('#fundingTypeContainer').empty();

            if (currentOnboardingDetails.length > 0) {
                currentOnboardingDetails.forEach((val, index) => {
                    var newRow = `
                        <div class="row funding-row mb-2">
                            <input type="hidden" name="detail_ids[]" value="${val.id}">
                            <div class="col-md-5">
                                <input type="text" class="form-control checkOnboardingDetail" name="name[]" placeholder="Funding Name" value="${val.name}">
                            </div>
                            <div class="col-md-3">
                                <select name="type[]" class="form-control checkOnboardingDetail">
                                    <option value="1" ${val.type == 1 ? 'selected' : ''}>Percentage</option>
                                    <option value="2" ${val.type == 2 ? 'selected' : ''}>Amount</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control checkOnboardingDetail" name="vat[]" placeholder="Value" value="${val.vat}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger btn-sm removeFundingRow"><i class="bx bx-minus"></i></button>
                            </div>
                        </div>`;
                    $('#fundingTypeContainer').append(newRow);
                });
            } else {
                // Add one empty row if no data exists
                var emptyRow = `
                    <div class="row funding-row mb-2">
                        <div class="col-md-5">
                            <input type="text" class="form-control checkOnboardingDetail" name="name[]" placeholder="Funding Name">
                        </div>
                        <div class="col-md-3">
                            <select name="type[]" class="form-control checkOnboardingDetail">
                                <option value="1">Percentage</option>
                                <option value="2" selected>Amount</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control checkOnboardingDetail" name="vat[]" placeholder="Value">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger btn-sm removeFundingRow"><i class="bx bx-minus"></i></button>
                        </div>
                    </div>`;
                $('#fundingTypeContainer').append(emptyRow);
            }

            // Always use global settings for frequency and rate
            var currentFreq = globalBillingInfo.frequency;
            var currentRate = globalBillingInfo.rate;

            if (currentFreq == 1 || currentFreq == '') {
                $("#weeklyfrequency").prop('checked', true);
                if (currentRate == '') currentRate = "{{ $home_details->weekly_allowance_service_users ?? '' }}";
            } else if (currentFreq == 2) {
                $("#monthlyfrequency").prop('checked', true);
                if (currentRate == '') currentRate = "{{ $home_details->monthly_allowance_service_users ?? '' }}";
            }
            $("#onboardingDetailFrequencyRate").val(currentRate);
        });

        $(document).on('change', '#onboardingDetailForm input[name="frequency"]', function() {
            var frequency = $(this).val();
            if (frequency == 1) { // Weekly
                $("#onboardingDetailFrequencyRate").val("{{ $home_details->weekly_allowance_service_users ?? '' }}");
            } else if (frequency == 2) { // Monthly
                $("#onboardingDetailFrequencyRate").val("{{ $home_details->monthly_allowance_service_users ?? '' }}");
            }
        });
        $(document).on('click', '#onboardingDetailSaveBtn', function() {
            var checkError = 0;
            $('.checkOnboardingDetail').each(function() {
                if ($(this).val() == '' || $(this).val() == undefined) {
                    $(this).css('border', '1px solid red').focus();
                    checkError = 1;
                    return false;
                } else {
                    $(this).css('border', '');
                    checkError = 0;
                }
            });
            if (checkError == 1) {
                return false;
            } else {
                var data = new FormData($("#onboardingDetailForm")[0]);
                data.append('client_id', client_id);
                // In edit mode, the detail_ids array needs to be present
                if ($("#onboardingDetail_id").val() != '') {
                    data.append('detail_ids[]', $("#onboardingDetail_id").val());
                }
                $.ajax({
                    type: "POST",
                    url: "{{url('roster/onboarding-detail-save')}}",
                    data: data,
                    async: false,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {
                        console.log(response);
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(response) == false) {
                                return false;
                            }
                        }
                        if (response.success === true) {
                            $("#onboardingDetails").modal('hide');
                            $("#onboardingDetailForm")[0].reset();
                            $('.ajax-alert-suc').show();
                            $('.msg').text(response.message);
                            onboardingDetailsList();
                            setTimeout(function() {
                                $(".notification-box").fadeOut();
                                $('.msg').text("");
                            }, 5000);
                        } else {
                            alert("Something went wrong");
                            return false;
                        }

                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + error);
                    }
                });
            }
        });

        function onboardingDetailsList() {
            $.ajax({
                type: "POST",
                url: "{{url('roster/onboarding-detail-list')}}",
                data: {
                    client_id: client_id,
                    _token: token
                },
                success: function(response) {
                    console.log(response);
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                    }
                    if (response.success === true) {
                        var db_data = response.data;
                        var billing = response.billing;
                        globalBillingInfo = billing; // Update global billing info
                        $("#onboardingDetailsListHtml").empty();
                        if (db_data.length > 0) {
                            db_data.forEach(val => {
                                var type = 'Percentage';
                                if (val.type == 2) {
                                    type = 'Amount';
                                }
                                var frequency = 'Weekly';
                                if (billing.frequency == 2) {
                                    frequency = 'Monthly';
                                }
                                var htmlData = `<tr>
                                                <td>${val.name}</td>
                                                <td>${type}</td>
                                                <td>${val.vat}</td>
                                            </tr>`;
                                $("#onboardingDetailsListHtml").append(htmlData);
                            });
                            currentOnboardingDetails = db_data; // Store globally
                        } else {
                            currentOnboardingDetails = [];
                            var noOnboardingData = `<tr><td colspan="5">
                                                        <div class="noData" style="text-align:center">
                                                        <div>
                                                            <p>No Data Found</p>
                                                        </div>
                                                    </div>
                                                    </td></tr>`
                            $("#onboardingDetailsListHtml").html(noOnboardingData);
                        }
                    } else {
                        alert("Something went wrong");
                        return false;
                    }

                },
                error: function(xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText;
                    alert('Error - ' + errorMessage + "\nMessage: " + error);
                }
            });
        }
        $(document).on('click', '.onboardingDetailDelete', function() {
            var onboardingId = $(this).data('id');
            var row = $(this).closest('tr');
            if (confirm("Are yous sure to delete it?")) {
                $.ajax({
                    type: "POST",
                    url: "{{url('roster/onboarding-detail-delete')}}",
                    data: {
                        id: onboardingId,
                        _token: token
                    },
                    success: function(response) {
                        console.log(response);
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(response) == false) {
                                return false;
                            }
                        }
                        if (response.success === true) {
                            row.remove();
                            var data_len = $("#onboardingDetailsListHtml tr").length;
                            if (data_len == 0) {
                                var noOnboardingData = `<tr><td colspan="5">
                                                        <div class="noData" style="text-align:center">
                                                        <div>
                                                            <p>No Data Found</p>
                                                        </div>
                                                    </div>
                                                    </td></tr>`
                                $("#onboardingDetailsListHtml").html(noOnboardingData);
                            }
                            $('.ajax-alert-suc').show();
                            $('.msg').text(response.message);
                            setTimeout(function() {
                                $(".notification-box").fadeOut();
                                $('.msg').text("");
                            }, 5000);
                        } else {
                            alert("Something went wrong");
                            return false;
                        }

                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText;
                        alert('Error - ' + errorMessage + "\nMessage: " + error);
                    }
                });
            }
        });

        function formatDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        function getExpenses() {
            $.ajax({
                type: "POST",
                url: "{{url('roster/client-expense-list')}}",
                data: {
                    service_user_id: "{{$clientDetails['id']}}",
                    _token: "{{csrf_token()}}"
                },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        if (response.data.length > 0) {
                            response.data.forEach(function(expense) {
                                html += `<tr>
                                    <td>${formatDate(expense.expense_date)}</td>
                                    <td>${expense.title}</td>
                                    <td>£${parseFloat(expense.amount).toFixed(2)}</td>
                                    <td>${expense.notes || ''}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info editExpense" data-id="${expense.id}" data-date="${expense.expense_date}" data-title="${expense.title}" data-amount="${expense.amount}" data-notes="${expense.notes || ''}">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteExpense" data-id="${expense.id}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                            });
                        } else {
                            html = '<tr><td colspan="5" class="text-center">No expenses found</td></tr>';
                        }
                        $('#clientExpensesListHtml').html(html);
                    }
                }
            });
        }

        $('#clientExpenseForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{url('roster/client-expense-save')}}",
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addExpenseModal').modal('hide');
                        $('#clientExpenseForm')[0].reset();
                        $('#expense_id').val('');
                        getExpenses();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        });

        $(document).on('click', '.editExpense', function() {
            var id = $(this).data('id');
            var date = $(this).data('date');
            var title = $(this).data('title');
            var amount = $(this).data('amount');
            var notes = $(this).data('notes');

            $('#expense_id').val(id);
            $('#expense_date').val(date);
            $('#expense_title').val(title);
            $('#expense_amount').val(amount);
            $('#expense_notes').val(notes);
            $('#addExpenseModal').modal('show');
            $('.modal-title').html('<i class="bx bx-edit"></i> Edit Expense');
        });

        $(document).on('click', '.deleteExpense', function() {
            if (confirm('Are you sure you want to delete this expense?')) {
                var id = $(this).data('id');
                $.ajax({
                    type: "POST",
                    url: "{{url('roster/client-expense-delete')}}",
                    data: {
                        id: id,
                        _token: "{{csrf_token()}}"
                    },
                    success: function(response) {
                        if (response.success) {
                            getExpenses();
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    }
                });
            }
        });

        // Reset modal title when adding new
        $(document).on('click', '[data-target="#addExpenseModal"]', function() {
            $('#clientExpenseForm')[0].reset();
            $('#expense_id').val('');
            $('.modal-title').html('<i class="bx bx-plus"></i> Add Expense');
        });
        //---------------- ONBOARDING FORM-----------------
            let STAGE_FORM_ID = null;
            let STAGE_ID = null;

            function loadOnboardingData() {
                $.ajax({
                    url: "{{ route('roster.clientonboarding.loadUserDetails') }}", // URL to send the request to
                    type: 'POST',
                    data: {
                        user_id: "{{ $clientDetails['id'] }}",
                        _token: "{{ @csrf_token() }}"
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        $("#activate_client_wrapper").addClass('d-none');
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        if (res.status) {
                            if (res.workflowData) {
                                HOME_ID = res.workflowData.home_id;
                                if (res.workflowData.form_percentage === 100) {
                                    $("#activate_client_wrapper").removeClass('d-none');
                                }



                                let userData = res.userData;

                                $("#manageModalClient .modal-title").html(`<i class="bx bx-user-check blueText blueText fs23"></i>Client
                                        Onboarding: ${userData.name}`)
                                let onboardingformprogresstext =
                                    `${res.workflowData.onboardingforms_count} of ${res.workflowData.getstages_count } stages completed`;
                                let onboardingformprogresspercentage =
                                    `${res.workflowData.form_percentage}%`;
                                $(".onboardingformprogresstext").html(onboardingformprogresstext);
                                $(".onboardingformprogresspercentage").html(
                                    onboardingformprogresspercentage);
                                $(".onboardingformprogressfill").css('width',
                                    onboardingformprogresspercentage)
                                $("#loadStagesData").html(`<div class="noData mt-2" id="noworkflowdata">
                                            <div>
                                                <i class="bx bx-cog"></i>
                                                <p class="mb-0">No Stages Found !!</p>
                                            </div>
                                        </div>`);
                                // return;
                                if (res.workflowStages.length > 0) {
                                    let html = '';
                                    $.each(res.workflowStages, function(key, val) {
                                        let stageformid = val.onboardingforms ? val
                                            .onboardingforms.id : "";
                                        let STATUS_VAL = stageformid ? 'Completed' :
                                            (val.required_stage == 1 ? 'Required' :
                                                "Pending");
                                        let HEADER_STATUS = stageformid ?
                                            '<span class="careBadg redbadges ms-2">Required</span>' :
                                            (val.required_stage == 1 ?
                                                '<span class="careBadg redbadges ms-2">Required</span>' :
                                                '<span class="borderBadg ms-2">Optional</span>'
                                            );

                                        html += `<div class="recordCard"><div class="rounded8 shadowp p24 mt20 recordBtn cursorPointer"
                                                                                        ${stageformid ? 'style="border: 1px solid #86efac;"':''} type="button">
                                                                                        <div class="flexBw recordBtn1">
                                                                                            <div>
                                                                                                <div class="dFlexGap mb-3 align-items-start">
                                                                                                    <div>
                                                                                                        <i class="bx ${stageformid?'bx-check-circle greenText':'bx-circle'} fs23"></i>
                                                                                                    </div>
                                                                                                    <div>
                                                                                                        <h6 class="h6Head">${val.order_no}. ${val.stage_name} ${HEADER_STATUS}
                                                                                                        </h6>
                                                                                                        <p class="fs13 textGray500 mb-0">${val.description}</p>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div>
                                                                                                <span class="careBadg ${stageformid?'darkGreenBadges':'darkMuteBadg'}">${STATUS_VAL}</span>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="recordContent" data-stageid="${val.id}" data-stageformid="${stageformid}">
                                                                                            <h6 class="fs13 font600 dynamic_form_title"></h6>
                                                                                            <form id="saveForms" class="mt-2 saveForms">
                                                                                                <div class="alert alert-danger d-none stage_error_msg"></div>
                                                                                                <div id="loadStagesFormData-${val.id}" class="loadStagesFormData">
                                                                                                    Loading...
                                                                                                </div>
                                                                                                <div class="row">
                                                                                                    <div class="col-md-12">
                                                                                                        <button class="bgBtn w100 mt-4 pgreenBtn submitBtn" type="button"
                                                                                                            id="submitBtn"><i class="bx bx-check-circle f18"></i>Save
                                                                                                            Form</button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </form>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>`;
                                    });
                                    $("#loadStagesData").html(html);
                                } else {
                                    $("#loadStagesData").html(`<div class="noData mt-2" id="noworkflowdata">
                                            <div>
                                                <i class="bx bx-cog"></i>
                                                <p class="mb-0">No Stages Found !!</p>
                                            </div>
                                        </div>`);
                                }
                            } else {
                                $("#loadStagesData").html(`<div class="noData mt-2" id="noworkflowdata">
                                            <div>
                                                <i class="bx bx-cog"></i>
                                                <p class="mb-0">No Onboarding Configured !!</p>
                                            </div>
                                        </div>`);
                            }
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        console.log('error');

                    }
                });
            }
            $(".activateClientBtn").click(function() {
                $.ajax({
                    url: "{{ route('roster.client_active_status') }}", // URL to send the request to
                    type: 'POST', // or 'POST'
                    data: {
                        id: "{{ $clientDetails['id'] }}",
                        status: 1,
                        _token: "<?= csrf_token() ?>"
                    }, // Data to send with the request
                    beforeSend: function() {},
                    success: function(res) {
                        if (typeof isAuthenticated === "function") {
                            if (isAuthenticated(res) == false) {
                                return false;
                            }
                        }
                        alertMsg('suc', res.message)
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        alertMsg('err', 'Status Not Updated');
                    }
                });
            });
            function alertMsg(selector = 'err', msg) {
                $('.ajax-alert-' + selector).find('.msg').text(
                    msg);
                $('.ajax-alert-' + selector).show();

                setTimeout(function() {
                    $(".ajax-alert-" + selector).fadeOut()
                }, 5000);
            }
            $(".recordSec").on("click", ".recordBtn1", function() {

                let card = $(this).closest(".recordCard");


                let content = card.find(".recordContent");

                if (!content.length) return;

                // close all
                $(".recordSec .recordContent").each(function() {
                    // console.log(this);
                    $(this).hide();
                    $(this).removeClass('activeForm');
                });
                $(".loadStagesFormData").html('');

                // open current
                STAGE_ID = content.attr('data-stageid');
                STAGE_FORM_ID = content.attr('data-stageformid');
                content.addClass('activeForm');
                viewdatawithvalueFormios()
                content.show();
            });
            function viewdatawithvalueFormios() {
                var token = "<?= csrf_token() ?>";
                var settings = {
                    "url": "{{ route('roster.staffonboarding.loadforms') }}",
                    "method": "POST",
                    "data": {
                        stage_id: STAGE_ID,
                        stage_form_id: STAGE_FORM_ID,
                        _token: token
                    },
                    //dataType: "json",
                };
                // $.ajax(settings).done(function(response) {
                //     if (typeof isAuthenticated === "function") {
                //         if (isAuthenticated(response) == false) {
                //             return false;
                //         }
                //     }
                //     $(".dynamic_form_title").html(response.title)
                //     Formio.createForm(document.getElementById('loadStagesFormData-' + STAGE_ID), {
                //         components: JSON.parse(response.pattern)
                //     }, {
                //         readOnly: false
                //     }).then(function(form) {
                //         if (response.pattern_value) {
                //             form.submission = {
                //                 data: JSON.parse(response.pattern_value)
                //             }
                //         }
                //     });
                // });
                $.ajax(settings).done(function(response) {

                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(response) == false) {
                            return false;
                        }
                    }

                    $(".dynamic_form_title").html(response.title);
                    console.log(JSON.stringify(response.pattern));
                    let parsedPattern = JSON.parse(response.pattern);

                    let formSchema = {};

                    // OLD FORMIO FORMAT
                    if (Array.isArray(parsedPattern)) {

                        formSchema = {
                            components: parsedPattern
                        };

                    } else {

                        // NEW CUSTOM FORMAT
                        formSchema = convertToFormioSchema(parsedPattern);
                    }

                    Formio.createForm(
                        document.getElementById('loadStagesFormData-' + STAGE_ID),
                        formSchema,
                        {
                            readOnly: false
                        }
                    ).then(function(form) {
                        currentForm = form;
                        if (response.pattern_value) {
                            let parsedData = JSON.parse(response.pattern_value);

                            if (typeof parsedData === "string") {
                                parsedData = JSON.parse(parsedData);
                            }

                            form.submission = {
                                data: parsedData
                            };
                        }
                    });

                });
            }
            function convertToFormioSchema(data) {

                let components = [];

                data.sections.forEach((section, sectionIndex) => {

                    let sectionComponents = [];

                    section.fields.forEach(field => {

                        let component = {
                            label: field.label || '',
                            key: field.id || '',
                            input: true,
                            tableView: true,
                            validate: {
                                required: field.required || false
                            },
                            placeholder: field.hint || ''
                        };

                        switch(field.type) {

                            // TEXT
                            case 'text':
                            case 'tel':
                            case 'email':

                                component.type = 'textfield';
                                break;

                            // TEXTAREA
                            case 'textarea':

                                component.type = 'textarea';
                                break;

                            // DATE
                            case 'date':

                                component.type = 'datetime';
                                component.enableDate = true;
                                component.enableTime = false;
                                component.format = 'yyyy-MM-dd';

                                break;

                            // RADIO
                            case 'radio':

                                component.type = 'radio';
                                component.values = field.options.map(opt => ({
                                    label: opt,
                                    value: opt
                                }));

                                break;

                            // CHECKBOX
                            case 'checkbox':

                                // multiple checkbox options
                                if (field.options && field.options.length) {

                                    component.type = 'selectboxes';

                                    component.values = field.options.map(opt => ({
                                        label: opt,
                                        value: opt
                                    }));

                                } else {

                                    component.type = 'checkbox';
                                }

                                break;

                            // SIGNATURE
                            case 'signature':

                                component.type = 'signature';

                                break;

                            // INFO / HTML CONTENT
                            case 'info':

                                component.type = 'content';
                                component.html = `
                                    <div class="alert alert-info">
                                        ${field.content || ''}
                                    </div>
                                `;

                                delete component.input;

                                break;

                            // TABLE
                            case 'table':

                                component.type = 'datagrid';

                                component.components = field.columns.map(col => ({
                                    label: col,
                                    key: col.toLowerCase()
                                            .replace(/\s+/g, '_')
                                            .replace(/[^\w]/g, ''),
                                    type: 'textfield',
                                    input: true
                                }));

                                break;

                            default:

                                component.type = 'textfield';
                        }

                        sectionComponents.push(component);
                    });

                    components.push({
                        title: section.title,
                        type: 'panel',
                        key: 'section_' + sectionIndex,
                        input: false,
                        collapsible: false,
                        components: sectionComponents
                    });
                });

                return {
                    display: 'form',
                    components: components
                };
            }
//             function convertToFormioSchema(customSchema) {
//     let components = [];

//     for (const [key, field] of Object.entries(customSchema)) {

//         let component = {
//             key: key,
//             label: field.label,
//             type: field.fieldType,
//             input: true,
//             tableView: true,
//             validate: {
//                 required: field.isRequired ? true : false
//             },
//         };

//         // TEXT FIELDS
//         if (field.fieldType === "text" || field.fieldType === "email") {
//             component.type = "textfield";
//             component.as = "default";
//         }

//         // TEXTAREA
//         if (field.fieldType === "textarea") {
//             component.type = "textarea";
//             component.as = "default";
//         }

//         // NUMBER
//         if (field.fieldType === "number") {
//             component.type = "number";
//             component.decimal = true;
//             component.as = "default";
//         }

//         // SELECT
//         if (field.fieldType === "select") {
//             component.type = "select";
//             component.data = {
//                 values: field.choices.map(c => ({
//                     label: c.text,
//                     value: c.value
//                 }))
//             };
//             component.as = "default";
//         }

//         //RADIO
//         if (field.fieldType === "radio") {
//             component.type = "radio";
//             component.data = {
//                 values: field.choices.map(c => ({
//                     label: c.text,
//                     value: c.value
//                 }))
//             };
//         }

//         //CHECKBOX
//         if (field.fieldType === "checkbox") {
//             component.type = "checkbox";
//             component.as = "default";
//         }

//         //DATE
//         if (field.fieldType === "date") {
//             component.type = "datetime";
//             component.datePicker.disableTime = true;
//             component.format = "yyyy-MM-dd";
//             component.as = "default";
//         }

//         // FILE
//         if (field.fieldType === "file") {
//             component.type = "file";
//             component.as = "default";
//         }

//         components.push(component);
//     }

//     return { components };
// }
            // $(document).on('click', ".submitBtn", function() {

            //     let form = $(this).closest("form");

            //     if (!form.length) return false;

            //     let isValid = true;

            //     let fields = form.find('[aria-required="true"]');

            //     console.log("Total required fields:", fields.length);
            //     let errHtml = '';
            //     fields.each(function() {

            //         let field = $(this);
            //         let val = field.val();

            //         if (!val || val.trim() === "") {
            //             errHtml += 'Please fill all required fields';
            //             field.addClass("error");
            //             isValid = false;
            //         } else {
            //             field.removeClass("error");
            //         }
            //     });
            //     if (!isValid) {
            //         form.find('.stage_error_msg')
            //             .html('Please fill all required fields')
            //             .removeClass('d-none');
            //         // alert("Please fill all required fields");
            //         return false;
            //     }
            //     let USER_ID = "{{ $clientDetails['id'] }}";
            //     let formData = new FormData(form[0]);
            //     console.log(`STAGE_ID : ${STAGE_ID}`,
            //         `STAGE_FORM_ID: ${STAGE_FORM_ID}  , USER_ID: ${USER_ID}`);
            //     // return;
            //     // extra params append karo
            //     formData.append('_token', "<?= csrf_token() ?>");
            //     formData.append('stage_id', STAGE_ID);
            //     formData.append('stage_form_id', STAGE_FORM_ID);
            //     formData.append('user_id', USER_ID);
            //     let fileInput = document.querySelector('input[type=file]');
            //     if (fileInput.files.length > 0) {
            //         formData.append('file', fileInput.files[0]);
            //     }
            //     $.ajax({
            //         url: "{{ route('roster.staffonboarding.saveforms') }}",
            //         type: 'POST',
            //         data: formData,

            //         // 👇 IMPORTANT for FormData
            //         processData: false,
            //         contentType: false,

            //         beforeSend: function() {},

            //         success: function(res) {
            //             if (typeof isAuthenticated === "function") {
            //                 if (isAuthenticated(res) == false) {
            //                     return false;
            //                 }
            //             }
            //             if (res.success) {
            //                 STAGE_FORM_ID = res.data.id;
            //                 alertMsg('suc', res.message);

            //                 // optional reload
            //                 location.reload();
            //             }
            //         },

            //         error: function() {
            //             alertMsg('err', 'Form Not Saved');
            //         }
            //     });

            // });
            let currentForm = null;
            $(document).on('click', ".submitBtn", function() {

                if (!currentForm) {
                    alert('Form not loaded');
                    return false;
                }

                // FORMIO VALIDATION
                let isValid = currentForm.checkValidity(
                    currentForm.submission.data,
                    true
                );

                console.log("VALID ?", isValid);

                console.log(currentForm.errors);

                if (!isValid) {

                    let messages = currentForm.errors.map(err => err.message);

                    $('.stage_error_msg')
                        .html(messages.join('<br>'))
                        .removeClass('d-none');

                    return false;
                }

                let USER_ID = "{{ $clientDetails['id'] }}";

                let formData = new FormData();

                formData.append(
                    'pattern_value',
                    JSON.stringify(currentForm.submission.data)
                );

                formData.append('_token', "<?= csrf_token() ?>");
                formData.append('stage_id', STAGE_ID);
                formData.append('stage_form_id', STAGE_FORM_ID);
                formData.append('user_id', USER_ID);
                // console.log("FORMDATA", formData);
                // return false;
                $.ajax({

                    url: "{{ route('roster.staffonboarding.saveforms') }}",

                    type: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    success: function(res) {
                        console.log(res);
                        // return;
                        if (res.success) {

                            STAGE_FORM_ID = res.data.id;

                            alertMsg('suc', res.message);

                            location.reload();
                        }
                    },

                    error: function() {

                        alertMsg('err', 'Form Not Saved');
                    }
                });

            });

        // Body Map — header button opens the modal in aggregated read-only
        // mode (every active injury for this client, across all risks).
        $(document).on('click', '.openBodyMapProfile', function() {
            var suId = $(this).data('service-user-id');
            $('input[name=bm_aggregated_su_id]').val(suId);
            $('input[name=su_rsk_id]').val('');
            $('#bodyMapModal').modal('show');
        });

        // Body Map — per-risk body map button on each risk assessment card.
        // Opens the modal in risk mode so carers can add/remove injuries
        // against that specific risk.
        $(document).on('click', '.realRiskBodyMapBtn', function() {
            var riskId = $(this).data('su-risk-id');
            $('input[name=bm_aggregated_su_id]').val('');
            $('input[name=su_rsk_id]').val(riskId);
            $('#bodyMapModal').modal('show');
        });
    </script>
    <!-- AI Generate Care Plan Modal -->
    <div class="modal fade" id="generateCarePlanModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class='bx bx-sparkles'></i> Generate AI Care Plan</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>Assessment Type</strong></label>
                        <select class="form-control" id="cpAssessmentType">
                            <option value="">Select...</option>
                            <option value="initial">Initial Assessment</option>
                            <option value="review">Review</option>
                            <option value="reassessment">Reassessment</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label><strong>Care Setting</strong></label>
                        <select class="form-control" id="cpCareSetting">
                            <option value="">Select...</option>
                            <option value="residential">Residential</option>
                            <option value="nursing">Nursing</option>
                            <option value="domiciliary">Domiciliary</option>
                        </select>
                    </div>
                    <div id="generateCPProgress" style="display:none; margin-top:15px; padding:12px; background:#f0f4ff; border-radius:8px; text-align:center; color:#555;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn borderBtn" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn allBtnUseColor" id="generateCPBtn" onclick="generateCarePlan()"><i class='bx bx-sparkles'></i> Generate</button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Review Generated Care Plan Modal -->
    <div class="modal fade" id="reviewCarePlanModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="closeCarePlanModal()" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class='bx bx-check-shield'></i> Review Generated Care Plan</h4>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div id="reviewPlanInfo" style="margin-bottom:10px;"></div>
                    <div id="reviewPlanContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn borderBtn" onclick="closeCarePlanModal()">Discard</button>
                    <button type="button" class="btn borderBtn" onclick="saveCarePlanAsDraft()"><i class='bx bx-save'></i> Save as Draft</button>
                    <button type="button" class="btn allBtnUseColor" onclick="saveCarePlanAsActive()"><i class='bx bx-check-circle'></i> Approve & Activate</button>
                </div>
            </div>
        </div>
    </div>

    <!-- AI View Care Plan Modal -->
    <div class="modal fade" id="viewCarePlanModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" onclick="closeCarePlanModal()" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class='bx bx-heart'></i> Care Plan</h4>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div id="viewPlanContent"></div>
                </div>
                <div class="modal-footer" id="viewPlanFooter">
                    <button type="button" class="btn borderBtn" onclick="closeCarePlanModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('frontEnd.serviceUserManagement.elements.risk_change.body_map_popup')

    @endsection
</main>