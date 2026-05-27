@extends('backEnd.layouts.master')

@section('title',' User Timesheet')

@section('content')

<?php
	if(isset($timeSheet))
	{
		$action = url('admin/user/timesheet/edit/'.$timeSheet->id);
		$task = "Edit";
		$form_id = 'EditUserAnnualLeave';
	}
	else
	{
		$action = url('admin/user/timesheet/add/'.$user_id);
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
                        {{ $task }} User Timesheet Form
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <div class="m-b-15">
                                {{$u_details->name}} Timesheet
                            </div>
                            @include('backEnd.common.alert_messages')
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                <!-- <div class="form-group">
                                    <label class="col-lg-2 control-label">Title</label>
                                    <div class="col-lg-10">
                                        <input type="text" name="title" class="form-control" placeholder="title" value="{{ (isset($u_annual_leave->title)) ? $u_annual_leave->title : '' }}" maxlength="255">
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Select Category</label>
                                    <div class="col-lg-10">
                                       <select name="category_id" id="category_id" class="form-control">
                                            <option selected="" disabled="">Select Category</option>
                                            <option value="1" <?php if(isset($timeSheet->category_id) && $timeSheet->category_id == 1){echo 'selected';}?>>Sleep</option>
                                            <option value="2" <?php if(isset($timeSheet->category_id) && $timeSheet->category_id == 2){echo 'selected';}?>>Disturbance</option>
                                            <option value="3" <?php if(isset($timeSheet->category_id) && $timeSheet->category_id == 3){echo 'selected';}?>>Wake Night</option>
                                            <option value="4" <?php if(isset($timeSheet->category_id) && $timeSheet->category_id == 4){echo 'selected';}?>>Annual Leave</option>
                                            <option value="5" <?php if(isset($timeSheet->category_id) && $timeSheet->category_id == 5){echo 'selected';}?>>On Call</option>
                                       </select>
                                    </div>
                                </div>  
                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Hours</label>
                                    <div class="col-lg-10">
                                       <input type="text" id="hours" name="hours" class="form-control" placeholder="Enter Hours" value="<?php if(isset($timeSheet->hours) && $timeSheet->hours != ''){echo $timeSheet->hours;}?>">
                                       <small class="form-text text-muted">
                                            Please enter hours in decimal format.  
                                            Example: <b>2.15</b> means 2 hours 15 minutes, <b>1.30</b> means 1 hour 30 minutes.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-lg-2 control-label">Comments</label>
                                    <div class="col-lg-10">
                                        <textarea type="text" name="comments" class="form-control" placeholder="Enter comments" maxlength="1000">{{ (isset($timeSheet->comments)) ? $timeSheet->comments : '' }}</textarea>
                                    </div>
                                </div>  
                                <div class="form-actions">
    								<div class="row">
    									<div class="col-lg-offset-2 col-lg-10">
    										<input type="hidden" name="_token" value="{{ csrf_token() }}">
    										<button type="submit" class="btn btn-primary">Save</button>
    										<a href="{{ url('admin/user/timesheet/'.$user_id) }}">
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
    $(document).on('input','#hours', function(){
        this.value = this.value.replace(/[^0-9.]/g, '');
        if ((this.value.match(/\./g) || []).length > 1) {
            this.value = this.value.slice(0, -1);
        }
        let floatVal = parseFloat(this.value);
        if (!isNaN(floatVal)) {
            if (floatVal <= 0) this.value = '';
            if (floatVal > 24) this.value = '24.00';
        }
        let parts = this.value.split('.');
        let hours = parseInt(parts[1]);
        if(parts[1] && parts[1].length > 2){
            parts[1] = parts[1].slice(0, 2);
            this.value = parts.join('.');
        }
        if(parts[1] && parts[1] > '59'){
            let update=parts[0]=Number(parts[0])+Number(1);
            this.value = update+'.00';
        }
    });
</script>

@endsection