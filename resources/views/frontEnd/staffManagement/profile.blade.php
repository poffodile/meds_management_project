@extends('frontEnd.layouts.master')
@section('title','Staff Member Profile')
@section('content')

    <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <!-- page start-->

            <div class="row">
                <div class="col-md-12">
                    <section class="panel">
                        <div class="panel-body profile-information">
                            <div class="col-md-3">
                                <?php
                                    $user_image = $staff_member->image;
                                    if(empty($user_image)){
                                        $user_image = 'default_user.jpg';
                                    } 
                                ?> 
                               <div class="profile-pic text-center">
                                   <img src="{{ userProfileImagePath.'/'.$user_image }}" alt="" class="profile_staff" />
                               </div>
                            </div>
                            <div class="col-md-6 col-sm-8 col-xs-12">
                               <div class="profile-desk p-0">
                                    <h1>{{ $staff_member->name }}</h1>
                                    <span class="text-muted">{{ $staff_member->job_title }}</span>
                                    <p class="top-def">{{ $staff_member->description }}</p>
                                    <p>
                                        <span class="profile-bigico"> 
                                            <!-- <a href="#" title="Calendar"><i class="fa fa-calendar"></i></a> -->
                                            <!-- <a href="#" title="MFC" class="mfc"><i class="fa fa-user-times"></i></a> 
                                            <a href="#" title="LS" class="living-skill-list"><i class="fa fa-child"></i></a> 
                                            <a href="#" title="File Manager"><i class="fa fa-file-pdf-o"></i></a> -->
                                        </span>
                                    </p>
                                    
                                   <p><!-- <strong style="color:#3399CC;">Job title</strong> : {{ $staff_member->job_title }}<br>
                                   <strong style="color:#3399CC;">Payroll</strong> : {{ $staff_member->payroll }}<br>
                                   <strong style="color:#3399CC;">Holiday entitlement</strong> : {{ $staff_member->holiday_entitlement }}<br> -->
                                   <!--<a href="#" class="btn btn-primary">Read Full Profile</a>-->
                               </div>
                            </div>
                            <div class="col-md-3">
                               <div class="profile-statistics">
                                    <h1>{{ $staff_member->payroll }}</h1>
                                    <p>Pay Roll</p>
                                    <h1>{{ $staff_member->holiday_entitlement }}(Hrs)</h1>
                                    <p>Holiday Entitlement</p>  
                                    <h1>{{ date('d/m/Y',strtotime($staff_member->date_of_joining)) }}</h1>
                                    <p>Joining Date</p>  
                               </div>
                            </div>
                        </div>
                    </section>
                </div> 
                <div class="col-md-12">
                    <section class="panel">
                        <header class="panel-heading tab-bg-dark-navy-blue cus-panel-heading">
                            <ul class="nav nav-tabs nav-justified ">
                                <li class="active">
                                    <a data-toggle="tab" href="#overview">
                                        Overview
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#staff_access_rights">
                                        Access Rights
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#staff_contacts" class="staff-contact-map">
                                        Contacts
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#staff_profile">
                                        Full Profile
                                    </a>
                                </li>
                                <li>
                                    <a data-toggle="tab" href="#settings">
                                        Settings
                                    </a>
                                </li>
                            </ul>
                        </header>
                        <div class="panel-body">
                            <div class="tab-content tasi-tab">
                                <div id="overview" class="tab-pane active">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="row">                                            
                                                <div class="col-md-3 col-sm-4 col-xs-12 ">
                                                    <div class="profile-nav alt">
                                                        <!-- <a href="#annualLeaveModal" data-toggle="modal"> -->
                                                        <a href="{{ url('absence/type=1?staff='.$staff_id) }}" data-toggle="modal">
                                                            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                                <div class="user-heading alt wdgt-row purple-bg">
                                                                    <i class="fa fa-files-o"></i>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="wdgt-value">
                                                                        <h4 class="count">Manage<br>Annual Leave</h4>
                                                                        <p></p>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </a>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-4 col-xs-12 ">
                                                    <div class="profile-nav alt" >
                                                        <!-- <a href="{{ url('/staff/rota/view') }}"> -->
                                                        <a href="{{ url('/rota-dashboard?user='.base64_encode($staff_id)) }}">
                                                            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                                <div class="user-heading alt wdgt-row bg-blue">
                                                                    <i class="fa fa-sliders"></i>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="wdgt-value">
                                                                        <h4 class="count">Manage<br>Rota</h4>
                                                                        <p></p>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- fa-hourglass-end  fa-life-ring  fa-hourglass-half  fa-hourglass-start  fa-clock-o  fa-tachometer  fa-sliders   -->
                                                <!-- <div class="col-md-3 col-sm-4 col-xs-12 ">
                                                    <div class="profile-nav alt" >
                                                        <a href="#sickLeaveModal" data-toggle="modal">
                                                            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                                <div class="user-heading alt wdgt-row terques-bg">
                                                                    <i class="fa fa-bed"></i>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="wdgt-value">
                                                                        <h4 class="count">Manage<br>Sick Records</h4>
                                                                        <p></p>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </a>
                                                    </div>
                                                </div> -->
                                                <div class="col-md-3 col-sm-4 col-xs-12 ">
                                                    <div class="profile-nav alt" >
                                                        <a href="{{url('/rota-absence?staff='.base64_encode($staff_id))}}">
                                                            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                                <div class="user-heading alt wdgt-row terques-bg">
                                                                    <i class="fa fa-frown-o"></i>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="wdgt-value">
                                                                        <h4 class="count">Manage<br>Absence</h4>
                                                                        <p></p>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-4 col-xs-12 ">
                                                    <div class="profile-nav alt" >
                                                        <a href="{{url('/overtime?staff='.base64_encode($staff_id))}}">
                                                            <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                                <div class="user-heading alt wdgt-row terques-bg">
                                                                    <i class="fa fa-clock-o"></i>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="wdgt-value">
                                                                        <h4 class="count">Manage<br>Overtime</h4>
                                                                        <p></p>
                                                                    </div>
                                                                </div>
                                                            </section>
                                                        </a>
                                                    </div>
                                                </div>
                                                <!-- <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <div class="below-divider"></div>
                                                </div> -->
                                            </div> 
                                            <div class="row">
                                                
                                                <div class="col-md-3 col-sm-4 col-xs-12 task-allocation-list">
                                                    <div class="profile-nav alt">
                                                        <section class="panel text-center" style="border-style:solid; border-color:#cccccc;">
                                                            <div class="user-heading alt wdgt-row red-bg">
                                                                <i class="fa fa-calendar"></i>
                                                            </div>
                                                            <div class="panel-body">
                                                                <div class="wdgt-value">
                                                                    <h4 class="count">Task<br>Allocation</h4>
                                                                    <p></p>
                                                                </div>
                                                            </div>
                                                        </section>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="feed-box text-center"></div>
                                            <div class="profile-nav alt"></div>
                                            <!-- notification start -->

                                            <section class="panel m-0">
                                                <header class="panel-heading"> Notification 
                                                    <!-- <span class="tools pull-right"> <a href="javascript:;" class="fa fa-chevron-down"></a> <a href="javascript:;" class="fa fa-cog"></a> <a href="javascript:;" class="fa fa-times"></a> </span> --> 
                                                </header>
                                                <div class="panel-body  min-ht-0">
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            @include('frontEnd.staffManagement.elements.user_rights')
                            @include('frontEnd.staffManagement.elements.staff_contacts')
                            @include('frontEnd.staffManagement.elements.staff_profile_detail')
                            @include('frontEnd.staffManagement.elements.staff_profile_settings')
                            </div>
                        </div>    
                    </section>
                </div>
            </div>
            <!-- page end-->
        </section>
    </section>
    <!--main content end-->

