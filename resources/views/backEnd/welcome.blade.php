@extends('backEnd.layouts.master')

@section('title',' Welcome')

@section('content')
<style type="text/css">
	#welcome_form1 .select2-container--default .select2-selection--single {
		background-color: #fff;
		border: 1px solid #aaa;
		border-radius: 4px;
		height: 35px;
		position: relative;
		top: 9px;
		width: 99%;
	}
    .mini-stat-icon.bg-darkgreen{
        background: #4ab661 none repeat scroll 0 0;
    }
    .mini-stat-icon.orange-bg{
        background: #ed6a22 none repeat scroll 0 0;
    }
    .mini-stat-icon.bg-red{
        background: #e13533 none repeat scroll 0 0 !important;
    }
    .cust_select.dataTables_length select {
      width: 100%;
    }
    .cust_select label {
      display: block;
      float: left;
      text-align: left;
    }
    .err{
        color: red;
    }

    .single_plan {
      background: #fff none repeat scroll 0 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      color: #606060;
      margin-bottom: 20px;
      overflow: hidden;
    }
    .plan_type {
      background: #1fb5ad none repeat scroll 0 0;
      color: #fff;
      margin: 0;
      padding: 10px 0;
    }
    .button_buy {
      margin: 20px 0;
    }
    .price {
      color: #1fb5ad;
    }

    .continue-cancel-btn-area {
     margin: 10px 0px 0px 0px;   
    }

    .continue-cancel-btn-area button {
     margin: 0px 10px 0px 3px;   
    }
    
    .btn-default {
     background-color: #eee;
     border-color: #eee;
     color: #aaa;   
    }

    .control-label {
     color:#767676; 
    }

    .form-control {
      color:#767676;    
    }

</style>
<script type="text/javascript" src="{{ url('public/backEnd/js/jquery.validate.js')}}"></script>
<script type="text/javascript" src="{{url('public/backEnd/js/select2.min.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{url('public/backEnd/css/select2.min.css')}}">
 <section id="main-content" class="">
    <section class="wrapper">
        <div class="row">
			<div class="col-lg-12">
                <section class="panel">
                @include('backEnd.common.alert_messages')
                    <header class="panel-heading">
                       Welcome 
                    </header>
                    <div class="panel-body">
                        <div class="position-center">
                            <form class="form-horizontal" role="form" method="post" action="{{ url('admin/welcome') }}" id="welcome_form1">
                            <?php $access_type = Session::get('scitsAdminSession')->access_type; ?>
                            
		                        <div class="form-group m-t-60">
	                                <label class="col-lg-3 col-sm-3 control-label">Select Company</label>
	                                <div class="col-lg-9 col-sm-9">
	                                    <select class="js-example-basic-single form-control" name="company" data-live-search="true">

	                                    	@if($access_type == 'S')
											<option value="">Select Company</option>
											@endif
											
											<?php foreach($companies as $company) { ?>
												<option value="{{ $company['company'] }}" <?php echo ($selected_company_id == $company['id']) ? 'selected':''; ?>  >{{ ucfirst($company['company']) }}</option>
											<?php } ?>	
										
										</select>
	                                </div>
	                            </div>
	                            
	                          	<div class="form-group inp-hide">
									<label class="col-lg-3 col-md-3 col-sm-3 col-xs-12 control-label">Select Home</label>
									<div class="col-lg-9 col-md-9 col-sm-9 col-xs-12">
									    <select class="form-control" name="home">
											<option value="">Select Home</option>
											<?php foreach($homes as $home) { ?>
												<option value="{{ $home['id'] }}" <?php echo ($selected_home_id == $home['id']) ? 'selected':''; ?> >{{ ucfirst($home['title']) }}</option>
											<?php } ?>	
										</select>
									</div>
								</div>

								<div class="form-actions"  style="margin-bottom:60px; ">
									<div class="row">
										<div class="col-lg-10 col-sm-offset-3 col-sm-9">
                                         <div class="continue-cancel-btn-area">   
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
											<input type="hidden" name="user_id" value="{{ (isset($user_info->id)) ? $user_info->id : '' }}">
											<button type="submit" class="btn btn-primary inp-hide" name="submit1">Continue</button>
											<a href="{{ url('admin/dashboard') }}">
												<button type="button" class="btn btn-default" name="cancel">Cancel</button>
											</a>
                                        </div>
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

