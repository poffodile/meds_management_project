@extends('frontEnd.layouts.master')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('title','Time Sheet')

<link rel="stylesheet" type="text/css" href="{{ url('public/frontEnd/jobs/css/custom.css')}}" />
@section('content')

<style>
    .formDtail .text-danger {
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
                        <h4>Time Sheet</h4>
                    </header>
                    <div class="panel-body">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="jobsection justify-content-end">
                                        <div>
                                            <select name="" class="form-control editInput selectOptions" id="getDataOnUsers">
                                                <option value="0">Please Select</option>
                                                @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <a href="javaScript:void(0)" type="button" class="profileDrop openTimeSheetModel" data-action="add"> <i class="fa fa-plus"></i> Add</a>
                                    </div>
                                </div>
                            </div>
                            <div class="productDetailTable mb-4 table-responsive">
                                <table class="table border-top border-bottom tablechange" id="timeSheetTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Hours</th>
                                            <th>Sleep</th>
                                            <th>Wake Night </th>
                                            <th>DIsturbance </th>
                                            <th>Annual Leave</th>
                                            <th>On Call</th>
                                            <th>Comments </th>
                                            <th>Action</th>
                                        </tr>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" style="text-align:right">Total:</th>
                                            <th></th> <!-- Hours total -->
                                            <th></th> <!-- Sleep total -->
                                            <th></th> <!-- Wake Night total -->
                                            <th></th> <!-- Disturbance total -->
                                            <th></th> <!-- Annual Leave total -->
                                            <th></th> <!-- On Call total -->
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    </thead>
                                    <tbody>
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
<div class="modal fade" id="addTimeSheetModal" tabindex="-1" aria-labelledby="addTimeSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                <h4 class="modal-title" id="modalTitle">Add Staff</h4>
            </div>
            <div class="modal-body">
                <form action="" id="time_sheet" class="customerForm ">
                    <input type="hidden" id="time_sheetId" name="id">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xl-12">
                            <div class="row formDtail ps-4 pe-4">
                                <div class="col-md-6 form-group">
                                    <input type="hidden" id="time_sheet_id" name="time_sheet_id">
                                    <label> User <span class="radStar">*</span> </label>
                                    <select name="user_id" id="user_id" class="form-control editInput">
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Date <span class="radStar">*</span></label>
                                    <input type="text" class="form-control editInput" id="timeSheetDt" name="date">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Hours </label>
                                    <input type="text" class="form-control editInput" id="hours" name="hours">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Sleep </label>
                                    <input type="text" class="form-control editInput" id="sleep" name="sleep">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Wake Night </label>
                                    <input type="text" class="form-control editInput" id="wake_night" name="wake_night">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Disturbance </label>
                                    <input type="text" class="form-control editInput" id="disturbance" name="disturbance">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> Annual Leave </label>
                                    <input type="text" class="form-control editInput" id="annual_leave" name="annual_leave">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label> On Call </label>
                                    <input type="text" class="form-control editInput" id="on_call" name="on_call">
                                </div>
                                <div class="col-md-12 form-group">
                                    <label> Comments <span class="radStar">*</span></label>
                                    <textarea class="form-control textareaInput" placeholder="Type your comments..." rows="3" id="comments" name="comments"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer customer_Form_Popup">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-warning" id="save_time_sheet">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add Staff Modal end here -->

<script>
    const timeSheetSaveUrl = "{{ url('/my-profile/time-sheet/add') }}";
    const timeSheetEditUrl = "{{ url('/my-profile/time-sheet/edit') }}";
    const deleteTimeSheet = "{{ url('/my-profile/time-sheet/delete') }}";
    const getData = "{{ url('/my-profile/time-sheet') }}";
</script>

<script type="text/javascript" src="{{ url('public\frontEnd\js\personal\timeSheet.js') }}"></script>
@endsection