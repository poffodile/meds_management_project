<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
@extends('frontEnd.layouts.master')
@section('title', 'Audit Log')
@section('content')
@include('frontEnd.roster.common.roster_header')
<main class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="staffHeaderp flexWrap gap-3">
                    <div>
                        <div class="d-flex gap-2 mb-3">
                            <div>
                                <i class="bx bx-file-detail indegoText" style="font-size: 30px;"></i>
                            </div>
                            <h1 class="mainTitlep mb-0"> Audit Log </h1>
                        </div>
                        <p class="header-subtitle mb-0">Complete system audit trail for regulatory compliance</p>
                    </div>
                    <div class="dFlexGap">
                        <button class="bgBtn" type="button" data-toggle="modal" data-target="#generateCqc"> <i class="bx bx-shield"></i> Generate CQC/Ofsted Report</button>
                        <button class="borderBtn"> <i class="bx bx-arrow-to-bottom"></i> Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-md-12">
                <div class="emergencyMain p24">
                    <h5 class="h5Head"> <i class="bx bx-filter f20"></i> Filters</h5>
                    <form action="">
                        <div class="mt20 auditLogRow">
                            <div>
                                <label class="formLabel">Event Type</label>
                                <select class="form-control">
                                    <option>All Types</option>
                                    <option>Staff Onboarding</option>
                                </select>
                            </div>
                            <div>
                                <label class="formLabel">Category</label>
                                <select class="form-control">
                                    <option>All Category</option>
                                    <option>Compliance</option>
                                </select>
                            </div>
                            <div>
                                <label class="formLabel">Service Area</label>
                                <select class="form-control">
                                    <option>All Area</option>
                                    <option>Residential</option>
                                </select>
                            </div>
                            <div>
                                <label class="formLabel">Start Date</label>
                                <input type="date" class="form-control">
                            </div>
                            <div>
                                <label class="formLabel">End Date</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="m-t-10">
                            <input type="search" class="form-control" placeholder="Search by action, entity, or user...">
                        </div>
                        <div class="flexBw mt-4">
                            <p class="mb-0 fs13 textGray500">0 records found</p>
                            <button class="borderBtn">Clear Filters</button>
                        </div>

                    </form>
                </div>
                <div class="emergencyMain mt20 p24">
                    <h5 class="h5Head"> Log Entries </h5>
                    <div class="lightBorderp p-4 rounded12 bottomSpace">
                        <div class="dFlexGap align-items-start">
                            <div>
                                <i class="bx bx-alert-circle f20 redtext"></i>
                            </div>
                            <div>
                                <div>
                                    <div class="dFlexGap">
                                        <h6 class="h6Head"> Medication administration error identified and reported - double dose prevented by checking systemMedication administration error identified and reported - double dose prevented by checking system </h6>
                                        <div class="fs13 textGray500 noWrap d-flex align-items-center"> <i class="bx bx-calendar-week"></i>
                                            <span>

                                                17/02/2026 14:20
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dFlexGap mb-3">
                                        <span class="borderBadg">medication admin</span>
                                        <span class="careBadg orangeBages">High</span>
                                        <span class="borderBadg">clinical</span>
                                        <span class="borderBadg">residential care</span>
                                    </div>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Entity:</span> Mr. John Smith</p>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Performed by:</span> Registered Nurse</p>
                                    <div class="viewDetails">
                                        <div class="recordUploadSec">
                                            <a class="blueText fs13 viewDetailsBtn cursorPointer"> <i class="bx bx-caret-right"></i> View Details</a>
                                        </div>
                                        <div class="muteBg viewDetailsCon p-4 textGray500 fs13" style="display: none;">
                                            <pre>
{
"incident_type": "near_miss",
"medication": "Paracetamol",
"action_taken": "System check prevented error, staff reminded of MAR checking protocol"
}
 </pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lightBorderp p-4 rounded12 bottomSpace">
                        <div class="dFlexGap align-items-start">
                            <div>
                                <i class="bx bx-alert-circle f20 yellowText"></i>
                            </div>
                            <div>
                                <div>
                                    <div class="dFlexGap">
                                        <h6 class="h6Head"> Medication administration error identified and reported - double dose prevented by checking systemMedication administration error identified and reported - double dose prevented by checking system </h6>
                                        <div class="fs13 textGray500 noWrap d-flex align-items-center"> <i class="bx bx-calendar-week"></i>
                                            <span>

                                                17/02/2026 14:20
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dFlexGap mb-3">
                                        <span class="borderBadg">medication admin</span>
                                        <span class="careBadg yellowBadges">Medium</span>
                                        <span class="borderBadg">clinical</span>
                                        <span class="borderBadg">residential care</span>
                                    </div>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Entity:</span> Mr. John Smith</p>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Performed by:</span> Registered Nurse</p>
                                    <div class="viewDetails">
                                        <div class="recordUploadSec">
                                            <a class="blueText fs13 viewDetailsBtn cursorPointer"> <i class="bx bx-caret-right"></i> View Details</a>
                                        </div>
                                        <div class="muteBg viewDetailsCon p-4 textGray500 fs13" style="display: none;">
                                            <pre>
{
"incident_type": "near_miss",
"medication": "Paracetamol",
"action_taken": "System check prevented error, staff reminded of MAR checking protocol"
}
</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lightBorderp p-4 rounded12 bottomSpace">
                        <div class="dFlexGap align-items-start">
                            <div>
                                <i class="bx bx-alert-circle f20 blueText"></i>
                            </div>
                            <div>
                                <div>
                                    <div class="dFlexGap">
                                        <h6 class="h6Head"> Medication administration error identified and reported - double dose prevented by checking systemMedication administration error identified and reported - double dose prevented by checking system </h6>
                                        <div class="fs13 textGray500 noWrap d-flex align-items-center"> <i class="bx bx-calendar-week"></i>
                                            <span>

                                                17/02/2026 14:20
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dFlexGap mb-3">
                                        <span class="borderBadg">medication admin</span>
                                        <span class="careBadg">Medium</span>
                                        <span class="borderBadg">clinical</span>
                                        <span class="borderBadg">residential care</span>
                                    </div>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Entity:</span> Mr. John Smith</p>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Performed by:</span> Registered Nurse</p>
                                    <div class="viewDetails">
                                        <div class="recordUploadSec">
                                            <a class="blueText fs13 viewDetailsBtn cursorPointer"> <i class="bx bx-caret-right"></i> View Details</a>
                                        </div>
                                        <div class="muteBg viewDetailsCon p-4 textGray500 fs13" style="display: none;">
                                            <pre>
{
"incident_type": "near_miss",
"medication": "Paracetamol",
"action_taken": "System check prevented error, staff reminded of MAR checking protocol"
}
</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lightBorderp p-4 rounded12 bottomSpace">
                        <div class="dFlexGap align-items-start">
                            <div>
                                <i class="bx bx-alert-circle f20 redtext"></i>
                            </div>
                            <div>
                                <div>
                                    <div class="dFlexGap">
                                        <h6 class="h6Head"> Medication administration error identified and reported - double dose prevented by checking systemMedication administration error identified and reported - double dose prevented by checking system </h6>
                                        <div class="fs13 textGray500 noWrap d-flex align-items-center"> <i class="bx bx-calendar-week"></i>
                                            <span>
                                                17/02/2026 14:20
                                            </span>
                                        </div>
                                    </div>
                                    <div class="dFlexGap mb-3">
                                        <span class="borderBadg">medication admin</span>
                                        <span class="careBadg redbadges">Critical</span>
                                        <span class="borderBadg">clinical</span>
                                        <span class="borderBadg">residential care</span>
                                    </div>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Entity:</span> Mr. John Smith</p>
                                    <p class="fs14 textGray500 mb-2"><span class="font600">Performed by:</span> Registered Nurse</p>
                                    <div class="viewDetails">
                                        <div class="recordUploadSec">
                                            <a class="blueText fs13 viewDetailsBtn cursorPointer"> <i class="bx bx-caret-right"></i> View Details</a>
                                        </div>
                                        <div class="muteBg viewDetailsCon p-4 textGray500 fs13" style="display: none;">
                                            <pre>
{
"incident_type": "near_miss",
"medication": "Paracetamol",
"action_taken": "System check prevented error, staff reminded of MAR checking protocol"
}
</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="noData py-5" style="border: unset; box-shadow: unset;">
                        <div>
                            <p class="mb-0">No audit logs found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- generate cqc Modal -->
        <div class="modal fade leaveCommunStyle" id="generateCqc" tabindex="1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg pModalScroll">
                <div class="modal-content">
                    <div class="modal-header p24">
                        <div class="flexBw">
                            <div class="dFlexGap">
                                <i class="bx bx-shield fs23 blueText"></i>
                                <h4 class="modal-title">Generate CQC Mock Inspection Report </h4>
                            </div>
                            <button class="close" type="button" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                    </div>
                    <div class="modal-body heightScrollModal p24" style="height: unset;">
                        <form action="">
                            <div>
                                <label for="">Regulatory Body</label>
                                <select name="" id="" class="form-control">
                                    <option value="">CQC (Care Quality Commission)</option>
                                    <option value="">Ofsted</option>
                                    <option value="">CIW (Care Inspectorate Wales)</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <div class="lightBlueBg p-4 rounded8">
                                    <p class="fs13 blackText">
                                        This will analyze your audit log data (26 entries) and generate a comprehensive mock inspection report with ratings, strengths, areas for improvement, and compliance findings.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 d-flex justify-content-end">
                                <button class="bgBtn"
                                    data-toggle="modal" id="generateReportBtn" type="button"><i class="bx bx-shield"></i>Generate Report</button>
                            </div>
                            <div class="mt20 d-flex justify-content-end">
                                <button class="borderBtn" type="button" data-dismiss="modal" aria-hidden="true"> Cancel</button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>

        </div>
        <!-- end generate cqc modal -->


        <div class="modal fade leaveCommunStyle " id="reportSuccessModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog pModalScroll modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="flexBw">
                            <div class="dFlexGap">
                                <i class="bx bx-shield fs23 blueText"></i>
                                <h4 class="modal-title">Generate CQC Mock Inspection Report</h4>
                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        </div>
                    </div>
                    <div class="modal-body  py-4">
                        <!-- generate part -->
                        <div>
                            <div class="p24 aIInsightsheader rounded12">
                                <button class="bgBtn blackBtn fs15 m-auto">Overall Rating: Good</button>
                                <h3 class="fs30 font700 text-center mt-4 mb-0">82/100</h3>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head mb-4">Domain Ratings</h5>
                                <div class="card-row">
                                    <div class="card-col">
                                        <div class="lightBorderp p-4 rounded8 text-center">
                                            <p class="fs13 mb-2 textGray500">Safe</p>
                                            <span class="careBadg darkBlackBadg">Good</span>
                                        </div>
                                    </div>
                                    <div class="card-col">
                                        <div class="lightBorderp p-4 rounded8 text-center">
                                            <p class="fs13 mb-2 textGray500">Effective</p>
                                            <span class="careBadg darkBlackBadg">Good</span>
                                        </div>
                                    </div>
                                    <div class="card-col">
                                        <div class="lightBorderp p-4 rounded8 text-center">
                                            <p class="fs13 mb-2 textGray500">Caring</p>
                                            <span class="careBadg darkBlackBadg">Good</span>
                                        </div>
                                    </div>
                                    <div class="card-col">
                                        <div class="lightBorderp p-4 rounded8 text-center">
                                            <p class="fs13 mb-2 textGray500">Responsive</p>
                                            <span class="careBadg darkBlackBadg">Good</span>
                                        </div>
                                    </div>
                                    <div class="card-col">
                                        <div class="lightBorderp p-4 rounded8 text-center">
                                            <p class="fs13 mb-2 textGray500">Well led</p>
                                            <span class="careBadg darkBlackBadg">Good</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <div class="dFlexGap mb-4">
                                    <i class="bx bx-star f20 greenText"></i>
                                    <h5 class="h5Head mb-0 greenText">Strengths</h5>
                                </div>
                                <div class="mt-4">
                                    <div class="lightGreeBg p-4 rounded5 bottomSpace">
                                        <div class="dFlexGap">
                                            <i class="bx bx-star f18 greenText"></i>
                                            <p class="mb-0 fs13 textGray600">Completed mandatory Safeguarding Adults Level 2 training by staff, ensuring awareness of abuse and harm prevention procedures.</p>
                                        </div>
                                    </div>
                                    <div class="lightGreeBg p-4 rounded5 bottomSpace">
                                        <div class="dFlexGap">
                                            <i class="bx bx-star f18 greenText"></i>
                                            <p class="mb-0 fs13 textGray600">Completed mandatory Safeguarding Adults Level 2 training by staff, ensuring awareness of abuse and harm prevention procedures.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head mb-0 orangeText700">Areas for Improvement </h5>
                                <div class="mt-4">
                                    <div class="lighOrangeBg rounded5 p-4 bottomSpace" style="border-left: 4px solid #f97316;">
                                        <div class="FlexBw mb-3">
                                            <h6 class="h6Head mb-0">Improve incident management and learning from safeguarding alerts.</h6>
                                            <span class="careBadg mediumOrangeBadg">HIGH</span>
                                        </div>
                                        <p class="fs13 mb-0 textGray600">Conduct a root cause analysis for the safeguarding alerts to identify systemic issues and improve training.</p>
                                    </div>
                                    <div class="lighOrangeBg rounded5 p-4 bottomSpace" style="border-left: 4px solid #f97316;">
                                        <div class="FlexBw mb-3">
                                            <h6 class="h6Head mb-0">Improve incident management and learning from safeguarding alerts.</h6>
                                            <span class="careBadg mediumOrangeBadg">HIGH</span>
                                        </div>
                                        <p class="fs13 mb-0 textGray600">Conduct a root cause analysis for the safeguarding alerts to identify systemic issues and improve training.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head mb-0 redText">Compliance Issues </h5>
                                <div class="mt-4">
                                    <div class="lightRedBg rounded5 p-4 bottomSpace" style="border-left: 4px solid #ef4444;">
                                        <div class="FlexBw mb-3">
                                            <h6 class="h6Head mb-0">Improve incident management and learning from safeguarding alerts.</h6>
                                            <span class="careBadg redDarkBadges">HIGH</span>
                                        </div>
                                        <p class="fs13 mb-0 textGray600">Conduct a root cause analysis for the safeguarding alerts to identify systemic issues and improve training.</p>
                                    </div>
                                    <div class="lightRedBg rounded5 p-4 bottomSpace" style="border-left: 4px solid #ef4444;">
                                        <div class="FlexBw mb-3">
                                            <h6 class="h6Head mb-0">Improve incident management and learning from safeguarding alerts.</h6>
                                            <span class="careBadg redDarkBadges">HIGH</span>
                                        </div>
                                        <p class="fs13 mb-0 textGray600">Conduct a root cause analysis for the safeguarding alerts to identify systemic issues and improve training.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <h5 class="h5Head mb-0">Summary Report</h5>
                                <div class="mt-4">
                                    <div class="lightBorderp p-4 muteBg rounded5">
                                        <p class="fs13 mb-0 textGray600">The mock inspection of Omega Life has identified areas of strength and areas requiring improvement. Across the SAFE domain, while there are measures in place for staff safeguarding training and audits, the presence of safeguarding alerts indicates a need for more robust follow-up and learning mechanisms. The organization scored well under EFFECTIVE due to demonstrated care planning practices and recent audits indicating a solid foundation of care outcomes. In CARING, the staff's commitment to person-centred approaches was evident, ensuring that care plans are developed with input from service users and families. However, in the RESPONSIVE domain, a significant complaint about delayed visit times highlights a need for better communication and organization to meet service users' needs effectively. Overall, leadership within the organization requires enhancement, specifically in governance practices and policy updates, which are essential for maintaining compliance with regulations. The numeric scoring is reflective of these findings, emphasizing the requirement for ongoing monitoring and action to meet expected standards.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <label for="">Send Report to Email (Optional)</label>
                                <div class="dFlexGap">
                                    <div class="flex1">
                                        <input type="email" class="form-control w100" placeholder="regulator@cqc.org.uk">
                                    </div>
                                    <div>
                                        <button class="borderBtn"><i class="bx bx-send"></i>Send</button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <div class="dFlexGap justify-content-end">
                                    <button class="borderBtn" data-dismiss="modal" aria-hidden="true">Close</button>
                                    <button class="bgBtn pgreenBtn"> <i class="bx bx-arrow-to-bottom"></i> to Mock Inspections</button>
                                </div>
                            </div>
                        </div>
                        <!-- generate part -->
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        const viewDetailsBtn = document.querySelectorAll(".viewDetailsBtn");
        viewDetailsBtn.forEach((btn) => {
            btn.addEventListener("click", () => {
                const detailBox = btn.closest(".viewDetails").querySelector(".viewDetailsCon");
                const isOpen = detailBox.style.display === "block";
                document.querySelectorAll(".viewDetailsCon").forEach((con) => con.style.display = "none");
                document.querySelectorAll(".viewDetailsBtn").forEach((b) => b.classList.remove("active"));
                if (!isOpen) {
                    detailBox.style.display = "block";
                    btn.classList.add("active");
                }

            });
        });
    </script>
    <!-- stack modal -->
    <script>
        const generateReportBtn = document.getElementById('generateReportBtn');
        generateReportBtn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            const originalWidth = this.offsetWidth;
            this.innerHTML = `
          Generating Report...
        `;
            this.disabled = true;
            setTimeout(() => {
                $('#generateCqc').modal('hide');
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    this.style.width = '';
                    $('#reportSuccessModal').modal('show');
                }, 300);

            }, 2500);
        });
    </script>

</main>
@endsection