<!-- Chose Home Package  -->
<?php
    $selected_company_id = '';
    $current_date = '';
    $expiry_date_next_day = '';
    $check_card_detail = '';

    if(Session::get('scitsAdminSession')->access_type == 'O'){
	    // $admin_dtl = Session::get('scitsAdminSession'); echo "<pre>"; print_r($admin_dtl); die;
	    $selected_home_id       = Session::get('scitsAdminSession')->home_id; 
	    $selected_company_id    = App\Home::where('id',$selected_home_id)->value('admin_id');
	    $check_package_dtl      = App\CompanyPayment::where('admin_id',$selected_company_id)
	                                            ->where('company_charges_id','1')
	                                            ->first();
	    // echo "<pre>"; print_r($check_package_dtl); //die;
	    $check_card_detail = App\AdminCardDetail::where('admin_id',$selected_company_id)
	                                            ->first();
	    if(!empty($check_package_dtl)){
	        
	        $current_date           = date('Y-m-d');
	        $expiry_date            = $check_package_dtl['expiry_date'];
	        $expiry_date_next_day   = date('Y-m-d',strtotime('+1 day',strtotime($expiry_date)));
	    }else{
	        $current_date = '';
	        $expiry_date_next_day = '';
	    }
	}
    
?>


<!-- add admin card detail -->
<div class="modal fade" id="card_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog cus-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                <h4 class="modal-title" id="myModalLabel"> Add Admin Card Detail</h4>
            </div>

                <div class="modal-body">
                    <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="card_detail_form">
                    <!-- <form method="post" action="3" id="add_classroom_form">   -->
                        <div class="row">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>Card Holder Name: </label>
                                <input type="text" class="form-control" name="card_holder_name" placeholder="Enter Card Holder Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Card Number: </label>
                                <input type="text" class="form-control" name="card_number" placeholder="Enter Card Number"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> MM/YY: </label>
                                <input type="text" class="form-control" name="card_expiry_date" placeholder="Enter MM/YY"/> 
                                <span class="err"></span>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> CVV: </label>
                                <input type="text" class="form-control" name="cvv" placeholder="Enter CVV Number"/> 
                            </div>
                           <!--  <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>First Name: </label>
                                <input type="text" class="form-control" name="f_name" placeholder="Enter First Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Last Name: </label>
                                <input type="text" class="form-control" name="l_name" placeholder="Enter Last Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Street: </label>
                                <input type="text" class="form-control" name="street" placeholder="Enter Street"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> State: </label>
                                <select class="form-control sel_state" name="state_code">
                                    <option value="">Select State</option>
                                    
                                    <option value=""></option>
                                    
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> City: </label>
                                <select class="form-control cty_lst" name="city_name">
                                    <option value="">Select City</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Zip Code: </label>
                                <input type="text" class="form-control" name="zip_code" placeholder="Enter Zip Code"/> 
                            </div> -->
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">

                                <input type="hidden" name="system_admin_id" value="{{$selected_company_id}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="submit" class="btn btn-primary" value="Submit"/>
                                <!-- <button class="btn btn-primary" type="submit" name="submit">Submit</button> -->
                            </div>
                        </div>
                    </form>
                </div>
        </div><!-- /.modal-content -->
    </div><!--/.modal-dialog -->
