@extends('frontEnd.layouts.master')
@section('title', 'Incident Management')
@section('content')

@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <!-- main page -->
    <div class="container-fluid" id="mainIncidentPage">
        <div class="row">
            <div class="col-md-12">
                <div class="staffHeaderp">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class=" mainTitleIcon bx bx-shield"></i>
                            <h1 class="mainTitlep mb-0"> CQC Incident Management</h1>
                        </div>


                        <p class="header-subtitle mb-0"> Record, investigate, and report incidents in line with CQC
                            regulations </p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div>

                            <button class="borderBtn"><i class=' f18 bx  bx bx-arrow-to-bottom-stroke me-2'></i>
                                Export</button>
                        </div>
                        <div>
                            <button class="borderBtn pupleBorderBtn" type="button"
                                onclick="window.location.href='{{url('roster/incident-ai-prevention')}}'"><i
                                    class='f18 bx  bx-sparkles me-2'></i> AI Prevention</button>

                        </div>
                        <div>
                            <button class="bgBtn bgRedBtn" type="button" onclick="showAddReportInc()"><i
                                    class='f18 bx  bx-plus me-2'></i> Report
                                Incident</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="bBorderCard mt-5 urReqSec ">
                    <div class="d-flex gap-3 align-items-center urReqCon">
                        <div>
                            <i class='bx  bx-alert-triangle'></i>
                        </div>
                        <div>
                            <h5 class="h5Head">Urgent Action Required</h5>
                            <div class="d-flex gap-4 mt-3 urReqDetails">

                                <span>• 6 incidents require CQC notification</span>
                                <span>• 2 critical incidents open</span>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="rota_dashboard-cards simpleCard mt-5 pRotaCard">
                    <div class="rota_dash-card bg-blue-50 p-4">
                        <div class="rota_dash-left">
                            <p class="rota_title"> <i class='bx  bx-file-detail me-2'></i> Total</p>
                            <h2 class="rota_count" id="totalCount">0</h2>
                        </div>
                    </div>
                    <div class="rota_dash-card bg-orange-50">
                        <div class="rota_dash-left">
                            <p class="rota_title"> <i class='bx  bx-clock me-2'></i> Open</p>
                            <h2 class="rota_count orangeText" id="openCount">0</h2>
                        </div>
                    </div>
                    <div class="rota_dash-card bg-red-50">
                        <div class="rota_dash-left">
                            <p class="rota_title"> <i class="bx bx-shield me-2"></i> Safeguarding</p>
                            <h2 class="rota_count" id="safeguardCount">0</h2>
                        </div>
                    </div>
                    <div class="rota_dash-card bg-purple-50">
                        <div class="rota_dash-left">
                            <p class="rota_title"><i class="bx  bx-alert-triangle me-2"></i> CQC Pending</p>
                            <h2 class="rota_count" id="cqcCount">0</h2>
                        </div>
                    </div>


                    <div class="rota_dash-card bg-greenp-50">
                        <div class="rota_dash-left">
                            <p class="rota_title"> <i class="bx  bx-check-circle me-2"></i> Resolved</p>
                            <h2 class="rota_count greenTextp" id="resolveCount">0</h2>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="emergencyMain p-4">
                    <div class="carer-form">
                        <div class="row">
                            <div class="col-md-4 col-sm-6 ">
                                <div class="input-group searchWithtabs" style="width: 100%;">
                                    <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search entries..." id="incident_serach">
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <input type="date" name="" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <input type="date" name="" id="end_date" class="form-control">
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <select class="form-control" id="incident_typeFileter">
                                    <option value="0">All Types</option>
                                    <option value="1">Safeguarding</option>
                                    <option value="2">Accident</option>
                                    <option value="4">Fall</option>
                                    <option value="5">Medication Error</option>
                                    <option value="6">Abuse Allegation</option>
                                    <option value="15">Complaint</option>
                                    <option value="12">Death</option>
                                    <option value="16">Other</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-sm-6 mb-2">
                                <select class="form-control" id="incident_statusFilter">
                                    <option value="0">All Status</option>
                                    <option value="1">Reported</option>
                                    <option value="2">Under Investigation</option>
                                    <option value="3">Resolved</option>
                                    <option value="4">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-4">
                                <div class="d-flex gap-3 align-items-center">
                                    <div>
                                        <div class="checkboxp">
                                            <input type="checkbox" id="safeguardingRiskFilter">
                                            <label for="safeguardingRiskFilter">
                                                Safeguarding Risk (Will notify Registered Manager)
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" onclick="clearFilterData()" class="borderBtn">Reset Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-md-12" id="incident_report_data">
                <div class="emergencyMain p24 text-center">
                    <i class="lightMute bx bx-shield" style="font-size:54px"></i>
                    <h5 class="h5Head mt-3">No incidents found</h5>
                    <p class="textGray fs13">No incidents match the selected criteria</p>
                    <div class="d-flex justify-content-center mt-3">
                        <button class="bgBtn bgRedBtn" type="button" onclick="showAddReportInc()">
                            <i class="f18 bx  bx-plus me-3"></i> Report First Incident
                        </button>
                    </div>
                </div>    
            </div>
            <div id="incident_reportPagination"></div>
        </div>

    </div>

    <!--  report new incident form -->
    <div class="container-fluid" id="incidentAddForm" style="display:none">
        <div class="row justify-content-center d-flex">
            <div class="col-md-10">
                <div class="emergencyMain">
                    <div class="emergencyHeader">
                        <div class="emeregencyParent align-items-center">
                            <div class="emergencyContent">
                                <div class="gap-3 d-flex align-items-center radIconClr">
                                    <i class="bx bx-shield f20"></i>
                                    <h3>Report New Incident

                                    </h3>
                                </div>

                            </div>
                            <div class="emergencyBtn">
                                <i onclick=showMainIncident() class='bx  bx-x'></i>
                            </div>
                        </div>
                    </div>
                    <div class="p24">
                        <form id="report_incident_form" action="{{url('roster/incident-report-save')}}" method="post">
                            @csrf
                            <div class="purpleBox p-4 reportRedBox">
                                <div class="d-flex gap-3">
                                    <div>
                                        <input type="checkbox" id="safeguarding" name="is_safeguarding" value="0">
                                    </div>
                                    <div class="">
                                        <p class="mb-2" for="safeguarding"> <strong>This is a SAFEGUARDING concern</strong>
                                        </p>
                                        <p class="mb-0">Click "Generate Care Plan" to automatically create care plan,
                                            medications, and risk assessments from uploaded documents
                                        </p>
                                    </div>
                                </div>

                            </div>
                            <div class="carer-form mt20">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Incident Type *</label>
                                        <select class="form-control checkVali" name="incident_type_id">
                                            <option selected disabled>Select Incident Type</option>
                                            @foreach($incident_type as $itval)
                                            <option value="{{$itval->id}}">{{$itval->type}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Severity *</label>
                                        <select class="form-control checkVali" name="severity_id" id="severity_id">
                                            <option value="1">Low</option>
                                            <option value="2" selected>Medium</option>
                                            <option value="3">High</option>
                                            <option value="4">Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6  m-t-10">
                                        <label>Client *</label>
                                        <select class="form-control checkVali" name="client_id">
                                            <option selected disabled>Select Client</option>
                                            @foreach($client as $cval)
                                            <option value="{{$cval->id}}">{{$cval->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 m-t-10">
                                        <label> Incident Date & Time *</label>
                                        <input type="datetime-local" class="form-control checkVali" name="date_time" id="date_time">
                                    </div>
                                    <div class="col-md-6 m-t-10">
                                        <label> Location *</label>
                                        <input type="text" class="form-control checkVali"
                                            placeholder="e.g., Client's home, Day centre" name="location">
                                    </div>
                                    <div class="col-md-6 m-t-10">
                                        <label>Location Detail</label>
                                        <input type="text" class="form-control" placeholder="e.g., Bathroom, Bedroom" name="location_detail">
                                    </div>
                                    <div class="col-md-12 mt20">
                                        <div class="purpleBox p-4 reportRedBox" style="display:none" id="safeguardingDetailsForm">
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <i class=" darkRedC bx bx-shield f20"></i>
                                                </div>
                                                <div class="">
                                                    <h6 class="mb-2 h6Head"> <strong style="font-size:15px">Safeguarding Detail</strong></h6>
                                                    <p class="mb-0"> Select all types of safeguarding concerns that apply:
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="row addReportCheck">
                                                 @foreach ($safeguard_type as $item)
                                                        <div class="col-md-6 m-t-5">
                                                            <div class="checkboxp">
                                                                <input type="checkbox"  id="safeguarding_details_{{ $item->id }}"
                                                                    name="safeguarding_detail[]"
                                                                    value="{{ $item->id }}"
                                                                    class="SafeguardingDetailsCheckBox">
                                                                <label  id="safeguarding_details_{{ $item->id }}">
                                                                    {{ $item->type }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                <!--<div class="col-md-6 m-t-10">-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="physicalAbuse" name="physicalAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="physicalAbuse">-->
                                                <!--            Physical Abuse-->
                                                <!--        </label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="twoPerson" name="emotional" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="twoPerson">-->

                                                <!--            Emotional/Psychological Abuse-->
                                                <!--        </label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="neglect" name="neglect" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="neglect">-->
                                                <!--            Neglect-->
                                                <!--        </label>-->
                                                <!--    </div>-->
                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="domesticAbuse" name="domesticAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="domesticAbuse">-->
                                                <!--            Domestic Abuse-->
                                                <!--        </label>-->
                                                <!--    </div>-->
                                                <!--    <div class="checkboxp mb-0">-->
                                                <!--        <input type="checkbox" id="selfNeglec" name="selfNeglec" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="selfNeglec">-->
                                                <!--            Self-Neglect-->
                                                <!--        </label>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                <!--<div class="col-md-6 m-t-10">-->
                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="sexualAbuse" name="sexualAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="sexualAbuse">Sexual Abuse</label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="financialAbuse" name="financialAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="financialAbuse">Financial Abuse</label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="discriminatoryAbuse" name="discriminatoryAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="discriminatoryAbuse">Discriminatory Abuse</label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="modernSlavery" name="modernSlavery" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="modernSlavery">Modern Slavery</label>-->
                                                <!--    </div>-->

                                                <!--    <div class="checkboxp">-->
                                                <!--        <input type="checkbox" id="organisationalAbuse" name="organisationalAbuse" value="0" class="SafeguardingDetailsCheckBox">-->
                                                <!--        <label for="organisationalAbuse">Organisational Abuse</label>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-md-12  m-t-10">
                                        <label>What Happened? * (Factual account)</label>
                                        <textarea class="form-control checkVali" rows="3" cols="20" placeholder="Provide a detailed factual account of what happened..." name="what_happened"></textarea>
                                        <small class="formIns">Task will appear for carer during this shift </small>
                                    </div>
                                    <div class="col-md-12  m-t-10">
                                        <label>Immediate Action Taken *</label>
                                        <textarea class="form-control checkVali" rows="3" cols="20" name="immediate_action" placeholder="What immediate actions were taken? (e.g., first aid given, ambulance called)"></textarea>

                                    </div>
                                    <div class="col-md-4 m-t-10">
                                        <label for="familyNotify">
                                            <div class="checkFamilyNoti">
                                                <div class="d-flex gap-2 align-items-center">

                                                    <input type="checkbox" id="familyNotify" name="family_notify" value="0" class="form_notification">
                                                    <div class="cqcCheck">
                                                        Family Notified
                                                    </div>
                                                </div>

                                            </div>

                                        </label>
                                    </div>
                                    <div class="col-md-4 m-t-10">
                                        <label for="cqcNotification">
                                            <div class="checkFamilyNoti bg-purple-50">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="checkbox" id="cqcNotification" name="cqcNotification" value="0" class="form_notification">
                                                    <div class="cqcCheck pupleTextp">
                                                        CQC Notification Required
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-md-4 m-t-10">
                                        <label for="policeInvolved">
                                            <div class="checkFamilyNoti bg-red-50">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="checkbox" id="policeInvolved" name="policeInvolved" value="0" class="form_notification">
                                                    <div class="cqcCheck textRedp">
                                                        Police Involved
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="col-md-12 m-t-10">
                                        <div class="purpleBox p-4 reportyellowBox">
                                            <div class="d-flex gap-3">
                                                <div>
                                                    <i class='darkyellowIc bx  bx-alert-triangle f20'></i>
                                                </div>
                                                <div class="">
                                                    <p class="mb-2" for="safeguarding"> <strong>This is a SAFEGUARDING
                                                            concern</strong>
                                                    </p>

                                                </div>
                                            </div>
                                            <ul class="addIncidentList">
                                                <li>All safeguarding concerns must be reported to local authority within 24
                                                    hours</li>
                                                <li>CQC must be notified of serious incidents without delay</li>
                                                <li>Deaths, serious injuries, and safeguarding concerns require statutory
                                                    notifications</li>
                                                <li>Ensure all relevant parties have been informed as per your policy</li>
                                            </ul>
                                        </div>


                                    </div>
                                    <div class="col-md-12 m-t-10">
                                        <hr class="hrLinep">
                                        <div class="d-flex gap-3 incidentAddFooter">
                                            <div>
                                                <button style="width:100%" class="bgBtn bgRedBtn incedent_form_submit"><i
                                                        class="bx bx-save f18"></i>Submit
                                                    Incident
                                                    Report</button>
                                            </div>
                                            <div>
                                                <button class="borderBtn" onclick=showMainIncident()>
                                                    Cancel
                                                </button>
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
    <!-- report new end -->
    <script>
    const showAddReportInc = () => {

        document.getElementById("incidentAddForm").style.display = "block";
        document.getElementById("mainIncidentPage").style.display = "none";
    };
    const showMainIncident = () => {
        document.getElementById("incidentAddForm").style.display = "none";
        document.getElementById("mainIncidentPage").style.display = "block";
    }
    </script>
    <script>
        $(document).ready(function(){
            loadIncidentReportData();
        });
        var is_ready = 1;
        function loadIncidentReportData(pageUrl = '{{ url("/roster/incident-report-loadData") }}',start_date = null,end_date = null,Safeguarding = 0, search = null,incident_typeFileter = null, incident_statusFilter = null){
            $.ajax({
                url: pageUrl,
                type: "post",
                data: {start_date:start_date,end_date:end_date,Safeguarding:Safeguarding,search_incident:search,incident_type_id:incident_typeFileter,status:incident_statusFilter,_token:"{{csrf_token()}}"},
                success: function (res) {
                    console.log(res);
                    // return false;
                    if (typeof isAuthenticated === "function") {
                        if (isAuthenticated(res) == false) {
                            return false;
                        }
                    }
                    if(res.success == false){
                        alert(res.errors);
                    }else{
                        
                        var data = res.data;
                        var no_data = `<div class="emergencyMain p24 text-center">
                    <i class="lightMute bx bx-shield" style="font-size:54px"></i>
                    <h5 class="h5Head mt-3">No incidents found</h5>
                    <p class="textGray fs13">No incidents match the selected criteria</p>
                    <div class="d-flex justify-content-center mt-3">
                        <button class="bgBtn bgRedBtn" type="button"onclick="showAddReportInc()">
                            <i class="f18 bx  bx-plus me-3"></i> Report First Incident
                        </button>
                    </div>
                </div>`;
                        if(data.length == 0){
                            $("#incident_report_data").html(no_data);
                        }else{
                            var allHtmlData = ``;
                            var totalCount = 0;
                            var openCount = 0;
                            var safeguardCount = 0;
                            var cqcCount = 0;
                            var resolveCount = 0;

                            data.forEach(function(item){
                                let bgColorClass = '';
                                let is_safeguardinghtml = '';
                                if(item.is_safeguarding == 1){
                                    safeguardCount++;
                                    bgColorClass = 'urReqSec';
                                    is_safeguardinghtml = `<div>
                                                            <span class="careBadg redDarkBadgesAni">SAFEGUARDING</span>
                                                        </div>`;
                                }
                                let severityhtml = '';
                                if(item.severity_id == 1){
                                    severityhtml = `<span class="careBadg">Low</span>`;
                                }else if(item.severity_id == 2){
                                    severityhtml = `<span class="careBadg yellowBadges">Medium</span>`;
                                }else if(item.severity_id == 3){
                                    severityhtml = `<span class="careBadg highBadges">High</span>`;
                                }else if(item.severity_id == 4){
                                    severityhtml = `<span class="careBadg redbadges">Critical</span>`;
                                }
                                let statushtml = '';
                                if(item.status == 1){
                                    openCount++;
                                    statushtml = `<span class="careBadg muteBadges">reported</span>`;
                                }else if(item.status == 2){
                                    openCount++;
                                    statushtml = `<span class="careBadg muteBadges">Under Investigation</span>`;
                                }else if(item.status == 3){
                                    resolveCount++;
                                    statushtml = `<span class="careBadg muteBadges">Resoled</span>`;
                                }else if(item.status == 4){
                                    openCount++;
                                    statushtml = `<span class="careBadg muteBadges">Closed</span>`;
                                }
                                let cocHtml = '';
                                if(item.cqcNotification == 1){
                                    cqcCount++;
                                    cocHtml = `<span class="careBadg purpleBadgesDark">CQC NOTIFICATION REQUIRED</span>`;
                                }
                               allHtmlData+=`<a href="{{ url('roster/incident-report-details/`+item.id+`') }}">
                                    <div class="bBorderCard mt-4 `+bgColorClass+`">
                                    
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="d-flex gap-3 align-items-center mb-2">
                                                    <h5 class="h5Head m-0">`+item.incident_type.type+`</h5>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div>
                                                            `+severityhtml+`
                                                        </div>

                                                        <div>
                                                            `+statushtml+`
                                                        </div>
                                                        `+is_safeguardinghtml+`
                                                        <div>
                                                            `+cocHtml+`
                                                        </div>
                                                        <div class="userMum">
                                                            <span class="title mt-0">
                                                                <span>Ref: </span> `+item.ref+`
                                                            </span>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="fallRedCon">
                                                    <div class="d-flex align-items-center gap-3 mt-3">
                                                        <p class="para text-sm mb-0"> <span>`+formatDateTime(item.date_time)+`</span></p>
                                                    </div>
                                                    <div>
                                                        <h6 class="h6Head my-3"><span>Client</span>: `+item.clients.name+`</h6>
                                                        <p class="para text-sm mb-0"> <span>`+item.what_happened+`</span></p>
                                                        <div class="footerRedFall">
                                                            <div class="d-flex gap-4">
                                                                <div>

                                                                    <span class="muchsmallText">Location: `+item.location+`</span>
                                                                </div>
                                                                <div>
                                                                    <span class="greenText" style="font-size: 11px;">• Action taken</span>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div>
                                                <button  class="borderBtn"><i
                                                        class='bx  bx-eye f18 me-3 '></i> View</button>
                                            </div>
                                        </div>

                                    </div>
                                </a>`; 
                            });
                            if(is_ready){
                                $("#totalCount").text(data.length);
                                $("#openCount").text(openCount);
                                $("#safeguardCount").text(safeguardCount);
                                $("#cqcCount").text(cqcCount);
                                $("#resolveCount").text(resolveCount);
                            }
                            is_ready = 0;
                            $("#incident_report_data").html(allHtmlData);
                            var paginationControls = $("#incident_reportPagination");
                            paginationControls.empty();
                            if (res.pagination.prev_page_url) {
                                paginationControls.append('<button class="profileDrop" onclick="loadIncidentReportData( \'' + pagination.prev_page_url + '\')">Previous</button>');
                            }
                            if (res.pagination.next_page_url) {
                                paginationControls.append('<button class="profileDrop" onclick="loadIncidentReportData( \'' + pagination.next_page_url + '\')">Next</button>');
                            }
                        } 
                    }
                }
            });
        }
        function formatDateTime(datetime) {
            const date = new Date(datetime.replace(' ', 'T'));

            return date.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }
        $(document).on('change','#safeguarding',function(){
            $("#safeguardingDetailsForm").hide();
            $(this).val(0);
            $("#cqcNotification").prop('checked',false).val(0);
            if ($(this).is(':checked')) {
                $(this).val(1);
                $("#severity_id").val(4);
                $("#cqcNotification").prop('checked',true).val(1);
                $("#safeguardingDetailsForm").show();
            }
        });
        $(document).on('change','.form_notification',function(){
            if($(this).is(':checked')){
                $(this).val(1);
            }else{
                $(this).val(0);
            }
        });
        $(document).on('click','.incedent_form_submit',function(){
            var error = 0;
            $('.checkVali').each(function(){
                if($(this).val() == ''|| $(this).val() == undefined){
                    error = 1;
                    $(this).css('border','1px solid red').focus();
                    return false;
                }else{
                    error = 0;
                    $(this).css('border','');
                }
            });
            if(error == 1){
                return false;
            }
        });
        $(document).on('change','.SafeguardingDetailsCheckBox',function(){
            if($(this).is(':checked')){
                $(this).val(1);
            }else{
                $(this).val(0);
            }
        });
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('date_time');

            if (!input.value) {
                const now = new Date();

                // YYYY-MM-DDTHH:MM (required format for datetime-local)
                const formatted =
                    now.getFullYear() + '-' +
                    String(now.getMonth() + 1).padStart(2, '0') + '-' +
                    String(now.getDate()).padStart(2, '0') + 'T' +
                    String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0');

                input.value = formatted;
            }
        });
    var searchData = undefined;
    var startDate = undefined;
    var endDate = undefined;
    var incident_typeData = undefined;
    var incident_statusData = undefined;
    var SafeguardingData = 0;
    $(document).on('keyup','#incident_serach',function(){
        searchData = $(this).val();
        loadIncidentReportData(undefined,startDate,endDate,SafeguardingData, searchData,incident_typeData,incident_statusData);
    });
    
    $(document).on('change','#start_date',function(){
        startDate = $(this).val();
    });

    $(document).on('change','#end_date',function(){
        if ((Date.parse($(this).val()) <= Date.parse(startDate))) {
            alert("Start date should be less than End date");
            $(this).val('');
            return false;
        }
        endDate = $(this).val();
        loadIncidentReportData(undefined,startDate,endDate,SafeguardingData, searchData,incident_typeData,incident_statusData);
    });
    
    $(document).on('change','#safeguardingRiskFilter',function(){
        SafeguardingData = 0;
        if($(this).is(':checked')){
            SafeguardingData = 1;
        }
        loadIncidentReportData(undefined,startDate,endDate,SafeguardingData, searchData,incident_typeData,incident_statusData);
    });
    $(document).on('change','#incident_typeFileter',function(){
        incident_typeData = $(this).val();
        loadIncidentReportData(undefined,startDate,endDate,SafeguardingData, searchData,incident_typeData,incident_statusData);
    });
    $(document).on('change','#incident_statusFilter',function(){
        incident_statusData = $(this).val();
        loadIncidentReportData(undefined,startDate,endDate,SafeguardingData, searchData,incident_typeData,incident_statusData);
    });
    function clearFilterData(){
        location.reload();
    }
    </script>
</main>

@endsection