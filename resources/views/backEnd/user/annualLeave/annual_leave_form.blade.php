@extends('backEnd.layouts.master')

@section('title',' User Annual Leaves')

@section('content')

<?php
	if(isset($u_annual_leave))
	{
		$action = url('admin/user/annual-leave/edit/'.$u_annual_leave->id);
		$task = "Edit";
		$form_id = 'EditUserAnnualLeave';
	}
	else
	{
		$action = url('admin/user/annual-leave/add/'.$user_id);
		$task = "Add";
		$form_id = 'AddUserAnnualLeave';
	}
?>

<style type="text/css">

.form-actions {
margin:20px 0px 0px 0px;    
}
    
 .col-lg-offset-2 .btn.btn-primary {
  margin:0px 10px 0px 0px;
 } 

</style>


 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                    <header class="panel-heading">
                        {{ $task }} Annual Leave Form
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <div class="m-b-15">
                                {{$u_details->name}} Annual Leave
                            </div>
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <!-- <div class="form-group">
                                    <label class="col-lg-2 control-label">Title</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="title" class="form-control" placeholder="title" value="{{ (isset($u_annual_leave->title)) ? $u_annual_leave->title : '' }}" maxlength="255">
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Start Date</label>
                                    <div class="col-lg-10">
                                       <input class="form-control default-date-picker" type="text" value="{{ (isset($u_annual_leave->start_date)) ? date('d-m-Y',strtotime($u_annual_leave->start_date)) : '' }}" placeholder="DD-MM-YYYY" name="start_date" value="" maxlength="10" readonly="">
                                    </div>
                                </div>  
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">End Date</label>
                                    <div class="col-lg-10">
                                       <input class="form-control default-date-picker" type="text" value="{{ (isset($u_annual_leave->end_date)) ? date('d-m-Y',strtotime($u_annual_leave->end_date)) : '' }}" placeholder="DD-MM-YYYY" name="end_date" value="" maxlength="10" readonly="">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Notes</label>
                                    <div class="col-lg-10">
                                        <textarea type="text" name="notes" class="form-control" placeholder="Enter reason" maxlength="1000">{{ (isset($u_annual_leave->notes)) ? $u_annual_leave->notes : '' }}</textarea>
                                    </div>
                                </div>  

                                <!-- <div class="form-group">
                                    <label class="col-lg-2 control-label">Comment</label>
                                    <div class="col-lg-10">
                                        <textarea type="text" name="comment" class="form-control" placeholder="Enter comments" maxlength="1000">{{ (isset($u_annual_leave->comments)) ? $u_annual_leave->comments : '' }}
                                        </textarea>
                                    </div>
                                </div>                  -->
                                
                                <div class="form-actions">
    								<div class="row">
    									<div class="col-lg-offset-2 col-lg-10">
    										<input type="hidden" name="_token" value="{{ csrf_token() }}">
    										<input type="hidden" name="id" value="">
    										<button type="submit" class="btn btn-primary" name="submit1">Save</button>
    										<a href="{{ url('admin/user/annual-leaves/'.$user_id) }}">
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