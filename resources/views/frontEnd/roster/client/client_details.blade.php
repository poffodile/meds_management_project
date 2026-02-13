<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title','Client')
@section('content')

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container-fluid">

        <div class="topHeaderCont">
            <div>
                <h1>{{$clientDetails['name']}}</h1>
                <p class="header-subtitle"><span>{{$status}}</span> local authority</p>
            </div>
            <div class="header-actions addnewicons">
                <button class="btn borderBtn editClient" data-toggle="modal" data-target="#addServiceUserModal" data-child_id="{{$clientDetails['id']}}"><i class='bx  bx-edit'></i> Edit Client</button>
                <button class="btn borderBtn"><i class='bx  bx-arrow-in-up-square-half'></i> Import Documents</button>
                <button class="btn allBtnUseColor"><i class='bx  bx-sparkles'></i> Generate Care Plan</button>

            </div>
        </div>
        <div class="calendarTabs leaveRequesttabs employeeDetailsTabs  m-t-20">
            <div class="clientOverTabs">
                <div class="tabs p-1 ">
                    <button class="tab active" data-tab="clientDetailsTab"> Details </button>
                    <button class="tab" data-tab="clientOnboardingTab"> Onboarding </button>
                    <button class="tab" data-tab="clientCareTasksTab"> Care Tasks </button>
                    <button class="tab" data-tab="clientAlertsTab"> Alerts </button>
                    <button class="tab" data-tab="clientAIInsightsTab"> AI Insights </button>
                    <button class="tab" data-tab="clientCarePlanTab"> Care Plan </button>
                    <button class="tab" data-tab="clientRiskAssessmentsTab"> Risk Assessments </button>
                    <button class="tab" data-tab="clientMedicationTab"> Medication </button>
                    <button class="tab" data-tab="clientPEEPTab"> PEEP </button>
                    <button class="tab" data-tab="clientRepositioningTab"> Repositioning </button>
                    <button class="tab" data-tab="clientBehaviorTab"> Behavior </button>
                    <button class="tab" data-tab="clientMentalCapacityTab"> Mental Capacity </button>
                    <button class="tab" data-tab="clientDoLSTab"> DoLS </button>
                    <button class="tab" data-tab="clientDNACPRTab"> DNACPR </button>
                    <button class="tab" data-tab="clientSafeguardingTab"> Safeguarding </button>
                    <button class="tab" data-tab="clientConsentTab"> Consent </button>
                    <button class="tab" data-tab="clientEmergencyTab"> Emergency </button>
                    <button class="tab" data-tab="clientDocumentsTab"> Documents </button>
                    <button class="tab" data-tab="clientProgressReportTab"> Progress Report </button>
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

                            <div class="d-flex justify-content-between">
                                <h5>Client Onboarding Progress
                                </h5>
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
                            </div>
                            <div class="onboardingBox boardingToggle p-4 mt-4">
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
                                <button class="allBtnUseColor" type="button" onclick="window.location.href='{{url('roster/care-task-add')}}'"> <i class='bx  bx-plus'></i> Add Task</button>
                            </div>
                        </header>
                        <div class="p-20 p-b-0">
                            <div class="rota_dashboard-cards simpleCard">
                                <div class="rota_dash-card bg-blue-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Total Tasks</p>
                                        <h2 class="rota_count">37</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-red-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Critical Priority</p>
                                        <h2 class="rota_count">36</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-orange-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">High Priority</p>
                                        <h2 class="rota_count orangeText">0</h2>
                                    </div>
                                </div>

                                <div class="rota_dash-card bg-purple-50">
                                    <div class="rota_dash-left">
                                        <p class="rota_title">Two Staff Required</p>
                                        <h2 class="rota_count">1</h2>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-20 p-t-0">
                            <div class="caretasknameandnumber m-b-10">
                                <span>2</span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard redborderleft mb-0">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Emotional support session with counselor</a></div>
                                                    <!-- <div class="role">part time</div> -->
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
                                        <div class="actions">
                                            <button class="edit" data-id="120"> <i class="fa-regular fa-pen-to-square"></i> Edit </button>
                                            <button class="delete" data-id="120"> <i class="fa-regular fa-trash-can"></i> </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="profile-card careTasksCard blueborderleft mb-0">
                                        <div class="card-header">
                                            <div class="user">
                                                <div class="info">
                                                    <div class="name"><a href="#!">Daily emotional support check-in</a></div>
                                                    <!-- <div class="role">part time</div> -->
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
                        </div>
                        <div class="p-20 p-t-0">
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
                                                    <!-- <div class="role">part time</div> -->
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
                                                    <!-- <div class="role">part time</div> -->
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
                                                    <!-- <div class="role">part time</div> -->
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
                        </div>

                    </div>
                </div>
                <div class="content" id="clientAlertsTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0">
                        <header class="panel-heading headingCapitilize clntalertheader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain radIconClr"><i class='bx  bx-alert-triangle'></i></i> Client Alerts </div>
                                <p>Manage important alerts and warnings for this client</p>
                            </div>

                            <div class="actions mt-0">
                                <button class="btn addAssessmentBtn addalertClientDetailsBtn"> <i class='bx  bx-plus'></i> Add alert</button>
                            </div>
                        </header>

                        <div class="p-20">
                            <div class="clientFilterform addalertClientDetailsform" style="border: 2px solid #fdabab; background: #fef2f2;">

                                <div class="createNewAlert"><i class='bx  bx-alert-triangle'></i></i> Create New Alert </div>

                                <form action="" class="addAlertForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Alert Type *</label>
                                                <select class="form-control">
                                                    <option>Single Day</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Severity *</label>
                                                <select class="form-control">
                                                    <option>Single Day</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Alert Title *</label>
                                                <input type="text" class="form-control" name="" placeholder="e.g., High Fall Risk - Use Walking Frame">
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Detailed Description *</label>
                                                <textarea name="short_description" required="" class="form-control" rows="3" cols="20" placeholder="Provide detailed information about this alert..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Action Required (Optional)</label>
                                                <textarea name="short_description" required="" class="form-control" rows="2" cols="20" placeholder="Specific actions staff should take..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Expiry Date (Optional)</label>
                                                <input type="date" class="form-control" name="" placeholder="e.g., High Fall Risk - Use Walking Frame">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="requiresStaff checkbox">
                                                <label>
                                                    <input type="checkbox"> Requires Staff Acknowledgment
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Display Alert On (select sections):</label>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked style="pointer-events: none;"> All </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled> medication </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled> dashboard </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled> visits </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled> care plan </label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <label><input type="checkbox" checked disabled> schedule </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button class="btn addAssessmentBtn " type="submit"> Create Alert </button>
                                            <button class="btn whiteBgBtncolor" type="submit"> Cancel </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="clientFilterform">
                                <div class="filtersSorting">
                                    <i class='bx bx-filter'></i> Filters & Sorting
                                </div>
                                <form action="">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Severity</label>
                                                <select class="form-control">
                                                    <option>All Severities</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control">
                                                    <option>All Status</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Type</label>
                                                <select class="form-control">
                                                    <option>All Type</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Sort By</label>
                                                <select class="form-control">
                                                    <option>Severity</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>




                        <div class="leavebanktabCont">
                            <i class='bx  bx-alert-triangle'></i>
                            <p>No alerts match the selected filters</p>
                        </div>
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
                <div class="content" id="clientRiskAssessmentsTab">
                    <div class="carePlanTabCont riskAssessmentSectionFirst" style="">
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
                <div class="content" id="clientMedicationTab">
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0 medicationSectionFirst" style="display:;">
                        <header class="panel-heading headingCapitilize medicationManagementHeader">
                            <div class="clientHeadung">
                                <div class="onlyheadingmain purpleiconclr"><i class='bx  bx-link'></i> Medication Management </div>
                                <p>Track medication administration and MAR sheets</p>
                            </div>
                        </header>

                        <div class="p-20">
                            <div class="medicationManagement" id="availabilityTab">
                                <div class="availabilityTabs">
                                    <div class="availabilityTabs__nav">
                                        <button class="availabilityTabs__tab borderBtn active" data-target="MARSheetsPanel">MAR Sheets <span>(2)</span> </button>
                                        <button class="availabilityTabs__tab borderBtn" data-target="medicationLogsPanel">Medication Logs <span>(6)</span></button>
                                    </div>
                                    <div class="availabilityTabs__content">
                                        <div class="availabilityTabs__panel active" id="MARSheetsPanel">
                                            <div class="carePlanTabCont" style="">
                                                <div class="workHoursHeader">
                                                    <div class="title"> MAR Sheets</div>
                                                    <div class="actions">
                                                        <button class="purpleBgBtn"> <i class='bx  bx-plus'></i> Add MAR Sheet</button>
                                                    </div>
                                                </div>

                                                <div class="carePlanWrapper">
                                                    <div class="planCard borderleftPurple">
                                                        <div class="planTop">
                                                            <div class="planTitle">
                                                                Norethisterone
                                                            </div>
                                                            <div class="planActions">
                                                                <button class="marSheetDetails"><i class="bx  bx-eye"></i> </button>
                                                                <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                            </div>
                                                        </div>
                                                        <div class="planMeta">
                                                            <div><strong>Dose: </strong> N/A</div>
                                                            <div><strong>Route: </strong> oral</div>
                                                            <div><strong>Frequency: </strong> N/A</div>
                                                            <div><strong>Period: </strong> December 2025</div>
                                                        </div>
                                                        <div class="planFooter">
                                                            <span><strong> Reason: </strong> Taken for one week during August to delay period whilst on holiday.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="carePlanWrapper">
                                                    <div class="planCard borderleftPurple">
                                                        <div class="planTop">
                                                            <div class="planTitle">
                                                                Zolmitriptan
                                                                <span class="roundTag yellow">PRN</span>
                                                            </div>
                                                            <div class="planActions">
                                                                <button class="marSheetDetails"><i class="bx  bx-eye"></i> </button>
                                                                <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                            </div>
                                                        </div>
                                                        <div class="planMeta">
                                                            <div><strong>Dose: </strong> N/A</div>
                                                            <div><strong>Route: </strong> oral</div>
                                                            <div><strong>Frequency: </strong> N/A</div>
                                                            <div><strong>Period: </strong> December 2025</div>
                                                        </div>
                                                        <div class="planFooter">
                                                            <span><strong> Reason: </strong> One to be taken at the onset of a migraine.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="availabilityTabs__panel" id="medicationLogsPanel">
                                            <div class="carePlanTabCont" style="">
                                                <div class="workHoursHeader">
                                                    <div class="title"> Medication Logs</div>
                                                    <div class="actions">
                                                        <button class="purpleBgBtn" id="logMedicationBtn"><i class='bx bx-plus'></i> Log Medication</button>
                                                    </div>
                                                </div>

                                                <div class="">
                                                    <div class="clientFilterform greanHeaderbgClr medicationLogsForm " style="display:none">

                                                        <div class="createNewAlert"><i class='bx  bx-link'></i> Add Medication Administration Log </div>

                                                        <form action="" class="addAlertForm">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Medication Name *</label>
                                                                        <input type="text" class="form-control" name="" placeholder="e.g., Paracetamol">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Dosage *</label>
                                                                        <input type="text" class="form-control" name="" placeholder="e.g., 500mg">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Frequency</label>
                                                                        <input type="text" class="form-control" name="" placeholder="e.g., Twice daily">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Administration Time *</label>
                                                                        <input type="date" class="form-control" name="" placeholder="">
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
                                                                        <label>Witnessed By (if required)</label>
                                                                        <input type="text" class="form-control" name="" placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label> Notes *</label>
                                                                        <textarea name="" class="form-control" rows="3" cols="20" placeholder="Any additional notes about administration"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label> Side Effects Observed</label>
                                                                        <textarea name="" class="form-control" rows="3" cols="20" placeholder="Any observed side effects or reactions"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="header-actions">
                                                                        <button class="btn allbuttonDarkClr " type="submit">Save Log </button>
                                                                        <button class="btn borderBtn" type="submit"> Cancel </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                    <div class="carePlanWrapper m-t-15">
                                                        <div class="planCard borderleftPurple">
                                                            <div class="planTop">
                                                                <div class="planTitle">
                                                                    DFVDF <span class="roundTag yellow"> missed</span>
                                                                </div>
                                                                <div class="planActions">
                                                                    <button class="danger"><i class='bx  bx-info-circle'></i> </button>
                                                                </div>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Dosage:<strong> DSFDS </strong> </span>
                                                            </div>
                                                            <div class="planFooter">
                                                                <span>Frequency: DSF </span>
                                                            </div>


                                                            <div class="planMeta">
                                                                <div class="aligniconMedication"><i class='bx  bx-clock-4'></i> Jan 6, 2026 at 18:28</div>
                                                                <div class="aligniconMedication"><i class='bx  bx-user'></i> By: Unknown Staff</div>
                                                            </div>
                                                            <div class="witnessedBy">
                                                                <span><strong>Witnessed by:</strong> DSFF </span>
                                                            </div>

                                                            <div class="witnessedBy witnessedByNotes">
                                                                <span><strong>Notes:</strong> DSFDSFSAFSDF </span>
                                                            </div>

                                                            <div class="witnessedBy witnessedBySideEffects yellow">
                                                                <strong class="aligniconMedication"><i class='bx  bx-info-circle'></i> Side Effects:</strong>
                                                                <p>SDFSDF</p>
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
                    </div>
                    <div class="careTaskstbbg sectionWhiteBgAllUse p-0 medicationSectionSecond" style="display:none">
                        <div class="medicationManagement" id="availabilityTab">
                            <div class="availabilityTabs">
                                <header class="panel-heading headingCapitilize medicationManagementHeader">
                                    <div class="clientHeadung">
                                        <button class="btn borderBtn backBtn" id="medicationBackBtn"><i class="bx  bx-arrow-left-stroke"></i> Back to MAR Sheets </button>
                                        <div class="onlyheadingmain purpleiconclr m-t-10"><i class='bx  bx-link'></i> Medication Management </div>
                                        <p>Track medication administration and MAR sheets</p>
                                    </div>

                                    <div class="availabilityTabs__nav m-b-0">
                                        <button class="availabilityTabs__tab borderBtn active" data-target="MARSheetsPanel"> <i class='bx  bx-list-ul'></i> List </button>
                                        <button class="availabilityTabs__tab borderBtn" data-target="medicationLogsPanel"> <i class='bx  bx-table-cells'></i> Table</button>
                                    </div>
                                </header>

                                <div class="availabilityTabs__content p-20">
                                    <div class="availabilityTabs__panel active" id="MARSheetsPanel">
                                        <div class="carePlanTabCont" style="">
                                            <div class="space-y-4">
                                                <div class="borderB">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">month year</h4>
                                                    <div class="text-gray-900">
                                                        <p>December 2025</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">medication name</h4>
                                                    <div class="text-gray-900">
                                                        <p>Norethisterone</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">dose</h4>
                                                    <div class="text-gray-900">
                                                        <p>N/A</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">route</h4>
                                                    <div class="text-gray-900">
                                                        <p>oral</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">frequency</h4>
                                                    <div class="text-gray-900">
                                                        <p>N/A</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">time slots</h4>
                                                    <div class="text-gray-900">
                                                        <p>N/A</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">reason for medication</h4>
                                                    <div class="text-gray-900">
                                                        <p>Taken for one week during August to delay period whilst on holiday.</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">start date</h4>
                                                    <div class="text-gray-900">
                                                        <p>August</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">administration records</h4>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">created date</h4>
                                                    <div class="text-gray-900">
                                                        <p>December 16th, 2025</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">updated date</h4>
                                                    <div class="text-gray-900">
                                                        <p>December 16th, 2025</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">created by id</h4>
                                                    <div class="text-gray-900">
                                                        <p>6909e61b89f26ef7fa768434</p>
                                                    </div>
                                                </div>
                                                <div class="borderB pt-3 pb-3">
                                                    <h4 class="fontSemibold textSm text-gray-600 uppercase mb-3">created by</h4>
                                                    <div class="text-gray-900">
                                                        <p>p.holt@omegalife.uk</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="availabilityTabs__panel" id="medicationLogsPanel">
                                        <div class="carePlanTabCont" style="">
                                            <div class="medicationSheet">
                                                <!-- Patient Header -->
                                                <div class="patientInfo">
                                                    <div>
                                                        <p><strong>Name:</strong> Logan Jones</p>
                                                        <p><strong>Date of Birth:</strong> 29.10.2009</p>
                                                        <p><strong>Address:</strong> N/A</p>
                                                    </div>
                                                    <div>
                                                        <p><strong>Period:</strong> December 2025</p>
                                                        <p><strong>Prescriber:</strong> N/A</p>
                                                        <p><strong>Pharmacy:</strong> N/A</p>
                                                    </div>
                                                </div>

                                                <!-- Medication Card -->
                                                <div class="medicationCard">
                                                    <div class="medGrid">
                                                        <div>
                                                            <span class="label">Medication:</span>
                                                            <p class="medName">Norethisterone</p>
                                                        </div>
                                                        <div>
                                                            <span class="label">Dose:</span>
                                                            <p>N/A</p>
                                                        </div>
                                                        <div>
                                                            <span class="label">Route:</span>
                                                            <p>Oral</p>
                                                        </div>
                                                        <div>
                                                            <span class="label">Frequency:</span>
                                                            <p>N/A</p>
                                                        </div>
                                                    </div>

                                                    <div class="reasonBox">
                                                        <strong>Reason:</strong>
                                                        Taken for one week during August to delay period whilst on holiday.
                                                    </div>
                                                </div>

                                                <!-- Legend -->
                                                <div class="legend">
                                                    <span class="tag a">A</span> Administered
                                                    <span class="tag r">R</span> Refused
                                                    <span class="tag s">S</span> Self-administered
                                                    <span class="tag h">H</span> Hospital
                                                    <span class="tag nd">ND</span> Not in home
                                                </div>

                                                <!-- Week 1 -->
                                                <div class="weekBlock">
                                                    <h4>Week 1</h4>
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>Time</th>
                                                                <th>Mon<br>01</th>
                                                                <th>Tue<br>02</th>
                                                                <th>Wed<br>03</th>
                                                                <th>Thu<br>04</th>
                                                                <th>Fri<br>05</th>
                                                                <th>Sat<br>06</th>
                                                                <th>Sun<br>07</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>N/A</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Week 2 -->
                                                <div class="weekBlock">
                                                    <h4>Week 2</h4>
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>Time</th>
                                                                <th>Mon<br>08</th>
                                                                <th>Tue<br>09</th>
                                                                <th>Wed<br>10</th>
                                                                <th>Thu<br>11</th>
                                                                <th>Fri<br>12</th>
                                                                <th>Sat<br>13</th>
                                                                <th>Sun<br>14</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>N/A</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <!-- Week 3 -->
                                                <div class="weekBlock">
                                                    <h4>Week 3</h4>
                                                    <table>
                                                        <thead>
                                                            <tr>
                                                                <th>Time</th>
                                                                <th>Mon<br>08</th>
                                                                <th>Tue<br>09</th>
                                                                <th>Wed<br>10</th>
                                                                <th>Thu<br>11</th>
                                                                <th>Fri<br>12</th>
                                                                <th>Sat<br>13</th>
                                                                <th>Sun<br>14</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>N/A</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
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
                                <form>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>DoLS Status *</label>
                                            <select class="form-control">
                                                <option>Screening Required</option>
                                                <option>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Authorisation Type</label>
                                            <select class="form-control">
                                                <option>Standard</option>
                                                <option>Part Time</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Referral Date </label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Authorisation Start Date</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>

                                        <div class="col-md-6  m-t-10">
                                            <label>Authorisation End Date</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Review Date</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Supervisory Body</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Case Reference</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Best Interests Assessor</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Mental Health Assessor</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-12  m-t-10">
                                            <label>Reason for DoLS</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div class="DoLSCheckList">
                                                <label><input type="checkbox"> IMCA Appointed</label>
                                                <label><input type="checkbox"> Mental Capacity Assessment Completed</label>
                                                <label><input type="checkbox"> Appeal Rights Explained</label>
                                                <label><input type="checkbox"> Care Plan Updated</label>
                                                <label><input type="checkbox"> Family/Next of Kin Notified</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12  m-t-10">
                                            <label>Reason for DoLS</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                        </div>
                                        <div class="col-md-12  m-t-10">
                                            <div class="header-actions addnewicons">
                                                <button class="btn allbuttonDarkClr"> Save DoLS Record</button>
                                                <button class="btn borderBtn" id="closeDolsformBtn"> Cancel</button>
                                            </div>
                                            <!-- <div class="actions mt-0">
                                                <button class="btn allbuttonDarkClr "> Save DoLS Record </button>
                                                <button class="btn borderBtn"> Cancel</button>
                                            </div> -->
                                        </div>
                                    </div>






                                </form>
                            </div>

                            <div class="carePlanWrapper dolsSectionSecond">
                                <div class="planCard borderleftPurple">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            <span class="roundTag yellow">SCREENING REQUIRED</span>
                                            <span class="inactive roundTag">urgent authorisation</span>
                                        </div>
                                        <div class="planActions">
                                            <button class="addDolsRecordBtn" data-formType="edit"><i class='bx  bx-edit'></i> </button>
                                            <!-- <button class="danger"><i class="bx  bx-trash"></i> </button> -->
                                        </div>
                                    </div>

                                    <div class="planMeta">
                                        <div><strong>Referral Date: </strong> January 5th, 2026</div>
                                        <div><strong>Start Date: </strong> January 14th, 2026</div>
                                    </div>
                                    <div class="planMeta">
                                        <div><strong>End Date: </strong> January 14th, 2026</div>
                                        <div><strong>Supervisory Body: </strong> Jugnu</div>
                                    </div>
                                    <div class="planFooter">
                                        <span><strong> Case Reference: </strong> Taken for one week during August to delay period whilst on holiday.</span>
                                    </div>
                                    <div class="medicationSheet">
                                        <div class="reasonBox">
                                            <strong>Reason:</strong>
                                            Taken for one week during August to delay period whilst on holiday.
                                        </div>
                                    </div>
                                    <!-- <div class="planFooter">
                                        <span><strong> Reason: </strong> Taken for one week during August to delay period whilst on holiday.</span>
                                    </div> -->
                                </div>
                            </div>
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
                                <form>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Status *</label>
                                            <select class="form-control">
                                                <option>Screening Required</option>
                                                <option>Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label>Decision Date *</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Review Date</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>

                                        <div class="col-md-6  m-t-10">
                                            <label>Expiry Date</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Decision Made By *</label>
                                            <input type="date" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>Decision Maker Role</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6  m-t-10">
                                            <label>GMC Number</label>
                                            <input type="number" name="" id="" class="form-control">
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Mental Capacity</label>
                                            <select class="form-control">
                                                <option>Standard</option>
                                                <option>Part Time</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Patient Involvement</label>
                                            <select class="form-control">
                                                <option>Select</option>
                                                <option>Part Time</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Clinical Reasons</label>
                                            <textarea name="reason" required="" class="form-control" rows="4" cols="20" placeholder="Clinical reasons for DNACPR decision..."></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Anticipated Circumstances</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder="Circumstances in which DNACPR applies..."></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Other Emergency Treatments</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder="Other treatments that should/shouldn't be given..."></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Patient Wishes</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder=""></textarea>
                                        </div>
                                        <div class="col-md-6 m-t-10">
                                            <label>Form Location</label>
                                            <input type="text" name="" id="" class="form-control" placeholder="Physical location of signed form">
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <div class="DoLSCheckList">
                                                <label><input type="checkbox"> Discussion Held with Patient</label>
                                                <label><input type="checkbox"> Family/LPA/IMCA Involved</label>
                                                <label><input type="checkbox"> Emergency Services Made Aware</label>
                                                <label><input type="checkbox"> Care Plan Updated</label>
                                                <label><input type="checkbox"> All Staff Briefed</label>
                                                <label><input type="checkbox"> GP Notified</label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 m-t-10">
                                            <label>Additional Notes</label>
                                            <textarea name="reason" required="" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <div class="header-actions addnewicons">
                                                <button class="btn addAssessmentBtn"> Save DNACPR</button>
                                                <button class="btn borderBtn closeDnaCprBtn"> Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="carePlanWrapper DnaCprSectionSecond">
                                <div class="planCard borderleftOrange">
                                    <div class="planTop">
                                        <div class="planTitle">
                                            <span class="roundTag yellow">EXPIRED</span>
                                            <span class="inactive roundTag">has capacity</span>
                                        </div>
                                        <div class="planActions">
                                            <button class="addDnaCprBtn" data-formType="edit"><i class='bx  bx-edit'></i> </button>
                                        </div>
                                    </div>

                                    <div class="planMeta">
                                        <div><strong>Decision Date: </strong> January 5th, 2026</div>
                                        <div><strong>Review Date: </strong> January 14th, 2026</div>
                                    </div>

                                    <div class="planFooter">
                                        <span><strong> Decision Maker: </strong> Taken for one week during August to delay period whilst on holiday.</span>
                                    </div>
                                    <div class="planFooter">
                                        <span><strong> Patient Involvement: </strong> patient has capacity and agrees</span>
                                    </div>
                                    <div class="medicationSheet">
                                        <div class="reasonBox">
                                            <strong>Clinical Reasons:</strong> August to delay period whilst on holiday.
                                        </div>
                                    </div>
                                </div>
                            </div>
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

                        <!-- <div class="leavebanktabCont">
                            <i class='bx  bx-alert-triangle'></i> 
                            <p>No alerts match the selected filters</p>
                        </div> -->
                    </div>
                </div>
                <div class="content" id="clientEmergencyTab">
                    <div class="emergencyMain">
                        <div id="emergencyAller">
                            <div class="emergencyHeader">
                                <div class="emeregencyParent">
                                    <div class="emergencyContent">
                                        <div class="gap-3 d-flex align-items-center radIconClr">
                                            <i class="fas fa-phone-volume"></i>
                                            <h3>Emergency Contacts </h3>
                                        </div>
                                        <p class="mt-1"><small>Manage client emergency contact information</small></p>
                                    </div>
                                    <div class="emergencyBtn">
                                        <button class="borderBtn editBtn">
                                            <i class="fas fa-pencil-alt"></i>
                                            <span> Edit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="emergencybottom p-4">
                                <div class="userMum">
                                    <div class="d-flex gap-3">
                                        <div class="icon__pp"><i class="fa fa-user-o" aria-hidden="true"></i></div>
                                        <div>
                                            <h2>Carolanne Jones</h2>
                                            <span class="title">Mum</span>
                                        </div>

                                    </div>

                                </div>
                                <div class="emergencyError">
                                    <p><strong>Priority Contact 1- </strong> - This contact will be reached in case of emergencies</p>
                                </div>
                            </div>
                        </div>
                        <div id="contactWrapper">
                            <div class="contactMain p-4">
                                <div class="d-flex justify-content-between">
                                    <h3>Contact #</h3>
                                    <div class="deleteIcon"> <i class="fa fa-trash-o" aria-hidden="true"></i>

                                    </div>
                                </div>

                                <div class="emergencyForm">
                                    <div class="row mt-4">
                                        <div class="col-lg-4">
                                            <label class="form-label">Full Name</label>
                                            <input class="form-control" type="text" placeholder="Enter Full Name">
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="form-label">Phone Number</label>
                                            <input class="form-control" type="text" placeholder="Enter Phone Number">
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="form-label">Relationship</label>
                                            <input class="form-control" type="text" placeholder="Mother">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="formFooter">
                                <div class="contactBtn d-flex gap-4">
                                    <button type="button" id="addContactBtn">
                                        <i class="fa fa-plus me-3"></i>
                                        <span>Add Another Contact</span>
                                    </button>
                                </div>
                                <div style="border-top: 1px solid var(--borderColor); margin-top:20px; padding-top:20px">

                                    <div class="d-flex gap-3">
                                        <button class="redBtn">Save Contacts</button>
                                        <button class="borderBtn cancelBtn" onclick="showEmergency()">Cancel</button>
                                    </div>
                                </div>
                            </div>
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

                                        <button class="borderBtn">
                                            <i class="bx  bx-sparkles me-3"></i>
                                            <span> Generate Care Plan</span>
                                        </button>
                                    </div>
                                    <div>
                                        <button class="bgBtn" data-target-form="docForm1" onclick="toggleDocForm()">
                                            <i class='bx  bx-arrow-from-bottom-stroke'></i>
                                            <span> Upload Document</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="margin: 24px;">
                            <div class="purpleBox">
                                <div class="d-flex gap-3">
                                    <i class="bx  bx-sparkles"></i>
                                    <div>
                                        <p class="mb-2"> <strong>Assessment documents detected</strong></p>
                                        <p class="mb-0">Click "Generate Care Plan" to automatically create care plan, medications, and risk assessments from uploaded documents
                                        </p>
                                    </div>
                                </div>

                            </div>
                            <div class="mainBlueCard">
                                <div class="docForm " data-form-id="docForm1" style="display: none;">
                                    <div class="emergencyHeader mt-4" style="border-bottom: unset;">
                                        <div class="emeregencyParent">
                                            <div class="emergencyContent">
                                                <div class="gap-3 d-flex align-items-center iconConsent">
                                                    <i style="color: #434447;" class="fa fa-upload" aria-hidden="true"></i>
                                                    <h3>Upload New Document
                                                    </h3>
                                                </div>
                                                <div class="emergencyForm">
                                                    <div class="row mt-4">
                                                        <div class="col-lg-6">
                                                            <div>
                                                                <label class="form-label">Document Type *
                                                                </label>
                                                                <select name="" id="" class="form-control">
                                                                    <option value="">Other</option>
                                                                    <option value="">Care Plan</option>
                                                                    <option value="">Risk Assessment</option>
                                                                    <option value="">Other</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-6">
                                                            <label class="form-label">
                                                                Document Name *</label>
                                                            <input class="form-control" type="text" placeholder="Enter document name">
                                                        </div>

                                                        <div class="col-lg-12">
                                                            <label class="form-label">Select File *</label>
                                                            <input class="form-control" type="file">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="form-label">Expiry Date (Optional)</label>
                                                            <input class="form-control" type="date">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="form-label">Access Level
                                                            </label>
                                                            <select name="" id="" class="form-control">
                                                                <option value="">Other</option>
                                                                <option value="">Care Plan</option>
                                                                <option value="">Risk Assessment</option>
                                                                <option value="">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <label class="form-label">Tags (comma separated)</label>
                                                            <input class="form-control" type="text " placeholder="e.g., urgent, review, annual">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <div class="formCheck d-flex gap-3 align-items-center">
                                                                <input type="checkbox">
                                                                <label class="form-label mb-0" style="display: inline-block;">Mark as Confidential</label>
                                                            </div>


                                                        </div>
                                                        <div class="col-lg-12">
                                                            <label class="form-label">Notes</label>
                                                            <textarea class="form-control" placeholder="Additional notes about this document "></textarea>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <div class="formFooter">

                                                                <div class="d-flex gap-3">
                                                                    <button class="redBtn">Save Contacts</button>
                                                                    <button class="borderBtn cancelBtn" onclick="closeDocForm()">Cancel</button>
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
                            <div id="aiPlan">
                                <div class="bBorderCard mt-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="bCardHead">
                                            <div>
                                                <i class="far fa-file-alt"></i>
                                            </div>
                                            <div>
                                                <h3>AI Care Plan - 19/12/2025</h3>
                                            </div>
                                            <div>
                                                <span class="careBadg">Care Plan</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 careIconButton">
                                            <div>
                                                <button class="uploadBtn"> <i class="fa fa-download" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div> <button class="deleteBtn"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="docPlanD">
                                        <div class="uploadBy">
                                            <p class="mb-3"><strong>Uploaded :</strong> <span>Dec 16, 2025</span></p>
                                            <p class="mb-3"><strong>By :</strong> <span>Unknown Staff</span></p>
                                        </div>
                                        <p class="mb-3"><strong>Size :</strong> <span>Dec 16, 2025</span></p>
                                        <p class="mb-3"><strong>Uploaded :</strong> <span> 2.71 KB</span></p>
                                    </div>
                                    <div class=" userMum  d-flex gap-3 mb-3">
                                        <span class="title">processed_for_care_plan </span>
                                        <span class="title">processed_for_care_plan </span>
                                    </div>
                                    <p class="para text-sm">AI-generated person-centered care plan with actionable tasks and objectives Used to generate care plan 6958abcf3cdd6f7f93bc71eb on 1/3/2026 Used to generate care plan 6958c1fa065bde2dc29dac0f on 1/3/2026</p>
                                </div>

                                <div class="bBorderCard mt-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="bCardHead">
                                            <div>
                                                <i class="far fa-file-alt"></i>
                                            </div>
                                            <div>
                                                <h3>AI Care Plan - 19/12/2025</h3>
                                            </div>
                                            <div>
                                                <span class="careBadg">Care Plan</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 careIconButton">
                                            <div>
                                                <button class="uploadBtn"> <i class="fa fa-download" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                            <div> <button class="deleteBtn"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="docPlanD">
                                        <div class="uploadBy">
                                            <p class="mb-3"><strong>Uploaded :</strong> <span>Dec 16, 2025</span></p>
                                            <p class="mb-3"><strong>By :</strong> <span>Unknown Staff</span></p>
                                        </div>
                                        <p class="mb-3"><strong>Size :</strong> <span>Dec 16, 2025</span></p>
                                        <p class="mb-3"><strong>Uploaded :</strong> <span> 2.71 KB</span></p>
                                    </div>
                                    <div class="userMum d-flex gap-3 mb-3">
                                        <span class="title">processed_for_care_plan </span>
                                        <span class="title">processed_for_care_plan </span>
                                    </div>
                                    <p class="para text-sm">AI-generated person-centered care plan with actionable tasks and objectives Used to generate care plan 6958abcf3cdd6f7f93bc71eb on 1/3/2026 Used to generate care plan 6958c1fa065bde2dc29dac0f on 1/3/2026</p>
                                </div>
                            </div>
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
                                                                <h5>Activities & Engagement
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
            </div>
            <!-- END TAB CONTENT -->
        </div>

    </div>


    </div>





    <!-- add Carer Modal -->
    <div class="modal fade leaveCommunStyle" id="addcreateCarePlanModal" tabindex="1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"> <i class="bx  bx-heart"></i> Create Care Plan - Logan Jones</h4>
                </div>
                <div class="modal-body heightScrollModal">
                    <div class="carer-form">

                        <div class="availabilityTabs createCarePlanTabs">
                            <!-- TAB HEADER -->
                            <div class="availabilityTabs__nav">
                                <button class="availabilityTabs__tab active" data-target="carePlanOverview"><i class="bx  bx-file-report"></i> Overview</button>
                                <button class="availabilityTabs__tab" data-target="carePlanObjectives"><i class='bx  bx-radio-circle-marked'></i> Objectives</button>
                                <button class="availabilityTabs__tab" data-target="carePlanCareTasks"><i class='bx  bx-checklist'></i> Care Tasks</button>
                                <button class="availabilityTabs__tab" data-target="carePlanMedication"><i class='bx  bx-pill'></i> Medication</button>
                                <button class="availabilityTabs__tab" data-target="carePlanPreferences"><i class='bx  bx-user'></i> Preferences</button>
                                <button class="availabilityTabs__tab" data-target="carePlanRisk"><i class='bx  bx-alert-triangle'></i> Risk</button>
                            </div>

                            <!-- TAB CONTENT -->
                            <div class="availabilityTabs__content">

                                <div class="availabilityTabs__panel active" id="carePlanOverview">
                                    <div class="">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Care Setting *</label>
                                                    <select class="form-control">
                                                        <option>Domiciliary Care</option>
                                                        <option>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Plan Type </label>
                                                    <select class="form-control">
                                                        <option>Initial Assessment</option>
                                                        <option>Part Time</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4  m-t-10">
                                                    <label>Assessment Date *</label>
                                                    <input type="text" class="form-control">
                                                </div>
                                                <div class="col-md-4  m-t-10">
                                                    <label>Status</label>
                                                    <input type="text" class="form-control">
                                                </div>

                                                <div class="col-md-4  m-t-10">
                                                    <label>Assessed By</label>
                                                    <input type="text" class="form-control" placeholder="Staff member name">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Status</label>
                                                    <select class="form-control">
                                                        <option>Full Time</option>
                                                        <option>Part Time</option>
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
                                                    <input type="text" class="form-control" value="Logan">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Language </label>
                                                    <input type="text" class="form-control" value="English">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Religion</label>
                                                    <input type="text" class="form-control" placeholder="Staff member name">
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Cultural Needs </label>
                                                    <input type="text" class="form-control" placeholder="Staff member name">
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="address">
                                                <label>Daily Routine</label>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Morning</label>
                                                    <textarea name="morning" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Afternoon </label>
                                                    <textarea name="afternoon" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Evening</label>
                                                    <textarea name="evening" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                </div>
                                                <div class="col-md-6  m-t-10">
                                                    <label>Night </label>
                                                    <textarea name="night" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="availabilityTabs__panel" id="carePlanObjectives">
                                    <div class="">
                                        <div class="workHoursHeader">
                                            <div class="title"> Care Objectives</div>
                                            <div class="actions mt-0">
                                                <button class="borderBtn"> <i class="bx  bx-plus"></i> Add Objective</button>
                                            </div>
                                        </div>
                                        <form>
                                            <div class="leave-card">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="ObjectiveEndDelete planActions">
                                                            <button class="objectiveNumber">Objective 1</button>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label>Morning</label>
                                                        <textarea name="morning" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Target Date</label>
                                                        <input type="date" class="form-control">
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Status</label>
                                                        <select class="form-control">
                                                            <option>Not Started</option>
                                                            <option>Part Time</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Outcome Measures</label>
                                                        <input type="text" class="form-control" placeholder="How will success be measured?">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="leave-card">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="ObjectiveEndDelete planActions">
                                                            <button class="objectiveNumber">Objective 1</button>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label>Morning</label>
                                                        <textarea name="morning" required="" class="form-control" rows="3" cols="20" placeholder="Describe morning routine..."></textarea>
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Target Date</label>
                                                        <input type="date" class="form-control">
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Status</label>
                                                        <select class="form-control">
                                                            <option>Not Started</option>
                                                            <option>Part Time</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Outcome Measures</label>
                                                        <input type="text" class="form-control" placeholder="How will success be measured?">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="availabilityTabs__panel" id="carePlanCareTasks">
                                    <div class="">
                                        <div class="workHoursHeader">
                                            <div class="title"> Care Tasks & Interventions</div>
                                            <div class="actions mt-0">
                                                <button class="borderBtn"> <i class="bx  bx-plus"></i> Add Task</button>
                                            </div>
                                        </div>
                                        <form>
                                            <div class="leave-card">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="ObjectiveEndDelete planActions workHoursHeader">
                                                            <span class="badge">Personal Care</span>

                                                            <div class="activeCheck">
                                                                <label><input type="checkbox"> Active</label>
                                                                <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Task Name</label>
                                                        <input type="date" class="form-control">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label>Category</label>
                                                        <select class="form-control">
                                                            <option>Not Started</option>
                                                            <option>Part Time</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12  m-t-10">
                                                        <label>Description</label>
                                                        <textarea name="Description" required="" class="form-control" rows="3" cols="20" placeholder="Describe what needs to be done..."></textarea>
                                                    </div>

                                                    <div class="col-md-4  m-t-10">
                                                        <label>Frequency</label>
                                                        <select class="form-control">
                                                            <option>Not Started</option>
                                                            <option>Part Time</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Preferred Time</label>
                                                        <input type="time" class="form-control" placeholder="">
                                                    </div>
                                                    <div class="col-md-4  m-t-10">
                                                        <label>Duration (mins)</label>
                                                        <input type="number" class="form-control" value="15">
                                                    </div>

                                                    <div class="col-md-12  m-t-10">
                                                        <label>Special Instructions</label>
                                                        <textarea name="Description" required="" class="form-control" rows="3" cols="20" placeholder="Any special instructions for carers..."></textarea>
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <div class="requiresLable">
                                                            <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike">
                                                            <label for="vehicle1"> Requires two carers</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="availabilityTabs__panel" id="carePlanMedication">
                                    <div class="">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="requiresLable">
                                                        <input type="checkbox" id="self-administers" name="self-administers" value="Bike">
                                                        <label for="self-administers">Client self-administers medication</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Administration Support Level</label>
                                                    <select class="form-control">
                                                        <option>Prompting Only</option>
                                                        <option>Part Time</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Pharmacy Details</label>
                                                    <input type="text" class="form-control" placeholder="Pharmacy name & contact">
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>GP Details</label>
                                                    <input type="text" class="form-control" placeholder="GP surgery & contact">
                                                </div>

                                                <div class="col-md-12 m-t-10">
                                                    <label>Allergies & Sensitivities</label>
                                                    <textarea name="Sensitivities" required="" class="form-control sensitivitiesTextarea" rows="3" cols="20" placeholder="List any known allergies or sensitivities..."></textarea>
                                                </div>
                                            </div>



                                            <div class="workHoursHeader m-t-15">
                                                <div class="title"> Medications</div>
                                                <div class="actions mt-0">
                                                    <button class="borderBtn"> <i class="bx  bx-plus"></i> Add Medication</button>
                                                </div>
                                            </div>
                                            <div class="leave-card">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="ObjectiveEndDelete planActions workHoursHeader">
                                                            <span class="badge"><i class='bx  bx-link'></i> </span>
                                                            <div class="activeCheck">
                                                                <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Medication Name</label>
                                                        <input type="date" class="form-control" placeholder="e.g., Paracetamol">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label>Dose</label>
                                                        <input type="text" class="form-control" placeholder="e.g., 500mg">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Frequency</label>
                                                        <input type="text" class="form-control" placeholder="e.g., 500mg">
                                                    </div>

                                                    <div class="col-md-6  m-t-10">
                                                        <label>Purpose</label>
                                                        <input type="text" name="purpose" required="" class="form-control" placeholder="What is this medication for?">
                                                    </div>
                                                    <div class="col-md-6 m-t-10">
                                                        <div class="requiresLable">
                                                            <input type="checkbox" id="PRN" name="PRN" value="Bike">
                                                            <label for="PRN">PRN (as needed)</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12  m-t-10">
                                                        <label>Special Instructions</label>
                                                        <input type="text" class="form-control" placeholder="e.g., Take with food">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="availabilityTabs__panel" id="carePlanPreferences">
                                    <div class="">
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Likes</label>
                                                    <textarea name="likes" required="" class="form-control" rows="3" cols="20" placeholder="Enter likes (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Dislikes</label>
                                                    <textarea name="dislikes" required="" class="form-control" rows="3" cols="20" placeholder="Enter dislikes (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Hobbies & Interests</label>
                                                    <textarea name="hobbies&Interests" required="" class="form-control" rows="3" cols="20" placeholder="Enter hobbies (one per line)"></textarea>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Food Preferences</label>
                                                    <textarea name="foodPreferences" required="" class="form-control" rows="3" cols="20" placeholder="Dietary requirements, favourite foods, etc."></textarea>
                                                </div>
                                                <div class="col-md-6 m-t-10">
                                                    <label>Personal Care Preferences</label>
                                                    <textarea name="personalCarePreferences" required="" class="form-control" rows="3" cols="20" placeholder="How they like to be supported with personal care..."></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Communication Preferences</label>
                                                    <textarea name="communicationPreferences" required="" class="form-control" rows="2" cols="20" placeholder="How they prefer to communicate, any aids needed..."></textarea>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Social Preferences</label>
                                                    <textarea name="socialPreferences" required="" class="form-control" rows="2" cols="20" placeholder="Social activities, visitors, alone time preferences..."></textarea>
                                                </div>
                                            </div>
                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="availabilityTabs__panel" id="carePlanRisk">
                                    <div class="">
                                        <div class="workHoursHeader">
                                            <div class="title"> Risk Factors</div>
                                            <div class="actions mt-0">
                                                <button class="borderBtn"> <i class="bx  bx-plus"></i> Add Risk</button>
                                            </div>
                                        </div>
                                        <form>
                                            <div class="leave-card">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="ObjectiveEndDelete planActions">
                                                            <button class="objectiveNumber">Risk 1</button>
                                                            <button class="danger"><i class="bx  bx-trash"></i> </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 ">
                                                        <label>Target Date</label>
                                                        <input type="date" class="form-control">
                                                    </div>
                                                    <div class="col-md-6 m-t-10">
                                                        <label>Likelihood</label>
                                                        <select class="form-control">
                                                            <option>Low</option>
                                                            <option>Medium</option>
                                                            <option>Heigh</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 m-t-10">
                                                        <label>Impact</label>
                                                        <select class="form-control">
                                                            <option>Low</option>
                                                            <option>Medium</option>
                                                            <option>Heigh</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 m-t-10">
                                                        <label>Control Measures</label>
                                                        <textarea name="Measures" required="" class="form-control" rows="3" cols="20" placeholder="How is this risk being managed?"></textarea>
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
                                                    <input type="text" class="form-control" placeholder="Preferred hospital">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="requiresLable">
                                                        <input type="checkbox" id="DNACPR" name="DNACPR" value="Bike">
                                                        <label for="DNACPR">DNACPR in place</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 m-t-10">
                                                    <label>Emergency Protocol</label>
                                                    <textarea name="Emergency" required="" class="form-control" rows="3" cols="20" placeholder="What to do in an emergency..."></textarea>
                                                </div>
                                            </div>


                                            <div class="actions">
                                                <button type="button" class="cancel">Cancel</button>
                                                <button type="submit" class="submit">Create Care Plan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
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
    <!-- pratima modal end -->

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
    </script>

    <!-- pratima js start -->
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://jqueryniceselect.hernansartorio.com/js/jquery.nice-select.min.js"></script>
    <script>
        const wrapper = document.getElementById("contactWrapper");

        const originalContact = wrapper.querySelector(".contactMain");
        const editBtn = document.querySelector(".editBtn");

        document.querySelector(".editBtn").addEventListener("click", function() {
            wrapper.style.display = "block";
            editBtn.style.display = "none"
        });

        function showEmergency() {
            wrapper.style.display = "none";
            editBtn.style.display = "block";

            wrapper.querySelectorAll(".contactMain").forEach((contact, index) => {
                if (index !== 0) contact.remove(); // Keep first one
            });
            originalContact.querySelectorAll("input").forEach(input => input.value = "");

        }

        document.getElementById("addContactBtn").addEventListener("click", function() {
            const newContact = originalContact.cloneNode(true);

            newContact.querySelectorAll("input").forEach(input => input.value = "");

            const footer = wrapper.querySelector(".formFooter");
            wrapper.insertBefore(newContact, footer);
        });


        wrapper.addEventListener("click", function(e) {
            if (e.target.closest(".deleteIcon")) {
                const contact = e.target.closest(".contactMain");

                if (contact !== originalContact) {
                    contact.remove();
                } else {

                    contact.querySelectorAll("input").forEach(input => input.value = "");
                }
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
        // console.log(clientDetails);
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

    @endsection
</main>