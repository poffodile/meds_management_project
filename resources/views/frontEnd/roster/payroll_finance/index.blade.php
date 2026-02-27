@extends('frontEnd.layouts.master')
@section('title', 'Payroll & Finance')
@section('content')

    @include('frontEnd.roster.common.roster_header')
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp">
                        <div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <i class="mainTitleIcon greenTextp bx bx-dollar"></i>
                                <h1 class="mainTitlep mb-0"> Payroll & Finance</h1>
                            </div>
                            <p class="header-subtitle mb-0"> Manage payroll, timesheets, and client invoicing</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <a href="{{ url('roster/timesheet-reconciliation') }}" class="bgBtn"><i
                                        class=" f18 bx bx-clock me-2"></i>
                                    Timesheet Reconciliation</a>
                            </div>

                            <div>
                                <a href="{{ url('roster/payroll-processing') }}" class="bgBtn pgreenBtn" type="button"><i
                                        class="f18 bx bx-file-detail me-2"></i>Process
                                    Payroll</a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-12">
                    <div class="bBorderCard mt-4 p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="h5Head">Week 3 - January 2025</h5>
                                <p class="textGray fs13">Jan 20 - Jan 26, 2025</p>
                                <div><span class="careBadg darkBlackBadg">draft</span></div>
                            </div>
                            <div>
                                <p class="textGray fs13">Pay Date </p>
                                <h5 class="h5Head mb-0 font700">Jan 31, 2025</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-3">
                    <a href="{{ url('roster/timesheet-reconciliation') }}">
                        <div class="rota_dash-card gradp-blue-50 p-4 lightBorderp rouded8">
                            <div class="rota_dash-left w100">
                                <div class="d-flex justify-content-between mb-3">
                                    <i class="bx bx-clock fs30 textBlue"></i>
                                    <i class="bx bx-trending-up f20 textBlue"></i>
                                </div>
                                <p class="fs13 textBlue">
                                    Total Hours (Approved)
                                </p>
                                <h1 class="h1Pay700 darkBlueTextp mt-0">169.4</h1>
                                <p class="muchsmallText textBlue">
                                    18 staff members
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-lg-3">
                    <a href="{{ url('roster/payroll-processing') }}">
                        <div class="rota_dash-card gradp-green-50 p-4 lightBorderp rouded8">
                            <div class="rota_dash-left w100">
                                <div class="d-flex justify-content-between mb-3">
                                    <i class="bx bx-dollar fs30 greenText"></i>
                                    <i class="bx bx-trending-up f20 greenText"></i>
                                </div>
                                <p class="fs13 greenText">
                                    Est. Payroll Cost
                                </p>
                                <h1 class="h1Pay700 darkGreenTextp mt-0">£2,322.72</h1>
                                <p class="fs13 greenText">
                                    Current period
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="{{ url('roster/timesheet-reconciliation') }}">
                        <div class="rota_dash-card gradp-orange-50 p-4 lightBorderp rouded8">
                            <div class="rota_dash-left w100">
                                <div class="d-flex justify-content-between mb-3">
                                    <i class="bx bx-file-detail fs30 orangeText"></i>
                                    <i class="bx-alert-triangle f20 orangeText"></i>
                                </div>
                                <p class="fs13 orangeText">
                                    Pending Timesheets
                                </p>
                                <h1 class="h1Pay700 darkOrangeTextp mt-0">0</h1>
                                <p class="fs13 orangeText"> Need approval </p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    <a href="{{ url('roster/invoice-management') }}">
                        <div class="rota_dash-card gradp-purple-50 p-4 lightBorderp rouded8">
                            <div class="rota_dash-left w100">
                                <div class="d-flex justify-content-between mb-3">
                                    <i class="bx bx-file-detail fs30 purpleTextp"></i>
                                    <i class="bx bx-trending-up f20 purpleTextp"></i>
                                </div>
                                <p class="fs13 purpleTextp">
                                    Outstanding Invoices
                                </p>
                                <h1 class="h1Pay700 darkPurpleTextp mt-0">£928</h1>
                                <p class="fs13 purpleTextp"> 4 invoices </p>
                            </div>
                        </div>
                    </a>

                </div>
            </div>
            <div class="row mt20 equalColRow">
                <div class="col-lg-4">
                    <div class="emergencyMain h100">
                        <a href="{{ url('roster/timesheet-reconciliation') }}" class="d-block h100">
                            <div class="cardHeaderp p24" style="background-color: #eef2ff;">
                                <h5 class="h5Head mb-0">
                                    <i class="bx bx-clock fs23 me-2 textBlue"></i>
                                    Timesheet Reconciliation
                                </h5>
                            </div>
                            <div class="p-4">
                                <p class="textGray fs13">
                                    Review and approve staff timesheets, handle adjustments
                                </p>
                                <div class="d-flex justify-content-between gap-3 mt-3 align-items-center">
                                    <div class="userMum">
                                        <span class="title mt-0">
                                            <span>0 </span> Pending
                                        </span>
                                    </div>
                                    <div>
                                        <button class="bgBtn blackBtn">Review <i class="bx bx-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="emergencyMain h100">
                        <a href="{{ url('roster/payroll-processing') }}" class="d-block h100">
                            <div class="cardHeaderp p24" style="background-color: #f0fdf4;">
                                <h5 class="h5Head mb-0">
                                    <i class="bx bx-dollar fs23 me-2 textGreen"></i>
                                    Process Payroll
                                </h5>
                            </div>
                            <div class="p-4">
                                <p class="textGray fs13">
                                    Generate payslips, calculate deductions, export to accounting
                                </p>
                                <div class="d-flex justify-content-between gap-3 mt-3 align-items-center">
                                    <div class="userMum">
                                        <span class="title mt-0">
                                            <span>18 </span> Staff
                                        </span>
                                    </div>
                                    <div>
                                        <button class="bgBtn blackBtn">Process <i class="bx bx-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="emergencyMain h100">
                        <a href="{{ url('roster/invoice-management') }}" class="d-block h100">
                            <div class="cardHeaderp p24" style="background-color: #faf5ff;">
                                <h5 class="h5Head mb-0">
                                    <i class="bx bx-file-detail fs23 me-2 purpleTextp"></i>
                                    Client Invoicing
                                </h5>
                            </div>
                            <div class="p-4">
                                <p class="textGray fs13">
                                    Create and manage client invoices, track payments
                                </p>
                                <div class="d-flex justify-content-between gap-3 mt-3 align-items-center">
                                    <div class="userMum">
                                        <span class="title mt-0">
                                            <span>4 </span> Outstanding
                                        </span>
                                    </div>
                                    <div>
                                        <button class="bgBtn blackBtn">Manage <i class="bx bx-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-12">
                    <div class="emergencyMain p24">
                        <h6 class="h6Head mb-3">Recent Payroll Activity</h6>
                        <div class="muteBg rounded8 p-4 mt-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <i class="bx  bx-check-circle fs23 greenText"></i>
                                    </div>
                                    <div>
                                        <h5 class="h5Head mb-2">
                                            Staff Member
                                        </h5>
                                        <p class="mb-0 textGray fs13 ">
                                            Nov 15, 2025
                                        </p>

                                    </div>
                                </div>
                                <div>
                                    <p class="h7Head mb-2">£718.2</p>
                                    <p class="mb-0  muchsmallText">Net Pay</p>
                                </div>
                            </div>
                        </div>
                        <div class="muteBg rounded8 p-4 mt-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <i class="bx  bx-check-circle fs23 greenText"></i>
                                    </div>
                                    <div>
                                        <h5 class="h5Head mb-2">
                                            Staff Member
                                        </h5>
                                        <p class="mb-0 textGray fs13">
                                            Nov 15, 2025
                                        </p>

                                    </div>
                                </div>
                                <div>
                                    <p class="h7Head mb-2">£718.2</p>
                                    <p class="mb-0  muchsmallText">Net Pay</p>
                                </div>
                            </div>
                        </div>
                        <div class="muteBg rounded8 p-4 mt-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <i class="bx  bx-check-circle fs23 greenText"></i>
                                    </div>
                                    <div>
                                        <h5 class="h5Head mb-2">
                                            Staff Member
                                        </h5>
                                        <p class="mb-0 textGray fs13">
                                            Nov 15, 2025
                                        </p>

                                    </div>
                                </div>
                                <div>
                                    <p class="h7Head mb-2">£718.2</p>
                                    <p class="mb-0  muchsmallText">Net Pay</p>
                                </div>
                            </div>
                        </div>
                        <div class="muteBg rounded8 p-4 mt-4">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <i class="bx  bx-check-circle fs23 greenText"></i>
                                    </div>
                                    <div>
                                        <h5 class="h5Head mb-2">
                                            Staff Member
                                        </h5>
                                        <p class="mb-0 textGray fs13">
                                            Nov 15, 2025
                                        </p>

                                    </div>
                                </div>
                                <div>
                                    <p class="h7Head mb-2">£718.2</p>
                                    <p class="mb-0  muchsmallText">Net Pay</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection