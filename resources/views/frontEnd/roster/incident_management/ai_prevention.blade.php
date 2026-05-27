@extends('frontEnd.layouts.master')
@section('title', 'Incident Management')
@section('content')

@include('frontEnd.roster.common.roster_header')

<main class="page-content">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <button class="borderBtn"><i class=' f18 bx  bx-arrow-left-stroke'></i> Back to Incidents</button>
            </div>
            <div class="col-lg-12">
                <header class="purplegradBox mt-5 d-flex justify-content-between align-items-center p24">
                    <div class="h3Head">
                        <div class="d-flex align-items-center gap-4">
                            <div class="gsquIcon">
                                <i class='bx  bx-sparkles'></i>
                            </div>
                            <div>
                                <h3 class="h3Head"> Predictive Incident Prevention</h3>
                                <p class="muteText">AI-powered analysis to prevent incidents before they
                                    occur
                                </p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button class="bgBtn purpleGradBtn">Run Analysis</button>
                    </div>
                </header>
            </div>
        </div>
        <div class="row mt20">
            <div class="emergencyMain activeRiskMain">
                <div class="emergencyHeader aiPreHeader">
                    <div class="emeregencyParent">
                        <div class="emergencyContent">
                            <div class="gap-3 d-flex align-items-center iconConsent">
                                <i class='bx  bx-alert-triangle'></i>

                                <h3>Active Risk Alerts (9)</h3>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="p24">
                    <div class="emergencyMain p-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="d-flex gap-3 align-items-center mb-4">
                                    <div>
                                        <i class=' orangeIcon bx  bx-trending-up f20'></i>
                                    </div>
                                    <div>
                                        <div>

                                            <span class="careBadg highBadges">High risk</span>
                                        </div>
                                        <p class="mb-0 muteText mt-2">high risk pattern</p>

                                    </div>
                                </div>
                                <h6 class="h6Head">Predicted Incidents:</h6>
                                <div class="userMum">
                                    <span class="title mt-0">
                                        Fall
                                    </span>
                                </div>
                            </div>
                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-check-circle f18'></i> Acknowledge
                                </button>
                            </div>
                        </div>
                        <div class="contentAipreBlue p-3 mt-3">
                            <div>
                                <strong>Recommended Actions:</strong>
                            </div>
                            <div class="flexRow mt-3">
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Increase supervision during high-risk hours, especially on
                                            Wednesdays at 14:00.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Implement mandatory training refreshers for staff on medication
                                            management.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Conduct safety audits in high-risk locations such as noida and
                                            kitchen areas to identify hazards.</p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="emergencyMain p-4 mt-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="d-flex gap-3 align-items-center mb-4">
                                    <div>
                                        <i class=' orangeIcon bx  bx-trending-up f20'></i>
                                    </div>
                                    <div>
                                        <div>

                                            <span class="careBadg yellowBadges">Medium Risk</span>
                                        </div>
                                        <p class="mb-0 muteText mt-2">high risk pattern</p>

                                    </div>
                                </div>
                                <h6 class="h6Head">Predicted Incidents:</h6>
                                <div class="userMum">
                                    <span class="title mt-0">
                                        Fall
                                    </span>
                                </div>
                            </div>
                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-check-circle f18'></i> Acknowledge
                                </button>
                            </div>
                        </div>
                        <div class="contentAipreBlue p-3 mt-3">
                            <div>
                                <strong>Recommended Actions:</strong>
                            </div>
                            <div class="flexRow mt-3">
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Increase supervision during high-risk hours, especially on
                                            Wednesdays at 14:00.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Implement mandatory training refreshers for staff on medication
                                            management.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Conduct safety audits in high-risk locations such as noida and
                                            kitchen areas to identify hazards.</p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="emergencyMain p-4 mt-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="d-flex gap-3 align-items-center mb-4">
                                    <div>

                                        <i class=' orangeIcon bx  bx-clock f20'></i>
                                    </div>
                                    <div>
                                        <div>

                                            <span class="careBadg yellowBadges">Medium risk</span>
                                        </div>
                                        <p class="mb-0 muteText mt-2">high risk pattern</p>

                                    </div>
                                </div>
                                <h6 class="h6Head">Predicted Incidents:</h6>
                                <div class="userMum">
                                    <span class="title mt-0">
                                        Fall
                                    </span>
                                </div>
                            </div>
                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-check-circle f18'></i> Acknowledge
                                </button>
                            </div>
                        </div>
                        <div class="contentAipreBlue p-3 mt-3">
                            <div>
                                <strong>Recommended Actions:</strong>
                            </div>
                            <div class="flexRow mt-3">
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Increase supervision during high-risk hours, especially on
                                            Wednesdays at 14:00.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Implement mandatory training refreshers for staff on medication
                                            management.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Conduct safety audits in high-risk locations such as noida and
                                            kitchen areas to identify hazards.</p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="emergencyMain p-4 mt-4">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="d-flex gap-3 align-items-center mb-4">
                                    <div>

                                        <i class='orangeIcon bx  bx-location f20'></i>
                                    </div>
                                    <div>
                                        <div>

                                            <span class="careBadg yellowBadges">Medium Risk</span>
                                        </div>
                                        <p class="mb-0 muteText mt-2">high risk pattern</p>

                                    </div>
                                </div>
                                <h6 class="h6Head">Predicted Incidents:</h6>
                                <div class="userMum">
                                    <span class="title mt-0">
                                        Fall
                                    </span>
                                </div>
                            </div>
                            <div>
                                <button class="borderBtn">
                                    <i class='bx  bx-check-circle f18'></i> Acknowledge
                                </button>
                            </div>
                        </div>
                        <div class="contentAipreBlue p-3 mt-3">
                            <div>
                                <strong>Recommended Actions:</strong>
                            </div>
                            <div class="flexRow mt-3">
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Increase supervision during high-risk hours, especially on
                                            Wednesdays at 14:00.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Implement mandatory training refreshers for staff on medication
                                            management.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-3">
                                    <div>

                                        <span class="careBadg darkBlueBadg">High</span>
                                    </div>
                                    <div>

                                        <p class="mb-0">Conduct safety audits in high-risk locations such as noida and
                                            kitchen areas to identify hazards.</p>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="emergencyMain careTaskstbbg sectionWhiteBgAllUse p-0 mt-4">
                        <div class="leavebanktabCont">
                            <i class='bx  bx-sparkles' style="color: #c084fc"></i>
                            <h4>AI-Powered Prevention</h4>
                            <p>Run predictive analysis to identify patterns and prevent future incidents</p>
                            <p class="muteText"> Requires at least 5 historical incidents for meaningful
                                analysis </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@endsection