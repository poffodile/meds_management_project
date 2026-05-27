@extends('backEnd.layouts.master')

@section('title',' User Sick Leaves')

@section('content')

<?php
	if(isset($u_sick_leave))
	{
		$action = url('admin/user/sick-leave/edit/'.$u_sick_leave->id);
		$task = "Edit";
		$form_id = 'EditUserSickLeave';
	}
	else
	{
		$action = url('admin/user/sick-leave/add/'.$user_id);
		$task = "Add";
		$form_id = 'AddUserSickLeave';
	}
?>

<style type="text/css">
 
 .form-actions {
 margin:20px 0px 0px 0px;   
 }

 .col-lg-offset-2 .btn.btn-primary {
  margin:0px 10px 0px 0px;  
 }



 .radio-btn {
    display: inline-block;
    margin-right: 10px;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 6px 12px;
    background: #fff;
    transition: all 0.2s;
}

.radio-btn input {
    /* display: none; */
}

.radio-btn span {
    font-size: 14px;
    color: #333;
}

/* Selected state */
.radio-btn input:checked + span {
    color: #007bff; /* text color */
    font-weight: 600;
}

/* Hover effect */
.radio-btn:hover {
    background: #f1faff;
    border-color: #007bff;
}

</style>

 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{ $task }} Sick Leave Form
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <div class="m-b-15">
                                {{$u_details->name}} Sick Leave
                            </div>
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <!-- <div class="form-group">
                                    <label class="col-lg-2 control-label">Title</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="title" class="form-control" placeholder="title" value="{{ (isset($u_sick_leave->title)) ? $u_sick_leave->title : '' }}" maxlength="255">
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Ongoing absence</label>
                                   <div class="col-lg-10">
                                        <label class="radio-btn">
                                            <input type="radio" name="ongoing_absence" value="1" <?php if(isset($u_sick_leave->ongoing_absence) && $u_sick_leave->ongoing_absence == 1){echo 'checked';}?>>
                                            <span>Yes</span>
                                        </label>
                                        <label class="radio-btn">
                                            <input type="radio" name="ongoing_absence" value="0" <?php if(isset($u_sick_leave->ongoing_absence) && $u_sick_leave->ongoing_absence == 0){echo 'checked';}?>>
                                            <span>No</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Start Date</label>
                                    <div class="col-lg-5">
                                       <input class="form-control default-date-picker" type="text" value="{{ (isset($u_sick_leave->start_date)) ? date('d-m-Y',strtotime($u_sick_leave->start_date)) : '' }}" placeholder="DD-MM-YYYY" name="start_date" value="" maxlength="10" readonly="">
                                    </div>
                                    <div class="col-lg-5">
                                       <select name="start_date_full_half" id="" class="form-control">
                                            <option value="1">Full Day</option>
                                            <option value="2">Half Daye</option>
                                       </select>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">End Date</label>
                                    <div class="col-lg-5">
                                       <input class="form-control default-date-picker" type="text" value="{{ (isset($u_sick_leave->end_date)) ? date('d-m-Y',strtotime($u_sick_leave->end_date)) : '' }}" placeholder="DD-MM-YYYY" name="end_date" value="" maxlength="10" readonly="">
                                    </div>
                                    <div class="col-lg-5">
                                       <select name="end_date_full_half" id="" class="form-control">
                                            <option value="1" <?php if(isset($u_sick_leave->end_date_full_half) && $u_sick_leave->end_date_full_half == 1){echo 'selected';}?>>Full Day</option>
                                            <option value="2" <?php if(isset($u_sick_leave->end_date_full_half) && $u_sick_leave->end_date_full_half == 2){echo 'selected';}?>>Half Daye</option>
                                       </select>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Working days missed</label>
                                    <div class="col-lg-10">
                                       <input class="form-control" type="number" value="{{ (isset($u_sick_leave->days)) ? $u_sick_leave->days : '' }}" name="days" value="">
                                    </div>
                                </div> 

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Notes</label>
                                    <div class="col-lg-10">
                                        <textarea type="text" name="notes" class="form-control" placeholder="Enter reason" maxlength="1000">{{ (isset($u_sick_leave->notes)) ? $u_sick_leave->notes : '' }}</textarea>
                                    </div>
                                </div>  

                                <!-- <div class="form-group">
                                    <label class="col-lg-2 control-label">Comment</label>
                                    <div class="col-lg-10">
                                        <textarea type="text" name="comment" class="form-control" placeholder="Enter comments" maxlength="1000">{{ (isset($u_sick_leave->comments)) ? $u_sick_leave->comments : '' }}</textarea>
                                    </div>
                                </div>                  -->
                                
                                <div class="form-actions">
    								<div class="row">
    									<div class="col-lg-offset-2 col-lg-10">
    										<input type="hidden" name="_token" value="{{ csrf_token() }}">
    										<input type="hidden" name="id" value="">
    										<button type="submit" class="btn btn-primary" name="submit1">Save</button>
    										<a href="{{ url('admin/user/sick-leaves/'.$user_id) }}">
    											<button type="button" class="btn btn-default" name="cancel">Cancel</button>
    										</a>
    									</div>
    								</div>
    							</div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
	</section>
</section>						

<script>
    $(document).ready(function() {

        $('.default-date-picker').datepicker({
            //format: 'yyyy-mm-dd'
            format: 'dd-mm-yyyy',
            // startDate: today,
            // minView : 2
            // maxDate:'+13-02-2017'
        });
    });
</script>

@endsection