</div>

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="package_modal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="float:left; width:100%; background-color: #fff;">
            <div class="modal-header ">
                <!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button> -->
                <h4 class="modal-title">Choose Plan ?</h4>
            </div>
            
            <div class="form-group col-sm-12 col-md-8 col-md-offset-2 col-xs-12 m-t-20 form-horizontal">
                <label class="col-md-2 col-sm-3 col-xs-12 p-0 control-label"> Plan Type: </label>
                <div class="col-md-10 col-sm-9 col-xs-12">
                    <div class="" style="width: 100%">
                        <select class="form-control pln_type" >
                            <option value="Monthly">Monthly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-sm-12">
                <div class="plans_wrap">
                    <?php  
                        $package_type = '';
                        $free_trial_done = '';
                        if(!empty($company_package)){
                            // echo "<pre>"; print_r($company_package); die;
                            // $current_date           = date('Y-m-d');
                            // $expiry_date_next_day   = date('Y-m-d',strtotime('+1 day',strtotime($company_package->expiry_date)));
                            $package_type           = $company_package->package_type;
                            $free_trial_done        = $company_package->free_trial_done;
                            
                        }


                        $free_days              = $company_charges['0']['days'];
                        $silver_price_monthly   = $company_charges['1']['price_monthly'];
                        $gold_price_monthly     = $company_charges['2']['price_monthly'];
                        $platinum_price_monthly = $company_charges['3']['price_monthly'];
                        $silver_price_yearly    = $company_charges['1']['price_yearly'];
                        $gold_price_yearly      = $company_charges['2']['price_yearly'];
                        $platinum_price_yearly  = $company_charges['3']['price_yearly'];

                        foreach ($company_charges as $company_charge) {

                            $range          = $company_charge['home_range'];
                            $range          = explode('-', $range);
                            $range_end      = 'Add upto '.$range['1'].' homes';
                            $price_monthly  = '$'.$company_charge['price_monthly'].'/Month';
                            $price_yearly   = '$'.$company_charge['price_yearly'].'/Year';

                            if($package_type == $company_charge['package_type']){
                                $disabled = 'disabled'; 
                            }else{
                                $disabled = ''; 
                            }
                            if($free_trial_done == '1' && $package_type == 'F'){
                                $disabled = 'disabled'; 
                            }else{
                                $disabled = ''; 
                            }

                            if($company_charge['package_type'] == 'F'){
                                $buy_plan           = 'Get Free';
                                $package_type       = 'Free Trial';
                                $pkg_cls            = 'free';
                                $price_monthly      = 'Free for '.$company_charges['0']['days'].' days';
                                $company_charges_id = '1';

                             // echo "<pre>"; print_r($disable_bt); die; 
                                // if(!empty($disable_btn)){
                                //     foreach ($disable_btn as $key => $value) {
                                //         if($value == 'F'){
                                //             $disabled = 'disabled';
                                //         }else{
                                //             $disabled = '';
                                //         }
                                //     }
                                // }

                                // if(!empty($disabled)){
                                //     if($free_trail == 'disabled' || $disabled == 'disabled'){
                                //         $disabled = 'disabled';
                                //     }else{
                                //         $disabled = '';
                                //     }
                                // }
                            }elseif($company_charge['package_type'] == 'S'){
                                $buy_plan           = 'Buy Plan';
                                $package_type       = 'Silver';
                                $pkg_cls            = 'slvr';
                                $company_charges_id = '2';

                                // if(!empty($disable_btn)){
                                //     foreach ($disable_btn as $key => $value) {
                                //         if($value == 'S'){
                                //             $disabled = 'disabled';
                                //         }else{
                                //             $disabled = '';
                                //         }
                                //     }
                                // }
                                
                            }elseif($company_charge['package_type'] == 'G'){
                                $buy_plan           = 'Buy Plan';
                                $package_type       = 'Gold';
                                $pkg_cls            = 'gld';
                                $company_charges_id = '3';
                                
                                // if(!empty($disable_btn)){
                                //     foreach ($disable_btn as $key => $value) {
                                //         if($value == 'G'){
                                //             $disabled = 'disabled';
                                //         }else{
                                //             $disabled = '';
                                //         }
                                //     }
                                // }
                            }elseif($company_charge['package_type'] == 'P'){
                                $buy_plan           = 'Buy Plan';
                                $package_type       = 'Platinum';
                                $pkg_cls            = 'pltnm';
                                $company_charges_id = '4';
                                
                                // if(!empty($disable_btn)){
                                //     foreach ($disable_btn as $key => $value) {
                                //         if($value == 'P'){
                                //             $disabled = 'disabled';
                                //         }else{
                                //             $disabled = '';
                                //         }
                                //     }
                                // }
                            }   
                        ?>
                    
                        <div class="col-sm-6">
                            <div class="single_plan text-center">
                                <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="choose_package">
                                    <h2 class="plan_type">{{ $package_type }}</h2>
                                    <div class="wrap_rng_pr">
                                        <h2 class="{{ $pkg_cls }} slvr_prce">{{ $price_monthly }}</h2>
                                        <h4 class="range">{{ $range_end }}</h4>
                                        <div class="button_buy"> 
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="company_charges_id" value="{{ $company_charges_id }}">
                                            <input type="hidden" name="system_admin_id" value="{{ $selected_company_id}}">
                                            <input type="hidden" name="package_duration" value="M"> 
                                            <button class="btn btn-primary" type="submit" {{ @(isset($disabled)) ? $disabled : '' }}>{{$buy_plan}}</button>
                                            </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!--main content end-->
@if(Session::get('scitsAdminSession')->access_type == 'O')
<script type="text/javascript">
    $(document).ready(function(){
        /*var current_date        = "{{ $current_date}}";
        var correct_expiry_date = "{{ $expiry_date_next_day}}";
        var check_card_detail   = "{{ $check_card_detail }}";
        // console.log(current_date);
        // console.log(correct_expiry_date);
        if(check_card_detail == ''){
            if((current_date !='') && (correct_expiry_date !='')){
                if(current_date == correct_expiry_date){
                    $('#card_modal').modal({backdrop: 'static', keyboard: false});  
                    $('#card_modal').modal('show');
                }
            }
        }else{
            if((current_date !='') && (correct_expiry_date !='') ){
                if(current_date == correct_expiry_date){
                    $('#package_modal').modal({backdrop: 'static', keyboard: false}); 
                    $('#package_modal').modal('show');
                }
            }
        }*/
    });
</script>
@endif
<!-- Chose Home Package  -->



<script>
	$(document).ready(function()
	{	
		/*$('select[name=\'company\']').val('');*/
		
		$('select[name=\'company\']').change(function(event)
		{ 
	        var company = $(this).val();
			if(company == ''){
			//$('.company_error').html('<label for="company_name" generated="true" class="error">This field is required.</label>');
			return false;
			} else{
			//$('.company_error').html('');
			}
			$('.loader').show();
	        $.ajax({
				type: 'get',
				url : "{{ url('admin/welcome/get-homes') }}"+'/'+company,
				success:function(resp)
				{
				if(resp != '')
	                {
						$('select[name=\'home\'] option').remove();
						$('select[name=\'home\']').append('<option value="">Select Home</option>'+resp);
						$('select[name=\'home\']').closest('.form-group').removeClass('inp-hide');
						$('.loader').hide();
	                }
	            else
	                {
                      $('select[name=\'home\'] option').remove();
        			  $('select[name=\'home\']').append('<option value="">Select Home</option>');
	                  $('select[name=\'home\']').closest('.form-group').addClass('inp-hide');
	                  $('input[name=\'username\']').closest('.form-group').addClass('inp-hide');
	                  $('input[name=\'password\']').closest('.form-group').addClass('inp-hide');
	                  $('.btn').addClass('inp-hide');          
	                  $('.loader').hide();        
	                }
				}
			});
	});
});
</script>

<script type="text/javascript">
	$(document).ready(function() {
		$('.js-example-basic-single').select2();
	});	
</script>


<script type="text/javascript">
    $("input[name='card_expiry_date']").each(function(){
        $(this).on("change keyup paste", function (e) {
            
            var output,
                $this = $(this),
                input = $this.val();

            if(e.keyCode != 8) {
                input    = input.replace(/[^0-9]/g, '');
                var area = input.substr(0, 2);
                var pre  = input.substr(2, 2);
                // var tel  = input.substr(5, 4);
                if (area.length < 2) {
                    output =  area;
                } else if (area.length == 2 && pre.length < 3) {

                    var ar_val = input.substr(0, 2);
                    var pre_val  = input.substr(2, 2);
                    var current_year = new Date().getFullYear().toString().substr(-2);
                    if(ar_val > 12){
                        $('.err').text('Please enter a valid month');
                    } 

                    if(pre.length == 2 && pre_val<=current_year){
                        $('.err').text('Please enter a valid year');    
                    } 

                    if(ar_val <= 12 && pre.length == 2 && pre_val >= current_year){
                        $('.err').text('');     
                    }
                    output = area + "/" + pre;
                }
              
                $this.val(output);
            }
        });
    });
</script>

<script type="text/javascript">
    $('#card_detail_form').validate({
        rules:{
            card_holder_name:{
                required:true,
                // minlength:2,
                // maxlength:100,
                regex:/^[a-zA-Z ]+$/
            },
            card_number:{
                required:true,
                minlength:10,
                maxlength:16,
                regex:/^[0-9]+$/
            },
            card_expiry_date:{
                required:true,
                // minlength:5,
                // maxlength:5,
                // regex:/^[0-9]+$/
            },
            cvv:{
                required:true,
                minlength:3,
                maxlength:3,
                number:true
            },
            f_name:{
                required:true,
                regex:/^[a-zA-Z ]+$/
            },
            l_name:{
                required:true,
                regex:/^[a-zA-Z ]+$/
            },
            street:{
                required:true,
            },
            city:{
                required:true,
            },
            state_code:{
                required:true,
            },
            zip_code:{
                required:true,
                minlength:5,
                maxlength:5,
                regex:/^[0-9]+$/
            },
        },
        messages:{
            card_holder_name:{
                regex: 'This field can only consist of alphabets'
            },
            card_number:{
                regex: 'This field can only consist of digits',
            },
            card_expiry_date:{
                // regex: 'This field can only consist of digits',
            },
            cvv:{
                regex: 'This field can only consist of digits',
            },
            f_name:{
                regex: 'This field can only consist of alphabets'
            },
            l_name:{
                regex: 'This field can only consist of alphabets'
            },
            zip_code:{
                regex: 'This field can only consist of digits',
            },

        },

        submitHandler:function(form){

            var err_txt = $('.err').text();
            if(err_txt != ''){
                return false;
            }else{
                var form_data = $('#card_detail_form').serialize();
                
                $('.loader').show();
                $.ajax({
                    type:'post',
                    url:"{{ url('admin/system-admin/home/card-detail')}}",
                    data:form_data,
                    success:function(resp){
                        // console.log(resp);
                        if(resp == '1'){
                            $('#card_modal').modal('hide');
                            $('#package_modal').modal('show');
                        }
                        $('.loader').hide();
                    }

                });
                // form.submit();
            }
            
        }
    });
</script>
<script type="text/javascript">
    var free_days               = 'Free for '+'{{ $free_days }}'+' days';
    var silver_price_monthly    = '$'+'{{ $silver_price_monthly }}'+'/'+'Month';
    var gold_price_monthly      = '$'+'{{ $gold_price_monthly }}'+'/'+'Month';
    var platinum_price_monthly  = '$'+'{{ $platinum_price_monthly }}'+'/'+'Month';
    var silver_price_yearly     = '$'+'{{ $silver_price_yearly }}'+'/'+'Year';
    var gold_price_yearly       = '$'+'{{ $gold_price_yearly }}'+'/'+'Year';
    var platinum_price_yearly   = '$'+'{{ $platinum_price_yearly }}'+'/'+'Year';

    $(document).on('change','.pln_type',function(){
        var plan_type = $(this).val();

        if(plan_type == 'Yearly'){
            $('.free').text(free_days);
            $('.slvr').text(silver_price_yearly);
            $('.gld').text(gold_price_yearly);
            $('.pltnm').text(platinum_price_yearly);
            $('input[name=package_duration]').val('Y');
        }else{
            $('.free').text(free_days);
            $('.slvr').text(silver_price_monthly);
            $('.gld').text(gold_price_monthly);
            $('.pltnm').text(platinum_price_monthly);
            $('input[name=package_duration]').val('M');
        }
    });
</script>
@endsection