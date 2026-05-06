@extends('frontEnd.layouts.master')
@section('title', 'Incident Management')
@section('content')

    @include('frontEnd.roster.common.roster_header')

    <main class="page-content">

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <button class="borderBtn" type="button" onclick="window.location.href='{{url('/roster/incident-management')}}'"><i class=" f18 bx  bx-arrow-left-stroke"></i> Back to Incidents</button>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="bBorderCard urReqSec emergencyHeader  incidentDetailHead">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="mb-2">
                                    <h5 class="h5Head mb-3"><i class="fs23  bx bx-shield me-3"></i>{{$report_details->incidentType->type}}</h5>
                                    <div class="d-flex align-items-center gap-3">
                                        <div>
                                            {{$severity}}
                                        </div>

                                        <div>
                                            {{$statushtml}}
                                        </div>
                                        @if($report_details->is_safeguarding == 1)
                                        <div>
                                            <span class="careBadg redDarkBadgesAni">SAFEGUARDING</span>
                                        </div>
                                        @endif
                                        @if($report_details->cqcNotification == 1)
                                        <div>
                                            <span class="careBadg purpleBadgesDark">CQC NOTIFIABLE</span>
                                        </div>
                                        @endif
                                        <div class="userMum">
                                            <span class="title mt-0">
                                                <span>Ref: </span> {{$report_details->ref}}
                                            </span>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div>
                                <a href="#Edit"
                                    class="borderBtn"><i class="bx bx-pencil f18 me-3 "></i> Edit</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row mt20">
                <!-- left part -->
                <div class="col-lg-8">
                    <div class="emergencyMain">
                        <div class="cardHeaderp p24">
                            <h5 class="h5Head mb-0">
                                Incident Details
                            </h5>
                        </div>
                        <div class="incidentDeCon p24">
                            <p><strong>What Happened</strong> </p>
                            <p>{{$report_details->what_happened}} </p>
                            <div class="bg-blue-50 p-4 mt-4">
                                <p><strong>Immediate Action Taken</strong></p>
                                <p>{{$report_details->immediate_action}}</p>
                            </div>
                        </div>
                        <div class="icndentDeFooter p24">
                            <div class="d-flex">
                                <div class="w50">
                                    <p><strong>Date & Time</strong></p>
                                    <p>{{date("d/m/Y H:i",strtotime($report_details->date_time))}}</p>
                                    <!-- 14/01/2026 14:35 -->
                                </div>
                                <div>
                                    <p><strong>Location</strong></p>
                                    <p class="mb-0">{{$report_details->location}}</p>
                                    <small class="muteText">({{$report_details->location_detail ?? ""}})</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- safeGuarding -->
                    <div class="emergencyMain bg-red-50 IncidentDetailsafe mt20">
                        <div class="cardHeaderp p24">
                            <h5 class="h5Head mb-0">
                                <i class="bx bx-alert-triangle fs23 me-2"></i> Safeguarding Concern
                            </h5>
                        </div>
                        <div class="incidentDeCon p24">
                            <p><strong>Types of Concern:</strong> </p>
                            <div class="parentRedBad">
                                <div>
                                    <span class="careBadg redDarkBadges">physical abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">emotional abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">sexual abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">
                                        financial abuse

                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">discriminatory abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">modern slavery
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">organisational abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">neglect
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">domestic abuse
                                    </span>
                                </div>
                                <div>
                                    <span class="careBadg redDarkBadges">self neglect
                                    </span>
                                </div>
                            </div>
                            <div class="incidentRrqAction p-4 mt-4">
                                <strong>Required Actions:</strong>
                                <ul>
                                    <li>Notify Local Authority Safeguarding Team immediately</li>
                                    <li>Complete safeguarding investigation</li>
                                    <li>Notify CQC if serious harm or risk</li>
                                    <li>Document all actions taken</li>
                                    <li>Consider police involvement if criminal offense</li>
                                </ul>
                            </div>
                        </div>

                    </div>
                    <!-- Investigation & Resolution -->
                    <div class="emergencyMain mt20">
                        <div class="cardHeaderp p24">
                            <h5 class="h5Head mb-0">
                                Investigation & Resolution </h5>
                        </div>
                        <div class="incidentDeCon p24">
                            <p class="muteText text-center">No investigation details recorded yet </p>
                            <div class="carer-form">
                                <form action="">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label>Status</label>
                                            <select class="form-control">
                                                <option>Under Investigation</option>
                                                <option>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Investigation Findings</label>
                                            <textarea name="morning" required="" class="form-control" rows="3" cols="20"
                                                placeholder="Document investigation findings..."></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Resolution Notes</label>
                                            <textarea name="morning" required="" class="form-control" rows="3" cols="20"
                                                placeholder="How was this resolved?"></textarea>
                                        </div>
                                        <div class="col-md-12 m-t-10">
                                            <label>Lessons Learned</label>
                                            <textarea name="morning" required="" class="form-control" rows="3" cols="20"
                                                placeholder="What did we learn from this incident?"></textarea>
                                        </div>
                                        <div class="col-lg-12 m-t-10">
                                            <button class="bgBtn pgreenBtn" style="width:100%"><i
                                                    class="bx bx-save me-3 f18"></i>
                                                Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <!-- AI incident Report -->
                    <div class="emergencyMain mt20 aiInciDetaReport">
                        <div class="cardHeaderp aIInsightsheader p24">
                            <h5 class="h5Head mb-0">
                                <i class="bx bx-sparkles me-2 fs23 "></i> AI Incident Report
                            </h5>
                        </div>
                        <div class="p24">
                            <div class="incidentDeCon">
                                <h5 class="h5Head"><i class="bx bx-alert-circle fs23 me-2 redIColor"></i> Root Cause
                                    Analysis
                                </h5>

                                <div class="incidentRrqAction p-4 mt-4">
                                    <p> <strong>Primary Cause</strong></p>
                                    <p>Lack of safety measures in place to prevent falls</p>
                                    <p><strong>Contributing Factors</strong></p>

                                    <ul>
                                        <li>Inadequate training for staff on safety protocols</li>
                                        <li>Absence of proper signage indicating hazardous areas</li>
                                        <li>Failure to conduct regular safety inspections</li>

                                    </ul>
                                    <p> <strong>Analysis Summary</strong></p>
                                    <p>The incident of the fall, categorized as critical, appears to stem from a lack of
                                        established safety measures that could have prevented the occurrence. Although no
                                        individuals were directly involved at the time of the incident, the ramifications
                                        could have included severe injuries. The contributing factors indicate a systemic
                                        failure to prioritize safety, including insufficient training and oversight.
                                    </p>
                                </div>

                            </div>
                            <!-- impact assesment -->
                            <div class="mt20">
                                <h5 class="h5Head"><i class="bx bx-alert-circle fs23 me-2" style="color:#ea580c"></i>Impact
                                    Assessment
                                </h5>
                                <div class="inDeimpactAssest bg-orange-50 p-4 mt-4">
                                    <p>
                                        <strong>Severity Level: <span
                                                class="ms-2 careBadg yellowBadges">Critical</span></strong>
                                    </p>
                                    <p> <strong>Affected Parties</strong></p>
                                    <div class="parentRedBad mb-3">

                                        <div class="userMum">
                                            <span class="title mt-0">
                                                <span><i class="bx bx-group"></i> </span> Potential victims (employees,
                                                visitors)
                                            </span>
                                        </div>
                                        <div class="userMum">
                                            <span class="title mt-0">
                                                <span><i class="bx bx-group"></i> </span>Management
                                            </span>
                                        </div>
                                        <div class="userMum">
                                            <span class="title mt-0">
                                                <span><i class="bx bx-group"></i> </span> Health and safety regulators

                                            </span>
                                        </div>

                                    </div>
                                    <p class="mb-2"> <strong>Potential Consequences</strong></p>
                                    <p>Severe injuries or fatalities, legal action, reputational damage, increased insurance
                                        premiums</p>
                                    <p class="mb-2"> <strong>Regulatory Implications</strong></p>
                                    <p>Possible investigations and penalties from health and safety regulatory bodies due to
                                        non-compliance with safety regulations.</p>
                                </div>
                            </div>
                            <!--prevent measure  -->

                            <div class="mt20">
                                <h5 class="h5Head"><i class="bx bx-check-circle fs23 me-2" style="color:#16a34a"></i> Root
                                    Preventive Measures
                                </h5>
                                <div class="mt-4">
                                    <div class="inDeimpactAssest bg-greenp-50 p-4 mt-3">
                                        <div class="d-flex justify-content-between">
                                            <div>

                                                <h6 class="h6Head">Implement comprehensive safety training programs for all
                                                    staff</h6>
                                                <p>
                                                <div class="d-flex gap-4">
                                                    <p> <i class="bx bx-clock f18 me-1"></i> Within 3 months</p>
                                                    <p> <i class="bx bx-group f18 me-1"></i>Safety Officer</p>
                                                </div>
                                            </div>
                                            <div>
                                                <p>
                                                    <span class="ms-2 careBadg yellowBadges">Critical</span>
                                                </p>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="inDeimpactAssest bg-greenp-50 p-4 mt-3">
                                        <div class="d-flex justify-content-between">
                                            <div>

                                                <h6 class="h6Head">Install clear signage to warn of potential hazards</h6>
                                                <p>
                                                <div class="d-flex gap-4">
                                                    <p> <i class="bx bx-clock f18 me-1"></i> Within 3 months</p>
                                                    <p> <i class="bx bx-group f18 me-1"></i>Safety Officer</p>
                                                </div>
                                            </div>
                                            <div>
                                                <p>
                                                    <span class="ms-2 careBadg yellowBadges">Critical</span>
                                                </p>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="inDeimpactAssest bg-greenp-50 p-4 mt-3">
                                        <div class="d-flex justify-content-between">
                                            <div>

                                                <h6 class="h6Head">Conduct regular safety inspections and audits to evaluate
                                                    risk areas

                                                </h6>
                                                <p>
                                                <div class="d-flex gap-4">
                                                    <p> <i class="bx bx-clock f18 me-1"></i> Within 3 months</p>
                                                    <p> <i class="bx bx-group f18 me-1"></i>Safety Officer</p>
                                                </div>
                                            </div>
                                            <div>
                                                <p>
                                                    <span class="ms-2 careBadg yellowBadges">Critical</span>
                                                </p>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="inDeimpactAssest bg-greenp-50 p-4 mt-4">
                                        <div class="d-flex justify-content-between">
                                            <div>

                                                <h6 class="h6Head"> Establish a safety committee to review and oversee
                                                    safety protocols</h6>
                                                <p>
                                                <div class="d-flex gap-4">
                                                    <p> <i class="bx bx-clock f18 me-1"></i> Within 3 months</p>
                                                    <p> <i class="bx bx-group f18 me-1"></i>Safety Officer</p>
                                                </div>
                                            </div>
                                            <div>
                                                <p>
                                                    <span class="ms-2 careBadg yellowBadges">Critical</span>
                                                </p>
                                            </div>
                                        </div>


                                    </div>

                                    <div class="incidentDeCon">
                                        <div class="bg-blue-50 p-4 mt-4">
                                            <h6 class="h6Head" style="color:#1e40af"> <i
                                                    class="bx bx-check-circle fs23 me-2"></i>Follow-Up Tasks Created

                                            </h6>
                                            <p>4 compliance tasks have been created and assigned to staff members. View them
                                                in the Compliance Task Center.</p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- right part -->
                <div class="col-lg-4">
                    <div class="inciDeRight">
                        <div class="emergencyMain">
                            <div class="cardHeaderp cyanGrad p24">
                                <h6 class="h6Head mb-0">
                                    Client Information
                                </h6>
                            </div>
                            <div class="p24">
                                <p> <strong>Ruby Donavan</strong> </p>
                                <p class="muteText">
                                    <i class="bx bx-phone f18 me-1"></i> 0780000001
                                </p>

                            </div>

                        </div>
                        <div class="emergencyMain mt20">
                            <div class="cardHeaderp p24">
                                <h6 class="h6Head mb-0">
                                    Notifications</h6>
                            </div>
                            <div class="p-4">
                                <div class="muteBg rounded8 p-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0"> <strong>Family Notified</strong> </p>
                                        <div>
                                            <i class="bx bx-check-circle f18 me-2" style="color:#16a34a"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-purple-50 rounded8 p-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0"> <strong>CQC Notification</strong> </p>
                                        <div>
                                            <i class="bx bx-alert-triangle  f18 me-2 redIColor"></i>
                                        </div>
                                    </div>

                                </div>
                                <div class="bg-red-50 rounded8  p-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0"> <strong>Police Involved</strong> </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="emergencyMain maincqcInde bg-purple-50 mt20">
                            <div class="cardHeaderp p24">
                                <h6 class="h6Head mb-0">CQC Requirements</h6>
                            </div>
                            <div class="listcqcInDe p-3">
                                <p>This incident requires statutory notification to CQC</p>
                                <ul>
                                    <li>Notify without delay</li>
                                    <li>Use CQC notification portal</li>
                                    <li>Provide full details</li>
                                    <li>Keep reference number</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--  AI Root Cause Analysis -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="emergencyMain aiInciDetaReport mt20">
                        <div class="cardHeaderp aIInsightsheader p24">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="h5Head mb-0">
                                    <i class="bx bx-sparkles me-2 fs23 "></i> AI Root Cause Analysis
                                </h5>
                                <div class="d-flex gap-3">
                                    <div>
                                        <button class="bgBtn aiBtnFst">Run AI Analysis</button>
                                    </div>
                                    <div>
                                        <button class="borderBtn pupleBorderBtn" type="button">Generate Tasks</button>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="leavebanktabCont">
                            <i class="bx bx-sparkles"></i>
                            <h4>AI-Powered Incident Intelligence</h4>
                            <p>Click "Run AI Analysis" to get root cause insights and preventative recommendations</p>
                        </div>
                        <div class="p24 aiRootCauseCon">
                            <div class="mt20">
                                <h5 class="h5Head"><i class="bx bx-alert-circle fs23 me-2 redIColor"></i> Potential Root
                                    Causes</h5>
                            </div>
                            <div class="p-4 rounded8 bg-red-50 mt-4 rtcozCardInDe">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="h6Head">Immediate Cause</h6>
                                        <p class="text-sm para">Wet floor leading to loss of balance and fall </p>
                                    </div>
                                    <div>
                                        <span class="careBadg redbadges">High Likelihood</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded8 bg-red-50 mt-4 rtcozCardInDe">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="h6Head">Underlying Cause</h6>
                                        <p class="text-sm para">Lack of proper signage to indicate slippery floors or recent
                                            cleaning </p>
                                    </div>
                                    <div>
                                        <span class="careBadg yellowBadges">Medium Likelihood</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded8 bg-red-50 mt-4 rtcozCardInDe">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="h6Head">Underlying Cause</h6>
                                        <p class="text-sm para">Insufficient staff training on safety protocols in areas
                                            prone to falls </p>
                                    </div>
                                    <div>
                                        <span class="careBadg redbadges">High Likelihood</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded8 bg-red-50 mt-4 rtcozCardInDe">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="h6Head">Underlying Cause</h6>
                                        <p class="text-sm para">Failure to conduct routine safety inspections</p>
                                    </div>
                                    <div>
                                        <span class="careBadg yellowBadges">Medium Likelihood</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head">Contributing Factors
                                </h5>
                            </div>
                            <div class="p-4 rounded8 bg-orange-50 mt-4 rtcozCardInDe">
                                <p class="text-sm para"><i class="dotC me-2"></i> Inadequate lighting in the area</p>
                            </div>
                            <div class="p-4 rounded8 bg-orange-50 mt-4 rtcozCardInDe">
                                <p class="text-sm para"><i class="dotC me-2"></i> Presence of obstacles (furniture,
                                    equipment) near walkways</p>
                            </div>
                            <div class="p-4 rounded8 bg-orange-50 mt-4 rtcozCardInDe">
                                <p class="text-sm para"><i class="dotC me-2"></i> Lack of immediate access to assistance
                                    post-fall</p>
                            </div>
                            <div class="p-4 rounded8 bg-orange-50 mt-4 rtcozCardInDe">
                                <p class="text-sm para"><i class="dotC me-2"></i> Staff overwhelmed with care duties,
                                    limiting safety oversight</p>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head"> <i class="bx bx-shield fs23 me-2" style="color:#1e40af"></i>Immediate
                                    Actions Recommended</h5>
                            </div>
                            <div class="p-4 rounded8 bg-blue-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i> Conduct a thorough assessment of the fall site
                                    to gather more details</h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div> <span class="careBadg darkBlueBadg ">Assign to: Jane Wakefield</span></div>
                                    <div>
                                        <p class="fs13 textBlue">As the only available administrative personnel
                                            with the authority to initiate the response.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded8 bg-blue-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i> Ensure all cleaning staff are reminded to use
                                    appropriate signage after mopping or cleaning floors.</h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div> <span class="careBadg darkBlueBadg ">Assign to: Jane Wakefield</span></div>
                                    <div>
                                        <p class="fs13 textBlue">As the only available administrative personnel
                                            with the authority to initiate the response.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded8 bg-blue-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i>Notify all staff of the incident through a
                                    safety briefing to raise awareness of fall risks.</h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div> <span class="careBadg darkBlueBadg ">Assign to: Shaheem Navad</span></div>
                                    <div>
                                        <p class="fs13 textBlue">Shaheem can mobilize the rest of the care staff for an
                                            urgent briefing due to his role.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt20">
                                <h5 class="h5Head"> <i class="bx bx-light-bulb fs23 me-2"
                                        style="color:#16a34a"></i>Preventative Measures</h5>
                            </div>
                            <div class="p-4 rounded8 bg-greenp-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i>Implement a formal cleaning protocol that
                                    includes clear signage and staff notification during and after cleaning.</h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div class="d-flex gap-4">
                                        <p class="muchsmallText"><span>Timeframe:</span>Within 1 month</p>
                                        <p class="muchsmallText"><span>Responsibility:</span>Manager</p>
                                    </div>
                                    <div> <span class="careBadg darkGreenBadges">Assign to: Shaheem Navad</span></div>
                                </div>
                                <p class="fs13 textGreen">Sarah is suited to coordinate staff trainings as she holds a
                                    relevant care position.

                                </p>
                            </div>
                            <div class="p-4 rounded8 bg-greenp-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i>Implement a formal cleaning protocol that
                                    includes clear signage and staff notification during and after cleaning.</h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div class="d-flex gap-4">
                                        <p class="muchsmallText"><span>Timeframe:</span>Within 1 month</p>
                                        <p class="muchsmallText"><span>Responsibility:</span>Manager</p>
                                    </div>
                                    <div> <span class="careBadg darkGreenBadges">Assign to: Shaheem Navad</span></div>
                                </div>
                                <p class="fs13 textGreen">Sarah is suited to coordinate staff trainings as she holds a
                                    relevant care position.

                                </p>
                            </div>
                            <div class="p-4 rounded8 bg-greenp-50 mt-4 rtcozCardInDe">
                                <h6 class="h7Head"><i class="dotC me-2"></i>Create a checklist for staff to complete after
                                    cleaning that includes checking for hazards and placing warning signs.

                                </h6>
                                <div class="d-flex flexWrap gap-3">
                                    <div class="d-flex gap-4">
                                        <p class="muchsmallText"><span>Timeframe:</span>Within 1 month</p>
                                        <p class="muchsmallText"><span>Responsibility:</span>Manager</p>
                                    </div>
                                    <div> <span class="careBadg darkGreenBadges">Assign to: Shaheem Navad</span></div>
                                </div>
                                <p class="fs13 textGreen">Emma is an admin staff member who can effectively manage
                                    procedural documentation.
                                </p>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head">Long-term Systemic Improvements</h5>
                            </div>
                            <div class="p-4 rounded8 mt-4 rtcozCardInDe">
                                <div class="userMum">
                                    <span class="title mt-0">
                                        Staff Training and Development
                                    </span>
                                </div>
                                <p class="text-sm para mt-2">Develop a comprehensive training program focusing on safety
                                    protocols and emergency response for all staff.</p>
                                <p class="muchsmallText"><span>Expected Impact:</span> This ensures staff are prepared to
                                    prevent and appropriately respond to similar incidents.
                                </p>
                            </div>
                            <div class="p-4 rounded8 mt-4 rtcozCardInDe">
                                <div class="userMum">
                                    <span class="title mt-0"> Communication Protocols </span>
                                </div>
                                <p class="text-sm para mt-2">Develop a comprehensive training program focusing on safety
                                    protocols and emergency response for all staff.</p>
                                <p class="muchsmallText"><span>Expected Impact:</span> This ensures staff are prepared to
                                    prevent and appropriately respond to similar incidents.
                                </p>
                            </div>
                            <div class="p-4 rounded8 mt-4 rtcozCardInDe">
                                <div class="userMum">
                                    <span class="title mt-0"> Safety Management </span>
                                </div>
                                <p class="text-sm para mt-2">Introduce a dedicated safety officer role to oversee compliance
                                    with
                                    safety measures and conduct regular audits.</p>
                                <p class="muchsmallText"><span>Expected Impact: </span> This creates ongoing safety
                                    awareness and accountability among the staff.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
@endsection