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
                        <h1 class="mainTitlep">Timesheet & Shift Reconciliation</h1>
                        <p class="header-subtitle mb-0">Review actual vs planned hours and approve timesheets</p>
                    </div>
                    <div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-12">
                <div class="card-row">
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <p class=" fs13 textGray500">Matched</p>
                            <h2 class="cardBoldTitle mt-0 mb-0 textBlue">0</h2>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <p class=" fs13 textGray500">Needs Adjustment</p>
                            <h2 class="cardBoldTitle mt-0 mb-0 orangeText">0</h2>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <p class=" fs13 textGray500">Unscheduled</p>
                            <h2 class="cardBoldTitle mt-0 mb-0 purpleTextp">0</h2>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <p class=" fs13 textGray500">Approved</p>
                            <h2 class="cardBoldTitle mt-0 mb-0 greenText">0</h2>
                        </div>
                    </div>
                    <div class="card-col">
                        <div class="emergencyMain p-4">
                            <p class="fs13 textGray500">Rejected</p>
                            <h2 class="cardBoldTitle mt-0 mb-0 redtext ">0</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-12">
                <div class="emergencyMain p-4">
                    <div class="row">
                        <div class="col-lg-3">
                            <select class="form-control">
                                <option>All Status</option>
                                <option>Draft</option>
                                <option>Sent</option>
                                <option>Paid</option>
                                <option>Overdue</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <input type="date" class="form-control">
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control">
                                <option>All Staff</option>
                                <option>Jane wake</option>
                                <option>Sent</option>
                                <option>Paid</option>
                                <option>Overdue</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <button class="borderBtn w100"><i class="bx bx-filter f18 me-2"></i> Reset Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt20">
            <div class="col-lg-12">
                <div class="panel-group" id="accordion">
                    <!-- pannel 1 -->
                    <div class="panel panel-default payRollAcood">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse1"
                                    class="lightBlueBg">
                                    <i class="bx bx-clock f20 blueText me-2"></i>
                                    Clocks with Shift Data (0)
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        @if ($shifts->count() > 0)
                        <div id="collapse1" class="panel-collapse collapse in">
                            <div class="panel-body">
                                @foreach ($shifts as $shift)
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">{{ $shift->staff ? $shift->staff->first_name . ' ' . $shift->staff->last_name : 'Unknown Staff' }}</h5>
                                                <div>
                                                    @if ($shift->variance_minutes == 0)
                                                    <span class="careBadg greenbadges">Matched</span>
                                                    @elseif ($shift->variance_minutes > 0)
                                                    <span class="careBadg orangeBages">Extra {{ number_format($shift->variance_minutes / 60, 2) }} Hour</span>
                                                    @else
                                                    <span class="careBadg redbadges">Short {{ number_format(abs($shift->variance_minutes) / 60, 2) }} Hour</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <p class="muteText mb-2">Date</p>
                                                <h6 class="h6Head mb-0">{{ \Carbon\Carbon::parse($shift->start_date)->format('D, M d') }}</h6>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn w100" data-toggle="modal" data-target="#adjustNodal"><i class="bx bx-eye me-2 f18"></i>
                                                Adjust</button>
                                            <!-- <button class="borderBtn" id="#ShiftDatadjustNodal" data-toggle="modal" data-target="#adjustNodal">Adjust</button> -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Scheduled Shift</p>
                                                <h6 class="h6Head">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2"> <i class="bx bx-eye me-1 fs16" style="cursor:pointer" type="button" data-toggle="modal" data-target="#clockDetails-{{ $shift->id }}"></i> Clock Times</p>
                                                <h6 class="h6Head">
                                                    @if (count($shift->login_activities) > 0)
                                                    {{ count($shift->login_activities) }} Log(s) Recorded
                                                    @else
                                                    No Logs
                                                    @endif
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Variance</p>
                                                <h6 class="h6Head {{ $shift->variance_minutes >= 0 ? 'greenText' : 'redtext' }}">
                                                    {{ $shift->variance_minutes > 0 ? '+' : '' }}{{ number_format($shift->variance_minutes / 60, 2) }}h
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Total Amount</p>
                                                <h6 class="h6Head greenText">Within Tolerance</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <p class="textGray500 fs13 text-center py-5 mb-0">No matched timesheets </p>
                        @endif
                    </div>
                    <!-- Panel 2-->
                    <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse2"
                                    class="lighOrangeBg">
                                    <i class="bx bx-alert-triangle f20 orangeText me-2"></i>
                                    Requires Adjustment (0)
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>

                            </h4>

                        </div>
                        <div id="collapse2" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="bBorderCard mt-4 p-4">

                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">John Smith </h5>
                                                <div><span class="careBadg greenbadges">Matched</span>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-4">
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Date </p>
                                                    <h6 class="h6Head blackText mb-0">
                                                        Sun, Nov 23 </h6>
                                                </div>
                                                <!-- <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Clock Times </p>
                                                    <h6 class="h6Head blackText" mb-0>08:00 - 16:00</h6>
                                                </div> -->
                                            </div>

                                        </div>
                                        <div>
                                            <button class="borderBtn">
                                                Review</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Scheduled Shift</p>
                                                <h6 class="h6Head">08:00 - 16:00</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Clock Times</p>
                                                <h6 class="h6Head">08:02 - 15:58</h6>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Missing Clock-Out</p>
                                                <h6 class="h6Head">Within Tolerance</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Action</p>
                                                <h6 class="h6Head">Manual Review</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="textGray500 fs13 text-center py-5 mb-0">No matched timesheets </p>
                            </div>
                        </div>
                    </div>
                    <!-- Panel 3-->

                    <div class="panel panel-default mt-4 payRollAcood p-0">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse3"
                                    class="lightGreeBg">
                                    <i class="bx bx-check-circle f20 greenText me-2"></i>
                                    Approved (20)
                                    <i class="bx bx-chevron-down accIcon"></i>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse3" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">John Smith </h5>
                                                <div class="d-flex gap-3 flexWrap">
                                                    <div><span class="careBadg greenbadges">approved</span>
                                                    </div>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            weekend
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex mb-4">
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Date </p>
                                                    <h6 class="h6Head blackText mb-0">
                                                        Sun, Nov 23 </h6>
                                                </div>
                                                <div class="flex1">
                                                    <p class="mb-2 fs13 textGray500">Clock Times </p>
                                                    <h6 class="h6Head blackText mb-0">08:00 - 16:00</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <button class="borderBtn w100" data-toggle="modal" data-target="#adjustNodal"><i class="bx bx-eye me-2 f18"></i>
                                                Adjust</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Planned</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Actual</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Variance</p>
                                                <h6 class="h6Head">0.00h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Action</p>
                                                <h6 class="h6Head">Manual Review</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">John Smith </h5>
                                                <div class="d-flex gap-3 flexWrap">
                                                    <div><span class="careBadg greenbadges">approved</span>
                                                    </div>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            weekend
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex mb-4">
                                                    <div class="flex1">
                                                        <p class="mb-2 fs13 textGray500">Date </p>
                                                        <h6 class="h6Head blackText mb-0">
                                                            Sun, Nov 23 </h6>
                                                    </div>
                                                    <div class="flex1">
                                                        <p class="mb-2 fs13 textGray500">Clock Times </p>
                                                        <h6 class="h6Head blackText mb-0">08:00 - 16:00</h6>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div>
                                            <button class="borderBtn w100"><i class="bx bx-eye me-2 f18"></i>
                                                Adjust</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Planned</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Actual</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Variance</p>
                                                <h6 class="h6Head redtext">+0.50h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Action</p>
                                                <h6 class="h6Head">Manual Review</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div>
                                                <span class="careBadg orangeBages"> <i class="bx bx-clock me-2"></i> Clocked In Late</span>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="bBorderCard mt-4 p-4">
                                    <div class="d-flex justify-content-between">
                                        <div class="flex1">
                                            <div class="d-flex gap-3 mb-3 align-items-center">
                                                <h5 class="h5Head mb-0">John Smith </h5>
                                                <div class="d-flex gap-3 flexWrap">
                                                    <div><span class="careBadg greenbadges">approved</span>
                                                    </div>
                                                    <div class="userMum">
                                                        <span class="title mt-0">
                                                            weekend
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="d-flex mb-4">
                                                    <div class="flex1">
                                                        <p class="mb-2 fs13 textGray500">Date </p>
                                                        <h6 class="h6Head blackText mb-0">
                                                            Sun, Nov 23 </h6>
                                                    </div>
                                                    <div class="flex1">
                                                        <p class="mb-2 fs13 textGray500">Clock Times </p>
                                                        <h6 class="h6Head blackText mb-0">08:00 - 16:00</h6>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div>
                                            <button class="borderBtn w100"><i class="bx bx-eye me-2 f18"></i>
                                                Adjust</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Planned</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Actual</p>
                                                <h6 class="h6Head">8.00h</h6>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Variance</p>
                                                <h6 class="h6Head blueText">+0.50h</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div>
                                                <p class="textGray500 fs13 mb-2">Action</p>
                                                <h6 class="h6Head">Manual Review</h6>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div>
                                                <span class="careBadg orangeBages"> <i class="bx bx-clock me-2"></i> Clocked In Late</span>
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

    <!-- modal Adjust reconciliation start -->

    <div class="modal fade leaveCommunStyle" id="adjustNodal" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modalMd pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Pay Adjustments </h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">
                    <div class="d-flex muteBg rounded5 p-4">
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Planned Hours </p>
                            <h5 class="h5Head font700">8.00h </h5>
                        </div>
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Current Actual Hours </p>
                            <h5 class="h5Head font700">8.00h </h5>
                        </div>
                    </div>
                    <div class="mt20">
                        <h6 class="h5Head">Clock Times</h6>
                        <form action="">

                            <div class="row">
                                <div class="col-md-6  m-t-10">
                                    <label>Clock In</label>
                                    <input type="time" id="scheduled_time" name="scheduled_time" class="form-control">
                                </div>
                                <div class="col-md-6  m-t-10">
                                    <label>Clock Out</label>
                                    <input type="time" id="scheduled_time" name="scheduled_time" class="form-control">
                                </div>

                            </div>
                            <div class="appendContainer">
                                <div class="flexBw mt20">
                                    <div>
                                        <h5 class="h5Head">Pay Adjustments </h5>
                                    </div>
                                    <div>
                                        <button class="borderBtn appendBtn"> <i class="bx  bx-plus me-2"></i> Add Row</button>
                                    </div>
                                </div>
                                <div class="flexRow mt-3">
                                    <div class="shadowp rounded8 p-4 lightBorderp appendRow" style="display: none;">
                                        <div class="dFlexGap align-item-end">
                                            <div class="flex1">
                                                <label for="">Hours</label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="flex1">
                                                <label for="">Minutes
                                                </label>
                                                <input type="text" class="form-control">
                                            </div>
                                            <div class="flex1">
                                                <label for="">Pay Bucket</label>
                                                <select class="form-control">
                                                    <option value="1">Standard</option>
                                                    <option value="1">OverTime</option>
                                                    <option value="1">Weekend</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label for=" " style="visibility: hidden;">delete</label>
                                                <div class="deleteIcon flex1 deleteAppend">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <div>
                                    <label>Adjustment Reason</label>
                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Enter reason for adjustment..." class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="mt20 lightBorderp bg-blue-50 p-4 rounded8">
                                <div class="flexBw">
                                    <h5 class="h6Head darkBlueTextp mb-0">Total Adjusted Hours</h5>
                                    <h3 class="darkBlueTextp my-0 font700">8.00h</h3>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer d-flex gap-3">
                    <div class="dFlexGap w100">
                        <div class="flex1">
                            <button class="borderBtn w100" class="close" data-dismiss="modal">Cancel </button>
                        </div>
                        <div class="flex1">
                            <button class="bgBtn pgreenBtn w100"> <i class="bx bx-save f18 me-2"></i> Save Adjustment</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal Adjust reconciliation end -->

    <!-- modal clock shift adjust  start -->

    <!-- <div class="modal fade leaveCommunStyle" id="ShiftDatadjustNodal" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modalMd pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Pay Adjustments </h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">
                    <div class="d-flex muteBg rounded5 p-4">
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Planned Hours </p>
                            <h5 class="h5Head font700">8.00h </h5>
                        </div>
                        <div class="flex1">
                            <p class="fs13 textGray mb-2">Current Actual Hours </p>
                            <h5 class="h5Head font700">8.00h </h5>
                        </div>
                    </div>
                    <div class="mt20">
                        <h6 class="h5Head">Clock Times</h6>
                        <form action="">

                            <div class="row">
                                <div class="col-md-6  m-t-10">
                                    <label>Clock In</label>
                                    <input type="time" id="scheduled_time" name="scheduled_time" class="form-control">
                                </div>
                                <div class="col-md-6  m-t-10">
                                    <label>Clock Out</label>
                                    <input type="time" id="scheduled_time" name="scheduled_time" class="form-control">
                                </div>

                            </div>
                            <div class="flexBw mt20">
                                <div>
                                    <h5 class="h5Head">Pay Adjustments </h5>
                                </div>
                                <div>
                                    <button class="borderBtn appendBtn"> <i class="bx  bx-plus me-2"></i> Add Row</button>
                                </div>
                            </div>
                            <div class="flexRow mt-3">
                                <div class="shadowp rounded8 p-4 lightBorderp appendRow" style="display: none;">
                                    <div class="dFlexGap align-item-end">
                                        <div class="flex1">
                                            <label for="">Hours</label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="flex1">
                                            <label for="">Minutes
                                            </label>
                                            <input type="text" class="form-control">
                                        </div>
                                        <div class="flex1">
                                            <label for="">Pay Bucket</label>
                                            <select class="form-control">
                                                <option value="1">Standard</option>
                                                <option value="1">OverTime</option>
                                                <option value="1">Weekend</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for=" " style="visibility: hidden;">delete</label>
                                            <div class="deleteIcon flex1 deleteAppend">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="mt20">
                                <div>
                                    <label>Adjustment Reason</label>
                                    <textarea name="notes" id="" rows="3" cols="20" placeholder="Enter reason for adjustment..." class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="mt20 lightBorderp bg-blue-50 p-4 rounded8">
                                <div class="flexBw">
                                    <h5 class="h6Head darkBlueTextp mb-0">Total Adjusted Hours</h5>
                                    <h3 class="darkBlueTextp my-0 font700">8.00h</h3>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer d-flex gap-3">
                    <div class="dFlexGap w100">
                        <div class="flex1">
                            <button class="borderBtn w100" class="close" data-dismiss="modal">Cancel </button>
                        </div>
                        <div class="flex1">
                            <button class="bgBtn pgreenBtn w100"> <i class="bx bx-save f18 me-2"></i> Save Adjustment</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <!-- modal clock shift adjust reconciliation end -->
    <!-- clocl details -->
    @foreach ($shifts as $shift)
    <div class="modal fade leaveCommunStyle" id="clockDetails-{{ $shift->id }}" tabindex="1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  pModalScroll">
            <div class="modal-content">
                <div class="modal-header p24">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Clock Details </h4>
                </div>
                <div class="modal-body heightScrollModal p24" style="height: unset;">

                    <div class="scrollDailyCheck pe-3">
                        @if (count($shift->login_activities) > 0)
                        @foreach ($shift->login_activities as $activity)
                        <div class="lightBorderp mb-3 rounded8 p-3">
                            <div class="dFlexGap gap-5">
                                <div class="flex1">
                                    <p class="fs13 textGray500 mb-2">Clock In:</p>
                                    <p class="fs13 blackText font600">{{ \Carbon\Carbon::parse($activity->check_in_time)->format('g:i A') }}</p>
                                </div>
                                <div class="flex1">
                                    <p class="fs13 textGray500 mb-2">Clock Out:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_out_time ? \Carbon\Carbon::parse($activity->check_out_time)->format('g:i A') : 'In progress' }}</p>
                                </div>
                            </div>
                            <div class="dFlexGap gap-5">
                                <div class="flex1">
                                    <p class="fs13 textGray500 mb-2">Clock In Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_in_reason ?: 'N/A' }}</p>
                                </div>
                                <div class="flex1">
                                    <p class="fs13 textGray500 mb-2">Clock Out Reason:</p>
                                    <p class="fs13 blackText font600">{{ $activity->check_out_reason ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="lightBorderp mb-3 rounded8 p-3">
                            <p class="textGray500 fs13 text-center mb-0">No Logs Recorded For This Shift</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    <!-- end clock details -->
</main>
<!-- append section -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".appendBtn").forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const container = button.closest(".appendContainer");
                if (!container) return;
                const templateRow = container.querySelector(".appendRow");
                if (!templateRow) return;

                // clone the row
                const newRow = templateRow.cloneNode(true);
                newRow.style.display = "block"; // show the row

                // reset inputs/selects
                newRow.querySelectorAll("input").forEach(input => input.value = "");
                newRow.querySelectorAll("select").forEach(select => select.selectedIndex = 0);
                newRow.querySelectorAll("textarea").forEach(txt => txt.value = "");

                // append new row to container
                container.appendChild(newRow);
            });
        });

        // Optional: delete row if you add delete icons
        document.addEventListener("click", function(e) {
            const del = e.target.closest(".deleteIcon");
            if (del) {
                const row = del.closest(".appendRow");
                if (row) row.remove();
            }
        });
    });
</script>
@endsection