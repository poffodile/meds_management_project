@extends('frontEnd.layouts.master')
@section('title', 'Payroll Process')
@section('content')

    @include('frontEnd.roster.common.roster_header')
    <main class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="staffHeaderp">
                        <div>
                            <h1 class="mainTitlep">Payroll Processing</h1>

                            <p class="header-subtitle mb-0">Generate payslips and export to accounting software </p>
                        </div>
                        <div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="bBorderCard mt20 p24">
                        <h5 class="h5Head"> Processing Steps:</h5>
                        <ol class="orderListpayPro">
                            <li class="textGray fs13">Ensure all timesheets are approved for the period
                            </li>
                            <li class="textGray fs13">Click "Process Payroll" for the desired period </li>
                            <li class="textGray fs13">System calculates gross pay, deductions, and net pay

                            </li>
                            <li class="textGray fs13">Holiday accruals are automatically calculated and updated </li>
                            <li class="textGray fs13">NMW compliance is verified for all staff
                            </li>
                            <li class="textGray fs13">Export to Sage/Xero for payment processing

                            </li>
                        </ol>
                    </div>
                    <div class="bBorderCard greenBorderClr mt-4 p-0">
                        <div class="muteBg bottomRadUnset p24 rounded12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="h3Head">Week 5 - February 2025</h3>
                                    <div class="mb-3"><span class="careBadg greenbadges">processed</span></div>
                                    <p class="textGray fs13">Feb 3 - Feb 9, 2025 </p>
                                    <p class="h7Head textGray"> Pay Date: Friday, Feb 14, 2025 </p>
                                </div>
                                <div>
                                    <p class="textGray fs13">Total </p>
                                    <h5 class="h5Bold greenText">
                                        £194.4 </h5>
                                    <p class="textGray fs13">Total Net </p>
                                    <h5 class="h5Head">£122.472</h5>
                                </div>
                            </div>

                        </div>
                        <div class="p24">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Staff</p>
                                        <h5 class="h5Head">2</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Total Hours</p>
                                        <h5 class="h5Head">16.0</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Approved Timesheets</p>
                                        <h5 class="h5Head greenText">16.0</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Pending</p>
                                        <h5 class="h5Head redtext">16.0</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex gap-3">
                                    <div>
                                        <button class="borderBtn"> <i
                                                class="bx bx-arrow-to-bottom-stroke f18 me-3"></i>Export to
                                            CSV</button>
                                    </div>
                                    <div>
                                        <button class="borderBtn"> <i class="bx bx-file-detail f18 me-3"></i>View
                                            Payslips</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bBorderCard greenBorderClr mt-4 p-0">
                        <div class="muteBg  bottomRadUnset p24 rounded12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h3 class="h3Head">Week 5 - February 2025</h3>
                                    <div class="mb-3"><span class="careBadg greenbadges">processed</span></div>
                                    <p class="textGray fs13">Feb 3 - Feb 9, 2025 </p>
                                    <p class="h7Head textGray"> Pay Date: Friday, Feb 14, 2025 </p>
                                </div>
                                <div>
                                    <p class="textGray fs13">Total </p>
                                    <h5 class="h5Bold greenText">
                                        £194.4 </h5>
                                    <p class="textGray fs13">Total Net </p>
                                    <h5 class="h5Head">£122.472</h5>
                                </div>
                            </div>

                        </div>
                        <div class="p24">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Staff</p>
                                        <h5 class="h5Head">2</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Total Hours</p>
                                        <h5 class="h5Head">16.0</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Approved Timesheets</p>
                                        <h5 class="h5Head greenText">16.0</h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <p class="textGray fs13">Pending</p>
                                        <h5 class="h5Head redtext">16.0</h5>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex gap-3">
                                    <div>
                                        <button class="bgBtn pgreenBtn"><i class="bxr  bx-caret-big-right me-3 f20"></i>
                                            Process Payroll</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Client Invoicing start -->

@endsection