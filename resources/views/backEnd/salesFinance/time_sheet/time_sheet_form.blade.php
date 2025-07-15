@extends('backEnd.layouts.master')
@section('title',' Add New')
@section('content')
<style>
    .position-center {
        width: 80%;
        margin: 0 auto;
    }

    .pad148 {
        padding: 0px 13.5%;
    }
</style>

<!--main content start-->
<section id="main-content" class="">
    <div class="wrapper">
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                <div class="panel">
                    <header class="panel-heading">
                        Add Time Sheet
                    </header>

                    @if (session('success'))
                    <div class="aalert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                    </div>
                    @endif


                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" method="POST" action="{{ url('admin/sales-finance/time-sheet/save') }}" role="form">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">User <span class="radStar">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="hidden" name="time_sheet_id" value="{{ $timeSheet->id ?? '' }}">
                                            <select class="form-control" id="" name="user_id">

                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ isset($timeSheet) && $timeSheet->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('user_id')
                                            <div class="radStar">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Date <span class="radStar">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="timeSheetDt" name="date" placeholder="" value="{{ isset($timeSheet) ? \Carbon\Carbon::parse($timeSheet->date)->format('d-m-Y') : '' }}">
                                            @error('date')
                                            <div class="radStar">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Hours </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" id="" name="hours" placeholder="" value="{{ isset($timeSheet) ? $timeSheet->hours : '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Sleep </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="" name="sleep" value="{{ isset($timeSheet) ? $timeSheet->sleep : '' }}" id="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Wake Night </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="" name="wake_night" value="{{ isset($timeSheet) ? $timeSheet->wake_night : '' }}" id="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Disturbance </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="" name="disturbance" value="{{ isset($timeSheet) ? $timeSheet->disturbance : '' }}" id="">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Annual Leave </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="" name="annual_leave" value="{{ isset($timeSheet) ? $timeSheet->annual_leave : '' }}" id="">
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">On Call </label>
                                        <div class="col-lg-9">
                                            <input type="text" class="form-control" placeholder="" name="on_call" id="" value="{{ isset($timeSheet) ? $timeSheet->on_call : '' }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Comments </label>
                                        <div class="col-lg-9">
                                            <textarea class="form-control" placeholder="Enter your comments" name="comments" rows="5">{{ isset($timeSheet) ? $timeSheet->comments : '' }}</textarea>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-6 form-group">
                                        <label for="" class="col-lg-3 col-sm-3 control-label">Upload file <span class="radStar">*</span></label>
                                        <div class="col-lg-9">
                                            <input type="file" class="form-control" name="file" id="" value="" >
                                        </div>
                                    </div> -->
                                    <div class="col-md-12 form-group">
                                        <div class="pad148">
                                            <button type="submit" class="btn btn-primary">Save</button>
                                            <button type="submit" class="btn btn-default">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- page end-->
    </div>
</section>
<script>
    $(document).ready(function () {
    // loadTimeSheetTable(); // Loads all data

    $('#timeSheetDt').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true, // Optional: close picker after selection
        todayHighlight: true // Optional: highlight today's date
    });
    $('#timeSheetDt').on('change', function () {
        $('#timeSheetDt').datepicker('hide');
    });
});
</script>
<!-- <script type="text/javascript" src="{{ url('public\frontEnd\js\personal\timeSheet.js') }}"></script> -->
@endsection
