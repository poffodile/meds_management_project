@extends('backEnd.layouts.master')
@section('title',' Homelist')
@section('content')
<script type="text/javascript" src="{{ url('public/backEnd/js/jquery.validate.js')}}"></script>
<style type="text/css">
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
    .err{
        color: red;
    }
</style>
<?php 
    /*if($company_package != ''){

        if($company_package->free_trial_done){
            $free_trail = 'disabled';
        }else{
            $free_trail = '';
        }
        
        $range          = $company_package->home_range;
        $range          = explode('-', $range);
        $range_end      = $range['1'];

        if($current_date < $company_package->expiry_date){
            
            if($company_package->homes_added < $range_end && $company_package->status == '1'){
                $add_home_url   = 'add_home'; // Add Home allowed
            }else{
                $add_home_url   = 'select_package'; // choose package
            }
        }else{
            $add_home_url = 'select_package'; // choose package
        }
    }else{
        $free_trail     = '';
        $disabled       = '';
        $add_home_url   = 'select_package';
    }*/
    $free_trail = '';
    $disabled = '';
    $add_home_url = 'add_home';
?>
<!--main content start-->
<section id="main-content">
    <section class="wrapper">
    <!-- page start-->
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <div class="panel-body">
                    <div class="adv-table editable-table ">
                        <div class="clearfix">
                            <div class="btn-group">
                                <!-- <a href="{{ url('admin/homelist/add') }}"> -->
                                <a href="javascript:;">
                                    <button id="editable-sample_new" class="btn btn-primary package_mdl">
                                        Add Home <i class="fa fa-plus"></i>
                                    </button>
                                </a>
                            </div>
                            @include('backEnd.common.alert_messages')
                        </div>
                        <div class="space15"></div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div id="editable-sample_length" class="dataTables_length">
                                    <form method='post' action="{{ url('admin/homelist') }}" id="records_per_page_form">
                                        <label>
                                            <select name="limit"  size="1" aria-controls="editable-sample" class="form-control xsmall select_limit">
                                                <option value="10" {{ ($limit == '10') ? 'selected': '' }}>10</option>
                                                <option value="20" {{ ($limit == '20') ? 'selected': '' }}>20</option>
                                                <option value="30" {{ ($limit == '30') ? 'selected': '' }}>30</option>
                                                <!-- <option value="all" {{ ($limit == 'all') ? 'selected': '' }}>All</option> -->
                                            </select> records per page
                                        </label>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <form method='get' action="{{ url('admin/homelist') }}">
                                    <div class="dataTables_filter" id="editable-sample_filter">
                                        <label>Search: <input name="search" type="text" value="{{ $search }}" aria-controls="editable-sample" class="form-control medium" placeholder="Enter Name" ></label>
                                        <!-- <button class="btn search-btn" type="submit"><i class="fa fa-search"></i></button>   -->
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered" id="editable-sample">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if($homelist->isEmpty()){ 
                                        echo '<tr style="text-align:center">
                                                <td colspan="4">No home found.</td>
                                              </tr>';
                                    } 
                                    else
                                    {
                                        foreach($homelist as $key => $value) 
                                        {  
                                            $image = home.'/default_home.png';
                                            if(!empty($value->image))
                                            {
                                                $image = home.'/'.$value->image;
                                            }
                                        ?>
                                    <tr class="">
                                        <td>{{ ucfirst($value->title) }}</td>
                                        <td><img src = "{{ $image }}" height="50px" width="auto"></td>
                                        <td class="action-icn">
                                            <a href="{{ url('admin/homelist/home-admin/'.$value->id) }}" class="user"><i data-toggle="tooltip" title="Add Home admins" class="fa fa-user-o "></i></a>
                                            
                                            <a href="{{ url('admin/homelist/edit/'.$value->id) }}" class="edit"><i data-toggle="tooltip" title="Edit" class="fa fa-edit"></i></a>

                                            <a href="{{ url('admin/homelist/delete/'.$value->id) }}" class="delete"><i data-toggle="tooltip" title="Delete" class="fa fa-trash-o"></i></a>
                                        </td>
                                    </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>

                        @if($homelist->links() !== null) 
                        {{ $homelist->links() }}
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
    <!-- page end-->
    </section>
</section>
<!--main content end-->

<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="PackageModal" class="modal fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="float:left; width:100%; background-color: #fff;">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
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
                        $price_monthly  = '£'.$company_charge['price_monthly'].'/Month';
                        $price_yearly   = '£'.$company_charge['price_yearly'].'/Year';

                        if($company_charge['package_type'] == 'F'){
                            $buy_plan           = 'Get Free';
                            $package_type       = 'Free Trial';
                            $pkg_cls            = 'free';
                            $price_monthly      = 'Free for '.$company_charges['0']['days'].' days';
                            $company_charges_id = '1';

                         // echo "<pre>"; print_r($disable_btn); die; 
                            if(!empty($disable_btn)){
                                foreach ($disable_btn as $key => $value) {
                                    // echo $value; die;
                                    if($value == 'F'){
                                        $disabled = 'disabled';
                                        break;
                                    }else{
                                        $disabled = '';
                                    }
                                }
                            }
                            // echo $disabled; die;
                            if(!empty($disabled)){
                                if($free_trail == 'disabled' || $disabled == 'disabled'){
                                    $disabled = 'disabled';
                                }else{
                                    $disabled = '';
                                }
                            }
                        }elseif($company_charge['package_type'] == 'S'){
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Silver';
                            $pkg_cls            = 'slvr';
                            $company_charges_id = '2';

                            if(!empty($disable_btn)){
                                foreach ($disable_btn as $key => $value) {
                                    if($value == 'S'){
                                        $disabled = 'disabled';
                                        break;
                                    }else{
                                        $disabled = '';
                                    }
                                    
                                }
                            }
                            
                        }elseif($company_charge['package_type'] == 'G'){
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Gold';
                            $pkg_cls            = 'gld';
                            $company_charges_id = '3';
                            
                            if(!empty($disable_btn)){
                                foreach ($disable_btn as $key => $value) {
                                    if($value == 'G'){
                                        $disabled = 'disabled';
                                        break;
                                    }else{
                                        $disabled = '';
                                    }
                                    
                                }
                            }
                        }elseif($company_charge['package_type'] == 'P'){
                            $buy_plan           = 'Buy Plan';
                            $package_type       = 'Platinum';
                            $pkg_cls            = 'pltnm';
                            $company_charges_id = '4';
                            
                            if(!empty($disable_btn)){
                                foreach ($disable_btn as $key => $value) {
                                    if($value == 'P'){
                                        $disabled = 'disabled';
                                        break;
                                    }else{
                                        $disabled = '';
                                    }
                                    
                                }
                            }
                        }   
                    ?>
                    @if(empty($admin_card_detail))
                        <div class="col-sm-6">
                            <div class="single_plan text-center">
                                <!-- <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="choose_package"> -->
                                <form id="choose_package">
                                    <h2 class="plan_type">{{ $package_type }}</h2>
                                    <div class="wrap_rng_pr">
                                        <h2 class="{{ $pkg_cls }} slvr_prce">{{ $price_monthly }}</h2>
                                        <h4 class="range">{{ $range_end }}</h4>
                                        <div class="button_buy">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <input type="hidden" name="company_charges_id" value="{{ $company_charges_id }}">
                                            <input type="hidden" name="system_admin_id" class="system_admin_id" value="{{ $admin_id }}">
                                            <input type="hidden" name="package_duration" value="M"> 
                                            <button class="btn btn-primary buy_plan" type="button" {{ @(isset($disabled)) ? $disabled : '' }}>{{$buy_plan}}</button>
                                            </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
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
                                            <input type="hidden" name="system_admin_id" value="{{ $admin_id }}">
                                            <input type="hidden" name="package_duration" value="M"> 
                                            <button class="btn btn-primary" type="submit" {{ @(isset($disabled)) ? $disabled : '' }}>{{$buy_plan}}</button>
                                            </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- add admin card detail -->
<div class="modal fade" id="admin_card_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog cus-modal" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"> Add Admin Card Detail</h4>
            </div>

                <div class="modal-body">
                    <form action="{{ url('admin/system-admin/home/company-package-type') }}" method="post" id="add_admin_card_detail">
                    <!-- <form method="post" action="3" id="add_classroom_form">   -->
                        <div class="row">
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label>Card Holder Name: </label>
                                <input type="text" class="form-control" name="card_holder_name" placeholder="Enter Card Holder Name"/> 
                            </div>
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> Card Number: </label>
                                <input type="text" class="form-control" name="card_number" placeholder="Enter Card Number"/> 
                            </div><div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> MM/YY: </label>
                                <input type="text" class="form-control" name="mm_yy" placeholder="Enter MM/YY"/> 
                                <span class="err"></span>
                            </div><div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <label> CVV: </label>
                                <input type="text" class="form-control" name="cvv" placeholder="Enter CVV Number"/>

                            </div>
                            <!-- <div class="form-group col-md-12 col-sm-12 col-xs-12">
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
                            </div>   -->   
                                <!-- <input type="hidden" name="package_type_data" value=""> -->
                            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" class="cmpny_chr_id" name="company_charges_id" value="">
                                <input type="hidden" class="sys_adm_id" name="system_admin_id" value="">
                                <input type="hidden" name="package_duration" value="M"> 
                                <input type="submit" class="btn btn-primary" value="Submit"/>
                                <!-- <button class="btn btn-primary" type="submit" name="submit">Submit</button> -->
                            </div>
                        </div>
                    </form>
                </div>
        </div><!-- /.modal-content -->
    </div><!--/.modal-dialog -->
</div>
<!--script for this page only-->
<script>
    $('document').ready(function(){
        $('.send-set-pass-link-btn').click(function(){
            var user_id = $(this).attr('id'); 
            $.ajax({
                type:'get',
                url : '{{ url("admin/users/send-set-pass-link") }}'+'/'+user_id,
                success:function(resp){
                    alert(resp); 
                }
            });
            return false;
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
       
        $(document).on('click','.package_mdl',function(){
         
            var add_home_url = "{{ $add_home_url }}";
            if(add_home_url != 'add_home'){
                $('#PackageModal').modal('show');
            }else{
                window.location.href = "{{ url('admin/homelist/add') }}";
            }
        });
    });
</script>

<script>
    var $j = jQuery.noConflict();
</script>
<script type="text/javascript">
    var free_days               = 'Free for '+'{{ $free_days }}'+' days';
    var silver_price_monthly    = '£'+'{{ $silver_price_monthly }}'+'/'+'Month';
    var gold_price_monthly      = '£'+'{{ $gold_price_monthly }}'+'/'+'Month';
    var platinum_price_monthly  = '£'+'{{ $platinum_price_monthly }}'+'/'+'Month';
    var silver_price_yearly     = '£'+'{{ $silver_price_yearly }}'+'/'+'Year';
    var gold_price_yearly       = '£'+'{{ $gold_price_yearly }}'+'/'+'Year';
    var platinum_price_yearly   = '£'+'{{ $platinum_price_yearly }}'+'/'+'Year';

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
<script type="text/javascript">
    $(document).ready(function(){
        $('.buy_plan').on('click',function(){
            var btn = $(this);
            var package_type = $(this).text();
            // var company_charges_id  = $(this).parent("input[name=company_charges_id]").val();
            var company_charges_id  = $(this).closest('.button_buy').find('input[name=company_charges_id]').val();
            var system_admin_id     = $(this).closest('.button_buy').find("input[name=system_admin_id]").val();
            var package_duration    = $(this).closest('.button_buy').find("input[name=package_duration]").val();
            // console.log(company_charges_id);
            // console.log(system_admin_id);
            // console.log(package_duration); 
            // return false;
            if(package_type == 'Get Free'){
                // var company_charges_id  = $("input[name=company_charges_id]").val();
                // var system_admin_id     = $("input[name=system_admin_id]").val();
                // var package_duration    = $("input[name=package_duration]").val();
                // var form_date = $('#choose_package').serialize();
                // console.log(form_date);
                var _token = '{{ csrf_token()}}';
                $('.loader').show();
                $.ajax({
                    type:'post',
                    url:"{{ url('admin/system-admin/home/company-package-type')}}",
                    data:{ 'company_charges_id':company_charges_id,'system_admin_id':system_admin_id, 'package_duration':package_duration,'_token':_token},
                    success:function(resp){
                        // console.log(resp);
                        if(resp =='1'){
                            $('.loader').hide();
                            sessionStorage.reloadAfterPageLoad = true;
                            
                            $( function () {
                                if ( sessionStorage.reloadAfterPageLoad ) {
                                    alert( "Free trial period started successfully. You can add home with in the free trail limit." );
                                    sessionStorage.reloadAfterPageLoad = false;
                                }
                            });
                            window.location.reload();
                        }else{
                            alert('{{ COMMON_ERROR }}');
                        }
                        $('.loader').hide();
                    }
                });

                $('#PackageModal').modal('hide');
            }else{
                // alert('2'); return false;
                $('.cmpny_chr_id').val(company_charges_id);
                $('.sys_adm_id').val(system_admin_id);
                $('#PackageModal').modal('hide');
                $('#admin_card_modal').modal('show');
            }
            
            
        });
    });
</script>

<script type="text/javascript">
    $('#add_admin_card_detail').validate({
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
            mm_yy:{
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
            
        },
        messages:{
            card_holder_name:{
                regex: 'This field can only consist of alphabets'
            },
            card_number:{
                regex: 'This field can only consist of digits',
            },
            
            cvv:{
                regex: 'This field can only consist of digits',
            },
            

        },

        submitHandler:function(form){

            var err_txt = $('.err').text();
            if(err_txt != ''){
                return false;
            }else{
                // var form_data = $('#card_detail_form').serialize();
                
                // $('.loader').show();
                // $.ajax({
                //     type:'post',
                //     url:"{{ url('admin/system-admin/home/card-detail')}}",
                //     data:form_data,
                //     success:function(resp){
                //         // console.log(resp);
                //         if(resp == '1'){
                //             $('#card_modal').modal('hide');
                //             $('#package_modal').modal('show');
                //         }
                //         $('.loader').hide();
                //     }

                // });
                form.submit();
            }
            
        }
    });
</script>
<script type="text/javascript">
    $("input[name='mm_yy']").each(function(){
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

@endsection