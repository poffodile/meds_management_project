@extends('frontEnd.layouts.master')
@section('title','General Admin')
@section('content')

<!--main content start-->
<section id="main-content">
    <section class="wrapper p-t-80">
        <div class="container p-0">
            
            <div class="col-md-7 col-sm-7 col-xs-12">
                <!--progress bar start-->
                <section class="panel">
                    <header class="panel-heading">
                        General Admin
                        <!-- <span class="tools pull-right">
                            <a href="javascript:;" class="fa fa-times"></a>
                        </span> -->
                    </header>
                    <div class="panel-body cus-panelbody">
                        <div class="col-md-6 col-sm-6 col-xs-6 petty-cash">
                            <div class="sys-mngmnt-box" >
                                <!-- data-toggle="modal" data-target="#addServiceUserModal" -->
                                <div class="sys-mngmnticon"> <i class="fa fa-money"></i> </div>
                                <p>Petty Cash </p>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6 log_book">
                            <div class="sys-mngmnt-box" >
                                <!-- data-toggle="modal" data-target="#addStaffModal" -->
                                <div class="sys-mngmnticon"> <i class="fa fa-address-book-o"></i> </div>
                                <p>Log Book</p>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="sys-mngmnt-box" >
                                <a href="{{url('/staff/trainings')}}">
                                    <div class="sys-mngmnticon"> 
                                        <i class="fa fa-slideshare"></i>
                                    </div>
                                    <p>Staff Training</p>
                                </a> 
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="sys-mngmnt-box" >
                                <a data-target="#AgendaMeetingModal" data-toggle="modal" class="MainNavText" >
                                    <div class="sys-mngmnticon"> 
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <p>Agenda & Meetings</p>
                                </a> 
                            </div>
                        </div> 
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="sys-mngmnt-box" >
                                <a data-target="#AddWeeklyMoney" data-toggle="modal" class="MainNavText" >
                                <div class="sys-mngmnticon"> 
                                <i class="fa fa-credit-card"></i>
                                </div>
                                <p>Weekly allowance</p>
                                </a> 
                            </div>
                        </div>
                        <!--12 June 2018-->
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="sys-mngmnt-box" >
                                <a data-target="#AddShoppingBudget" data-toggle="modal" class="MainNavText" >
                                <div class="sys-mngmnticon"> 
                                <i class="fa fa-suitcase"></i>
                                </div>
                                <p>Shopping Budget</p>
                                </a> 
                            </div>
                        </div> 
                    </div>
                </section>
                <!--progress bar end-->
            </div>

            @include('frontEnd.common.sidebar_dashboard')

        </div>
    </section>
</section>
<!--main content end-->

@include('frontEnd.generalAdmin.elements.log_book')
@include('frontEnd.generalAdmin.elements.su_log_book')

@include('frontEnd.generalAdmin.elements.petty_cash')
@include('frontEnd.generalAdmin.elements.view_edit_petty_cash')
@include('frontEnd.generalAdmin.elements.agenda_meeting')
@include('frontEnd.generalAdmin.elements.weekly_allowance')
@include('frontEnd.generalAdmin.elements.shopping_budget')
@include('frontEnd.generalAdmin.elements.su_add_to_calendar')

<!-- <script>
s    //cog icon on click event - options show
    $(document).ready(function(){ 
        $(document).on('click','.settings', function(){
            $(this).find('.pop-notifbox').toggleClass('active');
            $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
        });
        $(document).on('click',function(e){
            e.stopPropagation();
            var $trigger = $(".settings");
            if($trigger !== e.target && !$trigger.has(e.target).length){
                $('.pop-notifbox').removeClass('active');
            }
        });
    });
</script> -->
        
<script>
    //3 tabs script only for general admin
    $('.logged-box').hide();
    $('.search-box').hide();
    $('.logged-btn').removeClass('active');
    $('.search-btn').removeClass('active');

    $('.add-new-btn').on('click',function(){ 
        $(this).addClass('active');
        $(this).siblings().removeClass('active');

        $(this).closest('.modal-body').find('.risk-tabs').hide();
        $(this).closest('.modal-body').find('.add-new-box').show();
    });
    $('.logged-btn').on('click',function(){ //alert(1);
        $(this).addClass('active');
        $(this).siblings().removeClass('active');

        $(this).closest('.modal-body').find('.risk-tabs').hide();
        $(this).closest('.modal-body').find('.logged-box').show();

    });
    $('.search-btn').on('click',function(){
        $(this).addClass('active');
        $(this).siblings().removeClass('active');

        $(this).closest('.modal-body').find('.risk-tabs').hide();
        $(this).closest('.modal-body').find('.search-box').show();

    });
</script>
    

@endsection        