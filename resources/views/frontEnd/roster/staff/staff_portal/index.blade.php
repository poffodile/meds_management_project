@extends('frontEnd.layouts.master')
@section('title', 'Staff Portal')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container-fluid">
        <div class="row d-flex align-items-center">
            <div class="col-md-5">
                <div class="staffHeaderp">
                    <div>
                        <h1 class="mainTitlep">Staff Portal</h1>
                        <p class="header-subtitle mb-0">Welcome back, vipin </p>
                    </div>

                </div>
            </div>
            <div class="col-lg-7">
                <div class="staffPortanEmer">
                    <div class="clntalertheader p24 rounded8">
                        <h5 class="mb-0 h5Head"> <i class="bx bx-shield fs23 darkRedText"></i> Emergency</h5>
                    </div>
                    <div class="p24">
                        <p class="fs13 textGray500">If you need immediate assistance, press the SOS button. This will alert all managers with your location. </p>
                        <button class="bgBtn bgRedBtn py-4 w100" data-toggle="modal" data-target="#requestHelpModal"> <i class="bx bx-shield f18"></i> SOS - Request Help</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-lg-12">
                <!-- main tab -->
                <div class="tabContainerp mainTabstaffPortal">
                    <div class="shadowp lightBorderp rounded8">
                        <div class="muteBg p-3 scrollTabX">
                            <div class="tabs p-1">
                                <button class="tab active" data-tab="myShiftsTab"><i class="bx bx-calendar-week"></i> My Shifts</button>
                                <button class="tab" data-tab="careTasksTab"><i class="bx bx-heart"></i> Care Tasks</button>
                                <button class="tab" data-tab="clockTab"><i class="bx bx-clock"></i> Clock</button>
                                <button class="tab" data-tab="availabilityTab"><i class="bx bx-calendar-week"></i> Availability</button>
                                <button class="tab" data-tab="leaveTab"><i class="bx bx-calendar-week"></i> Leave</button>
                                <button class="tab" data-tab="requestsTab"><i class="bx bx-message"></i> Requests</button>
                                <button class="tab" data-tab="payslipsTab"><i class="bx bx-pound"></i> Payslips</button>
                                <button class="tab" data-tab="safeguardingTab"><i class="bx bx-shield"></i> Safeguarding</button>
                                <button class="tab" data-tab="confidentialTab"><i class="bx bx-lock"></i> Confidential</button>
                                <button class="tab" data-tab="updatesTab"><i class="bx bx-send"></i> Updates</button>
                                <button class="tab" data-tab="photosTab"><i class="bx bx-camera"></i> Photos</button>
                                <button class="tab" data-tab="offlineTab"><i class="bx bx-wifi"></i> Offline</button>
                            </div>
                        </div>
                        <!-- tab content -->
                        <div class="tab-content p24 bgWhite">
                            <div class="content active" id="myShiftsTab">
                                <!-- shift content -->
                                <div class="shadowp rounded8 lightBorderp bgWhite">
                                    <div class="careTaskheader p24 rounded8TR">
                                        <h2 class=""><i class="bx bx-calendar-week me-2"></i>My Shift</h2>
                                    </div>
                                    <div class="p24">
                                        <div class="noData py-5" style="border: unset; box-shadow: unset;">
                                            <div>
                                                <i class="bx bx-calendar-week"></i>
                                                <p class="mb-0">No shifts assigned yet</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <!-- end shift content -->
                            </div>
                            <div class="content" id="careTasksTab">
                                <!--  care tasks content -->
                                <div class="noData">
                                    <div>
                                        <i class="bx bx-heart"></i>
                                        <p class="mb-0">No shifts or visits scheduled for today</p>
                                    </div>
                                </div>
                                <!-- end care tasks content -->
                            </div>
                            <div class="content" id="clockTab">
                                <!--  clock content -->
                                <div class="noData py-5" style="border: unset; box-shadow: unset;">
                                    <div>
                                        <i class="bx bx-clock"></i>
                                        <h5 class="h5Head font600 textGray500 mt-4 mb-3">No active shift selected </h5>
                                        <p class="mb-0">Select a shift from "My Shifts" to clock in/out</p>
                                    </div>
                                </div>
                                <!-- end clock content -->
                            </div>
                            <div class="content" id="availabilityTab">

                                <!--  availability content -->
                                <div class="noData">
                                    <div>
                                        <i class="bx bx-alert-circle"></i>
                                        <p class="mb-0">Loading your availability...</p>
                                    </div>
                                </div>
                                <!-- end availability content -->

                            </div>
                            <div class="content" id="leaveTab">
                                <!--  leave content -->
                                <div class="noData">
                                    <div>
                                        <i class="bx bx-calendar-week"></i>
                                        <h6 class="mt-4 mb-3 textGray500 fs16">No leave requests yet</h6>
                                        <p class="mb-0">Click "Request Leave" to create your first request</p>
                                    </div>
                                </div>
                                <!-- end leave content -->

                            </div>
                            <div class="content" id="requestsTab">
                                <!--  requests content -->
                                <div class="flexBw">
                                    <h2>My Shifts Requests</h2>
                                    <span class="borderBadg">0 Pending</span>
                                </div>
                                <!-- no data -->
                                <div class="noData mt20">
                                    <div>
                                        <i class="bx bx-calendar-week"></i>
                                        <h6 class="mt-4 mb-3 blackText fs16 font600">No Shift Requests </h6>
                                        <p class="mb-0">You don't have any shift requests at the moment</p>
                                    </div>
                                </div>
                                <!-- end requests content -->
                            </div>
                            <div class="content" id="payslipsTab">
                                <!--pay  slip data-->
                                <div class="noData py-5" style="border: unset; box-shadow: unset;">
                                    <div>
                                        <i class="bx bx-file-detail"></i>
                                        <h6 class="mb-3 mt-4 blackText fs16 font600">No Shift Requests </h6>
                                        <p class="mb-0">Your payslips will appear here once processed</p>
                                    </div>
                                </div>
                                <!-- end payslip data -->

                            </div>
                            <div class="content" id="safeguardingTab">
                                <!-- safeguarding content -->
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="flexBw">
                                            <h2><i class="bx bx-shield me-2 purpleTextp"></i>Safeguarding</h2>
                                            <div>
                                                <button class="bgBtn grayBtn cancelConnBtn" style="display: none;"> <i class="bx bx-alert-triangle"></i> Cancel</button>
                                                <button class="bgBtn bgRedBtn addReportBtn"> <i class="bx bx-alert-triangle"></i> Report a Concern</button>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- danger red section start-->

                                    <div class="col-lg-12">
                                        <div class="bBorderCard mt20 urReqSec ">
                                            <div class="d-flex gap-3 urReqCon">
                                                <div>
                                                    <i class="bx  bx-alert-triangle"></i>
                                                </div>
                                                <div>
                                                    <h5 class="h5Head">If Someone is in Immediate Danger</h5>
                                                    <div class="d-flex gap-4 mt-3 urReqDetails">

                                                        <span>Call 999 immediately. Do not wait. Then inform your manager and complete a safeguarding referral. </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end danger section -->
                                    <div class="col-lg-12">
                                        <div class="safeGuardConForm mt20 shadowp" style="display: none;">
                                            <div class="bg-purple-50 p24 rounded8TR" style="border: none;">
                                                <h5 class="h5Head mb-0">Report a Safeguarding Concern</h5>
                                            </div>
                                            <div class="p-4">
                                                <form action="">
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label for="" class="formLabel"> Type of Concern *</label>
                                                            <select name="" id="" class="form-control">
                                                                <option value="">Physical Abuse</option>
                                                                <option value="">Emotional and psychological Abuse</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-12 m-t-10">
                                                            <label for="" class="formLabel">Person at Risk</label>
                                                            <input type="text" class="form-control" placeholder="Name of person you are concerned about">
                                                        </div>
                                                        <div class="col-lg-12 m-t-10">
                                                            <label for="" class="formLabel">Description of Concern *</label>
                                                            <textarea class="form-control" rows="3" cols="15" placeholder="Describe what you have seen, heard or been told. Include dates, times and any witnesses if known."></textarea>
                                                        </div>
                                                        <div class="col-lg-12 m-t-10">
                                                            <label for="" class="formLabel">Any Witnesses? </label>
                                                            <input type="text" class="form-control" placeholder="Names of any witnesses (optional)">
                                                        </div>
                                                        <div class="col-lg-12 m-t-10">
                                                            <div class="dFlexGap">
                                                                <input type="checkbox" class="checkBoxHW" id="immediateDanger">
                                                                <label for="immediateDanger" class="formLabel mb-0">This person is in immediate danger</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12 m-t-10">
                                                            <button class="w100 bgBtn purpleBgBtn"> <i class="bx bx-send f18"></i> Submit Safeguarding Concern</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- safeguarding form section -->

                                    <!--end safeguarding form section -->
                                </div>


                                <!-- no data -->
                                <div class="noData mt20">
                                    <div>
                                        <i class="bx bx-shield"></i>
                                        <h6 class="mt-4 mb-3 blackText fs16 font600">No Safeguarding Issues</h6>
                                        <p class="mb-0">You don't have any safeguarding issues at the moment</p>
                                    </div>
                                </div>
                                <!-- end safeguarding content -->
                            </div>
                            <div class="content" id="confidentialTab">Confidential Data</div>
                            <div class="content" id="updatesTab">Updates Data</div>
                            <div class="content" id="photosTab">Photos Data</div>
                            <div class="content" id="offlineTab">Offline Data</div>

                        </div>
                        <!-- end tab content -->
                    </div>
                </div>
                <!-- main tab end -->
            </div>
        </div>
        <!-- request help modal -->
        <div class="modal fade leaveCommunStyle in" id="requestHelpModal" tabindex="-1" role="dialog" aria-labelledby="requestHelpModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="">
                        <div class="modal-header p24 " style="border-bottom: unset;">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h5 class="fs16 darkRedText font600"> <i class="bx bx-alert-triangle redtext f20"></i> Emergency Assistance</h5>
                        </div>
                        <div class="modal-body p24 pt-0">
                            <p class="fs15 font600 textGray600">This will immediately alert all managers to your location and situation.</p>


                            <div>
                                <label>You can add additional notes if needed:</label>
                                <textarea rows="3" cols="20" placeholder="Optional: Describe the situation..." class="form-control m-t-10"></textarea>
                            </div>

                        </div>
                        <div class="modal-footer p24 pt-0 dFlexGap justify-content-end" style="border-top: unset;">
                            <button class="borderBtn" data-dismiss="modal"> Cancel</button>
                            <button type="button" class="bgBtn bgRedBtn"> <i class="bx bx-shield f18 me-2"></i>Confirm SOS</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- end request help modal -->
        <!-- js for tab -->
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

        <!-- js for safe guarding  -->
        <script>
            const addReportBtn = document.querySelector(".addReportBtn");
            const cancelConnBtn = document.querySelector(".cancelConnBtn");
            const safeGuardConForm = document.querySelector(".safeGuardConForm");
            addReportBtn.addEventListener("click", () => {
                safeGuardConForm.style.display = "block";
                addReportBtn.style.display = "none";
                cancelConnBtn.style.display = "inline-block";
            })
            cancelConnBtn.addEventListener("click", () => {
                cancelConnBtn.style.display = "none";
                addReportBtn.style.display = "inline-block";
                safeGuardConForm.style.display = "none";
            })
        </script>
    </div>

</main>
@endsection