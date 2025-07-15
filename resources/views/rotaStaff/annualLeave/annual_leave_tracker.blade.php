@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Annual Leave')
<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .omegaCareAnnual thead tr th,
    .omegaCareAnnual tbody tr td {
        white-space: nowrap;
    }
</style>

<!--main content start-->
<section class="wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 p-0">
                <div class="panel">
                    <header class="panel-heading px-5">
                        <h4>Annual Leave Tracker</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <!-- <div class="col-md-4"></div>
                                <div class="col-md-4"></div> -->
                                <div class="col-md-4">
                                    <div class="jobsection justify-content-end mb-0">
                                        @php
                                        $startYear = 2000;
                                        $currentYear = date('Y');
                                        @endphp

                                        <select name="year" class="form-control">
                                            @for ($year = $startYear; $year <= $currentYear; $year++)
                                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="maimtable productDetailTable mb-4 table-responsive">
                                <table id="leaveTracker" class="table border-top border-bottom tablechange omegaCareAnnual" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Start Date</th>
                                            <th>A/L Entitlement </th>
                                            <th>October</th>
                                            <th>November</th>
                                            <th>December </th>
                                            <th>January</th>
                                            <th>February</th>
                                            <th>March</th>
                                            <th>April</th>
                                            <th>May </th>
                                            <th>June</th>
                                            <th>July</th>
                                            <th>August</th>
                                            <th>September</th>
                                            <th>Total A/L Hours Used</th>
                                            <th>Total A/L Hours left</th>
                                            <th>Carried over</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>2.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>3.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>4.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>5.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>6.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>7.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>8.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>9.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                        <tr>
                                            <td>10.</td>
                                            <td>Aaron Hill </td>
                                            <td>Residential Care</td>
                                            <td>4/3/2023</td>
                                            <td>224</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>224</td>
                                            <td>0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal -->
<!-- <div class="modal fade" id="AddAnnualLeave" tabindex="-1" aria-labelledby="AddAnnualLeaveLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="error-text"></div>
            <form action="" id="addLeaveTrackerForm" class="customerForm">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title" id="modalTitle">Add Annual Leaves</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="row formDtail">
                                <div class="col-md-12 form-group">
                                    <label> User </label>
                                    <select class="form-control editInput selectOptions" name="user_id" id="user_id">
                                        <option>Select User</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label> Department <span class="radStar">*</span></label>
                                    <select class="form-control editInput selectOptions" name="department">
                                        <option selected="" disabled="">Select Department</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label> Months <span class="radStar">*</span></label>
                                    <select class="form-control editInput selectOptions" name="month">
                                        <option selected="" disabled="">Select Months</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">Auguest</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <label> Start Date <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput" id="Leave_startDate" name="start_date" placeholder="Start Date">
                                        </div>
                                        <div class="col-sm-6 pe-3">
                                            <label> A/L Entitlement <span class="radStar">*</span></label>
                                            <input type="text" class="form-control editInput" id="entitlement" name="entitlement" placeholder="A/L Entitlement">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <div class="row">
                                        <div class="col-sm-6 pe-3">
                                            <label>Tot. A/L Hrs Used</label>
                                            <input type="text" class="form-control editInput" name="annual_leave_used" placeholder="4">
                                        </div>
                                        <div class="col-sm-6 ps-3">
                                            <label>Tot. A/L Hrs left </label>
                                            <input type="text" class="form-control editInput" id="occupancy" name="annual_leave_left" placeholder="2">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>MAT leave<span class="radStar">*</span></label>
                                    <div>
                                        <input type="text" class="form-control editInput" name="account_number" placeholder="MAT leave">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer customer_Form_Popup">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="saveAnnualLeave">Save</button>
                </div>
            </form>
        </div>
    </div>
</div> -->

<script src="{{ url('public\js\rota\leave_tracker.js') }}"></script>

<script>
    // getUserData = "{{ url('rota/get-user-data') }}";
</script>


@endsection