@include('frontEnd.staffManagement.elements.annual_leaves')
@include('frontEnd.staffManagement.elements.sick_leaves')
@include('frontEnd.staffManagement.elements.task_allocation')

<!-- commom scripts -->
<!-- <script>
    //click on cog(setting) icon to view options of a record
    $(document).ready(function(){
        $(document).on('click','.settings',function(){
            $(this).find('.pop-notifbox').toggleClass('active');
            $(this).closest('.cog-panel').siblings('.cog-panel').find('.pop-notifbox').removeClass('active');
        });
        $(window).on('click',function(e){
            e.stopPropagation();
            var $trigger = $(".settings");
            if($trigger !== e.target && !$trigger.has(e.target).length){
                $('.pop-notifbox').removeClass('active');
            }
        });
    });
</script>
<script>
    //3 tabs script
    $('.logged-box').hide();
    $('.search-box').hide();
    $('.logged-btn').removeClass('active');
    $('.search-btn').removeClass('active');

    $('.add-new-btn').on('click',function(){ 
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.add-new-box').show();
        $(this).closest('.modal-body').find('.add-new-box').siblings('.risk-tabs').hide();
    });
    $('.logged-btn').on('click',function(){ //alert(1);
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.logged-box').show();
        $(this).closest('.modal-body').find('.logged-box').siblings('.risk-tabs').hide();
    });
    $('.search-btn').on('click',function(){
        $(this).addClass('active');
        $(this).siblings().removeClass('active');
        $(this).closest('.modal-body').find('.search-box').show();
        $(this).closest('.modal-body').find('.search-box').siblings('.risk-tabs').hide();
    });
</script>

<script>
    //when click on plus button then details of the record will be shown below the record
    $('.input-plusbox').hide();
    $(document).on('click','.input-plus',function(){
        $(this).closest('.cog-panel').find('.input-plusbox').toggle();
    });
</script> -->

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&AMP;sensor=false"></script> 
<?php $api_key = env('GOOGLE_MAP_API_KEY'); ?>
<script async defer
  src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&callback=initMap">
</script>

<script>
$(document).ready(function(){
    autosize($("textarea"));
    //google map
    function initialize() {
        var myLatlng = new google.maps.LatLng({{ $latitude }}, {{ $longitude }});
        var mapOptions = {
            zoom: 15,
            scrollwheel: false,
            center: myLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
        var marker = new google.maps.Marker({
            position: myLatlng,
            map: map,
            title: '{{ $staff_member->name }}'
        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
    
    $('.staff-contact-map').click(function(){
    
        //google map in tab click initialize
        function initialize() {
            var myLatlng = new google.maps.LatLng({{ $latitude }}, {{ $longitude }});
            var mapOptions = {
                zoom: 15,
                scrollwheel: false,
                center: myLatlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            var marker = new google.maps.Marker({
                position: myLatlng,
                map: map,
                title: '{{ $staff_member->name }}'
            });
        }
        google.maps.event.addDomListener(window, 'click', initialize);
    });
    });
</script>


@endsection
