@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Staff')

<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .formDtail .text-danger{
        position: absolute;
    }
</style>
<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Staff</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="jobsection justify-content-end">
                                        <a href="javaScript:void(0)" type="button" class="profileDrop openAddStaffModel" data-action="add"> <i class="fa fa-plus"></i> Add</a>
                                        <!-- <a href="javascript:void(0)" class="profileDrop">Export</a> -->
                                    </div>
                                </div>
                            </div>
                            <div class="productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange" id="staffWorker">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Surname</th>
                                            <th>Forename</th>
                                            <th>Address</th>
                                            <th>Post Code</th>
                                            <th>DOB </th>
                                            <th>Acct Number</th>
                                            <th>Sort Code</th>
                                            <th>Rate of Pay (£) </th>
                                            <th>Level</th>
                                            <th>Start Date </th>
                                            <th>Job Role</th>
                                            <th>NIN</th>
                                            <th>Starter Declaration <br>(HMRC Starter<br> Form Completed)</th>
                                            <th>Probation Start Date </th>
                                            <th>Probation End Date </th>
                                            <th>Probation Extended Date </th>
                                            <th>After Probation Enrolled</th>
                                            <th>Student Loan</th>
                                            <th>DBS Clear</th>
                                            <th>DBS Number</th>
                                            <th>On DBS Update Service</th>
                                            <th>Leave Date</th>
                                            <th>Email Address</th>
                                            <th>Mobile</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($staffWorkers as $key => $staffData)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $staffData->surname }}</td>
                                            <td>{{ $staffData->forename }}</td>
                                            <td>{{ $staffData->address }}</td>
                                            <td>{{ $staffData->postCode }}</td>
                                            <td class="white_space_nowrap">{{ \Carbon\Carbon::parse($staffData->DOB)->format('d-m-Y')  }}</td>
                                            <td>{{ $staffData->account_num }}</td>
                                            <td>{{ $staffData->sort_code }}</td>
                                            <td>{{ $staffData->rate_of_pay }}</td>
                                            <td>@if($staffData->level == "qualified") Qualified @else Unqualified @endif</td>
                                            <td class="white_space_nowrap">{{ \Carbon\Carbon::parse($staffData->start_date)->format('d-m-Y') }}</td>
                                            <td>{{ $staffData->job_role }}</td>
                                            <td>{{ $staffData->NIN }}</td>
                                            <td>@if($staffData->starter_declaration == 1 ) Yes-A @elseif($staffData->starter_declaration == 2) Yes-B @elseif($staffData->starter_declaration == 3) Yes-C @else No @endif</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_start_date)->format('d-m-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_end_date)->format('d-m-Y')  }}</td>
                                            <td>{{ \Carbon\Carbon::parse($staffData->probation_renew_date)->format('d-m-Y')  }}</td>
                                            <td>@if($staffData->after_probation_enrolled == "1") Yes @else No @endif</td>
                                            <td>@if($staffData->student_loan == "no_student_loan") No Student Loan @elseif($staffData->student_loan == "postgraduate") Postgraduate @elseif($staffData->student_loan == "plan_1") Plan 1 @elseif($staffData->student_loan == "plan_2") Plan 2 @elseif($staffData->student_loan == "plan_4") Plan 4 @endif</td>
                                            <td>@if($staffData->dbs_clear == 1) Yes @else No @endif</td>
                                            <td>{{ $staffData->dbs_number }}</td>
                                            <td>@if($staffData->dbs_update_service == 1) Yes @else No @endif</td>
                                            <td class="white_space_nowrap">{{ $staffData->leave_date ? \Carbon\Carbon::parse($staffData->leave_date)->format('d-m-Y') : '' }}</td>
                                            <td>{{ $staffData->email }}</td>
                                            <td>{{ $staffData->mobile }}</td>
                                            <td>
                                                <a href="#!" class="openModalBtn openAddStaffModel" data-action="edit" data-staff="{{ json_encode($staffData) }}" data-id="{{ $staffData->id }}" data-surname="{{ $staffData->surname }}" data-forename="{{ $staffData->forename }}" data-address="{{ $staffData->address }}" data-postCode="{{ $staffData->postCode }}" data-dob="{{ $staffData->dob }}" data-account_num="{{ $staffData->account_num }}" data-sort_code="{{ $staffData->sort_code }}" data-rate_of_pay="{{ $staffData->rate_of_pay }}" data-level="{{ $staffData->level}}" data-start_date="{{ $staffData->start_date }}" data-job_title="{{ $staffData->job_title }}" data-NIN="{{ $staffData->NIN }}" data-starter_declaration="{{ $staffData->starter_declaration }}" data-probation_start_date="{{ $staffData->probation_start_date }}" data-probation_end_date="{{ $staffData->probation_end_date }}" data-probation_renew_date="{{ $staffData->probation_renew_date }}" data-after_probation_enrolled="{{ $staffData->after_probation_enrolled }}" data-student_loan="{{ $staffData->student_loan }}" data-dbs_clear="{{ $staffData->dbs_clear }}" data-dbs_number="{{ $staffData->dbs_number }}" data-dbs_update_service="{{ $staffData->dbs_update_service }}" data-leave_date="{{ $staffData->leave_date }}"><i class="fa fa-pencil" aria-hidden="true"></i></a> |
                                                <a href="#!" onclick="deleteStaff({{ $staffData->id }})" class="deleteBtn"><i class="fa fa-trash radStar" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- End off main Table -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Staff Modal start here -->
<div class="modal fade" id="addStaffWorkerModal" tabindex="-1" aria-labelledby="addStaffWorkerModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle">Add Staff</h4>
            </div>
            <form id="addStaffWorkerForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="formDtail row">
                                <div class="form-group col-md-6 col-lg-6">
                                    <input type="hidden" name="staff_id" id="staff_id">
                                    <label> Surname <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" placeholder="Daniels" name="surname" id="surname">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label> Forename <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="forename" id="forename" placeholder="Jesse">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Address</label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="address" id="address" placeholder="34/36 Gresford Avenue">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label> Post Code <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="postCode" id="postCode" placeholder="L17 2AW">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Date of Birth</label>
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="DOB" id="DOB" type="text" autocomplete="off" class="form-control" placeholder="Date of Birth" readonly>
                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary openCalendarBtn" type="button" id="">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Bank Details, Acct Number  <span class="radStar">*</span></label>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" class="form-control editInput" name="account_num" id="account_num" placeholder="83903674">
                                        </div>
                                    </div>
                                </div>
                                 <div class="form-group col-md-6 col-lg-6">
                                    <label>Sort Code <span class="radStar">*</span></label>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" class="form-control editInput" name="sort_code" id="sort_code" placeholder="04-00-75">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Status <span class="radStar">*</span></label>
                                    <div>
                                        <select class="form-control editInput" name="status" id="status">
                                            <option value="">Please Select</option>
                                            <option value="residential">Residential</option>
                                            <option value="supported_accomodation">Supported Accomodation</option>
                                            <option value="parental">Parental</option>
                                            <option value="foundations_for_life">Foundations for life</option>
                                            <option value="office_staff">Office Staff</option>
                                            <option value="leavers">Leavers</option>
                                        </select>
                                    </div>
                                </div>
                          
                                  <div class="form-group col-md-6 col-lg-6">
                                    <label>Starter Declaration (HMRC Starter Form Completed) <span class="radStar">*</span></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-control editInput" name="starter_declaration" id="starter_declaration">
                                            <option value="1">Yes-A</option>
                                            <option value="2">Yes-B</option>
                                            <option value="3">Yes-C</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Start Date <span class="radStar">*</span></label>
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="start_date" id="start_Date" type="text" autocomplete="off" class="form-control" placeholder="Start Date" readonly>
                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary openCalendarBtn" type="button" id="">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Job Role <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="job_role" id="job_role" placeholder="Support Worker">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>NIN <span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="NIN" id="NIN" placeholder="JT083437B">
                                    </div>
                                </div>
                                <!-- Starter Declaration (HMRC Starter Form Completed) -->
                                <div class="form-group col-md-12 col-lg-12">
                                    <label>Rate of Pay (£) <span class="radStar">*</span></label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="test" class="form-control editInput" name="rate_of_pay" id="rate_of_pay">
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control editInput" name="level" id="level">
                                                <option value="">Select Level</option>
                                                <option value="qualified">Qualified</option>
                                                <option value="unqualified">Unqualified</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                              
                                <div class="form-group col-md-12 col-lg-12">
                                    <label>Probation End Date (6 months) Passed/Extended <span class="radStar">*</span></label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Start date <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput " name="probation_start_date" id="probation_start_date">
                                        </div>
                                        <div class="col-md-4">
                                            <label>End date <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput " name="probation_end_date" id="probation_end_date">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Extended date </label>
                                            <input type="text" class="form-control editInput" name="probation_renew_date" id="probation_renew_date">
                                        </div>
                                    </div>
                                </div>
                                <!-- After Probation Enrolled in Private Medical -->
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>After Probation Enrolled in Private Medical <span class="radStar">*</span></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input mt-0" type="radio" name="probation_enrollered" value="1" id="probation_enrollered_yes">
                                        <label class="form-check-label m-0" for="After_Probation_yes">Yes</label>
                                        <input class="form-check-input mt-0" type="radio" name="probation_enrollered" value="0" id="probation_enrollered_no">
                                        <label class="form-check-label m-0" for="After_Probation_no">No</label>
                                    </div>
                                </div>
                                <!-- Student Loan -->
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Student Loan <span class="radStar">*</span></label>
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-control editInput" name="student_loan" id="student_loan">
                                            <option value="no_student_loan">No Student Loan</option>
                                            <option value="postgraduate">Postgraduate</option>
                                            <option value="plan_1">Plan 1</option>
                                            <option value="plan_2">Plan 2</option>
                                            <option value="plan_4">Plan 4</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>DBS Clear? (Risk Assisment Required or not) </label>
                                    <div>
                                        <input class="form-check-input mt-0" type="radio" name="dbs_clear" value="1" id="dbs_clear_yes">
                                        <label class="form-check-label m-0" for="dbs_clear_yes">Yes</label>
                                        <input class="form-check-input mt-0" type="radio" name="dbs_clear" value="0" id="dbs_clear_no">
                                        <label class="form-check-label m-0" for="dbs_clear_no">No</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>DBS Number </label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="dbs_number" id="dbs_number" placeholder="1813929307">
                                    </div>
                                </div>
                                <!-- On DBS Update Service? -->
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>On DBS Update Service? </label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input class="form-check-input mt-0" type="radio" name="dbs_service_update" value="1" id="DBS_Update_yes">
                                        <label class="form-check-label m-0" for="DBS_Update_yes">Yes</label>
                                        <input class="form-check-input mt-0" type="radio" name="dbs_service_update" value="0" id="DBS_Update_no">
                                        <label class="form-check-label m-0" for="DBS_Update_no">No</label>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Leave Date </label>
                                    <div data-date-viewmode="years" data-date-format="dd-mm-yyyy" data-date="" class="input-group date">
                                        <input name="leave_date" id="leave_date" type="text" autocomplete="off" class="form-control" placeholder="Leave Date">
                                        <span class="input-group-btn datetime-picker2 btn_height">
                                            <button class="btn btn-primary openCalendarBtn" type="button" id="openCalendarLeaveBtn">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Email Address <span class="radStar">*</span></label>
                                    <div>
                                        <input type="email" class="form-control editInput" name="email" id="email" placeholder="example232@hotmail.com">
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-lg-6">
                                    <label>Mobile </label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="mobile" id="mobile" placeholder="07821 155 062">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End row -->
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="saveSatffWorkerModel">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add Staff Modal end here -->

<script>
    const addStaffWorker = "{{ url('/rota/staff-add') }}";
    const deleteStaffWorker = "{{ url('/rota/staff-delete') }}";
</script>

<script type="text/javascript" src="{{ url('public/js/rota/add_staff_worker.js') }}"></script>
@endsection