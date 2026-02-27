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
                            <h1 class="mainTitlep">Client Invoicing</h1>
                            <p class="header-subtitle mb-0">Manage client invoices and track payments</p>
                        </div>
                        <div>
                            <button class="bgBtn"><i class="bx  bx-plus me-2"></i>Create Invoice</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt20">
                <div class="col-lg-3">
                    <div class="rota_dash-card gradp-blue-50 p-4 lightBorderp rouded8">
                        <div class="rota_dash-left w100">
                            <div class="mb-3">
                                <i class="bx bx-file-detail fs30 textBlue"></i>

                            </div>
                            <p class="fs13 textBlue">
                                Total Invoiced </p>
                            <h1 class="h1Pay700 darkBlueTextp mt-0">£13,312</h1>

                        </div>
                    </div>

                </div>
                <div class="col-lg-3">
                    <div class="rota_dash-card gradp-orange-50 p-4 lightBorderp rouded8">
                        <div class="rota_dash-left w100">
                            <div class="mb-3">
                                <i class="bx bx-dollar fs30 orangeText"></i>
                            </div>
                            <p class="fs13 orangeText">
                                Outstanding </p>
                            <h1 class="h1Pay700 darkOrangeTextp mt-0">£928</h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">

                    <div class="rota_dash-card gradp-green-50 p-4 lightBorderp rouded8">
                        <div class="rota_dash-left w100">
                            <div class="mb-3">
                                <i class="bx  bx-check-circle fs30 greenText"></i>

                            </div>
                            <p class="fs13 greenText">
                                Paid
                            </p>
                            <h1 class="h1Pay700 darkGreenTextp mt-0">£8,500</h1>

                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="rota_dash-card gradp-red-50 p-4 lightBorderp rouded8">
                        <div class="rota_dash-left w100">
                            <div class="mb-3">
                                <i class="bx  bx-alert-triangle fs30 redtext"></i>
                            </div>
                            <p class="fs13 redtext">Overdue </p>
                            <h1 class="h1Pay700 textRedp mt-0">0</h1>

                        </div>
                    </div>

                </div>

            </div>
            <div class="row mt20">
                <div class="col-lg-12">
                    <div class="emergencyMain p-4">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="input-group searchWithtabs" style="width:100%">
                                    <span class="input-group-addon btn-white"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control"
                                        placeholder="Search by client or invoice number...">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <select class="form-control">
                                    <option>All Status</option>
                                    <option>Draft</option>
                                    <option>Sent</option>
                                    <option>Paid</option>
                                    <option>Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">

                    <div class="bBorderCard mt-4 p24">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex gap-3 mb-3">
                                    <h5 class="h5Head">INV-2025-005
                                    </h5>
                                    <div><span class="careBadg greenbadges">Paid</span></div>
                                </div>
                                <h6 class="h6Head textGray mb-0">
                                    Unknown Client
                                </h6>

                            </div>
                            <div>
                                <button class="borderBtn"><i class="bx bx-arrow-to-bottom-stroke me-2 f18"></i>
                                    PDF</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Invoice Date</p>
                                    <h6 class="h6Head">2</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Period</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Due Date</p>
                                    <h6 class="h6Head">16 Decemeber</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Total Amount</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="bBorderCard mt-4 p24">

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="d-flex gap-3 mb-3">
                                    <h5 class="h5Head">INV-2025-005
                                    </h5>
                                    <div><span class="careBadg yellowBadges">Partially Paid</span></div>
                                </div>
                                <h6 class="h6Head textGray mb-0">
                                    Unknown Client
                                </h6>

                            </div>
                            <div>
                                <button class="borderBtn"><i class="bx bx-arrow-to-bottom-stroke me-2 f18"></i>
                                    PDF</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Invoice Date</p>
                                    <h6 class="h6Head">2</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Period</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Due Date</p>
                                    <h6 class="h6Head">16 Decemeber</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Total Amount</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- split billing purple -->
                    <div class="bBorderCard mt-4 p24">

                        <div class="d-flex justify-content-between  align-items-center">
                            <div>
                                <div class="d-flex gap-3">
                                    <h5 class="h5Head">INV-2025-005
                                    </h5>
                                    <div class="mb-3"><span class="careBadg buleBadges">Sent</span></div>
                                </div>
                                <h6 class="h6Head textGray mb-0">
                                    Unknown Client
                                </h6>

                            </div>
                            <div>
                                <div>
                                    <button class="bgBtn pgreenBtn w100"><i class="bx  bx-check-circle me-2 f18"></i>
                                        Mark Paid</button>

                                </div>

                                <div class="mt-3">
                                    <button class="borderBtn w100"><i class="bx bx-arrow-to-bottom-stroke me-2 f18"></i>
                                        PDF</button>
                                </div>


                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Invoice Date</p>
                                    <h6 class="h6Head">2</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Period</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Due Date</p>
                                    <h6 class="h6Head">16 Decemeber</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Total Amount</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded8 mt-4" style="width:92%">
                            <p class="fs13 font600 purpleTextp">Split Billing:</p>
                            <div class="d-flex gap-3 flexWrap">
                                <div><span class="careBadg purpleBadges">Local Authority - Manchester: £204.8
                                        (80%)</span>
                                </div>
                                <div><span class="careBadg purpleBadges">Emma Williams (Self): £51.2 (20%)</span>
                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- split billing purple end -->
                    <div class="bBorderCard mt-4 p24">

                        <div class="d-flex justify-content-between  align-items-center">
                            <div>
                                <div class="d-flex gap-3 mb-3">
                                    <h5 class="h5Head">INV-2025-005
                                    </h5>
                                    <div><span class="careBadg buleBadges">Sent</span></div>
                                </div>
                                <h6 class="h6Head textGray mb-0">
                                    Unknown Client
                                </h6>

                            </div>
                            <div>
                                <div>
                                    <button class="bgBtn blackBtn w100"><i class="bx bx-send me-2 f18"></i>
                                        Send
                                    </button>

                                </div>

                                <div class="mt-3">
                                    <button class="borderBtn w100"><i class="bx bx-arrow-to-bottom-stroke me-2 f18"></i>
                                        PDF</button>
                                </div>


                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Invoice Date</p>
                                    <h6 class="h6Head">2</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Period</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Due Date</p>
                                    <h6 class="h6Head">16 Decemeber</h6>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <p class="textGray fs13">Total Amount</p>
                                    <h6 class="h6Head">16.0</h6>
                                </div>
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded8 mt-4" style="width:92%">
                            <p class="fs13 font600 purpleTextp">Split Billing:</p>
                            <div class="d-flex gap-3 flexWrap">
                                <div><span class="careBadg purpleBadges">Local Authority - Manchester: £204.8
                                        (80%)</span>
                                </div>
                                <div><span class="careBadg purpleBadges">Emma Williams (Self): £51.2 (20%)</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- split billing purple end -->
                </div>
            </div>
        </div>
    </main>

@endsection