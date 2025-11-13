@extends('backEnd.layouts.master')

@section('title',' User Pay Rates')

@section('content')

<?php
	if(isset($u_annual_leave))
	{
		$action = url('admin/user/pay-rates/edit/'.$u_annual_leave->id);
		$task = "Edit";
		$form_id = 'EditUserPayRates';
	}
	else
	{
		$action = url('admin/user/pay-rates/add');
		$task = "Add";
		$form_id = 'AddUserPayRates';
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
                                 Annual Leave
                            </div>
                            <form class="form-horizontal" role="form" method="post" action="{{ $action }}" id="{{ $form_id }}" enctype="multipart/form-data">
                                
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